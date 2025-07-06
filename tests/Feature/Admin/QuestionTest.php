<?php

use App\Enum\QuestionTypes;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\User;
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
    'matching' => [[
        'question' => 'Match the countries with their capitals',
        'type' => QuestionTypes::MATCHING->value,
        'options' => [
            'France' => 'Paris',
            'Germany' => 'Berlin',
            'Italy' => 'Rome',
        ],
        'correct_answer' => 'France-Paris, Germany-Berlin, Italy-Rome',
    ]],
]);

it('allows admin to get all questions', function () {
    Question::factory()->count(3)->create(['quiz_id' => $this->quiz->id]);
    
    $response = $this->actingAs($this->user)
                    ->getJson(route('admin.questions.index'));

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'question', 'type', 'quiz_id']
                ]
            ]);
});

it('allows admin to get a specific question', function () {
    $question = Question::factory()->create(['quiz_id' => $this->quiz->id]);
    
    $response = $this->actingAs($this->user)
                    ->getJson(route('admin.questions.show', $question));

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'question', 'type', 'quiz_id']
            ])
            ->assertJsonFragment([
                'id' => $question->id,
                'question' => $question->question,
            ]);
});

it('allows admin to update a question', function () {
    $question = Question::factory()->create(['quiz_id' => $this->quiz->id]);
    
    $updateData = [
        'question' => 'Updated question text',
        'type' => QuestionTypes::FILL_IN_THE_BLANK->value,
        'quiz_id' => $this->quiz->id,
        'correct_answer' => 'Updated answer',
    ];
    
    $response = $this->actingAs($this->user)
                    ->putJson(route('admin.questions.update', $question), $updateData);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'question', 'type', 'quiz_id', 'correct_answer']
            ])
            ->assertJsonFragment([
                'question' => 'Updated question text',
                'correct_answer' => 'Updated answer',
            ]);
});

it('allows admin to delete a question', function () {
    $question = Question::factory()->create(['quiz_id' => $this->quiz->id]);
    
    $response = $this->actingAs($this->user)
                    ->deleteJson(route('admin.questions.destroy', $question));

    $response->assertStatus(200)
            ->assertJsonFragment([
                'data' => 'Question deleted successfully'
            ]);
            
    $this->assertDatabaseMissing('questions', ['id' => $question->id]);
});

it('requires authentication to access question endpoints', function () {
    $question = Question::factory()->create(['quiz_id' => $this->quiz->id]);
    
    // Test all endpoints without authentication
    $this->getJson(route('admin.questions.index'))->assertStatus(401);
    $this->getJson(route('admin.questions.show', $question))->assertStatus(401);
    $this->postJson(route('admin.questions.store'), [])->assertStatus(401);
    $this->putJson(route('admin.questions.update', $question), [])->assertStatus(401);
    $this->deleteJson(route('admin.questions.destroy', $question))->assertStatus(401);
});

it('requires admin role to access question endpoints', function () {
    $regularUser = User::factory()->create();
    $question = Question::factory()->create(['quiz_id' => $this->quiz->id]);
    
    // Test all endpoints with regular user (non-admin)
    $this->actingAs($regularUser)->getJson(route('admin.questions.index'))->assertStatus(403);
    $this->actingAs($regularUser)->getJson(route('admin.questions.show', $question))->assertStatus(403);
    $this->actingAs($regularUser)->postJson(route('admin.questions.store'), [])->assertStatus(403);
    $this->actingAs($regularUser)->putJson(route('admin.questions.update', $question), [])->assertStatus(403);
    $this->actingAs($regularUser)->deleteJson(route('admin.questions.destroy', $question))->assertStatus(403);
});

it('validates required fields when creating a question', function ($field, $value, $expectedErrors) {
    $payload = [
        'question' => 'Sample question?',
        'type' => QuestionTypes::MULTIPLE_CHOICES->value,
        'quiz_id' => $this->quiz->id,
        'correct_answer' => 'A',
        'options' => ['A' => 'Option A', 'B' => 'Option B'],
    ];
    
    $payload[$field] = $value;
    
    $response = $this->actingAs($this->user)
                    ->postJson(route('admin.questions.store'), $payload);

    $response->assertStatus(422)
            ->assertJsonValidationErrors($expectedErrors);
})->with([
    'missing question' => ['question', null, ['question']],
    'empty question' => ['question', '', ['question']],
    'question too long' => ['question', str_repeat('a', 1001), ['question']],
    'missing type' => ['type', null, ['type']],
    'invalid type' => ['type', 'invalid_type', ['type']],
    'missing quiz_id' => ['quiz_id', null, ['quiz_id']],
    'non-existent quiz_id' => ['quiz_id', 999999, ['quiz_id']],
]);

