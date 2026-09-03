<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $leaveRequests = LeaveRequest::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return Inertia::render('Practice/LeaveRequestIndex', [
            'leaveRequests' => $leaveRequests,
        ]);
    }

    public function create()
    {
        return Inertia::render('Practice/LeaveRequestForm', []);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:500',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('leave-proofs', 'public');
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => Auth::id(),
            'reason' => $validated['reason'],
            'proof_file' => $proofPath,
            'status' => 'pending',
        ]);

        $admins = User::where('role', 'admin')->pluck('id');
        foreach ($admins as $adminId) {
            NotificationService::create(
                $adminId,
                'leave',
                'Pengajuan Izin Baru',
                Auth::user()->name . ' mengajukan izin: "' . \Illuminate\Support\Str::limit($validated['reason'], 80) . '"',
                ['leave_request_id' => $leaveRequest->id, 'action_url' => route('admin.leave-requests.index')],
                true
            );
        }

        return redirect()->route('leave-requests.index')
            ->with('success', 'Pengajuan izin berhasil dikirim!');
    }

    // Admin
    public function adminIndex()
    {
        $leaveRequests = LeaveRequest::with('user')
            ->latest()
            ->paginate(15);

        $counts = [
            'total' => LeaveRequest::count(),
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/LeaveRequests/LeaveRequestIndex', [
            'leaveRequests' => $leaveRequests,
            'counts' => $counts,
        ]);
    }

    public function adminShow($id)
    {
        $leaveRequest = LeaveRequest::with('user')->findOrFail($id);

        return Inertia::render('Admin/LeaveRequests/LeaveRequestShow', [
            'leaveRequest' => $leaveRequest,
        ]);
    }

    public function adminUpdateStatus(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $leaveRequest->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? null,
        ]);

        NotificationService::create(
            $leaveRequest->user_id,
            'leave',
            'Pengajuan Izin ' . ($validated['status'] === 'approved' ? 'Disetujui' : 'Ditolak'),
            'Pengajuan izin anda telah ' . ($validated['status'] === 'approved' ? 'disetujui' : 'ditolak') . ' oleh admin.',
            ['leave_request_id' => $leaveRequest->id, 'action_url' => route('leave-requests.index')],
            true
        );

        return redirect()->back()->with('success', 'Status pengajuan izin berhasil diperbarui.');
    }

    public function adminDestroy($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->proof_file) {
            Storage::disk('public')->delete($leaveRequest->proof_file);
        }

        $leaveRequest->delete();

        return redirect()->route('admin.leave-requests.index')
            ->with('success', 'Pengajuan izin berhasil dihapus.');
    }
}
