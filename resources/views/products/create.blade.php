<x-app-layout>
    <div class="container py-5">
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

                            <!-- Net Weight and Unit -->
                            <div class="row g-3 mb-3">
                                <div class="col-8">
                                    <label for="net_weight" class="form-label">Net Weight</label>
                                    <input type="number"
                                        step="any"
                                        name="net_weight"
                                        id="net_weight"
                                        class="form-control @error('net_weight') is-invalid @enderror"
                                        value="{{ str(old('net_weight'))->squish() }}">
                                    @error('net_weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-4">
                                    <label for="net_weight_unit_id" class="form-label">Unit</label>
                                    <select name="net_weight_unit_id"
                                        id="net_weight_unit_id"
                                        class="form-select @error('net_weight_unit_id') is-invalid @enderror">
                                        <option value="">Select...</option>
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
</x-app-layout>
