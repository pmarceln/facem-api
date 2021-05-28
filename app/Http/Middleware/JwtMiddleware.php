<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Entities\User;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        $token = $request->bearerToken();

        if(!$token) {
            // Unauthorized response if token not there
            return response()->json(null, Response::HTTP_UNAUTHORIZED);
        }
        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch(ExpiredException $e) {
            return response()->json(null, Response::HTTP_UNAUTHORIZED);
        } catch(Exception $e) {
            return response()->json(null, Response::HTTP_UNAUTHORIZED);
        }
        $user = User::find($credentials->sub);
        // Now let's put the user in the request class so that you can grab it from there
        $request->auth = $user;
        return $next($request);
    }
}
