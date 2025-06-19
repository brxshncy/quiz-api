<?php

namespace Database\Seeders;

use App\Enum\RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Create Admin role 

         Role::firstOrCreate(['name' => RoleEnum::ADMIN]);

         $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Bruce', 
                'password' => bcrypt('admin123'),
            ]
        );

        $admin->assignRole(RoleEnum::ADMIN);
    }
}
