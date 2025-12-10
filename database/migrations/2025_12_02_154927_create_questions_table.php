<?php

use App\Enums\QuestionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('body')->nullable();
            // Stati: vedi QuestionStatus enum
            $table->enum('status', array_column(QuestionStatus::cases(), 'value'))->default(QuestionStatus::Pending->value);
            // foreignId -> per mantenere integrità referenziale
            $table->foreignId('buzzed_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('timer_ends_at')->nullable();
            $table->text('answer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
