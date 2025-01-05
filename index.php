<?php include 'templates/header.php'; ?>

<!-- Loading indicator -->
<div id="loading" class="text-center d-none">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">טוען...</span>
    </div>
</div>

<!-- Carousels Section -->
<div id="carousels-section">
    <!-- Entertainment & Mobile -->
    <div class="carousel-wrapper mb-5">
        <h2>טלוויזיות, סלולר ושואבי אבק</h2>
        <div id="carousel1" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <!-- Items will be loaded here -->
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carousel1" data-bs-slide="prev">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">הקודם</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carousel1" data-bs-slide="next">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">הבא</span>
            </button>
        </div>
    </div>

    <!-- Computers & Gaming -->
    <div class="carousel-wrapper mb-5">
        <h2>מחשבים וגיימינג</h2>
        <div id="carousel2" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <!-- Items will be loaded here -->
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carousel2" data-bs-slide="prev">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">הקודם</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carousel2" data-bs-slide="next">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">הבא</span>
            </button>
        </div>
    </div>

    <!-- Home Appliances -->
    <div class="carousel-wrapper mb-5">
        <h2>מוצרי חשמל לבית</h2>
        <div id="carousel3" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <!-- Items will be loaded here -->
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carousel3" data-bs-slide="prev">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">הקודם</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carousel3" data-bs-slide="next">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">הבא</span>
            </button>
        </div>
    </div>

    <!-- Furniture & Sports -->
    <div class="carousel-wrapper mb-5">
        <h2>ריהוט, גן וספורט</h2>
        <div id="carousel4" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <!-- Items will be loaded here -->
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carousel4" data-bs-slide="prev">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">הקודם</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carousel4" data-bs-slide="next">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">הבא</span>
            </button>
        </div>
    </div>
</div>

<!-- Items Section -->
<div id="items-section" class="d-none">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn btn-outline-primary" id="back-to-categories">
            <i class="fas fa-arrow-right ml-2"></i> חזרה לקטגוריות
        </button>
        <h2 class="category-title mb-0"></h2>
    </div>
    <div id="items-container" class="row"></div>
</div>

<!-- Error messages -->
<div id="error-message" class="alert alert-danger d-none"></div>

<?php include 'templates/footer.php'; ?>
