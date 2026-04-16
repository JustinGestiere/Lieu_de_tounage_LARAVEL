<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 light:text-gray-200 leading-tight">
            Films
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white light:bg-gray-800 shadow sm:rounded-lg p-6">

                <!-- Bouton ajouter -->
                <div class="flex justify-end mb-4">
                    <a href="{{ route('film.create') }}"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        + Ajouter
                    </a>
                </div>

                <!-- Table -->
                <table class="w-full border border-gray-200 text-sm">
                    <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="p-2 border">ID</th>
                            <th class="p-2 border">Titre</th>
                            <th class="p-2 border">Année</th>
                            <th class="p-2 border">Synopsis</th>
                            @auth
                                @if(auth()->user()->is_admin)
                                    <th class="p-2 border">Actions</th>
                                @endif
                            @endauth
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($films as $film)
                            <tr class="text-center">
                                <td class="p-2 border">{{ $film->id }}</td>
                                <td class="p-2 border">{{ $film->titre }}</td>
                                <td class="p-2 border">{{ $film->annee }}</td>
                                <td class="p-2 border">{{ $film->synopsis }}</td>
                                @auth
                                    @if(auth()->user()->is_admin)
                                        <td class="p-2 border flex justify-center gap-2">

                                            <!-- Modifier -->
                                            <a href="{{ route('film.edit', $film->id) }}"
                                                class="bg-yellow-400 px-3 py-1 rounded hover:bg-yellow-500">
                                                Modifier
                                            </a>

                                            <!-- Supprimer -->
                                            <form action="{{ route('film.destroy', $film->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                                    Supprimer
                                                </button>
                                            </form>

                                        </td>
                                    @endif
                                @endauth
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>

        </div>
    </div>
</x-app-layout>