<?php

namespace App\Http\Controllers;

use App\Mail\PasswordVerificationCodeMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'name.required'  => 'حقل الاسم مطلوب.',
            'name.max'       => 'الاسم طويل جداً.',
            'email.required' => 'حقل البريد الإلكتروني مطلوب.',
            'email.email'    => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique'   => 'هذا البريد الإلكتروني مستخدم مسبقاً.',
        ]);

        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        return back()->with('ok', 'تم تحديث بياناتك الشخصية بنجاح.');
    }

    public function sendPasswordCode(Request $request)
    {
        $user = $request->user();

        // توليد رمز تحقق عشوائي من 6 أرقام
        $code = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token'      => Hash::make($code),
                'created_at' => now(),
            ]
        );

        Mail::to($user->email)->send(new PasswordVerificationCodeMail($user, $code));

        if ($request->wantsJson()) {
            return response()->json([
                'ok'      => true,
                'message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني (' . $user->email . ').',
            ]);
        }

        return back()->with('ok', 'تم إرسال رمز التحقق (6 أرقام) إلى بريدك الإلكتروني (' . $user->email . '). الرمز صالح لمدة 15 دقيقة.');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'code'     => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'code.required'      => 'أدخل رمز التحقق المكون من 6 أرقام.',
            'code.size'          => 'رمز التحقق يجب أن يتكون من 6 أرقام.',
            'password.required'  => 'أدخل كلمة المرور الجديدة.',
            'password.min'       => 'كلمة المرور يجب ألا تقل عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->first();

        if (! $record) {
            return back()->withErrors(['code' => 'لم يتم طلب رمز تحقق، أو انتهت صلاحيته. اضغط على «إرسال رمز التحقق» أولاً.']);
        }

        // التحقق من صلاحية الرمز (15 دقيقة)
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            return back()->withErrors(['code' => 'انتهت صلاحية رمز التحقق (مر أكثر من 15 دقيقة). يرجى طلب رمز جديد.']);
        }

        // التحقق من مطابقة الرمز
        if (! Hash::check($request->code, $record->token)) {
            return back()->withErrors(['code' => 'رمز التحقق غير صحيح. تأكد من إدخال الرمز المكون من 6 أرقام المرسل إلى بريدك.']);
        }

        // تحديث كلمة المرور
        $user->update([
            'password' => $request->password,
        ]);

        // حذف الرمز لمنع إعادة استخدامه
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return back()->with('ok', 'تم تغيير كلمة المرور بنجاح. أصبحت كلمة المرور الجديدة سارية المفعول.');
    }
}
