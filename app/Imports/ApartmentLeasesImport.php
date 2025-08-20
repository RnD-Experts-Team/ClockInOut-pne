<?php

namespace App\Imports;

use App\Models\ApartmentLease;
use App\Models\Store;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class ApartmentLeasesImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \App\Models\ApartmentLease|null
     */
    public function model(array $row)
    {
        // Extract store number
        $storeNumber = isset($row['store']) ? trim($row['store']) : null;
        $store = null;

        // Create or find the store if store number exists
        if ($storeNumber) {
            $store = Store::firstOrCreate(
                ['store_number' => $storeNumber],
                [
                    'name' => 'Imported Store #' . $storeNumber,
                    'is_active' => true,
                    'address' => null
                ]
            );
        }

        // Extract values
        $rent = isset($row['rent']) ? floatval($row['rent']) : 0;
        $utilities = isset($row['utilites']) ? floatval($row['utilites']) : null; // keeping original typo

        // Skip row if essential data is missing
        if (is_null($storeNumber) && $rent == 0 && is_null($utilities)) {
            return null;
        }

        // Handle expiration date conversion
        $expirationDate = null;
        if (isset($row['expiration_date']) && !empty($row['expiration_date'])) {
            $dateValue = $row['expiration_date'];

            // Check if it's a numeric value (Excel serial date)
            if (is_numeric($dateValue) && $dateValue > 0) {
                try {
                    // Convert Excel serial date to Carbon date
                    $expirationDate = Carbon::instance(Date::excelToDateTimeObject($dateValue))->format('Y-m-d');
                } catch (\Exception $e) {
                    $expirationDate = null;
                }
            }
            // Check if it's already a date string and not 'OLD DATE'
            elseif (!is_numeric($dateValue) && strtoupper($dateValue) !== 'OLD DATE') {
                try {
                    $expirationDate = Carbon::parse($dateValue)->format('Y-m-d');
                } catch (\Exception $e) {
                    $expirationDate = null;
                }
            }
        }

        return new ApartmentLease([
            'store_id'           => $store ? $store->id : null, // Links to Store model
            'store_number'       => $storeNumber, // Keeps original store_number for reference
            'apartment_address'  => $row['apartment_address'] ?? '',
            'rent'              => $rent,
            'utilities'         => $utilities,
            'number_of_AT'      => isset($row['number_of_at_living_in_it']) ? intval($row['number_of_at_living_in_it']) : 1,
            'has_car'           => isset($row['has_a_car']) ? intval($row['has_a_car']) : 0,
            'is_family'         => isset($row['is_a_familly_livign_in_the_apartment']) ? strtolower($row['is_a_familly_livign_in_the_apartment']) : 'no',
            'expiration_date'   => $expirationDate,
            'drive_time'        => $row['drive_time'] ?? null,
            'notes'             => $row['notes'] ?? null,
            'lease_holder'      => $row['lease_holder'] ?? '',
            'created_by'        => auth()->id(),
        ]);
    }
}
