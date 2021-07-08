<?php


namespace Modules\Transporte\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransporteUserTerminalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'terminal_id' => ['required'],
            'user_id' => ['required','exists:tenant.transporte_destinos,id']
            //
        ];
    }
}
