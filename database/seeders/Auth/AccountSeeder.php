<?php

namespace Database\Seeders\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Dokumen Admin',
            'username' => 'dokumen.mamura.admin',
            'email' => 'mamura.development@gmail.com',
            'password' => Hash::make('dokumenmamuraadmin123'),
            'register_at' => Carbon::now(env('APP_TIMEZONE'))
        ]);
    }
}
