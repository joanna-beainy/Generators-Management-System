<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates a default admin user only if no users exist.
     */
    public function run(): void
    {
        // Create default admin user if it doesn't exist
        User::firstOrCreate(
            ['name' => 'Admin'],
            ['password' => Hash::make('admin123')]
        );

        $this->command->info('✅ Default admin user created successfully!');
        $this->command->info('   Username: admin');
        $this->command->info('   Password: admin123');
        $this->command->warn('⚠️  Please change the password after first login!');
    }
}
