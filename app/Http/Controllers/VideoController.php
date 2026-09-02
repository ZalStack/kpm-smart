<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoOrder;
use App\Services\NotificationService;
use App\Models\Package;
use App\Support\SearchHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $query = Video::with('package');

        if ($request->filled('search')) {
            $search = $request->search;
            $escapedSearch = SearchHelper::escapeLike($search);
            $query->where('title', 'like', "%{$escapedSearch}%");
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $videos = $query->latest()->paginate(10)->withQueryString();

        return view('admin.videos.index', compact('videos'));
    }

    public function create()
    {
        $packages = Package::orderBy('title')->get();
        return view('admin.videos.create', compact('packages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'package_id' => 'nullable|exists:packages,id',
            'video_file' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:51200',
            'video_url' => 'nullable|url',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:percent,nominal',
            'discount_value' => 'nullable|numeric|min:0',
            'access_duration_days' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ], [
            'title.required' => 'Judul video wajib diisi.',
            'price.required' => 'Harga video wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga minimal 0.',
            'access_duration_days.required' => 'Durasi akses wajib diisi.',
            'access_duration_days.integer' => 'Durasi akses harus berupa angka bulat.',
            'access_duration_days.min' => 'Durasi akses minimal 1 hari.',
            'video_file.max' => 'Ukuran file video maksimal 50MB.',
            'video_file.uploaded' => 'File video gagal diunggah ke server. Kemungkinan ukuran file terlalu besar (maksimal 50MB) atau koneksi terputus. Silakan coba lagi.',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 3MB.',
        ]);

        $data = $validated;
        $data['is_active'] = $request->boolean('is_active');
        $data['discount_type'] = $request->discount_type ?: null;
        // Clamp diskon persen maksimal 100 agar harga akhir tidak minus.
        if ($data['discount_type'] === 'percent' && $data['discount_value'] !== null) {
            $data['discount_value'] = min(100, max(0, (float) $data['discount_value']));
        } else {
            $data['discount_value'] = $request->discount_value ?: null;
        }

        if (!$request->hasFile('video_file')) {
            $data['video_file'] = null;
        } else {
            $data['video_file'] = $request->file('video_file')->store('videos', 'public');
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('video-thumbnails', 'public');
        } else {
            $data['thumbnail'] = null;
        }

        if (empty($data['video_file']) && empty($data['video_url'])) {
            return back()->withErrors(['video_url' => 'Upload file video atau masukkan link video wajib diisi.'])->withInput();
        }

        Video::create($data);

        return redirect()->route('admin.videos.index')->with('success', 'Video berhasil ditambahkan!');
    }

    public function edit(Video $video)
    {
        $packages = Package::orderBy('title')->get();
        return view('admin.videos.edit', compact('video', 'packages'));
    }

    public function update(Request $request, Video $video)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'package_id' => 'nullable|exists:packages,id',
            'video_file' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:51200',
            'video_url' => 'nullable|url',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:percent,nominal',
            'discount_value' => 'nullable|numeric|min:0',
            'access_duration_days' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ], [
            'title.required' => 'Judul video wajib diisi.',
            'price.required' => 'Harga video wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga minimal 0.',
            'access_duration_days.required' => 'Durasi akses wajib diisi.',
            'access_duration_days.integer' => 'Durasi akses harus berupa angka bulat.',
            'access_duration_days.min' => 'Durasi akses minimal 1 hari.',
            'video_file.max' => 'Ukuran file video maksimal 50MB.',
            'video_file.uploaded' => 'File video gagal diunggah ke server. Kemungkinan ukuran file terlalu besar (maksimal 50MB) atau koneksi terputus. Silakan coba lagi.',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 3MB.',
        ]);

        $data = $validated;
        $data['is_active'] = $request->boolean('is_active');
        $data['discount_type'] = $request->discount_type ?: null;
        $data['discount_value'] = $request->discount_value !== null ? (float) $request->discount_value : null;

        if ($request->hasFile('video_file')) {
            if ($video->video_file) {
                Storage::disk('public')->delete($video->video_file);
            }
            $data['video_file'] = $request->file('video_file')->store('videos', 'public');
        } else {
            unset($data['video_file']);
        }

        if ($request->hasFile('thumbnail')) {
            if ($video->thumbnail) {
                Storage::disk('public')->delete($video->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('video-thumbnails', 'public');
        } else {
            unset($data['thumbnail']);
        }

        if (empty($data['video_file']) && empty($video->video_file) && empty($data['video_url']) && empty($video->video_url)) {
            return back()->withErrors(['video_url' => 'Upload file video atau masukkan link video wajib diisi.'])->withInput();
        }

        $video->update($data);

        return redirect()->route('admin.videos.index')->with('success', 'Video berhasil diperbarui!');
    }

    public function destroy(Video $video)
    {
        // videoOrders(): relasi ke tabel video_orders (model Video tidak punya relasi orders()).
        $paidOrders = $video->videoOrders()->where('payment_status', 'paid')->count();

        if ($paidOrders > 0) {
            return redirect()->back()->with('error', "Video ini memiliki {$paidOrders} pembelian lunas dan tidak dapat dihapus. Nonaktifkan saja agar riwayat pembelian user tetap aman.");
        }

        // Cegah "zombie order": pesanan pending yang menunjuk video yang sudah
        // terhapus tidak akan bisa diselesaikan/dirender lagi.
        $pendingOrders = $video->videoOrders()->where('payment_status', 'pending')->count();

        if ($pendingOrders > 0) {
            return redirect()->back()->with('error', "Video ini masih memiliki {$pendingOrders} pesanan pending. Tunggu pesanan selesai/kedaluwarsa sebelum menghapus video.");
        }

        if ($video->video_file) {
            Storage::disk('public')->delete($video->video_file);
        }
        if ($video->thumbnail) {
            Storage::disk('public')->delete($video->thumbnail);
        }

        $video->delete();

        return redirect()->route('admin.videos.index')->with('success', 'Video berhasil dihapus!');
    }

    public function toggleActive(Video $video)
    {
        $video->update(['is_active' => !$video->is_active]);

        $status = $video->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.videos.index')->with('success', "Video berhasil {$status}!");
    }

    public function ordersIndex(Request $request)
    {
        $query = VideoOrder::with(['user', 'video']);

        if ($request->filled('search')) {
            $search = $request->search;
            $escapedSearch = SearchHelper::escapeLike($search);
            $query->whereHas('user', function ($q) use ($escapedSearch) {
                $q->where('name', 'like', "%{$escapedSearch}%")
                  ->orWhere('email', 'like', "%{$escapedSearch}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('filter') && $request->filter === 'needs_activation') {
            $query->where('payment_status', 'paid')->where('access_granted', false);
        }

        if ($request->filled('filter') && $request->filter === 'paid_active') {
            $query->where('payment_status', 'paid')->where('access_granted', true);
        }

        $videoOrders = $query->latest()->paginate(10)->withQueryString();

        return view('admin.video-orders.index', compact('videoOrders'));
    }

    public function grantAccess(VideoOrder $videoOrder)
    {
        if ($videoOrder->payment_status !== 'paid') {
            return redirect()->back()->with('error', 'Pembayaran belum lunas!');
        }

        if (!$videoOrder->access_start || !$videoOrder->access_end) {
            $videoOrder->activateAccess();
        }

        $videoOrder->update(['access_granted' => true]);

        // Notify user about video access granted
        NotificationService::create(
            $videoOrder->user_id,
            'video',
            'Akses Video Diberikan',
            'Admin telah memberikan akses video "' . ($videoOrder->video->title ?? '-') . '". Akses kamu sudah aktif.',
            ['video_order_id' => $videoOrder->id, 'action_url' => route('videos.index')],
            true
        );

        return redirect()->route('admin.video-orders.index')->with('success', 'Akses video berhasil diberikan ke ' . ($videoOrder->user->name ?? 'User') . '!');
    }
}
