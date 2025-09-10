<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITENAME . ' | ' . $data['title']; ?></title>
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/style.css">
    <!-- Other common head elements -->
</head>
<body>
    <?php require_once APPROOT . '/views/includes/header.php'; ?>
    
    <main>
        <?php require_once $view . '.php'; ?>
    </main>
    
    <?php require_once APPROOT . '/views/includes/footer.php'; ?>
    
    <script src="<?php echo URLROOT; ?>/assets/js/main.js"></script>
</body>
</html>