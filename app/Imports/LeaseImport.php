<?php
// app/Imports/LeaseImport.php

namespace App\Imports;

use App\Models\Lease;
use App\Models\Store;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Throwable;

class LeaseImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsEmptyRows, WithBatchInserts, WithChunkReading
{
    private $errors = [];

    public function model(array $row)
    {
        // Parse store number first
        $storeNumber = $this->parseStoreNumber($row['store'] ?? null);
        $store = null;

        // Create or find the store if store number exists
        if ($storeNumber) {
            $store = Store::firstOrCreate(
                ['store_number' => $storeNumber],
                [
                    'name' => $row['known_as'] ?? 'Imported Store #' . $storeNumber,
                    'is_active' => true,
                    'address' => $row['store_address'] ?? null
                ]
            );
        }

        // Handle different date formats
        $franchiseExpirationDate = $this->parseDate($row['date_franchise_agreament_expiration_date2'] ?? null);
        $initialLeaseExpirationDate = $this->parseDate($row['initial_lease_expiration_date'] ?? null);

        // Parse HVAC - handle Yes/No, True/False, 1/0
        $hvac = $this->parseBoolean($row['hvac'] ?? null);

        // Clean monetary values - remove $ signs and commas
        $aws = $this->parseCurrency($row['aws'] ?? null);
        $baseRent = $this->parseCurrency($row['base_rent'] ?? null);
        $cam = $this->parseCurrency($row['cam'] ?? null);
        $insurance = $this->parseCurrency($row['insurance'] ?? null);
        $reTaxes = $this->parseCurrency($row['re_taxes'] ?? null);
        $others = $this->parseCurrency($row['others'] ?? null);
        $securityDeposit = $this->parseCurrency($row['security_deposit'] ?? null);

        // Parse percentage
        $percentIncrease = $this->parsePercentage($row['increase_per_year'] ?? null);

        // Parse SQF
        $sqf = is_numeric($row['sqf'] ?? null) ? (int) $row['sqf'] : null;

        // Clean renewal options format
        $renewalOptions = $this->cleanRenewalOptions($row['renewal_optionstermsyears'] ?? null);

        return new Lease([
            'store_id' => $store ? $store->id : null, // Links to Store model
            'store_number' => $storeNumber, // Keeps original store_number for reference
            'name' => $row['known_as'] ?? null,
            'store_address' => $row['store_address'] ?? null,
            'aws' => $aws,
            'base_rent' => $baseRent,
            'percent_increase_per_year' => $percentIncrease,
            'cam' => $cam,
            'insurance' => $insurance,
            're_taxes' => $reTaxes,
            'others' => $others,
            'security_deposit' => $securityDeposit,
            'franchise_agreement_expiration_date' => $franchiseExpirationDate,
            'renewal_options' => $renewalOptions,
            'initial_lease_expiration_date' => $initialLeaseExpirationDate,
            'sqf' => $sqf,
            'hvac' => $hvac,
            'landlord_responsibility' => $row['landlord_responsibility'] ?? null,
            'landlord_name' => $row['landlord_name'] ?? null,
            'landlord_email' => $this->extractEmail($row['email_phone'] ?? null),
            'landlord_phone' => $this->extractPhone($row['email_phone'] ?? null),
            'landlord_address' => $row['address'] ?? null,
            'comments' => $row['comments'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'store' => 'nullable|max:255',
            'known_as' => 'nullable|string|max:255',
            'store_address' => 'nullable|string',
            'aws' => 'nullable|numeric|min:0',
            'base_rent' => 'nullable|numeric|min:0',
            'increase_per_year' => 'nullable|numeric|min:0|max:100',
            'cam' => 'nullable|numeric|min:0',
            'insurance' => 'nullable|numeric|min:0',
            're_taxes' => 'nullable|numeric|min:0',
            'others' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'date_franchise_agreament_expiration_date2' => 'nullable',
            'renewal_optionstermsyears' => 'nullable|string|max:255',
            'initial_lease_expiration_date' => 'nullable',
            'sqf' => 'nullable|integer|min:0',
            'hvac' => 'nullable',
            'landlord_responsibility' => 'nullable|string',
            'landlord_name' => 'nullable|string|max:255',
            'email_phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'comments' => 'nullable|string',
        ];
    }

    public function onError(Throwable $e)
    {
        $this->errors[] = $e->getMessage();
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    // Helper methods for data parsing
    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        // Handle different date formats
        try {
            // Try common formats
            $formats = ['m/d/Y', 'Y-m-d', 'd/m/Y', 'm-d-Y', 'Y/m/d'];

            foreach ($formats as $format) {
                $parsed = Carbon::createFromFormat($format, $date);
                if ($parsed !== false) {
                    return $parsed->format('Y-m-d');
                }
            }

            // Try Carbon's automatic parsing as fallback
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseBoolean($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        $value = strtolower(trim($value));

        return in_array($value, ['yes', 'true', '1', 'on', 'available']);
    }

    private function parseCurrency($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        // Remove $ sign, commas, and spaces
        $cleaned = preg_replace('/[\$,\s]/', '', $value);

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    private function parsePercentage($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        // Remove % sign and spaces
        $cleaned = preg_replace('/[%\s]/', '', $value);

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    private function cleanRenewalOptions($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        // Clean and format renewal options (e.g., "3,5" or "3 terms, 5 years")
        $cleaned = preg_replace('/[^\d,]/', '', $value);

        // Ensure it's in proper format (e.g., "3,5")
        if (preg_match('/^\d+,\d+$/', $cleaned)) {
            return $cleaned;
        }

        return $value; // Return original if can't parse
    }

    /**
     * Parse store number and ensure it's always returned as a string
     *
     * @param mixed $value
     * @return string|null
     */
    private function parseStoreNumber($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        // Convert to string and trim whitespace
        $storeNumber = trim((string) $value);

        // Handle edge cases
        if ($storeNumber === '' || $storeNumber === '0') {
            return null;
        }

        // Always return as string, preserving any formatting like leading zeros
        return $storeNumber;
    }

    /**
     * Extract email from combined email/phone field
     *
     * @param string|null $emailPhone
     * @return string|null
     */
    private function extractEmail($emailPhone)
    {
        if (is_null($emailPhone) || $emailPhone === '') {
            return null;
        }

        // Look for email pattern
        if (preg_match('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $emailPhone, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract phone from combined email/phone field
     *
     * @param string|null $emailPhone
     * @return string|null
     */
    private function extractPhone($emailPhone)
    {
        if (is_null($emailPhone) || $emailPhone === '') {
            return null;
        }

        // Remove email if present
        $phoneOnly = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '', $emailPhone);

        // Look for phone pattern (various formats)
        if (preg_match('/[\d\-\(\)\s\+\.]{7,}/', $phoneOnly, $matches)) {
            return trim($matches[0]);
        }

        return null;
    }
}
