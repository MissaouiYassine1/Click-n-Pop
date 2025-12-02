<?php

$title = "About Us - Click n' Pop";


ob_start();
?>
<div class="about-container">

    <section class="about-hero">
        <h1>About <span>Click n' Pop</span></h1>
        <p>Click n' Pop is a fun and addictive bubble-popping click game developed to provide endless entertainment. Our mission is to create a simple yet engaging experience that players of all ages can enjoy.</p>
    </section>

    <section class="about-mission">
        <h2>Our Mission</h2>
        <p>At Click n' Pop, we believe in the power of simple pleasures. Our mission is to deliver a game that is easy to pick up but hard to put down, offering players a delightful way to pass the time and challenge themselves.</p>
    </section>

    <section class="about-team">
        <h2>Meet the Team</h2>
        <p>Click n' Pop was created by a passionate team of developers and designers who love gaming. We are dedicated to continuously improving the game and adding new features based on player feedback.</p>
    </section>

    <section class="about-contact">
        <h2>Get in Touch</h2>
        <p>If you have any questions, feedback, or just want to say hello, feel free to reach out to us through our <a href="/contact">Contact Page</a>. We would love to hear from you!</p>
    </section>
</div>
<?php
$content = ob_get_clean();
include "../templates/layout.php";
?>
