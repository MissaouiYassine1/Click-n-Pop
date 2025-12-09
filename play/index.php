<?php
session_start();
$title = "Play Now - Click n' Pop | Bubble Popping Game";
$metaDescription = "Play Click n' Pop - The ultimate bubble popping game! Challenge your reflexes, use power-ups, climb leaderboards and pop your way to victory!";
$bodyClass = "game-page";
$pageScripts = ['../assets/js/game.js'];
ob_start();
?>
<link rel="stylesheet" href="../assets/css/game.css">
<!-- Game Page Hero -->
<section class="game-hero" aria-labelledby="game-title">
    <div class="hero-content">
        <h1 id="game-title" class="hero-title">
            <span class="title-gradient">Ready to Pop?</span>
            <span class="title-sub">Challenge Your Reflexes!</span>
        </h1>
        <p class="hero-description">
            Click bubbles, earn points, unlock power-ups, and climb the leaderboards. 
            How high can you score?
        </p>
        <div class="hero-stats">
            <div class="stat-badge">
                <i class="fas fa-clock"></i>
                <span>60s Games</span>
            </div>
            <div class="stat-badge">
                <i class="fas fa-bolt"></i>
                <span>Fast-paced</span>
            </div>
            <div class="stat-badge">
                <i class="fas fa-trophy"></i>
                <span>Global Rankings</span>
            </div>
        </div>
    </div>
    <div class="hero-visual">
        <div class="floating-game-bubbles">
            <div class="game-bubble-demo golden"></div>
            <div class="game-bubble-demo normal"></div>
            <div class="game-bubble-demo time"></div>
        </div>
    </div>
</section>

<!-- Game Container -->
<div class="game-container">
    <!-- Game Area -->
    <main class="game-main" role="main">
        <!-- Game Controls -->
        <div class="game-controls">
            <div class="controls-left">
                <button id="start-game" class="btn btn-primary btn-lg btn-glow" aria-label="Start game">
                    <i class="fas fa-play-circle"></i>
                    Start Game
                </button>
                <button id="pause-game" class="btn btn-secondary btn-lg" aria-label="Pause game" disabled>
                    <i class="fas fa-pause"></i>
                    Pause
                </button>
                <button id="how-to-play" class="btn btn-outline btn-lg" aria-label="How to play instructions">
                    <i class="fas fa-question-circle"></i>
                    How to Play
                </button>
            </div>
            
            <div class="controls-right">
                <div class="game-stat">
                    <i class="fas fa-clock"></i>
                    <span>Time: <strong id="timer-display">60</strong>s</span>
                </div>
                <div class="game-stat">
                    <i class="fas fa-layer-group"></i>
                    <span>Level: <strong id="level-display">1</strong></span>
                </div>
                <div class="game-stat">
                    <i class="fas fa-bolt"></i>
                    <span>Speed: <strong id="speed-display">Normal</strong></span>
                </div>
            </div>
        </div>

        <!-- Game Arena -->
        <div class="game-arena" id="game-arena" aria-label="Bubble popping game arena" role="application">
            <div class="arena-overlay" id="arena-overlay">
                <div class="overlay-content">
                    <h3>Click Start to Play!</h3>
                    <p>Pop as many bubbles as you can before time runs out</p>
                    <div class="bubble-types-demo">
                        <div class="bubble-demo-item">
                            <div class="demo-bubble normal"></div>
                            <span>Normal (+10)</span>
                        </div>
                        <div class="bubble-demo-item">
                            <div class="demo-bubble golden"></div>
                            <span>Golden (+50)</span>
                        </div>
                        <div class="bubble-demo-item">
                            <div class="demo-bubble time"></div>
                            <span>Time (+5s)</span>
                        </div>
                        <div class="bubble-demo-item">
                            <div class="demo-bubble bomb"></div>
                            <span>Avoid Bomb!</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Bubbles will be generated here -->
        </div>

        <!-- Game Feedback -->
        <div class="game-feedback" id="game-feedback" aria-live="polite" role="status">
            <div class="feedback-message" id="feedback-message">Ready when you are!</div>
            <div class="combo-display" id="combo-display">
                <span class="combo-text">COMBO</span>
                <span class="combo-multiplier">x1</span>
            </div>
        </div>

        <!-- Power-ups -->
        <div class="power-ups-bar">
            <h3 class="power-ups-title">
                <i class="fas fa-magic"></i>
                Active Power-ups
            </h3>
            <div class="power-ups-list" id="power-ups-list">
                <div class="power-up-slot empty">
                    <i class="fas fa-plus"></i>
                    <span>No active power-ups</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Game Sidebar -->
    <aside class="game-sidebar" role="complementary" aria-label="Game statistics">
        <!-- Player Stats -->
        <div class="stats-card">
            <h3 class="stats-title">
                <i class="fas fa-chart-line"></i>
                Game Stats
            </h3>
            
            <div class="stat-item">
                <div class="stat-label">
                    <i class="fas fa-star"></i>
                    Score
                </div>
                <div class="stat-value" id="score-display">0</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-label">
                    <i class="fas fa-bullseye"></i>
                    Accuracy
                </div>
                <div class="stat-value" id="accuracy-display">0%</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-label">
                    <i class="fas fa-bubbles"></i>
                    Bubbles Popped
                </div>
                <div class="stat-value" id="bubbles-popped">0</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-label">
                    <i class="fas fa-fire"></i>
                    Combo Multiplier
                </div>
                <div class="stat-value" id="combo-multiplier">x1.0</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-label">
                    <i class="fas fa-crown"></i>
                    Personal Best
                </div>
                <div class="stat-value" id="personal-best">1,250</div>
            </div>
        </div>

        <!-- Power-ups Shop -->
        <div class="powerups-card">
            <h3 class="powerups-title">
                <i class="fas fa-store"></i>
                Power-ups Shop
            </h3>
            <div class="powerups-grid">
                <div class="powerup-item" data-powerup="timefreeze">
                    <div class="powerup-icon timefreeze">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="powerup-info">
                        <h4>Time Freeze</h4>
                        <p>Freeze time for 5s</p>
                    </div>
                    <div class="powerup-cost">100</div>
                </div>
                
                <div class="powerup-item" data-powerup="multiplier">
                    <div class="powerup-icon multiplier">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="powerup-info">
                        <h4>2x Multiplier</h4>
                        <p>Double points for 10s</p>
                    </div>
                    <div class="powerup-cost">200</div>
                </div>
                
                <div class="powerup-item" data-powerup="magnet">
                    <div class="powerup-icon magnet">
                        <i class="fas fa-magnet"></i>
                    </div>
                    <div class="powerup-info">
                        <h4>Bubble Magnet</h4>
                        <p>Attract bubbles for 15s</p>
                    </div>
                    <div class="powerup-cost">150</div>
                </div>
            </div>
        </div>

        <!-- Game Over Modal -->
        <div class="game-over-modal" id="game-over-modal" aria-hidden="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>
                        <i class="fas fa-gamepad"></i>
                        Game Over!
                    </h3>
                    <button class="modal-close" aria-label="Close modal">&times;</button>
                </div>
                
                <div class="modal-body">
                    <div class="final-score">
                        <div class="score-label">Final Score</div>
                        <div class="score-value" id="final-score-display">0</div>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-box">
                            <i class="fas fa-bubbles"></i>
                            <span id="final-bubbles">0</span>
                            <small>Bubbles</small>
                        </div>
                        <div class="stat-box">
                            <i class="fas fa-bullseye"></i>
                            <span id="final-accuracy">0%</span>
                            <small>Accuracy</small>
                        </div>
                        <div class="stat-box">
                            <i class="fas fa-fire"></i>
                            <span id="final-combo">x1</span>
                            <small>Max Combo</small>
                        </div>
                    </div>
                    
                    <div class="actions">
                        <button id="play-again" class="btn btn-primary btn-lg">
                            <i class="fas fa-redo"></i>
                            Play Again
                        </button>
                        <button id="share-score" class="btn btn-outline btn-lg">
                            <i class="fas fa-share-alt"></i>
                            Share Score
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</div>

