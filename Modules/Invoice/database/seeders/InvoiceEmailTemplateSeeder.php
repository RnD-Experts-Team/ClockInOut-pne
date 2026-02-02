<?php

namespace Modules\Invoice\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Invoice\Models\InvoiceEmailTemplate;

class InvoiceEmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if default template already exists
        if (InvoiceEmailTemplate::where('is_default', true)->exists()) {
            $this->command->info('Default email template already exists.');
            return;
        }

        InvoiceEmailTemplate::create([
            'name' => 'Default Invoice Email',
            'subject' => 'Invoice #{invoice_number} - {store_name}',
            'body' => '<p>Dear Valued Customer,</p>
<p>Please find your invoice details below for the maintenance services provided during the period <strong>{period}</strong>.</p>
<p>We appreciate your business and look forward to serving you again.</p>
<p>If you have any questions about this invoice, please don\'t hesitate to contact us.</p>
<p>Best regards,<br>
<strong>{technician_name}</strong><br>
Maintenance Services Team</p>',
            'is_default' => true,
        ]);

        $this->command->info('Default email template created successfully!');
    }
}
