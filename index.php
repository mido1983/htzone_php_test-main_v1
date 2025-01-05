<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTZone Sale</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="static/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <header class="my-4">
            <h1>HTZone Sale</h1>
        </header>

        <main>
            <!-- Carousels Section -->
            <section class="carousels-wrapper">
                <div id="carousel-1" class="carousel-container mb-4">
                    <h2 class="mb-3">Category 1</h2>
                    <div class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <!-- Items will be loaded here -->
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-1" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-1" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>

                <div id="carousel-2" class="carousel-container mb-4">
                    <h2 class="mb-3">Category 2</h2>
                    <div class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <!-- Items will be loaded here -->
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-2" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-2" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>

                <div id="carousel-3" class="carousel-container mb-4">
                    <h2 class="mb-3">Category 3</h2>
                    <div class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <!-- Items will be loaded here -->
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-3" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-3" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Filters Section -->
            <section class="filters-wrapper">
                <select id="category-filter">
                    <option value="">All Categories</option>
                </select>
                <select id="sort-select">
                    <option value="name-ASC">Name (A-Z)</option>
                    <option value="name-DESC">Name (Z-A)</option>
                    <option value="price-ASC">Price (Low to High)</option>
                    <option value="price-DESC">Price (High to Low)</option>
                </select>
                <form id="price-filter">
                    <input type="number" id="price-min" placeholder="Min Price">
                    <input type="number" id="price-max" placeholder="Max Price">
                    <button type="submit">Apply</button>
                </form>
            </section>

            <!-- Product List Section -->
            <section class="products-wrapper">
                <div id="product-list" class="grid-layout"></div>
                <div id="loading-indicator">Loading...</div>
            </section>
        </main>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="static/js/scripts.js"></script>
</body>
</html>
