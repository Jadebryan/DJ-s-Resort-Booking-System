<?php

namespace App\Exceptions;

use Illuminate\Contracts\Foundation\ExceptionRenderer;
use Illuminate\Support\Facades\View;
use Throwable;

class SimpleExceptionRenderer implements ExceptionRenderer
{
    /**
     * Renders the given exception as HTML using a simple view (avoids the broken
     * laravel-exceptions-renderer::topbar component when that namespace is incomplete).
     *
     * @param  \Throwable  $throwable
     * @return string
     */
    public function render($throwable)
    {
        $code = method_exists($throwable, 'getStatusCode') ? $throwable->getStatusCode() : 500;
        $message = $throwable->getMessage() ?: 'Server Error';
        $trace = config('app.debug') ? $throwable->getTraceAsString() : null;

        return View::make('errors.exception', [
            'code' => $code,
            'message' => $message,
            'exception' => $throwable,
            'trace' => $trace,
        ])->render();
    }
}
