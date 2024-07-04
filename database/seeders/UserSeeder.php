<?php

namespace Database\Seeders;

use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userData = [
            'name' => 'Dev Tester',
            'password' => bcrypt('DevTest')
        ];

        $email = 'dev_test@gmail.com';

       
        $user = User::updateOrCreate(
            ['email' => $email], 
            $userData 
        );
    }
}
