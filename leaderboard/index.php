<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$title = "Leaderboard - Click n' Pop | Top Players Rankings";
$metaDescription = "Check the Click n' Pop leaderboards! See top players, global rankings, daily challenges and compete for the highest bubble popping scores.";
$bodyClass = "leaderboard-page";
$pageScripts = ['/assets/js/leaderboard.js'];

// Initialiser la base de données
$db = Database::getInstance();
$connection = $db->getConnection();

// Déterminer si on utilise des données mock
$useMockData = Database::isUsingMockData();

// Récupérer les données
if ($connection && !$useMockData) {
    try {
        // Top 50 joueurs (30 derniers jours)
        $query = "
            SELECT 
                u.username,
                u.profile_pic,
                u.level,
                u.xp,
                u.country,
                MAX(g.score) as best_score,
                COUNT(g.id) as games_played,
                AVG(g.accuracy) as avg_accuracy,
                SUM(g.bubbles_popped) as total_bubbles,
                MAX(g.played_at) as last_played
            FROM users u
            LEFT JOIN games g ON u.id = g.user_id
            WHERE g.played_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
               OR g.id IS NULL
            GROUP BY u.id
            HAVING best_score IS NOT NULL
            ORDER BY best_score DESC
            LIMIT 50
        ";
        
        $topPlayers = $connection->query($query)->fetchAll();
        
        // Statistiques de l'utilisateur connecté
        $userStats = null;
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            
            // Rank de l'utilisateur
            $rankQuery = "
                SELECT COUNT(*) + 1 as user_rank
                FROM (
                    SELECT u.id, MAX(g.score) as max_score
                    FROM users u
                    LEFT JOIN games g ON u.id = g.user_id
                    GROUP BY u.id
                    HAVING max_score IS NOT NULL
                ) ranked
                WHERE max_score > (
                    SELECT MAX(score) 
                    FROM games 
                    WHERE user_id = ?
                )
            ";
            
            $rankStmt = $connection->prepare($rankQuery);
            $rankStmt->execute([$userId]);
            $userRank = $rankStmt->fetchColumn();
            
            // Stats détaillées
            $statsQuery = "
                SELECT 
                    u.username,
                    u.profile_pic,
                    u.level,
                    u.xp,
                    u.country,
                    u.created_at as join_date,
                    MAX(g.score) as best_score,
                    COUNT(g.id) as games_played,
                    AVG(g.accuracy) as avg_accuracy,
                    SUM(g.bubbles_popped) as total_bubbles,
                    MAX(g.played_at) as last_played
                FROM users u
                LEFT JOIN games g ON u.id = g.user_id
                WHERE u.id = ?
                GROUP BY u.id
            ";
            
            $statsStmt = $connection->prepare($statsQuery);
            $statsStmt->execute([$userId]);
            $userStats = $statsStmt->fetch();
            
            if ($userStats) {
                $userStats['user_rank'] = $userRank;
            }
        }
        
        // Statistiques globales
        $globalStats = [
            'total_players' => $connection->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'total_bubbles' => $connection->query("SELECT SUM(bubbles_popped) FROM games")->fetchColumn() ?: 0,
            'total_games' => $connection->query("SELECT COUNT(*) FROM games")->fetchColumn()
        ];
        
    } catch (Exception $e) {
        error_log("Leaderboard query error: " . $e->getMessage());
        $useMockData = true;
    }
}

// Fallback aux données mock si nécessaire
if ($useMockData || empty($topPlayers)) {
    $topPlayers = getMockPlayers(50);
    $userStats = getMockUserStats();
    $globalStats = getLeaderboardStats();
}

// Marquer l'utilisateur courant dans la liste
if (isset($_SESSION['username']) && $topPlayers) {
    foreach ($topPlayers as &$player) {
        $player['is_current_user'] = ($player['username'] === $_SESSION['username']);
    }
    unset($player);
}

