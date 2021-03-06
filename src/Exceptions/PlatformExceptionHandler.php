<?php

namespace SuperV\Platform\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class PlatformExceptionHandler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [ValidationException::class];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof PlatformException) {
            return $exception->toResponse();
        }

        if ($exception instanceof ValidationException) {
            return $exception->toResponse();
        }

        if ($request->isJson() && $exception->getCode()) {
            return response()->json([
                'error' => [
                    'description' => $exception->getMessage(),
                    'stack'       => $exception->getTrace(),
                ],
            ], 500);
        }

        return parent::render($request, $exception);
    }

    protected function ___convertExceptionToArray(Exception $e)
    {
//        if (! $this->isHttpException($e) || Current::envIsLocal()) {
//            PlatformException::debug(['exception' => get_class($e)]);
//        }

        return PlatformException::debug(parent::convertExceptionToArray($e));
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request                 $request
     * @param \Illuminate\Auth\AuthenticationException $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        abort(401);

        return redirect()->guest(route(\Platform::port()->slug().'::login'));
    }
}
