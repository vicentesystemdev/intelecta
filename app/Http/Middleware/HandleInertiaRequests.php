<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'roles' => $request->user() ? $request->user()->getRoleNames()->sort()->values() : [],
                'isSuperAdministrator' => $request->user()?->canChangeLoginEmail() ?? false,
                'security' => $request->user()?->canChangeLoginEmail() ?? false,
                'context' => $request->user()?->accessContext(),
                'homeUrl' => $request->user()?->homeRoute(),
                'organization' => [
                    'cargos' => $request->user()?->canManageOrganization('cargos.ver') ?? false,
                    'personal' => $request->user()?->canManageOrganization('personal.ver') ?? false,
                ],
                'permissions' => $request->user()
                    ? $request->user()->getAllPermissions()->pluck('name')->values()
                    : [],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
        ];
    }
}
