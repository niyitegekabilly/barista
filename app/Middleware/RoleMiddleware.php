<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

class RoleMiddleware implements Middleware {
    private array $allowedRoles;

    public function __construct(array|string $roles) {
        $this->allowedRoles = is_array($roles) ? $roles : explode(',', $roles);
    }

    public function handle(Request $request, \Closure $next): mixed {
        if (!auth_check()) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            Response::redirect('login');
        }

        $userRole = auth_role();
        // Super admin has access to everything
        if ($userRole === 'super_admin' || \App\Models\User::hasRole(auth_id(), 'super_admin')) {
            return $next($request);
        }

        $hasAccess = in_array($userRole, $this->allowedRoles, true);
        if (!$hasAccess) {
            foreach ($this->allowedRoles as $allowedRole) {
                if (\App\Models\User::hasRole(auth_id(), trim($allowedRole))) {
                    $hasAccess = true;
                    break;
                }
            }
        }

        if (!$hasAccess) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
            http_response_code(403);
            echo \App\Core\View::render('errors/403', ['title' => 'Forbidden Access'], 'layouts/main');
            exit;
        }

        return $next($request);
    }
}
