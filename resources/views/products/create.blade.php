<x-app-layout>
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                {{-- Header Section --}}
                <div class="mb-4">
                    <h1 class="h3 fw-bold text-dark mb-1">Add New Product</h1>
                    <p class="text-muted">Fill in the details below to add a new item to your catalog.</p>
                </div>

                <div class="card shadow-lg border-0 rounded-3">
                    {{-- Decorative Top Border --}}
                    <div class="card-header bg-primary text-white py-3 rounded-top-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-box-open me-2"></i>Product Details</h6>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('products.store') }}" method="POST">
                            @csrf

                            {{-- 1. ROW: Name & Category --}}
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-bold text-secondary small text-uppercase">Product Name</label>
                                    <input type="text"
                                        name="name"
                                        id="name"
                                        class="form-control form-control-lg bg-light border-0 @error('name') is-invalid @enderror"
                                        placeholder="e.g. Safeguard Soap"
                                        value="{{ str(old('name'))->squish() }}"
                                        autofocus>
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
                                            placeholder="Select or Type..."
                                            value="{{ old('category') }}"
                                            onfocus="this.value=''">
                                    </div>
                                    <datalist id="categories">
                                        @foreach($categories as $category)
                                            <option value="{{ $category->name }}">
                                        @endforeach
                                    </datalist>
                                    @error('category')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- 2. ROW: Price & Weight --}}
                            <div class="row g-4 mb-5">
                                <div class="col-md-4">
                                    <label for="price" class="form-label fw-bold text-secondary small text-uppercase">Price</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-0 text-muted">₱</span>
                                        <input type="number"
                                            step="0.01"
                                            name="price" id="price"
                                            class="form-control bg-light border-0 @error('price') is-invalid @enderror"
                                            placeholder="0.00"
                                            value="{{ str(old('price'))->squish() }}">
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="net_weight" class="form-label fw-bold text-secondary small text-uppercase">Net Weight</label>
                                    <input type="number"
                                        step="any"
                                        name="net_weight"
                                        id="net_weight"
                                        class="form-control form-control-lg bg-light border-0 @error('net_weight') is-invalid @enderror"
                                        placeholder="0"
                                        value="{{ str(old('net_weight'))->squish() }}">
                                    @error('net_weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="net_weight_unit_id" class="form-label fw-bold text-secondary small text-uppercase">Unit</label>
                                    <select name="net_weight_unit_id"
                                        id="net_weight_unit_id"
                                        class="form-select form-select-lg bg-light border-0 @error('net_weight_unit_id') is-invalid @enderror">
                                        <option value="" disabled selected>Select...</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('net_weight_unit_id') == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('net_weight_unit_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                                <a href="{{ route('products.index') }}" class="btn btn-light btn-lg px-4 text-muted border">
                                    Cancel
                                </a>
                                <button type="submit" name="stay" class="btn btn-primary btn-lg px-5 shadow-sm">
                                    <i class="fas fa-save me-2"></i> Save Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SweetAlert Scripts (Keep exactly as they were) --}}
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: "{{ session('error') }}",
            });
        });
    </script>
    @endif

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('success') }}",
                timer: 2000,
                position: 'top-end',
                showConfirmButton: false,
                toast: true
            });
        });
    </script>
    @endif
</x-app-layout>