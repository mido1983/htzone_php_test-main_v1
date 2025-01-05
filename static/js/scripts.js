$(document).ready(function() {
    // Category groups configuration
    const carouselGroups = {
        carousel1: [1580, 1818, 3151, 3153, 1554, 2639, 1519, 2181, 1887, 3935, 4257, 2362],
        carousel2: [1042, 2210, 1043, 3671, 3674],
        carousel3: [2624, 2626, 2625, 2881, 2127, 2273, 2182, 2164, 2417, 2131, 2618, 2638, 5905, 6224, 2620],
        carousel4: [2969, 2970, 2972, 2385, 2598, 2328, 1262, 3389, 2793, 4876, 1616]
    };

    // Show/Hide loading indicator
    function showLoading() {
        $('#loading').removeClass('d-none');
    }

    function hideLoading() {
        $('#loading').addClass('d-none');
    }

    // Error handling
    function showError(message) {
        $('#error-message').text(message).removeClass('d-none');
    }

    function hideError() {
        $('#error-message').addClass('d-none');
    }

    // Format price
    function formatPrice(price) {
        return '₪' + parseFloat(price).toFixed(2);
    }

    // Load categories and organize them into carousels
    function loadCategories() {
        showLoading();
        hideError();

        $.ajax({
            url: 'ajax/ajax.php',
            type: 'GET',
            data: { action: 'getCategories' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    organizeCarousels(response.data);
                } else {
                    showError(response.message || 'Failed to load categories');
                }
            },
            error: function(xhr, status, error) {
                showError('Error loading categories: ' + error);
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    // Organize categories into carousel slides
    function organizeCarousels(categories) {
        Object.entries(carouselGroups).forEach(([carouselId, categoryIds]) => {
            const carouselCategories = categories.filter(cat => categoryIds.includes(parseInt(cat.category_id)));
            const slides = [];
            
            // Create slides with 4 categories each
            for (let i = 0; i < carouselCategories.length; i += 4) {
                const slideCategories = carouselCategories.slice(i, i + 4);
                slides.push(createCarouselSlide(slideCategories));
            }

            // Add slides to carousel
            const carouselInner = $(`#${carouselId} .carousel-inner`);
            carouselInner.empty();
            
            slides.forEach((slide, index) => {
                carouselInner.append(
                    $('<div>')
                        .addClass(`carousel-item ${index === 0 ? 'active' : ''}`)
                        .append(slide)
                );
            });
        });
    }

    // Create a carousel slide with categories
    function createCarouselSlide(categories) {
        const row = $('<div>').addClass('row g-4');
        
        categories.forEach(category => {
            const col = $('<div>').addClass('col-md-3').append(
                $('<div>')
                    .addClass('product-card')
                    .attr('data-category-id', category.category_id)
                    .css('cursor', 'pointer')
                    .append(
                        // Image wrapper with favorite icon
                        $('<div>').addClass('product-image-wrapper').append(
                            $('<img>')
                                .addClass('product-image')
                                .attr({
                                    'src': category.image_url || 'static/images/demo.webp',
                                    'alt': category.title
                                }),
                            $('<div>').addClass('favorite-icon').append(
                                $('<i>').addClass('fas fa-heart')
                            )
                        ),
                        // Product info
                        $('<h3>')
                            .addClass('product-title')
                            .text(category.title),
                        $('<p>')
                            .addClass('product-description')
                            .text(category.description || 'תיאור המוצר'),
                        // Price section
                        $('<div>').addClass('price-section').append(
                            $('<div>').addClass('original-price').append(
                                $('<span>').addClass('currency').text('₪'),
                                $('<span>').addClass('amount').text(category.price || '125')
                            ),
                            $('<div>').addClass('discounted-price').append(
                                $('<span>').text('מחיר מועדון: '),
                                $('<span>').addClass('currency').text('₪'),
                                $('<span>').addClass('amount').text('94')
                            )
                        )
                    )
                    .on('click', function() {
                        const categoryId = $(this).data('category-id');
                        loadItems(categoryId);
                    })
            );
            row.append(col);
        });

        return row;
    }

    // Load and display items
    function loadItems(categoryId) {
        showLoading();
        hideError();
        
        $.ajax({
            url: 'ajax/ajax.php',
            type: 'GET',
            data: {
                action: 'getItems',
                categoryId: categoryId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayItems(response.data);
                    $('#carousels-section').addClass('d-none');
                    $('#items-section').removeClass('d-none');
                } else {
                    showError(response.message || 'Failed to load items');
                }
            },
            error: function(xhr, status, error) {
                showError('Error loading items: ' + error);
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    // Display items in grid
    function displayItems(items) {
        const container = $('#items-container');
        container.empty();

        items.forEach(item => {
            const card = $(`
                <div class="col-md-4 mb-4">
                    <div class="card h-100 item-card" data-item-api-id="${item.id}" role="button">
                        ${item.image_url ? 
                            `<img src="${item.image_url}" class="card-img-top" alt="${item.title}">` 
                            : ''}
                        <div class="card-body">
                            <h5 class="card-title">${item.title}</h5>
                            <p class="card-text">
                                <strong>Price: ${formatPrice(item.price)}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            `);

            // Add click handler
            card.find('.item-card').on('click', function(e) {
                e.preventDefault();
                const itemApiId = $(this).data('item-api-id');
                showLoading();
                loadItemDetails(itemApiId);
            });

            container.append(card);
        });
    }

    // Format description preview (shortened version)
    function formatDescriptionPreview(descriptionJson) {
        try {
            const desc = JSON.parse(descriptionJson);
            if (desc.description && desc.description.length > 0) {
                return desc.description[0].substring(0, 100) + '...';
            }
            return '';
        } catch (e) {
            return '';
        }
    }

    // Show product details in modal
    function showProductDetails(item) {
        $('#productModal').remove();
        
        const modalHtml = `
            <div class="modal fade" id="productModal" tabindex="-1" dir="rtl">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title fw-bold">${item.title}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="product-images">
                                        ${item.images && item.images.length > 0 ? `
                                            <div class="main-image mb-3">
                                                <img src="${item.images[0]}" class="img-fluid rounded shadow" alt="${item.title}">
                                            </div>
                                            ${item.images.length > 1 ? `
                                                <div class="image-thumbnails row g-2">
                                                    ${item.images.map(img => `
                                                        <div class="col-3">
                                                            <img src="${img}" class="img-thumbnail cursor-pointer" 
                                                                onclick="document.querySelector('.main-image img').src='${img}'">
                                                        </div>
                                                    `).join('')}
                                                </div>
                                            ` : ''}
                                        ` : ''}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="product-info">
                                        ${item.sub_title ? `<p class="text-muted mb-3">${item.sub_title}</p>` : ''}
                                        <div class="price-section mb-4">
                                            <h3 class="text-primary mb-2">מחיר: ${formatPrice(item.price)}</h3>
                                            ${item.warrenty_info ? `
                                                <div class="warranty-info">
                                                    <i class="fas fa-shield-alt"></i>
                                                    <span>${item.warrenty_info}</span>
                                                </div>
                                            ` : ''}
                                        </div>
                                        ${item.brief ? `
                                            <div class="brief-section mb-4">
                                                <h6 class="fw-bold">תיאור מוצר:</h6>
                                                <div class="brief-content">${item.brief}</div>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                            
                            ${item.features && Object.keys(item.features).length > 0 ? `
                                <div class="features-section mt-4">
                                    <h6 class="fw-bold mb-3">מפרט טכני:</h6>
                                    ${formatFeatures(item.features)}
                                </div>
                            ` : ''}
                            
                            ${item.delivery_info ? `
                                <div class="delivery-section mt-4">
                                    <h6 class="fw-bold mb-3">מידע על משלוח:</h6>
                                    <div class="delivery-content">${item.delivery_info}</div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHtml);
        const modal = new bootstrap.Modal('#productModal');
        modal.show();
    }

    // Format full description
    function formatFullDescription(descriptionJson) {
        try {
            const desc = JSON.parse(descriptionJson);
            let html = '<div class="product-description">';
            
            if (desc.description && desc.description.length > 0) {
                html += '<h5>Description</h5><ul>';
                desc.description.forEach(item => {
                    html += `<li>${item}</li>`;
                });
                html += '</ul>';
            }
            
            html += '</div>';
            return html;
        } catch (e) {
            return '';
        }
    }

    // Load item details
    function loadItemDetails(itemApiId) {
        $.ajax({
            url: 'ajax/ajax.php',
            type: 'GET',
            data: {
                action: 'getItemDetails',
                itemApiId: itemApiId
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    showProductDetails(response.data);
                } else {
                    showError(response.message || 'Failed to load item details');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                showError('Error loading item details: ' + error);
            }
        });
    }

    // Event handlers
    $(document).on('click', '.view-items', function() {
        const categoryId = $(this).data('category-id');
        loadItems(categoryId);
    });

    $('#back-to-categories').click(function() {
        $('#items-section').addClass('d-none');
        $('#carousels-section').removeClass('d-none');
    });

    // Initialize
    loadCategories();

    // Format description for display
    function formatDescription(description) {
        if (!description) return '';
        
        let html = '<div class="product-description">';
        
        // Add main description if exists
        if (description.description) {
            if (typeof description.description === 'string') {
                html += description.description;
            } else if (Array.isArray(description.description)) {
                html += description.description.join('<br>');
            }
        }
        
        // Add delivery info if exists
        if (description.delivery_info) {
            html += '<div class="delivery-info mt-3">';
            html += '<h6>Delivery Information:</h6>';
            html += description.delivery_info;
            html += '</div>';
        }
        
        html += '</div>';
        return html;
    }

    // Format features for display
    function formatFeatures(features) {
        if (!features || Object.keys(features).length === 0) return '';
        
        const translations = {
            'colors': 'צבעים',
            'features': 'מאפיינים',
            'dimensions': 'מידות',
            'technical': 'מפרט טכני'
            // Add more translations as needed
        };
        
        let html = '<div class="features-container">';
        
        Object.entries(features).forEach(([category, items]) => {
            if (items && Object.keys(items).length > 0) {
                const translatedCategory = translations[category] || category;
                html += `<div class="feature-category">`;
                html += `<h6 class="fw-bold">${translatedCategory}</h6>`;
                html += `<div class="feature-items">`;
                
                if (Array.isArray(items)) {
                    items.forEach(item => {
                        html += `<div class="feature-item">${item}</div>`;
                    });
                } else {
                    Object.entries(items).forEach(([key, value]) => {
                        html += `<div class="feature-item"><strong>${key}:</strong> ${value}</div>`;
                    });
                }
                
                html += `</div></div>`;
            }
        });
        
        html += '</div>';
        return html;
    }

    $(document).on('click', '.dropdown-item', function(e) {
        e.preventDefault();
        const categoryId = $(this).data('category-id');
        if (categoryId) {
            loadItems(categoryId);
        }
    });

    $(document).on('click', '.favorite-icon', function(e) {
        e.stopPropagation();
        $(this).toggleClass('active');
    });
});
