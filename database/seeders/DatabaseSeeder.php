<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        for($i=0; $i<20; $i++) {
            $user = User::factory()->create([
                "num"=>mt_rand(1,3)
            ]);
            Post::factory()
                ->count(mt_rand(0, 10))
                ->create([
                    "user_id" => $user->id
                ]);
        }
    }
}
