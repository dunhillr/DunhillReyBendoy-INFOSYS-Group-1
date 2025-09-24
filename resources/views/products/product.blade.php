<x-app-layout>

    <div class="container py-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">All Products</h1>
            <a href="{{ route('products.create') }}" class="btn btn-success">
                + Add Product
            </a>
        </div>

      <div class="row g-3">
        @foreach ($products as $product)
          <div class="col-6 col-md-3"> <!-- 4 boxes per row -->
            <div class="card h-100 shadow-sm text-center"
                  data-bs-toggle="modal"
                  data-bs-target="#productModal{{ $product->id }}">
              
              <!-- Center product image -->
<!--              <div class="d-flex justify-content-center mt-3">
                <img src="{{ $product->image ?? 'https://via.placeholder.com/120' }}"
                      class="card-img-top"
                      alt="{{ $product->name }}"
                      style="width:120px; height:120px; object-fit:cover;">
              </div>
-->
              <div class="card-body p-2">
                <h6 class="card-title mb-1">{{ $product->name }}</h6>
                <p class="text-muted small mb-0">${{ $product->price }}</p>

<!--                <p class="text-muted small mb-0">Quantity Sold: {{ $product->quantity }}</p>
-->
                <p class="text-muted small mb-0">{{ $product->description }}</p>
              </div>
            </div>
          </div>

          <!-- Modal -->
          <div class="modal fade" id="productModal{{ $product->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $product->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                  <!-- Product Image -->
<!--                  <img src="{{ $product->image ?? 'https://via.placeholder.com/200' }}"
                        class="img-fluid mb-3"
                        style="max-height:200px; object-fit:cover;">
-->
                  <!-- Description -->
                  <p>{{ $product->description }}</p>

                  <!-- Form to update quantity sold -->
<!--                  <form action="{{ route('products.update', $product->id) }}" method="POST" class="mb-3">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                      <label for="quantity{{ $product->id }}" class="form-label">Quantity Sold</label>
                      <input type="number" name="quantity" id="quantity{{ $product->id }}" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save</button>
                  </form>
-->
                  <!-- Edit / Delete Buttons -->
                  <div class="d-flex justify-content-between">
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning">Edit</a>

                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                  </div>

                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
</x-app-layout>