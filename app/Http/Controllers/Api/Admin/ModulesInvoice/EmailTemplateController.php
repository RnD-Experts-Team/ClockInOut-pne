<?php

namespace App\Http\Controllers\Api\Admin\ModulesInvoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\ModulesInvoice\StoreUpdateInvoiceEmailTemplateRequest;
use App\Services\Api\Admin\ModulesInvoice\EmailTemplateService;

class EmailTemplateController extends Controller
{
     protected $invoiceEmailTemplateService;

    public function __construct(EmailTemplateService $invoiceEmailTemplateService)
    {
        $this->invoiceEmailTemplateService = $invoiceEmailTemplateService;
    }

    public function index()
    {
        return $this->invoiceEmailTemplateService->index();
    }
    public function store(StoreUpdateInvoiceEmailTemplateRequest $request)
    {
        return $this->invoiceEmailTemplateService->store($request);
    }

    public function update(StoreUpdateInvoiceEmailTemplateRequest $request, $id)
    {
        return $this->invoiceEmailTemplateService->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->invoiceEmailTemplateService->destroy($id);
    }

    public function setDefault($id)
    {
        return $this->invoiceEmailTemplateService->setDefault($id);
    }

    public function preview($id)
    {
        return $this->invoiceEmailTemplateService->preview($id);
    }

}
