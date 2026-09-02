<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'phone', 'student_name', 'student_class', 'bidang', 'level', 'school_name', 'address', 'gender', 'religion', 'profile_photo', 'last_login_at'];

    protected $hidden = ['password', 'remember_token'];

    protected $guarded = ['role', 'is_verified', 'is_active', 'notifications', 'activity_logs'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'notifications' => 'array',
            'activity_logs' => 'array',
        ];
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=1E88E5&color=fff&size=256&bold=true';
    }

    public function practiceSessions()
    {
        return $this->hasMany(PracticeSession::class);
    }

    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class);
    }
}
