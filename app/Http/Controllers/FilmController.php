<?php

namespace App\Http\Controllers;

use App\Models\Film;
use Illuminate\Http\Request;

class FilmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $films = Film::all();
        return view('film.index', compact('films'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('film.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'annee' => 'required|integer',
            'synopsis' => 'nullable|string',
        ]);

        Film::create([
            'titre' => $request->titre,
            'annee' => $request->annee,
            'synopsis' => $request->synopsis,
        ]);

        return redirect()->route('film.index');
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
        $film = Film::findOrFail($id);
        return view('film.create', compact('film'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'annee' => 'required|integer',
            'synopsis' => 'nullable|string',
        ]);

        $film = Film::findOrFail($id);
        $film->update($request->all());

        return redirect()->route('film.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $film = Film::findOrFail($id);
        $film->delete();

        return redirect()->route('film.index');
    }
}
