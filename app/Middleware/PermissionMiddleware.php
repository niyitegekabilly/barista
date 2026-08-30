<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;

/**
 * Gates a route by a granular permission slug (e.g. `permission:courses.publish`)
 * instead of a fixed role list — so an admin can grant a specific ability to a
 * role (like Instructor) via /admin/roles without needing a code change or a
 * broader role allow-list on the route itself.
 */
class PermissionMiddleware implements Middleware {
    public function __construct(private string $permissionSlug) {
    }

    public function handle(Request $request, \Closure $next): mixed {
        if (!auth_check()) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            }
            Response::redirect('login');
        }

        if (!User::hasPermission((int)auth_id(), $this->permissionSlug)) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'message' => 'You do not have permission to perform this action.'], 403);
            }
            http_response_code(403);
            echo \App\Core\View::render('errors/403', ['title' => 'Forbidden Access'], 'layouts/main');
            exit;
        }

        return $next($request);
    }
}
