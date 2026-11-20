<?php

namespace Modules\Tattoo\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Tattoo\Models\TattooStyle;
use Modules\Tattoo\Http\Requests\TattooStyleRequest;
use Modules\Tattoo\Http\Resources\TattooStyleCollection;

class TattooStyleController extends Controller
{
    /**
     * Columnas permitidas para filtros.
     *
     * @var array
     */
    protected $allowedFilters = [
        'name',
        'description',
        'active',
    ];

    public function index(Request $request)
    {
        $records = TattooStyle::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $records->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $records->where('active', $request->input('status'));
        }

        $styles = $records->latest('id')->paginate(15);

        return view('tattoo::styles.index', compact('styles'));
    }

    public function columns()
    {
        return [
            'name' => 'Nombre',
            'description' => 'Descripción',
            'active' => 'Estado',
        ];
    }

    public function records(Request $request)
    {
        $column = $request->input('column', 'name');
        $value = trim((string) $request->input('value', ''));

        $records = TattooStyle::query();

        if (in_array($column, $this->allowedFilters) && $value !== '') {
            $records->where($column, 'like', "%{$value}%");
        }

        if ($request->filled('active')) {
            $records->where('active', $request->input('active'));
        }

        return new TattooStyleCollection(
            $records->latest('id')->paginate(config('tenant.items_per_page'))
        );
    }

    public function record($id)
    {
        return TattooStyle::findOrFail($id);
    }

    public function store(TattooStyleRequest $request)
    {
        DB::beginTransaction();

        try {
            $id = (int) $request->input('id');
            $name = trim((string) $request->input('name'));

            $exists = TattooStyle::where('name', $name);
            if ($id > 0) {
                $exists->where('id', '!=', $id);
            }

            if ($exists->exists()) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'El nombre del estilo ya existe',
                ], 422);
            }

            $record = TattooStyle::firstOrNew(['id' => $id]);
            $record->fill([
                'name' => $name,
                'description' => $request->input('description'),
                'active' => $request->has('active') ? (bool) $request->input('active') : false,
            ]);
            $record->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $id ? 'Estilo editado con éxito' : 'Estilo registrado con éxito',
                'data' => $record,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar el estilo',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $record = TattooStyle::findOrFail($id);
            $record->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estilo eliminado con éxito',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el estilo',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }
}