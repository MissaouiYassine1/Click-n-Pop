<?php

$title = "Home - Click n' Pop";


ob_start();
?>

<div class="home-container">

    <section class="hero">
        <h1>Welcome to <span>Click n' Pop</span></h1>
        <p>The most addictive bubble-popping click game!</p>

        <a href="play/index.php" class="btn-play">
            Start Playing
        </a>
    </section>

    <section class="features">
        <h2>Why You’ll Love This Game</h2>

        <div class="feature-list">

            <div class="feature">
                <img src="assets/images/bubble.png" alt="Bubble">
                <h3>Fun Gameplay</h3>
                <p>Click the bubbles and pop them before time runs out!</p>
            </div>

            <div class="feature">
                <img src="assets/images/trophy.png" alt="Leaderboard">
                <h3>Leaderboard</h3>
                <p>Compete with other players and rise to the top.</p>
            </div>

            <div class="feature">
                <img src="assets/images/sound.png" alt="Sound">
                <h3>Satisfying Sounds</h3>
                <p>Enjoy smooth popping effects and gameplay sounds.</p>
            </div>

        </div>
    </section>

    <section class="cta">
        <h2>Are you ready to pop?</h2>
        <a href="play/index.php" class="btn-start">
            Play Now
        </a>
    </section>

</div>

<?php

$content = ob_get_clean();


include "templates/layout.php";