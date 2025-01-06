<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTZone Sale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="static/css/styles.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar bg-primary text-white py-2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <span><i class="fas fa-phone-alt me-2"></i>שירות לקוחות: 1-800-000-000</span>
                </div>
                <div class="col-md-6 text-end">
                    <span><i class="fas fa-truck me-2"></i>משלוח חינם בקנייה מעל ₪500</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="site-header py-3 bg-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="/" class="logo">
                        <img src="static/images/logo_htz2.webp" alt="HTZone" class="img-fluid">
                    </a>
                </div>
                <div class="col-md-6">
                    <form class="search-form">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="חיפוש מוצרים...">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-3 text-end">
                    <div class="header-icons">
                        <a href="#" class="me-3"><i class="fas fa-user"></i></a>
                        <a href="#" class="me-3"><i class="fas fa-heart"></i></a>
                        <a href="#"><i class="fas fa-shopping-cart"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            טלוויזיות ובידור ביתי
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-category="1580">טלוויזיות</a></li>
                            <li><a class="dropdown-item" href="#" data-category="1818">אודיו וסאונד בר</a></li>
                            <li><a class="dropdown-item" href="#" data-category="3151">סטרימרים וקונסולות</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            מחשבים וגיימינג
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-category="1042">מחשבים ניידים</a></li>
                            <li><a class="dropdown-item" href="#" data-category="2210">מסכי מחשב</a></li>
                            <li><a class="dropdown-item" href="#" data-category="3671">מחשבי גיימינג</a></li>
                        </ul>
                    </li>
                    <!-- Add more menu items -->
                </ul>
            </div>
        </div>
    </nav>

    <main class="site-main py-4">
        <div class="container"> 