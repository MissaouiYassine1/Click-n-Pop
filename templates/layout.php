<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? "Click n' Pop"; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="../assets/js/animations.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../components/header.php'; ?>


    <main>
        <?= $content ?? ""; ?>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>
    
</body>
</html>
