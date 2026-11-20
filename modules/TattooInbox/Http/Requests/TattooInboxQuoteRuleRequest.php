<?php

namespace Modules\TattooInbox\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TattooInboxQuoteRuleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'rules' => ['required', 'array', 'min:1'],
            'rules.*.rule_key' => ['required', 'string', 'max:100'],
            'rules.*.rule_value' => ['required', 'numeric'],
            'rules.*.description' => ['nullable', 'string'],
            'rules.*.active' => ['nullable', 'boolean'],
        ];
    }
}