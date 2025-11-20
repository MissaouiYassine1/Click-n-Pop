<?php 
$title = "Play - Click n' Pop";

ob_start();
?>

<div class="play-container">
    <h1>Play the Game</h1>
    <p>Get ready to pop some bubbles!</p>
    <!-- Game content goes here -->
     
</div>

<?php
$content = ob_get_clean();

include "../templates/layout.php";
?>