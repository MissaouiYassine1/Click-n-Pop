<?php
// Déterminer le thème actuel
$currentTheme = $_SESSION['theme_pref'] ?? 'light';
$pageTitle = $title ?? "Click n' Pop | The Ultimate Bubble Popping Experience";
$metaDescription = $metaDescription ?? "Click n' Pop is an addictive, fast-paced bubble popping game with leaderboards, achievements, and power-ups. Challenge your friends and rise to the top!";
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($currentTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta name="keywords" content="bubble game, click game, popping game, online game, casual game, arcade, fun game">
    <meta name="author" content="Click n' Pop Studio">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta property="og:image" content="<?= $ogImage ?? '/assets/images/social-share.jpg' ?>">
    <meta property="og:url" content="https://clicknpop.com<?= $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:type" content="website">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@clicknpop">
    <meta name="twitter:creator" content="@clicknpop">
    
    <!-- Icons -->
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    
    <!-- Stylesheets -->
    
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    
    <!-- Preload critical resources -->
    <link rel="preload" href="/assets/js/core.js" as="script">
    <link rel="preload" href="/assets/fonts/BubblegumSans-Regular.woff2" as="font" type="font/woff2" crossorigin>
    
    <!-- Schema.org -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "Click n' Pop",
        "description": "Addictive bubble popping arcade game",
        "url": "https://clicknpop.com",
        "applicationCategory": "GameApplication",
        "operatingSystem": "Any",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        }
    }
    </script>
</head>
<body class="<?= $bodyClass ?? '' ?>">
    <!-- Loading Spinner -->
    <div id="global-loader" class="global-loader">
        <div class="loader-bubbles">
            <div class="bubble-loader"></div>
            <div class="bubble-loader"></div>
            <div class="bubble-loader"></div>
        </div>
    </div>

    <!-- Header -->
    <?php include __DIR__ . '/../components/header.php'; ?>

    <!-- Toast Notifications -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Main Content -->
    <main id="main-content" class="main-content">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../components/footer.php'; ?>

    <!-- Theme Toggle -->
    <button id="theme-toggle" class="theme-toggle" aria-label="Toggle dark/light mode">
        <i class="fas fa-moon"></i>
        <i class="fas fa-sun"></i>
    </button>

    <!-- Back to Top -->
    <button id="back-to-top" class="back-to-top" aria-label="Back to top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Scripts -->
    <script src="/assets/js/core.js" defer></script>
    <?php if (isset($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?= $script ?>" defer></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>