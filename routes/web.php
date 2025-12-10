<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('quiz.index');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/play', [QuizController::class, 'index'])->name('quiz.index');
    Route::post('/quiz/start', [QuizController::class, 'start'])->name('quiz.start');
    Route::post('/quiz/buzz', [QuizController::class, 'buzz'])->name('quiz.buzz');
    Route::post('/quiz/answer', [QuizController::class, 'answer'])->name('quiz.answer');
    Route::post('/quiz/validate', [QuizController::class, 'validate'])->name('quiz.validate'); // Admin decide
    Route::post('/quiz/reset', [QuizController::class, 'reset'])->name('quiz.reset');
    Route::post('/quiz/timeout', [QuizController::class, 'timeout'])->name('quiz.timeout');
});

require __DIR__.'/auth.php';
