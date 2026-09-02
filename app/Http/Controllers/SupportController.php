<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\SearchHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    // User - Submit question via floating chat
    public function submitQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|min:3|max:1000',
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $sessionId = $request->session()->getId();

        $ticket = SupportTicket::create([
            'session_id' => $sessionId,
            'user_id' => auth()->check() ? auth()->id() : null,
            'name' => $request->name ?? (auth()->check() ? auth()->user()->name : null),
            'email' => $request->email ?? (auth()->check() ? auth()->user()->email : null),
            'question' => $request->question,
            'status' => 'pending',
        ]);

        // Notify all admins about new support ticket
        $admins = User::where('role', 'admin')->pluck('id');
        $senderName = $ticket->name ?? 'Anonim';
        foreach ($admins as $adminId) {
            NotificationService::create(
                $adminId,
                'support',
                'Tiket Bantuan Baru',
                $senderName . ' mengirim tiket bantuan baru: "' . \Illuminate\Support\Str::limit($request->question, 80) . '"',
                ['ticket_id' => $ticket->id, 'action_url' => route('admin.support.index')],
                true
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pertanyaan berhasil dikirim! Kami akan segera merespons.',
            'data' => $ticket
        ]);
    }

    // User - Get ticket status by session
    public function getTickets(Request $request)
    {
        $sessionId = $request->session()->getId();
        $userId = auth()->id();

        if ($userId) {
            $tickets = SupportTicket::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $tickets = SupportTicket::where('session_id', $sessionId)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    // Admin - Index
    public function adminIndex(Request $request)
    {
        $query = SupportTicket::query();

        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $escapedSearch = SearchHelper::escapeLike($search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('question', 'like', "%{$escapedSearch}%")
                    ->orWhere('answer', 'like', "%{$escapedSearch}%")
                    ->orWhere('name', 'like', "%{$escapedSearch}%")
                    ->orWhere('email', 'like', "%{$escapedSearch}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);

        $counts = [
            'total' => SupportTicket::count(),
            'pending' => SupportTicket::where('status', 'pending')->count(),
            'answered' => SupportTicket::where('status', 'answered')->count(),
            'closed' => SupportTicket::where('status', 'closed')->count(),
        ];

        return view('admin.support.index', compact('tickets', 'counts'));
    }

    // Admin - Show detail
    public function adminShow($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        return view('admin.support.show', compact('ticket'));
    }

    // Admin - Answer ticket
    public function adminAnswer(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'answer' => 'required|string|min:3|max:5000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $ticket = SupportTicket::findOrFail($id);
        $ticket->answer = $request->answer;
        $ticket->status = 'answered';
        $ticket->answered_at = now();
        $ticket->save();

        // Notify user about support ticket answer
        if ($ticket->user_id) {
            NotificationService::create(
                $ticket->user_id,
                'support',
                'Tiket Bantuan Dijawab',
                'Admin telah menjawab tiket bantuanmu: "' . \Illuminate\Support\Str::limit($ticket->question, 60) . '". Lihat jawabannya sekarang.',
                ['ticket_id' => $ticket->id, 'action_url' => route('support.tickets')],
                true
            );
        }

        return redirect()->route('admin.support.show', $ticket->id)
            ->with('success', 'Jawaban berhasil dikirim!');
    }

    // Admin - Update status
    public function adminUpdateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,answered,closed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid',
            ], 422);
        }

        $ticket = SupportTicket::findOrFail($id);
        $ticket->status = $request->status;

        if ($request->status === 'answered' && !$ticket->answered_at) {
            $ticket->answered_at = now();
        }

        $ticket->save();

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diupdate!',
            'data' => $ticket
        ]);
    }

    // Admin - Delete ticket
    public function adminDelete($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->delete();

        return redirect()->route('admin.support.index')
            ->with('success', 'Tiket berhasil dihapus!');
    }

    // Admin - Bulk delete
    public function adminBulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required',
        ]);

        $ids = $request->ids;
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada tiket yang dipilih!');
        }

        // Decode JSON if needed
        if (is_string($ids)) {
            $ids = json_decode($ids, true);
        }

        if (!is_array($ids) || empty($ids)) {
            return redirect()->back()->with('error', 'Format data tidak valid!');
        }

        $ids = array_filter($ids, 'is_numeric');
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Format data tidak valid!');
        }

        SupportTicket::whereIn('id', $ids)->delete();

        return redirect()->route('admin.support.index')
            ->with('success', count($ids) . ' tiket berhasil dihapus!');
    }

    // Admin - Export
    public function adminExport(Request $request)
    {
        $query = SupportTicket::query();

        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        $filename = 'support_tickets_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://memory', 'w');

        // Header
        fputcsv($handle, [
            'ID', 'Nama', 'Email', 'Pertanyaan', 'Jawaban', 'Status', 'Dibuat', 'Dijawab'
        ]);

        // Data
        foreach ($tickets as $ticket) {
            fputcsv($handle, [
                $ticket->id,
                $ticket->name ?? '-',
                $ticket->email ?? '-',
                $ticket->question,
                $ticket->answer ?? '-',
                ucfirst($ticket->status),
                $ticket->created_at->format('d/m/Y H:i'),
                $ticket->answered_at ? $ticket->answered_at->format('d/m/Y H:i') : '-'
            ]);
        }

        fseek($handle, 0);

        return response()->streamDownload(function () use ($handle) {
            fpassthru($handle);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
