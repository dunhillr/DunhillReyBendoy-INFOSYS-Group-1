<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Record Sale
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm p-4">
                    <h5 class="mb-4">Add Products to Sale</h5>
                    <div class="mb-3">
                        <label for="product_search" class="form-label">Search Product</label>
                        <input type="text" id="product_search" class="form-control" placeholder="Type product name or ID" autocomplete="off">
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="sale-items-table">
                            <!-- Products will be added here via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm p-4">
                    <form action="{{ route('sales.store') }}" method="POST" id="sale-form">
                        @csrf
                        <h5 class="mb-4">Summary</h5>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <span id="total-amount">0.00</span>
                        </div>
                        <hr class="my-3">
                        <div class="mb-3">
                            <label for="payment_amount" class="form-label">Payment</label>
                            <input type="number" step="0.01" id="payment_amount" name="payment_amount" class="form-control">
                        </div>
                        <div class="d-flex justify-content-between">
                            <strong>Change:</strong>
                            <span id="change-amount">0.00</span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-4">Record Sale</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


@push('scripts')
<script>
    console.log("✅ Autocomplete script loaded");
    $(function() {
        let saleItems = [];
        const productSearch = $('#product_search');
        const saleItemsTable = $('#sale-items-table');
        const totalAmountSpan = $('#total-amount');
        const changeAmountSpan = $('#change-amount');
        const paymentAmountInput = $('#payment_amount');
        const saleForm = $('#sale-form');
        const recordBtn = saleForm.find('button[type="submit"]');

        // --- Autocomplete setup ---
        productSearch.autocomplete({
            source: function(request, response) {
                $.get("{{ route('products.search') }}", { query: request.term }, function(data) {
                    const formattedData = data.map(item => ({
                        label: item.label,
                        value: item.value,
                        id: item.id,
                        price: parseFloat(item.price),
                        net_weight: item.net_weight,
                        net_weight_unit_name: item.net_weight_unit_name
                    }));
                    response(formattedData);
                });
            },
            select: function(event, ui) {
                const product = ui.item;
                addProductToSale(product);
                productSearch.val('');
                productSearch.focus();
                return false;
            }
        });

        // --- addProductToSale function ---
        function addProductToSale(product) {
            let existingItem = saleItems.find(item => item.id === product.id);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                saleItems.push({
                    id: product.id,
                    name: product.label,
                    price: product.price,
                    net_weight: product.net_weight,
                    net_weight_unit_name: product.net_weight_unit_name,
                    quantity: 1 // Start with a quantity of 1
                });
            }
            renderSaleItems();
        }

        // --- renderSaleItems function ---
        function renderSaleItems() {
            const focusedInput = saleItemsTable.find('.quantity-input:focus');
            const focusedIndex = focusedInput.data('index');
            const focusedValue = focusedInput.val();

            saleItemsTable.empty();
            let total = 0;
            saleItems.forEach((item, index) => {
                // --- FIX START ---
                // Ensure price and quantity are valid numbers, default to 0 if not.
                const price = parseFloat(item.price) || 0;
                const quantity = parseInt(item.quantity) || 0;
                const subtotal = price * quantity;
                const netWeight = item.net_weight || '';
                const netWeightUnitName = item.net_weight_unit_name || '';
                // --- FIX END ---

                total += subtotal;
                const row = `<tr>
                    <td>${netWeight} ${netWeightUnitName} ${item.name}</td>
                    <td>${price.toFixed(2)}</td>
                    <td><input type="number" value="${quantity}" min="1" class="form-control form-control-sm quantity-input" data-index="${index}"></ td>
                    <td>${subtotal.toFixed(2)}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-item" data-index="${index}">Remove</button></td>
                </tr>`;
                saleItemsTable.append(row);
            });
            totalAmountSpan.text(total.toFixed(2));
            updateChange();
        
            if (focusedInput.length && focusedIndex !== undefined) {
                const newFocusedInput = saleItemsTable.find(`.quantity-input[data-index="${focusedIndex}"]`);
                if (newFocusedInput.length) {
                newFocusedInput.focus().val(focusedValue);
                }
            }

            saleForm.find('input[name^="sale_items"]').remove();
            saleItems.forEach(item => {
                saleForm.append(`<input type="hidden" name="sale_items[][id]" value="${item.id}">`);
                saleForm.append(`<input type="hidden" name="sale_items[][quantity]" value="${item.quantity}">`);
                });
            }

            // --- Remove item event handler ---
        saleItemsTable.on('click', '.remove-item', function() {
            const index = $(this).data('index');
                Swal.fire({
                title: 'Are you sure?',
                text: "This will remove the product from the sale.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    saleItems.splice(index, 1);
                    renderSaleItems();
                    Swal.fire('Removed!', 'Product has been removed.', 'success');
                }
            });
        });

        saleItemsTable.on('blur', '.quantity-input', function() {
            const $input = $(this);
            const index = $input.data('index');
            let newQuantity = parseInt($(this).val());

            // Prevent invalid or empty input
            if (!isNaN(newQuantity) && newQuantity < 1) {
                // If input is invalid, reset to the last valid quantity
                $input.val(saleItems[index].quantity);
                return;
            } else {
            // Only re-render if the quantity actually changed
                if (saleItems[index].quantity !== newQuantity) {
                    saleItems[index].quantity = newQuantity;
                    renderSaleItems();
                }
            }
        });

        paymentAmountInput.on('input', updateChange);

        function updateChange() {
            const total = parseFloat(totalAmountSpan.text());
            const payment = parseFloat(paymentAmountInput.val()) || 0;
            const change = payment - total;
            changeAmountSpan
                .text(change.toFixed(2))
                .removeClass('text-success text-danger animate__animated animate__flash');

            if (payment === 0) return;

            if (change >= 0) {
                changeAmountSpan.addClass('text-success animate__animated animate__flash');
            } else {
                changeAmountSpan.addClass('text-danger animate__animated animate__flash');
            }
        }

        saleForm.on('submit', function(e) {
            const total = parseFloat(totalAmountSpan.text());
            const paymentAmount = paymentAmountInput.val();

            if (saleItems.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'No Products Added',
                    text: 'Please add at least one product to the sale.'
                });
            return;
        }

        if (paymentAmount === '' || paymentAmount === null) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Payment Required',
                text: 'Payment amount cannot be empty.'
            });
            return;
        }
    
        const payment = parseFloat(paymentAmount);
    
        if (isNaN(payment)) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Number',
                text: 'Please enter a valid number for the payment amount.'
            });
            return;
        }
    
        if (payment < total) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Insufficient Payment',
                text: 'Payment amount is less than the total.'
            });
            return;
        }
    
        // Optional: Confirmation before submitting
        e.preventDefault(); // Stop auto-submit first
        Swal.fire({
            icon: 'question',
            title: 'Confirm Sale',
            text: 'Do you want to complete this sale?',
            showCancelButton: true,
            confirmButtonText: 'Yes, Record Sale',
            cancelButtonText: 'No, Review Again'
        }).then((result) => {
            if (result.isConfirmed) {
                saleForm.off('submit'); // Remove the handler to avoid recursion
                saleForm.submit(); // Then submit
            }
        });
    });

    });
</script>

@endpush

</x-app-layout>