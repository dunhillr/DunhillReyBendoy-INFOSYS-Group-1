<!DOCTYPE html>
<html>
<head>
    <title>Create Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="card-title text-center mb-4">Add Product</h1>

                        <form action="{{ route('products.store') }}" method="POST">
                            @csrf

                            <!-- Product Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text"
                                    name="name"
                                    id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ str(old('name'))->squish() }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description"
                                    id="description"
                                    class="form-control @error('description') is-invalid @enderror">
                                </textarea>

                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>

                            <!-- Quantity -->
<!--                           <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number"
                                    name="quantity"
                                    id="quantity"
                                    class="form-control @error('quantity') is-invalid @enderror">

                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                            </div>
-->
                            <!-- Price -->
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number"
                                    step="0.01"
                                    name="price" id="price"
                                    class="form-control @error('price') is-invalid @enderror"
                                    placeholder="e.g., 19.99"
                                    value="{{ str(old('price'))->squish() }}">

                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>

                            <!-- ✅ Category Dropdown -->
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input list="categories"
                                    name="category"
                                    id="category"
                                    class="form-control @error('category') is-invalid @enderror"
                                    value="{{ old('category') }}"
                                    onfocus="this.value=''">
                                
                                <datalist id="categories">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->name }}">
                                    @endforeach
                                </datalist>

                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                            </div>


                            <div class="d-flex justify-content-between">
                                <a href="{{ route('products.index') }}" onclick="window.history.back(); return false;" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
