<?php

namespace Modules\Invoice\Services;

use App\Models\Clocking;
use App\Models\Configuration;
use Modules\Invoice\Models\InvoiceCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MileageDistributionService
{
    /**
     * Distribute return miles and driving time proportionally across all invoice cards in a clocking session
     * Uses percentage-based distribution based on each card's driven miles
     *
     * @param int $clockingId
     * @return void
     */
    public function distributeReturnMiles(int $clockingId): void
    {
        DB::beginTransaction();
        
        try {
            // Get the clocking session
            $clocking = Clocking::findOrFail($clockingId);
            
            // Get all invoice cards for this session (including not_done cards)
            $invoiceCards = InvoiceCard::where('clocking_id', $clockingId)->get();
            
            if ($invoiceCards->isEmpty()) {
                Log::warning("No invoice cards found for clocking session {$clockingId}");
                DB::rollBack();
                return;
            }
            
            Log::info("Starting distribution for clocking {$clockingId}", [
                'total_cards' => $invoiceCards->count(),
                'card_statuses' => $invoiceCards->pluck('status', 'id')->toArray(),
                'card_miles' => $invoiceCards->pluck('calculated_miles', 'id')->toArray(),
            ]);
            
            // Calculate final segment miles and driving time
            $finalSegmentMiles = $this->calculateFinalSegment($clockingId);
            $finalSegmentDrivingTime = $this->calculateFinalDrivingTime($clockingId);
            
            if ($finalSegmentMiles <= 0 && $finalSegmentDrivingTime <= 0) {
                Log::info("No final segment miles or driving time to distribute for clocking session {$clockingId}");
                DB::rollBack();
                return;
            }
            
            // Calculate percentages for each card
            $percentages = $this->calculatePercentages($clockingId);
            
            // Get mile rate and hourly rate from configuration
            $mileRate = Configuration::getGasPaymentRate();
            
            // Distribute final segment miles and driving time by percentage
            foreach ($invoiceCards as $card) {
                $cardPercentage = $percentages[$card->id] ?? 0;
                
                Log::info("Distributing to card {$card->id}", [
                    'status' => $card->status,
                    'calculated_miles' => $card->calculated_miles,
                    'existing_allocated_return_miles' => $card->allocated_return_miles ?? 0,
                    'existing_allocated_return_driving_time' => $card->allocated_return_driving_time ?? 0,
                    'percentage' => $cardPercentage,
                    'final_segment_miles' => $finalSegmentMiles,
                    'final_segment_driving_time' => $finalSegmentDrivingTime,
                ]);
                
                // Calculate new allocation for this session
                $newAllocatedReturnMiles = $finalSegmentMiles * $cardPercentage;
                $newAllocatedReturnDrivingTime = $finalSegmentDrivingTime * $cardPercentage;
                
                // Add to existing allocation (for cards that were reopened from previous sessions)
                $card->allocated_return_miles = ($card->allocated_return_miles ?? 0) + $newAllocatedReturnMiles;
                $card->allocated_return_driving_time = ($card->allocated_return_driving_time ?? 0) + $newAllocatedReturnDrivingTime;
                
                // Calculate total miles for this card
                $card->total_miles = ($card->calculated_miles ?? 0) + $card->allocated_return_miles;
                
                // Calculate total driving time for this card (existing + allocated return time)
                $card->total_driving_time_hours = ($card->driving_time_hours ?? 0) + $card->allocated_return_driving_time;
                
                // Recalculate mileage payment (total miles only, not including driving payment)
                $card->mileage_payment = $card->total_miles * $mileRate;
                
                // Recalculate driving time payment using user's hourly rate
                $hourlyRate = $card->user->hourly_pay ?? 20; // Default to $20/hour
                $card->driving_time_payment = $card->total_driving_time_hours * $hourlyRate;
                
                // Recalculate total cost (includes driving payment, labor, materials, and mileage)
                $card->total_cost = $card->driving_time_payment
                                  + ($card->labor_cost ?? 0) 
                                  + ($card->materials_cost ?? 0) 
                                  + $card->mileage_payment;
                
                $card->save();
                
                Log::info("Distributed miles and driving time for card {$card->id}", [
                    'status' => $card->status,
                    'calculated_miles' => $card->calculated_miles,
                    'percentage' => $cardPercentage,
                    'new_allocated_return_miles' => $newAllocatedReturnMiles,
                    'new_allocated_return_driving_time' => $newAllocatedReturnDrivingTime,
                    'total_allocated_return_miles' => $card->allocated_return_miles,
                    'total_allocated_return_driving_time' => $card->allocated_return_driving_time,
                    'driving_time_hours' => $card->driving_time_hours,
                    'total_driving_time_hours' => $card->total_driving_time_hours,
                    'total_miles' => $card->total_miles,
                    'mileage_payment' => $card->mileage_payment,
                    'driving_payment' => $card->driving_time_payment,
                    'total_cost' => $card->total_cost,
                ]);
            }
            
            // Update clocking with total session miles
            $totalDrivenMiles = $invoiceCards->sum('calculated_miles') ?? 0;
            $clocking->total_session_miles = $totalDrivenMiles + $finalSegmentMiles;
            $clocking->save();
            
            DB::commit();
            
            Log::info("Successfully distributed return miles and driving time for clocking session {$clockingId}", [
                'final_segment_miles' => $finalSegmentMiles,
                'final_segment_driving_time' => $finalSegmentDrivingTime,
                'total_session_miles' => $clocking->total_session_miles,
                'cards_processed' => $invoiceCards->count(),
                'not_done_cards' => $invoiceCards->where('status', 'not_done')->count(),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to distribute return miles for clocking {$clockingId}: {$e->getMessage()}");
            throw $e;
        }
    }
    
    /**
     * Calculate mileage payment for a single invoice card
     *
     * @param InvoiceCard $card
     * @return void
     */
    public function calculateMileagePayment(InvoiceCard $card): void
    {
        $mileRate = Configuration::getGasPaymentRate();
        
        if ($card->total_miles) {
            $card->mileage_payment = $card->total_miles * $mileRate;
            $card->save();
        }
    }
    
    /**
     * Calculate final segment driving time (from last store to clock-out)
     *
     * @param int $clockingId
     * @return float Hours
     */
    public function calculateFinalDrivingTime(int $clockingId): float
    {
        $clocking = Clocking::findOrFail($clockingId);
        
        if (!$clocking->clock_out) {
            return 0;
        }
        
        // Get the card that was finished last (by end_time, not start_time)
        // This ensures we get the correct "last store" regardless of card status
        $lastCard = InvoiceCard::where('clocking_id', $clockingId)
            ->whereNotNull('end_time') // Only cards that have been finished
            ->orderBy('end_time', 'desc') // Order by when they were finished, not when they started
            ->first();
        
        if (!$lastCard || !$lastCard->end_time) {
            Log::warning("No card with end_time found for clocking {$clockingId}");
            return 0;
        }
        
        // Calculate time difference between last card end and clock out
        $lastCardEndTime = \Carbon\Carbon::parse($lastCard->end_time);
        $clockOutTime = \Carbon\Carbon::parse($clocking->clock_out);
        
        $finalDrivingTimeMinutes = $lastCardEndTime->diffInMinutes($clockOutTime);
        $finalDrivingTimeHours = $finalDrivingTimeMinutes / 60;
        
        Log::info("Final driving time calculated for clocking {$clockingId}", [
            'last_card_id' => $lastCard->id,
            'last_card_status' => $lastCard->status,
            'last_card_end_time' => $lastCard->end_time,
            'clock_out_time' => $clocking->clock_out,
            'final_driving_time_minutes' => $finalDrivingTimeMinutes,
            'final_driving_time_hours' => $finalDrivingTimeHours,
        ]);
        
        return max(0, $finalDrivingTimeHours); // Ensure non-negative
    }
    
    /**
     * Calculate final segment miles (from last store to clock-out)
     *
     * @param int $clockingId
     * @return float
     */
    public function calculateFinalSegment(int $clockingId): float
    {
        $clocking = Clocking::findOrFail($clockingId);
        
        if (!$clocking->miles_out) {
            return 0;
        }
        
        // Get the last card's arrival odometer
        $lastCard = InvoiceCard::where('clocking_id', $clockingId)
            ->orderBy('start_time', 'desc')
            ->first();
        
        if (!$lastCard || !$lastCard->arrival_odometer) {
            return 0;
        }
        
        $finalSegment = $clocking->miles_out - $lastCard->arrival_odometer;
        
        Log::info("Final segment calculated for clocking {$clockingId}", [
            'miles_out' => $clocking->miles_out,
            'last_arrival_odometer' => $lastCard->arrival_odometer,
            'final_segment' => $finalSegment,
        ]);
        
        return max(0, $finalSegment); // Ensure non-negative
    }
    
    /**
     * Calculate percentage distribution for each card based on driven miles
     *
     * @param int $clockingId
     * @return array Card ID => percentage
     */
    public function calculatePercentages(int $clockingId): array
    {
        $invoiceCards = InvoiceCard::where('clocking_id', $clockingId)->get();
        
        Log::info("Calculating percentages for clocking {$clockingId}", [
            'total_cards' => $invoiceCards->count(),
            'card_details' => $invoiceCards->map(function ($card) {
                return [
                    'id' => $card->id,
                    'status' => $card->status,
                    'calculated_miles' => $card->calculated_miles ?? 0,
                    'existing_allocated_return_miles' => $card->allocated_return_miles ?? 0,
                ];
            })->toArray(),
        ]);
        
        // Calculate total driven miles (sum of calculated_miles)
        $totalDrivenMiles = $invoiceCards->sum('calculated_miles') ?? 0;
        
        if ($totalDrivenMiles == 0) {
            // Equal distribution if no miles recorded
            $equalPercentage = 1 / max(1, $invoiceCards->count());
            Log::info("No miles recorded, using equal distribution", [
                'equal_percentage' => $equalPercentage,
            ]);
            return $invoiceCards->pluck('id')->mapWithKeys(function ($id) use ($equalPercentage) {
                return [$id => $equalPercentage];
            })->toArray();
        }
        
        // Calculate each card's percentage
        $percentages = [];
        foreach ($invoiceCards as $card) {
            $cardMiles = $card->calculated_miles ?? 0;
            $percentages[$card->id] = $cardMiles / $totalDrivenMiles;
        }
        
        Log::info("Calculated percentages for clocking {$clockingId}", [
            'total_driven_miles' => $totalDrivenMiles,
            'percentages' => $percentages,
            'percentage_details' => $invoiceCards->map(function ($card) use ($percentages) {
                return [
                    'card_id' => $card->id,
                    'status' => $card->status,
                    'calculated_miles' => $card->calculated_miles ?? 0,
                    'percentage' => round(($percentages[$card->id] ?? 0) * 100, 2) . '%',
                ];
            })->toArray(),
        ]);
        
        return $percentages;
    }
    
    /**
     * Recalculate distribution for all cards in a clocking session
     * Useful for fixing existing data where distribution was missed
     *
     * @param int $clockingId
     * @return void
     */
    public function recalculateDistribution(int $clockingId): void
    {
        Log::info("Recalculating distribution for clocking session {$clockingId}");
        
        // Reset existing distribution values
        InvoiceCard::where('clocking_id', $clockingId)
            ->update([
                'allocated_return_miles' => 0,
                'allocated_return_driving_time' => 0,
                'total_miles' => DB::raw('calculated_miles'),
                'total_driving_time_hours' => DB::raw('driving_time_hours'),
            ]);
        
        // Recalculate distribution
        $this->distributeReturnMiles($clockingId);
        
        Log::info("Distribution recalculated for clocking session {$clockingId}");
    }
    
    /**
     * Fix distribution for all completed clocking sessions that might be missing distribution
     *
     * @return void
     */
    public function fixMissingDistribution(): void
    {
        Log::info("Starting fix for missing distribution");
        
        // Find clocking sessions that are completed but have cards with no allocated_return_miles
        $clockingsToFix = Clocking::whereNotNull('miles_out')
            ->where('using_car', true)
            ->whereHas('invoiceCards', function ($query) {
                $query->where('calculated_miles', '>', 0)
                      ->where(function ($q) {
                          $q->whereNull('allocated_return_miles')
                            ->orWhere('allocated_return_miles', 0);
                      });
            })
            ->get();
        
        Log::info("Found {$clockingsToFix->count()} clocking sessions that need distribution fix");
        
        foreach ($clockingsToFix as $clocking) {
            try {
                $this->recalculateDistribution($clocking->id);
                Log::info("Fixed distribution for clocking {$clocking->id}");
            } catch (\Exception $e) {
                Log::error("Failed to fix distribution for clocking {$clocking->id}: {$e->getMessage()}");
            }
        }
        
        Log::info("Completed fixing missing distribution");
    }
}