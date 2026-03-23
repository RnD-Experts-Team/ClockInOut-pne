<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClockingExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    public function headings(): array
    {
        return [
            'Name',
            'Date',
            'Clock In',
            'Clock Out',
            'Miles In',
            'Miles Out',
            'Total Miles',
            'Gas Payment',
            'Purchase Cost',
            'Total Hours',
            'Total Salary',
            'Hourly Rate',
        ];
    }

    public function collection()
    {
        return collect($this->data)->map(function ($item) {

            if (is_array($item)) {
                return $item;
            }

            if (is_object($item)) {
                return (array) $item;
            }

            return ['value' => $item];
        });
    }
}