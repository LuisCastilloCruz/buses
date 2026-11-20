<?php

namespace Modules\Tattoo\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Tattoo\Models\TattooSession;
use Modules\Tattoo\Models\TattooAppointment;
use Modules\Tattoo\Models\TattooArtist;
use Modules\Tattoo\Http\Requests\TattooSessionRequest;
use Modules\Tattoo\Http\Resources\TattooSessionCollection;

class TattooSessionController extends Controller
{
    /**
     * Columnas permitidas.
     *
     * @var array
     */
    protected $allowedFilters = [
        'status',
        'session_date',
        'notes',
        'session_number',
        'price',
        'payment_amount',
    ];

    public function index(Request $request)
    {
        $records = TattooSession::query()
            ->with(['appointment.person', 'appointment.artist', 'appointment.style']);

        if ($request->filled('customer_name')) {
            $customerName = trim((string) $request->input('customer_name'));
            $records->whereHas('appointment.person', function ($q) use ($customerName) {
                $q->where('name', 'like', "%{$customerName}%");
            });
        }

        if ($request->filled('artist_id')) {
            $records->whereHas('appointment', function ($q) use ($request) {
                $q->where('tattoo_artist_id', $request->input('artist_id'));
            });
        }

        if ($request->filled('tattoo_appointment_id')) {
            $records->where('tattoo_appointment_id', $request->input('tattoo_appointment_id'));
        }

        if ($request->filled('status')) {
            $records->where('status', $request->input('status'));
        }

        if ($request->filled('session_date')) {
            $records->whereDate('session_date', $request->input('session_date'));
        }

        $sessions = $records->latest('id')->paginate(15);

        $appointments = TattooAppointment::query()
            ->with(['person', 'artist'])
            ->orderByDesc('id')
            ->get();

        $artists = TattooArtist::query()
            ->select('id', 'stage_name')
            ->where('active', true)
            ->orderBy('stage_name')
            ->get();

        return view('tattoo::sessions.index', compact(
            'sessions',
            'appointments',
            'artists'
        ));
    }

    public function columns()
    {
        return [
            'status' => 'Estado',
            'session_date' => 'Fecha',
            'session_number' => 'Sesión',
        ];
    }

    public function records(Request $request)
    {
        $records = TattooSession::query()
            ->with(['appointment.person', 'appointment.artist', 'appointment.style']);

        $column = $request->input('column');
        $value = trim((string) $request->input('value', ''));

        if ($column && $value !== '' && in_array($column, $this->allowedFilters)) {
            $records->where($column, 'like', "%{$value}%");
        }

        if ($request->filled('customer_name')) {
            $customerName = trim((string) $request->input('customer_name'));
            $records->whereHas('appointment.person', function ($q) use ($customerName) {
                $q->where('name', 'like', "%{$customerName}%");
            });
        }

        if ($request->filled('artist_name')) {
            $artistName = trim((string) $request->input('artist_name'));
            $records->whereHas('appointment.artist', function ($q) use ($artistName) {
                $q->where('stage_name', 'like', "%{$artistName}%");
            });
        }

        if ($request->filled('tattoo_appointment_id')) {
            $records->where('tattoo_appointment_id', $request->input('tattoo_appointment_id'));
        }

        return new TattooSessionCollection(
            $records->latest('id')->paginate(config('tenant.items_per_page'))
        );
    }

    public function record($id)
    {
        return TattooSession::with(['appointment.person', 'appointment.artist', 'appointment.style'])->findOrFail($id);
    }

    public function store(TattooSessionRequest $request)
    {
        DB::beginTransaction();

        try {
            $id = (int) $request->input('id');
            $appointmentId = (int) $request->input('tattoo_appointment_id');
            $sessionDate = $request->input('session_date');
            $sessionTime = $request->input('session_time');

            $appointment = TattooAppointment::with(['artist', 'sessions'])
                ->findOrFail($appointmentId);

            $artistId = (int) $appointment->tattoo_artist_id;

            if ($sessionDate && $sessionTime) {
                if ($this->artistHasSessionConflict($artistId, $sessionDate, $sessionTime, $id ?: null)) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'El artista ya tiene otra sesión programada en la misma fecha y hora.',
                    ], 422);
                }

                if ($this->artistHasAppointmentConflict($artistId, $sessionDate, $sessionTime, $appointment->id)) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'El artista ya tiene una cita programada en la misma fecha y hora.',
                    ], 422);
                }
            }

            $record = TattooSession::firstOrNew(['id' => $id]);
            $record->fill([
                'tattoo_appointment_id' => $appointmentId,
                'session_number' => $request->input('session_number'),
                'session_date' => $sessionDate ?: null,
                'session_time' => $sessionTime ?: null,
                'status' => $request->input('status') ?: 'pending',
                'price' => $request->input('price') ?: 0,
                'payment_amount' => $request->input('payment_amount') ?: 0,
                'notes' => $request->input('notes'),
            ]);
            $record->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $id ? 'Sesión editada con éxito' : 'Sesión registrada con éxito',
                'data' => $record->load(['appointment.person', 'appointment.artist', 'appointment.style']),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar la sesión',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $record = TattooSession::findOrFail($id);
            $record->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sesión eliminada con éxito',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar la sesión',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Valida si el artista ya tiene una sesión en la misma fecha y hora.
     */
    protected function artistHasSessionConflict(int $artistId, string $date, string $time, ?int $ignoreSessionId = null): bool
    {
        $query = TattooSession::query()
            ->whereDate('session_date', $date)
            ->where('session_time', $time)
            ->whereHas('appointment', function ($q) use ($artistId) {
                $q->where('tattoo_artist_id', $artistId);
            });

        if ($ignoreSessionId) {
            $query->where('id', '!=', $ignoreSessionId);
        }

        return $query->exists();
    }

    /**
     * Valida si el artista ya tiene una cita en la misma fecha y hora.
     */
    protected function artistHasAppointmentConflict(int $artistId, string $date, string $time, ?int $ignoreAppointmentId = null): bool
    {
        $query = TattooAppointment::query()
            ->where('tattoo_artist_id', $artistId)
            ->whereDate('appointment_date', $date)
            ->where('appointment_time', $time);

        if ($ignoreAppointmentId) {
            $query->where('id', '!=', $ignoreAppointmentId);
        }

        return $query->exists();
    }
}