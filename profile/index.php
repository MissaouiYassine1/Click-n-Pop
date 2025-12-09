<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/exceptions.php';

// Vérifier la connexion
requireLogin('/auth');

// Récupérer l'utilisateur
try {
    $user = currentUser();
    if (!$user) {
        throw new NotFoundException("Utilisateur non trouvé");
    }
    
    // Récupérer les statistiques
    $gameHistory = auth()->getGameHistory($user['id'], 10, 1);
    $stats = $gameHistory['stats'];
    
    // Récupérer les réalisations
    $achievements = $this->getUserAchievements($user['id']);
    
} catch (AppException $e) {
    ExceptionHandler::handle($e);
}

// Définir la page
$title = "Profil - " . htmlspecialchars($user['username']) . " - Click n' Pop";
$metaDescription = "Profil de " . htmlspecialchars($user['username']) . " sur Click n' Pop. Niveau {$user['level']}, score record: {$stats['best_score']} points.";
$bodyClass = "profile-page";
$pageScripts = ['/assets/js/profile.js'];

ob_start();
?>

<div class="profile-container">
    <!-- En-tête du profil -->
    <div class="profile-header">
        <div class="profile-cover">
            <div class="cover-gradient"></div>
            <div class="cover-content">
                <div class="profile-avatar-large">
                    <?php if (!empty($user['profile_pic'])): ?>
                    <img src="<?= htmlspecialchars($user['avatar_url']) ?>" 
                         alt="<?= htmlspecialchars($user['username']) ?>">
                    <?php else: ?>
                    <div class="avatar-large" style="background-color: <?= $user['avatar_color'] ?>">
                        <?= strtoupper(substr($user['username'], 0, 2)) ?>
                    </div>
                    <?php endif; ?>
                    <button class="btn-change-avatar" data-modal="avatar-modal">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                
                <div class="profile-info">
                    <h1 class="profile-username">
                        <?= htmlspecialchars($user['username']) ?>
                        <span class="verified-badge" title="Compte vérifié">
                            <i class="fas fa-check-circle"></i>
                        </span>
                    </h1>
                    
                    <div class="profile-meta">
                        <span class="meta-item">
                            <i class="fas fa-envelope"></i>
                            <?= htmlspecialchars($user['email']) ?>
                        </span>
                        <?php if (!empty($user['country'])): ?>
                        <span class="meta-item">
                            <i class="fas fa-globe"></i>
                            <?= htmlspecialchars($user['country']) ?>
                        </span>
                        <?php endif; ?>
                        <span class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            Membre depuis <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                        </span>
                    </div>
                    
                    <div class="profile-actions">
                        <a href="/profile/edit.php" class="btn btn-outline">
                            <i class="fas fa-edit"></i>
                            Éditer le profil
                        </a>
                        <a href="/settings" class="btn btn-outline">
                            <i class="fas fa-cog"></i>
                            Paramètres
                        </a>
                        <a href="/play" class="btn btn-primary">
                            <i class="fas fa-play"></i>
                            Jouer maintenant
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="profile-stats-grid">
        <div class="stat-card">
            <div class="stat-icon level">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-content">
                <h3>Niveau</h3>
                <div class="stat-value"><?= $user['level'] ?></div>
                <div class="stat-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $user['xp_progress'] ?>%"></div>
                    </div>
                    <div class="progress-text">
                        <?= $user['xp_current_level'] ?> / 1000 XP
                    </div>
                </div>
                <div class="stat-hint">
                    <?= $user['xp'] ?> XP total
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon score">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-content">
                <h3>Meilleur score</h3>
                <div class="stat-value"><?= number_format($stats['best_score'] ?? 0) ?></div>
                <div class="stat-hint">
                    <?= $stats['total_games'] ?? 0 ?> parties jouées
                </div>
                <div class="stat-rank">
                    <i class="fas fa-crown"></i>
                    Classé #<?= rand(1, 1000) ?>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon accuracy">
                <i class="fas fa-bullseye"></i>
            </div>
            <div class="stat-content">
                <h3>Précision</h3>
                <div class="stat-value"><?= number_format($stats['avg_accuracy'] ?? 0, 1) ?>%</div>
                <div class="stat-hint">
                    Moyenne sur <?= $stats['total_games'] ?? 0 ?> parties
                </div>
                <div class="accuracy-rating">
                    <?php 
                    $accuracy = $stats['avg_accuracy'] ?? 0;
                    if ($accuracy >= 95) echo '<span class="rating excellent">SS</span>';
                    elseif ($accuracy >= 90) echo '<span class="rating great">S</span>';
                    elseif ($accuracy >= 85) echo '<span class="rating good">A</span>';
                    elseif ($accuracy >= 80) echo '<span class="rating average">B</span>';
                    elseif ($accuracy >= 75) echo '<span class="rating fair">C</span>';
                    else echo '<span class="rating poor">D</span>';
                    ?>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon time">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3>Temps de jeu</h3>
                <div class="stat-value"><?= $stats['total_play_time_formatted'] ?? '0h 0m' ?></div>
                <div class="stat-hint">
                    <?= number_format($stats['total_bubbles'] ?? 0) ?> bulles éclatées
                </div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-up"></i>
                    <?= number_format($stats['avg_play_time'] ?? 0, 1) ?> min/moyenne
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="profile-content">
        <!-- Onglets -->
        <div class="profile-tabs">
            <button class="tab active" data-tab="history">
                <i class="fas fa-history"></i>
                Historique
            </button>
            <button class="tab" data-tab="achievements">
                <i class="fas fa-medal"></i>
                Réalisations
                <span class="badge"><?= count($achievements ?? []) ?></span>
            </button>
            <button class="tab" data-tab="friends">
                <i class="fas fa-users"></i>
                Amis
                <span class="badge"><?= rand(5, 50) ?></span>
            </button>
            <button class="tab" data-tab="stats">
                <i class="fas fa-chart-bar"></i>
                Statistiques
            </button>
        </div>

        <!-- Contenu des onglets -->
        <div class="tab-content">
            <!-- Historique -->
            <div class="tab-pane active" id="history">
                <div class="section-header">
                    <h2>
                        <i class="fas fa-history"></i>
                        Dernières parties
                    </h2>
                    <a href="/history" class="btn-link">
                        Voir tout l'historique
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <?php if (empty($gameHistory['games'])): ?>
                <div class="empty-state">
                    <i class="fas fa-gamepad"></i>
                    <h3>Aucune partie jouée</h3>
                    <p>Commencez à jouer pour apparaître ici !</p>
                    <a href="/play" class="btn btn-primary">
                        <i class="fas fa-play"></i>
                        Jouer maintenant
                    </a>
                </div>
                <?php else: ?>
                <div class="game-history">
                    <?php foreach ($gameHistory['games'] as $game): ?>
                    <div class="game-card">
                        <div class="game-header">
                            <div class="game-date">
                                <i class="far fa-calendar"></i>
                                <?= htmlspecialchars($game['formatted_date']) ?>
                                <?php if ($game['days_ago'] == 0): ?>
                                <span class="badge new">Aujourd'hui</span>
                                <?php elseif ($game['days_ago'] == 1): ?>
                                <span class="badge recent">Hier</span>
                                <?php endif; ?>
                            </div>
                            <div class="game-difficulty">
                                <span class="difficulty-badge <?= $game['difficulty'] ?>">
                                    <?= ucfirst($game['difficulty']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="game-stats">
                            <div class="stat">
                                <div class="stat-label">Score</div>
                                <div class="stat-value"><?= number_format($game['score']) ?></div>
                            </div>
                            <div class="stat">
                                <div class="stat-label">Précision</div>
                                <div class="stat-value"><?= number_format($game['accuracy'], 1) ?>%</div>
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
                        
                        <div class="game-actions">
                            <button class="btn btn-sm btn-outline" onclick="shareGame(<?= $game['id'] ?>)">
                                <i class="fas fa-share"></i>
                                Partager
                            </button>
                            <button class="btn btn-sm btn-outline" onclick="viewGameDetails(<?= $game['id'] ?>)">
                                <i class="fas fa-info-circle"></i>
                                Détails
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Réalisations -->
            <div class="tab-pane" id="achievements">
                <div class="section-header">
                    <h2>
                        <i class="fas fa-medal"></i>
                        Réalisations débloquées
                    </h2>
                    <div class="achievement-progress">
                        <span><?= count($achievements ?? []) ?> / 24 débloquées</span>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= (count($achievements ?? []) / 24) * 100 ?>%"></div>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($achievements)): ?>
                <div class="empty-state">
                    <i class="fas fa-medal"></i>
                    <h3>Aucune réalisation</h3>
                    <p>Jouez pour débloquer des réalisations !</p>
                </div>
                <?php else: ?>
                <div class="achievements-grid">
                    <?php foreach ($achievements as $achievement): ?>
                    <div class="achievement-card <?= $achievement['rarity'] ?>">
                        <div class="achievement-icon">
                            <i class="fas fa-<?= $achievement['icon'] ?>"></i>
                        </div>
                        <div class="achievement-content">
                            <h4><?= htmlspecialchars($achievement['name']) ?></h4>
                            <p><?= htmlspecialchars($achievement['description']) ?></p>
                            <div class="achievement-meta">
                                <span class="xp-reward">
                                    <i class="fas fa-star"></i>
                                    +<?= $achievement['xp_reward'] ?> XP
                                </span>
                                <span class="unlocked-date">
                                    <i class="far fa-calendar"></i>
                                    <?= date('d/m/Y', strtotime($achievement['unlocked_at'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Amis (placeholder) -->
            <div class="tab-pane" id="friends">
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>Système d'amis à venir</h3>
                    <p>Cette fonctionnalité sera bientôt disponible !</p>
                </div>
            </div>

            <!-- Statistiques (placeholder) -->
            <div class="tab-pane" id="stats">
                <div class="empty-state">
                    <i class="fas fa-chart-bar"></i>
                    <h3>Statistiques détaillées</h3>
                    <p>En cours de développement...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Barre latérale -->
    <div class="profile-sidebar">
        <!-- Prochain niveau -->
        <div class="sidebar-card">
            <h3>
                <i class="fas fa-arrow-up"></i>
                Prochain niveau
            </h3>
            <div class="level-progress-card">
                <div class="level-info">
                    <div class="current-level">Niveau <?= $user['level'] ?></div>
                    <div class="next-level">Niveau <?= $user['level'] + 1 ?></div>
                </div>
                <div class="progress-bar large">
                    <div class="progress-fill" style="width: <?= $user['xp_progress'] ?>%"></div>
                </div>
                <div class="xp-needed">
                    <?= 1000 - $user['xp_current_level'] ?> XP nécessaires
                </div>
            </div>
        </div>

        <!-- Tendances -->
        <div class="sidebar-card">
            <h3>
                <i class="fas fa-chart-line"></i>
                Tendances
            </h3>
            <div class="trends-list">
                <div class="trend-item positive">
                    <div class="trend-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="trend-content">
                        <div class="trend-label">Score moyen</div>
                        <div class="trend-value">+12.5% cette semaine</div>
                    </div>
                </div>
                <div class="trend-item positive">
                    <div class="trend-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div class="trend-content">
                        <div class="trend-label">Précision</div>
                        <div class="trend-value">+3.2% ce mois</div>
                    </div>
                </div>
                <div class="trend-item negative">
                    <div class="trend-icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="trend-content">
                        <div class="trend-label">Temps de jeu</div>
                        <div class="trend-value">-5% aujourd'hui</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classement personnel -->
        <div class="sidebar-card">
            <h3>
                <i class="fas fa-crown"></i>
                Classement
            </h3>
            <div class="ranking-card">
                <div class="global-rank">
                    <div class="rank-label">Global</div>
                    <div class="rank-value">#<?= rand(1, 1000) ?></div>
                </div>
                <div class="monthly-rank">
                    <div class="rank-label">Ce mois</div>
                    <div class="rank-value">#<?= rand(1, 500) ?></div>
                </div>
                <div class="daily-rank">
                    <div class="rank-label">Aujourd'hui</div>
                    <div class="rank-value">#<?= rand(1, 200) ?></div>
                </div>
            </div>
            <a href="/leaderboard" class="btn-link">
                Voir le classement complet
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <!-- Partage du profil -->
        <div class="sidebar-card">
            <h3>
                <i class="fas fa-share-alt"></i>
                Partage
            </h3>
            <p>Partagez votre profil avec vos amis !</p>
            <div class="share-buttons">
                <button class="btn-share twitter" onclick="shareProfile('twitter')">
                    <i class="fab fa-twitter"></i>
                </button>
                <button class="btn-share facebook" onclick="shareProfile('facebook')">
                    <i class="fab fa-facebook"></i>
                </button>
                <button class="btn-share link" onclick="copyProfileLink()">
                    <i class="fas fa-link"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de changement d'avatar -->
<div class="modal" id="avatar-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Changer votre avatar</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="avatar-form" enctype="multipart/form-data">
                <div class="upload-area">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Glissez-déposez une image ou cliquez pour sélectionner</p>
                    <input type="file" id="avatar-input" accept="image/*" hidden>
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('avatar-input').click()">
                        Choisir une image
                    </button>
                    <div class="file-info"></div>
                </div>
                
                <div class="avatar-options">
                    <h4>Ou choisissez une couleur :</h4>
                    <div class="color-options">
                        <?php 
                        $colors = ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4'];
                        foreach ($colors as $color): 
                        ?>
                        <label class="color-option <?= $color == $user['avatar_color'] ? 'selected' : '' ?>">
                            <input type="radio" name="avatar_color" value="<?= $color ?>" 
                                   <?= $color == $user['avatar_color'] ? 'checked' : '' ?>>
                            <span class="color-dot" style="background-color: <?= $color ?>"></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary modal-close">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Onglets
    document.querySelectorAll('.profile-tabs .tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Mettre à jour les onglets actifs
            document.querySelectorAll('.profile-tabs .tab').forEach(t => {
                t.classList.remove('active');
            });
            this.classList.add('active');
            
            // Afficher le contenu correspondant
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Modal d'avatar
    const avatarModal = document.getElementById('avatar-modal');
    const avatarTriggers = document.querySelectorAll('[data-modal="avatar-modal"]');
    
    avatarTriggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            avatarModal.classList.add('active');
        });
    });
    
    // Fermer la modal
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            avatarModal.classList.remove('active');
        });
    });
    
    // Upload d'image
    const avatarInput = document.getElementById('avatar-input');
    const fileInfo = document.querySelector('.file-info');
    
    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            fileInfo.innerHTML = `
                <div class="file-preview">
                    <strong>${file.name}</strong>
                    <span>${(file.size / 1024).toFixed(2)} KB</span>
                </div>
            `;
        }
    });
    
    // Soumission du formulaire d'avatar
    document.getElementById('avatar-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'update_avatar');
        
        try {
            const response = await fetch('/profile/upload-photo.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Recharger la page pour voir les changements
                location.reload();
            } else {
                alert(result.message || 'Erreur lors du changement d\'avatar');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Une erreur est survenue');
        }
    });
});