ob_start();
?>
<link rel="stylesheet" href="../assets/css/leaderboard.css">
<!-- Leaderboard Hero -->
<section class="leaderboard-hero" aria-labelledby="leaderboard-title">
    <div class="hero-content">
        <h1 id="leaderboard-title" class="hero-title">
            <span class="title-gradient">Global Leaderboard</span>
            <span class="title-sub">Who's the Best Bubble Popper?</span>
        </h1>
        <p class="hero-description">
            Compete with players worldwide. Every pop counts! Can you reach the top?
        </p>
        
        <?php if ($useMockData): ?>
        <div class="demo-notice">
            <i class="fas fa-info-circle"></i>
            <span>Demo Mode: Showing sample data. Connect to database for real rankings.</span>
        </div>
        <?php endif; ?>
        
        <?php if ($userStats): ?>
        <div class="user-rank-card">
            <div class="rank-badge">
                <span class="rank-number">#<?= $userStats['user_rank'] ?? '?' ?></span>
                <span class="rank-label">Your Rank</span>
            </div>
            <div class="user-stats">
                <div class="stat">
                    <span class="stat-value"><?= number_format($userStats['best_score'] ?? 0) ?></span>
                    <span class="stat-label">Best Score</span>
                </div>
                <div class="stat">
                    <span class="stat-value"><?= number_format($userStats['games_played'] ?? 0) ?></span>
                    <span class="stat-label">Games</span>
                </div>
                <div class="stat">
                    <span class="stat-value"><?= number_format($userStats['avg_accuracy'] ?? 0, 1) ?>%</span>
                    <span class="stat-label">Accuracy</span>
                </div>
            </div>
            <a href="/play" class="btn-improve-rank">
                <i class="fas fa-gamepad"></i>
                Improve Rank
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="hero-visual">
        <div class="trophy-animation">
            <i class="fas fa-trophy"></i>
        </div>
        <div class="floating-bubbles">
            <?php for ($i = 0; $i < 15; $i++): ?>
            <div class="floating-bubble" style="
                --size: <?= rand(20, 60) ?>px;
                --x: <?= rand(0, 100) ?>%;
                --delay: <?= rand(0, 3000) ?>ms;
                --duration: <?= rand(3000, 8000) ?>ms;
            "></div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- Leaderboard Content -->
