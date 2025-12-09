/**
 * Leaderboard Functionality
 * Handles leaderboard interactions, filtering, and real-time updates
 */

class Leaderboard {
    constructor() {
        this.currentFilter = 'global';
        this.currentPage = 1;
        this.totalPages = 1;
        this.searchTimeout = null;
        this.isLoading = false;
        
        this.init();
    }

    init() {
        console.log('Leaderboard initialized');
        
        this.bindEvents();
        this.updateLastUpdated();
        this.startLiveUpdates();
        
        // Marquer la ligne de l'utilisateur courant
        this.highlightCurrentUser();
    }

    bindEvents() {
        // Boutons de filtre
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleFilterClick(e));
        });

        // Recherche
        const searchInput = document.querySelector('#player-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e));
        }

        // Bouton effacer recherche
        const searchClear = document.querySelector('.search-clear');
        if (searchClear) {
            searchClear.addEventListener('click', () => this.clearSearch());
        }

        // Rafraîchissement
        const refreshBtn = document.querySelector('.btn-refresh');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.refreshData());
        }

        // Pagination
        document.querySelectorAll('.page-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handlePagination(e));
        });

        document.querySelectorAll('.page-number').forEach(btn => {
            btn.addEventListener('click', (e) => this.handlePageNumber(e));
        });

        // Actions sur les joueurs
        this.bindPlayerActions();
        
        // Partager le leaderboard
        const shareBtn = document.getElementById('share-leaderboard');
        if (shareBtn) {
            shareBtn.addEventListener('click', () => this.shareLeaderboard());
        }
        
        // Modal
        const modalOverlay = document.querySelector('.modal-overlay');
        if (modalOverlay) {
            modalOverlay.addEventListener('click', () => this.closeModal());
        }
        
        const modalClose = document.querySelector('.modal-close');
        if (modalClose) {
            modalClose.addEventListener('click', () => this.closeModal());
        }
        
        // Fermer modal avec ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModal();
            }
        });
    }

    handleFilterClick(event) {
        if (this.isLoading) return;
        
        const btn = event.currentTarget;
        const filter = btn.dataset.filter;
        
        if (filter === this.currentFilter) return;
        
        // Mettre à jour les boutons actifs
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('active');
        });
        btn.classList.add('active');
        
        // Changer le filtre
        this.currentFilter = filter;
        
        // Afficher l'indicateur de chargement
        this.showLoading();
        
        // Simuler le chargement des données
        setTimeout(() => {
            this.loadLeaderboard();
        }, 800);
    }

    async loadLeaderboard() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        
        try {
            // Dans une application réelle, on ferait un appel API
            // const response = await fetch(`/api/leaderboard?filter=${this.currentFilter}&page=${this.currentPage}`);
            // const data = await response.json();
            
            // Pour l'exemple, on simule des données
            const mockData = this.getMockData();
            
            this.updateTable(mockData.players);
            this.updatePagination(mockData.pagination);
            this.updateStats(mockData.stats);
            this.updateLastUpdated();
            
            // Re-binder les actions des joueurs
            this.bindPlayerActions();
            
            this.showNotification(`Showing ${this.currentFilter} rankings`, 'success');
            
        } catch (error) {
            console.error('Error loading leaderboard:', error);
            this.showNotification('Failed to load leaderboard data', 'error');
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }

    getMockData() {
        // Générer des données mock pour la démo
        const players = [];
        const stats = {
            total_players: Math.floor(Math.random() * 5000) + 10000,
            total_bubbles: Math.floor(Math.random() * 10000000) + 5000000,
            total_games: Math.floor(Math.random() * 50000) + 25000,
            avg_accuracy: Math.floor(Math.random() * 10) + 85
        };
        
        for (let i = 0; i < 50; i++) {
            const rank = i + 1;
            players.push({
                rank: rank,
                username: `Player${Math.floor(Math.random() * 9999) + 1000}`,
                level: Math.floor(Math.random() * 100) + 1,
                best_score: Math.floor(Math.random() * 20000) + 5000,
                games_played: Math.floor(Math.random() * 500) + 50,
                avg_accuracy: Math.floor(Math.random() * 20) + 75 + (Math.random() * 0.9),
                total_bubbles: Math.floor(Math.random() * 500000) + 10000,
                is_current_user: Math.random() > 0.9
            });
        }
        
        return {
            players,
            stats,
            pagination: {
                current: this.currentPage,
                total: 10,
                has_next: this.currentPage < 10,
                has_prev: this.currentPage > 1
            }
        };
    }

    handleSearch(event) {
        const searchTerm = event.target.value.toLowerCase().trim();
        const searchClear = document.querySelector('.search-clear');
        
        // Afficher/masquer le bouton effacer
        if (searchClear) {
            searchClear.style.opacity = searchTerm ? '1' : '0';
        }
        
        // Délai pour éviter trop d'appels
        clearTimeout(this.searchTimeout);
        
        this.searchTimeout = setTimeout(() => {
            this.filterPlayers(searchTerm);
        }, 300);
    }

    filterPlayers(searchTerm) {
        const rows = document.querySelectorAll('#leaderboard-body tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const playerName = row.querySelector('.player-name').textContent.toLowerCase();
            const username = row.dataset.username.toLowerCase();
            
            const matches = playerName.includes(searchTerm) || username.includes(searchTerm);
            row.style.display = matches ? '' : 'none';
            
            if (matches) {
                visibleCount++;
                row.querySelector('.rank-number').textContent = visibleCount;
            }
        });
        
        // Mettre à jour le compteur
        const countElement = document.querySelector('.players-count');
        if (countElement) {
            countElement.textContent = `(${visibleCount} players)`;
        }
    }

    clearSearch() {
        const searchInput = document.querySelector('#player-search');
        if (searchInput) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        }
    }

    async refreshData() {
        if (this.isLoading) return;
        
        const refreshBtn = document.querySelector('.btn-refresh');
        if (refreshBtn) {
            refreshBtn.classList.add('rotating');
        }
        
        // Désactiver l'entrée pendant le rafraîchissement
        this.isLoading = true;
        
        try {
            // Simuler un appel API
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            // Recharger les données
            await this.loadLeaderboard();
            
            this.showNotification('Leaderboard refreshed successfully', 'success');
            
        } catch (error) {
            console.error('Error refreshing data:', error);
            this.showNotification('Failed to refresh data', 'error');
        } finally {
            this.isLoading = false;
            if (refreshBtn) {
                refreshBtn.classList.remove('rotating');
            }
        }
    }

    updateTable(players) {
        const tbody = document.querySelector('#leaderboard-body');
        if (!tbody) return;
        
        tbody.innerHTML = players.map(player => this.createPlayerRow(player)).join('');
        
        // Re-highlight l'utilisateur courant
        this.highlightCurrentUser();
    }

    createPlayerRow(player) {
        const isCurrentUser = player.is_current_user || false;
        const rowClass = isCurrentUser ? 'current-user' : '';
        const medal = player.rank <= 3 ? `
            <div class="medal medal-${player.rank}">
                <i class="fas fa-medal"></i>
            </div>
        ` : '';
        
        return `
            <tr class="${rowClass}" data-rank="${player.rank}" data-username="${player.username}">
                <td class="rank-cell">
                    <div class="rank-indicator">
                        ${medal}
                        <span class="rank-number">${player.rank}</span>
                    </div>
                </td>
                
                <td class="player-cell">
                    <div class="player-info">
                        <div class="player-avatar">
                            <div class="avatar-placeholder">
                                ${player.username.charAt(0).toUpperCase()}
                            </div>
                        </div>
                        <div class="player-details">
                            <div class="player-name">
                                ${player.username}
                                ${isCurrentUser ? '<span class="you-badge"><i class="fas fa-user"></i> You</span>' : ''}
                            </div>
                            <div class="player-tagline">
                                <i class="fas fa-star"></i>
                                ${this.getPlayerTitle(player.level)}
                            </div>
                        </div>
                    </div>
                </td>
                
                <td class="score-cell">
                    <div class="score-value">
                        ${player.best_score.toLocaleString()}
                        ${player.rank === 1 ? '<span class="top-score-badge"><i class="fas fa-crown"></i></span>' : ''}
                    </div>
                </td>
                
                <td class="level-cell">
                    <div class="level-display">
                        <div class="level-progress">
                            <div class="level-bar" style="width: ${player.level % 10 * 10}%"></div>
                        </div>
                        <span class="level-text">Lv. ${player.level}</span>
                    </div>
                </td>
                
                <td class="games-cell">
                    <div class="games-count">
                        <i class="fas fa-play-circle"></i>
                        ${player.games_played.toLocaleString()}
                    </div>
                </td>
                
                <td class="accuracy-cell">
                    <div class="accuracy-display">
                        <div class="accuracy-bar">
                            <div class="accuracy-fill" style="width: ${Math.min(100, player.avg_accuracy)}%"></div>
                            <span class="accuracy-text">${player.avg_accuracy.toFixed(1)}%</span>
                        </div>
                        <div class="accuracy-rating">
                            ${this.getAccuracyRating(player.avg_accuracy)}
                        </div>
                    </div>
                </td>
                
                <td class="bubbles-cell">
                    <div class="bubbles-count">
                        <i class="fas fa-bubbles"></i>
                        ${player.total_bubbles.toLocaleString()}
                    </div>
                </td>
                
                <td class="action-cell">
                    <div class="action-buttons">
                        <button class="btn-view-profile" 
                                data-user="${player.username}"
                                title="View profile">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${!isCurrentUser ? `
                        <button class="btn-add-friend" 
                                data-user="${player.username}"
                                title="Add friend">
                            <i class="fas fa-user-plus"></i>
                        </button>
                        <button class="btn-challenge" 
                                data-user="${player.username}"
                                title="Send challenge">
                            <i class="fas fa-gamepad"></i>
                        </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    }

    bindPlayerActions() {
        // Voir profil
        document.querySelectorAll('.btn-view-profile').forEach(btn => {
            btn.addEventListener('click', (e) => this.viewProfile(e));
        });

        // Ajouter ami
        document.querySelectorAll('.btn-add-friend').forEach(btn => {
            btn.addEventListener('click', (e) => this.addFriend(e));
        });

        // Envoyer défi
        document.querySelectorAll('.btn-challenge').forEach(btn => {
            btn.addEventListener('click', (e) => this.sendChallenge(e));
        });
    }

    getPlayerTitle(level) {
        if (level >= 90) return 'Bubble Legend';
        if (level >= 75) return 'Pop Demigod';
        if (level >= 60) return 'Master Popper';
        if (level >= 45) return 'Bubble Expert';
        if (level >= 30) return 'Skilled Popper';
        if (level >= 20) return 'Rising Star';
        if (level >= 10) return 'Enthusiast';
        return 'New Player';
    }

    getAccuracyRating(accuracy) {
        if (accuracy >= 95) return '<span class="rating excellent">SS</span>';
        if (accuracy >= 90) return '<span class="rating great">S</span>';
        if (accuracy >= 85) return '<span class="rating good">A</span>';
        if (accuracy >= 80) return '<span class="rating average">B</span>';
        if (accuracy >= 75) return '<span class="rating fair">C</span>';
        return '<span class="rating poor">D</span>';
    }

    highlightCurrentUser() {
        // Ajouter un effet spécial à la ligne de l'utilisateur courant
        const currentUserRows = document.querySelectorAll('.current-user');
        currentUserRows.forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.boxShadow = '0 0 0 2px rgba(99, 102, 241, 0.2)';
            });
            
            row.addEventListener('mouseleave', () => {
                row.style.boxShadow = '';
            });
        });
    }

    async viewProfile(event) {
        const username = event.currentTarget.dataset.user;
        
        try {
            // Dans une application réelle, on ferait un appel API
            // const response = await fetch(`/api/profile/${username}`);
            // const profile = await response.json();
            
            // Pour l'exemple, on montre un profil mock
            const profile = {
                username: username,
                level: Math.floor(Math.random() * 100) + 1,
                xp: Math.floor(Math.random() * 100000) + 1000,
                rank: Math.floor(Math.random() * 500) + 1,
                best_score: Math.floor(Math.random() * 20000) + 5000,
                avg_accuracy: Math.floor(Math.random() * 20) + 75 + (Math.random() * 0.9),
                total_games: Math.floor(Math.random() * 500) + 50,
                total_bubbles: Math.floor(Math.random() * 500000) + 10000,
                join_date: new Date(Date.now() - Math.random() * 31536000000).toISOString().split('T')[0],
                country: ['US', 'FR', 'UK', 'DE', 'CA', 'JP', 'AU', 'BR'][Math.floor(Math.random() * 8)]
            };
            
            this.showProfileModal(profile);
            
        } catch (error) {
            console.error('Error loading profile:', error);
            this.showNotification('Failed to load profile', 'error');
        }
    }

    showProfileModal(profile) {
        const modal = document.getElementById('profile-modal');
        const content = document.getElementById('modal-profile-content');
        
        if (!modal || !content) return;
        
        content.innerHTML = `
            <div class="profile-modal-content">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <div class="avatar-large">
                            ${profile.username.charAt(0).toUpperCase()}
                        </div>
                    </div>
                    <div class="profile-info">
                        <h3 class="profile-username">${profile.username}</h3>
                        <div class="profile-level">
                            <span class="level-badge">Level ${profile.level}</span>
                            <span class="xp-text">${profile.xp.toLocaleString()} XP</span>
                        </div>
                        <div class="profile-rank">
                            <i class="fas fa-trophy"></i>
                            Rank #${profile.rank}
                        </div>
                    </div>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-row">
                        <div class="stat-item">
                            <span class="stat-label">Best Score</span>
                            <span class="stat-value">${profile.best_score.toLocaleString()}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Accuracy</span>
                            <span class="stat-value">${profile.avg_accuracy.toFixed(1)}%</span>
                        </div>
                    </div>
                    
                    <div class="stat-row">
                        <div class="stat-item">
                            <span class="stat-label">Total Games</span>
                            <span class="stat-value">${profile.total_games}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Total Bubbles</span>
                            <span class="stat-value">${profile.total_bubbles.toLocaleString()}</span>
                        </div>
                    </div>
                    
                    <div class="profile-meta">
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            Joined ${new Date(profile.join_date).toLocaleDateString()}
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-globe"></i>
                            ${profile.country}
                        </div>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <button class="btn-modal primary" id="btn-send-message">
                        <i class="fas fa-envelope"></i>
                        Send Message
                    </button>
                    <button class="btn-modal secondary" id="btn-view-games">
                        <i class="fas fa-history"></i>
                        View Games
                    </button>
                </div>
            </div>
        `;
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Binder les boutons de la modal
        setTimeout(() => {
            const sendMessageBtn = document.getElementById('btn-send-message');
            const viewGamesBtn = document.getElementById('btn-view-games');
            
            if (sendMessageBtn) {
                sendMessageBtn.addEventListener('click', () => {
                    this.showNotification('Message feature coming soon!', 'info');
                });
            }
            
            if (viewGamesBtn) {
                viewGamesBtn.addEventListener('click', () => {
                    this.showNotification('Game history feature coming soon!', 'info');
                });
            }
        }, 100);
    }

    closeModal() {
        const modal = document.getElementById('profile-modal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    async addFriend(event) {
        const username = event.currentTarget.dataset.user;
        const btn = event.currentTarget;
        
        // Désactiver le bouton pendant l'envoi
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        try {
            // Simuler un appel API
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            // Mettre à jour l'état du bouton
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.classList.add('success');
            btn.title = 'Friend request sent';
            
            this.showNotification(`Friend request sent to ${username}`, 'success');
            
        } catch (error) {
            console.error('Error sending friend request:', error);
            btn.innerHTML = '<i class="fas fa-user-plus"></i>';
            btn.disabled = false;
            this.showNotification('Failed to send friend request', 'error');
        }
    }

    async sendChallenge(event) {
        const username = event.currentTarget.dataset.user;
        const btn = event.currentTarget;
        
        // Désactiver le bouton pendant l'envoi
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        try {
            // Simuler un appel API
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            // Mettre à jour l'état du bouton
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.classList.add('success');
            btn.title = 'Challenge sent';
            
            this.showNotification(`Challenge sent to ${username}!`, 'success');
            
        } catch (error) {
            console.error('Error sending challenge:', error);
            btn.innerHTML = '<i class="fas fa-gamepad"></i>';
            btn.disabled = false;
            this.showNotification('Failed to send challenge', 'error');
        }
    }

    handlePagination(event) {
        if (this.isLoading) return;
        
        const btn = event.currentTarget;
        const direction = btn.classList.contains('next') ? 'next' : 'prev';
        
        if (direction === 'next' && this.currentPage >= this.totalPages) return;
        if (direction === 'prev' && this.currentPage <= 1) return;
        
        this.currentPage += direction === 'next' ? 1 : -1;
        this.updatePaginationUI();
        this.loadLeaderboard();
    }

    handlePageNumber(event) {
        if (this.isLoading) return;
        
        const btn = event.currentTarget;
        const page = parseInt(btn.textContent);
        
        if (page === this.currentPage) return;
        
        this.currentPage = page;
        this.updatePaginationUI();
        this.loadLeaderboard();
    }

    updatePagination(pagination) {
        this.totalPages = pagination.total;
        
        // Mettre à jour l'UI de pagination
        const prevBtn = document.querySelector('.page-btn.prev');
        const nextBtn = document.querySelector('.page-btn.next');
        
        if (prevBtn) {
            prevBtn.disabled = !pagination.has_prev;
        }
        
        if (nextBtn) {
            nextBtn.disabled = !pagination.has_next;
        }
        
        // Mettre à jour les numéros de page (simplifié pour l'exemple)
        const pageNumbers = document.querySelectorAll('.page-number');
        pageNumbers.forEach((numBtn, index) => {
            const pageNum = index + 1;
            numBtn.classList.toggle('active', pageNum === this.currentPage);
        });
        
        // Mettre à jour les informations de page
        const pageInfo = document.querySelector('.page-info');
        if (pageInfo) {
            const start = (this.currentPage - 1) * 50 + 1;
            const end = Math.min(this.currentPage * 50, pagination.total * 50);
            pageInfo.textContent = `Showing ${start}-${end} of ${pagination.total * 50} players`;
        }
    }

    updatePaginationUI() {
        // Mettre à jour les boutons actifs
        document.querySelectorAll('.page-number').forEach(btn => {
            const pageNum = parseInt(btn.textContent);
            btn.classList.toggle('active', pageNum === this.currentPage);
        });
        
        // Mettre à jour les boutons précédent/suivant
        const prevBtn = document.querySelector('.page-btn.prev');
        const nextBtn = document.querySelector('.page-btn.next');
        
        if (prevBtn) {
            prevBtn.disabled = this.currentPage <= 1;
        }
        
        if (nextBtn) {
            nextBtn.disabled = this.currentPage >= this.totalPages;
        }
    }

    updateStats(stats) {
        // Mettre à jour les statistiques globales
        const statCards = document.querySelectorAll('.stat-card .stat-value');
        if (statCards.length >= 4) {
            statCards[0].textContent = stats.total_players.toLocaleString();
            statCards[1].textContent = stats.total_bubbles.toLocaleString();
            // Les autres statistiques sont déjà mises à jour côté serveur
        }
    }

    updateLastUpdated() {
        const now = new Date();
        const timeString = now.toLocaleTimeString([], { 
            hour: '2-digit', 
            minute: '2-digit',
            second: '2-digit'
        });
        
        const dateString = now.toLocaleDateString([], {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
        
        const lastUpdated = document.getElementById('last-updated');
        if (lastUpdated) {
            lastUpdated.textContent = `${dateString} ${timeString}`;
        }
    }

    startLiveUpdates() {
        // Mettre à jour automatiquement toutes les 2 minutes
        setInterval(() => {
            if (!this.isLoading && document.visibilityState === 'visible') {
                this.updateLastUpdated();
            }
        }, 120000);
    }

    async shareLeaderboard() {
        if (!navigator.share) {
            // Fallback pour les navigateurs qui ne supportent pas l'API Web Share
            navigator.clipboard.writeText(window.location.href).then(() => {
                this.showNotification('Link copied to clipboard!', 'success');
            }).catch(() => {
                prompt('Copy this link to share:', window.location.href);
            });
            return;
        }
        
        try {
            await navigator.share({
                title: 'Click n\' Pop Leaderboard',
                text: 'Check out the top players on Click n\' Pop! Can you beat them?',
                url: window.location.href
            });
            
            this.showNotification('Leaderboard shared successfully!', 'success');
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Error sharing:', error);
                this.showNotification('Failed to share leaderboard', 'error');
            }
        }
    }

    showLoading() {
        // Afficher un indicateur de chargement
        const tbody = document.querySelector('#leaderboard-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr class="loading-row">
                    <td colspan="8">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                            <span>Loading ${this.currentFilter} rankings...</span>
                        </div>
                    </td>
                </tr>
            `;
        }
        
        // Ajouter des styles pour le spinner
        if (!document.querySelector('#loading-styles')) {
            const style = document.createElement('style');
            style.id = 'loading-styles';
            style.textContent = `
                .loading-row td {
                    padding: 3rem !important;
                    text-align: center;
                }
                
                .loading-spinner {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 1rem;
                    color: var(--color-text-tertiary);
                }
                
                .spinner {
                    width: 40px;
                    height: 40px;
                    border: 3px solid var(--color-border);
                    border-top-color: var(--color-primary);
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }
                
                @keyframes spin {
                    to { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
    }

    hideLoading() {
        // Retirer les styles de chargement
        const styles = document.querySelector('#loading-styles');
        if (styles) {
            styles.remove();
        }
    }

    showNotification(message, type = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <i class="${icons[type] || icons.info}"></i>
            <div class="toast-content">
                <div class="toast-title">${type.charAt(0).toUpperCase() + type.slice(1)}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(toast);
        
        // Fermer automatiquement après 5 secondes
        const autoClose = setTimeout(() => {
            toast.remove();
        }, 5000);
        
        // Fermer manuellement
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
            clearTimeout(autoClose);
            toast.remove();
        });
        
        return toast;
    }
}

// Initialiser quand le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    // Vérifier si nous sommes sur la page leaderboard
    if (document.body.classList.contains('leaderboard-page')) {
        window.leaderboard = new Leaderboard();
    }
});

// Exporter pour une utilisation globale
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Leaderboard;
}