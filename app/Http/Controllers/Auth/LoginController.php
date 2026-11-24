<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'أدخل البريد الإلكتروني.',
            'email.email'       => 'صيغة البريد الإلكتروني غير صحيحة.',
            'password.required' => 'أدخل كلمة المرور.',
        ]);

        // الحساب المعطّل لا يستطيع الدخول
        $attempt = array_merge($credentials, ['is_active' => true]);

        if (Auth::attempt($attempt, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $target = $user->isAdmin() ? route('dashboard') : route('projects.index');

            return redirect()->intended($target);
        }

        return back()
            ->withErrors(['email' => 'بيانات الدخول غير صحيحة أو أن الحساب معطّل.'])
            ->onlyInput('email');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
