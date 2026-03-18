<?php
namespace App\Services\Api\Admin\ModulesInvoice;

use App\Models\ModulesInvoice\InvoiceRecipient;

class EmailRecipientService
{
    public function list()
    {
        return InvoiceRecipient::with('store')
            ->orderBy('store_id')
            ->orderBy('is_default', 'desc')
            ->get();
    }

    public function create(array $data)
    {
        if (!empty($data['is_default'])) {
            InvoiceRecipient::where('store_id', $data['store_id'])
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        return InvoiceRecipient::create([
            'store_id' => $data['store_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'is_default' => $data['is_default'] ?? false,
        ]);
    }

    public function update($id, array $data)
    {
        $recipient = InvoiceRecipient::findOrFail($id);

        if (!empty($data['is_default'])) {
            InvoiceRecipient::where('store_id', $data['store_id'])
                ->where('id', '!=', $id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $recipient->update([
            'store_id' => $data['store_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'is_default' => $data['is_default'] ?? false,
        ]);

        return $recipient;
    }

    public function delete($id)
    {
        $recipient = InvoiceRecipient::findOrFail($id);
        $recipient->delete();

        return true;
    }
}