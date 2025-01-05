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
                $('<div>').addClass('category-card card h-100').append(
                    // Add demo image
                    $('<img>')
                        .addClass('card-img-top')
                        .attr({
                            'src': 'static/images/demo.webp',
                            'alt': category.title
                        })
                        .css({
                            'height': '200px',
                            'object-fit': 'cover'
                        }),
                    $('<div>').addClass('card-body text-center').append(
                        $('<h5>')
                            .addClass('card-title mb-3')
                            .text(category.title),
                        $('<button>')
                            .addClass('btn btn-primary view-items')
                            .attr('data-category-id', category.category_id)
                            .text('View Items')
                    )
                )
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
                    <div class="card h-100">
                        ${item.image_url ? 
                            `<img src="${item.image_url}" class="card-img-top" alt="${item.title}">` 
                            : ''}
                        <div class="card-body">
                            <h5 class="card-title">${item.title}</h5>
                            ${item.description_json ? 
                                `<div class="card-text">${formatDescription(item.description_json)}</div>` 
                                : ''}
                            <p class="card-text">
                                <strong>Price: ${formatPrice(item.price)}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            `);
            container.append(card);
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
});
