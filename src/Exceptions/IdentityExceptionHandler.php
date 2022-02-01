<?php

namespace Zploited\Laravel\Identity\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Request;

class IdentityExceptionHandler extends Handler
{
    protected $dontReport = [];
    protected $dontFlash = [];

    public function register()
    {
        $this->renderable(function (AuthorizationException $e, Request $request) {
            if($request->expectsJson()) {
                return response()->json([
                    'error' => 'access_denied',
                    'error_description' => $e->getMessage()
                ], 401);
            } else {
                abort(401, $e->getMessage());
            }
        });
    }
}