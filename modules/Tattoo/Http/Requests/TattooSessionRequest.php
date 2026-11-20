<?php

namespace Modules\Tattoo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Tattoo\Models\TattooSession;
use Modules\Tattoo\Models\TattooAppointment;

class TattooSessionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => ['nullable', 'integer'],
            'tattoo_appointment_id' => ['required', 'integer', 'exists:tenant.tattoo_appointments,id'],
            'session_number' => ['required', 'integer', 'min:1'],
            'session_date' => ['nullable', 'date'],
            'session_time' => ['nullable', 'date_format:H:i'],
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'price' => ['nullable', 'numeric', 'min:0'],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $id = (int) $this->input('id');
            $appointmentId = (int) $this->input('tattoo_appointment_id');
            $sessionNumber = (int) $this->input('session_number');
            $sessionDate = $this->input('session_date');
            $sessionTime = $this->input('session_time');
            $status = $this->input('status', 'pending');
            $price = (float) $this->input('price', 0);
            $paymentAmount = (float) $this->input('payment_amount', 0);

            if ($price > 0 && $paymentAmount > $price) {
                $validator->errors()->add('payment_amount', 'El pago no puede ser mayor al precio de la sesión.');
            }

            $duplicateNumber = TattooSession::query()
                ->when($id > 0, function ($q) use ($id) {
                    $q->where('id', '!=', $id);
                })
                ->where('tattoo_appointment_id', $appointmentId)
                ->where('session_number', $sessionNumber)
                ->exists();

            if ($duplicateNumber) {
                $validator->errors()->add(
                    'session_number',
                    'Ya existe una sesión con ese número para esta cita.'
                );
            }

            $appointment = TattooAppointment::query()
                ->with('artist')
                ->find($appointmentId);

            if (!$appointment) {
                $validator->errors()->add('tattoo_appointment_id', 'La cita seleccionada no existe.');
                return;
            }

            if (!$sessionDate || !$sessionTime || $status === 'cancelled') {
                return;
            }

            $artistId = $appointment->tattoo_artist_id;

            $conflictSession = TattooSession::query()
                ->when($id > 0, function ($q) use ($id) {
                    $q->where('id', '!=', $id);
                })
                ->whereHas('appointment', function ($q) use ($artistId) {
                    $q->where('tattoo_artist_id', $artistId);
                })
                ->whereDate('session_date', $sessionDate)
                ->where('session_time', $sessionTime)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($conflictSession) {
                $validator->errors()->add(
                    'session_time',
                    'El artista ya tiene otra sesión en la misma fecha y hora.'
                );
            }

            $conflictAppointment = TattooAppointment::query()
                ->where('id', '!=', $appointmentId)
                ->where('tattoo_artist_id', $artistId)
                ->whereDate('appointment_date', $sessionDate)
                ->where('appointment_time', $sessionTime)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($conflictAppointment) {
                $validator->errors()->add(
                    'session_time',
                    'El artista ya tiene una cita agendada en la misma fecha y hora.'
                );
            }
        });
    }

    public function messages()
    {
        return [
            'tattoo_appointment_id.required' => 'Debes seleccionar una cita.',
            'tattoo_appointment_id.exists' => 'La cita seleccionada no existe.',
            'session_number.required' => 'Debes indicar el número de sesión.',
            'session_number.integer' => 'El número de sesión debe ser un entero.',
            'session_number.min' => 'El número de sesión debe ser mayor o igual a 1.',
            'session_date.date' => 'La fecha de sesión no es válida.',
            'session_time.date_format' => 'La hora debe tener formato HH:MM.',
            'status.in' => 'El estado de la sesión no es válido.',
            'price.numeric' => 'El precio de la sesión debe ser numérico.',
            'payment_amount.numeric' => 'El pago de la sesión debe ser numérico.',
        ];
    }
}