<div class="leaderboard-container">
    <!-- Leaderboard Filters -->
    <div class="leaderboard-filters">
        <div class="filter-group">
            <button class="filter-btn active" data-filter="global">
                <i class="fas fa-globe"></i>
                Global
            </button>
            <button class="filter-btn" data-filter="daily">
                <i class="fas fa-calendar-day"></i>
                Daily
            </button>
            <button class="filter-btn" data-filter="weekly">
                <i class="fas fa-calendar-week"></i>
                Weekly
            </button>
            <button class="filter-btn" data-filter="monthly">
                <i class="fas fa-calendar-alt"></i>
                Monthly
            </button>
            <button class="filter-btn" data-filter="friends">
                <i class="fas fa-user-friends"></i>
                Friends
            </button>
        </div>
        
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="search" id="player-search" placeholder="Search player..." aria-label="Search players">
            <button class="search-clear" aria-label="Clear search">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="leaderboard-table-container">
        <div class="table-header">
            <h2>
                <i class="fas fa-crown"></i>
                Top Players
                <span class="players-count">(<?= count($topPlayers) ?> players)</span>
            </h2>
            <div class="table-actions">
                <button class="btn-refresh" aria-label="Refresh leaderboard" title="Refresh">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <div class="last-updated">
                    <i class="far fa-clock"></i>
                    Updated: <span id="last-updated">Just now</span>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="leaderboard-table" aria-label="Player rankings">
                <thead>
                    <tr>
                        <th scope="col" class="rank-col">Rank</th>
                        <th scope="col" class="player-col">Player</th>
                        <th scope="col" class="score-col">Best Score</th>
                        <th scope="col" class="level-col">Level</th>
                        <th scope="col" class="games-col">Games</th>
                        <th scope="col" class="accuracy-col">Accuracy</th>
                        <th scope="col" class="bubbles-col">Bubbles</th>
                        <th scope="col" class="action-col">Actions</th>
                    </tr>
                </thead>
                <tbody id="leaderboard-body">
                    <?php foreach ($topPlayers as $index => $player): ?>
                    <?php 
                    $rank = $index + 1;
                    $isCurrentUser = $player['is_current_user'] ?? false;
                    $rowClass = $isCurrentUser ? 'current-user' : '';
                    $rowClass .= $rank <= 3 ? ' top-player' : '';
                    ?>
                    <tr class="<?= $rowClass ?>" data-rank="<?= $rank ?>" data-username="<?= htmlspecialchars($player['username']) ?>">
                        <td class="rank-cell">
                            <div class="rank-indicator">
                                <?php if ($rank <= 3): ?>
                                <div class="medal medal-<?= $rank ?>">
                                    <i class="fas fa-medal"></i>
                                </div>
                                <?php endif; ?>
                                <span class="rank-number"><?= $rank ?></span>
                            </div>
                        </td>
                        
                        <td class="player-cell">
                            <div class="player-info">
                                <div class="player-avatar">
                                    <?php if (!empty($player['profile_pic'])): ?>
                                    <img src="/assets/images/profiles/<?= htmlspecialchars($player['profile_pic']) ?>" 
                                         alt="<?= htmlspecialchars($player['username']) ?>"
                                         onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\"avatar-placeholder\">" . strtoupper(substr($player['username'], 0, 1)) . "</div>'">
                                    <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?= strtoupper(substr($player['username'], 0, 1)) ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($player['country'])): ?>
                                    <span class="country-flag" title="<?= htmlspecialchars($player['country']) ?>">
                                        <?= $player['country'] ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <div class="player-details">
                                    <div class="player-name">
                                        <?= htmlspecialchars($player['username']) ?>
                                        <?php if ($isCurrentUser): ?>
                                        <span class="you-badge" title="This is you">
                                            <i class="fas fa-user"></i> You
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="player-tagline">
                                        <i class="fas fa-star"></i>
                                        <?= getPlayerTitle($player['level']) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="score-cell">
                            <div class="score-value">
                                <?= number_format($player['best_score'] ?? 0) ?>
                                <?php if ($rank === 1): ?>
                                <span class="top-score-badge">
                                    <i class="fas fa-crown"></i>
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        
                        <td class="level-cell">
                            <div class="level-display">
                                <div class="level-progress">
                                    <div class="level-bar" style="width: <?= ($player['xp'] ?? 0) % 100 ?>%"></div>
                                </div>
                                <span class="level-text">Lv. <?= $player['level'] ?? 1 ?></span>
                            </div>
                        </td>
                        
                        <td class="games-cell">
                            <div class="games-count">
                                <i class="fas fa-play-circle"></i>
                                <?= number_format($player['games_played'] ?? 0) ?>
                            </div>
                        </td>
                        
                        <td class="accuracy-cell">
                            <div class="accuracy-display">
                                <div class="accuracy-bar">
                                    <div class="accuracy-fill" 
                                         style="width: <?= min(100, ($player['avg_accuracy'] ?? 0)) ?>%"></div>
                                    <span class="accuracy-text"><?= number_format($player['avg_accuracy'] ?? 0, 1) ?>%</span>
                                </div>
                                <div class="accuracy-rating">
                                    <?= getAccuracyRating($player['avg_accuracy'] ?? 0) ?>
                                </div>
                            </div>
                        </td>
                        
                        <td class="bubbles-cell">
                            <div class="bubbles-count">
                                <i class="fas fa-bubbles"></i>
                                <?= number_format($player['total_bubbles'] ?? 0) ?>
                            </div>
                        </td>
                        
                        <td class="action-cell">
                            <div class="action-buttons">
                                <button class="btn-view-profile" 
                                        data-user="<?= htmlspecialchars($player['username']) ?>"
                                        title="View profile">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if (!$isCurrentUser && isset($_SESSION['user_id'])): ?>
                                <button class="btn-add-friend" 
                                        data-user="<?= htmlspecialchars($player['username']) ?>"
                                        title="Add friend">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                                <button class="btn-challenge" 
                                        data-user="<?= htmlspecialchars($player['username']) ?>"
                                        title="Send challenge">
                                    <i class="fas fa-gamepad"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="pagination">
            <button class="page-btn prev" disabled aria-label="Previous page">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="page-numbers">
                <button class="page-number active">1</button>
                <button class="page-number">2</button>
                <button class="page-number">3</button>
                <span class="page-dots">...</span>
                <button class="page-number">10</button>
            </div>
            <button class="page-btn next" aria-label="Next page">
                <i class="fas fa-chevron-right"></i>
            </button>
            <div class="page-info">
                Showing 1-50 of <?= number_format($globalStats['total_players'] ?? count($topPlayers)) ?> players
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>Total Players</h3>
                <div class="stat-value"><?= number_format($globalStats['total_players'] ?? count($topPlayers)) ?></div>
                <div class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i>
                    4.2% this week
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-bubbles"></i>
            </div>
            <div class="stat-content">
                <h3>Total Bubbles Popped</h3>
                <div class="stat-value"><?= number_format($globalStats['total_bubbles'] ?? 0) ?></div>
                <div class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i>
                    12.5% today
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-content">
                <h3>Top Score This Month</h3>
                <div class="stat-value"><?= number_format($topPlayers[0]['best_score'] ?? 0) ?></div>
                <div class="stat-player">by <?= htmlspecialchars($topPlayers[0]['username'] ?? 'No players') ?></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3>Average Play Time</h3>
                <div class="stat-value"><?= number_format($globalStats['avg_play_time'] ?? 24.3, 1) ?> min</div>
                <div class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i>
                    +2.1 min
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3>
            <i class="fas fa-bolt"></i>
            Quick Actions
        </h3>
        <div class="action-buttons">
            <a href="/play" class="btn-action primary">
                <i class="fas fa-play"></i>
                Play Now
            </a>
            <a href="/profile" class="btn-action secondary">
                <i class="fas fa-user"></i>
                Your Profile
            </a>
            <a href="/history" class="btn-action tertiary">
                <i class="fas fa-history"></i>
                Game History
            </a>
            <button class="btn-action outline" id="share-leaderboard">
                <i class="fas fa-share-alt"></i>
                Share
            </button>
        </div>
    </div>
