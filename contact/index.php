<?php

$title = "Contact US - Click n' Pop";


ob_start();
?>
<div class="contact-container">

    <section class="contact-hero">
        <h1>Contact <span>Click n' Pop</span></h1>
        <p>We'd love to hear from you! Whether you have questions, feedback, or just want to say hi, feel free to reach out.</p>
    </section>

    <section class="contact-form-section">
        <h2>Get in Touch</h2>

        <form action="/contact/submit" method="POST" class="contact-form">
            <fieldset>
                <legend>
                    Contact Us
                </legend>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="5" required></textarea>

                <button type="submit" class="btn-submit">Send Message</button>
            </fieldset>
            
        </form>
    </section>
</div>
<?php
$content = ob_get_clean();
include "../templates/layout.php";
?>
