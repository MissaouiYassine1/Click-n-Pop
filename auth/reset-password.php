<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/exceptions.php';

$title = "Réinitialisation - Click n' Pop";
$bodyClass = "auth-page reset-password-page";

// Vérifier le token
$token = $_GET['token'] ?? '';
$validToken = false;

if ($token) {
    try {
        // Vérifier si le token est valide
        $db = getDB();
        $stmt = $db->prepare("
            SELECT email FROM password_resets 
            WHERE token = :token AND expires_at > NOW()
        ");
        $stmt->execute([':token' => $token]);
        $validToken = (bool) $stmt->fetch();
        
    } catch (Exception $e) {
        // Token invalide
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    try {
        $result = auth()->resetPassword(
            $token,
            $_POST['password'] ?? ''
        );
        
        if ($result['success']) {
            header('Location: /auth?message=password_reset');
            exit;
        }
        
    } catch (ValidationException $e) {
        $errors = $e->getDetails();
    } catch (AuthException $e) {
        $error = $e->getMessage();
    }
}

ob_start();
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <a href="/" class="auth-logo">
                <i class="fas fa-bubbles"></i>
                <span>Click n' Pop</span>
            </a>
            
            <?php if (!$validToken): ?>
            <h1>Lien invalide</h1>
            <p class="auth-subtitle">
                Ce lien de réinitialisation est invalide ou a expiré.
            </p>
            <?php else: ?>
            <h1>Nouveau mot de passe</h1>
            <p class="auth-subtitle">
                Choisissez un nouveau mot de passe sécurisé
            </p>
            <?php endif; ?>
        </div>

        <?php if (!$validToken): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            Ce lien de réinitialisation est invalide ou a expiré.
            Veuillez demander un nouveau lien.
        </div>
        
        <div class="auth-actions">
            <a href="/auth/forgot-password.php" class="btn btn-primary btn-block">
                <i class="fas fa-redo"></i>
                Nouveau lien de réinitialisation
            </a>
            <a href="/auth" class="btn btn-outline btn-block">
                <i class="fas fa-sign-in-alt"></i>
                Retour à la connexion
            </a>
        </div>
        
        <?php else: ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Nouveau mot de passe
                </label>
                <div class="password-input">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        minlength="8"
                        class="<?= !empty($errors['password']) ? 'error' : '' ?>"
                    >
                    <button type="button" class="toggle-password" data-target="password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <?php if (!empty($errors['password'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($errors['password']) ?>
                </div>
                <?php endif; ?>
                <div class="password-hint">
                    <i class="fas fa-info-circle"></i>
                    Minimum 8 caractères
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <i class="fas fa-lock"></i>
                    Confirmer le mot de passe
                </label>
                <div class="password-input">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                        class="<?= !empty($errors['confirm_password']) ? 'error' : '' ?>"
                    >
                    <button type="button" class="toggle-password" data-target="confirm_password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <?php if (!empty($errors['confirm_password'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($errors['confirm_password']) ?>
                </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-save"></i>
                Enregistrer le nouveau mot de passe
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Validation des mots de passe
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères');
                return false;
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include "../templates/layout.php";
?>