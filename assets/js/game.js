// Click n' Pop - Game Engine v2.0 - CORRIGÉ AVEC AUDIO
class BubbleGame {
    constructor() {
        this.initElements();
        this.initGameState();
        this.initEventListeners();
        this.initAudio();
        this.createFloatingBubbles();
    }

    // Initialisation
    initElements() {
        // Game elements
        this.gameArea = document.getElementById('game-arena');
        this.arenaOverlay = document.getElementById('arena-overlay');
        this.startBtn = document.getElementById('start-game');
        this.pauseBtn = document.getElementById('pause-game');
        this.howToPlayBtn = document.getElementById('how-to-play');
        this.tutorialModal = document.getElementById('tutorial-modal');
        this.startTutorialBtn = document.getElementById('start-tutorial-game');
        
        // Display elements
        this.timerDisplay = document.getElementById('timer-display');
        this.levelDisplay = document.getElementById('level-display');
        this.speedDisplay = document.getElementById('speed-display');
        this.scoreDisplay = document.getElementById('score-display');
        this.accuracyDisplay = document.getElementById('accuracy-display');
        this.bubblesPoppedDisplay = document.getElementById('bubbles-popped');
        this.comboMultiplierDisplay = document.getElementById('combo-multiplier');
        this.comboDisplay = document.getElementById('combo-display');
        this.feedbackMessage = document.getElementById('feedback-message');
        this.personalBestDisplay = document.getElementById('personal-best');
        
        // Game over elements
        this.gameOverModal = document.getElementById('game-over-modal');
        this.finalScoreDisplay = document.getElementById('final-score-display');
        this.finalBubblesDisplay = document.getElementById('final-bubbles');
        this.finalAccuracyDisplay = document.getElementById('final-accuracy');
        this.finalComboDisplay = document.getElementById('final-combo');
        this.playAgainBtn = document.getElementById('play-again');
        this.shareScoreBtn = document.getElementById('share-score');
        
        // Power-ups
        this.powerupsList = document.getElementById('power-ups-list');
        this.powerupItems = document.querySelectorAll('.powerup-item');
    }

    initGameState() {
        this.gameState = {
            isPlaying: false,
            isPaused: false,
            score: 0,
            timeLeft: 60,
            level: 1,
            combo: 1,
            comboStreak: 0,
            comboTimeout: null,
            accuracy: {
                hits: 0,
                total: 0,
                value: 0
            },
            bubblesPopped: 0,
            bubblesMissed: 0,
            gameSpeed: 1.0,
            activePowerUps: new Map(),
            spawnInterval: null,
            animationFrame: null,
            lastUpdate: 0,
            bubbles: new Set()
        };

        // Load best score from localStorage
        this.bestScore = parseInt(localStorage.getItem('clicknpop_best_score')) || 1250;
        this.personalBestDisplay.textContent = this.bestScore.toLocaleString();

        // Level configurations
        this.levels = [
            { spawnRate: 800, speed: 1.0, bubbleTypes: ['normal', 'normal', 'normal', 'golden'] },
            { spawnRate: 650, speed: 1.2, bubbleTypes: ['normal', 'normal', 'golden', 'time'] },
            { spawnRate: 500, speed: 1.4, bubbleTypes: ['normal', 'golden', 'time', 'multiplier'] },
            { spawnRate: 400, speed: 1.6, bubbleTypes: ['normal', 'golden', 'time', 'multiplier', 'bomb'] },
            { spawnRate: 300, speed: 1.8, bubbleTypes: ['golden', 'time', 'multiplier', 'bomb'] }
        ];

        // Bubble types configuration
        this.bubbleTypes = {
            normal: {
                color: '#4a90e2',
                points: 10,
                probability: 0.7,
                radius: [30, 45],
                speed: [40, 80]
            },
            golden: {
                color: '#FFD700',
                points: 50,
                probability: 0.1,
                radius: [25, 35],
                speed: [60, 100],
                effect: 'points'
            },
            time: {
                color: '#00FF88',
                points: 0,
                probability: 0.08,
                radius: [35, 50],
                speed: [30, 60],
                effect: 'addTime'
            },
            multiplier: {
                color: '#9C27B0',
                points: 30,
                probability: 0.07,
                radius: [30, 40],
                speed: [50, 90],
                effect: 'boostCombo'
            },
            bomb: {
                color: '#FF4444',
                points: -20,
                probability: 0.05,
                radius: [40, 55],
                speed: [20, 50],
                effect: 'resetCombo'
            }
        };
    }

