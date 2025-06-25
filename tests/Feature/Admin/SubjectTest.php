<?php

use App\Enum\RoleEnum;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminRole = Role::create(['name' => RoleEnum::ADMIN]);

    $this->user = User::factory()->create([
        'name' => 'Admin Bruce',
        'email' => 'admin@test.com',
        'password' => Hash::make('admin123'),
    ]);

     $this->user->assignRole($this->adminRole);

    $this->subjects  = Subject::factory()->count(3)->create();
    $this->subject  = Subject::factory()->create();
});

it("allows admin user to create a subject", function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('admin.subject.store'),
            ['name'=> 'Math']
        );
    
    $response->assertStatus(200)
              ->assertJsonStructure([
                'success' ,
                'data' => ['name']
              ])
             ->assertJsonFragment([
                 'name' => 'Math',
             ]);
});

it("allows admin to get all subjects created",  function () {
    $response = $this->actingAs($this->user)->getJson(route('admin.subject.index'));

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'=>  [['name']]
            ]);
});

it("allows admin to update a subject", function () {
    $response = $this->actingAs($this->user)
                    ->putJson(route("admin.subject.update", $this->subject), [
                        'name' => 'Update Subject name'
                    ]);
    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'=>  ['name']
            ])
            ->assertJsonFragment([
                 'name' => 'Update Subject name',
            ]);
});

it("allows admin to delete subject", function () {
    $response = $this->actingAs($this->user)
                     ->deleteJson(route("admin.subject.destroy", $this->subject));

    $response->assertStatus(200)
            ->assertJsonFragment([
                'data' => 'Subject deleted successfully'
            ]);
});