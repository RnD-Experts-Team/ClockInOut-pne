<?php

namespace App\Services\Api\Admin\ModulesInvoice;
use App\Models\ModulesInvoice\InvoiceEmailTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EmailTemplateService
{
     
    public function index()
    {
        try {
            $templates = InvoiceEmailTemplate::orderBy('is_default', 'desc')
                ->orderBy('name')
                ->get();
            
            return response()->json($templates);
        } catch (\Exception $e) {
            Log::error('Error fetching invoice email templates: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load invoice email templates'
            ], 500);
        }
    }
     public function store($request)
    {
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

            return response()->json([
                'message' => 'Email template created successfully!'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create template: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to create template: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update($request, $id)
    {
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

            return response()->json([
                'message' => 'Email template updated successfully!'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update template: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to update template: ' . $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $template = InvoiceEmailTemplate::findOrFail($id);
            
            if ($template->is_default) {
                return response()->json([
                    'error' => 'Cannot delete the default template. Please set another template as default first.'
                ], 400);
            }

            $template->delete();

            return response()->json([
                'message' => 'Email template deleted successfully!'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to delete template: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to delete template: ' . $e->getMessage()
            ], 500);
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

            return response()->json([
                'message' => 'Default template updated successfully!'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to set default template: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to set default template: ' . $e->getMessage()
            ], 500);
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
            ], 200);

        } catch (\Exception $e) {
            Log::error('Template not found: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Template not found',
            ], 404);
        }
    }

     
}