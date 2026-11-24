<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepo
    ) {}

    public function index()
    {
        return view('users.index', ['users' => $this->userRepo->all()]);
    }

    public function create()
    {
        return view('users.form', ['user' => new User(['role' => 'user', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $user = User::create($data);
        $user->syncRoles([$data['role']]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("قام بإنشاء مستخدم جديد: {$user->name} بصلاحية ({$user->role})");

        return redirect()->route('users.index')->with('ok', 'تمت إضافة المستخدم بنجاح.');
    }

    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);

        if ($user->isAdmin() && ($data['role'] !== 'admin' || ! $data['is_active']) && $this->isLastAdmin($user)) {
            return back()->withInput()->withErrors(['role' => 'لا يمكن تعطيل أو تخفيض آخر مدير في النظام.']);
        }
        if ($user->is($request->user()) && ! $data['is_active']) {
            return back()->withInput()->withErrors(['is_active' => 'لا يمكنك تعطيل حسابك الحالي.']);
        }

        if (empty($data['password'])) {
            unset($data['password']); // تركها فارغة = بدون تغيير
        }
        $user->update($data);
        $user->syncRoles([$data['role']]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("قام بتعديل بيانات وصلاحيات المستخدم: {$user->name}");

        $to = $request->user()->fresh()->isAdmin() ? 'users.index' : 'dashboard';

        return redirect()->route($to)->with('ok', 'تم تحديث بيانات المستخدم.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'لا يمكنك حذف حسابك الحالي.']);
        }
        if ($user->isAdmin() && $this->isLastAdmin($user)) {
            return back()->withErrors(['user' => 'لا يمكن حذف آخر مدير في النظام.']);
        }
        if ($user->projects()->exists()) {
            return back()->withErrors(['user' => 'لا يمكن حذف مستخدم لديه مشاريع. عطّل حسابه بدلاً من حذفه.']);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("قام بحذف المستخدم: {$user->name}");

        $user->delete();

        return back()->with('ok', 'تم حذف المستخدم.');
    }

    private function isLastAdmin(User $user): bool
    {
        return ! User::where('role', 'admin')->where('is_active', true)->where('id', '!=', $user->id)->exists();
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user)],
            'role'     => ['required', Rule::in(['admin', 'user', 'viewer'])],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ], [
            'required'  => 'حقل :attribute مطلوب.',
            'email'     => 'صيغة البريد الإلكتروني غير صحيحة.',
            'unique'    => 'هذا البريد الإلكتروني مستخدم مسبقاً.',
            'min'       => 'كلمة المرور يجب ألا تقل عن :min أحرف.',
            'confirmed' => 'تأكيد كلمة المرور غير مطابق.',
            'max'       => 'حقل :attribute طويل جداً.',
            'in'        => 'الصلاحية غير صحيحة.',
        ], [
            'name' => 'الاسم', 'email' => 'البريد الإلكتروني', 'role' => 'الصلاحية', 'password' => 'كلمة المرور',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
