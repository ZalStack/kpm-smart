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
                    'username' => $request->user()->username,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'phone' => $request->user()->phone,
                    'student_class' => $request->user()->student_class,
                    'bidang' => $request->user()->bidang,
                    'level' => $request->user()->level,
                    'school_name' => $request->user()->school_name,
                    'address' => $request->user()->address,
                    'parent_name' => $request->user()->parent_name,
                    'parent_phone' => $request->user()->parent_phone,
                    'place_of_birth' => $request->user()->place_of_birth,
                    'date_of_birth' => $request->user()->date_of_birth,
                    'parent_profession' => $request->user()->parent_profession,
                    'parent_education' => $request->user()->parent_education,
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
