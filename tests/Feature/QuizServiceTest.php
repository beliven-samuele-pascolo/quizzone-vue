<?php

use App\Enums\QuestionStatus;
use App\Enums\UserRole;
use App\Events\QuestionUpdated;
use App\Models\Question;
use App\Models\User;
use App\Services\QuizService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new QuizService;

    // previene il firing reale degli eventi durante i test
    Event::fake(QuestionUpdated::class);
});

test('admin can start a new question', function () {
    $fakeBody = 'Test di una domanda super difficile';
    $this->service->startNewQuestion($fakeBody);

    $this->assertDatabaseHas('questions', [
        'body' => $fakeBody,
        'status' => QuestionStatus::Active,
    ]);

    // verifica il dispatch dell'evento
    Event::assertDispatched(QuestionUpdated::class);
});

test('it resets banned players when starting a new question', function () {
    $player = User::factory()->create(['role' => UserRole::Player, 'banned' => true]);

    $this->service->startNewQuestion('Test nuova domanda');

    // Verifica che l'utente non sia più bannato
    expect($player->fresh()->banned)->toBeFalse();
});

test('player can buzz on active question', function () {
    $player = User::factory()->create(['role' => UserRole::Player]);
    $this->service->startNewQuestion('Test domanda attiva');

    $this->service->buzz($player);

    $question = Question::first();
    expect($question->status)->toBe(QuestionStatus::Buzzed)
        ->and($question->buzzed_user_id)->toBe($player->id);

    Event::assertDispatched(QuestionUpdated::class);
});

test('banned player cannot buzz', function () {
    $player = User::factory()->create(['role' => UserRole::Player, 'banned' => true]);
    $this->service->startNewQuestion('Test domanda attiva');
    $this->service->buzz($player);

    expect(Question::first()->status)->toBe(QuestionStatus::Active);
});

test('cannot buzz if question is not active', function () {
    $player = User::factory()->create();

    Question::create([
        'body' => 'Test',
        'status' => QuestionStatus::Closed,
    ]);

    $this->service->buzz($player);

    expect(Question::first()->buzzed_user_id)->toBeNull();

    // non dovrebbe partire nessun dispatch in questo caso
    Event::assertNotDispatched(QuestionUpdated::class);
});

test('correct answer should increment score and close question', function () {
    $player = User::factory()->create(['score' => 0]);
    $this->service->startNewQuestion('Test domanda random');

    $this->service->buzz($player);

    $this->service->handleAnswer(true);

    $question = Question::first();

    expect($player->fresh()->score)->toBe(1)
        ->and($question->status)->toBe(QuestionStatus::Closed)
        ->and($question->winner_user_id)->toBe($player->id);
});

test('wrong answer should ban player and reopen question if not all players are banned', function () {
    $player = User::factory()->create(['score' => 0, 'banned' => false]);
    $player2 = User::factory()->create(['score' => 0, 'banned' => false]);

    $this->service->startNewQuestion('Test domanda random');

    $this->service->buzz($player);

    $this->service->handleAnswer(false);

    $question = Question::first();

    expect($player->fresh()->banned)->toBeTrue()
        ->and($player->fresh()->score)->toBe(0)
        ->and($question->status)->toBe(QuestionStatus::Active)
        ->and($question->buzzed_user_id)->toBeNull();
});

test('wrong answer should ban player and close question if all players are banned', function () {
    $player = User::factory()->create(['score' => 0, 'banned' => false]);

    $this->service->startNewQuestion('Test domanda random');

    $this->service->buzz($player);

    $this->service->handleAnswer(false);

    $question = Question::first();

    expect($player->fresh()->banned)->toBeTrue()
        ->and($player->fresh()->score)->toBe(0)
        ->and($question->status)->toBe(QuestionStatus::Closed)
        ->and($question->buzzed_user_id)->toBeNull();
});

test('it closes question when timer expires', function () {
    $question = Question::create([
        'body' => 'Test domanda scaduta',
        'status' => QuestionStatus::Active,
        'timer_ends_at' => now()->subSeconds(5),  // scaduta 5s fa
    ]);

    $this->service->checkAndCloseIfExpired();

    expect($question->fresh()->status)->toBe(QuestionStatus::Closed);

    // l'evento viene triggerato solo quando il timer scade
    Event::assertDispatched(QuestionUpdated::class);
});

test('it does not close question if timer is not expired yet', function () {
    $question = Question::create([
        'body' => 'Test domanda ancora valida',
        'status' => QuestionStatus::Active,
        'timer_ends_at' => now()->addSeconds(10), // mancano 10s
    ]);

    $this->service->checkAndCloseIfExpired();

    expect($question->fresh()->status)->toBe(QuestionStatus::Active);
});

test('it should give victory', function () {
    $player = User::factory()->create([
        'role' => UserRole::Player,
        'score' => 4, // manca un punto alla vittoria
    ]);

    $this->service->startNewQuestion('Test domanda finale per la vittoria');
    $this->service->buzz($player);
    $this->service->handleAnswer(true); // risposta corretta
    expect($player->fresh()->score)->toBe(5);

    Event::assertDispatched(QuestionUpdated::class);
});
