<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
});

// test se l'admin riceve i props corretti
test('index renders the correct component and props for admin', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->get(route('quiz.index'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('QuizRoom')
            ->has('players')
            ->has('question')
        );
});

// test se il player riceve i props corretti
test('index renders correct props for player', function () {
    $player = User::factory()->create(['role' => UserRole::Player]);

    $this->actingAs($player)
        ->get(route('quiz.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('QuizRoom')
        );
});