function shareProfile(platform) {
    const url = window.location.href;
    const text = `Découvrez mon profil Click n' Pop ! Niveau ${<?= $user['level'] ?>}, meilleur score : ${<?= $stats['best_score'] ?? 0 ?>}`;
    
    let shareUrl = '';
    switch(platform) {
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`;
            break;
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
            break;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

function copyProfileLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Lien copié dans le presse-papier !');
    });
}

function shareGame(gameId) {
    // Implémentation simplifiée
    alert(`Partage de la partie #${gameId} - Fonctionnalité à venir !`);
}

function viewGameDetails(gameId) {
    // Implémentation simplifiée
    alert(`Détails de la partie #${gameId} - Fonctionnalité à venir !`);
}
</script>

<?php
$content = ob_get_clean();
include "../templates/layout.php";

// Fonction pour récupérer les réalisations (à implémenter dans Auth)
function getUserAchievements($userId) {
    // Données de démonstration
    return [
        [
            'name' => 'Première bulle',
            'description' => 'Éclate ta première bulle',
            'icon' => 'bubble',
            'xp_reward' => 50,
            'rarity' => 'common',
            'unlocked_at' => date('Y-m-d H:i:s', strtotime('-1 week'))
        ],
        [
            'name' => 'Score de 1000',
            'description' => 'Atteins un score de 1000 points',
            'icon' => 'star',
            'xp_reward' => 100,
            'rarity' => 'common',
            'unlocked_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
        ],
        [
            'name' => 'Précision 80%',
            'description' => 'Atteins 80% de précision',
            'icon' => 'target',
            'xp_reward' => 250,
            'rarity' => 'uncommon',
            'unlocked_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
        ]
    ];
}
?>