<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateUpvotesCountJob;
use App\Models\Film;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'upvotes_count' => 'nullable|integer',
        ]);

        Location::create([
            'film_id' => $request->film_id,
            'user_id' => $request->user_id,
            'name' => $request->name,
            'city' => $request->city,
            'country' => $request->country,
            'description' => $request->description,
            'upvotes_count' => 0,
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

        // Vérification des permissions : Admin OU Auteur
        if (! Auth::user()->is_admin && $location->user_id !== Auth::id()) {
            abort(403, "Vous n'êtes pas l'auteur de cette location.");
        }

        $films = Film::all();

        return view('location.create', compact('location', 'films'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $location = Location::findOrFail($id);

        if (! Auth::user()->is_admin && $location->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'film_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'description' => 'nullable|string',
            'upvotes_count' => 'nullable|integer',
        ]);

        $location->update($request->only([
            'film_id',
            'name',
            'city',
            'country',
            'description',
            'upvotes_count',
        ]));

        return redirect()->route('location.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $location = Location::findOrFail($id);

        // Vérification des permissions : Admin OU Auteur
        if (! Auth::user()->is_admin && $location->user_id !== Auth::id()) {
            abort(403);
        }

        $location->delete();

        return redirect()->route('location.index');
    }

    public function upvote(string $id)
    {
        $userId = Auth::id();
        $locationId = $id;

        Log::info("Tentative de vote par l'utilisateur $userId pour la location $locationId");

        // Vérifier si l'utilisateur a déjà voté
        $alreadyVoted = DB::table('location_votes')
            ->where('user_id', $userId)
            ->where('location_id', $locationId)
            ->exists();

        if (! $alreadyVoted) {
            Log::info('Vote autorisé. Enregistrement en base...');
            // Insérer le vote
            DB::table('location_votes')->insert([
                'user_id' => $userId,
                'location_id' => $locationId,
                'created_at' => now(),
            ]);

            // 🔥 dispatch du job
            Log::info("Dispatch du job UpdateUpvotesCountJob pour la location $locationId");
            UpdateUpvotesCountJob::dispatch((int) $id);
        } else {
            Log::info("Vote refusé : l'utilisateur a déjà voté pour ce lieu.");
        }

        return redirect()->back();
    }
}
