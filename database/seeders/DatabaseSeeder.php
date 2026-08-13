<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // สร้าง admin account เริ่มต้น
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'fullname' => 'ผู้ดูแลระบบ',
                'role'     => 'admin',
                'password' => bcrypt('password'),
            ]
        );

        // สร้าง staff account ตัวอย่าง
        User::firstOrCreate(
            ['username' => 'staff01'],
            [
                'fullname' => 'พนักงาน 01',
                'role'     => 'staff',
                'password' => bcrypt('password'),
            ]
        );
    }
}
