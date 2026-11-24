<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Month;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRoleSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_access_dashboard_and_is_redirected_to_projects(): void
    {
        $user = User::factory()->create([
            'role'      => 'user',
            'is_active' => true,
        ]);

        // Regular user accessing /dashboard is forbidden or redirected to /projects
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertRedirect(route('projects.index'));

        // Regular user accessing /users is forbidden (403)
        $usersResponse = $this->actingAs($user)->get(route('users.index'));
        $usersResponse->assertStatus(403);
    }

    public function test_regular_user_navbar_does_not_contain_dashboard_or_users_management(): void
    {
        $user = User::factory()->create([
            'role'      => 'user',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('projects.index'));
        $response->assertStatus(200);
        $response->assertSee('المشاريع والفواتير');
        $response->assertDontSee('لوحة التحكم');
        $response->assertDontSee('المستخدمون');
        $response->assertSee(route('profile.edit'));
    }

    public function test_regular_user_can_update_their_profile(): void
    {
        $user = User::factory()->create([
            'name'      => 'أحمد محمد',
            'email'     => 'ahmed@example.com',
            'password'  => 'OldPassword123',
            'role'      => 'user',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        // Access profile page
        $page = $this->get(route('profile.edit'));
        $page->assertStatus(200);
        $page->assertSee('بيانات الحساب الشخصي');

        // Update name and email
        $updateResp = $this->put(route('profile.update'), [
            'name'  => 'أحمد علي',
            'email' => 'ahmed.new@example.com',
        ]);

        $updateResp->assertSessionHas('ok');

        $user->refresh();
        $this->assertEquals('أحمد علي', $user->name);
        $this->assertEquals('ahmed.new@example.com', $user->email);
        $this->assertEquals('user', $user->role); // Role must remain user
    }

    public function test_admin_can_access_dashboard_and_users(): void
    {
        $admin = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('لوحة التحكم');

        $usersResponse = $this->actingAs($admin)->get(route('users.index'));
        $usersResponse->assertStatus(200);
    }

    public function test_admin_cannot_access_or_modify_another_users_projects_or_invoices(): void
    {
        $userA = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // User A has a project, month, and invoice
        $projectA = Project::create(['name' => 'مشروع المستخدم أ', 'user_id' => $userA->id]);
        $monthA = $projectA->months()->create(['ym' => '2026-11', 'closed' => false]);
        $invoiceA = $monthA->invoices()->create([
            'number'       => 'INV-A-1',
            'invoice_date' => '2026-11-20',
            'amount'       => 500,
            'has_due'      => false,
        ]);

        // Admin logs in
        $this->actingAs($admin);

        // 1. In projects index, admin does NOT see User A's project
        $indexResp = $this->get(route('projects.index'));
        $indexResp->assertStatus(200);
        $indexResp->assertDontSee('مشروع المستخدم أ');

        // 2. Admin cannot view User A's project
        $this->get(route('projects.show', $projectA))->assertStatus(403);

        // 3. Admin cannot update User A's project
        $this->put(route('projects.update', $projectA), ['name' => 'محاولة اختراق'])->assertStatus(403);

        // 4. Admin cannot delete User A's project
        $this->delete(route('projects.destroy', $projectA))->assertStatus(403);

        // 5. Admin cannot add month to User A's project
        $this->post(route('projects.months.store', $projectA), ['ym' => '2026-12'])->assertStatus(403);

        // 6. Admin cannot view User A's month
        $this->get(route('months.show', [$projectA, $monthA]))->assertStatus(403);

        // 7. Admin cannot add invoice to User A's month
        $this->post(route('months.invoices.store', $monthA), [
            'number'       => 'HACK-1',
            'invoice_date' => '2026-11-21',
            'amount'       => 100,
        ])->assertStatus(403);

        // 8. Admin cannot update User A's invoice
        $this->put(route('invoices.update', $invoiceA), [
            'number'       => 'MOD-1',
            'invoice_date' => '2026-11-21',
            'amount'       => 999,
        ])->assertStatus(403);

        // 9. Admin cannot delete User A's invoice
        $this->delete(route('invoices.destroy', $invoiceA))->assertStatus(403);

        // 10. Admin CAN create their own project, which is strictly assigned to the admin's id
        $postAdminProject = $this->post(route('projects.store'), ['name' => 'مشروع خاص بالمدير']);
        $adminProject = Project::where('name', 'مشروع خاص بالمدير')->first();
        $this->assertNotNull($adminProject);
        $this->assertEquals($admin->id, $adminProject->user_id);
    }
}
