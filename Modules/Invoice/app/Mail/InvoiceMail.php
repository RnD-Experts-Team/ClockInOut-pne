<?php

namespace Modules\Invoice\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Modules\Invoice\Models\Invoice;
use Modules\Invoice\Models\InvoiceEmailTemplate;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $template;
    public $invoiceData;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, InvoiceEmailTemplate $template, array $invoiceData)
    {
        $this->invoice = $invoice;
        $this->template = $template;
        $this->invoiceData = $invoiceData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->template->subject ?? "Invoice #{$this->invoice->invoice_number} - {$this->invoiceData['store']->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'invoice::emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'template' => $this->template,
                'store' => $this->invoiceData['store'],
                'technician' => $this->invoiceData['technician'],
                'materials' => $this->invoiceData['materials'],
                'equipment_items' => $this->invoiceData['equipment_items'],
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
