<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Models\CompanySize;
use App\Models\Industry;
use App\Models\LeadSource;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;
use Throwable;

class AccountImportService
{
    public function __construct(private AccountService $accountService) {}

    /**
     * Parse a spreadsheet file and return its headers and first preview rows.
     *
     * @return array{headers: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function parsePreview(string $path): array
    {
        $rows = $this->readSpreadsheet($path);

        if ($rows === []) {
            throw new InvalidArgumentException('Import file is empty.');
        }

        $headers = array_shift($rows);
        $preview = array_slice($rows, 0, 10);

        return [
            'headers' => $headers,
            'rows' => $preview,
        ];
    }

    /**
     * Import accounts from a spreadsheet file using field mapping and bulk options.
     *
     * @param array<string, string|null> $mapping
     * @param array<string, mixed> $bulk
     *
     * @return array{created: int, errors: array<int, string>}
     */
    public function import(int $workspaceId, string $path, array $mapping, array $bulk): array
    {
        $rows = $this->readSpreadsheet($path);

        if ($rows === []) {
            throw new InvalidArgumentException('Import file is empty.');
        }

        $headers = array_shift($rows);
        $headerMap = $this->buildHeaderMap($headers);

        $created = 0;
        $errors = [];
        $line = 1;

        foreach ($rows as $row) {
            $line++;

            try {
                $this->importRow($workspaceId, $row, $headerMap, $mapping, $bulk);
                $created++;
            } catch (Throwable $e) {
                $errors[$line] = $e->getMessage();
                Log::warning('Account import failed', ['line' => $line, 'error' => $e->getMessage()]);
            }
        }

        return ['created' => $created, 'errors' => $errors];
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function readSpreadsheet(string $path): array
    {
        if (! file_exists($path) || ! is_readable($path)) {
            throw new InvalidArgumentException('Import file is not readable.');
        }

        try {
            $spreadsheet = IOFactory::load($path);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('Unable to read spreadsheet: '.$e->getMessage());
        }

        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();

                if ($value === null) {
                    $rowData[] = '';
                    continue;
                }

                if ($cell->getDataType() === DataType::TYPE_NUMERIC && SpreadsheetDate::isDateTime($cell)) {
                    $rowData[] = date('Y-m-d', SpreadsheetDate::excelToTimestamp($value));
                    continue;
                }

                $rowData[] = trim((string) $value);
            }

            if (implode('', $rowData) !== '') {
                $data[] = $rowData;
            }
        }

