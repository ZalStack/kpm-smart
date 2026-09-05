<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\SearchHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AdminUserController extends Controller
{
    /**
     * Daftar semua user (management data users).
     */
    public function index(Request $request)
    {
        $query = User::query()->where('role', 'user');

        if ($search = $request->get('search')) {
            $escapedSearch = SearchHelper::escapeLike($search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('name', 'like', "%{$escapedSearch}%")
                  ->orWhere('email', 'like', "%{$escapedSearch}%")
                  ->orWhere('student_name', 'like', "%{$escapedSearch}%")
                  ->orWhere('school_name', 'like', "%{$escapedSearch}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('is_active', $status === 'active');
        }

        if ($bidang = $request->get('bidang')) {
            $query->where('bidang', $bidang);
        }

        if ($level = $request->get('level')) {
            $query->where('level', $level);
        }

        if ($kelas = $request->get('kelas')) {
            $query->where('student_class', $kelas);
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        $totalUsers = User::where('role', 'user')->count();
        $activeUsers = User::where('role', 'user')->where('is_active', true)->count();
        $inactiveUsers = $totalUsers - $activeUsers;
        $newThisMonth = User::where('role', 'user')->whereMonth('created_at', now()->month)->count();

        $allBidang = User::where('role', 'user')->whereNotNull('bidang')->where('bidang', '!=', '')->distinct()->pluck('bidang')->sort()->values();
        $allLevel = User::where('role', 'user')->whereNotNull('level')->where('level', '!=', '')->distinct()->pluck('level')->sort()->values();
        $allKelas = User::where('role', 'user')->whereNotNull('student_class')->where('student_class', '!=', '')->distinct()->pluck('student_class')->sort()->values();

        return Inertia::render('Admin/Users/UserIndex', [
            'users' => $users,
            'search' => $search ?? '',
            'status' => $status ?? '',
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'allBidang' => $allBidang,
            'allLevel' => $allLevel,
            'allKelas' => $allKelas,
            'filters' => $request->only(['search', 'status', 'bidang', 'level', 'kelas']),
        ]);
    }

    /**
     * Form tambah user baru.
     */
    public function create()
    {
        return Inertia::render('Admin/Users/UserCreate');
    }

    /**
     * Simpan user baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'student_class' => 'nullable|string|max:50',
            'bidang' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'school_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'gender' => 'nullable|string|in:Laki-laki,Perempuan',
            'religion' => 'nullable|string|max:50',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'student_name' => $validated['name'],
            'student_class' => $validated['student_class'] ?? null,
            'bidang' => $validated['bidang'] ?? null,
            'level' => $validated['level'] ?? null,
            'school_name' => $validated['school_name'] ?? null,
            'address' => $validated['address'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'religion' => $validated['religion'] ?? null,
        ]);

        $user->role = 'user';
        $user->is_verified = true;
        $user->is_active = true;
        $user->save();

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $user->update(['profile_photo' => $path]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User "' . $user->name . '" berhasil ditambahkan. Email: ' . $user->email);
    }

    /**
     * Detail satu user, termasuk ringkasan pesanan/aktivitasnya.
     */
    public function show(User $user)
    {
        return Inertia::render('Admin/Users/UserShow', [
            'user' => $user,
        ]);
    }

    /**
     * Form edit user.
     */
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/UserEdit', [
            'user' => $user,
        ]);
    }

    /**
     * Update data user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'student_class' => 'nullable|string|max:50',
            'bidang' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'school_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'gender' => 'nullable|string|in:Laki-laki,Perempuan',
            'religion' => 'nullable|string|max:50',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'student_name' => $validated['name'],
            'student_class' => $validated['student_class'] ?? null,
            'bidang' => $validated['bidang'] ?? null,
            'level' => $validated['level'] ?? null,
            'school_name' => $validated['school_name'] ?? null,
            'address' => $validated['address'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'religion' => $validated['religion'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $user->update($data);

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', 'Data user "' . $user->name . '" berhasil diperbarui.');
    }

    /**
     * Hapus user.
     */
    public function destroy(User $user)
    {
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User "' . $userName . '" berhasil dihapus.');
    }

    /**
     * Toggle aktif/nonaktifkan akun user.
     */
    public function toggleActive(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', $user->is_active
            ? 'Akun ' . $user->name . ' diaktifkan kembali.'
            : 'Akun ' . $user->name . ' dinonaktifkan.');
    }

    /**
     * Update level user via AJAX (realtime inline edit).
     */
    public function updateLevel(Request $request, User $user)
    {
        $validated = $request->validate([
            'level' => 'nullable|string|max:50',
        ]);

        $user->level = $validated['level'] ?: null;
        $user->save();

        return response()->json([
            'success' => true,
            'level' => $user->level,
            'message' => 'Level "' . ($user->level ?? '-') . '" berhasil diperbarui.',
        ]);
    }

    /**
     * Form import user dari Excel.
     */
    public function showImportExcel()
    {
        return Inertia::render('Admin/Users/ImportUsers');
    }

    /**
     * Reset (hapus) semua data user yang diimport dari Excel.
     */
    public function resetImportedUsers()
    {
        $deleted = User::where('role', 'user')->delete();

        return back()->with('success', "Berhasil menghapus {$deleted} data user.");
    }

    /**
     * Proses import user dari file Excel (.xlsx, .xls, .csv).
     * Kolom: No, Nama, Kelas, Bidang, Asal Sekolah, password, Level
     * Email: namabelakang@gmail.com
     * Password default: "password"
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('file');
        $realPath = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv') {
            $handle = fopen($realPath, 'r');
            $rows = [];
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        } else {
            $reader = IOFactory::createReaderForFile($realPath);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $spreadsheet = $reader->load($realPath);
            $rows = [];
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetRows = $sheet->toArray(null, true, true, false);
                if (empty($rows)) {
                    $rows = $sheetRows;
                } else {
                    $header = array_map(fn($v) => strtolower(trim((string) ($v ?? ''))), $rows[0]);
                    $isSameHeader = true;
                    $sheetHeader = array_map(fn($v) => strtolower(trim((string) ($v ?? ''))), $sheetRows[0] ?? []);
                    if (count($header) === count($sheetHeader)) {
                        for ($i = 0; $i < count($header); $i++) {
                            if ($header[$i] !== $sheetHeader[$i]) {
                                $isSameHeader = false;
                                break;
                            }
                        }
                    } else {
                        $isSameHeader = false;
                    }
                    if ($isSameHeader) {
                        $rows = array_merge($rows, array_slice($sheetRows, 1));
                    }
                }
            }
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }

        $colMap = $this->detectColumns($rows);

        if (empty($colMap['nama'])) {
            return back()->withErrors(['file' => 'File Excel harus memiliki kolom "Nama".']);
        }

        $now = now()->toDateTimeString();
        $defaultHashed = Hash::make('password');
        $usedEmails = array_flip(User::pluck('email')->map(fn($e) => strtolower($e))->toArray());
        $toInsert = [];
        $skipped = 0;

        foreach ($rows as $index => $row) {
            if ($index < 1) continue;

            $nama = trim((string) ($row[$colMap['nama']] ?? ''));
            if ($nama === '') {
                $skipped++;
                continue;
            }

            $kelas = trim((string) ($row[$colMap['kelas']] ?? ''));
            $bidang = trim((string) ($row[$colMap['bidang']] ?? ''));
            $sekolah = trim((string) ($row[$colMap['sekolah']] ?? ''));
            $level = trim((string) ($row[$colMap['level']] ?? ''));

            $excelPassword = isset($colMap['password']) ? trim((string) ($row[$colMap['password']] ?? '')) : '';
            if ($excelPassword !== '' && strtolower($excelPassword) !== 'password') {
                $rowHash = Hash::make($excelPassword);
            } else {
                $rowHash = $defaultHashed;
            }

            $nameParts = explode(' ', $nama);
            $lastName = end($nameParts);
            $baseEmail = strtolower($lastName);

            $counter = 1;
            $email = $baseEmail . '@gmail.com';
            while (isset($usedEmails[$email])) {
                $counter++;
                $email = $baseEmail . $counter . '@gmail.com';
            }
            $usedEmails[$email] = true;

            $toInsert[] = [
                'name' => $nama,
                'email' => $email,
                'password' => $rowHash,
                'phone' => null,
                'student_name' => $nama,
                'student_class' => $kelas ?: null,
                'bidang' => $bidang ?: null,
                'level' => $level ?: null,
                'school_name' => $sekolah ?: null,
                'address' => null,
                'gender' => null,
                'religion' => null,
                'role' => 'user',
                'is_verified' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $imported = 0;
        if (!empty($toInsert)) {
            foreach (array_chunk($toInsert, 100) as $chunk) {
                DB::table('users')->insert($chunk);
                $imported += count($chunk);
            }
        }

        if ($imported > 0) {
            return back()->with('success', "Import selesai! {$imported} user berhasil ditambahkan. {$skipped} baris dilewati.");
        }

        return back()->withErrors(['file' => 'Tidak ada data valid yang bisa diimport.']);
    }

    private function detectColumns(array $rows): array
    {
        $colMap = [];
        $sampleRows = array_slice($rows, 0, 5);

        foreach ($sampleRows as $row) {
            foreach ($row as $i => $val) {
                $v = strtolower(trim((string) ($val ?? '')));
                if ($v === '') continue;
                if ($v === 'nama' || (empty($colMap['nama']) && strlen($v) > 3 && preg_match('/^[a-z\s]+$/', $v) && str_contains($v, 'nama'))) $colMap['nama'] = $i;
                elseif ($v === 'kelas' || (empty($colMap['kelas']) && $v === 'kelas')) $colMap['kelas'] = $i;
                elseif ($v === 'bidang' || (empty($colMap['bidang']) && $v === 'bidang')) $colMap['bidang'] = $i;
                elseif ((str_contains($v, 'sekolah') || str_contains($v, 'asal')) && empty($colMap['sekolah'])) $colMap['sekolah'] = $i;
                elseif ($v === 'password' && empty($colMap['password'])) $colMap['password'] = $i;
                elseif (($v === 'level' || str_contains($v, 'berbakat') || str_contains($v, 'level')) && empty($colMap['level'])) $colMap['level'] = $i;
            }
            if (count($colMap) >= 4) break;
        }

        if (empty($colMap['level'])) {
            foreach (array_slice($rows, 1, 20) as $row) {
                foreach ($row as $i => $val) {
                    $v = strtolower(trim((string) ($val ?? '')));
                    if (str_contains($v, 'berbakat')) {
                        $colMap['level'] = $i;
                        break 2;
                    }
                }
            }
        }

        if (empty($colMap['kelas'])) {
            foreach (array_slice($rows, 1, 10) as $row) {
                foreach ($row as $i => $val) {
                    $v = trim((string) ($val ?? ''));
                    if ($v !== '' && is_numeric($v) && (int) $v >= 1 && (int) $v <= 12 && empty($colMap['kelas'])) {
                        $colMap['kelas'] = $i;
                        break 2;
                    }
                }
            }
        }

        if (empty($colMap['bidang'])) {
            foreach (array_slice($rows, 1, 10) as $row) {
                foreach ($row as $i => $val) {
                    $v = strtoupper(trim((string) ($val ?? '')));
                    if ($v !== '' && preg_match('/^[A-Z\s]+$/', $v) && strlen($v) <= 15 && empty($colMap['bidang'])) {
                        $colMap['bidang'] = $i;
                        break 2;
                    }
                }
            }
        }

        if (empty($colMap['sekolah'])) {
            foreach (array_slice($rows, 1, 5) as $row) {
                foreach ($row as $i => $val) {
                    $v = trim((string) ($val ?? ''));
                    if ($v !== '' && strlen($v) > 5 && !is_numeric($v) && empty($colMap['sekolah'])
                        && $i !== ($colMap['nama'] ?? -1) && $i !== ($colMap['kelas'] ?? -1)
                        && $i !== ($colMap['bidang'] ?? -1) && $i !== ($colMap['level'] ?? -1)
                        && $i !== ($colMap['password'] ?? -1)) {
                        $colMap['sekolah'] = $i;
                        break 2;
                    }
                }
            }
        }

        return $colMap;
    }
}
