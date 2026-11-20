<?php

namespace Modules\Tattoo\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Person;
use Modules\Tattoo\Models\TattooArtist;
use Modules\Tattoo\Models\TattooStyle;
use Modules\Tattoo\Models\TattooAppointment;
use Modules\Tattoo\Models\TattooSession;
use Modules\Tattoo\Http\Requests\TattooAppointmentRequest;
use Modules\Tattoo\Http\Resources\TattooAppointmentCollection;

class TattooAppointmentController extends Controller
{
    /**
     * Columnas permitidas para filtros.
     *
     * @var array
     */
    protected $allowedFilters = [
        'appointment_date',
        'status',
        'body_zone',
        'tattoo_size',
        'estimated_price',
        'deposit_amount',
    ];

    public function index(Request $request)
    {
        $records = TattooAppointment::query()
            ->with(['person', 'artist', 'style', 'consent'])
            ->withCount('sessions');

        if ($request->filled('customer_name')) {
            $search = trim((string) $request->input('customer_name'));
            $records->whereHas('person', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('artist_id')) {
            $records->where('tattoo_artist_id', $request->input('artist_id'));
        }

        if ($request->filled('status')) {
            $records->where('status', $request->input('status'));
        }

        if ($request->filled('appointment_date')) {
            $records->whereDate('appointment_date', $request->input('appointment_date'));
        }

        $appointments = $records->latest('id')->paginate(15);

        $persons = Person::query()
            ->select('id', 'name', 'number')
            ->orderBy('name')
            ->get();

        $artists = TattooArtist::query()
            ->select('id', 'stage_name')
            ->where('active', true)
            ->orderBy('stage_name')
            ->get();

        $styles = TattooStyle::query()
            ->select('id', 'name')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('tattoo::appointments.index', compact(
            'appointments',
            'persons',
            'artists',
            'styles'
        ));
    }

    public function columns()
    {
        return [
            'appointment_date' => 'Fecha',
            'status' => 'Estado',
            'body_zone' => 'Zona',
            'customer_name' => 'Cliente',
            'artist_name' => 'Artista',
        ];
    }

    public function records(Request $request)
    {
        $records = TattooAppointment::query()
            ->with(['person', 'artist', 'style', 'consent'])
            ->withCount('sessions');

        $column = $request->input('column');
        $value = trim((string) $request->input('value', ''));

        if ($column && $value !== '') {
            switch ($column) {
                case 'customer_name':
                    $records->whereHas('person', function ($q) use ($value) {
                        $q->where('name', 'like', "%{$value}%");
                    });
                    break;

                case 'artist_name':
                    $records->whereHas('artist', function ($q) use ($value) {
                        $q->where('stage_name', 'like', "%{$value}%");
                    });
                    break;

                default:
                    if (in_array($column, $this->allowedFilters)) {
                        $records->where($column, 'like', "%{$value}%");
                    }
                    break;
            }
        }

        if ($request->filled('date_from')) {
            $records->whereDate('appointment_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $records->whereDate('appointment_date', '<=', $request->input('date_to'));
        }

        return new TattooAppointmentCollection(
            $records->latest('id')->paginate(config('tenant.items_per_page'))
        );
    }

    public function record($id)
    {
        $record = TattooAppointment::with([
            'person',
            'artist',
            'style',
            'sessions',
            'consent',
        ])->withCount('sessions')->findOrFail($id);

        $sessionTotal = (float) $record->sessions->sum('price');
        $sessionPaid = (float) $record->sessions->sum('payment_amount');
        $estimated = (float) $record->estimated_price;
        $deposit = (float) $record->deposit_amount;

        return response()->json([
            'id' => $record->id,
            'person_id' => $record->person_id,
            'tattoo_artist_id' => $record->tattoo_artist_id,
            'tattoo_style_id' => $record->tattoo_style_id,
            'appointment_date' => $record->appointment_date,
            'appointment_time' => $record->appointment_time,
            'status' => $record->status,
            'body_zone' => $record->body_zone,
            'tattoo_size' => $record->tattoo_size,
            'estimated_price' => $record->estimated_price,
            'deposit_amount' => $record->deposit_amount,
            'idea' => $record->idea,
            'observations' => $record->observations,

            'person_name' => optional($record->person)->name,
            'artist_name' => optional($record->artist)->stage_name,
            'style_name' => optional($record->style)->name,

            'sessions_count' => (int) $record->sessions_count,
            'has_consent' => !is_null($record->consent),
            'consent_accepted' => optional($record->consent)->accepted,
            'consent_signed_date' => optional($record->consent)->signed_date,

            'session_total' => $sessionTotal,
            'session_paid' => $sessionPaid,
            'pending_balance' => max($estimated - ($deposit + $sessionPaid), 0),
        ]);
    }

    public function store(TattooAppointmentRequest $request)
    {
        DB::beginTransaction();

        try {
            $id = (int) $request->input('id');

            $artistId = (int) $request->input('tattoo_artist_id');
            $appointmentDate = $request->input('appointment_date');
            $appointmentTime = $request->input('appointment_time');

            if ($appointmentTime && $this->artistHasAppointmentConflict($artistId, $appointmentDate, $appointmentTime, $id ?: null)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'El artista ya tiene otra cita programada en la misma fecha y hora.',
                ], 422);
            }

            if ($appointmentTime && $this->artistHasSessionConflict($artistId, $appointmentDate, $appointmentTime)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'El artista ya tiene una sesión programada en la misma fecha y hora.',
                ], 422);
            }

            $record = TattooAppointment::firstOrNew(['id' => $id]);
            $record->fill([
                'person_id' => $request->input('person_id'),
                'tattoo_artist_id' => $artistId,
                'tattoo_style_id' => $request->input('tattoo_style_id') ?: null,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime ?: null,
                'status' => $request->input('status') ?: 'pending',
                'body_zone' => $request->input('body_zone'),
                'tattoo_size' => $request->input('tattoo_size'),
                'estimated_price' => $request->input('estimated_price') ?: 0,
                'deposit_amount' => $request->input('deposit_amount') ?: 0,
                'idea' => $request->input('idea'),
                'observations' => $request->input('observations'),
            ]);
            $record->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $id ? 'Cita editada con éxito' : 'Cita registrada con éxito',
                'data' => $record->load(['person', 'artist', 'style', 'consent'])->loadCount('sessions'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar la cita',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $record = TattooAppointment::with(['sessions', 'consent'])->findOrFail($id);

            if ($record->sessions->count() > 0) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la cita porque tiene sesiones registradas.',
                ], 422);
            }

            if ($record->consent) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la cita porque ya tiene consentimiento registrado.',
                ], 422);
            }

            $record->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cita eliminada con éxito',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar la cita',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Valida si el artista ya tiene una cita en la misma fecha y hora.
     */
    protected function artistHasAppointmentConflict(int $artistId, string $date, string $time, ?int $ignoreId = null): bool
    {
        $query = TattooAppointment::query()
            ->where('tattoo_artist_id', $artistId)
            ->whereDate('appointment_date', $date)
            ->where('appointment_time', $time);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
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
}