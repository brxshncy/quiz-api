<?php

use App\Enum\QuestionTypes;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setupAdminAndSubjects();
    $this->quiz = Quiz::factory()->create();
});

it('allows admin user to create a question associated with a quiz with different types', function ($payload) {
  
    $payload['quiz_id'] = $this->quiz->id;
    
    $response = $this->actingAs($this->user)
                    ->postJson(route('admin.questions.store'), $payload);

    $response->assertStatus(200);
    
    // Base JSON structure that all question types should have
    $baseStructure = [
        'success',
        'data' => ['question', 'type', 'quiz_id', 'correct_answer', 'id']
    ];
    
    // If it's a multiple choice question, also assert the options key
    if ($payload['type'] === QuestionTypes::MULTIPLE_CHOICES->value) {
        $baseStructure['data'][] = 'options';
    }
    
    $response->assertJsonStructure($baseStructure);
})->with([
    'multiple choices' => [[
        'question' => 'What is the capital of France?',
        'type' => QuestionTypes::MULTIPLE_CHOICES->value,
        'options' => [
            'A' => 'Paris',
            'B' => 'London',
            'C' => 'Berlin',
            'D' => 'Madrid',
        ],
        'correct_answer' => 'A',
    ]],
    'fill in the blank' => [[
        'question' => 'What is the capital of France?',
        'type' => QuestionTypes::FILL_IN_THE_BLANK->value,
        'correct_answer' => 'Paris',
    ]],
    'essay' => [[
        'question' => 'Sample essay',
        'type' => QuestionTypes::ESSAY->value,
        'correct_answer' => 'Answer essay',
    ]],
    'true false' => [[
        'question' => 'Is F the first letter of France',
        'type' => QuestionTypes::TRUE_FALSE->value,
        'correct_answer' => 'true',
    ]],
]);