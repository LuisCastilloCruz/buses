<?php

namespace App\Models\Tenant;

use Modules\Expense\Models\Expense;
use Modules\Expense\Models\ExpensePayment;
use Modules\Sale\Models\TechnicalService;
use Modules\Finance\Models\IncomePayment;
use Modules\Transporte\Models\TransporteEncomienda;

class CashDocument extends ModelTenant
{
    // protected $with = ['document'];

    public $timestamps = false;

    protected $fillable = [
        'cash_id',
        'document_id',
        'sale_note_id',

        'technical_service_id',
        // 'expense_id',
        'expense_payment_id',
        'purchase_id',
        'income_payment_id',
    ];



    public function cash()
    {
        return $this->belongsTo(Cash::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function sale_note()
    {
        return $this->belongsTo(SaleNote::class);
    }

    public function expense_payment()
    {
        return $this->belongsTo(ExpensePayment::class);
    }
    public function income_payment()
    {
        return $this->belongsTo(IncomePayment::class);
    }

    public function technical_service()
    {
        return $this->belongsTo(TechnicalService::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    // public function expense()
    // {
    //     return $this->belongsTo(Expense::class);
    // }

    public function transporte_encomienda()
    {
        return $this->belongsTo(TransporteEncomienda::class,'sale_note_id','note_id');
    }

}
