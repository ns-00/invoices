<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Month;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ReportAndFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_can_view_reports_page(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $project = Project::create(['user_id' => $user->id, 'name' => 'مشروع المقاولات']);
        $month = Month::create(['project_id' => $project->id, 'ym' => '2026-11', 'closed' => false]);

        $invoice = Invoice::create([
            'month_id'     => $month->id,
            'number'       => 'INV-9901',
            'invoice_date' => '2026-11-15',
            'description'  => 'شراء أدوات وتجهيزات',
            'amount'       => 1000.00,
            'has_due'      => true,
            'due_date'     => '2026-12-15',
            'due_paid'     => false,
        ]);

        $invoiceNoDue = Invoice::create([
            'month_id'     => $month->id,
            'number'       => 'INV-NODUE-1',
            'invoice_date' => '2026-11-16',
            'amount'       => 500.00,
            'has_due'      => false,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('تقرير فواتير المشتريات');
        $response->assertSee('جدول فواتير المشتريات');
        $response->assertSee('INV-9901');
        $response->assertSee('INV-NODUE-1');
        $response->assertSee('غير مستحق');
        $response->assertSee('sar.png');
        $response->assertDontSee('الجدول الثاني');

        // Test filtering by no_due
        $filterResp = $this->actingAs($user)->get(route('reports.index', ['paid_status' => 'no_due']));
        $filterResp->assertStatus(200);
        $filterResp->assertSee('INV-NODUE-1');
        $filterResp->assertDontSee('INV-9901');
    }

    public function test_user_can_export_reports_to_excel(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $project = Project::create(['user_id' => $user->id, 'name' => 'مشروع التجارة']);
        $month = Month::create(['project_id' => $project->id, 'ym' => '2026-11', 'closed' => false]);
        Invoice::create([
            'month_id'     => $month->id,
            'number'       => 'INV-EXCEL-1',
            'invoice_date' => '2026-11-10',
            'amount'       => 500.00,
        ]);

        $response = $this->actingAs($user)->get(route('reports.export.excel'));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), '.xlsx'));
    }

    public function test_user_can_export_reports_to_pdf(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $project = Project::create(['user_id' => $user->id, 'name' => 'مشروع التقنية']);
        $month = Month::create(['project_id' => $project->id, 'ym' => '2026-11', 'closed' => false]);
        Invoice::create([
            'month_id'     => $month->id,
            'number'       => 'INV-PDF-1',
            'invoice_date' => '2026-11-10',
            'amount'       => 2000.00,
        ]);

        $response = $this->actingAs($user)->get(route('reports.export.pdf'));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_image_upload_converts_to_webp_and_rejects_greater_than_5mb(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $project = Project::create(['user_id' => $user->id, 'name' => 'مشروع الفحص']);
        $month = Month::create(['project_id' => $project->id, 'ym' => '2026-11', 'closed' => false]);

        // 1. Image > 5MB (e.g. 6000 KB) fails validation
        $largeFile = UploadedFile::fake()->create('large_image.jpg', 6000, 'image/jpeg');
        $failResp = $this->actingAs($user)->post(route('months.invoices.store', $month), [
            'number'       => 'INV-FAIL-1',
            'invoice_date' => '2026-11-01',
            'amount'       => 100,
            'image'        => $largeFile,
        ]);
        $failResp->assertSessionHasErrors('image');

        // 2. Normal image converts to WebP and stores
        $validImage = UploadedFile::fake()->image('test_receipt.png', 100, 100);
        $passResp = $this->actingAs($user)->post(route('months.invoices.store', $month), [
            'number'       => 'INV-WEBP-1',
            'invoice_date' => '2026-11-01',
            'amount'       => 350.00,
            'image'        => $validImage,
        ]);

        $passResp->assertSessionHas('ok');

        $invoice = Invoice::where('number', 'INV-WEBP-1')->first();
        $this->assertNotNull($invoice);
        $this->assertNotNull($invoice->image_path);
        $this->assertStringEndsWith('.webp', $invoice->image_path);
    }

    public function test_spatie_activity_log_records_system_events(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($user);

        // Create project
        $projectResp = $this->post(route('projects.store'), ['name' => 'مشروع التتبع']);
        $project = Project::where('name', 'مشروع التتبع')->first();

        // Check activity log recorded
        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $user->id,
            'subject_id' => $project->id,
        ]);

        // View activity logs page
        $logPage = $this->get(route('activity-logs.index'));
        $logPage->assertStatus(200);
        $logPage->assertSee('مشروع التتبع');
    }

    public function test_viewer_role_can_only_view_and_cannot_modify_or_create(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer', 'is_active' => true]);
        $viewer->syncRoles(['viewer']);

        $owner = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $project = Project::create(['user_id' => $viewer->id, 'name' => 'مشروع العرض']);
        $month = Month::create(['project_id' => $project->id, 'ym' => '2026-11', 'closed' => false]);

        $this->actingAs($viewer);

        // Viewer can view reports and projects
        $this->get(route('reports.index'))->assertStatus(200);
        $this->get(route('projects.index'))->assertStatus(200);

        // Viewer CANNOT create project (403)
        $this->post(route('projects.store'), ['name' => 'مشروع ممنوع'])
            ->assertStatus(403);

        // Viewer CANNOT add invoice (403)
        $this->post(route('months.invoices.store', $month), [
            'number'       => 'INV-DENIED',
            'invoice_date' => '2026-11-01',
            'amount'       => 100,
        ])->assertStatus(403);
    }

    public function test_backup_command_and_backup_controller(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // Run artisan command
        $this->artisan('app:backup-run --source=Test')
            ->assertExitCode(0);

        // View backups page
        $resp = $this->actingAs($admin)->get(route('backups.index'));
        $resp->assertStatus(200);
        $resp->assertSee('إدارة النسخ الاحتياطية');
    }
}
