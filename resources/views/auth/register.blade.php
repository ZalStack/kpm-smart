<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar - KPM Belajar Online</title>
    <meta name="description" content="Buat akun KPM Belajar Online dan mulai akses ribuan soal dengan pembahasan lengkap.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *{ font-family:'Poppins', sans-serif; }
        .font-display{ font-family:'Sora', sans-serif; }
        html, body{ height:100%; }
        body{ background:hsl(var(--background)); }

        @media (prefers-reduced-motion: no-preference){
            @keyframes floatSlow{ 0%,100%{ transform:translateY(0) } 50%{ transform:translateY(-14px) } }
            @keyframes blob{ 0%,100%{ transform:translate(0,0) scale(1) } 33%{ transform:translate(20px,-14px) scale(1.06) } 66%{ transform:translate(-16px,12px) scale(.96) } }
            @keyframes fadeUp{ from{ opacity:0; transform:translateY(16px) } to{ opacity:1; transform:translateY(0) } }
            @keyframes stepIn{ from{ opacity:0; transform:translateX(14px) } to{ opacity:1; transform:translateX(0) } }
            .anim-blob{ animation: blob 12s ease-in-out infinite; }
            .fade-up{ opacity:0; animation: fadeUp .7s cubic-bezier(.16,1,.3,1) forwards; }
            .step-panel.active{ animation: stepIn .35s cubic-bezier(.16,1,.3,1); }
        }
        @media (prefers-reduced-motion: reduce){ .fade-up{ opacity:1; } }

        .field input[type="password"], .field input.pr-12{ padding-right:3.2rem; }

        .eye-btn{
            position:absolute; right:.75rem; top:1.15rem;
            color:hsl(var(--muted-foreground)); transition: color .2s ease;
            padding:.25rem; border-radius:.5rem;
        }
        .eye-btn:hover{ color:hsl(var(--foreground)); }

        .btn-ghost{ transition: all .2s ease; }
        .btn-ghost:hover{ background:hsl(var(--muted)); border-color:hsl(var(--border)); color:hsl(var(--foreground)); }

        .spinner{ width:18px; height:18px; border-radius:999px; border:2.5px solid rgba(255,255,255,.35); border-top-color:#fff; animation: spin .7s linear infinite; }
        @keyframes spin{ to{ transform: rotate(360deg); } }

        /* Stepper */
        .step-dot{
            width:38px; height:38px; border-radius:999px; display:flex; align-items:center; justify-content:center;
            font-family:'Sora',sans-serif; font-weight:700; font-size:.85rem;
            background:hsl(var(--muted)); color:hsl(var(--muted-foreground)); border:2px solid transparent; transition: all .25s ease;
        }
        .step-dot.current, .step-dot.done{ flex-shrink:0; }
        .step-dot.done{ background:linear-gradient(135deg, #009A4B, #00B264); color:#fff; box-shadow:0 6px 16px -6px rgba(0,154,75,.55); }
        .step-dot.current{ background:hsl(var(--background)); color:hsl(var(--foreground)); border-color:hsl(var(--primary)); box-shadow:0 0 0 4px hsl(var(--primary)/.16); }
        .step-line{ flex:1; height:2px; background:hsl(var(--border)); position:relative; overflow:hidden; border-radius:999px; }
        .step-line::after{ content:''; position:absolute; inset:0; background:linear-gradient(90deg, #009A4B, #00B264); width:0%; transition: width .35s ease; }
        .step-line.filled::after{ width:100%; }

        .step-panel{ display:none; }
        .step-panel.active{ display:block; }

        select{ appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236B7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position: right .9rem center; padding-right:2.5rem; }

        .strength-bar{ height:5px; border-radius:999px; background:hsl(var(--border)); overflow:hidden; margin-top:.65rem; }
        .strength-bar > span{ display:block; height:100%; width:0%; transition: width .25s ease, background .25s ease; }
    </style>
</head>
<body class="min-h-screen">

    <div class="grid lg:grid-cols-2 min-h-screen">

        <!-- LEFT: Brand panel (full-bleed, sticky) -->
        <div class="hidden lg:flex brand-panel relative overflow-hidden text-white flex-col justify-between p-12 xl:p-16 lg:sticky lg:top-0 lg:h-screen">
            <div class="grid-dots absolute inset-0 opacity-70"></div>
            <div class="absolute w-96 h-96 rounded-full bg-[var(--cyan)] opacity-20 -top-24 -right-24 anim-blob blur-3xl"></div>
            <div class="absolute w-80 h-80 rounded-full bg-[var(--gold)] opacity-15 -bottom-16 -left-16 anim-blob blur-3xl" style="animation-delay:-4s"></div>
            <div class="absolute w-56 h-56 rounded-full bg-[var(--green)] opacity-15 top-1/2 -left-20 anim-blob blur-3xl" style="animation-delay:-8s"></div>

            <div class="relative z-10 fade-up max-w-lg">
                <div class="flex items-center gap-3 mb-12">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-gold-400 to-gold-500 flex items-center justify-center text-navy-deep font-display font-extrabold text-xl shadow-lg">K</div>
                    <div class="leading-tight">
                        <span class="font-display font-bold text-lg block">KPM Belajar Online</span>
                        <span class="text-xs text-white/60">Platform belajar & bank soal</span>
                    </div>
                </div>

                <h1 class="font-display text-[2.4rem] xl:text-[2.7rem] font-bold leading-[1.15] mb-5">
                    Tiga langkah<br>
                    menuju <span class="text-gold-grad">akun baru</span><br>
                    milikmu.
                </h1>
                <p class="text-white/65 text-base leading-relaxed max-w-sm">
                    Isi datamu bertahap — cepat, jelas, dan bisa kamu koreksi kapan saja sebelum kirim.
                </p>
            </div>

            <!-- Signature element: mirrored step preview -->
            <div class="relative z-10 space-y-3 max-w-sm" aria-hidden="true">
                <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-3.5 border border-white/10 transition" id="brandStep1">
                    <span class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center text-sm font-display font-bold">1</span>
                    <div>
                        <p class="font-semibold text-sm">Data Akun</p>
                        <p class="text-white/55 text-xs">Foto, nama, email &amp; sandi</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-3.5 border border-white/10 transition" id="brandStep2">
                    <span class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center text-sm font-display font-bold">2</span>
                    <div>
                        <p class="font-semibold text-sm">Siswa & Sekolah</p>
                        <p class="text-white/55 text-xs">Kelas, jurusan, asal sekolah</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-3.5 border border-white/10 transition" id="brandStep3">
                    <span class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center text-sm font-display font-bold">3</span>
                    <div>
                        <p class="font-semibold text-sm">Info Tambahan</p>
                        <p class="text-white/55 text-xs">Opsional — bisa dilewati</p>
                    </div>
                </div>
            </div>

            <div class="relative z-10 text-xs text-white/40">© {{ date('Y') }} KPM Belajar Online. Hak cipta dilindungi.</div>
        </div>

        <!-- RIGHT: Wizard panel (full-bleed) -->
        <div class="right-bg relative overflow-hidden flex items-center justify-center p-5 sm:p-10 lg:p-16">
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-[var(--cyan)] opacity-[.08] blur-3xl"></div>
            <div class="absolute -bottom-28 -left-24 w-80 h-80 rounded-full bg-[var(--gold)] opacity-[.09] blur-3xl"></div>

            <div class="relative z-10 w-full max-w-2xl fade-up">

                <!-- Mobile brand -->
                <div class="lg:hidden flex items-center justify-center gap-3 mb-6 relative">
                    <a href="{{ url('/') }}" aria-label="Kembali ke beranda"
                       class="absolute left-0 top-1/2 -translate-y-1/2 w-10 h-10 rounded-xl bg-background border border-border text-foreground flex items-center justify-center shadow-sm hover:bg-muted transition text-lg">←</a>
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-gold-400 to-gold-500 flex items-center justify-center text-navy-deep font-display font-extrabold text-lg">K</div>
                        <span class="font-display font-bold text-foreground">KPM Belajar Online</span>
                    </div>
                </div>

                <!-- Auth card -->
                <div class="auth-card rounded-[1.75rem] p-5 sm:p-9">
                    <div class="mb-7 text-center sm:text-left">
                        <h1 class="font-display text-xl sm:text-[1.7rem] font-bold text-foreground">Buat akun baru</h1>
                        <p class="text-muted-foreground text-sm mt-1.5">Bergabung dan mulai belajar hari ini.</p>
                    </div>

                    @if($errors->any())
                        <div class="bg-destructive/10 border border-destructive/20 border-l-4 border-l-destructive text-destructive px-4 py-3 rounded-md mb-6 text-sm" role="alert">
                            <div class="flex items-start gap-2.5">
                                <svg class="flex-shrink-0 mt-0.5" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                <ul class="space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Stepper -->
                    <div class="flex items-center gap-2 mb-2" role="tablist" aria-label="Langkah pendaftaran">
                        <button type="button" class="step-dot current" data-goto="1" id="dot1" aria-current="step">1</button>
                        <div class="step-line" id="line1"></div>
                        <button type="button" class="step-dot" data-goto="2" id="dot2">2</button>
                        <div class="step-line" id="line2"></div>
                        <button type="button" class="step-dot" data-goto="3" id="dot3">3</button>
                    </div>
                    <div class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wide mb-6 px-0.5" id="stepLabel">
                        Langkah 1 dari 3 · Data Akun
                    </div>

                    <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" id="registerForm" novalidate>
                        @csrf

                        <!-- STEP 1: Akun -->
                        <div class="step-panel active" data-step="1">
                            <div class="grid grid-cols-1 sm:grid-cols-[auto_1fr] gap-5 sm:gap-6 mb-6 items-start">
                                <div class="flex flex-col items-center gap-2 mx-auto sm:mx-0">
                                    <div class="relative group">
                                        <img id="photoPreview"
                                             src="https://ui-avatars.com/api/?name=%3F&background=EEF0F7&color=9CA3AF&size=128"
                                             alt="Pratinjau foto profil"
                                             class="w-20 h-20 rounded-full object-cover border-4 border-[var(--gold)] shadow-md">
                                         <label for="profile_photo"
                                                class="absolute bottom-0 right-0 bg-gradient-to-br from-navy-light to-navy text-white w-8 h-8 rounded-full flex items-center justify-center cursor-pointer shadow-md hover:from-accent-400 hover:to-navy-light transition"
                                                title="Unggah foto">
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2Z"/><circle cx="12" cy="13" r="4"/></svg>
                                        </label>
                                        <input type="file" name="profile_photo" id="profile_photo" accept="image/*" class="hidden">
                                    </div>
                                    <p class="text-[11px] text-muted-foreground text-center">Maks. 2MB</p>
                                    @error('profile_photo')<p class="text-xs text-destructive">{{ $message }}</p>@enderror
                                </div>
                                <div class="flex items-center h-full text-sm text-muted-foreground bg-accent border border-border rounded-md p-3.5 self-center">
                                    Foto profil bersifat opsional. Lengkapi data akun di sebelah kanan, lalu lanjut ke langkah berikutnya.
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="field {{ $errors->has('name') ? 'has-error' : '' }}">
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder=" " autocomplete="name">
                                    <label for="name">Nama Lengkap *</label>
                                    <p class="hint-err text-xs text-destructive mt-1">@error('name'){{ $message }}@enderror</p>
                                </div>
                                <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
                                    <input type="email" name="email" id="reg_email" value="{{ old('email') }}" required placeholder=" " autocomplete="email" autocapitalize="none" spellcheck="false" inputmode="email">
                                    <label for="reg_email">Email *</label>
                                    <p class="hint-err text-xs text-destructive mt-1">@error('email'){{ $message }}@enderror</p>
                                </div>
                                <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
                                    <input type="password" name="password" id="reg_password" required placeholder=" " minlength="8" autocomplete="new-password">
                                    <label for="reg_password">Kata Sandi *</label>
                                    <button type="button" class="eye-btn" data-toggle-pwd="reg_password" aria-label="Tampilkan kata sandi">
                                        <svg data-icon-open viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg data-icon-closed class="hidden" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.6 21.6 0 0 1 5.06-6.06M9.9 4.24A10.6 10.6 0 0 1 12 4c7 0 11 8 11 8a21.5 21.5 0 0 1-2.61 3.94M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>
                                    </button>
                                    <div class="strength-bar"><span id="strengthFill"></span></div>
                                    <p class="text-[11px] text-muted-foreground mt-1" id="strengthText">Minimal 8 karakter</p>
                                    <p class="hint-err text-xs text-destructive mt-1">@error('password'){{ $message }}@enderror</p>
                                </div>
                                <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
                                    <input type="password" name="password_confirmation" id="password_confirmation" required placeholder=" " autocomplete="new-password">
                                    <label for="password_confirmation">Konfirmasi Sandi *</label>
                                    <button type="button" class="eye-btn" data-toggle-pwd="password_confirmation" aria-label="Tampilkan kata sandi">
                                        <svg data-icon-open viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg data-icon-closed class="hidden" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.6 21.6 0 0 1 5.06-6.06M9.9 4.24A10.6 10.6 0 0 1 12 4c7 0 11 8 11 8a21.5 21.5 0 0 1-2.61 3.94M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>
                                    </button>
                                    <p class="hint-err text-xs text-destructive mt-1" id="matchError"></p>
                                </div>
                                <div class="field {{ $errors->has('phone') ? 'has-error' : '' }}">
                                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required placeholder=" " autocomplete="tel" inputmode="tel">
                                    <label for="phone">No. HP / WhatsApp *</label>
                                    <p class="hint-err text-xs text-destructive mt-1">@error('phone'){{ $message }}@enderror</p>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2: Siswa & Sekolah -->
                        <div class="step-panel" data-step="2">
                            <h2 class="font-display text-sm font-bold text-foreground uppercase tracking-wide mb-4">Data Siswa & Sekolah</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="field {{ $errors->has('student_name') ? 'has-error' : '' }}">
                                    <input type="text" name="student_name" id="student_name" value="{{ old('student_name') }}" required placeholder=" ">
                                    <label for="student_name">Nama Siswa *</label>
                                    <p class="hint-err text-xs text-destructive mt-1">@error('student_name'){{ $message }}@enderror</p>
                                </div>
                                <div class="field {{ $errors->has('student_class') ? 'has-error' : '' }}">
                                    <input type="text" name="student_class" id="student_class" value="{{ old('student_class') }}" required placeholder=" ">
                                    <label for="student_class">Kelas *</label>
                                    <p class="hint-err text-xs text-destructive mt-1">@error('student_class'){{ $message }}@enderror</p>
                                </div>
                                <div class="field {{ $errors->has('student_major') ? 'has-error' : '' }}">
                                    <input type="text" name="student_major" id="student_major" value="{{ old('student_major') }}" required placeholder=" ">
                                    <label for="student_major">Jurusan *</label>
                                    <p class="hint-err text-xs text-destructive mt-1">@error('student_major'){{ $message }}@enderror</p>
                                </div>
                                <div class="field {{ $errors->has('school_name') ? 'has-error' : '' }}">
                                    <input type="text" name="school_name" id="school_name" value="{{ old('school_name') }}" required placeholder=" ">
                                    <label for="school_name">Nama Sekolah *</label>
                                    <p class="hint-err text-xs text-destructive mt-1">@error('school_name'){{ $message }}@enderror</p>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 3: Info tambahan + review -->
                        <div class="step-panel" data-step="3">
                            <h2 class="font-display text-sm font-bold text-foreground uppercase tracking-wide mb-1">Info Tambahan</h2>
                            <p class="text-xs text-muted-foreground mb-4">Opsional — boleh dilewati dan diisi belakangan.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="field {{ $errors->has('gender') ? 'has-error' : '' }}">
                                    <select name="gender" id="gender">
                                        <option value=""></option>
                                        <option value="Laki-laki" {{ old('gender') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="Perempuan" {{ old('gender') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    <label for="gender">Jenis Kelamin</label>
                                    <p class="hint-err text-xs text-destructive mt-1">@error('gender'){{ $message }}@enderror</p>
                                </div>
                                <div class="field {{ $errors->has('religion') ? 'has-error' : '' }}">
                                    <select name="religion" id="religion">
                                        <option value=""></option>
                                        <option value="Islam" {{ old('religion') === 'Islam' ? 'selected' : '' }}>Islam</option>
                                        <option value="Kristen" {{ old('religion') === 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                        <option value="Katolik" {{ old('religion') === 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                        <option value="Hindu" {{ old('religion') === 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                        <option value="Buddha" {{ old('religion') === 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                        <option value="Konghucu" {{ old('religion') === 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                                    </select>
                                    <label for="religion">Agama</label>
                                    <p class="hint-err text-xs text-destructive mt-1">@error('religion'){{ $message }}@enderror</p>
                                </div>
                                <div class="field sm:col-span-2 {{ $errors->has('address') ? 'has-error' : '' }}">
                                    <textarea name="address" id="address" rows="3" placeholder=" ">{{ old('address') }}</textarea>
                                    <label for="address">Alamat Lengkap</label>
                                    <p class="hint-err text-xs text-destructive mt-1">@error('address'){{ $message }}@enderror</p>
                                </div>
                            </div>

                            <div class="mt-6 bg-accent border border-border rounded-md p-4 flex items-start gap-3">
                                <svg class="flex-shrink-0 text-success-500 mt-0.5" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m22 4-10 10-3-3"/></svg>
                                <p class="text-xs text-muted-foreground leading-relaxed">Periksa kembali data di langkah sebelumnya sebelum mengirim — klik nomor langkah di atas untuk kembali mengubahnya.</p>
                            </div>
                        </div>

                        <!-- Nav buttons -->
                        <div class="flex items-center gap-3 mt-8">
                            <button type="button" id="prevBtn" class="btn-ghost hidden items-center gap-2 px-5 py-3 rounded-md font-semibold text-sm text-muted-foreground border border-border">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                Kembali
                            </button>
                            <button type="button" id="nextBtn" class="btn-auth flex-1 py-3.5 text-[15px] font-semibold text-primary-foreground rounded-md flex items-center justify-center gap-2">
                                Lanjut
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                            <button type="submit" id="submitBtn" class="btn-auth hidden flex-1 py-3.5 text-[15px] font-semibold text-primary-foreground rounded-md items-center justify-center gap-2.5">
                                <span id="btnLabel">Daftar Sekarang</span>
                            </button>
                        </div>
                    </form>

                    <div class="mt-7 relative text-center">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-border"></div></div>
                        <span class="relative bg-background px-4 text-xs text-muted-foreground uppercase tracking-wide">atau</span>
                    </div>
                    <p class="mt-6 text-center text-sm text-muted-foreground">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="text-primary hover:text-primary/80 font-bold transition">Masuk</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            const TOTAL_STEPS = 3;
            const stepFieldMap = {
                1: ['name','reg_email','reg_password','password_confirmation','phone'],
                2: ['student_name','student_class','student_major','school_name'],
                3: []
            };
            const serverFieldToStep = {
                name:1, email:1, password:1, phone:1, profile_photo:1,
                student_name:2, student_class:2, student_major:2, school_name:2,
                gender:3, religion:3, address:3
            };

            let current = 1;
            const panels = document.querySelectorAll('.step-panel');
            const dots = { 1: document.getElementById('dot1'), 2: document.getElementById('dot2'), 3: document.getElementById('dot3') };
            const lines = { 1: document.getElementById('line1'), 2: document.getElementById('line2') };
            const brandSteps = { 1: document.getElementById('brandStep1'), 2: document.getElementById('brandStep2'), 3: document.getElementById('brandStep3') };
            const stepLabel = document.getElementById('stepLabel');
            const labels = { 1:'Langkah 1 dari 3 · Data Akun', 2:'Langkah 2 dari 3 · Siswa & Sekolah', 3:'Langkah 3 dari 3 · Info Tambahan' };

            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');

            function renderStep(){
                panels.forEach(p => p.classList.toggle('active', Number(p.dataset.step) === current));
                stepLabel.textContent = labels[current];

                [1,2,3].forEach(n => {
                    const dot = dots[n];
                    dot.classList.remove('current','done');
                    if(n < current) dot.classList.add('done');
                    else if(n === current) dot.classList.add('current');
                    dot.innerHTML = n < current
                        ? '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>'
                        : n;
                    if(brandSteps[n]){
                        brandSteps[n].style.opacity = (n === current) ? '1' : '.55';
                        brandSteps[n].style.borderColor = (n === current) ? 'rgba(252,198,38,.5)' : 'rgba(255,255,255,.1)';
                    }
                });
                if(lines[1]) lines[1].classList.toggle('filled', current > 1);
                if(lines[2]) lines[2].classList.toggle('filled', current > 2);

                prevBtn.classList.toggle('hidden', current === 1);
                prevBtn.classList.toggle('flex', current !== 1);
                const isLast = current === TOTAL_STEPS;
                nextBtn.classList.toggle('hidden', isLast);
                submitBtn.classList.toggle('hidden', !isLast);
                submitBtn.classList.toggle('flex', isLast);

                document.getElementById('registerForm').scrollIntoView({ block:'start', behavior:'smooth' });
            }

            function validateStep(n){
                let valid = true;
                (stepFieldMap[n] || []).forEach(id => {
                    const el = document.getElementById(id);
                    if(!el) return;
                    el.setAttribute('data-touched', 'true');
                    if(!el.checkValidity()) valid = false;
                });
                if(n === 1){
                    const p1 = document.getElementById('reg_password').value;
                    const p2 = document.getElementById('password_confirmation').value;
                    const matchError = document.getElementById('matchError');
                    if(p1 !== p2){
                        matchError.textContent = 'Konfirmasi sandi tidak sama.';
                        valid = false;
                    } else {
                        matchError.textContent = '';
                    }
                }
                if(!valid){
                    const form = document.getElementById('registerForm');
                    form.reportValidity();
                }
                return valid;
            }

            nextBtn.addEventListener('click', function(){
                if(!validateStep(current)) return;
                if(current < TOTAL_STEPS){ current++; renderStep(); }
            });
            prevBtn.addEventListener('click', function(){
                if(current > 1){ current--; renderStep(); }
            });
            [1,2,3].forEach(n => {
                dots[n].addEventListener('click', function(){
                    if(n <= current || n === current + 1){
                        if(n > current && !validateStep(current)) return;
                        current = n; renderStep();
                    } else if(n < current){
                        current = n; renderStep();
                    }
                });
            });

            const input = document.getElementById('profile_photo');
            const preview = document.getElementById('photoPreview');
            const MAX_MB = 2;
            input.addEventListener('change', function(){
                const file = this.files && this.files[0];
                if(!file) return;
                if(file.size > MAX_MB * 1024 * 1024){
                    alert('Ukuran foto terlalu besar (maks ' + MAX_MB + 'MB).');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = e => preview.src = e.target.result;
                reader.readAsDataURL(file);
            });

            const pwd = document.getElementById('reg_password');
            const fill = document.getElementById('strengthFill');
            const text = document.getElementById('strengthText');
            pwd.addEventListener('input', function(){
                const v = pwd.value;
                let score = 0;
                if(v.length >= 8) score++;
                if(/[A-Z]/.test(v)) score++;
                if(/[0-9]/.test(v)) score++;
                if(/[^A-Za-z0-9]/.test(v)) score++;
                const levels = [
                    { w:'0%', c:'#E5E7EB', t:'Minimal 8 karakter' },
                    { w:'30%', c:'#F87171', t:'Lemah' },
                    { w:'60%', c:'#FCC626', t:'Cukup' },
                    { w:'85%', c:'#00A2E9', t:'Baik' },
                    { w:'100%', c:'#009A4B', t:'Kuat' },
                ];
                const lv = v.length === 0 ? levels[0] : levels[Math.min(score+1, 4)];
                fill.style.width = lv.w;
                fill.style.background = lv.c;
                text.textContent = lv.t;
                text.style.color = v.length ? lv.c : '#9CA3AF';
            });

            document.querySelectorAll('.eye-btn').forEach(btn => {
                btn.addEventListener('click', function(){
                    const pwd = document.getElementById(btn.dataset.togglePwd);
                    if(!pwd) return;
                    const show = pwd.getAttribute('type') === 'password';
                    pwd.setAttribute('type', show ? 'text' : 'password');
                    btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                    btn.querySelector('[data-icon-open]').classList.toggle('hidden', show);
                    btn.querySelector('[data-icon-closed]').classList.toggle('hidden', !show);
                });
            });

            document.querySelectorAll('.field select').forEach(sel => {
                const sync = () => sel.classList.toggle('has-value', sel.value !== '');
                sel.addEventListener('change', sync);
                sync();
            });

            const serverErrorFields = @json(collect($errors->keys() ?? [])->values());
            if(Array.isArray(serverErrorFields) && serverErrorFields.length){
                const steps = serverErrorFields.map(f => serverFieldToStep[f]).filter(Boolean);
                if(steps.length){ current = Math.min(...steps); }
            }
            renderStep();

            const form = document.getElementById('registerForm');
            const btnLabel = document.getElementById('btnLabel');
            form.addEventListener('submit', function(e){
                if(!validateStep(current)){ e.preventDefault(); return; }
                submitBtn.setAttribute('disabled', 'true');
                btnLabel.textContent = 'Mengirim…';
                const spinner = document.createElement('span');
                spinner.className = 'spinner';
                submitBtn.appendChild(spinner);
            });
        })();
    </script>
</body>
</html>
