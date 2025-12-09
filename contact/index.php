<?php

$title = "Contact Us - Click n' Pop";

ob_start();
?>

<div class="contact-page">

    <!-- HERO SECTION -->
    <section class="contact-hero">
        <h1>Contact <span>Click n' Pop</span></h1>
        <p>
            We'd love to hear from you! Whether you have questions, feedback,
            or just want to say hello, feel free to reach out.
        </p>
    </section>

    <!-- CONTACT FORM -->
    <section class="contact-form-wrapper">
        <h2 class="section-title">Get in Touch</h2>

        <form id="contact-form" class="contact-form" method="POST">
            <fieldset>
                <legend>Contact Us</legend>

                <div class="form-group">
                    <label for="name">Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="user_name" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="user_email" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea 
                        id="message" 
                        name="message" 
                        rows="5" 
                        required
                    ></textarea>
                </div>

                <button type="submit" class="btn hover">Send Message</button>
            </fieldset>
        </form>
    </section>

</div>

<!-- EmailJS SDK -->
<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>

<script>
   // Initialize EmailJS with your public key
   (function() {
      emailjs.init("6e9uh7VVm4_oXMliQ"); // EX: Y5F3Ax0n8lG2Kd9H
   })();

   const form = document.getElementById('contact-form');

   form.addEventListener('submit', function(event) {
       event.preventDefault();

       emailjs.sendForm(
           "service_rnzlp1g",     // EX: service_rnzlp1g
           "template_nbg4ku5",     // TON TEMPLATE ID
           this
       )
       .then(() => {
           alert("Message sent successfully!");
           form.reset();
       })
       .catch(error => {
           alert("Failed to send the message. Please try again.");
           console.error("EmailJS Error:", error);
       });
   });
</script>

<?php
$content = ob_get_clean();
include "../templates/layout.php";
?>
