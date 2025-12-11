import * as bootstrap from 'bootstrap'; // Import Bootstrap for Modal
import $ from 'jquery';

$(function() {
    console.log("✅ Products Create Script Loaded");

    const categoryInput = $('#category');
    const hiddenCategoryId = $('#category_id');
    const modalEl = document.getElementById('categorySearchModal');
    const modalSearchInput = $('#modal-category-search');
    const modalList = $('#modal-category-list');
    let searchMoreModal = null; // Bootstrap modal instance

    if (categoryInput.length === 0) return;

    const allCategories = categoryInput.data('source'); 

    // 1. Initialize Autocomplete
    categoryInput.autocomplete({
        source: function(request, response) {
            const term = request.term.toLowerCase();
            
            // Filter categories
            let results = allCategories.filter(c => 
                c.name.toLowerCase().includes(term)
            ).map(c => ({
                label: c.name,
                value: c.name,
                id: c.id
            }));

            // ✅ LIMIT LOGIC: Only show top 8 in dropdown
            const limit = 8;
            const hasMore = results.length > limit;
            
            if (hasMore) {
                results = results.slice(0, limit);
                // Add special "Search More" item at the bottom
                results.push({
                    label: 'Search More...',
                    value: 'SEARCH_MORE',
                    isSearchMore: true
                });
            }

            // Exact Match Logic
            const exactMatch = allCategories.some(c => c.name.toLowerCase() === term);

            // Add "Create New" at TOP if needed
            if (term.length > 0 && !exactMatch) {
                results.unshift({
                    label: `Create "${request.term}"`, 
                    value: request.term,               
                    isNew: true                        
                });
            }

            response(results);
        },
        select: function(event, ui) {
            // Handle "Search More"
            if (ui.item.isSearchMore) {
                if (!searchMoreModal) {
                    searchMoreModal = new bootstrap.Modal(modalEl);
                }
                
                // Pre-fill modal search with what user typed
                modalSearchInput.val(categoryInput.val());
                renderModalList(categoryInput.val()); // Show full list filtered by current input
                
                searchMoreModal.show();
                return false; // Prevent input update
            }

            // Handle Standard Selection
            $('#category').val(ui.item.value);
            
            if (ui.item.isNew) {
                hiddenCategoryId.val(''); // New category = no ID yet
            } else {
                hiddenCategoryId.val(ui.item.id);
            }
            return false; 
        },
        focus: function(event, ui) {
            return false; 
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        if (item.isNew) {
            return $("<li>")
                .addClass("create-new-item mb-1")
                .append(`<div class='ui-menu-item-wrapper'><i class="fas fa-plus-circle me-2"></i>${item.label}</div>`)
                .appendTo(ul);
        }
        if (item.isSearchMore) {
            return $("<li>")
                .addClass("search-more-item mt-1")
                .append(`<div class='ui-menu-item-wrapper'><i class="fas fa-search me-2"></i>${item.label}</div>`)
                .appendTo(ul);
        }
        return $("<li>")
            .append(`<div class='ui-menu-item-wrapper'>${item.label}</div>`)
            .appendTo(ul);
    };

    // 2. Modal Search Logic
    function renderModalList(filterText) {
        modalList.empty();
        const term = filterText.toLowerCase();
        
        // In the modal, we show ALL matches (no limit)
        const filtered = allCategories.filter(c => c.name.toLowerCase().includes(term));

        if (filtered.length === 0) {
            modalList.append('<div class="p-4 text-center text-muted fs-5">No categories found.</div>');
            return;
        }

        filtered.forEach(cat => {
            const item = $(`
                <button type="button" class="list-group-item list-group-item-action border-0 border-bottom">
                    ${cat.name}
                </button>
            `);
            
            item.on('click', function() {
                categoryInput.val(cat.name);
                hiddenCategoryId.val(cat.id);
                searchMoreModal.hide();
            });
            
            modalList.append(item);
        });
    }

    modalSearchInput.on('input', function() {
        renderModalList($(this).val());
    });

    modalEl.addEventListener('shown.bs.modal', function () {
        modalSearchInput.focus();
        // If input is empty, show all. If text exists, filter by it.
        renderModalList(modalSearchInput.val()); 
    });

    // 3. Clear Button
    $('#clear-category-btn').on('click', function() {
        categoryInput.val('').focus();
        hiddenCategoryId.val('');
    });
});