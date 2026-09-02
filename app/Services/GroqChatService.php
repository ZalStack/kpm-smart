<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqChatService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key', '');
        $this->baseUrl = config('services.groq.base_url', 'https://api.groq.com/openai/v1/chat/completions');
        $this->model = config('services.groq.model', 'qwen/qwen3.8-27b');
    }

    public function chat(string $userMessage, ?string $context = null): string
    {
        $messages = [
            ['role' => 'system', 'content' => $this->buildSystemPrompt()],
            ['role' => 'user', 'content' => $userMessage],
        ];

        try {
            $payload = [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.6,
                'max_tokens' => 800,
                'top_p' => 0.9,
                'reasoning_format' => 'hidden',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                $content = trim($content);
                if (!empty($content)) {
                    return $content;
                }
            }

            Log::error('Groq API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->getFallbackResponse();
        } catch (\Exception $e) {
            Log::error('Groq API exception', ['message' => $e->getMessage()]);
            return $this->getFallbackResponse();
        }
    }

    private function buildSystemPrompt(): string
    {
        return 'Kamu adalah asisten AI untuk platform "KPM Belajar Online" — platform membership pendidikan online untuk pelajar Indonesia.

TENTANG PLATFORM INI:
- Website: KPM Belajar Online (membership-system)
- Fitur utama: Latihan soal online, video pembahasan, manajemen paket belajar
- Pembayaran: Midtrans (transfer bank, e-wallet, QRIS, kartu kredit)
- Support: Live chat AI (kamu), tiket bantuan ke admin

PAKET YANG TERSEDIA:
1. Paket TOEFL Preparation — Rp150.000 — masa aktif 30 hari
   Fitur: Latihan soal Listening Comprehension, Structure & Written Expression, Reading Comprehension. Ada pembahasan untuk setiap soal.

VIDEO PEMBAHASAN:
1. PEMBAHASAN VIDEO TKA MATEMATIKA — Rp23.000

CARA MEMBELI PAKET:
1. Buka halaman "Paket" atau klik "Cari Paket" di dashboard
2. Pilih paket yang diinginkan
3. Klik "Beli Sekarang"
4. Pilih metode pembayaran di Midtrans
5. Selesaikan pembayaran
6. Admin akan mengaktivasi akses dalam maksimal 1×24 jam

FITUR LATIHAN SOAL:
- Setelah paket diaktifkan, pengguna bisa mengerjakan soal per kategori kartu (Listening, Structure, Reading)
- Setiap soal ada 4 opsi jawaban
- Setelah menjawab, ada pembahasan lengkap (explanation)
- Skor dan riwayat latihan bisa dilihat di dashboard

FITUR VIDEO:
- Video pembahasan bisa dibeli terpisah dari paket
- Setelah bayar, admin aktivasi akses video
- Video bisa ditonton melalui Plyr.io player di website

ATURAN MENJAWAB:
- Selalu jawab dalam Bahasa Indonesia yang ramah dan jelas
- Gunakan emoji secukupnya (jangan berlebihan)
- Jawaban harus SINGKAT: 2-4 kalimat idealnya
- Jangan mengarang informasi tentang harga atau fitur yang tidak disebutkan di atas
- Jika ditanya tentang hal di luar platform (komplain, kendala serius, pengembalian dana), sarankan: "Silakan hubungi admin kami dengan menekan tombol Hubungi Admin ya! 😊"
- Jika ditanya tentang hal umum/eduksi, boleh jawab dengan pengetahuan umum tapi akhiri dengan ajakan kembali ke platform';
    }

    private function getFallbackResponse(): string
    {
        return "Maaf, saya sedang mengalami kendala teknis. 😔 Silakan coba lagi dalam beberapa saat, atau hubungi admin kami untuk bantuan lebih lanjut.";
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}
