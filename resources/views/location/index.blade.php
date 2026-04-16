<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 light:text-gray-200 leading-tight">
            Locations
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white light:bg-gray-800 shadow sm:rounded-lg p-6">

                <!-- Bouton ajouter -->
                <div class="flex justify-end mb-4">
                    <a href="{{ route('location.create') }}"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        + Ajouter une location
                    </a>
                </div>

                <!-- Table -->
                <table class="w-full border border-gray-200 text-sm">
                    <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="p-2 border">ID</th>
                            <th class="p-2 border">Film</th>
                            <th class="p-2 border">Nom du lieu</th>
                            <th class="p-2 border">Ville</th>
                            <th class="p-2 border">Pays</th>
                            <th class="p-2 border">Proposé par</th>
                            <th class="p-2 border">Votes</th>
                            <th class="p-2 border">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="text-black-900 dark:text-black-100">
                        @foreach($locations as $location)
                            <tr class="text-center">
                                <td class="p-2 border">{{ $location->id }}</td>
                                <td class="p-2 border">{{ $location->film->titre ?? 'N/A' }}</td>
                                <td class="p-2 border">{{ $location->name }}</td>
                                <td class="p-2 border">{{ $location->city }}</td>
                                <td class="p-2 border">{{ $location->country }}</td>
                                <td class="p-2 border">{{ $location->user->name ?? 'N/A' }}</td>
                                <td class="p-2 border">{{ $location->upvotes_count }}</td>
                                <td class="p-2 border flex justify-center gap-2">

                                    <!-- Modifier -->
                                    @auth
                                        @if(auth()->user()->is_admin)
                                            <a href="{{ route('location.edit', $location->id) }}"
                                                class="bg-yellow-400 text-black px-3 py-1 rounded hover:bg-yellow-500">
                                                Modifier
                                            </a>
                                        @endif
                                    @endauth

                                    <!-- Supprimer -->
                                    @auth
                                        @if(auth()->user()->is_admin)
                                            <form action="{{ route('location.destroy', $location->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600"
                                                    onclick="return confirm('Êtes-vous sûr ?')">
                                                    Supprimer
                                                </button>
                                            </form>
                                        @endif
                                    @endauth

                                    <!-- Upvote -->

                                    <form action="{{ route('location.upvote', $location->id) }}" method="POST">
                                        @csrf
                                        <button class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                            Upvote
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>

        </div>
    </div>
</x-app-layout>