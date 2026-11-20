<?php

namespace Modules\Tattoo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TattooStyleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}