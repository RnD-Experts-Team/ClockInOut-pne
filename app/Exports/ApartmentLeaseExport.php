<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ApartmentLeaseExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
     public function headings(): array
        {
            return [
                'Store Name',
                'Apartment Address',
                'Rent',
                'Utilities',
                'Total Rent',
                'Number of AT',
                'Is Family',
                'Expiration Date',
                'Drive Time',
                'Notes',
                'Lease Holder', 
                'Expiration Warning', 
                'Renewal Date', 
                'Renewal Status', 
                'Renewal Notes', 
                'Days Until Renewal', 
                'Renewal Created By'
              
            ];
        }

    public function collection()
    {
        return collect($this->data);
    }
}