<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Inbound channel webhooks are machine-to-machine POSTs with no
        // browser session/CSRF token — auth is the adapter's own secret
        // check instead (see InboundMessageWebhookController).
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // With debug off (production), render 403/404/419/500/503 inside the
        // SPA's own Error.vue instead of Laravel's bare error pages, so a
        // user never leaves the app's design system. Left alone when debug
        // is on so the detailed Laravel/Whoops page stays available locally.
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if (! config('app.debug') && in_array($response->getStatusCode(), [403, 404, 419, 500, 503], true)) {
                return Inertia::render('Error', ['status' => $response->getStatusCode()])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            }

            return $response;
        });
    })->create();
