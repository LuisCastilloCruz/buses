<?php

namespace Modules\Tattoo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Tattoo\Models\TattooAppointment;
use Modules\Tattoo\Models\TattooSession;

class TattooAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => ['nullable', 'integer'],
            'person_id' => ['required', 'integer', 'exists:tenant.persons,id'],
            'tattoo_artist_id' => ['required', 'integer', 'exists:tenant.tattoo_artists,id'],
            'tattoo_style_id' => ['nullable', 'integer', 'exists:tenant.tattoo_styles,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['nullable', 'date_format:H:i'],
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])],
            'body_zone' => ['nullable', 'string', 'max:255'],
            'tattoo_size' => ['nullable', 'string', 'max:255'],
            'estimated_price' => ['nullable', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'idea' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $id = (int) $this->input('id');
            $artistId = $this->input('tattoo_artist_id');
            $date = $this->input('appointment_date');
            $time = $this->input('appointment_time');
            $status = $this->input('status', 'pending');
            $estimatedPrice = (float) $this->input('estimated_price', 0);
            $depositAmount = (float) $this->input('deposit_amount', 0);

            if ($estimatedPrice > 0 && $depositAmount > $estimatedPrice) {
                $validator->errors()->add('deposit_amount', 'El adelanto no puede ser mayor al precio estimado.');
            }

            if (!$artistId || !$date || !$time || $status === 'cancelled') {
                return;
            }

            $conflictAppointment = TattooAppointment::query()
                ->when($id > 0, function ($q) use ($id) {
                    $q->where('id', '!=', $id);
                })
                ->where('tattoo_artist_id', $artistId)
                ->whereDate('appointment_date', $date)
                ->where('appointment_time', $time)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($conflictAppointment) {
                $validator->errors()->add(
                    'appointment_time',
                    'El artista ya tiene otra cita registrada en la misma fecha y hora.'
                );
            }

            $conflictSession = TattooSession::query()
                ->whereHas('appointment', function ($q) use ($artistId) {
                    $q->where('tattoo_artist_id', $artistId);
                })
                ->whereDate('session_date', $date)
                ->where('session_time', $time)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($conflictSession) {
                $validator->errors()->add(
                    'appointment_time',
                    'El artista ya tiene una sesión programada en la misma fecha y hora.'
                );
            }
        });
    }

    public function messages()
    {
        return [
            'person_id.required' => 'Debes seleccionar un cliente.',
            'person_id.exists' => 'El cliente seleccionado no existe.',
            'tattoo_artist_id.required' => 'Debes seleccionar un artista.',
            'tattoo_artist_id.exists' => 'El artista seleccionado no existe.',
            'tattoo_style_id.exists' => 'El estilo seleccionado no existe.',
            'appointment_date.required' => 'La fecha de la cita es obligatoria.',
            'appointment_date.date' => 'La fecha de la cita no es válida.',
            'appointment_time.date_format' => 'La hora debe tener formato HH:MM.',
            'status.in' => 'El estado seleccionado no es válido.',
            'estimated_price.numeric' => 'El precio estimado debe ser numérico.',
            'deposit_amount.numeric' => 'El adelanto debe ser numérico.',
        ];
    }
}