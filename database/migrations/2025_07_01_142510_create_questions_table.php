<?php

use App\Enum\QuestionTypes;
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
            $table->foreignId('quiz_id')->constrained('quizzes');
            $table->string('question');
            $table->enum('type', [
                QuestionTypes::MULTIPLE_CHOICES->value,
                QuestionTypes::FILL_IN_THE_BLANK->value,    
                QuestionTypes::ESSAY->value,
                QuestionTypes::TRUE_FALSE->value,
                QuestionTypes::MATCHING->value,
            ]);
            $table->json('options')->nullable();
            $table->string('correct_answer')->nullable();
            $table->string('media_url')->nullable();
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
