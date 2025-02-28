<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!$request->user() || $request->user()->role->name !== $role) {
            abort(403, 'Bạn không có quyền truy cập chức năng này');
        }
        
        return $next($request);
    }
}