<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = Location::with(['film', 'user'])->get();
        return view('location.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $films = Film::all();
        return view('location.create', compact('films'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'film_id' => 'required|integer',
            'user_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'description' => 'nullable|string',
            'upvotes_count' => 'required|integer',
        ]);

        Location::create([
            'film_id' => $request->film_id,
            'user_id' => $request->user_id,
            'name' => $request->name,
            'city' => $request->city,
            'country' => $request->country,
            'description' => $request->description,
            'upvotes_count' => $request->upvotes_count,
        ]);

        return redirect()->route('location.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $location = Location::findOrFail($id);
        $films = Film::all();
        return view('location.create', compact('location', 'films'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'film_id' => 'required|integer',
            'user_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'description' => 'nullable|string',
            'upvotes_count' => 'required|integer',
        ]);

        $location = Location::findOrFail($id);
        $location->update($request->all());

        return redirect()->route('location.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return redirect()->route('location.index');
    }

    public function upvote(string $id)
    {
        $userId = Auth::id();
        $locationId = $id;

        // Vérifier si l'utilisateur a déjà voté
        $alreadyVoted = DB::table('location_votes')
            ->where('user_id', $userId)
            ->where('location_id', $locationId)
            ->exists();

        if (!$alreadyVoted) {
            // Insérer le vote
            DB::table('location_votes')->insert([
                'user_id' => $userId,
                'location_id' => $locationId,
                'created_at' => now(),
            ]);

            // Optionnel : Incrémenter le compteur de cache dans la table locations
            Location::where('id', $locationId)->increment('upvotes_count');
        }

        return redirect()->back();
    }
}