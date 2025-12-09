<?php
$title = "Click n' Pop | Pop Bubbles, Score High, Have Fun!";
$metaDescription = "Free online bubble popping game with power-ups, leaderboards, and daily challenges. Join thousands of players popping bubbles!";
$bodyClass = "home-page";
$pageScripts = ['/assets/js/home.js'];
ob_start();
?>

<!-- Hero Section -->
<section class="hero-section" aria-labelledby="main-heading">
    <div class="hero-content">
        <div class="hero-text">
            <h1 id="main-heading" class="hero-title">
                <span class="title-gradient">Pop & Conquer</span>
                <span class="title-sub">The Ultimate Bubble Adventure</span>
            </h1>
            <p class="hero-description">
                Experience the most satisfying bubble popping game ever created. 
                Challenge your reflexes, climb leaderboards, and unlock amazing power-ups.
            </p>
            <div class="hero-cta">
                <a href="/play" class="btn btn-primary btn-lg btn-icon">
                    <i class="fas fa-play-circle"></i>
                    Play Now Free
                </a>
                <a href="#features" class="btn btn-outline btn-lg">
                    <i class="fas fa-star"></i>
                    See Features
                </a>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number" data-count="125000">0</span>
                    <span class="stat-label">Active Players</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" data-count="9850000">0</span>
                    <span class="stat-label">Bubbles Popped</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" data-count="7500">0</span>
                    <span class="stat-label">High Scores</span>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <div class="floating-bubbles">
                <div class="bubble-float large"></div>
                <div class="bubble-float medium"></div>
                <div class="bubble-float small"></div>
            </div>
        </div>
    </div>
    
    <div class="scroll-indicator">
        <div class="mouse">
            <div class="wheel"></div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section id="features" class="features-section" aria-labelledby="features-heading">
    <div class="section-header">
        <h2 id="features-heading" class="section-title">
            <span class="title-decor">Why Players Love</span>
            Our Game
        </h2>
        <p class="section-subtitle">Everything you need for the ultimate popping experience</p>
    </div>

    <div class="features-grid">
        <!-- Feature 1 -->
        <div class="feature-card" data-aos="fade-up">
            <div class="feature-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <h3 class="feature-title">Lightning Fast Gameplay</h3>
            <p class="feature-description">
                Experience 60fps smooth popping action with instant feedback and satisfying visual effects.
            </p>
        </div>

        <!-- Feature 2 -->
        <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
            <div class="feature-icon">
                <i class="fas fa-trophy"></i>
            </div>
            <h3 class="feature-title">Competitive Leaderboards</h3>
            <p class="feature-description">
                Compete globally or with friends. Daily, weekly, and all-time rankings with rewards.
            </p>
        </div>

        <!-- Feature 3 -->
        <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
            <div class="feature-icon">
                <i class="fas fa-magic"></i>
            </div>
            <h3 class="feature-title">Epic Power-ups</h3>
            <p class="feature-description">
                Unlock special bubbles: Time Freeze, Score Multipliers, Chain Combos, and more!
            </p>
        </div>

        <!-- Feature 4 -->
        <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
            <div class="feature-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3 class="feature-title">Detailed Statistics</h3>
            <p class="feature-description">
                Track your progress with advanced analytics, heatmaps, and performance insights.
            </p>
        </div>

        <!-- Feature 5 -->
        <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
            <div class="feature-icon">
                <i class="fas fa-palette"></i>
            </div>
            <h3 class="feature-title">Customizable Experience</h3>
            <p class="feature-description">
                Choose from multiple themes, bubble skins, and sound packs to personalize your game.
            </p>
        </div>

        <!-- Feature 6 -->
        <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
            <div class="feature-icon">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="feature-title">Social Features</h3>
            <p class="feature-description">
                Challenge friends, share achievements, and join teams for collaborative gameplay.
            </p>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section" aria-labelledby="cta-heading">
    <div class="cta-container">
        <div class="cta-content">
            <h2 id="cta-heading" class="cta-title">Ready to Start Popping?</h2>
            <p class="cta-text">
                Join thousands of players worldwide. No download required - play instantly in your browser!
            </p>
            <div class="cta-buttons">
                <a href="/auth/register" class="btn btn-primary btn-xl btn-glow">
                    <i class="fas fa-user-plus"></i>
                    Sign Up Free
                </a>
                <a href="/play" class="btn btn-secondary btn-xl">
                    <i class="fas fa-gamepad"></i>
                    Play as Guest
                </a>
            </div>
            <div class="trust-badges">
                <div class="badge">
                    <i class="fas fa-shield-alt"></i>
                    <span>100% Safe & Secure</span>
                </div>
                <div class="badge">
                    <i class="fas fa-infinity"></i>
                    <span>No Ads Premium</span>
                </div>
                <div class="badge">
                    <i class="fas fa-globe"></i>
                    <span>Worldwide Players</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include "templates/layout.php";
?>