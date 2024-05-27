<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        User::create([
            'name' => 'Admin',
            'surname' => 'Administrador',
            'email' => 'admin@admin.com',
            'username' => 'admin',
            'password' => Hash::make('admin'),            
            'is_active' => true,
            'created_at' => now(),
        ])->assignRole('admin');
        
        User::create([
            'name' => 'Juan',
            'surname' => 'Perez',
            'email' => 'jperez@sit.com',
            'username' => 'jperez',
            'password' => Hash::make('12345678'),            
            'is_active' => true,
            'created_at' => now(),
        ])->assignRole('tesorero');

        User::create([
            'name' => 'Juan',
            'surname' => 'Gonzalez',
            'email' => 'jgonzalez@sit.com',
            'username' => 'jgonzalez',
            'password' => Hash::make('12345678'),            
            'is_active' => true,
            'created_at' => now(),
        ])->assignRole('auxiliar');

        User::create([
            'name' => 'Roberto',
            'surname' => 'Gomez',
            'email' => 'rgomez@sit.com',
            'username' => 'rgomez',
            'password' => Hash::make('12345678'),            
            'is_active' => true,
            'created_at' => now(),
        ])->assignRole('administrativo');
    }
}
