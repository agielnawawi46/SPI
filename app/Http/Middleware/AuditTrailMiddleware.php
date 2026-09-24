<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditTrailMiddleware
{
    /**
     * Actions/routes to SKIP logging (too noisy).
     */
    private array $skipRoutes = [
        'notifications.*',
        'api.*',
    ];

    /**
     * HTTP methods that modify data — always log.
     */
    private array $logMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log authenticated users
        if (!Auth::check()) {
            return $response;
        }

        // Skip noise routes
        foreach ($this->skipRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return $response;
            }
        }

        // Only log data-modifying requests and page views of important resources
        if (!in_array($request->method(), $this->logMethods)) {
            return $response;
        }

        // Don't log failed requests
        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        $action = match($request->method()) {
            'POST'   => 'create',
            'PUT',
            'PATCH'  => 'update',
            'DELETE' => 'delete',
            default  => 'action',
        };

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => $action,
            'description' => strtoupper($request->method()) . ' ' . $request->path(),
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'url'         => $request->fullUrl(),
        ]);

        return $response;
    }
}
