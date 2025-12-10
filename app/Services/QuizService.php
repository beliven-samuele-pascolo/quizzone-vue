<?php

namespace App\Services;

use App\Enums\QuestionStatus;
use App\Enums\UserRole;
use App\Events\QuestionUpdated;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QuizService
{
    public function getCurrentQuestion(): ?Question
    {
        return Question::whereNot('status', QuestionStatus::Closed)->first();
    }

    private function broadcastGameState(): void
    {
        $question = $this->getCurrentQuestion();
        if ($question) {
            $question->load('buzzedUser');
        }

        $players = User::where('role', UserRole::Player)->orderByDesc('score')->get();
        $winner = User::where('score', '>=', 5)->first();

        $gameData = [
            'question' => $question,
            'players' => $players,
            'game_winner' => $winner,
        ];

        QuestionUpdated::dispatch($gameData);
    }

    public function startNewQuestion(string $text): void
    {
        // Transaction per garantire che tutte le operazioni siano svolte correttamente
        DB::transaction(function () use ($text) {

            // reset ban giocatori
            User::where('role', UserRole::Player)->update(['banned' => false]);

            Question::create([
                'body' => $text,
                'status' => QuestionStatus::Active,
                'timer_ends_at' => now()->addSeconds(30),
            ]);
        });

        $this->broadcastGameState();
    }

    // Resetta il gioco -> azzera punteggi e chiude domande attive
    public function resetGame(): void
    {
        DB::transaction(function () {
            User::query()->update(['score' => 0, 'banned' => false]);
            Question::whereIn('status', [QuestionStatus::Active, QuestionStatus::Buzzed])->update(['status' => QuestionStatus::Closed]);
        });

        $this->broadcastGameState();
    }

    // un player preme il buzzer
    public function buzz(User $user): void
    {
        $question = $this->getCurrentQuestion();

        if (! $question || $question->status !== QuestionStatus::Active || $user->banned || now()->greaterThan($question->timer_ends_at)) {
            return;
        }

        // atomic lock su redis con 1 secondo di attesa -> TTL deve bastare al db per l'operazione
        Cache::lock('quiz_buzzer_lock', 1)->get(function () use ($question, $user) {

            // ri-check condizioni dopo il lock -> consigliato
            $question->refresh();
            $user->refresh();

            if ($question->status !== QuestionStatus::Active || $user->banned) {
                return;
            }

            // Blocca il gioco e prenota
            $question->update([
                'status' => QuestionStatus::Buzzed,
                'buzzed_user_id' => $user->id,
                'timer_ends_at' => now()->addSeconds(10),
                'answer' => null,
            ]);

            $this->broadcastGameState();
        });
    }

    // gestione risposta data dal giocatore
    public function answer(User $user, string $text): void
    {
        $question = $this->getCurrentQuestion();

        if (! $question || $question->status !== QuestionStatus::Buzzed || $question->buzzed_user_id !== $user->id || now()->greaterThan($question->timer_ends_at)) {
            return;
        }

        // Salva la risposta data dal giocatore
        $question->update([
            'answer' => $text,
            'timer_ends_at' => null,
        ]);

        $this->broadcastGameState();
    }

    // gestione validazione della risposta dall'admin
    public function handleAnswer(bool $isCorrect): void
    {
        $question = $this->getCurrentQuestion();

        if (! $question || ! $question->buzzed_user_id) {
            return;
        }

        $buzzedUser = $question->buzzedUser;

        if ($isCorrect) {
            // risposta corretta -> +1 punto e chiudi domanda
            DB::transaction(function () use ($buzzedUser, $question) {
                $buzzedUser->increment('score');

                $question->update([
                    'status' => QuestionStatus::Closed,
                    'winner_user_id' => $buzzedUser->id,
                ]);
            });
        } else {
            // risposta errata -> banna giocatore e riattiva domanda
            DB::transaction(function () use ($buzzedUser, $question) {
                $buzzedUser->update(['banned' => true]);

                $question->update([
                    'status' => QuestionStatus::Active,
                    'buzzed_user_id' => null,
                    'timer_ends_at' => now()->addSeconds(10),
                    'answer' => null,
                ]);

                $this->closeIfAllBanned();
            });
        }

        $this->broadcastGameState();
    }

    public function checkAndCloseIfExpired(): void
    {
        $question = $this->getCurrentQuestion();

        if (! $question || ! in_array($question->status, [QuestionStatus::Active, QuestionStatus::Buzzed])) {
            return;
        }

        if ($question->timer_ends_at && now()->greaterThanOrEqualTo($question->timer_ends_at)) {

            if ($question->status === QuestionStatus::Buzzed) {
                // un giocatore aveva premuto il buzzer ma non ha risposto in tempo:
                // - banna il giocatore
                // - riattiva la domanda con timer 10s
                $buzzedUser = $question->buzzedUser;
                $buzzedUser->update(['banned' => true]);
                $question->update(['buzzed_user_id' => null, 'answer' => null, 'status' => QuestionStatus::Active, 'timer_ends_at' => now()->addSeconds(10)]);

                $this->closeIfAllBanned();
            } else {
                // nessuno ha premuto il buzzer -> chiudi la domanda senza vincitore
                $question->update(['status' => QuestionStatus::Closed]);
            }

            $this->broadcastGameState();
        }
    }

    private function closeIfAllBanned(): void
    {
        $question = $this->getCurrentQuestion();
        $allBanned = User::where('role', UserRole::Player)->where('banned', false)->count() === 0;

        if ($allBanned) {
            $question->update(['status' => QuestionStatus::Closed]);
        }
    }
}
