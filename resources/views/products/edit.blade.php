<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Product
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm p-4">
                    <form action="{{ route('products.update', $product->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- ✅ Product Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ✅ Net Weight and Unit -->
                        <div class="row g-3 mb-3">
                            <div class="col-8">
                                <label for="net_weight" class="form-label">Net Weight</label>
                                <input type="number" step="any" name="net_weight" id="net_weight" value="{{ old('net_weight', $product->net_weight) }}" class="form-control @error('net_weight') is-invalid @enderror">
                                @error('net_weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label for="net_weight_unit_id" class="form-label">Unit</label>
                                <select name="net_weight_unit_id" id="net_weight_unit_id" class="form-select @error('net_weight_unit_id') is-invalid @enderror">
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('net_weight_unit_id', $product->net_weight_unit_id) == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('net_weight_unit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- ✅ Price -->
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="text" name="price" id="price" value="{{ old('price', $product->price) }}" class="form-control @error('price') is-invalid @enderror">
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ✅ Category Input with Datalist -->
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input list="categories"
                                    name="category"
                                    id="category"
                                    class="form-control @error('category') is-invalid @enderror"
                                    value="{{ old('category', $product->category->name) }}"
                                    onfocus="this.value=''"
                                >
                                <datalist id="categories">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->name }}" data-category-id="{{ $cat->id }}">
                                    @endforeach
                                </datalist>
                                <input type="hidden" name="category_id" id="category_id" value="{{ old('category_id', $product->category_id) }}">
                            
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @push('scripts')
                            <script>
                                // Script to sync the hidden category_id input with the datalist selection
                                document.addEventListener('DOMContentLoaded', function() {
                                    const categoryInput = document.getElementById('category');
                                    const categoryDatalist = document.getElementById('categories');
                                    const hiddenCategoryId = document.getElementById('category_id');
                                
                                    categoryInput.addEventListener('input', function() {
                                        const selectedOption = categoryDatalist.querySelector(`option[value="${this.value}"]`);
                                        if (selectedOption) {
                                            hiddenCategoryId.value = selectedOption.dataset.categoryId;
                                        } else {
                                            hiddenCategoryId.value = '';
                                        }
                                    });
                                });
                            </script>
                            @endpush

                            <div>
                                <!-- Submit button that stays on the current page -->
                                <button type="submit" name="stay" value="1" class="btn btn-primary">
                                    Save
                                </button>
                                
                                <!-- Submit button that returns to the product list -->
                                <button type="submit" class="btn btn-success">
                                    Save & Return
                                </button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
