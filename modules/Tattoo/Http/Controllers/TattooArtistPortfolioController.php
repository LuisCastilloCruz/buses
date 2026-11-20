<?php

namespace Modules\Tattoo\Http\Controllers;

use Exception;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Tattoo\Models\TattooArtist;
use Modules\Tattoo\Models\TattooArtistPortfolio;
use Modules\Tattoo\Http\Requests\TattooArtistPortfolioRequest;

class TattooArtistPortfolioController extends Controller
{
    public function store(TattooArtistPortfolioRequest $request)
    {
        DB::beginTransaction();

        try {
            $id = (int) $request->input('id');
            $artistId = (int) $request->input('tattoo_artist_id');

            $artist = TattooArtist::findOrFail($artistId);

            $record = TattooArtistPortfolio::firstOrNew(['id' => $id]);
            $record->fill([
                'tattoo_artist_id' => $artist->id,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'active' => $request->has('active') ? (bool) $request->input('active') : false,
                'sort_order' => $request->input('sort_order') ?: 0,
            ]);

            if ($request->hasFile('image')) {
                if ($record->image_path && Storage::disk('public')->exists($record->image_path)) {
                    Storage::disk('public')->delete($record->image_path);
                }

                $record->image_path = $request->file('image')->store('tattoo/portfolio', 'public');
            }

            if (!$record->image_path) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Debes subir una imagen del trabajo.',
                ], 422);
            }

            $record->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $id ? 'Trabajo editado con éxito' : 'Trabajo agregado con éxito',
                'data' => [
                    'id' => $record->id,
                    'tattoo_artist_id' => $record->tattoo_artist_id,
                    'title' => $record->title,
                    'description' => $record->description,
                    'image_path' => $record->image_path,
                    'image_url' => asset('storage/' . $record->image_path),
                    'active' => $record->active,
                    'sort_order' => $record->sort_order,
                ],
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar el trabajo del artista',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function record($id)
    {
        $record = TattooArtistPortfolio::findOrFail($id);

        return response()->json([
            'id' => $record->id,
            'tattoo_artist_id' => $record->tattoo_artist_id,
            'title' => $record->title,
            'description' => $record->description,
            'image_path' => $record->image_path,
            'image_url' => $record->image_path ? asset('storage/' . $record->image_path) : null,
            'active' => $record->active,
            'sort_order' => $record->sort_order,
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $record = TattooArtistPortfolio::findOrFail($id);

            if ($record->image_path && Storage::disk('public')->exists($record->image_path)) {
                Storage::disk('public')->delete($record->image_path);
            }

            $record->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Trabajo eliminado con éxito',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el trabajo',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }
}