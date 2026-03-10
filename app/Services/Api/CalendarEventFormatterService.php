<?php

namespace App\Services\Api;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CalendarEventFormatterService
{

    public function cleanAndParseDate($date): Carbon
    {
        if (is_string($date)) {

            $date = preg_replace('/\+\d{2}:\d{2}$/', '', $date);
            $date = preg_replace('/T\d{2}:\d{2}:\d{2}/', '', $date);

        }

        return Carbon::parse($date);
    }



    public function formatEvents(Collection $events): array
    {
        return $events->map(function ($event) {

            try {
                // Check if it's a real CalendarEvent model or a temporary one

                if (method_exists($event, 'formatForCalendar')) {
                    return $event->formatForCalendar();
                }else
                {
                     // Manual formatting for temporary events
                     return [
                    'id' => $event->id ?? uniqid(),
                    'title' => $event->title ?? 'Event',

                    'start' => ($event->is_all_day ?? true)
                        ? $event->start_date
                        : $event->start_date . 'T' . ($event->start_time ?? '00:00:00'),

                    'end' => isset($event->end_date)
                        ? (($event->is_all_day ?? true)
                            ? $event->end_date
                            : $event->end_date . 'T' . ($event->end_time ?? '23:59:59'))
                        : null,

                    'allDay' => $event->is_all_day ?? true,
                    'color' => $event->color_code ?? '#667eea',
                    'textColor' => '#ffffff',
                    'description' => $event->description ?? '',
                    'eventType' => $event->event_type ?? 'custom',
                    'className' => 'event-' . ($event->event_type ?? 'custom'),

                    'extendedProps' => [
                        'description' => $event->description ?? '',
                        'event_type' => $event->event_type ?? 'custom',
                        'urgency' => $event->urgency ?? 'normal',
                       // ➕ FIX: Add additional_data to extendedProps
                        'additional_data' => $event->additional_data ?? [],
                        'related_model_type' => $event->related_model_type ?? null,
                        'related_model_id' => $event->related_model_id ?? null,
                    ]
                ];
                }            
            } catch (\Exception $e) {

                Log::error('Error formatting individual event: ' . $e->getMessage());

                return null;
            }

        })->filter()->values()->toArray();
    }
}