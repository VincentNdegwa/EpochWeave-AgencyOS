<?php

namespace App\Services;

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
     * Parse a spreadsheet file and return its headers and preview rows.
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

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    /**
     * Import accounts from the edited frontend data.
     *
     * @param  array<int, array<string, mixed>>  $accounts
     * @return array{created: int, errors: array<int, string>}
     */
    public function importFromData(int $workspaceId, array $accounts): array
    {
        $created = 0;
        $errors = [];
        $line = 0;

        foreach ($accounts as $account) {
            $line++;

            try {
                $this->accountService->createAccount($this->buildAccountData($workspaceId, $account));
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
     * @param  array<int, string>  $headers
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
     * @param  array<string, mixed>  $account
     *
     * @throws InvalidArgumentException
     */
    private function buildAccountData(int $workspaceId, array $account): array
    {
        $companyName = $this->trimToNull($account['company_name'] ?? null);

        if ($companyName === null) {
            throw new InvalidArgumentException('Company name is required.');
        }

        $data = [
            'workspace_id' => $workspaceId,
            'company_name' => $companyName,
            'phone' => $this->trimToNull($account['phone'] ?? null),
            'website' => $this->trimToNull($account['website'] ?? null),
            'status' => $account['status'] ?? 'lead',
            'industry_id' => $this->castInteger($account['industry_id'] ?? null),
            'lead_source_id' => $this->castInteger($account['lead_source_id'] ?? null),
            'company_size_id' => $this->castInteger($account['company_size_id'] ?? null),
        ];

        $contacts = $this->buildContacts($account);

        if ($contacts !== []) {
            $data['contacts'] = $contacts;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $account
     * @return array<int, array<string, mixed>>
     */
    private function buildContacts(array $account): array
    {
        $email = $this->trimToNull($account['contact_email'] ?? null);
        $firstName = $this->trimToNull($account['contact_first_name'] ?? null);
        $lastName = $this->trimToNull($account['contact_last_name'] ?? null);

        if ($email === null) {
            return [];
        }

        if ($firstName === null || $lastName === null) {
            throw new InvalidArgumentException('Contact first and last name are required when email is provided.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Contact email is invalid.');
        }

        return [
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $this->trimToNull($account['contact_phone'] ?? null),
                'is_primary' => true,
            ],
        ];
    }

    private function trimToNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function castInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
