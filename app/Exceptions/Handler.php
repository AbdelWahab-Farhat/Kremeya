<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->renderable(function (QueryException $e, $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            // MySQL duplicate entry
            $isDuplicate = ($e->getCode() === '23000') || (($e->errorInfo[1] ?? null) === 1062);

            if ($isDuplicate) {
                $msg = $e->getMessage();

                if (str_contains($msg, 'customers_phone_unique')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This phone already exists.',
                        'errors'  => ['phone' => ['This phone already exists.']],
                    ], 422);
                }

                if (str_contains($msg, 'users_email_unique')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This email already exists.',
                        'errors'  => ['email' => ['This email already exists.']],
                    ], 422);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate data.',
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Database error occurred.',
                'debug'   => app()->isLocal() ? $e->getMessage() : null,
            ], 500);
        });

        $this->renderable(function (Throwable $e, $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'debug'   => app()->isLocal() ? $e->getMessage() : null,
            ], 500);
        });
    }
}
