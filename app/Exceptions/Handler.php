<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler {
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e) {
        $response = parent::render($request, $e);
        $status = $response->getStatusCode();

        return match ($status) {
            403 => Inertia::render('403')->toResponse($request)->setStatusCode($status),
            404 => Inertia::render('404')->toResponse($request)->setStatusCode($status),
            // 500, 503 => Inertia::render('Error')->toResponse($request)->setStatusCode($status),
            default => $response
        };

        return $response;
    }
}
