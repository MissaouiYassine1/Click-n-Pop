<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/exceptions.php';

// Définir la page
$isLoginPage = true;
$title = "Connexion - Click n' Pop";
$metaDescription = "Connectez-vous à Click n' Pop pour sauvegarder vos scores, participer au classement et débloquer des réalisations !";
$bodyClass = "auth-page login-page";

// Vérifier si l'utilisateur est déjà connecté
if (isLoggedIn()) {
    header('Location: /');
    exit;
}

// Déterminer le mode (login ou register)
$mode = $_GET['mode'] ?? 'login';
$isRegisterMode = $mode === 'register';

if ($isRegisterMode) {
    $title = "Inscription - Click n' Pop";
    $bodyClass = "auth-page register-page";
}

// Traitement du formulaire
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['login'])) {
            // Connexion
            $result = auth()->login(
                $_POST['username'] ?? '',
                $_POST['password'] ?? '',
                isset($_POST['remember_me'])
            );
            
            if ($result['success']) {
                header('Location: /');
                exit;
            }
            
        } elseif (isset($_POST['register'])) {
            // Inscription
            $result = auth()->register(
                $_POST['username'] ?? '',
                $_POST['email'] ?? '',
                $_POST['password'] ?? '',
                $_POST['confirm_password'] ?? ''
            );
            
            if ($result['success']) {
                $success = true;
                // Redirection après 3 secondes
                header('Refresh: 3; url=/');
            }
        }
        
    } catch (ValidationException $e) {
        $errors = $e->getDetails();
    } catch (AuthException $e) {
        $errors['general'] = $e->getMessage();
    } catch (AppException $e) {
        $errors['general'] = $e->getMessage();
    }
}

