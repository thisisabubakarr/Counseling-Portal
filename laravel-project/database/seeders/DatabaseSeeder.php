<?php

namespace Database\Seeders;

use App\Models\Counselor;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $counselors = [
            [
                'counselor_code' => 'CNS-WAJEEHA',
                'name' => 'Wajeeha Nabeel',
                'email' => 'wajeeha.nabeel@bnu.edu.pk',
                'active' => true,
            ],
            [
                'counselor_code' => 'CNS-HAREEM',
                'name' => 'Hareem Jamil',
                'email' => 'hareem.jamil@bnu.edu.pk',
                'active' => true,
            ],
        ];

        foreach ($counselors as $data) {
            Counselor::updateOrCreate(
                ['counselor_code' => $data['counselor_code']],
                $data + ['password' => Hash::make('Counselor@123')]
            );
        }

        Student::updateOrCreate(
            ['roll_number' => 'CS-2021-123'],
            [
                'name' => 'John Doe',
                'dob' => '2002-03-15',
                'phone' => '0300-1234567',
                'department' => 'Computer Science',
                'semester' => '6th Semester',
                'session_status' => 'Ongoing',
                'nature_of_consent' => 'Standard',
                'password' => Hash::make('Student@123'),
            ]
        );
    }
}
