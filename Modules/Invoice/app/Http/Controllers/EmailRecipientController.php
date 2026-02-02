<?php

namespace Modules\Invoice\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Invoice\Models\InvoiceRecipient;
use App\Models\Store;
use Illuminate\Http\Request;

class EmailRecipientController extends Controller
{
    public function index()
    {
        $recipients = InvoiceRecipient::with('store')
            ->orderBy('store_id')
            ->orderBy('is_default', 'desc')
            ->get();
        
        return view('invoice::email-recipients.index', compact('recipients'));
    }

    public function create()
    {
        $stores = Store::orderBy('store_number')->get();
        return view('invoice::email-recipients.create', compact('stores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'is_default' => 'boolean',
        ]);

        try {
            // If this recipient is set as default for this store, unset other defaults for the same store
            if ($request->is_default) {
                InvoiceRecipient::where('store_id', $request->store_id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            InvoiceRecipient::create([
                'store_id' => $request->store_id,
                'name' => $request->name,
                'email' => $request->email,
                'is_default' => $request->is_default ?? false,
            ]);

            return redirect()->route('invoice.email-recipients.index')
                ->with('success', 'Email recipient added successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to add recipient: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $recipient = InvoiceRecipient::findOrFail($id);
        $stores = Store::orderBy('store_number')->get();
        return view('invoice::email-recipients.edit', compact('recipient', 'stores'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'is_default' => 'boolean',
        ]);

        try {
            $recipient = InvoiceRecipient::findOrFail($id);

            // If this recipient is set as default for this store, unset other defaults for the same store
            if ($request->is_default) {
                InvoiceRecipient::where('store_id', $request->store_id)
                    ->where('id', '!=', $id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $recipient->update([
                'store_id' => $request->store_id,
                'name' => $request->name,
                'email' => $request->email,
                'is_default' => $request->is_default ?? false,
            ]);

            return redirect()->route('invoice.email-recipients.index')
                ->with('success', 'Email recipient updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update recipient: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $recipient = InvoiceRecipient::findOrFail($id);
            $recipient->delete();

            return redirect()->route('invoice.email-recipients.index')
                ->with('success', 'Email recipient deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete recipient: ' . $e->getMessage()]);
        }
    }
}
