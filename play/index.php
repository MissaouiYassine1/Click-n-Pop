<?php 
$title = "Play - Click n' Pop";

require __DIR__ . '/../components/Bubble.php';

ob_start();
?>
<div class="hero">
    <h1>Play the Game</h1>
    <p>Get ready to pop some bubbles!</p>
</div>
<div class="play-container">
    <div>
        

        <button id="toggle-btn">Start Game</button>
        <!-- <button id="stop">Stop Game</button> -->
        <div id="game-area">
            <!-- Game will be rendered here -->
        </div>
    </div>
    <aside>
        <h2>Score:<span id='score-display'>0 </span><span> points</span></h2>
        
        <h2>Accuracy:<span id='accuracy'>0</span></h2>
        
    </aside>
    
</div>
<script src="../assets/js/game.js"></script>
<?php
$content = ob_get_clean();
include "../templates/layout.php";
?>