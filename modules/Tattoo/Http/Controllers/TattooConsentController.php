<?php

namespace Modules\Tattoo\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Tattoo\Models\TattooConsent;
use Modules\Tattoo\Models\TattooAppointment;
use Modules\Tattoo\Http\Requests\TattooConsentRequest;
use Modules\Tattoo\Http\Resources\TattooConsentCollection;

class TattooConsentController extends Controller
{
    /**
     * Columnas permitidas.
     *
     * @var array
     */
    protected $allowedFilters = [
        'signature_name',
        'signed_date',
        'accepted',
    ];

    public function index(Request $request)
    {
        $records = TattooConsent::query()
            ->with(['appointment.person', 'appointment.artist', 'appointment.style']);

        if ($request->filled('customer_name')) {
            $customerName = trim((string) $request->input('customer_name'));
            $records->whereHas('appointment.person', function ($q) use ($customerName) {
                $q->where('name', 'like', "%{$customerName}%");
            });
        }

        if ($request->filled('accepted')) {
            $records->where('accepted', $request->input('accepted'));
        }

        if ($request->filled('signed_date')) {
            $records->whereDate('signed_date', $request->input('signed_date'));
        }

        $consents = $records->latest('id')->paginate(15);

        $appointments = TattooAppointment::query()
            ->with(['person', 'artist'])
            ->orderByDesc('id')
            ->get();

        return view('tattoo::consents.index', compact(
            'consents',
            'appointments'
        ));
    }

    public function columns()
    {
        return [
            'signature_name' => 'Firma',
            'signed_date' => 'Fecha firma',
            'accepted' => 'Aceptado',
        ];
    }

    public function records(Request $request)
    {
        $records = TattooConsent::query()
            ->with(['appointment.person', 'appointment.artist', 'appointment.style']);

        $column = $request->input('column');
        $value = trim((string) $request->input('value', ''));

        if ($column && $value !== '' && in_array($column, $this->allowedFilters)) {
            if ($column === 'accepted') {
                $records->where('accepted', $value);
            } else {
                $records->where($column, 'like', "%{$value}%");
            }
        }

        if ($request->filled('customer_name')) {
            $customerName = trim((string) $request->input('customer_name'));
            $records->whereHas('appointment.person', function ($q) use ($customerName) {
                $q->where('name', 'like', "%{$customerName}%");
            });
        }

        return new TattooConsentCollection(
            $records->latest('id')->paginate(config('tenant.items_per_page'))
        );
    }

    public function record($id)
    {
        return TattooConsent::with(['appointment.person', 'appointment.artist', 'appointment.style'])->findOrFail($id);
    }

    public function store(TattooConsentRequest $request)
    {
        DB::beginTransaction();

        try {
            $id = (int) $request->input('id');

            $appointment = TattooAppointment::find($request->input('tattoo_appointment_id'));
            if (!$appointment) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'La cita seleccionada no existe.',
                ], 422);
            }

            $record = TattooConsent::firstOrNew(['id' => $id]);
            $record->fill([
                'tattoo_appointment_id' => $request->input('tattoo_appointment_id'),
                'signed_date' => $request->input('signed_date') ?: null,
                'accepted' => $request->has('accepted') ? (bool) $request->input('accepted') : false,
                'medical_notes' => $request->input('medical_notes'),
                'allergies' => $request->input('allergies'),
                'observations' => $request->input('observations'),
                'signature_name' => $request->input('signature_name'),
            ]);
            $record->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $id ? 'Consentimiento editado con éxito' : 'Consentimiento registrado con éxito',
                'data' => $record->load(['appointment.person', 'appointment.artist', 'appointment.style']),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar el consentimiento',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $record = TattooConsent::findOrFail($id);
            $record->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Consentimiento eliminado con éxito',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el consentimiento',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }
}