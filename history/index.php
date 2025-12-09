<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/exceptions.php';

// Vérifier la connexion
requireLogin('/auth');

try {
    $user = currentUser();
    if (!$user) {
        throw new NotFoundException("Utilisateur non trouvé");
    }
    
    // Récupérer la page courante
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 20;
    
    // Récupérer l'historique
    $gameHistory = auth()->getGameHistory($user['id'], $limit, $page);
    $games = $gameHistory['games'];
    $pagination = $gameHistory['pagination'];
    $stats = $gameHistory['stats'];
    
} catch (AppException $e) {
    ExceptionHandler::handle($e);
}

// Définir la page
$title = "Historique - " . htmlspecialchars($user['username']) . " - Click n' Pop";
$metaDescription = "Historique des parties de " . htmlspecialchars($user['username']) . " sur Click n' Pop. {$stats['total_games']} parties jouées, {$stats['total_bubbles']} bulles éclatées.";
$bodyClass = "history-page";
$pageScripts = ['/assets/js/history.js'];

ob_start();
?>

<div class="history-container">
    <!-- En-tête -->
    <div class="history-header">
        <h1>
            <i class="fas fa-history"></i>
            Historique des parties
        </h1>
        <p class="subtitle">
            Retrouvez toutes vos parties et suivez votre progression
        </p>
        
        <!-- Statistiques rapides -->
        <div class="quick-stats">
            <div class="quick-stat">
                <div class="stat-icon">
                    <i class="fas fa-gamepad"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= number_format($stats['total_games'] ?? 0) ?></div>
                    <div class="stat-label">Parties jouées</div>
                </div>
            </div>
            <div class="quick-stat">
                <div class="stat-icon">
                    <i class="fas fa-bubbles"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= number_format($stats['total_bubbles'] ?? 0) ?></div>
                    <div class="stat-label">Bulles éclatées</div>
                </div>
            </div>
            <div class="quick-stat">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $stats['total_play_time_formatted'] ?? '0h 0m' ?></div>
                    <div class="stat-label">Temps total</div>
                </div>
            </div>
            <div class="quick-stat">
                <div class="stat-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= number_format($stats['best_score'] ?? 0) ?></div>
                    <div class="stat-label">Meilleur score</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="history-filters">
        <div class="filter-group">
            <button class="filter-btn active" data-filter="all">
                <i class="fas fa-list"></i>
                Toutes
            </button>
            <button class="filter-btn" data-filter="today">
                <i class="fas fa-calendar-day"></i>
                Aujourd'hui
            </button>
            <button class="filter-btn" data-filter="week">
                <i class="fas fa-calendar-week"></i>
                Cette semaine
            </button>
            <button class="filter-btn" data-filter="month">
                <i class="fas fa-calendar-alt"></i>
                Ce mois
            </button>
        </div>
        
        <div class="search-sort">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="search" placeholder="Rechercher une partie..." id="game-search">
            </div>
            
            <div class="sort-dropdown">
                <select id="sort-games">
                    <option value="newest">Plus récentes</option>
                    <option value="oldest">Plus anciennes</option>
                    <option value="highest">Score (haut)</option>
                    <option value="lowest">Score (bas)</option>
                    <option value="accuracy">Précision</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Liste des parties -->
    <div class="games-list">
        <?php if (empty($games)): ?>
        <div class="empty-state large">
            <div class="empty-icon">
                <i class="fas fa-gamepad"></i>
            </div>
            <h2>Aucune partie jouée</h2>
            <p>Commencez à jouer pour remplir votre historique !</p>
            <a href="/play" class="btn btn-primary btn-lg">
                <i class="fas fa-play"></i>
                Jouer maintenant
            </a>
            <a href="/leaderboard" class="btn btn-outline">
                <i class="fas fa-crown"></i>
                Voir le classement
            </a>
        </div>
        <?php else: ?>
            <?php foreach ($games as $game): ?>
            <div class="game-card detailed" data-game-id="<?= $game['id'] ?>">
                <div class="game-card-header">
                    <div class="game-id">
                        <span class="label">Partie #<?= $game['id'] ?></span>
                    </div>
                    <div class="game-date">
                        <i class="far fa-calendar"></i>
                        <?= htmlspecialchars($game['formatted_date']) ?>
                        <?php if ($game['days_ago'] == 0): ?>
                        <span class="badge new">Aujourd'hui</span>
                        <?php elseif ($game['days_ago'] == 1): ?>
                        <span class="badge recent">Hier</span>
                        <?php elseif ($game['days_ago'] <= 7): ?>
                        <span class="badge recent">Il y a <?= $game['days_ago'] ?> jours</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="game-card-body">
                    <div class="game-main-stats">
                        <div class="main-stat score">
                            <div class="stat-label">Score</div>
                            <div class="stat-value">
                                <?= number_format($game['score']) ?>
                                <?php if ($game['score'] == $stats['best_score']): ?>
                                <span class="badge best">Record !</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="game-secondary-stats">
                            <div class="stat">
                                <div class="stat-label">Précision</div>
                                <div class="stat-value">
                                    <?= number_format($game['accuracy'], 1) ?>%
                                    <div class="accuracy-bar small">
                                        <div class="accuracy-fill" style="width: <?= min(100, $game['accuracy']) ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="stat">
                                <div class="stat-label">Bulles</div>
                                <div class="stat-value"><?= number_format($game['bubbles_popped']) ?></div>
                            </div>
                            <div class="stat">
                                <div class="stat-label">Durée</div>
                                <div class="stat-value"><?= gmdate('i:s', $game['duration']) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="game-meta">
                        <div class="difficulty-info">
                            <span class="difficulty-badge <?= $game['difficulty'] ?>">
                                <i class="fas fa-signal"></i>
                                <?= ucfirst($game['difficulty']) ?>
                            </span>
                        </div>
                        
                        <div class="performance-rating">
                            <?php 
                            $performance = $game['score'] * ($game['accuracy'] / 100);
                            if ($performance > 15000): ?>
                            <span class="rating excellent">Performance exceptionnelle</span>
                            <?php elseif ($performance > 10000): ?>
                            <span class="rating good">Bonne performance</span>
                            <?php elseif ($performance > 5000): ?>
                            <span class="rating average">Performance moyenne</span>
                            <?php else: ?>
                            <span class="rating fair">À améliorer</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="game-card-actions">
                    <button class="btn-action share" onclick="shareGame(<?= $game['id'] ?>)">
                        <i class="fas fa-share"></i>
                        Partager
                    </button>
                    <button class="btn-action analyze" onclick="analyzeGame(<?= $game['id'] ?>)">
                        <i class="fas fa-chart-line"></i>
                        Analyser
                    </button>
                    <button class="btn-action replay" onclick="replayGame(<?= $game['id'] ?>)">
                        <i class="fas fa-redo"></i>
                        Rejouer
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?page=1" class="page-btn first">
            <i class="fas fa-angle-double-left"></i>
        </a>
        <a href="?page=<?= $page - 1 ?>" class="page-btn prev">
            <i class="fas fa-angle-left"></i>
        </a>
        <?php endif; ?>
        
        <div class="page-numbers">
            <?php
            $start = max(1, $page - 2);
            $end = min($pagination['total_pages'], $page + 2);
            
            if ($start > 1) echo '<span class="page-dots">...</span>';
            
            for ($i = $start; $i <= $end; $i++):
            ?>
            <a href="?page=<?= $i ?>" class="page-number <?= $i == $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
            
            if ($end < $pagination['total_pages']) echo '<span class="page-dots">...</span>';
            ?>
        </div>
        
        <?php if ($page < $pagination['total_pages']): ?>
        <a href="?page=<?= $page + 1 ?>" class="page-btn next">
            <i class="fas fa-angle-right"></i>
        </a>
        <a href="?page=<?= $pagination['total_pages'] ?>" class="page-btn last">
            <i class="fas fa-angle-double-right"></i>
        </a>
        <?php endif; ?>
        
        <div class="page-info">
            Page <?= $page ?> sur <?= $pagination['total_pages'] ?>
            (<?= $pagination['total_games'] ?> parties au total)
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal d'analyse de partie -->
<div class="modal" id="analysis-modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3>Analyse de partie</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="analysis-content">
                <!-- Chargé via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtres
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filtrer les parties
            const filter = this.dataset.filter;
            filterGames(filter);
        });
    });
    
    // Recherche
    const searchInput = document.getElementById('game-search');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterGames('search', searchTerm);
    });
    
    // Tri
    document.getElementById('sort-games').addEventListener('change', function() {
        sortGames(this.value);
    });
    
    // Modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.querySelector('.modal-close').addEventListener('click', () => {
            modal.classList.remove('active');
        });
    });
});

