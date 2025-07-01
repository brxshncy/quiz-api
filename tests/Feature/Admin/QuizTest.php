<?php

use App\Enum\RoleEnum;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
      setupAdminAndSubjects();

      $this->payload = [
        'title' => 'Sample Quiz',
        'description' => 'Sample Quiz',
        'subject_id' => $this->subject->id,
      ];

      $this->quiz = Quiz::factory()->create();

      $this->quizzes = Quiz::factory()->count(3)->create();
});

it("allows admin user to create set of quizzes under a subject", function() {
    $response = $this->actingAs($this->user)
                    ->postJson(route('admin.quizzes.store'), $this->payload);

    $response->assertStatus(200)
              ->assertJsonStructure([
                'success' ,
                'data' => ['title', 'description', 'subject_id', 'id', ]
              ])
             ->assertJsonFragment([
                'title' => 'Sample Quiz',
                'description' => 'Sample Quiz',
                'subject_id' => $this->subject->id,
             ]);
});

it("allows admin user to update set of quizzes under a subject", function() {
    $response = $this->actingAs($this->user)
                    ->putJson(route('admin.quizzes.update', $this->quiz), [
                        'title' => 'Update Quiz',
                        'description' => 'Update Decsription',
                        'subject_id' => $this->subject->id,
                    ]);

    $response->assertStatus(200)
              ->assertJsonStructure([
                'success' ,
                'data' => ['title', 'description', 'subject_id', 'id' ]
              ])
             ->assertJsonFragment([
                'title' => 'Update Quiz',
                'description' => 'Update Decsription',
                'subject_id' => $this->subject->id,
             ]);
});

it("allows admin user to get set of quizzes under a subject",  function () {
    $response = $this->actingAs($this->user)->getJson(route('admin.quizzes.index'));

    $response->assertStatus(200)
           ->assertJsonStructure([
                'success' ,
                'data' => [['title', 'description', 'subject_id', 'id' ]]
           ]);
});


it("allows admin to delete subject", function () {
    $response = $this->actingAs($this->user)
                     ->deleteJson(route("admin.quizzes.destroy", $this->quiz));

    $response->assertStatus(200)
            ->assertJsonFragment([
                'data' => 'Quiz deleted successfully'
            ]);
});