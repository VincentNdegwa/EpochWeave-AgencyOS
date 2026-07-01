<?php

namespace Tests\Feature\Account;

use App\Models\Industry;
use App\Models\LeadSource;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class AccountImportTest extends TestCase
{
    use RefreshDatabase;

    private function createCsvFile(string $content): UploadedFile
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'accounts_import_');
        file_put_contents($tmpPath, $content);

        return new UploadedFile($tmpPath, 'accounts.csv', 'text/csv', null, true);
    }

    private function createXlsxFile(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1', true);

        $tmpPath = tempnam(sys_get_temp_dir(), 'accounts_import_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return new UploadedFile($tmpPath, 'accounts.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    private function setupUserAndWorkspace(): array
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->addRole($role, $workspace);

        return [$user, $workspace];
    }

    public function test_import_page_loads(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/accounts/import');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('account/import')
                ->has('industries')
                ->has('lead_sources')
                ->has('company_sizes'),
            );
    }

    public function test_user_can_download_import_template(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->get('/accounts/import-template');

        $response->assertOk();
        $response->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );
        $response->assertHeader('Content-Disposition', 'attachment; filename="accounts_import_template.xlsx"');
    }

    public function test_import_preview_returns_headers_and_rows(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $csv = <<<'CSV'
Company,Phone,Email
Acme Corp,+1 555 1234,john@acme.example.com
Globex Inc,+1 555 5678,
CSV;

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->postJson('/accounts/import-preview', [
                'file' => $this->createCsvFile($csv),
            ]);

        $response->assertOk()
            ->assertJson([
                'headers' => ['Company', 'Phone', 'Email'],
                'rows' => [
                    ['Acme Corp', '+1 555 1234', 'john@acme.example.com'],
                    ['Globex Inc', '+1 555 5678', ''],
                ],
            ]);
    }

    public function test_import_creates_accounts_with_mapping(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $csv = <<<'CSV'
Company,Phone,Website,Employees,Revenue
Acme Corp,+1 555 1234,https://acme.example.com,50,1000000
CSV;

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'file' => $this->createCsvFile($csv),
                'mapping' => [
                    'company_name' => 'Company',
                    'phone' => 'Phone',
                    'website' => 'Website',
                    'employee_count' => 'Employees',
                    'annual_revenue' => 'Revenue',
                ],
                'bulk' => ['status' => 'lead'],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('accounts', [
            'company_name' => 'Acme Corp',
            'workspace_id' => $workspace->id,
            'phone' => '+1 555 1234',
            'website' => 'https://acme.example.com',
            'employee_count' => 50,
            'annual_revenue' => '1000000.00',
            'status' => 'lead',
        ]);
    }

    public function test_import_creates_accounts_from_xlsx_file(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $rows = [
            ['Company', 'Phone', 'Website', 'Employees', 'Revenue'],
            ['Acme Corp', '+1 555 1234', 'https://acme.example.com', 50, 1000000],
        ];

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'file' => $this->createXlsxFile($rows),
                'mapping' => [
                    'company_name' => 'Company',
                    'phone' => 'Phone',
                    'website' => 'Website',
                    'employee_count' => 'Employees',
                    'annual_revenue' => 'Revenue',
                ],
                'bulk' => ['status' => 'opportunity'],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('accounts', [
            'company_name' => 'Acme Corp',
            'workspace_id' => $workspace->id,
            'phone' => '+1 555 1234',
            'website' => 'https://acme.example.com',
            'employee_count' => 50,
            'annual_revenue' => '1000000.00',
            'status' => 'opportunity',
        ]);
    }

    public function test_import_creates_accounts_with_contacts(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $csv = <<<'CSV'
Company,First Name,Last Name,Email,Contact Phone
Acme Corp,John,Doe,john@acme.example.com,+1 555 5678
CSV;

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'file' => $this->createCsvFile($csv),
                'mapping' => [
                    'company_name' => 'Company',
                    'contact_first_name' => 'First Name',
                    'contact_last_name' => 'Last Name',
                    'contact_email' => 'Email',
                    'contact_phone' => 'Contact Phone',
                ],
                'bulk' => ['status' => 'lead'],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('accounts', [
            'company_name' => 'Acme Corp',
            'workspace_id' => $workspace->id,
        ]);
        $this->assertDatabaseHas('account_contacts', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@acme.example.com',
            'phone' => '+1 555 5678',
            'is_primary' => true,
        ]);
    }

    public function test_import_applies_bulk_options(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();
        $industry = Industry::factory()->create(['workspace_id' => $workspace->id, 'name' => 'Technology']);
        $leadSource = LeadSource::factory()->create(['workspace_id' => $workspace->id, 'name' => 'Website']);

        $csv = <<<'CSV'
Company
Acme Corp
CSV;

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'file' => $this->createCsvFile($csv),
                'mapping' => ['company_name' => 'Company'],
                'bulk' => [
                    'status' => 'opportunity',
                    'industry_id' => $industry->id,
                    'lead_source_id' => $leadSource->id,
                ],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('accounts', [
            'company_name' => 'Acme Corp',
            'status' => 'opportunity',
            'industry_id' => $industry->id,
            'lead_source_id' => $leadSource->id,
        ]);
    }

    public function test_import_rejects_missing_company_name_mapping(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $csv = <<<'CSV'
Company
Acme Corp
CSV;

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'file' => $this->createCsvFile($csv),
                'mapping' => [],
                'bulk' => ['status' => 'lead'],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('accounts', 0);
    }

    public function test_import_reports_errors_for_invalid_rows(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $csv = <<<'CSV'
Company
,lead
Acme Corp
CSV;

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'file' => $this->createCsvFile($csv),
                'mapping' => ['company_name' => 'Company'],
                'bulk' => ['status' => 'invalid-status'],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('accounts', 0);
    }

    public function test_import_rejects_bulk_options_from_other_workspace(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();
        $otherWorkspace = Workspace::factory()->create();
        $industry = Industry::factory()->create(['workspace_id' => $otherWorkspace->id]);

        $csv = <<<'CSV'
Company
Acme Corp
CSV;

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'file' => $this->createCsvFile($csv),
                'mapping' => ['company_name' => 'Company'],
                'bulk' => ['industry_id' => $industry->id],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('accounts', 0);
    }

    public function test_import_rejects_non_csv_files(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'file' => UploadedFile::fake()->create('accounts.pdf', 100, 'application/pdf'),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('accounts', 0);
    }
}