function filterGames(filter, searchTerm = '') {
    const games = document.querySelectorAll('.game-card');
    
    games.forEach(game => {
        let show = true;
        
        if (filter === 'search' && searchTerm) {
            const gameText = game.textContent.toLowerCase();
            show = gameText.includes(searchTerm);
        } else if (filter !== 'all') {
            // Implémentation simplifiée
            // Dans une vraie app, on ferait une requête AJAX
            show = true;
        }
        
        game.style.display = show ? '' : 'none';
    });
}

function sortGames(sortBy) {
    // Dans une vraie application, on ferait une requête AJAX avec le tri
    console.log('Sorting by:', sortBy);
    // Réorganiser les cartes selon le critère
}

function shareGame(gameId) {
    const url = `${window.location.origin}/game/${gameId}`;
    const text = `Regardez ma partie sur Click n' Pop ! #${gameId}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Click n\' Pop - Partie partagée',
            text: text,
            url: url
        });
    } else {
        // Fallback: copier le lien
        navigator.clipboard.writeText(url).then(() => {
            alert('Lien copié dans le presse-papier !');
        });
    }
}

async function analyzeGame(gameId) {
    try {
        const response = await fetch(`/api/game/${gameId}/analysis`);
        const analysis = await response.json();
        
        // Afficher l'analyse dans la modal
        const modal = document.getElementById('analysis-modal');
        const content = document.getElementById('analysis-content');
        
        content.innerHTML = `
            <div class="analysis-content">
                <div class="analysis-header">
                    <h4>Partie #${gameId}</h4>
                    <div class="analysis-score">
                        Score: <strong>${analysis.score.toLocaleString()}</strong>
                    </div>
                </div>
                
                <div class="analysis-grid">
                    <div class="analysis-card">
                        <h5>Points forts</h5>
                        <ul>
                            ${analysis.strengths.map(s => `<li>${s}</li>`).join('')}
                        </ul>
                    </div>
                    
                    <div class="analysis-card">
                        <h5>À améliorer</h5>
                        <ul>
                            ${analysis.improvements.map(i => `<li>${i}</li>`).join('')}
                        </ul>
                    </div>
                    
                    <div class="analysis-card">
                        <h5>Conseils</h5>
                        <ul>
                            ${analysis.tips.map(t => `<li>${t}</li>`).join('')}
                        </ul>
                    </div>
                </div>
                
                <div class="analysis-chart">
                    <h5>Évolution du score</h5>
                    <!-- Graphique serait ici -->
                    <div class="chart-placeholder">
                        <i class="fas fa-chart-line"></i>
                        <p>Graphique d'évolution</p>
                    </div>
                </div>
            </div>
        `;
        
        modal.classList.add('active');
        
    } catch (error) {
        console.error('Error:', error);
        alert('Impossible d\'analyser cette partie pour le moment');
    }
}

function replayGame(gameId) {
    // Rediriger vers la page de jeu avec les mêmes paramètres
    alert(`Rejouer la partie #${gameId} - Fonctionnalité à venir !`);
    // window.location.href = `/play?replay=${gameId}`;
}
</script>

<?php
$content = ob_get_clean();
include "../templates/layout.php";
?>