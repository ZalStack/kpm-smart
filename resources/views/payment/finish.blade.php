@php
    $isVideo = $order->isVideoOrder();
    $video = $isVideo ? $order->videoOrder?->video : null;
    $itemTitle = $order->item_title;
    $redirectUrl = $isVideo && $video ? route('videos.show', $video->id) : route('orders.index');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - KPM Belajar Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Poppins', sans-serif; }
        .confetti-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
            overflow: hidden;
        }
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            opacity: 0.8;
            animation: confetti-fall linear forwards;
        }
        @keyframes confetti-fall {
            0% { transform: translateY(-10vh) rotate(0deg); opacity: 1; }
            100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
        }
        .success-checkmark {
            animation: checkmark 0.6s ease-in-out;
        }
        @keyframes checkmark {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        @media (max-width: 640px) {
            .confetti { width: 6px; height: 6px; font-size: 10px !important; }
        }
    </style>
</head>
<body class="bg-muted min-h-screen flex items-center justify-center p-3 md:p-4">
    <div id="confetti-container" class="confetti-container"></div>

    <div class="w-full max-w-md">
        <div class="bg-card rounded-lg md:rounded-lg shadow-xl p-5 md:p-10 text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-green-400 via-emerald-400 to-green-500"></div>

            <div class="success-checkmark w-20 h-20 md:w-24 md:h-24 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 md:mb-6 shadow-lg shadow-green-200">
                <svg class="w-10 h-10 md:w-12 md:h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-xl md:text-2xl font-extrabold text-foreground">Pembayaran Berhasil! 🎉</h1>
            <p class="text-muted-foreground mt-1 text-sm md:text-base">Terima kasih telah melakukan pembayaran</p>

            <div class="mt-4 md:mt-6 bg-muted rounded-lg p-4 md:p-5 text-left">
                <div class="flex items-center gap-3 pb-3 border-b border-border">
                    <div class="w-8 h-8 md:w-10 md:h-10 rounded-md flex items-center justify-center flex-shrink-0 {{ $isVideo ? 'bg-gradient-to-br from-pink-50 to-purple-50' : 'bg-gradient-to-br from-blue-50 to-indigo-50' }}">
                        <span class="text-base md:text-lg">{{ $isVideo ? '🎬' : '📚' }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-foreground text-sm md:text-base truncate">{{ $itemTitle }}</p>
                        <p class="text-[10px] md:text-xs text-muted-foreground">{{ $isVideo ? 'Video Pembahasan' : 'Paket Bank Soal' }}</p>
                    </div>
                </div>
                <div class="space-y-1.5 md:space-y-2 pt-2 md:pt-3">
                    <div class="flex justify-between text-xs md:text-sm">
                        <span class="text-muted-foreground">Total Pembayaran</span>
                        <span class="font-bold text-success-500">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs md:text-sm">
                        <span class="text-muted-foreground">No. Pesanan</span>
                        <span class="font-mono text-[10px] md:text-xs text-foreground break-all">{{ $order->order_number }}</span>
                    </div>
                    <div class="flex justify-between text-xs md:text-sm pt-1.5 md:pt-2 border-t border-border">
                        <span class="text-muted-foreground">Status</span>
                        <span class="font-medium text-success-500 flex items-center">
                            <span class="w-1.5 h-1.5 bg-success-500 rounded-full mr-1.5"></span>
                            Lunas
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-4 md:mt-6 p-3 md:p-4 rounded-lg text-left {{ $isVideo ? 'bg-pink-50' : 'bg-primary/10' }}">
                <p class="text-xs md:text-sm font-semibold {{ $isVideo ? 'text-pink-800' : 'text-primary' }}">📌 Langkah Selanjutnya</p>
                <div class="mt-1.5 md:mt-2 space-y-1.5 md:space-y-2">
                    @if ($isVideo)
                        <div class="flex items-start gap-2 md:gap-3 text-xs md:text-sm">
                            <span class="{{ $isVideo ? 'text-pink-500' : 'text-primary' }} font-bold flex-shrink-0">1.</span>
                            <span class="{{ $isVideo ? 'text-pink-700' : 'text-primary' }}">Admin akan mengaktifkan akses video Anda</span>
                        </div>
                        <div class="flex items-start gap-2 md:gap-3 text-xs md:text-sm">
                            <span class="{{ $isVideo ? 'text-pink-500' : 'text-primary' }} font-bold flex-shrink-0">2.</span>
                            <span class="{{ $isVideo ? 'text-pink-700' : 'text-primary' }}">Buka menu <strong>Video Pembahasan</strong> untuk menonton</span>
                        </div>
                        <div class="flex items-start gap-2 md:gap-3 text-xs md:text-sm">
                            <span class="{{ $isVideo ? 'text-pink-500' : 'text-primary' }} font-bold flex-shrink-0">3.</span>
                            <span class="{{ $isVideo ? 'text-pink-700' : 'text-primary' }}">Akses aktif selama {{ $video->access_duration_days ?? 30 }} hari</span>
                        </div>
                    @else
                        <div class="flex items-start gap-2 md:gap-3 text-xs md:text-sm">
                            <span class="text-primary font-bold flex-shrink-0">1.</span>
                            <span class="text-primary">Admin akan mengirimkan <strong>Enroll Key</strong></span>
                        </div>
                        <div class="flex items-start gap-2 md:gap-3 text-xs md:text-sm">
                            <span class="text-primary font-bold flex-shrink-0">2.</span>
                            <span class="text-primary">Masukkan Enroll Key untuk mengaktifkan paket</span>
                        </div>
                        <div class="flex items-start gap-2 md:gap-3 text-xs md:text-sm">
                            <span class="text-primary font-bold flex-shrink-0">3.</span>
                            <span class="text-primary">Mulai belajar dengan soal-soal berkualitas</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-6 md:mt-8 flex flex-col sm:flex-row gap-2 md:gap-3">
                <a href="{{ $redirectUrl }}" class="flex-1 bg-navy-light text-white py-3 md:py-3.5 px-4 md:px-6 rounded-md font-semibold hover:bg-navy hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 text-center text-sm md:text-base">{{ $isVideo ? '🎬 Tonton Video' : '📦 Lihat Pesanan' }}</a>
                <a href="{{ route('orders.index') }}" class="flex-1 bg-muted text-foreground py-3 md:py-3.5 px-4 md:px-6 rounded-md font-semibold hover:bg-muted transition-all duration-200 text-center text-sm md:text-base">{{ $isVideo ? '📦 Lihat Pesanan' : '📚 Lanjut Belajar' }}</a>
            </div>

            <div class="mt-3 md:mt-4 text-xs md:text-sm text-muted-foreground">
                Mengalihkan dalam <span id="countdown" class="font-bold text-foreground">5</span> detik
            </div>

            <div class="mt-4 md:mt-6 pt-3 md:pt-4 border-t border-border text-center">
                <p class="text-[10px] md:text-xs text-muted-foreground">© {{ date('Y') }} KPM Belajar Online</p>
            </div>
        </div>
    </div>

    <script>
        function createConfetti() {
            const container = document.getElementById('confetti-container');
            const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#FF69B4', '#00CED1', '#FFD93D', '#6BCB77'];
            const shapes = ['■', '●', '▲', '★', '♦', '♥'];

            for (let i = 0; i < (window.innerWidth < 640 ? 60 : 100); i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.textContent = shapes[Math.floor(Math.random() * shapes.length)];
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.color = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.fontSize = (Math.random() * (window.innerWidth < 640 ? 14 : 18) + 6) + 'px';
                confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
                confetti.style.animationDelay = (Math.random() * 2) + 's';
                container.appendChild(confetti);
            }
            setTimeout(() => { container.innerHTML = ''; }, 5000);
        }

        createConfetti();

        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        const timer = setInterval(function() {
            countdown--;
            countdownElement.textContent = countdown;
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = '{{ $redirectUrl }}';
            }
        }, 1000);
    </script>
</body>
</html>
