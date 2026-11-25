<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Les vraies données de démo (catalogue, annonces, commandes…) viendront avec la tâche « Données de démo ».
     */
    public function run(): void
    {
        User::factory()->create([
            'username' => 'test',
            'email' => 'test@example.com',
        ]);
    }
}
