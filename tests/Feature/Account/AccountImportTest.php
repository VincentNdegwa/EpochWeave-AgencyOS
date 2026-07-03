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
        $spreadsheet = new Spreadsheet;
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

    public function test_import_creates_accounts_from_data(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'accounts' => [
                    [
                        'company_name' => 'Acme Corp',
                        'phone' => '+1 555 1234',
                        'website' => 'https://acme.example.com',
                        'status' => 'lead',
                    ],
                ],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('accounts', [
            'company_name' => 'Acme Corp',
            'workspace_id' => $workspace->id,
            'phone' => '+1 555 1234',
            'website' => 'https://acme.example.com',
            'status' => 'lead',
        ]);
    }

    public function test_import_creates_accounts_with_opportunity_status(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'accounts' => [
                    [
                        'company_name' => 'Acme Corp',
                        'phone' => '+1 555 1234',
                        'website' => 'https://acme.example.com',
                        'status' => 'opportunity',
                    ],
                ],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('accounts', [
            'company_name' => 'Acme Corp',
            'workspace_id' => $workspace->id,
            'phone' => '+1 555 1234',
            'website' => 'https://acme.example.com',
            'status' => 'opportunity',
        ]);
    }

    public function test_import_creates_accounts_with_contacts(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'accounts' => [
                    [
                        'company_name' => 'Acme Corp',
                        'contact_first_name' => 'John',
                        'contact_last_name' => 'Doe',
                        'contact_email' => 'john@acme.example.com',
                        'contact_phone' => '+1 555 5678',
                        'status' => 'lead',
                    ],
                ],
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

    public function test_import_applies_per_row_options(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();
        $industry = Industry::factory()->create(['workspace_id' => $workspace->id, 'name' => 'Technology']);
        $leadSource = LeadSource::factory()->create(['workspace_id' => $workspace->id, 'name' => 'Website']);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'accounts' => [
                    [
                        'company_name' => 'Acme Corp',
                        'status' => 'opportunity',
                        'industry_id' => (string) $industry->id,
                        'lead_source_id' => (string) $leadSource->id,
                    ],
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

    public function test_import_rejects_missing_company_name(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'accounts' => [
                    [
                        'company_name' => '',
                        'status' => 'lead',
                    ],
                ],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('accounts', 0);
    }

    public function test_import_reports_errors_for_invalid_rows(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'accounts' => [
                    [
                        'company_name' => '',
                        'status' => 'invalid-status',
                    ],
                    [
                        'company_name' => 'Acme Corp',
                        'status' => 'invalid-status',
                    ],
                ],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('accounts', 0);
    }

    public function test_preview_rejects_non_spreadsheet_files(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import-preview', [
                'file' => UploadedFile::fake()->create('accounts.pdf', 100, 'application/pdf'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('file');
    }

    public function test_import_rejects_non_array_accounts(): void
    {
        [$user, $workspace] = $this->setupUserAndWorkspace();

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post('/accounts/import', [
                'accounts' => 'not-an-array',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('accounts', 0);
    }
}
