<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Buat Password Baru - KPM Belajar Online</title>
    <meta name="description" content="Buat kata sandi baru untuk akun KPM Belajar Online kamu.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{
            --navy:#0E1447;
            --navy-deep:#090C2E;
            --blue:#27438D;
            --cyan:#00A2E9;
            --gold:#FCC626;
            --green:#009A4B;
            --ink:#1B1E34;
            --muted:#64748B;
            --line:#E5EAF4;
            --ring: rgba(0,162,233,.16);
        }
        *{ font-family:'Poppins', sans-serif; }
        .font-display{ font-family:'Sora', sans-serif; }
        html, body{ height:100%; }
        body{ background:#F6F8FC; }

        @media (prefers-reduced-motion: no-preference){
            @keyframes blob{ 0%,100%{ transform:translate(0,0) scale(1) } 33%{ transform:translate(20px,-14px) scale(1.06) } 66%{ transform:translate(-16px,12px) scale(.96) } }
            @keyframes fadeUp{ from{ opacity:0; transform:translateY(16px) } to{ opacity:1; transform:translateY(0) } }
            @keyframes ringPulse{ 0%,100%{ box-shadow:0 0 0 0 rgba(252,198,38,.4) } 50%{ box-shadow:0 0 0 10px rgba(252,198,38,0) } }
            @keyframes sheen{ 0%{ transform:translateX(-130%) skewX(-12deg) } 60%,100%{ transform:translateX(260%) skewX(-12deg) } }
            .anim-blob{ animation: blob 12s ease-in-out infinite; }
            .fade-up{ opacity:0; animation: fadeUp .7s cubic-bezier(.16,1,.3,1) forwards; }
            .ring-pulse{ animation: ringPulse 2.6s ease-in-out infinite; }
            .btn-shine::after{ content:''; position:absolute; inset:0; background:linear-gradient(110deg, transparent 32%, rgba(255,255,255,.26) 50%, transparent 68%); animation: sheen 4.5s ease-in-out infinite; pointer-events:none; }
        }
        @media (prefers-reduced-motion: reduce){ .fade-up{ opacity:1; } }

        .brand-panel{
            background:
                radial-gradient(95% 65% at 88% 8%, rgba(0,162,233,.34), transparent 58%),
                radial-gradient(70% 55% at 0% 92%, rgba(252,198,38,.16), transparent 60%),
                radial-gradient(60% 50% at 65% 100%, rgba(0,154,75,.14), transparent 62%),
                linear-gradient(150deg, var(--navy-deep) 0%, var(--navy) 46%, #1B2E74 100%);
        }
        .grid-dots{ background-image: radial-gradient(rgba(255,255,255,.13) 1px, transparent 1px); background-size: 22px 22px; }
        .text-gold-grad{ background:linear-gradient(92deg,#FCC626 0%,#FFD877 60%,#FFB020 100%); -webkit-background-clip:text; background-clip:text; color:transparent; }

        .right-bg{
            background:
                radial-gradient(45% 40% at 8% 0%, rgba(0,162,233,.07), transparent 60%),
                radial-gradient(42% 38% at 100% 92%, rgba(252,198,38,.08), transparent 60%),
                #F6F8FC;
        }

        .auth-card{
            background:rgba(255,255,255,.9);
            -webkit-backdrop-filter:blur(14px);
            backdrop-filter:blur(14px);
            border:1px solid rgba(229,234,244,.95);
            box-shadow:0 24px 60px -24px rgba(14,20,71,.18);
        }

        .field{ position:relative; }
        .field input{
            width:100%;
            padding: 1.3rem 1rem .6rem 3.6rem;
            background:#F7F9FE;
            border:1.5px solid var(--line);
            border-radius:1rem;
            font-size:.95rem;
            color:var(--ink);
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .field input:focus{
            outline:none;
            border-color:var(--cyan);
            box-shadow:0 0 0 4px var(--ring);
            background:#fff;
        }
        .field label{
            position:absolute;
            left:3.6rem;
            top:1.02rem;
            font-size:.94rem;
            color:var(--muted);
            pointer-events:none;
            transition: all .18s ease;
            background:transparent;
        }
        .field input:focus + label,
        .field input:not(:placeholder-shown) + label{
            top:.4rem;
            left:3.6rem;
            font-size:.66rem;
            font-weight:600;
            color:var(--blue);
            letter-spacing:.02em;
        }
        .field .icon-wrap{
            position:absolute; left:.7rem; top:50%; transform:translateY(-50%);
            width:2.25rem; height:2.25rem; border-radius:.75rem;
            background:#EDF1FA; color:#8A93AD;
            display:flex; align-items:center; justify-content:center;
            transition: all .2s ease; pointer-events:none;
        }
        .field input:focus ~ .icon-wrap{ background:rgba(0,162,233,.12); color:var(--cyan); }

        .field.has-error input{ border-color:#F87171; }
        .field.has-error input:focus{ box-shadow:0 0 0 4px rgba(248,113,113,.15); }
        .field.has-error .icon-wrap{ background:#FEF2F2; color:#F87171; }
        .field.has-error input:focus ~ .icon-wrap{ background:rgba(248,113,113,.14); color:#EF4444; }

        .field-hint{ min-height:1rem; }

        .btn-primary{
            position:relative; overflow:hidden;
            background: linear-gradient(135deg, var(--blue), var(--navy));
            box-shadow:0 10px 24px -10px rgba(14,20,71,.55);
            transition: transform .2s ease, box-shadow .25s ease;
        }
        .btn-primary:hover{ transform: translateY(-2px); box-shadow: 0 16px 32px -12px rgba(0,162,233,.55); }
        .btn-primary:active{ transform: translateY(0); }
        .btn-primary[disabled]{ opacity:.75; cursor:progress; transform:none; box-shadow:none; }

        .spinner{ width:18px; height:18px; border-radius:999px; border:2.5px solid rgba(255,255,255,.35); border-top-color:#fff; animation: spin .7s linear infinite; }
        @keyframes spin{ to{ transform: rotate(360deg); } }

        .step-dot{ width:.5rem; height:.5rem; border-radius:999px; background:var(--line); transition: all .25s ease; }
        .step-dot.active{ width:1.5rem; background: var(--cyan); }
        .step-dot.done{ background: var(--green); }

        /* Password strength meter */
        .strength-bar{ height:4px; border-radius:999px; background:#E2E8F0; overflow:hidden; }
        .strength-fill{ height:100%; width:0%; border-radius:999px; transition: width .25s ease, background .25s ease; }
    </style>
</head>
<body class="min-h-screen">

    <div class="grid lg:grid-cols-2 min-h-screen">

        <!-- LEFT: Brand panel -->
        <div class="hidden lg:flex brand-panel relative overflow-hidden text-white flex-col justify-between p-12 xl:p-16">
            <div class="grid-dots absolute inset-0 opacity-70"></div>
            <div class="absolute w-96 h-96 rounded-full bg-[var(--cyan)] opacity-20 -top-24 -right-24 anim-blob blur-3xl"></div>
            <div class="absolute w-80 h-80 rounded-full bg-[var(--gold)] opacity-15 -bottom-16 -left-16 anim-blob blur-3xl" style="animation-delay:-4s"></div>
            <div class="absolute w-56 h-56 rounded-full bg-[var(--green)] opacity-15 top-1/2 -left-20 anim-blob blur-3xl" style="animation-delay:-8s"></div>

            <div class="relative z-10 fade-up max-w-lg">
                <div class="flex items-center gap-3 mb-12">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[var(--gold)] to-[#f5a623] flex items-center justify-center text-[var(--navy-deep)] font-display font-bold text-xl shadow-lg ring-pulse">K</div>
                    <div class="leading-tight">
                        <span class="font-display font-bold text-lg block">KPM Belajar Online</span>
                        <span class="text-xs text-white/60">Platform belajar & bank soal</span>
                    </div>
                </div>

                <h1 class="font-display text-[2.4rem] xl:text-[2.9rem] font-bold leading-[1.15] mb-5">
                    Hampir selesai,<br>
                    <span class="text-gold-grad">satu langkah</span><br>
                    lagi menuju akun aman.
                </h1>
                <p class="text-white/65 text-base leading-relaxed max-w-sm">
                    Buat kata sandi baru yang kuat dan mudah kamu ingat. Setelah ini kamu bisa langsung masuk kembali.
                </p>
            </div>

            <div class="relative z-10 text-xs text-white/40 fade-up" style="animation-delay:.15s">
                © {{ date('Y') }} KPM Belajar Online. Hak cipta dilindungi.
            </div>
        </div>

        <!-- RIGHT: Form panel -->
        <div class="right-bg relative overflow-hidden flex items-center justify-center p-5 sm:p-10 lg:p-16">
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-[var(--cyan)] opacity-[.08] blur-3xl"></div>
            <div class="absolute -bottom-28 -left-24 w-80 h-80 rounded-full bg-[var(--gold)] opacity-[.09] blur-3xl"></div>

            <div class="relative z-10 w-full max-w-md fade-up" style="animation-delay:.05s">

                <!-- Mobile brand -->
                <div class="lg:hidden flex items-center justify-center gap-3 mb-6">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[var(--gold)] to-[#f5a623] flex items-center justify-center text-[var(--navy-deep)] font-display font-bold text-lg">K</div>
                    <div class="leading-tight text-left">
                        <span class="font-display font-bold text-[var(--navy)] block">KPM Belajar Online</span>
                        <span class="text-[11px] text-muted-foreground">Platform belajar & bank soal</span>
                    </div>
                </div>

                <!-- Auth card -->
                <div class="auth-card rounded-[1.75rem] p-5 sm:p-9">

                    <!-- Step indicator -->
                    <div class="hidden sm:flex items-center gap-2 mb-6" aria-hidden="true">
                        <span class="step-dot done"></span>
                        <span class="step-dot active"></span>
                        <span class="text-xs text-[var(--muted)] ml-2">Langkah 2 dari 2 · Password Baru</span>
                    </div>

                    <div class="mb-7">
                        <h1 class="font-display text-xl sm:text-[1.8rem] font-bold text-[var(--navy)]">Buat Password Baru</h1>
                        <p class="text-[var(--muted)] text-sm mt-1.5">Tautan terverifikasi! Sekarang buat kata sandi baru untuk akunmu.</p>
                    </div>

                    @if($errors->any())
                        <div class="bg-danger-50 border border-danger-100 border-l-4 border-l-danger-400 text-danger-700 px-4 py-3 rounded-xl mb-6 text-sm" role="alert">
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4.5 h-4.5 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                <ul class="space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('password.reset.submit') }}" method="POST" class="space-y-5" id="resetForm" novalidate>
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
                            <span class="icon-wrap">
                                <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input type="password" name="password" id="password" required placeholder=" " autocomplete="new-password" minlength="8" class="pr-12">
                            <label for="password">Password Baru</label>
                            <button type="button" class="eye-btn absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-[var(--blue)] transition p-1" data-toggle-pwd="password" aria-label="Tampilkan kata sandi" aria-pressed="false">
                                <svg data-icon-open viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg data-icon-closed class="hidden" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.6 21.6 0 0 1 5.06-6.06M9.9 4.24A10.6 10.6 0 0 1 12 4c7 0 11 8 11 8a21.5 21.5 0 0 1-2.61 3.94M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>
                            </button>
                            <p class="field-hint text-xs text-danger-600 mt-1">@error('password'){{ $message }}@enderror</p>
                        </div>

                        <!-- Indikator kekuatan password -->
                        <div class="-mt-3">
                            <div class="strength-bar">
                                <div id="strengthFill" class="strength-fill"></div>
                            </div>
                            <p id="strengthText" class="text-xs text-muted-foreground mt-1.5">Minimal 8 karakter</p>
                        </div>

                        <div class="field">
                            <span class="icon-wrap">
                                <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><path d="m9 15 2 2 4-4"/></svg>
                            </span>
                            <input type="password" name="password_confirmation" id="password_confirmation" required placeholder=" " autocomplete="new-password" minlength="8" class="pr-12">
                            <label for="password_confirmation">Konfirmasi Password Baru</label>
                            <button type="button" class="eye-btn absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-[var(--blue)] transition p-1" data-toggle-pwd="password_confirmation" aria-label="Tampilkan kata sandi" aria-pressed="false">
                                <svg data-icon-open viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg data-icon-closed class="hidden" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.6 21.6 0 0 1 5.06-6.06M9.9 4.24A10.6 10.6 0 0 1 12 4c7 0 11 8 11 8a21.5 21.5 0 0 1-2.61 3.94M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>
                            </button>
                            <p id="matchHint" class="field-hint text-xs mt-1"></p>
                        </div>

                        <button type="submit" id="submitBtn"
                                class="btn-primary btn-shine w-full py-3.5 text-[15px] font-semibold text-white rounded-xl flex items-center justify-center gap-2.5">
                            <span id="btnLabel">Simpan Password Baru</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            // Toggle show/hide password
            document.querySelectorAll('.eye-btn').forEach(btn => {
                btn.addEventListener('click', function(){
                    const pwd = document.getElementById(btn.dataset.togglePwd);
                    if(!pwd) return;
                    const show = pwd.getAttribute('type') === 'password';
                    pwd.setAttribute('type', show ? 'text' : 'password');
                    btn.setAttribute('aria-pressed', String(show));
                    btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                    btn.querySelector('[data-icon-open]').classList.toggle('hidden', show);
                    btn.querySelector('[data-icon-closed]').classList.toggle('hidden', !show);
                });
            });

            // Password strength meter
            const pwdInput = document.getElementById('password');
            const fill = document.getElementById('strengthFill');
            const text = document.getElementById('strengthText');
            const levels = [
                { min: 0,  w: '0%',   c: '#E2E8F0', t: 'Minimal 8 karakter' },
                { min: 1,  w: '25%',  c: '#F87171', t: 'Lemah' },
                { min: 2,  w: '55%',  c: '#FCC626', t: 'Sedang' },
                { min: 3,  w: '80%',  c: '#00A2E9', t: 'Kuat' },
                { min: 4,  w: '100%', c: '#009A4B', t: 'Sangat kuat' },
            ];
            pwdInput.addEventListener('input', function(){
                const v = pwdInput.value;
                let score = 0;
                if(v.length >= 8) score++;
                if(/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
                if(/\d/.test(v)) score++;
                if(/[^A-Za-z0-9]/.test(v)) score++;
                const lv = levels[v.length ? score : 0];
                fill.style.width = lv.w;
                fill.style.background = lv.c;
                text.textContent = v.length ? lv.t : 'Minimal 8 karakter';
                text.style.color = v.length ? lv.c : '#9CA3AF';
            });

            // Real-time match check
            const confirmInput = document.getElementById('password_confirmation');
            const matchHint = document.getElementById('matchHint');
            function checkMatch(){
                if(!confirmInput.value){ matchHint.textContent = ''; return; }
                if(confirmInput.value === pwdInput.value){
                    matchHint.textContent = 'Password cocok ✓';
                    matchHint.className = 'field-hint text-xs mt-1 text-[var(--green)]';
                } else {
                    matchHint.textContent = 'Password belum sama';
                    matchHint.className = 'field-hint text-xs mt-1 text-danger-500';
                }
            }
            confirmInput.addEventListener('input', checkMatch);
            pwdInput.addEventListener('input', checkMatch);

            // Submit handling
            const form = document.getElementById('resetForm');
            const btn = document.getElementById('submitBtn');
            const label = document.getElementById('btnLabel');
            form.addEventListener('submit', function(e){
                if(!form.checkValidity()){ return; }
                if(pwdInput.value !== confirmInput.value){
                    e.preventDefault();
                    checkMatch();
                    return;
                }
                btn.setAttribute('disabled', 'true');
                label.textContent = 'Menyimpan…';
                const spinner = document.createElement('span');
                spinner.className = 'spinner';
                btn.appendChild(spinner);
            });
        })();
    </script>
</body>
</html>
