# KPM Smart

Platform pembelajaran daring: bank soal dan tugas (PR), manajemen siswa,
gamifikasi, pengajuan izin, notifikasi push, serta laporan mingguan ke orang tua.
Seluruh antarmuka memakai bahasa Indonesia.

Dibangun dengan **Laravel 13**, **Inertia.js + Vue 3**, **Tailwind CSS**, dan **MySQL**.

---

## Daftar Isi

- [Kebutuhan Sistem](#kebutuhan-sistem)
- [Development Locally](#development-locally)
- [Deploy ke Shared Hosting / cPanel](#deploy-ke-shared-hosting--cpanel)
- [Konfigurasi Environment](#konfigurasi-environment)
- [API](#api)
- [Menjalankan Test](#menjalankan-test)
- [Masalah yang Sering Muncul](#masalah-yang-sering-muncul)

---

## Kebutuhan Sistem

| Komponen | Versi | Catatan |
|---|---|---|
| PHP | 8.3+ | Wajib, dicek `composer.json` |
| MySQL / MariaDB | 5.7+ / 10.3+ | |
| Node.js | 20+ | Hanya untuk build frontend, **tidak perlu di server** |
| Ekstensi PHP | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `gd`, `zip`, `intl` | `zip` wajib untuk export Excel |
| Biner sistem | `tesseract-ocr`, `ghostscript`, `imagemagick` | **Hanya** bila fitur import PDF/OCR dipakai |

---

## Development Locally

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

#Buat database lalu sesuaikan DB_* di .env
php artisan migrate
php artisan app:make-admin --email=admin@contoh.test --name="Admin"
```

Jalankan semua service sekaligus:

```bash
composer dev
```

> **`composer dev` menjalankan `npm run dev`, yang membuat file `public/hot`.**
> File tersebut **tidak boleh** ikut ter-deploy. Lihat
> [Deploy](#deploy-ke-shared-hosting--cpanel).

Buat akun admin tanpa password hardcoded:

```bash
php artisan app:make-admin --email=admin@domain.com --name="Admin"
# password di-generate dan ditampilkan sekali
```

### Data contoh

`php artisan db:seed` aman untuk local/staging — tapi **diblokir otomatis di
production**, karena seeder membuat akun dengan password `password123` yang
tertulis di repository publik.

---

## Deploy ke Shared Hosting / cPanel

Shared hosting tidak punya daemon, jadi **queue worker tidak diperlukan**
(aplikasi ini tidak punya queue job). Yang dibutuhkan hanya satu cron job.

### 1. Upload source code

Upload seluruh project ke `/home/USER/kpm-smart` (di luar `public_html`, atau di
subfolder yang tidak bisa diakses langsung).

### 2. Arahkan document root

**Ini langkah paling penting.** Di cPanel: **Domains → domain → Document Root**,
arahkan ke:

```
/home/USER/kpm-smart/public
```

Kalau document root **tidak bisa** diubah, file `.htaccess` di root project
sudah disiapkan untuk mengarahkan request ke `public/` sekaligus memblokir akses
langsung ke `.env`, `storage/`, `composer.json`, dan lain-lain. Tapi ini opsi
yang lebih rapuh — tetap prioritaskan document root.

> Kalau document root justru diarahkan ke root project tanpa `.htaccess` yang
> aktif, `.env` bisa diunduh siapa pun.tidak berarti apa-apa。 Segera cek:
> `curl -I https://domain-anda.com/.env` harus mengembalikan **403/404**.

### 3. Install dependency

```bash
cd /home/USER/kpm-smart
composer install --no-dev --optimize-autoloader
```

> Jangan pakai `--no-dev` di local, karena `phpunit` dibutuhkan untuk test.

### 4. Buat `.env` di server

**Jangan menyalin `.env` dari mesin lokal.** `APP_KEY` dan password database
lokal dianggap sudah bocor.

```bash
cp .env.production.example .env
nano .env
```

Lalu sesuaikan minimal: `APP_KEY`, `APP_URL`, `DB_*`, `MAIL_*`, `CORS_ALLOWED_ORIGINS`,
`VAPID_*`.

Buat `APP_KEY` baru:

```bash
php artisan key:generate
```

Buat akun admin:

```bash
php artisan app:make-admin --email=admin@domain-anda.com --name="Admin"
```

### 5. Migrasi database

```bash
php artisan migrate --force
```

**Jangan** menjalankan `db:seed --force` — sudah diblokir di production, dan
memang tidak perlu karena `app:make-admin` adalah cara yang tepat.

### 6. Symlink storage

```bash
php artisan storage:link
```

### 7. Permission

```bash
chmod -R 775 storage bootstrap/cache
```

Kalau web server berjalan sebagai user berbeda, sesuaikan owner-nya.

### 8. Cache production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> `config:cache` **mengunci** nilai `.env`. Kalau perlu mengubah environment
>_variable_ setelah ini, jalankan `php artisan config:clear` dulu.

### 9. Cron job (cPanel)

Tambahkan **satu** cron job di cPanel → Cron Jobs:

```bash
* * * * * cd /home/USER/kpm-smart && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

Ganti `/usr/local/bin/php` dengan path PHP binary Anda. Satu entry ini sudah
menangani|Notifikasi`' jadwal harian pukul 07:00, 13:00, dan 18:00.

> Sebagian host membatasi jumlah cron job. `schedule:run` per menit cukup untuk
> ketiga jadwal tersebut.

### 10. Frontend

Karena shared hosting tidak punya Node, **hasil build wajib ada di repository**:

```bash
npm ci
npm run build
git add -A public/build
```

Pastikan `public/hot` **tidak ada** sebelum deploy:

```bash
rm -f public/hot
```

Kalau `public/hot` ikut ter-deploy, seluruh halaman akan kosong putih karena
Vite menunjuk dev server di `127.0.0.1`. Middleware `SecurityHeadersMiddleware`
juga akan mencatat error ini di `storage/logs/laravel.log`.

---

## Konfigurasi Environment

| Key | Wajib | Keterangan |
|---|---|---|
| `APP_ENV` | ya | Selalu `production` di server |
| `APP_DEBUG` | ya | **Selalu `false`.** `true` membocorkan stack trace, query, dan `APP_KEY` |
| `APP_KEY` | ya | Generate per server, jangan dicopy |
| `APP_URL` | ya | URL HTTPS lengkap, tanpa trailing slash |
| `DB_*` | ya | Gunakan user MySQL khusus, bukan `root` |
| `SESSION_SECURE_COOKIE` | ya | `true` agar cookie hanya lewat HTTPS |
| `SANCTUM_TOKEN_EXPIRATION` | tidak | Masa berlaku token API dalam menit. Default `1440` (24 jam), `0` = tidak pernah expire |
| `CORS_ALLOWED_ORIGINS` | ya | Origin browser yang boleh memanggil API, pisahkan dengan koma |
| `VAPID_PUBLIC_KEY` / `VAPID_PRIVATE_KEY` | tidak | Generate dengan `php artisan web-push:generate-vapid-keys`. Kosong → notifikasi push otomatis dimatikan, bukan error |
| `PARENT_APP_URL` | tidak | Base URL web induk/master app. Kosong atau mati → auto-sync & laporan mingguan dilewati, login tetap aman |
| `MAIL_MAILER` | **ya** | Untuk reset password & enroll key. `log` berarti email tidak terkirim |

---

## API

Semua endpoint berada di prefix `/api` dan **wajib** memakai Sanctum bearer token,
kecuali endpoint login.

```bash
# Ambil token
curl -X POST https://domain-anda.com/api/v1/auth/login \
  -H "Accept: application/json" \
  -d "email=admin@domain-anda.com&password=rahasia"

# Pakai token
curl https://domain-anda.com/api/v1/auth/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

| Group | Prefix | Akses |
|---|---|---|
| Auth | `/api/v1/auth/*` | `login` publik; `me` & `logout` butuh token |
| Admin | `/api/admin/v1/*` | Wajib token **role admin** (403 bila role lain) |
| User | `/api/user/v1/*` | Wajib token |

Rate limit:

- `api-login` — 5 percobaan/menit per email+IP
- `api` — 120 request/menit per user

### Tentang `?user_id=`

Endpoint **user** mengabaikan `?user_id=` — data yang dikembalikan selalu milik
user yang sedang login. Menambahkan `?user_id=<id lain>` tidak akan memberi akses
ke data orang lain (perlindungan IDOR).

Endpoint **admin** tetap memakai `?user_id=` untuk melihat data user tertentu,
karena route-nya dijaga `role:admin`.

### Kunci jawaban

Kunci jawaban **tidak pernah** dikirim ke browser sebelum siswa selesai menjawab.
Penilaian dilakukan sepenuhnya di server. Halaman admin tetap menampilkan kunci
jawaban karena memang dibutuhkan untuk menyusun soal.

---

## Ikon (Material Design Icons)

Aplikasi memakai `<Icon icon="mdi:...">` dari `@iconify/vue`. Ikon **dilayani
dari bundel**, bukan dari CDN.

Dua hal yang membuatnya begitu:

1. `@iconify/vue/offline` — entry point yang mengekspor API sama persis
   (`Icon`, `addCollection`, `addIcon`) tapi **tidak memuat kode jaringan
   sama sekali** (tidak ada `fetch`, tidak ada daftar API host).
2. `resources/js/icons/mdi.json` — collection Iconify berisi **hanya** ikon yang
   dipakai aplikasi, didaftarkan lewat `addCollection()` di
   `resources/js/icons/index.js`.

Konsekuensinya: tidak ada request ke `api.iconify.design`, `connect-src` cukup
`'self'`, aplikasi tidak bergantung pada pihak ketiga saat runtime, dan ikon
tidak hilang saat CDN down.

### Menambah ikon baru

```bash
# 1. Pakai ikon di kode, mis. <Icon icon="mdi:rocket-launch" />
# 2. Generate ulang collection:
npm run icons
```

Collection hasil generate **harus di-commit** — shared hosting tidak menjalankan
`npm run icons`, dan `@iconify-json/mdi` hanya dipakai sebagai devDependency
saat generate.

Kalau nama ikon tidak ada di Material Design Icons, `npm run icons` akan gagal
dan menyebut nama yang bermasalah — itu disengaja, bukan bug.

### Test yang menjaga

`IconBundleTest` gagal kalau:

- ada ikon di source yang belum masuk collection,
- collection berisi ikon yang sudah tidak dipakai,
- ada file yang masih import `@iconify/vue` (bukan `/offline`),
- CSP mengizinkan host Iconify.



```bash
php artisan test
```

Test suite mencakup:

- `ApiAuthorizationTest` — memindai **seluruh** rute API dan memastikan tidak ada
  yang bisa diakses tanpa token atau oleh role non-admin
- `AnswerKeyExposureTest` — memastikan kunci jawaban tidak bocor lewat halaman
  mengerjakan soal maupun endpoint API
- `SecurityHardeningTest` — security headers, rate limit login, proteksi seeder
- `RoleMiddlewareTest`, `ApiEndpointsTest`, `PasswordResetFlowTest`

> Suite ini adalah **deployment gate**. Jangan deploy kalau `php artisan test`
> tidak hijau.

---

## Masalah yang Sering Muncul

### Halaman kosong putih setelah deploy

Penyebab hampir selalu `public/hot` ikut ter-deploy. Hapus file tersebut:

```bash
rm -f public/hot
```

### Import PDF gagal dengan "Maksimal 50 MB" padahal file kecil

Batas `upload_max_filesize` milik host. `public/.user.ini` sudah disiapkan;
pastikan file itu ikut ter-upload dan `max_execution_time` cukup.

### Error 500 setiap kali kirim email

`MAIL_MAILER=log` hanya menulis ke log. Untuk benar-benar mengirim, isi
`MAIL_MAILER=sendmail` (butuh sendmail terpasang) atau `smtp` dengan konfigurasi
relay. Cek juga `storage/logs/laravel.log`.

### 419 / token_csrf mismatch

`SESSION_SECURE_COOKIE=true` padahal situs diakses lewat HTTP. Pastikan HTTPS
sudah aktif dan `APP_URL` memakai `https://`.

### Login berhasil tapi auto-sync ke web induk diam-diam gagal

`PARENT_APP_URL` kosong atau web induk mati. Ini memang ditangani dengan
try/catch sehingga login tidak terganggu. Cek `storage/logs/laravel.log`.

### Notifikasi push tidak dikirim

`VAPID_PUBLIC_KEY` / `VAPID_PRIVATE_KEY` masih kosong. Generate dengan
`php artisan web-push:generate-vapid-keys`, lalu pastikan endpoint
`/push/vapid-key` mengembalikan 200 (bukan 503).

### 500 "Class ... not found" setelah deploy

`bootstrap/cache` masih menyimpan cache paket dari versi lama:

```bash
php artisan config:clear && php artisan cache:clear
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php
php artisan package:discover
```
