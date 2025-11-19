<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? "Click n' Pop"; ?></title>
</head>
<body>
    <?php include 'components/header.php'; ?>

    <main>
        <?= $content ?? ""; ?>
    </main>

    <?php include 'components/footer.php'; ?>
</body>
</html>
