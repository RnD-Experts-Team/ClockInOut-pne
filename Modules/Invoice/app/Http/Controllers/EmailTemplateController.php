<?php

namespace Modules\Invoice\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Invoice\Models\InvoiceEmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = InvoiceEmailTemplate::orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
        
        return view('invoice::email-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('invoice::email-templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'is_default' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // If this template is set as default, unset other defaults
            if ($request->is_default) {
                InvoiceEmailTemplate::where('is_default', true)->update(['is_default' => false]);
            }

            InvoiceEmailTemplate::create([
                'name' => $request->name,
                'subject' => $request->subject,
                'body' => $request->body,
                'is_default' => $request->is_default ?? false,
            ]);

            DB::commit();

            return redirect()->route('invoice.email-templates.index')
                ->with('success', 'Email template created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create template: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $template = InvoiceEmailTemplate::findOrFail($id);
        return view('invoice::email-templates.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'is_default' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $template = InvoiceEmailTemplate::findOrFail($id);

            // If this template is set as default, unset other defaults
            if ($request->is_default) {
                InvoiceEmailTemplate::where('id', '!=', $id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $template->update([
                'name' => $request->name,
                'subject' => $request->subject,
                'body' => $request->body,
                'is_default' => $request->is_default ?? false,
            ]);

            DB::commit();

            return redirect()->route('invoice.email-templates.index')
                ->with('success', 'Email template updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update template: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $template = InvoiceEmailTemplate::findOrFail($id);
            
            if ($template->is_default) {
                return back()->withErrors(['error' => 'Cannot delete the default template. Please set another template as default first.']);
            }

            $template->delete();

            return redirect()->route('invoice.email-templates.index')
                ->with('success', 'Email template deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete template: ' . $e->getMessage()]);
        }
    }

    public function setDefault($id)
    {
        DB::beginTransaction();
        try {
            // Unset all defaults
            InvoiceEmailTemplate::where('is_default', true)->update(['is_default' => false]);
            
            // Set this one as default
            $template = InvoiceEmailTemplate::findOrFail($id);
            $template->update(['is_default' => true]);

            DB::commit();

            return redirect()->route('invoice.email-templates.index')
                ->with('success', 'Default template updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to set default template: ' . $e->getMessage()]);
        }
    }

    public function preview($id)
    {
        try {
            $template = InvoiceEmailTemplate::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'body' => $template->body,
                'subject' => $template->subject,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found',
            ], 404);
        }
    }
}
