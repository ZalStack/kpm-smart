<?php

namespace App\Support;

class UserAgentFormatter
{
    public static function shorten(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Tidak diketahui';
        }

        $browser = 'Browser Tidak Dikenal';

        if (preg_match('/Edg\//i', $userAgent)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/OPR\/|Opera/i', $userAgent)) {
            $browser = 'Opera';
        } elseif (preg_match('/Chrome\//i', $userAgent)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/Safari\//i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Firefox\//i', $userAgent)) {
            $browser = 'Mozilla Firefox';
        }

        $os = 'OS Tidak Dikenal';

        if (preg_match('/Windows NT 10/i', $userAgent)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/Windows/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Android/i', $userAgent)) {
            if (preg_match('/Android\s([\d.]+)/i', $userAgent, $m)) {
                $os = 'Android ' . $m[1];
            } else {
                $os = 'Android';
            }
        } elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            $os = 'iOS';
        } elseif (preg_match('/Mac OS X/i', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $os = 'Linux';
        }

        return "{$browser} • {$os}";
    }
}
