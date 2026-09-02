<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Admin PKA Litbang',
            'email' => 'admin@pkalitbang.id',
            'password' => 'password123',
            'role' => 'admin',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Test User
        User::create([
            'name' => 'User Test',
            'email' => 'user@test.com',
            'password' => 'password123',
            'phone' => '08123456789',
            'student_name' => 'Test Student',
            'student_class' => 'XII',
            'student_major' => 'IPA',
            'school_name' => 'SMA Negeri 1 Jakarta',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'address' => 'Jl. Contoh No. 123, Jakarta',
            'role' => 'user',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Sample Package
        $package = Package::create([
            'title' => 'Paket TOEFL Preparation',
            'description' => 'Persiapan lengkap untuk menghadapi tes TOEFL dengan soal-soal terbaru dan pembahasan mendetail.',
            'price' => 150000,
            'is_active' => true,
            'cards' => [
                [
                    'id' => 'card-1',
                    'title' => 'Listening Comprehension',
                    'description' => 'Latihan soal listening untuk memahami percakapan dan monolog dalam bahasa Inggris.',
                    'created_at' => now()->toDateTimeString(),
                ],
                [
                    'id' => 'card-2',
                    'title' => 'Structure & Written Expression',
                    'description' => 'Latihan tata bahasa dan struktur kalimat bahasa Inggris.',
                    'created_at' => now()->toDateTimeString(),
                ],
                [
                    'id' => 'card-3',
                    'title' => 'Reading Comprehension',
                    'description' => 'Latihan membaca dan memahami teks akademik dalam bahasa Inggris.',
                    'created_at' => now()->toDateTimeString(),
                ],
            ],
            'questions' => [
                [
                    'id' => 'q-1',
                    'card_id' => 'card-1',
                    'question' => 'What is the main topic of the conversation?',
                    'options' => ['A. Travel plans', 'B. Academic schedule', 'C. Job interview', 'D. Family gathering'],
                    'correct_answer' => 'B. Academic schedule',
                    'explanation' => 'The conversation discusses class schedules and assignments.',
                    'created_at' => now()->toDateTimeString(),
                ],
                [
                    'id' => 'q-2',
                    'card_id' => 'card-2',
                    'question' => 'Which sentence is grammatically correct?',
                    'options' => ['A. He go to school', 'B. She goes to school', 'C. They goes to school', 'D. We goes to school'],
                    'correct_answer' => 'B. She goes to school',
                    'explanation' => 'Subject "She" is singular third person, so the verb must use "goes".',
                    'created_at' => now()->toDateTimeString(),
                ],
                [
                    'id' => 'q-3',
                    'card_id' => 'card-3',
                    'question' => 'According to the passage, what is the main cause of climate change?',
                    'options' => ['A. Deforestation', 'B. Greenhouse gas emissions', 'C. Ocean pollution', 'D. Urban development'],
                    'correct_answer' => 'B. Greenhouse gas emissions',
                    'explanation' => 'The passage states that greenhouse gas emissions from human activities are the primary cause.',
                    'created_at' => now()->toDateTimeString(),
                ],
            ],
        ]);
    }
}
