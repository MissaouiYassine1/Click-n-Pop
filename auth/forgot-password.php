<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/exceptions.php';

$title = "Mot de passe oublié - Click n' Pop";
$bodyClass = "auth-page forgot-password-page";

ob_start();
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <a href="/" class="auth-logo">
                <i class="fas fa-bubbles"></i>
                <span>Click n' Pop</span>
            </a>
            <h1>Mot de passe oublié</h1>
            <p class="auth-subtitle">
                Entrez votre adresse email pour recevoir un lien de réinitialisation
            </p>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Si cet email existe, vous recevrez un lien de réinitialisation.
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            Une erreur est survenue. Veuillez réessayer.
        </div>
        <?php endif; ?>

        <form method="POST" action="/auth/reset-password.php" class="auth-form">
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                    Adresse email
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    autofocus
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-paper-plane"></i>
                Envoyer le lien
            </button>

            <div class="auth-switch">
                <a href="/auth">
                    <i class="fas fa-arrow-left"></i>
                    Retour à la connexion
                </a>
            </div>
        </form>

        <div class="security-notice">
            <i class="fas fa-shield-alt"></i>
            <p>
                <strong>Sécurité :</strong> Le lien de réinitialisation expirera dans 1 heure 
                et ne pourra être utilisé qu'une seule fois.
            </p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include "../templates/layout.php";
?>