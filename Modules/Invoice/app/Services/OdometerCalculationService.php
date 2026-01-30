<?php

namespace Modules\Invoice\Services;

use App\Models\Clocking;
use App\Models\Configuration;
use Modules\Invoice\Models\InvoiceCard;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OdometerCalculationService
{
    /**
     * Calculate distance for an invoice card based on odometer readings
     *
     * @param InvoiceCard $invoiceCard
     * @param bool $accumulate Whether to add to existing miles (for reopened cards)
     * @return float
     */
    public function calculateDistance(InvoiceCard $invoiceCard, bool $accumulate = false): float
    {
        if (!$invoiceCard->arrival_odometer) {
            Log::warning("No arrival odometer for invoice card {$invoiceCard->id}");
            return 0;
        }

        $previousOdometer = $this->getPreviousOdometer($invoiceCard);

        // Calculate distance
        $distance = $invoiceCard->arrival_odometer - $previousOdometer;

        // Handle odometer rollover (999,999 -> 0)
        if ($distance < 0) {
            // Assume 6-digit odometer rollover
            $distance = (999999 - $previousOdometer) + $invoiceCard->arrival_odometer;
            Log::info("Odometer rollover detected for card {$invoiceCard->id}", [
                'previous' => $previousOdometer,
                'current' => $invoiceCard->arrival_odometer,
                'calculated_distance' => $distance,
            ]);
        }

        // Update the card - accumulate if this is a reopened card
        if ($accumulate && $invoiceCard->calculated_miles) {
            $invoiceCard->calculated_miles += $distance;
            Log::info("Accumulating distance for reopened card {$invoiceCard->id}", [
                'previous_calculated_miles' => $invoiceCard->calculated_miles - $distance,
                'new_distance' => $distance,
                'total_calculated_miles' => $invoiceCard->calculated_miles,
            ]);
        } else {
            $invoiceCard->calculated_miles = $distance;
        }
        
        $invoiceCard->save();

        Log::info("Distance calculated for card {$invoiceCard->id}", [
            'previous_odometer' => $previousOdometer,
            'arrival_odometer' => $invoiceCard->arrival_odometer,
            'calculated_miles' => $invoiceCard->calculated_miles,
            'accumulated' => $accumulate,
        ]);

        return $distance;
    }

    /**
     * Calculate driving time for an invoice card
     *
     * @param InvoiceCard $invoiceCard
     * @param bool $accumulate Whether to add to existing driving time (for reopened cards)
     * @return float Hours spent driving
     */
    public function calculateDrivingTime(InvoiceCard $invoiceCard, bool $accumulate = false): float
    {
        if (!$invoiceCard->start_time) {
            Log::warning("No start time for invoice card {$invoiceCard->id}");
            return 0;
        }

        $previousTime = $this->getPreviousTime($invoiceCard);

        // Calculate time difference in hours
        $startTime = Carbon::parse($invoiceCard->start_time);
        $previousTimeCarbon = Carbon::parse($previousTime);
        
        $drivingHours = $previousTimeCarbon->diffInMinutes($startTime) / 60;

        // Update the card - accumulate if this is a reopened card
        if ($accumulate && $invoiceCard->driving_time_hours) {
            $invoiceCard->driving_time_hours += $drivingHours;
            Log::info("Accumulating driving time for reopened card {$invoiceCard->id}", [
                'previous_driving_hours' => $invoiceCard->driving_time_hours - $drivingHours,
                'new_driving_hours' => $drivingHours,
                'total_driving_hours' => $invoiceCard->driving_time_hours,
            ]);
        } else {
            $invoiceCard->driving_time_hours = $drivingHours;
        }
        
        $invoiceCard->save();

        Log::info("Driving time calculated for card {$invoiceCard->id}", [
            'previous_time' => $previousTime,
            'arrival_time' => $invoiceCard->start_time,
            'driving_hours' => $invoiceCard->driving_time_hours,
            'accumulated' => $accumulate,
        ]);

        return $drivingHours;
    }

    /**
     * Calculate driving time payment for an invoice card
     * 
     * NOTE: This should only be called BEFORE distribution is calculated.
     * After distribution, MileageDistributionService will recalculate using total_driving_time_hours.
     *
     * @param InvoiceCard $invoiceCard
     * @return float
     */
    public function calculateDrivingPayment(InvoiceCard $invoiceCard): float
    {
        // Use total_driving_time_hours if available (includes allocated return time)
        // Otherwise use driving_time_hours (for initial calculation before distribution)
        $drivingHours = $invoiceCard->total_driving_time_hours ?? $invoiceCard->driving_time_hours ?? 0;
        
        if (!$drivingHours) {
            return 0;
        }

        // Get user's hourly pay rate
        $hourlyRate = $invoiceCard->user->hourly_pay ?? 20; // Default to $20/hour

        $drivingPayment = $drivingHours * $hourlyRate;

        // Update the card
        $invoiceCard->driving_time_payment = $drivingPayment;
        $invoiceCard->save();

        Log::info("Driving payment calculated for card {$invoiceCard->id}", [
            'driving_time_hours' => $invoiceCard->driving_time_hours,
            'total_driving_time_hours' => $invoiceCard->total_driving_time_hours,
            'driving_hours_used' => $drivingHours,
            'hourly_rate' => $hourlyRate,
            'driving_payment' => $drivingPayment,
        ]);

        return $drivingPayment;
    }

    /**
     * Validate odometer reading
     *
     * @param float $newReading
     * @param float $previousReading
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validateOdometer(float $newReading, float $previousReading): array
    {
        // Check for reasonable range
        if ($newReading < 0 || $newReading > 999999) {
            return [
                'valid' => false,
                'error' => 'Odometer reading must be between 0 and 999,999'
            ];
        }

        // Check for non-decreasing (with rollover tolerance)
        if ($newReading < $previousReading) {
            // Allow if it looks like a rollover (previous near max, new near zero)
            if ($previousReading > 900000 && $newReading < 100000) {
                return ['valid' => true, 'error' => null];
            }

            return [
                'valid' => false,
                'error' => "Odometer reading ({$newReading}) must be greater than or equal to previous reading ({$previousReading})"
            ];
        }

        // Check for unreasonably large jump (more than 500 miles in one segment)
        $difference = $newReading - $previousReading;
        if ($difference > 500) {
            return [
                'valid' => false,
                'error' => "Odometer jump ({$difference} miles) seems too large. Please verify the reading."
            ];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Get the previous odometer reading
     * Either from the previous card or from clocking.miles_in
     *
     * @param InvoiceCard $invoiceCard
     * @return float
     */
    public function getPreviousOdometer(InvoiceCard $invoiceCard): float
    {
        // Get the previous card in this clocking session
        $previousCard = InvoiceCard::where('clocking_id', $invoiceCard->clocking_id)
            ->where('start_time', '<', $invoiceCard->start_time)
            ->orderBy('start_time', 'desc')
            ->first();

        if ($previousCard && $previousCard->arrival_odometer) {
            return $previousCard->arrival_odometer;
        }

        // If this is the first card, use clocking.miles_in
        $clocking = Clocking::find($invoiceCard->clocking_id);
        return $clocking->miles_in ?? 0;
    }

    /**
     * Get the previous time (end time of previous card or clock_in time)
     *
     * @param InvoiceCard $invoiceCard
     * @return string
     */
    public function getPreviousTime(InvoiceCard $invoiceCard): string
    {
        // Get the previous card in this clocking session
        $previousCard = InvoiceCard::where('clocking_id', $invoiceCard->clocking_id)
            ->where('start_time', '<', $invoiceCard->start_time)
            ->orderBy('start_time', 'desc')
            ->first();

        if ($previousCard && $previousCard->end_time) {
            return $previousCard->end_time;
        }

        // If this is the first card, use clocking.clock_in
        $clocking = Clocking::find($invoiceCard->clocking_id);
        return $clocking->clock_in ?? now();
    }

    /**
     * Calculate all odometer-related values for an invoice card
     * (distance and driving time only - payment is calculated later by MileageDistributionService)
     *
     * @param InvoiceCard $invoiceCard
     * @param bool $accumulate Whether to add to existing values (for reopened cards)
     * @return void
     */
    public function calculateAll(InvoiceCard $invoiceCard, bool $accumulate = false): void
    {
        $this->calculateDistance($invoiceCard, $accumulate);
        $this->calculateDrivingTime($invoiceCard, $accumulate);
        // NOTE: Don't calculate driving payment here - it will be calculated by MileageDistributionService
        // after distribution is done, using total_driving_time_hours

        Log::info("Odometer calculations completed for card {$invoiceCard->id}", [
            'accumulated' => $accumulate,
            'calculated_miles' => $invoiceCard->calculated_miles,
            'driving_time_hours' => $invoiceCard->driving_time_hours,
        ]);
    }
}