it('validates question creation with media url', function () {
    $payload = [
        'question' => 'What do you see in this image?',
        'type' => QuestionTypes::MULTIPLE_CHOICES->value,
        'quiz_id' => $this->quiz->id,
        'correct_answer' => 'A',
        'options' => ['A' => 'A cat', 'B' => 'A dog'],
        'media_url' => 'https://example.com/image.jpg',
    ];
    
    $response = $this->actingAs($this->user)
                    ->postJson(route('admin.questions.store'), $payload);

    $response->assertStatus(200)
            ->assertJsonFragment([
                'media_url' => 'https://example.com/image.jpg',
            ]);
});

it('validates invalid media url format', function () {
    $payload = [
        'question' => 'Sample question?',
        'type' => QuestionTypes::FILL_IN_THE_BLANK->value,
        'quiz_id' => $this->quiz->id,
        'correct_answer' => 'Answer',
        'media_url' => 'not-a-valid-url',
    ];
    
    $response = $this->actingAs($this->user)
                    ->postJson(route('admin.questions.store'), $payload);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['media_url']);
});

it('handles question creation for different question types without options', function ($type) {
    $payload = [
        'question' => 'Sample question for ' . $type,
        'type' => $type,
        'quiz_id' => $this->quiz->id,
        'correct_answer' => 'Sample answer',
    ];
    
    $response = $this->actingAs($this->user)
                    ->postJson(route('admin.questions.store'), $payload);

    $response->assertStatus(200)
            ->assertJsonFragment([
                'type' => $type,
                'question' => 'Sample question for ' . $type,
            ]);
})->with([
    QuestionTypes::FILL_IN_THE_BLANK->value,
    QuestionTypes::ESSAY->value,
    QuestionTypes::TRUE_FALSE->value,
]);

it('creates question with correct_answer validation for true/false questions', function ($answer) {
    $payload = [
        'question' => 'The earth is round',
        'type' => QuestionTypes::TRUE_FALSE->value,
        'quiz_id' => $this->quiz->id,
        'correct_answer' => $answer,
    ];
    
    $response = $this->actingAs($this->user)
                    ->postJson(route('admin.questions.store'), $payload);

    $response->assertStatus(200)
            ->assertJsonFragment([
                'correct_answer' => $answer,
            ]);
})->with(['true', 'false', 'TRUE', 'FALSE', '1', '0']);

it('handles questions with long correct answers', function () {
    $longAnswer = trim(str_repeat('This is a long answer. ', 20)); // Around 500 chars, trimmed
    
    $payload = [
        'question' => 'Write an essay about programming',
        'type' => QuestionTypes::ESSAY->value,
        'quiz_id' => $this->quiz->id,
        'correct_answer' => $longAnswer,
    ];
    
    $response = $this->actingAs($this->user)
                    ->postJson(route('admin.questions.store'), $payload);

    $response->assertStatus(200)
            ->assertJsonPath('data.correct_answer', $longAnswer);
});

it('rejects correct_answer that exceeds maximum length', function () {
    $tooLongAnswer = str_repeat('a', 501); // Exceeds 500 char limit
    
    $payload = [
        'question' => 'Sample question',
        'type' => QuestionTypes::ESSAY->value,
        'quiz_id' => $this->quiz->id,
        'correct_answer' => $tooLongAnswer,
    ];
    
    $response = $this->actingAs($this->user)
                    ->postJson(route('admin.questions.store'), $payload);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['correct_answer']);
});

it('handles updating question with partial data', function () {
    $question = Question::factory()->create([
        'quiz_id' => $this->quiz->id,
        'question' => 'Original question',
        'type' => QuestionTypes::FILL_IN_THE_BLANK->value,
    ]);
    
    // Only update the question text
    $updateData = [
        'question' => 'Updated question only',
        'type' => $question->type->value,
        'quiz_id' => $question->quiz_id,
    ];
    
    $response = $this->actingAs($this->user)
                    ->putJson(route('admin.questions.update', $question), $updateData);

    $response->assertStatus(200)
            ->assertJsonFragment([
                'question' => 'Updated question only',
            ]);
});

it('returns 404 when trying to access non-existent question', function () {
    $response = $this->actingAs($this->user)
                    ->getJson(route('admin.questions.show', 999999));

    $response->assertStatus(404);
});

it('creates multiple choice question with complex options structure', function () {
    $payload = [
        'question' => 'Which of the following are programming languages?',
        'type' => QuestionTypes::MULTIPLE_CHOICES->value,
        'quiz_id' => $this->quiz->id,
        'correct_answer' => 'A,C,D',
        'options' => [
            'A' => 'JavaScript',
            'B' => 'HTML',
            'C' => 'Python',
            'D' => 'Java',
            'E' => 'CSS',
        ],
    ];
    
    $response = $this->actingAs($this->user)
                    ->postJson(route('admin.questions.store'), $payload);

    $response->assertStatus(200)
            ->assertJsonFragment([
                'correct_answer' => 'A,C,D',
            ])
            ->assertJsonPath('data.options.A', 'JavaScript')
            ->assertJsonPath('data.options.E', 'CSS');
});