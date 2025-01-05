<?php
require_once 'class/HtzoneApi.php';

try {
    $api = new HtzoneApi();
    
    // Get and store categories
    echo "<h2>Fetching and storing categories...</h2>";
    $result = $api->getCategories();
    
    echo "<div style='background: #e8f5e9; padding: 10px; margin: 10px 0;'>";
    echo "<h3>Storage Results:</h3>";
    echo "Successfully stored {$result['stored_count']} categories in database<br>";
    
    // Verify database storage
    $db = Database::getInstance()->getConnection();
    
    // Show all categories from database
    $dbResult = $db->query("SELECT * FROM categories");
    echo "<h3>Categories in Database:</h3>";
    echo "<table border='1' style='width: 100%; margin-top: 10px;'>";
    echo "<tr>
            <th>ID</th>
            <th>Title</th>
            <th>Parent Title</th>
            <th>Group Title</th>
            <th>Action</th>
        </tr>";
    
    while ($row = $dbResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['category_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['parent_title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['group_title']) . "</td>";
        echo "<td><a href='?category=" . $row['category_id'] . "'>View Items</a></td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // If category is selected, show items
    if (isset($_GET['category'])) {
        $categoryId = (int)$_GET['category'];
        echo "<h2>Fetching data for category {$categoryId}...</h2>";
        
        // Get both regular items and sub-category items
        $items = $api->getItems($categoryId);
        $subCategory = $api->getSubCategory($categoryId);
        
        // Debug Sub-Category Response
        echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px 0; border: 1px solid #dee2e6;'>";
        echo "<h3>Sub-Category Response:</h3>";
        echo "<pre style='max-height: 300px; overflow: auto;'>";
        if (isset($subCategory['api_data']) && is_array($subCategory['api_data'])) {
            print_r($subCategory['api_data']);
        } else {
            echo "No sub-category data available";
        }
        echo "</pre>";
        echo "</div>";
        
        // Debug Items Response
        echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px 0; border: 1px solid #dee2e6;'>";
        echo "<h3>Items Response:</h3>";
        echo "<pre style='max-height: 300px; overflow: auto;'>";
        if (isset($items['api_data']) && is_array($items['api_data'])) {
            print_r($items['api_data']);
        } else {
            echo "No items data available";
        }
        echo "</pre>";
        echo "</div>";
        
        // Show storage results
        echo "<div style='background: #e8f5e9; padding: 10px; margin: 10px 0;'>";
        echo "<h3>Storage Results:</h3>";
        echo "<ul>";
        echo "<li>Regular items stored: {$items['stored_count']}</li>";
        echo "<li>Sub-category items stored: {$subCategory['stored_count']}</li>";
        echo "</ul>";
        
        // Show items from database
        $itemsResult = $db->query("SELECT * FROM items WHERE category_id = {$categoryId}");
        $totalItems = $itemsResult->num_rows;
        
        echo "<h3>Items in Database ({$totalItems} total):</h3>";
        echo "<div class='row'>";
        
        while ($item = $itemsResult->fetch_assoc()) {
            echo "<div class='col-md-4 mb-4'>";
            echo "<div class='card'>";
            if (!empty($item['image_url'])) {
                echo "<img src='" . htmlspecialchars($item['image_url']) . "' class='card-img-top' alt='" . htmlspecialchars($item['title']) . "'>";
            }
            echo "<div class='card-body'>";
            echo "<h5 class='card-title'>" . htmlspecialchars($item['title']) . "</h5>";
            
            // Handle description display
            if (!empty($item['description'])) {
                echo "<p class='card-text'>";
                // Split description by newlines and display as bullet points if multiple lines
                $descLines = explode("\n", $item['description']);
                if (count($descLines) > 1) {
                    echo "<ul class='list-unstyled mb-2'>";
                    foreach ($descLines as $line) {
                        if (!empty(trim($line))) {
                            echo "<li>• " . htmlspecialchars(trim($line)) . "</li>";
                        }
                    }
                    echo "</ul>";
                } else {
                    echo htmlspecialchars($item['description']);
                }
                echo "</p>";
            }
            
            echo "<p class='card-text'><strong>Price: ₪" . number_format($item['price'], 2) . "</strong></p>";
            echo "</div></div></div>";
        }
        
        echo "</div>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px; background: #ffebee; border: 1px solid #ffcdd2;'>";
    echo "<h2>Error occurred:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Debug Information:</h3>";
    echo "<pre>";
    print_r(error_get_last());
    echo "</pre>";
    echo "</div>";
}
?>
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
