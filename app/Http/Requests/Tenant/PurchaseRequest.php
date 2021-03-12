<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        if($this->input('document_type_id')=='07'|| $this->input('document_type_id')=='08' )
        {
            return [
                'supplier_id' => [
                    'required',
                ],
                'number' => [
                    'required',
                    'numeric'
                ],
                'series' => [
                    'required',
                ],
                'date_of_issue' => [
                    'required',
                ],
                'note.series' => [
                    'required',
                ],
                'note.number' => [
                    'required',
                ],
                'note.note_description' => [
                    'required',
                ],
            ];
        }
        else {
            return [
                'supplier_id' => [
                    'required',
                ],
                'number' => [
                    'required',
                    'numeric'
                ],
                'series' => [
                    'required',
                ],
                'date_of_issue' => [
                    'required',
                ]
            ];
        }
    }
}
