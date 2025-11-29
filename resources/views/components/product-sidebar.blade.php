<div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
        <span>Product Categories</span>
            <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm sidebar-action" id="sidebar-back-btn" style="display: none;">
                Back
            </a>
    </div>
    @if($showCategories ?? false)
        <ul class="list-group list-group-flush">
            @foreach($categories as $cat)
                <a href="{{ route('products.index', ['category_id' => $cat->id]) }}"
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                   data-category-id="{{ $cat->id }}"
                   data-category-name="{{ $cat->name }}">
                    {{ $cat->name }}
                    <span class="badge bg-primary rounded-pill">{{ $cat->products_count }}</span>
                </a>
            @endforeach
        </ul>
    @else
        <div class="card-body">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100 mb-2 sidebar-action">
                <i class="bi bi-list-ul"></i> All Products
            </a>
            <a href="{{ route('products.index', ['show_categories' => true]) }}" class="btn btn-outline-primary w-100 sidebar-action">
                <i class="bi bi-tags"></i> Category
            </a>
        </div>
    @endif
</div>
