<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ isset($location) ? 'Modifier' : 'Ajouter' }} une location
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white light:bg-gray-800 shadow sm:rounded-lg p-6">

                <form method="POST"
                    action="{{ isset($location) ? route('location.update', $location->id) : route('location.store') }}">
                    @csrf

                    @if(isset($location))
                        @method('PUT')
                    @endif

                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                    <div class="mb-4">
                        <label class="block mb-1 text-gray-700 light:text-gray-300">Film</label>
                        <select name="film_id"
                            class="w-full border p-2 rounded @error('film_id') border-red-500 @enderror">
                            <option value="">Sélectionnez un film</option>
                            @foreach($films as $film)
                                <option value="{{ $film->id }}" {{ (old('film_id', $location->film_id ?? '') == $film->id) ? 'selected' : '' }}>
                                    {{ $film->titre }}
                                </option>
                            @endforeach
                        </select>
                        @error('film_id')
                            <div class="text-red-500 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 text-black-700 dark:text-black-300">Nom du lieu</label>
                        <input type="text" name="name" value="{{ $location->name ?? old('name') }}"
                            class="w-full border p-2 rounded @error('name') border-red-500 @enderror">
                        @error('name')
                            <div class="text-red-500 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 text-black-700 dark:text-black-300">Ville</label>
                        <input type="text" name="city" value="{{ $location->city ?? old('city') }}"
                            class="w-full border p-2 rounded @error('city') border-red-500 @enderror">
                        @error('city')
                            <div class="text-red-500 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 text-black-700 dark:text-black-300">Pays</label>
                        <input type="text" name="country" value="{{ $location->country ?? old('country') }}"
                            class="w-full border p-2 rounded @error('country') border-red-500 @enderror">
                        @error('country')
                            <div class="text-red-500 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 text-black-700 dark:text-black-300">Description</label>
                        <textarea name="description"
                            class="w-full border p-2 rounded @error('description') border-red-500 @enderror">{{ $location->description ?? old('description') }}</textarea>
                        @error('description')
                            <div class="text-red-500 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 text-black-700 dark:text-black-300">Votes</label>
                        <input type="number" name="upvotes_count"
                            value="{{ $location->upvotes_count ?? old('upvotes_count', 0) }}"
                            class="w-full border p-2 rounded @error('upvotes_count') border-red-500 @enderror">
                        @error('upvotes_count')
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