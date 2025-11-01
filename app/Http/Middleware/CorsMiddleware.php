<?php namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        // Allow requests from any origin. You can restrict this to specific origins for security.
        header('Access-Control-Allow-Origin: *');
        // Add other CORS headers as needed, like those for allowed methods and headers.
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, Authorization, Accept');
        header('Access-Control-Allow-Credentials: true');

        // Handle the OPTIONS method pre-flight request
        if ($request->isMethod('OPTIONS')) {
            return response('', 200);
        }

        return $next($request);
    }
}