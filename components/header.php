<?php
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['username'] ?? 'Guest';
$userInitial = strtoupper(substr($userName, 0, 1));
$userLevel = $_SESSION['user_level'] ?? 1;
$userScore = $_SESSION['total_score'] ?? 0;
$userGames = $_SESSION['games_played'] ?? 0;
?>

<header class="site-header" role="banner">
    <div class="header-container">
        <!-- Logo -->
        <a href="/" class="logo-section" aria-label="Click n' Pop Home">
            <div class="logo-badge">
                <i class="fas fa-bubbles"></i>
            </div>
            <div class="logo-text">
                <div class="logo-title">Click n' Pop</div>
                <div class="logo-subtitle">Bubble Adventure</div>
            </div>
        </a>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Navigation -->
        <nav class="main-nav" role="navigation">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="/" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/play" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'play.php' ? 'active' : '' ?>">
                        <i class="fas fa-gamepad"></i>
                        <span>Play Now</span>
                        <span class="nav-badge" aria-label="New game mode available"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/leaderboard" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'leaderboard.php' ? 'active' : '' ?>">
                        <i class="fas fa-trophy"></i>
                        <span>Leaderboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/profile" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/history" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : '' ?>">
                        <i class="fas fa-history"></i>
                        <span>History</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- User Section -->
        <div class="header-user-section">
            <!-- Search -->
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="search" class="search-input" placeholder="Search players..." aria-label="Search players">
            </div>

            <?php if ($isLoggedIn): ?>
                <!-- User Profile -->
                <div class="user-profile">
                    <div class="user-avatar" aria-label="<?= htmlspecialchars($userName) ?>'s profile">
                        <?= $userInitial ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($userName) ?></div>
                        <div class="user-level">
                            <i class="fas fa-star"></i>
                            <span class="level-badge">Level <?= $userLevel ?></span>
                        </div>
                    </div>

                    <!-- Dropdown Menu -->
                    <div class="user-dropdown">
                        <div class="dropdown-header">
                            <div class="dropdown-avatar"><?= $userInitial ?></div>
                            <div class="dropdown-user-info">
                                <h4><?= htmlspecialchars($userName) ?></h4>
                                <p>Bubble Popper</p>
                                <div class="dropdown-stats">
                                    <div class="stat-item">
                                        <span class="stat-value"><?= number_format($userScore) ?></span>
                                        <span class="stat-label">Score</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value"><?= number_format($userGames) ?></span>
                                        <span class="stat-label">Games</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dropdown-menu">
                            <a href="/profile" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>My Profile</span>
                            </a>
                            <a href="/settings" class="dropdown-item">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                            <a href="/dashboard" class="dropdown-item">
                                <i class="fas fa-chart-line"></i>
                                <span>Dashboard</span>
                            </a>
                            
                            <div class="dropdown-divider"></div>
                            
                            <a href="/logout" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Auth Buttons -->
                <div class="auth-buttons">
                    <a href="/auth/login" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                    <a href="/auth/register" class="btn-signup">
                        <i class="fas fa-user-plus"></i>
                        <span>Sign Up</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>