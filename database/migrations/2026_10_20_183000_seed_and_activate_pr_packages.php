<?php

use App\Models\Package;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Perpanjang seluruh jadwal paket yang ada agar tidak expired dan tetap aktif
        DB::table('packages')->update([
            'is_active' => true,
            'start_date' => now()->subDays(7)->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'start_time' => null,
            'end_time' => null,
            'show_answer_key' => true,
            'show_explanation' => true,
            'show_score' => true,
        ]);

        // 2. Buat / Update Paket PR Matematika Pekan 43
        $questionsMtk = [
            [
                'id' => 'q-mtk-1',
                'question' => 'Jika 3x + 7 = 22, berapakah nilai dari 2x - 1?',
                'options' => [
                    'A. 7',
                    'B. 9',
                    'C. 11',
                    'D. 13',
                ],
                'correct_answer' => 'B. 9',
                'explanation' => '3x = 22 - 7 => 3x = 15 => x = 5. Maka 2x - 1 = 2(5) - 1 = 9.',
                'created_at' => now()->toDateTimeString(),
            ],
            [
                'id' => 'q-mtk-2',
                'question' => 'Sebuah toko memberikan diskon 20% untuk sebuah baju seharga Rp 150.000. Berapa harga yang harus dibayar pembeli setelah diskon?',
                'options' => [
                    'A. Rp 110.000',
                    'B. Rp 120.000',
                    'C. Rp 125.000',
                    'D. Rp 130.000',
                ],
                'correct_answer' => 'B. Rp 120.000',
                'explanation' => 'Diskon = 20% x 150.000 = Rp 30.000. Harga bayar = 150.000 - 30.000 = Rp 120.000.',
                'created_at' => now()->toDateTimeString(),
            ],
            [
                'id' => 'q-mtk-3',
                'question' => 'Suatu persegi panjang memiliki panjang (2x + 3) cm dan lebar (x + 1) cm. Jika kelilingnya 32 cm, berapakah luas persegi panjang tersebut?',
                'options' => [
                    'A. 55 cm²',
                    'B. 60 cm²',
                    'C. 65 cm²',
                    'D. 70 cm²',
                ],
                'correct_answer' => 'A. 55 cm²',
                'explanation' => 'Keliling = 2(p + l) = 2(3x + 4) = 6x + 8 = 32 => 6x = 24 => x = 4. Maka p = 11 cm dan l = 5 cm. Luas = 11 x 5 = 55 cm².',
                'created_at' => now()->toDateTimeString(),
            ],
            [
                'id' => 'q-mtk-4',
                'question' => 'Rasio siswa laki-laki dan perempuan dalam suatu kelas adalah 3 : 5. Jika total siswa 40 anak, berapa banyak siswa perempuan?',
                'options' => [
                    'A. 15 siswa',
                    'B. 20 siswa',
                    'C. 25 siswa',
                    'D. 30 siswa',
                ],
                'correct_answer' => 'C. 25 siswa',
                'explanation' => 'Jumlah rasio = 3 + 5 = 8. Siswa perempuan = (5/8) x 40 = 25 siswa.',
                'created_at' => now()->toDateTimeString(),
            ],
            [
                'id' => 'q-mtk-5',
                'question' => 'Sebuah mobil menempuh jarak 180 km dalam waktu 3 jam. Berapakah kecepatan rata-rata mobil tersebut?',
                'options' => [
                    'A. 50 km/jam',
                    'B. 60 km/jam',
                    'C. 70 km/jam',
                    'D. 80 km/jam',
                ],
                'correct_answer' => 'B. 60 km/jam',
                'explanation' => 'Kecepatan = Jarak / Waktu = 180 km / 3 jam = 60 km/jam.',
                'created_at' => now()->toDateTimeString(),
            ],
        ];

        DB::table('packages')->updateOrInsert(
            ['title' => 'PR Matematika Pekan 43 - Aljabar & Aritmatika Sosial'],
            [
                'description' => 'Tugas PR Matematika Pekan 43: Menyelesaikan masalah aljabar, perbandingan, dan aritmatika sosial.',
                'kelas' => null,
                'bidang' => 'MTK',
                'level' => 'Berbakat A',
                'start_date' => now()->subDays(7)->format('Y-m-d'),
                'end_date' => now()->addMonths(3)->format('Y-m-d'),
                'start_time' => null,
                'end_time' => null,
                'show_answer_key' => true,
                'show_explanation' => true,
                'show_score' => true,
                'is_active' => true,
                'questions' => json_encode($questionsMtk),
                'cards' => json_encode([]),
                'reviews' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 3. Buat / Update Paket PR IPA Pekan 43
        $questionsIpa = [
            [
                'id' => 'q-ipa-1',
                'question' => 'Energi yang dimiliki oleh suatu benda karena kedudukan atau posisinya di atas permukaan bumi disebut energi...',
                'options' => [
                    'A. Kinetik',
                    'B. Potensial gravitasi',
                    'C. Kalor',
                    'D. Kimia',
                ],
                'correct_answer' => 'B. Potensial gravitasi',
                'explanation' => 'Energi potensial gravitasi (Ep = m x g x h) adalah energi yang tersimpan akibat posisi ketinggian benda terhadap acuan bumi.',
                'created_at' => now()->toDateTimeString(),
            ],
            [
                'id' => 'q-ipa-2',
                'question' => 'Organel sel yang berfungsi sebagai tempat respirasi sel dan menghasilkan energi utama (ATP) adalah...',
                'options' => [
                    'A. Ribosom',
                    'B. Badan Golgi',
                    'C. Mitokondria',
                    'D. Vakuola',
                ],
                'correct_answer' => 'C. Mitokondria',
                'explanation' => 'Mitokondria sering disebut sebagai "the powerhouse of cell" karena bertindak sebagai tempat respirasi aerob dan pembentukan ATP.',
                'created_at' => now()->toDateTimeString(),
            ],
            [
                'id' => 'q-ipa-3',
                'question' => 'Perubahan wujud zat dari gas langsung menjadi padat tanpa melalui fase cair disebut...',
                'options' => [
                    'A. Mengembun',
                    'B. Menyublim / Mengkristal',
                    'C. Menguap',
                    'D. Membeku',
                ],
                'correct_answer' => 'B. Menyublim / Mengkristal',
                'explanation' => 'Perubahan wujud dari gas langsung ke padat disebut mengkristal / deposition (sering juga dikelompokkan ke desublimasi).',
                'created_at' => now()->toDateTimeString(),
            ],
            [
                'id' => 'q-ipa-4',
                'question' => 'Interaksi antara lebah yang mengisap nektar bunga dengan bunga yang terbantu proses penyerbukannya merupakan contoh simbiosis...',
                'options' => [
                    'A. Komensalisme',
                    'B. Parasitisme',
                    'C. Mutualisme',
                    'D. Netralisme',
                ],
                'correct_answer' => 'C. Mutualisme',
                'explanation' => 'Kedua pihak saling menguntungkan: lebah mendapat makanan nektar, sedangkan bunga terbantu penyerbukannya.',
                'created_at' => now()->toDateTimeString(),
            ],
            [
                'id' => 'q-ipa-5',
                'question' => 'Sebuah benda bermassa 2 kg bergerak dengan kecepatan 6 m/s. Berapakah energi kinetik benda tersebut?',
                'options' => [
                    'A. 18 Joule',
                    'B. 24 Joule',
                    'C. 36 Joule',
                    'D. 72 Joule',
                ],
                'correct_answer' => 'C. 36 Joule',
                'explanation' => 'Ek = 1/2 x m x v² = 1/2 x 2 x 6² = 36 Joule.',
                'created_at' => now()->toDateTimeString(),
            ],
        ];

        DB::table('packages')->updateOrInsert(
            ['title' => 'PR IPA Pekan 43 - Energi & Dinamika Lingkungan'],
            [
                'description' => 'Tugas PR IPA Pekan 43: Menguji konsep energi, struktur sel biologi, dan interaksi ekosistem.',
                'kelas' => null,
                'bidang' => 'IPA',
                'level' => 'Berbakat A',
                'start_date' => now()->subDays(7)->format('Y-m-d'),
                'end_date' => now()->addMonths(3)->format('Y-m-d'),
                'start_time' => null,
                'end_time' => null,
                'show_answer_key' => true,
                'show_explanation' => true,
                'show_score' => true,
                'is_active' => true,
                'questions' => json_encode($questionsIpa),
                'cards' => json_encode([]),
                'reviews' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 4. Buat / Update Paket PR Umum Terbuka untuk Semua Siswa
        DB::table('packages')->updateOrInsert(
            ['title' => 'PR Penalaran Terpadu - Pekan 43'],
            [
                'description' => 'Paket PR Terbuka untuk seluruh siswa dan level kelas. Kerjakan seluruh soal dengan teliti!',
                'kelas' => null,
                'bidang' => null,
                'level' => null,
                'start_date' => now()->subDays(7)->format('Y-m-d'),
                'end_date' => now()->addMonths(3)->format('Y-m-d'),
                'start_time' => null,
                'end_time' => null,
                'show_answer_key' => true,
                'show_explanation' => true,
                'show_score' => true,
                'is_active' => true,
                'questions' => json_encode(array_merge($questionsMtk, $questionsIpa)),
                'cards' => json_encode([]),
                'reviews' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('packages')->whereIn('title', [
            'PR Matematika Pekan 43 - Aljabar & Aritmatika Sosial',
            'PR IPA Pekan 43 - Energi & Dinamika Lingkungan',
            'PR Penalaran Terpadu - Pekan 43',
        ])->delete();
    }
};