<!-- Audio Elements -->
<div class="audio-elements" aria-hidden="true">
    <audio id="pop-sound" preload="auto">
        <source src="../assets/sounds/yes.mp3" type="audio/mpeg">
    </audio>
    <audio id="golden-pop" preload="auto">
        <source src="../assets/sounds/golden.mp3" type="audio/mpeg">
    </audio>
    <audio id="bomb-sound" preload="auto">
        <source src="../assets/sounds/ops.mp3" type="audio/mpeg">
    </audio>
    <audio id="powerup-sound" preload="auto">
        <source src="../assets/sounds/yes.mp3" type="audio/mpeg">
    </audio>
    <audio id="game-start" preload="auto">
        <source src="../assets/sounds/start.mp3" type="audio/mpeg">
    </audio>
    <audio id="game-over-sound" preload="auto">
        <source src="../assets/sounds/game-over.mp3" type="audio/mpeg">
    </audio>
</div>

<!-- How to Play Modal -->
<div class="tutorial-modal" id="tutorial-modal" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fas fa-graduation-cap"></i>
                How to Play
            </h3>
            <button class="modal-close" aria-label="Close tutorial">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="tutorial-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>Click Bubbles</h4>
                    <p>Click on bubbles to pop them and earn points</p>
                </div>
            </div>
            
            <div class="tutorial-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Special Bubbles</h4>
                    <div class="bubble-types">
                        <div class="bubble-type">
                            <div class="bubble-example normal"></div>
                            <span>Normal (+10)</span>
                        </div>
                        <div class="bubble-type">
                            <div class="bubble-example golden"></div>
                            <span>Golden (+50)</span>
                        </div>
                        <div class="bubble-type">
                            <div class="bubble-example time"></div>
                            <span>Time (+5s)</span>
                        </div>
                        <div class="bubble-type">
                            <div class="bubble-example bomb"></div>
                            <span>Bomb (-20)</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tutorial-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Build Combos</h4>
                    <p>Pop bubbles quickly to build combo multipliers</p>
                </div>
            </div>
            
            <div class="tutorial-step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h4>Use Power-ups</h4>
                    <p>Collect coins to unlock special power-ups</p>
                </div>
            </div>
            
            <div class="tutorial-actions">
                <button id="start-tutorial-game" class="btn btn-primary">
                    <i class="fas fa-play"></i>
                    Start Playing
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include "../templates/layout.php";
?>