ob_start();
?>
<link rel="stylesheet" href="../assets/css/auth.css"/>
<div class="auth-container">
    <div class="auth-card">
        <!-- En-tête avec logo -->
        <div class="auth-header">
            <a href="/" class="auth-logo">
                <i class="fas fa-bubbles"></i>
                <span>Click n' Pop</span>
            </a>
            <h1><?= $isRegisterMode ? 'Créer un compte' : 'Se connecter' ?></h1>
            <p class="auth-subtitle">
                <?= $isRegisterMode 
                    ? 'Rejoignez la communauté et devenez un maître du pop !' 
                    : 'Accédez à votre compte pour continuer votre aventure' 
                ?>
            </p>
        </div>

        <!-- Messages d'erreur/succès -->
        <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($errors['general']) ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Compte créé avec succès ! Redirection vers l'accueil...
        </div>
        <?php endif; ?>

        <!-- Formulaire de connexion -->
        <?php if (!$isRegisterMode): ?>
        <form method="POST" class="auth-form">
            <input type="hidden" name="login" value="1">
            
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i>
                    Nom d'utilisateur ou Email
                </label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    required
                    autofocus
                    class="<?= !empty($errors['username']) ? 'error' : '' ?>"
                >
                <?php if (!empty($errors['username'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($errors['username']) ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Mot de passe
                </label>
                <div class="password-input">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
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
            </div>

            <div class="form-options">
                <label class="checkbox">
                    <input type="checkbox" name="remember_me" value="1">
                    <span>Se souvenir de moi</span>
                </label>
                <a href="/auth/forgot-password.php" class="forgot-password">
                    Mot de passe oublié ?
                </a>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter
            </button>

            <div class="auth-divider">
                <span>ou</span>
            </div>

            <div class="social-login">
                <button type="button" class="btn btn-google">
                    <i class="fab fa-google"></i>
                    Continuer avec Google
                </button>
                <button type="button" class="btn btn-github">
                    <i class="fab fa-github"></i>
                    Continuer avec GitHub
                </button>
            </div>

            <div class="auth-switch">
                Pas encore de compte ? 
                <a href="?mode=register">S'inscrire</a>
            </div>
        </form>
        <?php endif; ?>

        <!-- Formulaire d'inscription -->
        <?php if ($isRegisterMode): ?>
        <form method="POST" class="auth-form">
            <input type="hidden" name="register" value="1">
            
            <div class="form-group">
                <label for="reg_username">
                    <i class="fas fa-user"></i>
                    Nom d'utilisateur
                </label>
                <input 
                    type="text" 
                    id="reg_username" 
                    name="username" 
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    required
                    autofocus
                    class="<?= !empty($errors['username']) ? 'error' : '' ?>"
                >
                <?php if (!empty($errors['username'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($errors['username']) ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="reg_email">
                    <i class="fas fa-envelope"></i>
                    Adresse email
                </label>
                <input 
                    type="email" 
                    id="reg_email" 
                    name="email" 
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                    class="<?= !empty($errors['email']) ? 'error' : '' ?>"
                >
                <?php if (!empty($errors['email'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($errors['email']) ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="reg_password">
                        <i class="fas fa-lock"></i>
                        Mot de passe
                    </label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="reg_password" 
                            name="password" 
                            required
                            class="<?= !empty($errors['password']) ? 'error' : '' ?>"
                        >
                        <button type="button" class="toggle-password" data-target="reg_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?php if (!empty($errors['password'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($errors['password']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="reg_confirm_password">
                        <i class="fas fa-lock"></i>
                        Confirmer le mot de passe
                    </label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="reg_confirm_password" 
                            name="confirm_password" 
                            required
                            class="<?= !empty($errors['confirm_password']) ? 'error' : '' ?>"
                        >
                        <button type="button" class="toggle-password" data-target="reg_confirm_password">
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
            </div>

            <div class="form-group">
                <label class="checkbox">
                    <input type="checkbox" name="terms" value="1" required>
                    <span>
                        J'accepte les 
                        <a href="/terms" target="_blank">conditions d'utilisation</a> 
                        et la 
                        <a href="/privacy" target="_blank">politique de confidentialité</a>
                    </span>
                </label>
                <?php if (!empty($errors['terms'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($errors['terms']) ?>
                </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-user-plus"></i>
                Créer mon compte
            </button>

            <div class="auth-divider">
                <span>ou</span>
            </div>

            <div class="social-login">
                <button type="button" class="btn btn-google">
                    <i class="fab fa-google"></i>
                    S'inscrire avec Google
                </button>
                <button type="button" class="btn btn-github">
                    <i class="fab fa-github"></i>
                    S'inscrire avec GitHub
                </button>
            </div>

            <div class="auth-switch">
                Déjà un compte ? 
                <a href="?mode=login">Se connecter</a>
            </div>
        </form>
        <?php endif; ?>

        <!-- Bannières de confiance -->
        <div class="trust-badges">
            <div class="badge">
                <i class="fas fa-shield-alt"></i>
                <span>100% sécurisé</span>
            </div>
            <div class="badge">
                <i class="fas fa-bolt"></i>
                <span>Connexion rapide</span>
            </div>
            <div class="badge">
                <i class="fas fa-gamepad"></i>
                <span>Progression sauvegardée</span>
            </div>
        </div>
    </div>

    <!-- Illustration -->
    <div class="auth-illustration">
        <div class="bubble-animation">
            <?php for ($i = 1; $i <= 15; $i++): ?>
            <div class="bubble" style="
                --size: <?= rand(20, 60) ?>px;
                --delay: <?= rand(0, 3000) ?>ms;
                --duration: <?= rand(3000, 8000) ?>ms;
                --x: <?= rand(0, 100) ?>%;
            "></div>
            <?php endfor; ?>
        </div>
        <div class="illustration-content">
            <h2>Rejoignez l'aventure !</h2>
            <ul class="features-list">
                <li><i class="fas fa-trophy"></i> Participez au classement mondial</li>
                <li><i class="fas fa-chart-line"></i> Suivez votre progression</li>
                <li><i class="fas fa-medal"></i> Débloquez des réalisations</li>
                <li><i class="fas fa-users"></i> Défiez vos amis</li>
                <li><i class="fas fa-cloud"></i> Sauvegarde automatique</li>
            </ul>
        </div>
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
    
    // Validation en temps réel
    const forms = document.querySelectorAll('.auth-form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validation des mots de passe pour l'inscription
            if (form.querySelector('#reg_password') && form.querySelector('#reg_confirm_password')) {
                const password = document.getElementById('reg_password').value;
                const confirmPassword = document.getElementById('reg_confirm_password').value;
                
                if (password !== confirmPassword) {
                    alert('Les mots de passe ne correspondent pas');
                    isValid = false;
                }
                
                if (password.length < 8) {
                    alert('Le mot de passe doit contenir au moins 8 caractères');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include "../templates/layout.php";
?>