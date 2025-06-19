<?php

use App\Enum\RoleEnum;
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

});


it('allows Admin to log in successfully', function () {
 
    $this->user->assignRole($this->adminRole);

    $response = $this->postJson(route('admin.login'), [
        'email' => $this->user->email, 
        'password' => 'admin123'
    ]);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'token',
                ]
            ]);
});

it ('fails login with wrong password', function () {

    $this->user->assignRole($this->adminRole);

    $response = $this->postJson(route('admin.login'), [
        'email' => $this->user->email,
        'password' => 'wrongpassword'
    ]);

    $response->assertStatus(422)
             ->assertJson([
                 'message' => 'Login failed',
                 "errors" => ["password" => ["Invalid credentials"]]
             ]);

});

it('rejects login if user is not admin', function () {
    $user = User::factory()->create([
        'email' => 'user@test.com',
        'password' => bcrypt('userpass'),
    ]);

    // No role assigned (or assign "user" role if you seeded it)

    $response = $this->postJson(route('admin.login'), [
        'email' => 'user@test.com',
        'password' => 'userpass',
    ]);

    $response->assertStatus(422)
             ->assertJson([
                 'message' => 'Login failed',
                 "errors" => ["email" => ["User is not an admin"]]
             ]);
});