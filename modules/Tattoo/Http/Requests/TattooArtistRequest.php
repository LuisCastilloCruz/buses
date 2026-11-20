<?php

namespace Modules\Tattoo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TattooArtistRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer', 'exists:tenant.users,id'],
            'stage_name' => ['required', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'stage_name.required' => 'El nombre artístico es obligatorio.',
            'photo.image' => 'El archivo de foto debe ser una imagen válida.',
            'photo.mimes' => 'La foto debe ser JPG, JPEG, PNG o WEBP.',
            'photo.max' => 'La foto no debe pesar más de 4 MB.',
        ];
    }
}