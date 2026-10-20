<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Jika user adalah WARGA dan belum mengisi email DAN phone_number
        if ($user && $user->role === 'WARGA' && empty($user->email) && empty($user->phone_number)) {
            // Jangan redirect jika sedang berada di halaman pengaturan
            if (! $request->routeIs('settings.*') && ! $request->is('logout')) {
                return redirect()->route('settings.index')->withErrors([
                    'profile' => 'Untuk keamanan dan kemudahan login, Anda wajib mengisi Email atau Nomor HP.',
                ]);
            }
        }

        return $next($request);
    }
}
