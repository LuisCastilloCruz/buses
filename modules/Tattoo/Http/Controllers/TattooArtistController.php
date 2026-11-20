<?php

namespace Modules\Tattoo\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenant\User;
use Modules\Tattoo\Models\TattooArtist;
use Modules\Tattoo\Http\Requests\TattooArtistRequest;
use Modules\Tattoo\Http\Resources\TattooArtistCollection;

class TattooArtistController extends Controller
{
    protected $allowedFilters = [
        'stage_name',
        'specialty',
        'instagram',
        'active',
    ];

    public function index(Request $request)
    {
        $artists = TattooArtist::query()
            ->with(['user', 'portfolios'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->input('search'));
                $q->where('stage_name', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('active', (int) $request->input('status'));
            })
            ->latest('id')
            ->paginate(15);

        $users = User::query()
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('tattoo::artists.index', compact('artists', 'users'));
    }

    public function columns()
    {
        return [
            'stage_name' => 'Nombre artístico',
            'specialty' => 'Especialidad',
            'instagram' => 'Instagram',
            'active' => 'Estado',
        ];
    }

    public function records(Request $request)
    {
        $column = $request->input('column', 'stage_name');
        $value = trim((string) $request->input('value', ''));

        $records = TattooArtist::query()->with(['user', 'portfolios']);

        if (in_array($column, $this->allowedFilters) && $value !== '') {
            $records->where($column, 'like', "%{$value}%");
        }

        return new TattooArtistCollection(
            $records->latest('id')->paginate(config('tenant.items_per_page'))
        );
    }

    public function record($id)
    {
        return TattooArtist::with(['user', 'portfolios'])->findOrFail($id);
    }

    public function store(TattooArtistRequest $request)
    {
        DB::beginTransaction();

        try {
            $id = (int) $request->input('id');
            $stageName = trim((string) $request->input('stage_name'));

            $exists = TattooArtist::where('stage_name', $stageName);
            if ($id > 0) {
                $exists->where('id', '!=', $id);
            }

            if ($exists->exists()) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'El nombre artístico ya existe',
                ], 422);
            }

            $record = TattooArtist::firstOrNew(['id' => $id]);

            $record->fill([
                'user_id' => $request->input('user_id') ?: null,
                'stage_name' => $stageName,
                'specialty' => $request->input('specialty'),
                'bio' => $request->input('bio'),
                'instagram' => $request->input('instagram'),
                'active' => $request->has('active') ? (bool) $request->input('active') : false,
            ]);

            if ($request->hasFile('photo')) {
                if ($record->photo_path && Storage::disk('public')->exists($record->photo_path)) {
                    Storage::disk('public')->delete($record->photo_path);
                }

                $record->photo_path = $request->file('photo')->store('tattoo/artists', 'public');
            }

            $record->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $id ? 'Tatuador editado con éxito' : 'Tatuador registrado con éxito',
                'data' => $record->load(['user', 'portfolios']),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar el tatuador',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $record = TattooArtist::with('portfolios')->findOrFail($id);

            foreach ($record->portfolios as $portfolio) {
                if ($portfolio->image_path && Storage::disk('public')->exists($portfolio->image_path)) {
                    Storage::disk('public')->delete($portfolio->image_path);
                }
            }

            if ($record->photo_path && Storage::disk('public')->exists($record->photo_path)) {
                Storage::disk('public')->delete($record->photo_path);
            }

            $record->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tatuador eliminado con éxito',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el tatuador',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }
}