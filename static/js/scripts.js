let currentPage = 1;
const itemsPerPage = 10;
let isLoading = false;
let hasMoreItems = true;

//Example for getItems, you are allowed to change it if needed.
function get_items(options = {}, append = false) {
    if (isLoading || (!append && !hasMoreItems)) return;
    
    isLoading = true;
    $('#loading-indicator').show();
    
    const params = {
        act: 'getItems',
        page: append ? currentPage : 1,
        limit: itemsPerPage,
        category: options.category || '',
        price_min: options.price_min || '',
        price_max: options.price_max || '',
        brand: options.brand || '',
        sort_field: options.sort_field || 'name',
        sort_direction: options.sort_direction || 'ASC'
    };
    
    $.ajax({
        url: 'ajax/ajax.php',
        method: 'POST',
        data: params,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                if (!append) {
                    $('#product-list').empty();
                    currentPage = 1;
                }
                
                response.data.items.forEach(function(item) {
                    $('#product-list').append(
                        $('<div>').addClass('product-item').append(
                            $('<img>').addClass('product-image')
                                .attr('src', item.image_url)
                                .attr('alt', item.name),
                            $('<h3>').text(item.name),
                            $('<p>').addClass('brand').text(item.brand),
                            $('<p>').addClass('price').text('$' + item.price.toFixed(2)),
                        )
                    );
                });
                
                hasMoreItems = response.data.items.length === itemsPerPage;
                if (hasMoreItems) currentPage++;
            } else {
                console.error('Error fetching items:', response.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX error:', textStatus, errorThrown);
        },
        complete: function() {
            isLoading = false;
            $('#loading-indicator').hide();
        }
    });
}

// Carousel functionality
function loadCarousel(carouselId, categoryId) {
    $.ajax({
        url: 'ajax/ajax.php',
        method: 'POST',
        data: {
            act: 'getCarouselItems',
            category_id: categoryId,
            limit: 10
        },
        success: function(response) {
            if (response.status === 'success') {
                const items = response.data;
                const carouselInner = $(`#${carouselId} .carousel-inner`);
                carouselInner.empty();
                
                // Group items into slides (4 items per slide)
                for (let i = 0; i < items.length; i += 4) {
                    const slideItems = items.slice(i, i + 4);
                    const isActive = i === 0 ? 'active' : '';
                    
                    const slide = $('<div>', {
                        class: `carousel-item ${isActive}`
                    });
                    
                    const row = $('<div>', {
                        class: 'row'
                    });
                    
                    slideItems.forEach(item => {
                        const col = $('<div>', {
                            class: 'col-md-3'
                        }).append(
                            $('<div>', {
                                class: 'card h-100'
                            }).append(
                                $('<img>', {
                                    src: item.image_url,
                                    class: 'card-img-top',
                                    alt: item.name
                                }),
                                $('<div>', {
                                    class: 'card-body'
                                }).append(
                                    $('<h5>', {
                                        class: 'card-title',
                                        text: item.name
                                    }),
                                    $('<p>', {
                                        class: 'card-text brand',
                                        text: item.brand
                                    }),
                                    $('<p>', {
                                        class: 'card-text price',
                                        text: `$${parseFloat(item.price).toFixed(2)}`
                                    })
                                )
                            )
                        );
                        row.append(col);
                    });
                    
                    slide.append(row);
                    carouselInner.append(slide);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading carousel:', error);
        }
    });
}

$(document).ready(function() {
    get_items();

    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 500) {
            get_items({}, true);
        }
    });
    
    // Example filter handlers
    $('#category-filter').on('change', function() {
        get_items({ category: $(this).val() });
    });
    
    $('#sort-select').on('change', function() {
        const [field, direction] = $(this).val().split('-');
        get_items({ 
            sort_field: field, 
            sort_direction: direction 
        });
    });
    
    // Price filter example
    $('#price-filter').on('submit', function(e) {
        e.preventDefault();
        get_items({
            price_min: $('#price-min').val(),
            price_max: $('#price-max').val()
        });
    });
    
    // Load carousels with their respective category IDs
    loadCarousel('carousel-1', 1); // Replace with actual category IDs
    loadCarousel('carousel-2', 2);
    loadCarousel('carousel-3', 3);
});
