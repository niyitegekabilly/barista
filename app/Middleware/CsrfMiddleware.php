<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Csrf;

class CsrfMiddleware implements Middleware {
    public function handle(Request $request, \Closure $next): mixed {
        if ($request->isPost()) {
            $token = $request->input('_csrf_token') ?? $request->header('X-CSRF-TOKEN');
            if (!Csrf::validate($token)) {
                if ($request->isAjax()) {
                    Response::json(['success' => false, 'message' => 'CSRF token mismatch. Please refresh.'], 419);
                }
                flash('danger', 'Your session expired or token was invalid. Please try again.');
                Response::back();
            }
        }
        return $next($request);
    }
}
