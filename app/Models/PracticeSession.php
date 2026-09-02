<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticeSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'package_id', 'order_id', 'card_id',
        'total_question', 'correct_answer', 'wrong_answer',
        'unanswered', 'total_score', 'duration_seconds',
        'started_at', 'finished_at', 'status', 'answers'
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'answers' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