        return $data;
    }

    /**
     * @param array<int, string> $headers
     *
     * @return array<string, int>
     */
    private function buildHeaderMap(array $headers): array
    {
        $map = [];
        foreach ($headers as $index => $header) {
            $map[strtolower(trim($header))] = $index;
        }

        return $map;
    }

    /**
     * @param array<int, string> $row
     * @param array<string, int> $headerMap
     * @param array<string, string|null> $mapping
     * @param array<string, mixed> $bulk
     *
     * @throws InvalidArgumentException
     */
    private function importRow(int $workspaceId, array $row, array $headerMap, array $mapping, array $bulk): void
    {
        $companyName = $this->value($row, $headerMap, $mapping, 'company_name');

        if ($companyName === null || $companyName === '') {
            throw new InvalidArgumentException('Company name is required.');
        }

        $industryId = $this->bulkLookup($workspaceId, $bulk, 'industry_id', Industry::class);
        $leadSourceId = $this->bulkLookup($workspaceId, $bulk, 'lead_source_id', LeadSource::class);
        $companySizeId = $this->bulkLookup($workspaceId, $bulk, 'company_size_id', CompanySize::class);

        $status = $this->bulkStatus($bulk);
        $foundedAt = $this->value($row, $headerMap, $mapping, 'founded_at');
        $annualRevenue = $this->value($row, $headerMap, $mapping, 'annual_revenue');
        $employeeCount = $this->value($row, $headerMap, $mapping, 'employee_count');

        $accountData = [
            'workspace_id' => $workspaceId,
            'company_name' => $companyName,
            'phone' => $this->value($row, $headerMap, $mapping, 'phone'),
            'website' => $this->value($row, $headerMap, $mapping, 'website'),
            'description' => $this->value($row, $headerMap, $mapping, 'description'),
            'founded_at' => $this->date($foundedAt),
            'status' => $status,
            'annual_revenue' => $this->numeric($annualRevenue, 'annual_revenue'),
            'employee_count' => $this->integer($employeeCount, 'employee_count'),
            'industry_id' => $industryId,
            'lead_source_id' => $leadSourceId,
            'company_size_id' => $companySizeId,
        ];

        $contacts = [];
        $contactEmail = $this->value($row, $headerMap, $mapping, 'contact_email');
        $contactFirstName = $this->value($row, $headerMap, $mapping, 'contact_first_name');
        $contactLastName = $this->value($row, $headerMap, $mapping, 'contact_last_name');

        if ($contactEmail !== null && $contactEmail !== '') {
            if ($contactFirstName === null || $contactFirstName === '' || $contactLastName === null || $contactLastName === '') {
                throw new InvalidArgumentException('Contact first and last name are required when email is provided.');
            }

            if (! filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Contact email is invalid.');
            }

            $contacts[] = [
                'first_name' => $contactFirstName,
                'last_name' => $contactLastName,
                'email' => $contactEmail,
                'phone' => $this->value($row, $headerMap, $mapping, 'contact_phone'),
                'is_primary' => true,
            ];
        }

        if ($contacts !== []) {
            $accountData['contacts'] = $contacts;
        }

        $this->accountService->createAccount($accountData);
    }

    /**
     * @param array<int, string> $row
     * @param array<string, int> $headerMap
     * @param array<string, string|null> $mapping
     */
    private function value(array $row, array $headerMap, array $mapping, string $field): ?string
    {
        $csvColumn = $mapping[$field] ?? null;

        if ($csvColumn === null || $csvColumn === '') {
            return null;
        }

        $index = $headerMap[strtolower(trim($csvColumn))] ?? null;

        if ($index === null || ! isset($row[$index])) {
            return null;
        }

        $value = trim($row[$index]);

        return $value === '' ? null : $value;
    }

    /**
     * @param array<string, mixed> $bulk
     * @param class-string<\Illuminate\Database\Eloquent\Model> $model
     */
    private function bulkLookup(int $workspaceId, array $bulk, string $key, string $model): ?int
    {
        $id = $bulk[$key] ?? null;

        if ($id === null || $id === '' || $id === 0) {
            return null;
        }

        $id = (int) $id;

        if (! $model::where('id', $id)->where('workspace_id', $workspaceId)->exists()) {
            throw new InvalidArgumentException(ucfirst(str_replace('_', ' ', $key)).' not found in workspace.');
        }

        return $id;
    }

    /**
     * @param array<string, mixed> $bulk
     */
    private function bulkStatus(array $bulk): string
    {
        $value = $bulk['status'] ?? null;

        if ($value === null || $value === '') {
            return AccountStatus::Lead->value;
        }

        try {
            return AccountStatus::from((string) $value)->value;
        } catch (Throwable) {
            throw new InvalidArgumentException('Status must be one of: lead, opportunity, client, archived.');
        }
    }

    private function date(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = date_parse($value);
        if ($date['error_count'] > 0 || $date['warning_count'] > 0 || $date['year'] === false) {
            throw new InvalidArgumentException('Founded date must be a valid date (YYYY-MM-DD).');
        }

        return sprintf('%04d-%02d-%02d', $date['year'], $date['month'], $date['day']);
    }

    private function integer(?string $value, string $key): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! ctype_digit($value)) {
            throw new InvalidArgumentException("{$key} must be an integer.");
        }

        return (int) $value;
    }

    private function numeric(?string $value, string $key): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            throw new InvalidArgumentException("{$key} must be numeric.");
        }

        return (float) $value;
    }
}
