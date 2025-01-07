<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';

use App\Bootstrap;

// Initialize application
Bootstrap::init();

?>
<!DOCTYPE html>
<html lang="en">
    <?php include BASE_PATH . '/templates/header.php'; ?>
    <body>
        <?php include BASE_PATH . '/templates/form.php'; ?>
        <?php include BASE_PATH . '/templates/footer.php'; ?>
    </body>
</html>
