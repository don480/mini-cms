<x-layout>
    <x-slot:title>Catégorie introuvable - Mini-CMS</x-slot:title>

    <h1 class="text-3xl font-bold text-gray-900">Catégorie introuvable</h1>
    <p class="mt-2 mb-8 text-gray-600">Cette catégorie n'existe pas ou a été supprimée.</p>

    @if ($categories->isNotEmpty())
        <h2 class="mb-4 text-xl font-bold">Catégories disponibles</h2>
        <ul class="max-w-2xl space-y-2">
            @foreach ($categories as $category)
                <li>
                    <a href="{{ route('posts.category', $category) }}" class="font-medium text-indigo-600 hover:underline">{{ $category->name }}</a>
                </li>
            @endforeach
        </ul>
    @endif
</x-layout>
