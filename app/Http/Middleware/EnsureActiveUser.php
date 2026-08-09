<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class EnsureActiveUser
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && !$request->user()->is_active) { Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('login')->withErrors(['login'=>'Akun Anda telah dinonaktifkan. Hubungi administrator.']); }
        return $next($request);
    }
}
