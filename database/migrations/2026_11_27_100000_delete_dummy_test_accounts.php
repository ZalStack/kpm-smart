<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $usernames = [
            'siswa',
            'adminrawamangun',
            'siswadummy',
            'siswarwm1',
            'siswarwm2',
            'siswarwm3',
            'siswarwm4',
            'siswarwm5',
            'siswarwm6',
            'siswarwm7',
            'siswarwm8',
            'siswarwm9',
            'siswarwm10',
        ];
        $emails = [
            'siswa@adikaka.com',
            'admin.rawamangun@kpm.com',
            'siswa.dummy@kpm.id',
            'siswa.dummy@adikaka.com',
            'siswa.rwm1@adikaka.com',
            'siswa.rwm2@adikaka.com',
            'siswa.rwm3@adikaka.com',
            'siswa.rwm4@adikaka.com',
            'siswa.rwm5@adikaka.com',
            'siswa.rwm6@adikaka.com',
            'siswa.rwm7@adikaka.com',
            'siswa.rwm8@adikaka.com',
            'siswa.rwm9@adikaka.com',
            'siswa.rwm10@adikaka.com',
        ];

        $users = DB::table('users')
            ->whereIn('username', $usernames)
            ->orWhereIn('email', $emails)
            ->get();

        $userIds = $users->pluck('id')->toArray();

        if (! empty($userIds)) {
            if (Schema::hasTable('practice_sessions')) {
                DB::table('practice_sessions')->whereIn('user_id', $userIds)->delete();
            }
            if (Schema::hasTable('user_points')) {
                DB::table('user_points')->whereIn('user_id', $userIds)->delete();
            }
            if (Schema::hasTable('user_achievements')) {
                DB::table('user_achievements')->whereIn('user_id', $userIds)->delete();
            }
            if (Schema::hasTable('notifications')) {
                DB::table('notifications')->whereIn('notifiable_id', $userIds)->delete();
            }
            DB::table('users')->whereIn('id', $userIds)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed for cleanup
    }
};
