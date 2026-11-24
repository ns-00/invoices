<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordVerificationCodeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'أدخل البريد الإلكتروني.',
            'email.email'    => 'صيغة البريد الإلكتروني غير صحيحة.',
        ]);

        $user = User::where('email', $request->email)->first();

        // لأسباب أمنية إذا لم يكن الحساب موجوداً أو معطلاً
        if (! $user || ! $user->is_active) {
            return back()->withErrors(['email' => 'البريد الإلكتروني غير مسجل في النظام أو أن الحساب معطّل.']);
        }

        $code = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token'      => Hash::make($code),
                'created_at' => now(),
            ]
        );

        Mail::to($user->email)->send(new PasswordVerificationCodeMail($user, $code));

        return redirect()->route('password.reset.form', ['email' => $user->email])
            ->with('ok', 'تم إرسال رمز التحقق (6 أرقام) إلى بريدك الإلكتروني بنجاح.');
    }

    public function showResetForm(Request $request)
    {
        $email = $request->query('email', old('email'));

        return view('auth.reset-password', compact('email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'code'     => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required'     => 'البريد الإلكتروني مطلوب.',
            'code.required'      => 'أدخل رمز التحقق المكون من 6 أرقام.',
            'code.size'          => 'رمز التحقق يجب أن يتكون من 6 أرقام.',
            'password.required'  => 'أدخل كلمة المرور الجديدة.',
            'password.min'       => 'كلمة المرور يجب ألا تقل عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record) {
            return back()->withErrors(['code' => 'لم يتم طلب رمز تحقق لهذا البريد أو انتهت صلاحيته.']);
        }

        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return back()->withErrors(['code' => 'انتهت صلاحية رمز التحقق (أكثر من 15 دقيقة). يرجى طلب رمز جديد.']);
        }

        if (! Hash::check($request->code, $record->token)) {
            return back()->withErrors(['code' => 'رمز التحقق غير صحيح. تأكد من الرقم المرسل إلى بريدك.']);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->update(['password' => $request->password]);
        }

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('ok', 'تم تغيير كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول.');
    }
}
