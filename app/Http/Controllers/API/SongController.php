<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class SongController extends Controller
{
    public function index()
    {
        try {
            Log::info('Fetching all songs');
            $songs = Song::all();
            return response()->json($songs, 200);
        } catch (Exception $e) {
            Log::error('Error fetching songs: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching songs'], 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('Starting creation of new song', $request->all());

        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'artist' => 'required|string|max:255',
                'album' => 'required|string|max:255',
                'duration' => 'required|integer',
                'cover_art' => 'nullable|image|max:2048',
            ]);

            Log::info('Data validation successful');

            $song = new Song($validatedData);

            if ($request->hasFile('cover_art')) {
                Log::info('Processing cover art image');
                $path = $request->file('cover_art')->store('covers', 'public');
                $song->cover_art_url = Storage::url($path);
                Log::info('Cover art image saved', ['path' => $song->cover_art_url]);
            }

            $song->save();
            Log::info('Song saved successfully', ['song_id' => $song->id]);

            return response()->json($song, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation error', 'details' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error creating song', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error creating song', 'details' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            Log::info('Fetching song', ['id' => $id]);
            $song = Song::findOrFail($id);
            return response()->json($song);
        } catch (Exception $e) {
            Log::error('Error fetching song: ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['error' => 'Song not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Starting update of song', ['id' => $id, 'data' => $request->all()]);

        try {
            $song = Song::findOrFail($id);

            $validatedData = $request->validate([
                'title' => 'sometimes|string|max:255',
                'artist' => 'sometimes|string|max:255',
                'album' => 'sometimes|string|max:255',
                'duration' => 'sometimes|integer',
                'cover_art' => 'nullable|image|max:2048',
            ]);

            Log::info('Data validation successful');

            $song->fill($validatedData);

            if ($request->hasFile('cover_art')) {
                Log::info('Processing new cover art image');
                if ($song->cover_art_url) {
                    Storage::delete(str_replace('/storage/', 'public/', $song->cover_art_url));
                }
                $path = $request->file('cover_art')->store('covers', 'public');
                $song->cover_art_url = Storage::url($path);
                Log::info('New cover art image saved', ['path' => $song->cover_art_url]);
            }

            $song->save();
            Log::info('Song updated successfully', ['song_id' => $song->id]);

            return response()->json($song);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Song not found', ['id' => $id]);
            return response()->json(['error' => 'Song not found'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation error', 'details' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error updating song', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error updating song', 'details' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        Log::info('Starting deletion of song', ['id' => $id]);

        try {
            $song = Song::findOrFail($id);

            if ($song->cover_art_url) {
                Storage::delete(str_replace('/storage/', 'public/', $song->cover_art_url));
                Log::info('Cover art deleted', ['path' => $song->cover_art_url]);
            }

            $song->delete();
            Log::info('Song deleted successfully', ['song_id' => $id]);

            return response()->json(null, 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Song not found for deletion', ['id' => $id]);
            return response()->json(['error' => 'Song not found'], 404);
        } catch (Exception $e) {
            Log::error('Error deleting song', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error deleting song', 'details' => $e->getMessage()], 500);
        }
    }
}

