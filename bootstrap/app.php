<?php

use App\Http\Middleware\EnsureAccountAccess;
use App\Http\Middleware\EnsureAdministrativeAccess;
use App\Http\Middleware\EnsureOrganizationAccess;
use App\Http\Middleware\EnsureStudentAccess;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Inertia\Inertia;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Resolve account identity before binding academic resources to avoid existence leaks.
        $middleware->prependToPriorityList(
            SubstituteBindings::class,
            EnsureStudentAccess::class,
        );
        $middleware->prependToPriorityList(EnsureStudentAccess::class, EnsureAccountAccess::class);
        $middleware->web(append: [
            EnsureAccountAccess::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'administrative' => EnsureAdministrativeAccess::class,
            'organization' => EnsureOrganizationAccess::class,
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $forbidden = fn (Request $request) => Inertia::render('Errors/Forbidden')
            ->toResponse($request)
            ->setStatusCode(403);

        $exceptions->render(
            fn (UnauthorizedException $exception, Request $request) => $forbidden($request),
        );
        $exceptions->render(
            fn (AuthorizationException $exception, Request $request) => $forbidden($request),
        );
    })->create();
