<?php

namespace Modules\Transporte\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Transporte\Models\TransporteProgramacion;
use Modules\Transporte\Models\TransporteTerminales;
use Modules\Transporte\Traits\PasajerosRuta;

class TransporteWebController extends Controller
{
    use PasajerosRuta;

    public function index()
    {
        return view('transporte-web.index');
    }

    public function getTerminales()
    {
        try {
            Log::info('[TransporteWeb] getTerminales IN');

            $terminales = TransporteTerminales::with('destino')
                ->orderBy('nombre')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nombre' => $item->nombre,
                        'destino' => optional($item->destino)->nombre,
                        'color' => $item->color,
                    ];
                })
                ->values();

            Log::info('[TransporteWeb] getTerminales OK', [
                'count' => $terminales->count(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $terminales,
            ]);
        } catch (\Throwable $th) {
            Log::error('[TransporteWeb] getTerminales ERROR', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar terminales',
                'error' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ], 500);
        }
    }

    public function getProgramacionesDisponibles(Request $request)
    {
        try {
            // Permitir pruebas por GET o uso normal por POST
            $origenId = $request->input('origen_id');
            $destinoId = $request->input('destino_id');
            $fechaSalida = $request->input('fecha_salida');

            if (!$origenId || !$destinoId || !$fechaSalida) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faltan parámetros requeridos: origen_id, destino_id y fecha_salida',
                    'received' => $request->all(),
                    'method' => $request->method(),
                    'example_get' => url('/transportes-public/programaciones-disponibles') . '?origen_id=15&destino_id=19&fecha_salida=2026-04-05',
                    'example_post' => [
                        'origen_id' => 15,
                        'destino_id' => 19,
                        'fecha_salida' => '2026-04-05',
                    ],
                ], 422);
            }

            Log::info('[TransporteWeb] getProgramacionesDisponibles IN', [
                'method' => $request->method(),
                'origen_id' => $origenId,
                'destino_id' => $destinoId,
                'fecha_salida' => $fechaSalida,
            ]);

            $origen = TransporteTerminales::find($origenId);
            $destino = TransporteTerminales::find($destinoId);

            if (!$origen || !$destino) {
                return response()->json([
                    'success' => false,
                    'message' => 'Origen o destino no existen en este tenant',
                    'origen_id' => $origenId,
                    'destino_id' => $destinoId,
                    'origen_exists' => !is_null($origen),
                    'destino_exists' => !is_null($destino),
                ], 404);
            }

            $query = TransporteProgramacion::with([
                'origen',
                'destino',
                'vehiculo',
                'vehiculo.seats',
            ])
                ->where('terminal_origen_id', $origenId)
                ->where('terminal_destino_id', $destinoId)
                ->where('active', true);

            // Tu código original usa WhereEqualsOrBiggerDate(). :contentReference[oaicite:1]{index=1}
            // Aquí lo hacemos seguro: si existe el scope se usa, si no, se cae a whereDate.
            $model = $query->getModel();
            if (method_exists($model, 'scopeWhereEqualsOrBiggerDate')) {
                $query->WhereEqualsOrBiggerDate($fechaSalida);
                Log::info('[TransporteWeb] usando scope WhereEqualsOrBiggerDate');
            } else {
                Log::warning('[TransporteWeb] scopeWhereEqualsOrBiggerDate NO existe, usando fallback');
                $table = $model->getTable();

                if (Schema::hasColumn($table, 'fecha_salida')) {
                    $query->whereDate('fecha_salida', '>=', $fechaSalida);
                } elseif (Schema::hasColumn($table, 'fecha_viaje')) {
                    $query->whereDate('fecha_viaje', '>=', $fechaSalida);
                } elseif (Schema::hasColumn($table, 'date_of_issue')) {
                    $query->whereDate('date_of_issue', '>=', $fechaSalida);
                } else {
                    Log::warning('[TransporteWeb] no se encontró columna de fecha conocida en la tabla', [
                        'table' => $table,
                    ]);
                }
            }

            $date = Carbon::parse($fechaSalida);
            $today = Carbon::now();

            if ($date->isSameDay($today)) {
                $time = date('H:i:s', strtotime('-120 minutes'));
                $query->whereRaw("TIME_FORMAT(hora_salida,'%H:%i:%s') >= ?", [$time]);
            }

            $items = $query->get();

            $list = $items->map(function ($item) use ($fechaSalida) {
                return [
                    'id' => $item->id,
                    'fecha_salida' => $fechaSalida,
                    'hora_salida' => $item->hora_salida ?: 'Por confirmar',
                    'origen' => [
                        'id' => optional($item->origen)->id,
                        'nombre' => optional($item->origen)->nombre,
                    ],
                    'destino' => [
                        'id' => optional($item->destino)->id,
                        'nombre' => optional($item->destino)->nombre,
                    ],
                    'vehiculo' => [
                        'id' => optional($item->vehiculo)->id,
                        'nombre' => optional($item->vehiculo)->nombre,
                        'placa' => optional($item->vehiculo)->placa,
                        'pisos' => optional($item->vehiculo)->pisos,
                        'asientos' => optional($item->vehiculo)->asientos,
                        'ancho_vehiculo' => optional($item->vehiculo)->ancho_vehiculo,
                    ],
                    'precio' => null,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $list,
            ]);
        } catch (\Throwable $th) {
            Log::error('[TransporteWeb] getProgramacionesDisponibles ERROR', [
                'payload' => $request->all(),
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar programaciones',
                'error' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'payload' => $request->all(),
            ], 500);
        }
    }

    public function getAsientosDisponibles(Request $request)
    {
        try {
            $programacionId = $request->input('programacion_id');
            $fechaSalida = $request->input('fecha_salida');

            if (!$programacionId || !$fechaSalida) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faltan parámetros requeridos: programacion_id y fecha_salida',
                    'received' => $request->all(),
                    'method' => $request->method(),
                    'example_get' => url('/transportes-public/asientos-disponibles') . '?programacion_id=1&fecha_salida=2026-04-05',
                    'example_post' => [
                        'programacion_id' => 1,
                        'fecha_salida' => '2026-04-05',
                    ],
                ], 422);
            }

            Log::info('[TransporteWeb] getAsientosDisponibles IN', [
                'method' => $request->method(),
                'programacion_id' => $programacionId,
                'fecha_salida' => $fechaSalida,
            ]);

            $programacion = TransporteProgramacion::with([
                'vehiculo',
                'vehiculo.seats',
                'origen',
                'destino',
                'programacion',
            ])->findOrFail($programacionId);

            $vehiculo = $programacion->vehiculo;

            if (!$vehiculo) {
                return response()->json([
                    'success' => false,
                    'message' => 'La programación no tiene vehículo asociado',
                    'programacion_id' => $programacionId,
                ], 404);
            }

            $layout = $vehiculo->seats()
                ->orderBy('piso')
                ->orderBy('numero_asiento')
                ->get();

            $pasajes = $this->getPasajeros($programacion, $fechaSalida, true);

            $ocupados = [];
            foreach ($pasajes as $pasaje) {
                if (!empty($pasaje->asiento_id)) {
                    $ocupados[$pasaje->asiento_id] = (int) $pasaje->estado_asiento_id;
                }
            }

            $asientos = $layout->map(function ($seat) use ($ocupados) {
                $estadoId = $ocupados[$seat->id] ?? 1;

                $estado = 'free';
                if ($estadoId === 2) {
                    $estado = 'busy';
                } elseif ($estadoId === 3) {
                    $estado = 'reserved';
                }

                return [
                    'id' => $seat->id,
                    'numero_asiento' => $seat->numero_asiento,
                    'piso' => (int) $seat->piso,
                    'left' => (int) $seat->left,
                    'top' => (int) $seat->top,
                    'type' => $seat->type,
                    'estado_asiento_id' => $estadoId,
                    'estado' => $estado,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'programacion_id' => $programacion->id,
                    'vehiculo' => [
                        'id' => $vehiculo->id,
                        'nombre' => $vehiculo->nombre,
                        'placa' => $vehiculo->placa,
                        'pisos' => (int) $vehiculo->pisos,
                        'ancho_vehiculo' => (int) ($vehiculo->ancho_vehiculo ?: 680),
                        'asientos' => (int) $vehiculo->asientos,
                    ],
                    'ruta' => [
                        'origen' => optional($programacion->origen)->nombre,
                        'destino' => optional($programacion->destino)->nombre,
                        'hora_salida' => $programacion->hora_salida,
                        'fecha_salida' => $fechaSalida,
                    ],
                    'asientos' => $asientos,
                ],
            ]);
        } catch (\Throwable $th) {
            Log::error('[TransporteWeb] getAsientosDisponibles ERROR', [
                'payload' => $request->all(),
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar asientos',
                'error' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'payload' => $request->all(),
            ], 500);
        }
    }
}