<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Month;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_project_and_add_month_and_invoices(): void
    {
        $admin = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // 1. Authenticate as admin
        $this->actingAs($admin);

        // 2. Access projects index
        $response = $this->get(route('projects.index'));
        $response->assertStatus(200);
        $response->assertSee('المشاريع');

        // 3. Create a project
        $postProject = $this->post(route('projects.store'), [
            'name' => 'مشروع برج الرياض',
        ]);
        $project = Project::first();
        $this->assertNotNull($project);
        $this->assertEquals('مشروع برج الرياض', $project->name);
        $postProject->assertRedirect(route('projects.show', $project));

        // 4. Open a month
        $postMonth = $this->post(route('projects.months.store', $project), [
            'ym' => '2026-11',
        ]);
        $month = Month::first();
        $this->assertNotNull($month);
        $postMonth->assertRedirect(route('months.show', [$project, $month]));

        // Assert projects.show renders cleanly with months
        $showResponse = $this->get(route('projects.show', $project));
        $showResponse->assertStatus(200);
        $showResponse->assertSee($month->label);

        // 5. Add an invoice
        $postInvoice = $this->post(route('months.invoices.store', $month), [
            'number'       => 'INV-001',
            'invoice_date' => '2026-11-15',
            'description'  => 'شراء أدوات كهربائية',
            'amount'       => 1000.00,
            'has_due'      => 1,
            'due_date'     => '2026-11-25',
        ]);
        $postInvoice->assertSessionHas('ok');

        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        $this->assertEquals('INV-001', $invoice->number);
        $this->assertEquals(1000.00, $invoice->amount);
        $this->assertEquals(150.00, $invoice->tax);
        $this->assertEquals(1150.00, $invoice->total);
        $this->assertEquals(50.00, $invoice->due_amount);
        $this->assertFalse($invoice->due_paid);

        // 6. Toggle paid status
        $toggleResp = $this->post(route('invoices.toggle-paid', $invoice));
        $invoice->refresh();
        $this->assertTrue($invoice->due_paid);
        $this->assertNotNull($invoice->due_paid_at);

        // 7. Toggle close month (should auto-open next month 2026-12)
        $toggleMonth = $this->post(route('months.toggle', $month));
        $month->refresh();
        $this->assertTrue($month->closed);

        $nextMonth = Month::where('ym', '2026-12')->first();
        $this->assertNotNull($nextMonth);
        $this->assertEquals('2026-12', $nextMonth->ym);
        $this->assertFalse($nextMonth->closed);
    }
}
