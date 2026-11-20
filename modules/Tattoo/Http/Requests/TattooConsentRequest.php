<?php

namespace Modules\Tattoo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TattooConsentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => ['nullable', 'integer'],
            'tattoo_appointment_id' => ['required', 'integer'],
            'signed_date' => ['nullable', 'date'],
            'accepted' => ['nullable', 'boolean'],
            'medical_notes' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'signature_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}