    initEventListeners() {
        // Game control buttons
        this.startBtn.addEventListener('click', () => this.toggleGame());
        this.pauseBtn.addEventListener('click', () => this.togglePause());
        this.howToPlayBtn.addEventListener('click', () => this.showTutorial());
        this.startTutorialBtn.addEventListener('click', () => this.startFromTutorial());
        
        // Modal controls - Corrigé
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const modal = e.target.closest('.modal');
                this.hideModal(modal);
            });
        });
        
        // Game over controls
        this.playAgainBtn.addEventListener('click', () => this.restartGame());
        this.shareScoreBtn.addEventListener('click', () => this.shareScore());
        
        // Power-up purchases
        this.powerupItems.forEach(item => {
            item.addEventListener('click', (e) => {
                if (!this.gameState.isPlaying) return;
                const powerup = e.currentTarget.dataset.powerup;
                this.buyPowerUp(powerup);
            });
        });
        
        // Keyboard controls
        document.addEventListener('keydown', (e) => {
            if (e.code === 'Space') {
                e.preventDefault();
                this.togglePause();
            } else if (e.code === 'KeyP' && this.gameState.isPlaying) {
                this.togglePause();
            } else if (e.code === 'KeyR' && !this.gameState.isPlaying) {
                this.restartGame();
            } else if (e.code === 'Escape') {
                // Fermer les modals avec Escape
                if (this.tutorialModal.getAttribute('aria-hidden') === 'false') {
                    this.hideModal(this.tutorialModal);
                }
                if (this.gameOverModal.getAttribute('aria-hidden') === 'false') {
                    this.hideModal(this.gameOverModal);
                }
            }
        });
        
        // Fermer modal en cliquant à l'extérieur
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.hideModal(modal);
                }
            });
        });
        
        // Prevent context menu on game area
        this.gameArea.addEventListener('contextmenu', (e) => e.preventDefault());
    }

    initAudio() {
        this.audio = {
            pop: document.getElementById('pop-sound'),
            golden: document.getElementById('golden-pop'),
            bomb: document.getElementById('bomb-sound'),
            powerup: document.getElementById('powerup-sound'),
            gameStart: document.getElementById('game-start'),
            gameOver: document.getElementById('game-over-sound')
        };
    }

    // Game control methods
    toggleGame() {
        if (!this.gameState.isPlaying) {
            this.startGame();
        } else {
            this.restartGame();
        }
    }

    startGame() {
        // Reset game state
        this.resetGameState();
        
        // Update UI
        this.gameState.isPlaying = true;
        this.startBtn.innerHTML = '<i class="fas fa-redo"></i> Restart';
        this.pauseBtn.disabled = false;
        this.arenaOverlay.style.display = 'none';
        
        // Update displays
        this.updateDisplays();
        
        // Play start sound
        this.playSound('gameStart');
        
        // Start game loop
        this.startGameLoop();
        
        // Start spawning bubbles
        this.startSpawning();
        
        // Show feedback
        this.showFeedback('Game started! Pop those bubbles!', 'info');
    }

    togglePause() {
        if (!this.gameState.isPlaying) return;
        
        if (this.gameState.isPaused) {
            this.resumeGame();
        } else {
            this.pauseGame();
        }
    }

    pauseGame() {
        this.gameState.isPaused = true;
        this.pauseBtn.innerHTML = '<i class="fas fa-play"></i> Resume';
        this.pauseBtn.classList.add('btn-success');
        
        // Stop spawning and animation
        clearInterval(this.gameState.spawnInterval);
        cancelAnimationFrame(this.gameState.animationFrame);
        
        // Pause all active bubbles
        this.gameState.bubbles.forEach(bubble => {
            bubble.el.style.animationPlayState = 'paused';
        });
        
        this.showFeedback('Game Paused', 'warning');
    }

    resumeGame() {
        this.gameState.isPaused = false;
        this.pauseBtn.innerHTML = '<i class="fas fa-pause"></i> Pause';
        this.pauseBtn.classList.remove('btn-success');
        
        // Resume spawning
        this.startSpawning();
        
        // Resume animation
        this.startGameLoop();
        
        // Resume bubble animations
        this.gameState.bubbles.forEach(bubble => {
            bubble.el.style.animationPlayState = 'running';
        });
        
        this.showFeedback('Game Resumed!', 'success');
    }

    restartGame() {
        // Close modals
        this.hideModal(this.gameOverModal);
        
        // Clear existing game
        this.clearGame();
        
        // Start new game
        this.startGame();
    }

    endGame() {
        this.gameState.isPlaying = false;
        this.gameState.isPaused = false;
        
        // Stop game loops
        clearInterval(this.gameState.spawnInterval);
        cancelAnimationFrame(this.gameState.animationFrame);
        
        // Update UI
        this.startBtn.innerHTML = '<i class="fas fa-play-circle"></i> Start Game';
        this.pauseBtn.disabled = true;
        this.arenaOverlay.style.display = 'flex';
        
        // Calculate final stats
        const finalAccuracy = this.gameState.accuracy.total > 0 
            ? (this.gameState.accuracy.hits / this.gameState.accuracy.total * 100).toFixed(1)
            : '0.0';
        
        // Update game over modal
        this.finalScoreDisplay.textContent = this.gameState.score.toLocaleString();
        this.finalBubblesDisplay.textContent = this.gameState.bubblesPopped;
        this.finalAccuracyDisplay.textContent = finalAccuracy + '%';
        this.finalComboDisplay.textContent = 'x' + this.gameState.combo.toFixed(1);
        
        // Check for new high score
        if (this.gameState.score > this.bestScore) {
            this.bestScore = this.gameState.score;
            localStorage.setItem('clicknpop_best_score', this.bestScore);
            this.personalBestDisplay.textContent = this.bestScore.toLocaleString();
            this.showFeedback('🎉 New High Score! 🎉', 'success');
        }
        
        // Show game over modal
        this.gameOverModal.style.display = 'flex';
        setTimeout(() => {
            this.gameOverModal.setAttribute('aria-hidden', 'false');
        }, 10);
        
        // Play game over sound
        this.playSound('gameOver');
    }

    // Game loop methods
    startGameLoop() {
        this.gameState.lastUpdate = performance.now();
        const gameLoop = (currentTime) => {
            if (!this.gameState.isPlaying || this.gameState.isPaused) return;
            
            const deltaTime = (currentTime - this.gameState.lastUpdate) / 1000;
            this.gameState.lastUpdate = currentTime;
            
            // Update timer
            this.gameState.timeLeft -= deltaTime;
            this.timerDisplay.textContent = Math.max(0, Math.ceil(this.gameState.timeLeft));
            
            // Update level based on time
            this.updateLevel();
            
            // Update bubbles
            this.updateBubbles(deltaTime);
            
            // Check game over
            if (this.gameState.timeLeft <= 0) {
                this.endGame();
                return;
            }
            
            // Continue loop
            this.gameState.animationFrame = requestAnimationFrame(gameLoop);
        };
        
        this.gameState.animationFrame = requestAnimationFrame(gameLoop);
    }

    startSpawning() {
        const levelConfig = this.levels[this.gameState.level - 1] || this.levels[0];
        
        this.gameState.spawnInterval = setInterval(() => {
            if (!this.gameState.isPlaying || this.gameState.isPaused) return;
            this.spawnBubble();
        }, levelConfig.spawnRate);
    }

    spawnBubble() {
        const gameAreaRect = this.gameArea.getBoundingClientRect();
        const levelConfig = this.levels[this.gameState.level - 1] || this.levels[0];
        
        // Get random bubble type based on probabilities
        const bubbleType = this.getRandomBubbleType();
        const config = this.bubbleTypes[bubbleType];
        
        // Calculate position and size
        const radius = this.getRandomNumber(...config.radius);
        const x = this.getRandomNumber(radius, gameAreaRect.width - radius);
        const y = gameAreaRect.height + radius; // Start below visible area
        
        const speed = this.getRandomNumber(...config.speed) * this.gameState.gameSpeed;
        
        // Create bubble
        const bubble = new GameBubble({
            x,
            y,
            radius,
            speed,
            color: config.color,
            type: bubbleType,
            points: config.points,
            effect: config.effect
        });
        
        // Add to game state
        this.gameState.bubbles.add(bubble);
        
        // Add to DOM
        this.gameArea.appendChild(bubble.el);
    }

    updateBubbles(deltaTime) {
        const gameAreaRect = this.gameArea.getBoundingClientRect();
        
        this.gameState.bubbles.forEach(bubble => {
            // Update position
            bubble.y -= bubble.speed * deltaTime;
            bubble.el.style.top = (bubble.y - bubble.radius) + 'px';
            
            // Check if bubble is out of bounds
            if (bubble.y + bubble.radius < 0) {
                this.removeBubble(bubble, false); // Missed bubble
            }
        });
    }

    removeBubble(bubble, wasPopped) {
        // Remove from DOM
        if (bubble.el && bubble.el.parentNode) {
            bubble.el.parentNode.removeChild(bubble.el);
        }
        
        // Remove from game state
        this.gameState.bubbles.delete(bubble);
        
        // Update accuracy
        this.gameState.accuracy.total++;
        if (wasPopped) {
            this.gameState.accuracy.hits++;
        } else {
            this.gameState.bubblesMissed++;
        }
        
        // Update accuracy display
        this.updateAccuracy();
    }

    // Bubble interaction - CORRIGÉ POUR L'AUDIO
    handleBubbleClick(bubble) {
        if (!this.gameState.isPlaying || this.gameState.isPaused) return;
        
        // Update combo
        this.updateCombo(true);
        
        // Calculate points with combo multiplier
        const points = Math.round(bubble.points * this.gameState.combo);
        this.gameState.score += points;
        this.gameState.bubblesPopped++;
        
        // Play sound based on bubble type - CORRECTION AUDIO
        if (bubble.points > 0) {
            if (bubble.type === 'golden') {
                this.playSound('golden');
            } else {
                this.playSound('pop');
            }
        } else {
            this.playSound('bomb');
        }
        
        // Apply bubble effect
        if (bubble.effect) {
            this.applyBubbleEffect(bubble);
        }
        
        // Create pop effect
        this.createPopEffect(bubble);
        
        // Show score popup
        this.showScorePopup(bubble, points);
        
        // Remove bubble
        this.removeBubble(bubble, true);
        
        // Update displays
        this.updateDisplays();
        
        // Show feedback for special bubbles
        if (bubble.type === 'golden') {
            this.showFeedback('Golden Bubble! +' + points, 'success');
        } else if (bubble.type === 'time') {
            this.showFeedback('+5 Seconds!', 'info');
        } else if (bubble.type === 'bomb') {
            this.showFeedback('Bomb! -' + Math.abs(points), 'danger');
        }
    }

    // Helper methods
    getRandomNumber(min, max) {
        return Math.random() * (max - min) + min;
    }

    getRandomBubbleType() {
        const rand = Math.random();
        let cumulative = 0;
        
        for (const [type, config] of Object.entries(this.bubbleTypes)) {
            cumulative += config.probability;
            if (rand <= cumulative) {
                return type;
            }
        }
        
        return 'normal';
    }

    updateLevel() {
        const newLevel = Math.min(
            5, // Max level
            Math.floor((60 - this.gameState.timeLeft) / 12) + 1 // New level every 12 seconds
        );
        
        if (newLevel !== this.gameState.level) {
            this.gameState.level = newLevel;
            this.levelDisplay.textContent = newLevel;
            
            // Update spawn rate
            clearInterval(this.gameState.spawnInterval);
            this.startSpawning();
            
            // Update speed display
            const speedNames = ['Normal', 'Fast', 'Faster', 'Very Fast', 'Extreme'];
            this.speedDisplay.textContent = speedNames[newLevel - 1] || 'Normal';
            
            // Show level up feedback
            this.showFeedback(`Level ${newLevel}!`, 'info');
        }
    }

    updateCombo(success) {
        if (success) {
            this.gameState.comboStreak++;
            
            // Increase combo based on streak
            if (this.gameState.comboStreak >= 10) {
                this.gameState.combo = 2;
            } else if (this.gameState.comboStreak >= 20) {
                this.gameState.combo = 3;
            } else if (this.gameState.comboStreak >= 30) {
                this.gameState.combo = 4;
            } else if (this.gameState.comboStreak >= 40) {
                this.gameState.combo = 5;
            }
            
            // Reset combo timeout
            clearTimeout(this.gameState.comboTimeout);
            this.gameState.comboTimeout = setTimeout(() => {
                this.gameState.comboStreak = 0;
                this.gameState.combo = 1;
                this.comboDisplay.querySelector('.combo-multiplier').textContent = 'x1';
                this.showFeedback('Combo lost!', 'warning');
            }, 1500); // 1.5 seconds to maintain combo
        } else {
            this.gameState.comboStreak = 0;
            this.gameState.combo = 1;
        }
        
        // Update combo display
        this.comboMultiplierDisplay.textContent = 'x' + this.gameState.combo.toFixed(1);
        this.comboDisplay.querySelector('.combo-multiplier').textContent = 'x' + this.gameState.combo.toFixed(1);
    }

    updateAccuracy() {
        if (this.gameState.accuracy.total > 0) {
            this.gameState.accuracy.value = (this.gameState.accuracy.hits / this.gameState.accuracy.total * 100);
            this.accuracyDisplay.textContent = this.gameState.accuracy.value.toFixed(1) + '%';
        }
    }

    updateDisplays() {
        this.scoreDisplay.textContent = this.gameState.score.toLocaleString();
        this.bubblesPoppedDisplay.textContent = this.gameState.bubblesPopped;
        this.updateAccuracy();
    }

    applyBubbleEffect(bubble) {
        switch(bubble.effect) {
            case 'addTime':
                this.gameState.timeLeft += 5;
                this.showFeedback('+5 seconds!', 'success');
                break;
            case 'boostCombo':
                this.gameState.combo *= 2;
                this.showFeedback('Combo Boost! x' + this.gameState.combo, 'success');
                break;
            case 'resetCombo':
                this.gameState.combo = 1;
                this.gameState.comboStreak = 0;
                this.showFeedback('Combo Reset!', 'danger');
                break;
        }
    }

    createPopEffect(bubble) {
        const popEffect = document.createElement('div');
        popEffect.className = 'pop-effect';
        popEffect.style.cssText = `
            position: absolute;
            left: ${bubble.x}px;
            top: ${bubble.y}px;
            width: ${bubble.radius * 2}px;
            height: ${bubble.radius * 2}px;
            border-radius: 50%;
            background: radial-gradient(circle, ${bubble.color} 0%, transparent 70%);
            pointer-events: none;
            z-index: 10;
            transform: scale(1);
            opacity: 1;
        `;
        
        this.gameArea.appendChild(popEffect);
        
        // Animate pop effect
        requestAnimationFrame(() => {
            popEffect.style.transition = 'transform 0.3s ease-out, opacity 0.3s ease-out';
            popEffect.style.transform = 'scale(2)';
            popEffect.style.opacity = '0';
            
            setTimeout(() => {
                if (popEffect.parentNode) {
                    popEffect.parentNode.removeChild(popEffect);
                }
            }, 300);
        });
    }

    showScorePopup(bubble, points) {
        const popup = document.createElement('div');
        popup.className = 'score-popup';
        popup.textContent = (points > 0 ? '+' : '') + points;
        popup.style.cssText = `
            position: absolute;
            left: ${bubble.x}px;
            top: ${bubble.y}px;
            font-size: 1.2rem;
            font-weight: bold;
            color: ${points > 0 ? '#00FF88' : '#FF4444'};
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
            pointer-events: none;
            z-index: 20;
            transform: translateY(0);
            opacity: 1;
        `;
        
        this.gameArea.appendChild(popup);
        
        // Animate popup
        requestAnimationFrame(() => {
            popup.style.transition = 'transform 0.8s ease-out, opacity 0.8s ease-out';
            popup.style.transform = 'translateY(-60px)';
            popup.style.opacity = '0';
            
            setTimeout(() => {
                if (popup.parentNode) {
                    popup.parentNode.removeChild(popup);
                }
            }, 800);
        });
    }

    showFeedback(message, type = 'info') {
        this.feedbackMessage.textContent = message;
        this.feedbackMessage.className = 'feedback-message';
        
        // Add type class for styling
        this.feedbackMessage.classList.add(`feedback-${type}`);
        
        // Auto-hide after 2 seconds
        setTimeout(() => {
            this.feedbackMessage.classList.remove(`feedback-${type}`);
        }, 2000);
    }

    playSound(soundName) {
        const audio = this.audio[soundName];
        if (audio) {
            audio.currentTime = 0;
            audio.play().catch(e => {
                console.log('Audio play failed:', e);
            });
        }
    }

    buyPowerUp(powerupType) {
        // In a real game, this would check for coins/currency
        // For now, just activate the power-up
        
        this.showFeedback(`Activated ${powerupType}!`, 'success');
        this.playSound('powerup');
        
        // Add to active power-ups
        this.gameState.activePowerUps.set(powerupType, Date.now() + 10000); // 10 seconds
        
        // Update power-ups display
        this.updatePowerUpsDisplay();
    }

    updatePowerUpsDisplay() {
        this.powerupsList.innerHTML = '';
        
        if (this.gameState.activePowerUps.size === 0) {
            this.powerupsList.innerHTML = `
                <div class="power-up-slot empty">
                    <i class="fas fa-plus"></i>
                    <span>No active power-ups</span>
                </div>
            `;
            return;
        }
        
        this.gameState.activePowerUps.forEach((expiry, powerup) => {
            const timeLeft = Math.max(0, Math.ceil((expiry - Date.now()) / 1000));
            
            const powerupElement = document.createElement('div');
            powerupElement.className = 'power-up-slot active';
            powerupElement.innerHTML = `
                <div class="powerup-icon ${powerup}">
                    <i class="fas fa-${this.getPowerUpIcon(powerup)}"></i>
                </div>
                <div class="powerup-timer">${timeLeft}s</div>
            `;
            
            this.powerupsList.appendChild(powerupElement);
        });
    }

    getPowerUpIcon(powerup) {
        const icons = {
            timefreeze: 'clock',
            multiplier: 'times',
            magnet: 'magnet'
        };
        return icons[powerup] || 'star';
    }

    showTutorial() {
        this.tutorialModal.style.display = 'flex';
        setTimeout(() => {
            this.tutorialModal.setAttribute('aria-hidden', 'false');
        }, 10);
    }

    hideModal(modal) {
        modal.setAttribute('aria-hidden', 'true');
        setTimeout(() => {
            if (modal.getAttribute('aria-hidden') === 'true') {
                modal.style.display = 'none';
            }
        }, 300);
    }

    startFromTutorial() {
        this.hideModal(this.tutorialModal);
        this.startGame();
    }

    shareScore() {
        const score = this.gameState.score;
        const text = `I scored ${score} points in Click n' Pop! Can you beat my score?`;
        
        if (navigator.share) {
            navigator.share({
                title: 'Click n\' Pop Score',
                text: text,
                url: window.location.href
            });
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(text).then(() => {
                this.showFeedback('Score copied to clipboard!', 'success');
            });
        }
    }

    resetGameState() {
        this.gameState = {
            isPlaying: false,
            isPaused: false,
            score: 0,
            timeLeft: 60,
            level: 1,
            combo: 1,
            comboStreak: 0,
            comboTimeout: null,
            accuracy: {
                hits: 0,
                total: 0,
                value: 0
            },
            bubblesPopped: 0,
            bubblesMissed: 0,
            gameSpeed: 1.0,
            activePowerUps: new Map(),
            spawnInterval: null,
            animationFrame: null,
            lastUpdate: 0,
            bubbles: new Set()
        };
    }

    clearGame() {
        // Stop all intervals and animations
        clearInterval(this.gameState.spawnInterval);
        cancelAnimationFrame(this.gameState.animationFrame);
        
        // Remove all bubbles from DOM
        this.gameState.bubbles.forEach(bubble => {
            if (bubble.el && bubble.el.parentNode) {
                bubble.el.parentNode.removeChild(bubble.el);
            }
        });
        
        this.gameState.bubbles.clear();
        
        // Clear game area
        const popups = this.gameArea.querySelectorAll('.pop-effect, .score-popup');
        popups.forEach(el => el.remove());
    }

    createFloatingBubbles() {
        // Create decorative floating bubbles for the hero section
        const container = document.querySelector('.floating-game-bubbles');
        if (!container) return;
        
        for (let i = 0; i < 10; i++) {
            const bubble = document.createElement('div');
            bubble.className = 'floating-bubble';
            
            const size = 20 + Math.random() * 40;
            const duration = 3 + Math.random() * 4;
            const delay = Math.random() * 2;
            
            bubble.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                border-radius: 50%;
                background: radial-gradient(circle at 30% 30%, 
                    rgba(255,255,255,0.8) 0%, 
                    rgba(${Math.random() * 255},${Math.random() * 255},255,0.3) 70%);
                animation: float ${duration}s ease-in-out ${delay}s infinite alternate;
                opacity: ${0.3 + Math.random() * 0.4};
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
            `;
            
            container.appendChild(bubble);
        }
    }
}

// Bubble Class
class GameBubble {
    constructor(config) {
        this.x = config.x;
        this.y = config.y;
        this.radius = config.radius;
        this.speed = config.speed;
        this.color = config.color;
        this.type = config.type;
        this.points = config.points;
        this.effect = config.effect;
        
        this.createElement();
        this.addEventListeners();
    }
    
    createElement() {
        this.el = document.createElement('div');
        this.el.className = `game-bubble ${this.type}`;
        
        // Base styles
        this.el.style.cssText = `
            position: absolute;
            left: ${this.x - this.radius}px;
            top: ${this.y - this.radius}px;
            width: ${this.radius * 2}px;
            height: ${this.radius * 2}px;
            border-radius: 50%;
            background: ${this.color};
            cursor: pointer;
            user-select: none;
            transition: transform 0.1s ease;
            box-shadow: 
                inset -5px -5px 12px rgba(0, 0, 0, 0.25),
                inset 4px 4px 10px rgba(255, 255, 255, 0.8),
                0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 5;
        `;
        
        // Add inner highlight
        const inner = document.createElement('div');
        inner.className = 'bubble-inner';
        inner.style.cssText = `
            position: absolute;
            width: ${this.radius}px;
            height: ${this.radius}px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.9), transparent 60%);
            top: 10%;
            left: 10%;
            pointer-events: none;
        `;
        
        this.el.appendChild(inner);
        
        // Add special effects based on type
        if (this.type === 'golden') {
            this.el.style.animation = 'goldenGlow 2s ease-in-out infinite';
        } else if (this.type === 'time') {
            this.el.style.animation = 'pulse 1.5s ease-in-out infinite';
        } else if (this.type === 'bomb') {
            this.el.style.animation = 'dangerPulse 1s ease-in-out infinite';
        }
        
        // Add hover effect
        this.el.addEventListener('mouseenter', () => {
            if (!window.game?.gameState?.isPaused) {
                this.el.style.transform = 'scale(1.12)';
                this.el.style.filter = 'brightness(1.1)';
            }
        });
        
        this.el.addEventListener('mouseleave', () => {
            this.el.style.transform = 'scale(1)';
            this.el.style.filter = 'brightness(1)';
        });
    }
    
    addEventListeners() {
        this.el.addEventListener('click', (e) => {
            e.stopPropagation();
            if (window.game && window.game.handleBubbleClick) {
                window.game.handleBubbleClick(this);
            }
        });
    }
}

// Initialize game when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.game = new BubbleGame();
    
    // Add CSS animations if not already present
    if (!document.querySelector('#game-animations')) {
        const style = document.createElement('style');
        style.id = 'game-animations';
        style.textContent = `
            @keyframes float {
                0% { transform: translateY(0) rotate(0deg); }
                100% { transform: translateY(-20px) rotate(10deg); }
            }
            
            @keyframes goldenGlow {
                0%, 100% { box-shadow: 0 0 20px rgba(255, 215, 0, 0.5); }
                50% { box-shadow: 0 0 40px rgba(255, 215, 0, 0.8); }
            }
            
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }
            
            @keyframes dangerPulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.7; }
            }
            
            .feedback-info { color: #4a90e2; }
            .feedback-success { color: #00FF88; }
            .feedback-warning { color: #FFA500; }
            .feedback-danger { color: #FF4444; }
            
            .power-up-slot {
                width: 100px;
                height: 100px;
                background: rgba(255,255,255,0.1);
                border: 2px dashed rgba(255,255,255,0.3);
                border-radius: 12px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                color: white;
                position: relative;
                overflow: hidden;
            }
            
            .power-up-slot.empty {
                color: rgba(255,255,255,0.5);
            }
            
            .power-up-slot.active {
                border-style: solid;
                border-color: #00FF88;
                background: rgba(0, 255, 136, 0.1);
            }
            
            .powerup-timer {
                position: absolute;
                bottom: 5px;
                right: 5px;
                background: rgba(0,0,0,0.5);
                color: white;
                padding: 2px 6px;
                border-radius: 10px;
                font-size: 0.8rem;
            }
        `;
        document.head.appendChild(style);
    }
});