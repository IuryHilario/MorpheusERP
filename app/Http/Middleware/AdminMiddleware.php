<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()?->tipo_Usuario !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Acesso restrito a administradores.'
                ], 403);
            }

            return redirect()->route('home')->with('error', 'Acesso restrito a administradores.');
        }

        return $next($request);
    }
}
