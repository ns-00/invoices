<?php

namespace Tests\Feature;

use App\Mail\PasswordVerificationCodeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_request_password_otp_code(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email'     => 'testuser@example.com',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('profile.send-code'));
        $response->assertSessionHas('ok');

        Mail::assertSent(PasswordVerificationCodeMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_authenticated_user_cannot_update_password_without_code(): void
    {
        $user = User::factory()->create([
            'password'  => 'OldPassword123',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'password'              => 'NewPassword2026',
            'password_confirmation' => 'NewPassword2026',
        ]);

        $response->assertSessionHasErrors('code');
        $user->refresh();
        $this->assertTrue(Hash::check('OldPassword123', $user->password));
    }

    public function test_authenticated_user_cannot_update_password_with_invalid_code(): void
    {
        $user = User::factory()->create([
            'email'     => 'user@example.com',
            'password'  => 'OldPassword123',
            'is_active' => true,
        ]);

        // Put a valid code in database
        DB::table('password_reset_tokens')->insert([
            'email'      => $user->email,
            'token'      => Hash::make('654321'),
            'created_at' => now(),
        ]);

        // Submit wrong code
        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'code'                  => '111111',
            'password'              => 'NewPassword2026',
            'password_confirmation' => 'NewPassword2026',
        ]);

        $response->assertSessionHasErrors('code');
        $user->refresh();
        $this->assertTrue(Hash::check('OldPassword123', $user->password));
    }

    public function test_authenticated_user_cannot_update_password_with_expired_code(): void
    {
        $user = User::factory()->create([
            'email'     => 'user@example.com',
            'password'  => 'OldPassword123',
            'is_active' => true,
        ]);

        // Insert code from 20 minutes ago (> 15 minutes)
        DB::table('password_reset_tokens')->insert([
            'email'      => $user->email,
            'token'      => Hash::make('654321'),
            'created_at' => Carbon::now()->subMinutes(20),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'code'                  => '654321',
            'password'              => 'NewPassword2026',
            'password_confirmation' => 'NewPassword2026',
        ]);

        $response->assertSessionHasErrors('code');
        $user->refresh();
        $this->assertTrue(Hash::check('OldPassword123', $user->password));
    }

    public function test_authenticated_user_can_update_password_with_valid_code(): void
    {
        $user = User::factory()->create([
            'email'     => 'valid@example.com',
            'password'  => 'OldPassword123',
            'is_active' => true,
        ]);

        $code = '789123';
        DB::table('password_reset_tokens')->insert([
            'email'      => $user->email,
            'token'      => Hash::make($code),
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'code'                  => $code,
            'password'              => 'BrandNewPassword2026',
            'password_confirmation' => 'BrandNewPassword2026',
        ]);

        $response->assertSessionHas('ok');

        $user->refresh();
        $this->assertTrue(Hash::check('BrandNewPassword2026', $user->password));

        // Token must be deleted after use
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_guest_can_request_reset_code_and_reset_password(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email'     => 'forgotten@example.com',
            'password'  => 'ForgottenPass123',
            'is_active' => true,
        ]);

        // Request code
        $requestResp = $this->post(route('password.email'), [
            'email' => 'forgotten@example.com',
        ]);

        $requestResp->assertRedirect(route('password.reset.form', ['email' => 'forgotten@example.com']));

        $sentCode = null;
        Mail::assertSent(PasswordVerificationCodeMail::class, function ($mail) use (&$sentCode, $user) {
            $sentCode = $mail->code;
            return $mail->hasTo($user->email);
        });

        $this->assertNotNull($sentCode);

        // Submit new password with the code
        $resetResp = $this->post(route('password.update'), [
            'email'                 => 'forgotten@example.com',
            'code'                  => $sentCode,
            'password'              => 'RestoredPassword2026',
            'password_confirmation' => 'RestoredPassword2026',
        ]);

        $resetResp->assertRedirect(route('login'));
        $resetResp->assertSessionHas('ok');

        $user->refresh();
        $this->assertTrue(Hash::check('RestoredPassword2026', $user->password));

        // Token must be deleted after use
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'forgotten@example.com',
        ]);
    }

    public function test_admin_also_must_use_otp_code_to_change_password(): void
    {
        $admin = User::factory()->create([
            'role'      => 'admin',
            'email'     => 'admin@example.com',
            'password'  => 'OldAdminPass123',
            'is_active' => true,
        ]);

        // Attempting to change without code fails
        $response = $this->actingAs($admin)->put(route('profile.password.update'), [
            'password'              => 'NewAdminPass2026',
            'password_confirmation' => 'NewAdminPass2026',
        ]);

        $response->assertSessionHasErrors('code');

        // With valid code succeeds
        $code = '456789';
        DB::table('password_reset_tokens')->insert([
            'email'      => $admin->email,
            'token'      => Hash::make($code),
            'created_at' => now(),
        ]);

        $successResp = $this->actingAs($admin)->put(route('profile.password.update'), [
            'code'                  => $code,
            'password'              => 'NewAdminPass2026',
            'password_confirmation' => 'NewAdminPass2026',
        ]);

        $successResp->assertSessionHas('ok');
        $admin->refresh();
        $this->assertTrue(Hash::check('NewAdminPass2026', $admin->password));
    }
}
