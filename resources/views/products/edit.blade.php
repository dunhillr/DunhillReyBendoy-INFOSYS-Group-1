<x-app-layout>
    <div class="container py-4">
        
        {{-- 1. HEADER SECTION --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Edit Product</h1>
                <p class="text-muted mb-0">Update product details, pricing, and category.</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Catalog
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                {{-- 2. EDIT FORM CARD --}}
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-edit me-2"></i>Product Information
                        </h6>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('products.update', $product->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            {{-- Row 1: Name & Category --}}
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-bold text-secondary small text-uppercase">Product Name</label>
                                    <input type="text"
                                        name="name"
                                        id="name"
                                        class="form-control form-control-lg bg-light border-0 @error('name') is-invalid @enderror"
                                        value="{{ old('name', $product->name) }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="category" class="form-label fw-bold text-secondary small text-uppercase">Category</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-0 ps-0"><i class="fas fa-tag text-muted"></i></span>
                                        <input list="categories"
                                            name="category"
                                            id="category"
                                            class="form-control form-control-lg bg-light border-0 @error('category') is-invalid @enderror"
                                            value="{{ old('category', $product->category->name) }}"
                                            placeholder="Select or Type..."
                                            >
                                    </div>
                                    
                                    {{-- Datalist & Hidden ID logic remains the same --}}
                                    <datalist id="categories">
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->name }}" data-category-id="{{ $cat->id }}">
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="category_id" id="category_id" value="{{ old('category_id', $product->category_id) }}">
                                    
                                    @error('category')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Row 2: Price, Weight, Unit --}}
                            <div class="row g-4 mb-5">
                                <div class="col-md-4">
                                    <label for="price" class="form-label fw-bold text-secondary small text-uppercase">Price</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-0 text-muted">₱</span>
                                        <input type="number"
                                            step="0.01"
                                            name="price" id="price"
                                            class="form-control bg-light border-0 @error('price') is-invalid @enderror"
                                            value="{{ old('price', $product->price) }}">
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="net_weight" class="form-label fw-bold text-secondary small text-uppercase">Net Weight</label>
                                    <input type="number"
                                        step="any"
                                        name="net_weight" id="net_weight"
                                        class="form-control form-control-lg bg-light border-0 @error('net_weight') is-invalid @enderror"
                                        value="{{ old('net_weight', $product->net_weight) }}">
                                    @error('net_weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="net_weight_unit_id" class="form-label fw-bold text-secondary small text-uppercase">Unit</label>
                                    <select name="net_weight_unit_id"
                                        id="net_weight_unit_id"
                                        class="form-select form-select-lg bg-light border-0 @error('net_weight_unit_id') is-invalid @enderror">
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

                            {{-- 3. ACTION BUTTONS --}}
                            <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                                {{-- Option 1: Save & Stay --}}
                                <button type="submit" name="stay" value="1" class="btn btn-white border shadow-sm px-4">
                                    Save changes
                                </button>
                                
                                {{-- Option 2: Save & Return (Primary Action) --}}
                                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                    <i class="fas fa-save me-2"></i> Save & Return
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
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
</x-app-layout>