<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded p-4">
        <h1 class="text-2xl font-bold mb-4">Product List</h1>

        <a href="{{ route('products.create') }}"
          class="inline-block mb-4 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            + Add New Product
        </a>


        <table class="table-auto w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border border-gray-300 px-4 py-2">ID</th>
                    <th class="border border-gray-300 px-4 py-2">Name</th>
                    <th class="border border-gray-300 px-4 py-2">Description</th>
                    <th class="border border-gray-300 px-4 py-2">Quantity</th>
                    <th class="border border-gray-300 px-4 py-2">Price</th>
                    <th class="border border-gray-300 px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">{{ $product->id }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $product->name }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $product->description }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $product->quantity }}</td>
                        <td class="border border-gray-300 px-4 py-2">₱{{ $product->price }}</td>
                        <td class="border border-gray-300 px-4 py-2 flex gap-2">

                        <!-- Edit button -->
                        <a href="{{ route('products.edit', $product->id) }}" class="bg-blue-500 text-white px-3 py-1 rounded">Edit</a>

                        <!-- Delete button -->
                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
                        </form>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
              class="mb-4 p-4 bg-green-200 text-green-800 rounded">
              {{ session('success') }}
            </div>
        @endif

    </div>
</body>
</html>

