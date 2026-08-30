<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

class AuthMiddleware implements Middleware {
    public function handle(Request $request, \Closure $next): mixed {
        if (!auth_check()) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            flash('warning', 'Please sign in to access this area.');
            Response::redirect('login');
        }
        return $next($request);
    }
}
