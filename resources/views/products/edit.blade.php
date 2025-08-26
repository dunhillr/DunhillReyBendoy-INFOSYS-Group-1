<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Product
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('products.update', $product->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-gray-700">Name</label>
                        <input type="text" name="name" value="{{ $product->name }}" class="border px-2 py-1 w-full">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Description</label>
                        <textarea name="description" class="border px-2 py-1 w-full">{{ $product->description }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Quantity</label>
                        <input type="number" name="quantity" value="{{ $product->quantity }}" class="border px-2 py-1 w-full">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Price</label>
                        <input type="text" name="price" value="{{ $product->price }}" class="border px-2 py-1 w-full">
                    </div>

                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
