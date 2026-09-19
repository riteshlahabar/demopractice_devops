<?php

use App\Exceptions\Auth\AccountRoleConflictException;
use App\Exceptions\Auth\DealerNotRegisteredException;
use App\Exceptions\Auth\InvalidPhoneCredentialException;
use App\Exceptions\Files\UnsupportedUploadException;
use App\Exceptions\Finance\PaymentGatewayException;
use App\Http\Middleware\AuthenticateApiToken;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureAdminPermission;
use App\Http\Middleware\IdentifyApiToken;
use App\Http\Middleware\SetApiLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Every API response is locale-aware: product and category names come back
        // translated without each endpoint having to ask for it.
        $middleware->api(prepend: [SetApiLocale::class]);

        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'admin.permission' => EnsureAdminPermission::class,
            'api.auth' => AuthenticateApiToken::class,
            // Public routes that still need to know who is calling (dealer pricing).
            'api.identify' => IdentifyApiToken::class,
        ]);
        // The bank POSTs its result from its own domain, so there is no
        // session token to send. The signature check in the controller is
        // what protects this route instead.
        $middleware->validateCsrfTokens(except: ['payments/eazypay/callback']);

        $middleware->redirectGuestsTo(fn (Request $request): ?string => $request->is('admin*') ? route('admin.login') : null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Phone verification is a domain concern and raises its own exception;
        // only the HTTP status for it is decided here, at the edge. 422 rather
        // than 401 because the caller has not authenticated at all yet.
        $exceptions->render(function (InvalidPhoneCredentialException $e, Request $request) {
            return $request->is('api/*')
                ? response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => []], 422)
                : back()->withInput()->with('error', $e->getMessage());
        });

        $exceptions->render(function (AccountRoleConflictException $e, Request $request) {
            return $request->is('api/*')
                ? response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => []], 422)
                : back()->withInput()->with('error', $e->getMessage());
        });

        $exceptions->render(function (DealerNotRegisteredException $e, Request $request) {
            return $request->is('api/*')
                ? response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => []], 422)
                : back()->withInput()->with('error', $e->getMessage());
        });

        // A gateway that is switched off or misconfigured is a 503, not a 500:
        // the request was fine, the payment route simply is not open.
        $exceptions->render(function (PaymentGatewayException $e, Request $request) {
            return $request->is('api/*')
                ? response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => []], 503)
                : back()->withInput()->with('error', $e->getMessage());
        });

        // The uploader is a domain service and raises its own exception; the
        // HTTP status for it is decided here, at the edge.
        $exceptions->render(function (UnsupportedUploadException $e, Request $request) {
            return $request->is('api/*')
                ? response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => []], 422)
                : back()->withInput()->with('error', $e->getMessage());
        });
    })->create();
