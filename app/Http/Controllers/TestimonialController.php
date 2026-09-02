<?php
// app/Http/Controllers/TestimonialController.php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TestimonialController extends Controller
{
    /**
     * Display a listing of testimonials for admin
     */
    public function adminIndex()
    {
        $testimonials = Testimonial::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $stats = [
            'total' => Testimonial::count(),
            'approved' => Testimonial::where('is_approved', true)->count(),
            'pending' => Testimonial::where('is_approved', false)->count(),
            'avg_rating' => Testimonial::where('is_approved', true)->avg('rating') ?? 0,
        ];

        // Perbaiki path view di sini
        return view('admin.testimoni.index', compact('testimonials', 'stats'));
    }

    /**
     * Store a newly created testimonial
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:10|max:500',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user already has a pending testimonial
        $existing = Testimonial::where('user_id', Auth::id())
            ->where('is_approved', false)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memiliki testimoni yang menunggu persetujuan.'
            ], 400);
        }

        $testimonial = Testimonial::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'rating' => $request->rating,
            'is_approved' => false,
            'is_active' => true,
        ]);

        // Notify all admins about new testimonial
        $user = Auth::user();
        $admins = User::where('role', 'admin')->pluck('id');
        foreach ($admins as $adminId) {
            NotificationService::create(
                $adminId,
                'testimonial',
                'Testimoni Baru',
                $user->name . ' mengirimkan testimoni baru (rating ' . $request->rating . '/5). Menunggu persetujuan.',
                ['testimonial_id' => $testimonial->id, 'action_url' => route('admin.testimonials.index')],
                true
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Testimoni berhasil dikirim dan menunggu persetujuan admin.',
            'data' => $testimonial->load('user')
        ]);
    }

    /**
     * Get user's own testimonial status
     */
    public function getUserTestimonial()
    {
        $testimonial = Testimonial::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $testimonial ? $testimonial->load('user') : null
        ]);
    }

    /**
     * Get approved testimonials for public display
     */
    public function getPublicTestimonials()
    {
        $testimonials = Testimonial::with('user')
            ->approved()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Keamanan: API publik hanya boleh mengekspos kolom yang aman.
        // Jangan bocorkan email/phone/school/activity_logs milik user.
        $data = $testimonials->map(function ($testimonial) {
            return [
                'id' => $testimonial->id,
                'message' => $testimonial->content,
                'rating' => $testimonial->rating,
                'created_at' => optional($testimonial->created_at)->toISOString(),
                'user' => $testimonial->user ? [
                    'name' => $testimonial->user->name,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Approve testimonial (admin)
     */
    public function approve(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);
        
        $testimonial->update([
            'is_approved' => true,
            'approved_at' => now()
        ]);

        // Notify user about testimonial approval
        NotificationService::create(
            $testimonial->user_id,
            'testimonial',
            'Testimoni Disetujui',
            'Testimoni kamu telah disetujui dan sekarang ditampilkan di website. Terima kasih atas feedbackmu!',
            ['testimonial_id' => $testimonial->id, 'action_url' => route('testimonials.my')],
            true
        );

        return response()->json([
            'success' => true,
            'message' => 'Testimoni berhasil disetujui.'
        ]);
    }

    /**
     * Toggle active status (admin)
     */
    public function toggleActive(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);
        
        $testimonial->update([
            'is_active' => !$testimonial->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status testimoni berhasil diubah.',
            'data' => $testimonial
        ]);
    }

    /**
     * Delete testimonial (admin)
     */
    public function destroy($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();

        return response()->json([
            'success' => true,
            'message' => 'Testimoni berhasil dihapus.'
        ]);
    }

    /**
     * Bulk delete testimonials (admin)
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $ids = $request->ids;

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada testimoni yang dipilih.'
            ], 400);
        }

        Testimonial::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($ids) . ' testimoni berhasil dihapus.'
        ]);
    }

    /**
     * Export testimonials to CSV (admin)
     */
    public function export()
    {
        $testimonials = Testimonial::with('user')
            ->approved()
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'testimoni_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($testimonials) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nama', 'Rating', 'Testimoni', 'Tanggal']);

            foreach ($testimonials as $testimonial) {
                fputcsv($file, [
                    $testimonial->user?->name ?? '-',
                    $testimonial->rating . ' ★',
                    $testimonial->content,
                    $testimonial->created_at->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}