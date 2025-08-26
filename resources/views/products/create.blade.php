<!DOCTYPE html>
<html>
<head>
    <title>Create Product</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-md mx-auto bg-white shadow-md rounded p-6">
        <h1 class="text-2xl font-bold mb-4">Add Product</h1>

        <form action="{{ route('products.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-semibold">Name</label>
                <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block font-semibold">Description</label>
                <textarea name="description" class="w-full border rounded px-3 py-2"></textarea>
            </div>

            <div>
                <label class="block font-semibold">Quantity</label>
                <input type="number" name="quantity" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block font-semibold">Price</label>
                <input type="number" step="0.01" name="price" class="w-full border rounded px-3 py-2" required>
            </div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Save
            </button>
        </form>
    </div>
</body>
</html>
