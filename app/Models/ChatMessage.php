<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'role',
        'message',
        'is_ai',
    ];

    protected $casts = [
        'is_ai' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