</div>

<!-- Player Profile Modal -->
<div class="modal" id="profile-modal" aria-hidden="true" role="dialog">
    <div class="modal-overlay" data-close-modal></div>
    <div class="modal-content">
        <button class="modal-close" aria-label="Close modal" data-close-modal>
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-body" id="modal-profile-content">
            <!-- Chargé via AJAX -->
        </div>
    </div>
</div>

<!-- Notification Toast -->
<div class="toast-container" id="toast-container"></div>

<?php
$content = ob_get_clean();

// Fonctions helper
function getPlayerTitle($level) {
    if ($level >= 90) return 'Bubble Legend';
    if ($level >= 75) return 'Pop Demigod';
    if ($level >= 60) return 'Master Popper';
    if ($level >= 45) return 'Bubble Expert';
    if ($level >= 30) return 'Skilled Popper';
    if ($level >= 20) return 'Rising Star';
    if ($level >= 10) return 'Enthusiast';
    return 'New Player';
}

function getAccuracyRating($accuracy) {
    if ($accuracy >= 95) return '<span class="rating excellent">SS</span>';
    if ($accuracy >= 90) return '<span class="rating great">S</span>';
    if ($accuracy >= 85) return '<span class="rating good">A</span>';
    if ($accuracy >= 80) return '<span class="rating average">B</span>';
    if ($accuracy >= 75) return '<span class="rating fair">C</span>';
    return '<span class="rating poor">D</span>';
}

include "../templates/layout.php";
?>