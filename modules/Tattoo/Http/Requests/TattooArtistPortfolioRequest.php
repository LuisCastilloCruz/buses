<?php

namespace Modules\Tattoo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TattooArtistPortfolioRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => ['nullable', 'integer'],
            'tattoo_artist_id' => ['required', 'integer', 'exists:tenant.tattoo_artists,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages()
    {
        return [
            'tattoo_artist_id.required' => 'Debes seleccionar un artista.',
            'tattoo_artist_id.exists' => 'El artista seleccionado no existe.',
            'image.image' => 'El archivo debe ser una imagen válida.',
            'image.mimes' => 'La imagen debe ser JPG, JPEG, PNG o WEBP.',
            'image.max' => 'La imagen no debe pesar más de 6 MB.',
        ];
    }
}