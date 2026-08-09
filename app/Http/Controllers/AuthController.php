<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View { return view('auth.login'); }

    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->validated();
        $credentials['is_active'] = true;

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Username atau password yang Anda masukkan salah.'], 422);
            }
            return back()->withInput($request->only('username', 'remember'))
                ->withErrors(['login' => 'Username atau password yang Anda masukkan salah.']);
        }

        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();

        $message = 'Selamat datang, '.$request->user()->name.'.';
        if ($request->expectsJson()) {
            session()->flash('success', $message);
            return response()->json(['message'=>$message, 'redirect'=>redirect()->intended(route('dashboard'))->getTargetUrl()]);
        }
        return redirect()->intended(route('dashboard'))->with('success', $message);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda berhasil keluar dari sistem.');
    }
}
