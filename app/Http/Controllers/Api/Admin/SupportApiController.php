<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupportApiController extends BaseApiController
{
    /**
     * Get paginated support tickets
     */
    public function index(Request $request): JsonResponse
    {
        $query = SupportTicket::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('question', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->sendResponse($tickets, 'Daftar tiket bantuan berhasil dimuat.');
    }

    /**
     * Get support ticket detail
     */
    public function show($id): JsonResponse
    {
        $ticket = SupportTicket::find($id);

        if (!$ticket) {
            return $this->sendError('Tiket bantuan tidak ditemukan.', [], 404);
        }

        return $this->sendResponse($ticket, 'Detail tiket bantuan berhasil dimuat.');
    }
}
