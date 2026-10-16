<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\User;
use App\Services\ParentSyncService;
use App\Support\Mailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $loginInput = trim($request->input('email', ''));
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ], [
            'email.required' => 'Email atau Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Keamanan (anti brute-force): maksimal 5 percobaan per menit
        $throttleKey = Str::transliterate(Str::lower($loginInput).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        // 1. Coba verifikasi & auto-sync langsung dari Web Induk / Master App
        $parentUrl = rtrim(config('app.parent_app_url', 'https://kelas-khusus.kpmacademic.io'), '/');
        try {
            $verifyPayload = [
                'password' => $request->input('password'),
            ];
            if ($isEmail) {
                $verifyPayload['email'] = $loginInput;
            } else {
                $verifyPayload['username'] = $loginInput;
            }

            Log::info('kpm-smart: Login verify attempt to Parent', [
                'loginInput' => $loginInput,
                'parentUrl' => $parentUrl,
            ]);

            $response = Http::timeout(6)
                ->withHeaders([
                    'X-API-Key' => env('PARENT_APP_API_KEY', 'kpm-satellite-secret-key-2026'),
                    'Accept' => 'application/json',
                ])
                ->post("{$parentUrl}/api/v1/verify", $verifyPayload);

            Log::info('kpm-smart: Parent verify response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->json(),
            ]);

            if ($response->successful() && ($response->json('valid') ?? false)) {
                $master = $response->json();
                $role = in_array($master['role'], ['super_admin', 'administrator', 'admin', 'guru']) ? 'admin' : 'user';
                $fotoProfil = $master['foto_profil'] ?? "{$parentUrl}/api/foto/".urlencode($master['email']);
                $username = $master['nis'] ?: explode('@', $master['email'])[0];

                $userUpdateData = [
                    'name' => $master['name'],
                    'username' => $username,
                    'password' => $request->input('password'),
                    'student_name' => $master['name'],
                    'student_class' => $master['class_name'] ?: ($master['kelas'] ?: 'VII-A'),
                    'profile_photo' => $fotoProfil,
                    'school_name' => $master['asal_sekolah'] ?? 'KPM Academy',
                    'bidang' => $master['bidang'] ?? 'Matematika & IPA',
                    'level' => $master['level'] ?? '1',
                ];
                foreach (['phone', 'address', 'place_of_birth', 'date_of_birth', 'parent_name', 'parent_phone', 'parent_profession', 'parent_education'] as $f) {
                    if (isset($master[$f]) && $master[$f] !== null && $master[$f] !== '') {
                        $userUpdateData[$f] = $master[$f];
                    }
                }

                $user = User::updateOrCreate(
                    ['email' => $master['email']],
                    $userUpdateData
                );

                $user->forceFill([
                    'username' => $username,
                    'role' => $role,
                    'is_active' => true,
                    'is_verified' => true,
                ])->save();

                Auth::login($user, $request->boolean('remember'));
                RateLimiter::clear($throttleKey);
                $request->session()->regenerate();
                $user->update(['last_login_at' => now()]);

                if ($user->role === 'admin') {
                    return redirect()->route('admin.dashboard');
                }

                return redirect()->intended(route('user.dashboard'));
            }
        } catch (\Throwable $e) {
            // Jika Web Master offline, lanjutkan ke Auth lokal
        }

        // 2. Auth Lokal (Cari via Email atau Username)
        $localUser = User::where(function ($q) use ($loginInput) {
            $q->where('email', $loginInput)
                ->orWhere('username', $loginInput);
        })->first();

        if ($localUser && Hash::check($request->input('password'), $localUser->password)) {
            if ($localUser->is_active === false) {
                RateLimiter::hit($throttleKey);

                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.',
                ]);
            }

            Auth::login($localUser, $request->boolean('remember'));
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            $localUser->update(['last_login_at' => now()]);

            LoginLog::create([
                'user_id' => $localUser->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'location' => null,
                'login_at' => now(),
            ]);

            if ($localUser->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('user.dashboard');
        }

        RateLimiter::hit($throttleKey);

        return back()->withErrors(['email' => 'Email/Username atau password salah!'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Keamanan: hancurkan session lama & regenerasi token CSRF
        // agar session tidak bisa dipakai ulang setelah logout.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showProfile()
    {
        return Inertia::render('Profile/Edit', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'school_name' => 'nullable|string',
            'address' => 'nullable|string',
            'parent_name' => 'nullable|string',
            'parent_phone' => 'nullable|string',
            'place_of_birth' => 'nullable|string',
            'date_of_birth' => 'nullable|string',
            'parent_profession' => 'nullable|string',
            'parent_education' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'name', 'phone', 'school_name', 'address', 'parent_name', 'parent_phone',
            'place_of_birth', 'date_of_birth', 'parent_profession', 'parent_education',
        ]);

        foreach ($data as $k => $v) {
            if (is_string($v) && trim($v) === '') {
                $data[$k] = null;
            }
        }
        if (! empty($data['date_of_birth']) && strtotime($data['date_of_birth'])) {
            $data['date_of_birth'] = date('Y-m-d', strtotime($data['date_of_birth']));
        } else {
            $data['date_of_birth'] = null;
        }

        $uploadedPhoto = null;
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo && ! str_starts_with($user->profile_photo, 'http')) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $uploadedPhoto = $request->file('profile_photo')->store('profile_photos', 'public');
            $data['profile_photo'] = $uploadedPhoto;
        }

        $user->update($data);

        // Sync perubahan profil ke Web Induk
        try {
            ParentSyncService::syncUserToParent($user, null, $uploadedPhoto);
        } catch (\Throwable $e) {
            \Log::error('Gagal sync profil update ke Web Induk: '.$e->getMessage());
        }

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function showChangePassword()
    {
        $user = Auth::user();

        return Inertia::render('Profile/ChangePassword', [
            'canChangePassword' => $user->role === 'admin',
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return back()->withErrors([
                'current_password' => 'Kata sandi akun siswa dikelola terpusat dari Web Induk. Perubahan kata sandi hanya dapat dilakukan melalui Web Induk.',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah!']);
        }

        $user->forceFill([
            'password' => $request->password,
        ])->save();

        return back()->with('success', 'Password berhasil diperbarui!');
    }

    // ============ ADMIN PROFILE ============

    public function adminShowProfile()
    {
        return Inertia::render('Admin/Profile/Edit', [
            'user' => null,
        ]);
    }

    public function adminUpdateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($request->email !== $user->email) {
            $emailExists = User::where('email', $request->email)->where('id', '!=', $user->id)->exists();
            if ($emailExists) {
                return back()->withErrors(['email' => 'Email sudah digunakan oleh user lain.'])->withInput();
            }
        }

        $data = $request->only(['name', 'email', 'phone']);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo && ! str_starts_with($user->profile_photo, 'http')) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $user->update($data);

        // Sync perubahan profil admin ke Web Induk
        try {
            ParentSyncService::syncUserToParent($user, null, $data['profile_photo'] ?? null);
        } catch (\Throwable $e) {
            \Log::error('Gagal sync admin profil update ke Web Induk: '.$e->getMessage());
        }

        return back()->with('success', 'Profil admin berhasil diperbarui!');
    }

    public function adminShowChangePassword()
    {
        return Inertia::render('Admin/Profile/ChangePassword');
    }

    public function adminChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah!']);
        }

        $user->forceFill([
            'password' => $request->password,
        ])->save();

        return back()->with('success', 'Password admin berhasil diperbarui!');
    }

    /*
    |--------------------------------------------------------------------
    | LUPA PASSWORD (2 langkah, berbasis token yang dikirim via email)
    |--------------------------------------------------------------------
    | Step 1: user masukkan email -> jika terdaftar, sistem mengirim tautan
    |         reset berisi token acak 64 karakter kriptografis.
    | Step 2: user klik tautan -> buat password baru + konfirmasi.
    |
    | Keamanan:
    | - Token disimpan sebagai hash SHA-256 (bukan teks asli) di database,
    |   sehingga bocornya database tidak membocorkan token.
    | - Token kedaluwarsa dalam 30 menit dan hanya bisa dipakai SEKALI.
    | - Token lama otomatis tidak berlaku saat minta tautan baru.
    | - Respons selalu identik meski email tidak terdaftar
    |   (anti user enumeration).
    | - Rate limit per email+IP mencegah spam email & brute-force.
    */

    private const RESET_TOKEN_TTL_MINUTES = 30;

    private const RESET_REQUEST_MAX_ATTEMPTS = 3;

    private const RESET_REQUEST_DECAY_SECONDS = 600; // 10 menit

    // STEP 1 - Form input email
    public function showForgotPassword()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Keamanan: batasi permintaan reset per email + IP agar alur ini
        // tidak bisa dijadikan sarana spam email / brute-force.
        $throttleKey = Str::transliterate('pwreset|'.Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, self::RESET_REQUEST_MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($throttleKey) / 60);

            return back()->withErrors([
                'email' => "Terlalu banyak permintaan reset password. Silakan coba lagi dalam {$minutes} menit.",
            ])->withInput();
        }

        RateLimiter::hit($throttleKey, self::RESET_REQUEST_DECAY_SECONDS);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $this->issueResetToken($user);
        }

        // Keamanan (anti user enumeration): respons selalu sama, terlepas
        // dari apakah email terdaftar atau tidak.
        return redirect()
            ->route('password.sent')
            ->with('reset_email_sent', $request->email);
    }

    // Halaman konfirmasi "tautan telah dikirim" (GET, aman dari refresh/re-submit).
    public function showResetLinkSent(Request $request)
    {
        if (! session('reset_email_sent')) {
            return redirect()->route('password.request');
        }

        return Inertia::render('Auth/ForgotPasswordSent', [
            'email' => session('reset_email_sent'),
        ]);
    }

    /**
     * Buat token reset baru, simpan hash-nya, lalu kirim tautan via email.
     */
    private function issueResetToken(User $user): void
    {
        // Hapus token lama agar hanya tautan terbaru yang valid.
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        // Token acak kriptografis 64 karakter hex (256 bit entropi).
        $token = bin2hex(random_bytes(32));

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(self::RESET_TOKEN_TTL_MINUTES),
            'created_at' => now(),
        ]);

        $resetUrl = url(route('password.reset', ['token' => $token]));

        app(Mailer::class)->sendHtml(
            $user->email,
            'Reset Kata Sandi - '.config('app.name'),
            'emails.password-reset',
            [
                'resetUrl' => $resetUrl,
                'email' => $user->email,
                'name' => $user->name,
            ],
        );
    }

    // STEP 2 - Form password baru via token dari email
    public function showResetPassword(Request $request, string $token)
    {
        if (! $this->validResetToken($token)) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.']);
        }

        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $record = $this->validResetToken($request->token);

        if (! $record) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.']);
        }

        $user = User::where('email', $record->email)->first();

        if (! $user) {
            DB::table('password_reset_tokens')->where('email', $record->email)->delete();

            return redirect()->route('password.request')
                ->withErrors(['email' => 'Terjadi kesalahan, silakan ulangi proses.']);
        }

        // Keamanan: rotasi remember_token agar sesi "ingat saya" lama
        // (mis. milik penyerang yang mencuri cookie) langsung tidak berlaku.
        // forceFill calls setAttribute which triggers the mutator, so pass
        // plaintext here — the mutator handles hashing automatically.
        $user->forceFill([
            'password' => $request->password,
            'remember_token' => Str::random(60),
        ])->save();

        // Keamanan: token hanya bisa dipakai SEKALI.
        DB::table('password_reset_tokens')->where('email', $record->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Password berhasil diperbarui! Silakan masuk dengan password baru.');
    }

    /**
     * Validasi token reset: cari berdasarkan hash SHA-256 dan pastikan
     * belum kedaluwarsa. Mengembalikan record jika valid, null jika tidak.
     */
    private function validResetToken(?string $token): ?object
    {
        if (! $token || strlen($token) !== 64 || ! ctype_xdigit($token)) {
            return null;
        }

        $record = DB::table('password_reset_tokens')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (! $record || now()->gt($record->expires_at)) {
            return null;
        }

        return $record;
    }
}
