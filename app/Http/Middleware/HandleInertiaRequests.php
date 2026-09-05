<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'phone' => $request->user()->phone,
                    'student_class' => $request->user()->student_class,
                    'bidang' => $request->user()->bidang,
                    'level' => $request->user()->level,
                    'school_name' => $request->user()->school_name,
                    'address' => $request->user()->address,
                    'gender' => $request->user()->gender,
                    'religion' => $request->user()->religion,
                    'profile_photo' => $request->user()->profile_photo,
                    'is_active' => $request->user()->is_active,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'csrfToken' => fn () => csrf_token(),
        ];
    }
}
