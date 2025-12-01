import $ from 'jquery';
import Swal from 'sweetalert2';

$(function() {
    console.log("✅ Create Sale Script Loaded");

    // 1. State & Elements
    let saleItems = [];
    const productSearch = $('#product_search');
    const saleItemsTable = $('#sale-items-table');
    const emptyCartMessage = $('#empty-cart-message');
    const totalAmountSpan = $('#total-amount');
    const changeAmountSpan = $('#change-amount');
    const changeDisplay = $('#change-display');
    const paymentAmountInput = $('#payment_amount');
    const saleForm = $('#sale-form');

    // 2. Search Logic (Same as before)
    productSearch.autocomplete({
        source: function(request, response) {
            const searchUrl = productSearch.data('route'); 
            $.get(searchUrl, { query: request.term }, function(data) {
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
            addProductToSale(ui.item);
            productSearch.val(''); 
            return false;
        },
        minLength: 1 
    });

    productSearch.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault(); 
            const query = $(this).val().trim();
            const searchUrl = productSearch.data('route');

            if (query.length > 0) {
                $.get(searchUrl, { query: query }, function(data) {
                    if (data.length === 1) {
                        const item = {
                            id: data[0].id,
                            label: data[0].label,
                            price: parseFloat(data[0].price),
                            net_weight: data[0].net_weight,
                            net_weight_unit_name: data[0].net_weight_unit_name,
                            value: data[0].value 
                        };
                        addProductToSale(item);
                        productSearch.val('');
                        productSearch.autocomplete("close");
                    } else if (data.length > 1) {
                        productSearch.autocomplete("search", query);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Not Found', text: 'Product not found.', timer: 1000, showConfirmButton: false });
                        productSearch.val('');
                    }
                });
            }
        }
    });

    // 3. Logic Functions
    function addProductToSale(product) {
        let existingItem = saleItems.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            saleItems.push({
                id: product.id,
                name: product.value || product.label,
                price: product.price,
                net_weight: product.net_weight,
                net_weight_unit_name: product.net_weight_unit_name,
                quantity: 1
            });
        }
        // Full render only when adding/removing rows
        renderSaleItems();
    }

    function renderSaleItems() {
        saleItemsTable.empty();
        
        if (saleItems.length === 0) {
            emptyCartMessage.show();
            saleItemsTable.closest('table').hide();
            totalAmountSpan.text('0.00');
            updateChange();
            return;
        } else {
            emptyCartMessage.hide();
            saleItemsTable.closest('table').show();
        }

        saleItems.forEach((item, index) => {
            const price = parseFloat(item.price) || 0;
            const quantity = parseInt(item.quantity) || 0;
            const subtotal = price * quantity;
            
            const weightText = (item.net_weight && item.net_weight_unit_name) 
                ? `<small class="text-muted d-block">${item.net_weight} ${item.net_weight_unit_name}</small>` 
                : '';

            const row = `
                <tr data-index="${index}">
                    <td class="ps-3">
                        <span class="fw-bold text-dark">${item.name}</span>
                        ${weightText}
                    </td>
                    <td class="text-center">₱${price.toFixed(2)}</td>
                    <td class="text-center">
                        <div class="input-group input-group-sm mx-auto" style="width: 120px;">
                            <button class="btn btn-outline-secondary btn-decrease" type="button">-</button>
                            <input type="number" 
                                value="${quantity}" 
                                min="1" 
                                class="form-control text-center quantity-input fw-bold" 
                                data-index="${index}">
                            <button class="btn btn-outline-secondary btn-increase" type="button">+</button>
                        </div>
                    </td>
                    <td class="text-end fw-bold subtotal-cell">₱${subtotal.toFixed(2)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-link text-danger p-0 remove-item" data-index="${index}" title="Remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
            saleItemsTable.append(row);
        });

        recalculateTotals();
    }

    // 🆕 New Function: Updates totals without re-rendering HTML
    function recalculateTotals() {
        let total = 0;
        saleItems.forEach(item => {
            total += (parseFloat(item.price) || 0) * (parseInt(item.quantity) || 0);
        });

        totalAmountSpan.text(total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        updateChange();
        syncHiddenInputs();
    }

    function syncHiddenInputs() {
        saleForm.find('input[name^="sale_items"]').remove();
        saleItems.forEach((item, index) => {
            saleForm.append(`<input type="hidden" name="sale_items[${index}][id]" value="${item.id}">`);
            saleForm.append(`<input type="hidden" name="sale_items[${index}][quantity]" value="${item.quantity}">`);
        });
    }

    function updateChange() {
        const total = parseFloat(totalAmountSpan.text().replace(/,/g, '')) || 0;
        const payment = parseFloat(paymentAmountInput.val()) || 0;
        const change = payment - total;

        changeAmountSpan.text(change.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        
        changeDisplay.removeClass('text-success text-danger text-secondary');

        if (payment > 0) {
            changeDisplay.addClass(change >= 0 ? 'text-success' : 'text-danger');
        } else {
            changeDisplay.addClass('text-secondary');
        }
    }

    // 4. Event Listeners

    // ✅ FIX: Quantity Input Logic
    saleItemsTable.on('input', '.quantity-input', function() {
        // Use .attr() to get the fresh attribute from DOM, avoiding jQuery cache issues
        const index = parseInt($(this).attr('data-index'));
        let val = $(this).val();
        
        // Safety check for index
        if (typeof saleItems[index] === 'undefined') return;

        // Allow empty string while typing, otherwise parse
        if (val === '') {
            saleItems[index].quantity = 0; 
        } else {
            saleItems[index].quantity = parseInt(val);
        }

        // Update ONLY the subtotal for this row (DOM Manipulation)
        const row = $(this).closest('tr');
        const price = parseFloat(saleItems[index].price);
        const subtotal = price * (parseInt(val) || 0);
        
        // Update the subtotal text immediately
        row.find('.subtotal-cell').text('₱' + subtotal.toFixed(2));

        // Recalculate Grand Total
        recalculateTotals();
    });

    // ✅ FIX: Blur Event (Reset invalid values when user leaves field)
    saleItemsTable.on('blur', '.quantity-input', function() {
        const index = parseInt($(this).attr('data-index'));
        let val = parseInt($(this).val());

        if (typeof saleItems[index] === 'undefined') return;

        if (isNaN(val) || val < 1) {
            $(this).val(1); // Visual reset
            saleItems[index].quantity = 1; // Data reset
            
            // Update subtotal again
            const price = parseFloat(saleItems[index].price);
            $(this).closest('tr').find('.subtotal-cell').text('₱' + price.toFixed(2));
            recalculateTotals();
        }
    });

    // ✅ New: +/- Buttons for easier touch/click adjustments
    saleItemsTable.on('click', '.btn-increase', function() {
        const input = $(this).siblings('.quantity-input');
        let currentVal = parseInt(input.val()) || 0;
        input.val(currentVal + 1).trigger('input');
    });

    saleItemsTable.on('click', '.btn-decrease', function() {
        const input = $(this).siblings('.quantity-input');
        let val = parseInt(input.val()) || 0;
        if (val > 1) {
            input.val(val - 1).trigger('input');
        }
    });

    // Remove Item
    saleItemsTable.on('click', '.remove-item', function() {
        const index = $(this).data('index');
        saleItems.splice(index, 1);
        renderSaleItems(); // Full re-render needed here to update indices
    });

    paymentAmountInput.on('input', updateChange);

    saleForm.on('submit', function(e) {
        e.preventDefault();
        
        const total = parseFloat(totalAmountSpan.text().replace(/,/g, '')) || 0;
        const payment = parseFloat(paymentAmountInput.val()) || 0;

        if (saleItems.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Cart Empty', text: 'Add products first.', confirmButtonColor: '#3085d6' });
            return;
        }

        if (payment < total) {
            Swal.fire({ icon: 'error', title: 'Insufficient Payment', text: `Short by ₱${(total - payment).toFixed(2)}`, confirmButtonColor: '#d33' });
            return;
        }

        Swal.fire({
            title: 'Complete Sale?',
            text: `Total: ₱${total.toFixed(2)} | Change: ₱${(payment - total).toFixed(2)}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Yes, Record it!'
        }).then((result) => {
            if (result.isConfirmed) {
                syncHiddenInputs();
                this.submit();
            }
        });
    });
});