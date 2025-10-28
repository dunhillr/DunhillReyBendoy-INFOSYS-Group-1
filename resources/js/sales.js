import $ from 'jquery';

$(function() {
    let productCounter = 0; // The counter can still be used for unique IDs

    function addProductField() {
        productCounter++;
        const productHtml = `
            <div class="card mb-3" data-product-id="${productCounter}">
                <div class="card-body">
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn-close remove-product" aria-label="Close"></button>
                    </div>
                    <div class="mb-3">
                        <label for="product_name_${productCounter}" class="form-label">Product Name</label>
                        <input type="text" id="product_name_${productCounter}" class="form-control product-autocomplete" placeholder="Start typing product name...">
                        <!-- Use products[][id] instead of a counter to let the server handle array indexing -->
                        <input type="hidden" name="products[][id]" id="product_id_${productCounter}">
                    </div>
                    <div class="mb-3">
                        <label for="quantity_${productCounter}" class="form-label">Quantity</label>
                        <!-- Use products[][quantity] -->
                        <input type="number" name="products[][quantity]" id="quantity_${productCounter}" class="form-control" min="1">
                    </div>
                </div>
            </div>
        `;
        $('#product-list').append(productHtml);

        // Initialize autocomplete on the newly added field
        $(`#product_name_${productCounter}`).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "/products/search",
                    dataType: "json",
                    data: {
                        query: request.term
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.label,
                                value: item.value,
                                id: item.id
                            };
                        }));
                    }
                });
            },
            select: function(event, ui) {
                // Find the closest card and set its hidden input
                $(this).closest('.card').find('input[name="products[][id]"]').val(ui.item.id);
            }
        });
    }

    addProductField(); // Add one field by default

    $('#add-product').on('click', function() {
        addProductField();
    });

    $('#product-list').on('click', '.remove-product', function() {
        $(this).closest('.card').remove();
    });
});
