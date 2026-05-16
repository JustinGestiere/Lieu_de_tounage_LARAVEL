<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 light:text-gray-200">
            {{ isset($film) ? 'Modifier' : 'Ajouter' }} un film
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white light:bg-gray-800 shadow sm:rounded-lg p-6">

                <form method="POST" action="{{ isset($film) ? '/film/' . $film->id : '/film' }}">
                    @csrf

                    @if(isset($film))
                        @method('PUT')
                    @endif

                    <div class="mb-4">
                        <label class="block mb-1">Titre</label>
                        <input type="text" name="titre" value="{{ $film->titre ?? old('titre') }}"
                            class="w-full border p-2 rounded @error('titre') border-red-500 @enderror">
                        @error('titre')
                            <div class="text-red-500 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1">Année</label>
                        <input type="number" name="annee" value="{{ $film->annee ?? old('annee') }}"
                            class="w-full border p-2 rounded @error('annee') border-red-500 @enderror">
                        @error('annee')
                            <div class="text-red-500 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1">Synopsis</label>
                        <input type="text" name="synopsis" value="{{ $film->synopsis ?? old('synopsis') }}"
                            class="w-full border p-2 rounded @error('synopsis') border-red-500 @enderror">
                        @error('synopsis')
                            <div class="text-red-500 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        Enregistrer
                    </button>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>