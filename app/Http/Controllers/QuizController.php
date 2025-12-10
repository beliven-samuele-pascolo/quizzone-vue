<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class QuizController extends Controller
{
    // inizializzo la pagina principale e i componenti Vue con tutti i dati necessari usando Inertia
    public function index(QuizService $service)
    {
        $currentQuestion = $service->getCurrentQuestion();
        if ($currentQuestion) {
            $currentQuestion->load('buzzedUser');
        }

        return Inertia::render('QuizRoom', [
            'auth' => [
                'user' => Auth::user(),
                'is_admin' => Auth::user()->role === UserRole::Admin,
                'is_banned' => Auth::user()->banned,
            ],
            'question' => $currentQuestion,
            'game_winner' => User::where('score', '>=', 5)->first(),
            'players' => User::where('role', UserRole::Player)
                ->orderByDesc('score')
                ->get(),
        ]);
    }

    public function start(Request $request, QuizService $service)
    {
        if (Auth::user()->role !== UserRole::Admin) {
            abort(403);
        }

        $request->validate(['text' => 'required|string|min:3']);
        $service->startNewQuestion($request->text);

        return back();  // è un 302
    }

    public function buzz(QuizService $service)
    {
        $service->buzz(Auth::user());

        return back();
    }

    public function answer(Request $request, QuizService $service)
    {
        $request->validate(['answer' => 'required|string|max:255']);

        $service->answer(Auth::user(), $request->answer);

        return back();
    }

    public function validate(Request $request, QuizService $service)
    {
        if (Auth::user()->role !== UserRole::Admin) {
            abort(403);
        }

        $request->validate(['correct' => 'required|boolean']);

        $service->handleAnswer($request->correct);

        return back();
    }

    public function reset(QuizService $service)
    {
        if (Auth::user()->role !== UserRole::Admin) {
            abort(403);
        }
        $service->resetGame();

        return back();
    }

    public function timeout(QuizService $service)
    {
        if (Auth::user()->role !== UserRole::Admin) {
            abort(403);
        }
        $service->checkAndCloseIfExpired();

        return back();
    }
}
