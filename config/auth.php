<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/exceptions.php';

/**
 * Classe de gestion de l'authentification
 */
class Auth {
    private static $instance = null;
    private $db;
    
    // Constantes de configuration
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_TIME = 900; // 15 minutes en secondes
    const SESSION_LIFETIME = 86400; // 24 heures
    const REMEMBER_ME_LIFETIME = 2592000; // 30 jours
    
    private function __construct() {
        session_start();
        $this->db = getDB();
        
        // Vérifier et nettoyer la session
        $this->validateSession();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Auth();
        }
        return self::$instance;
    }
    
    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register($username, $email, $password, $confirmPassword) {
        try {
            // Validation des données
            $errors = $this->validateRegistration($username, $email, $password, $confirmPassword);
            if (!empty($errors)) {
                throw new ValidationException("Erreur de validation", $errors);
            }
            
            // Vérifier si l'utilisateur existe déjà
            if ($this->userExists($username, $email)) {
                throw new AuthException("Un utilisateur avec ce nom ou cet email existe déjà");
            }
            
            // Hasher le mot de passe
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Générer une couleur d'avatar aléatoire
            $avatarColors = ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];
            $avatarColor = $avatarColors[array_rand($avatarColors)];
            
            // Créer l'utilisateur
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password_hash, avatar_color, created_at) 
                VALUES (:username, :email, :password_hash, :avatar_color, NOW())
            ");
            
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => $passwordHash,
                ':avatar_color' => $avatarColor
            ]);
            
            $userId = $this->db->lastInsertId();
            
            // Créer les paramètres par défaut
            $this->createDefaultSettings($userId);
            
            // Connecter automatiquement l'utilisateur
            $this->login($username, $password, false);
            
            // Log l'inscription
            $this->logRegistration($userId, $username);
            
            return [
                'success' => true,
                'user_id' => $userId,
                'username' => $username
            ];
            
        } catch (PDOException $e) {
            throw new DatabaseException("Erreur lors de l'inscription : " . $e->getMessage());
        }
    }
    
    /**
     * Connexion d'un utilisateur
     */
    public function login($username, $password, $rememberMe = false) {
        try {
            // Vérifier les tentatives de connexion
            if ($this->isAccountLocked($username)) {
                throw new AuthException("Trop de tentatives de connexion. Veuillez réessayer dans 15 minutes.");
            }
            
            // Récupérer l'utilisateur
            $stmt = $this->db->prepare("
                SELECT id, username, email, password_hash, level, xp, profile_pic, avatar_color, is_active
                FROM users 
                WHERE (username = :identifier OR email = :identifier) 
                AND is_active = 1
            ");
            
            $stmt->execute([':identifier' => $username]);
            $user = $stmt->fetch();
            
            // Log la tentative
            $this->logLoginAttempt($username, $user && password_verify($password, $user['password_hash']));
            
            if (!$user) {
                throw new AuthException("Nom d'utilisateur ou mot de passe incorrect");
            }
            
            // Vérifier le mot de passe
            if (!password_verify($password, $user['password_hash'])) {
                throw new AuthException("Nom d'utilisateur ou mot de passe incorrect");
            }
            
            // Créer la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['level'] = $user['level'];
            $_SESSION['xp'] = $user['xp'];
            $_SESSION['profile_pic'] = $user['profile_pic'];
            $_SESSION['avatar_color'] = $user['avatar_color'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            // Gestion du "Se souvenir de moi"
            if ($rememberMe) {
                $this->setRememberMeCookie($user['id']);
            }
            
            // Mettre à jour la dernière connexion
            $this->updateLastLogin($user['id']);
            
            // Réinitialiser les tentatives de connexion
            $this->resetLoginAttempts($username);
            
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'level' => $user['level']
                ]
            ];
            
        } catch (PDOException $e) {
            throw new DatabaseException("Erreur lors de la connexion : " . $e->getMessage());
        }
    }
    
    /**
     * Déconnexion
     */
    public function logout() {
        // Supprimer les cookies "Se souvenir de moi"
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            setcookie('remember_user', '', time() - 3600, '/');
        }
        
        // Détruire la session
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        return ['success' => true];
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     */
    public function isLoggedIn() {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            // Vérifier l'expiration de la session
            if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > self::SESSION_LIFETIME) {
                $this->logout();
                return false;
            }
            return true;
        }
        
        // Vérifier le cookie "Se souvenir de moi"
        if (isset($_COOKIE['remember_token']) && isset($_COOKIE['remember_user'])) {
            return $this->validateRememberMeToken(
                $_COOKIE['remember_user'],
                $_COOKIE['remember_token']
            );
        }
        
        return false;
    }
    
    /**
     * Récupérer l'utilisateur courant
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, us.theme, us.language, us.notifications
                FROM users u
                LEFT JOIN user_settings us ON u.id = us.user_id
                WHERE u.id = :user_id
            ");
            
            $stmt->execute([':user_id' => $_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Calculer la progression vers le prochain niveau
                $user['next_level_xp'] = ($user['level'] + 1) * 1000;
                $user['xp_progress'] = ($user['xp'] % 1000) / 10; // Pourcentage
                $user['xp_current_level'] = $user['xp'] % 1000;
                
                // Ajouter l'URL de l'avatar
                $user['avatar_url'] = $this->getAvatarUrl($user);
            }
            
            return $user;
            
        } catch (PDOException $e) {
            throw new DatabaseException("Erreur lors de la récupération de l'utilisateur");
        }
    }
    
    /**
     * Mettre à jour le profil
     */
    public function updateProfile($userId, $data) {
        try {
            $allowedFields = ['username', 'email', 'country'];
            $updates = [];
            $params = [':id' => $userId];
            
            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    // Vérifier l'unicité du username/email
                    if ($field === 'username' || $field === 'email') {
                        if ($this->fieldExists($field, $value, $userId)) {
                            throw new ValidationException("Ce {$field} est déjà utilisé");
                        }
                    }
                    
                    $updates[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $value;
                    
                    // Mettre à jour la session si nécessaire
                    if ($field === 'username') {
                        $_SESSION['username'] = $value;
                    }
                }
            }
            
            if (empty($updates)) {
                throw new ValidationException("Aucune donnée à mettre à jour");
            }
            
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return ['success' => true, 'message' => 'Profil mis à jour avec succès'];
            
        } catch (PDOException $e) {
            throw new DatabaseException("Erreur lors de la mise à jour du profil");
        }
    }
    
    /**
     * Changer le mot de passe
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Récupérer le hash actuel
            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                throw new AuthException("Mot de passe actuel incorrect");
            }
            
            // Valider le nouveau mot de passe
            if (strlen($newPassword) < 8) {
                throw new ValidationException("Le mot de passe doit contenir au moins 8 caractères");
            }
            
            // Hasher le nouveau mot de passe
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Mettre à jour
            $stmt = $this->db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
            $stmt->execute([':hash' => $newHash, ':id' => $userId]);
            
            // Déconnecter toutes les sessions sauf la courante
            $this->invalidateOtherSessions($userId);
            
            return ['success' => true, 'message' => 'Mot de passe changé avec succès'];
            
        } catch (PDOException $e) {
            throw new DatabaseException("Erreur lors du changement de mot de passe");
        }
    }
    
    /**
     * Mot de passe oublié
     */
    public function forgotPassword($email) {
        try {
            // Vérifier si l'email existe
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email AND is_active = 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                // Pour des raisons de sécurité, on ne dit pas que l'email n'existe pas
                return ['success' => true, 'message' => 'Si cet email existe, vous recevrez un lien de réinitialisation'];
            }
            
            // Générer un token sécurisé
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Supprimer les anciens tokens
            $this->db->prepare("DELETE FROM password_resets WHERE email = :email")
                     ->execute([':email' => $email]);
            
            // Insérer le nouveau token
            $stmt = $this->db->prepare("
                INSERT INTO password_resets (email, token, expires_at) 
                VALUES (:email, :token, :expires)
            ");
            $stmt->execute([
                ':email' => $email,
                ':token' => $token,
                ':expires' => $expires
            ]);
            
            // Envoyer l'email (simulé pour l'exemple)
            $resetLink = "http://{$_SERVER['HTTP_HOST']}/auth/reset-password.php?token={$token}";
            
            // Dans une vraie application, vous enverriez un email ici
            // mail($email, "Réinitialisation de mot de passe", "Cliquez sur le lien : {$resetLink}");
            
            // Pour le développement, on log le lien
            error_log("Password reset link for {$email}: {$resetLink}");
            
            return ['success' => true, 'message' => 'Lien de réinitialisation envoyé (voir logs pour développement)'];
            
        } catch (PDOException $e) {
            throw new DatabaseException("Erreur lors de la demande de réinitialisation");
        }
    }
    
    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword($token, $newPassword) {
        try {
            // Vérifier le token
            $stmt = $this->db->prepare("
                SELECT email FROM password_resets 
                WHERE token = :token AND expires_at > NOW()
            ");
            $stmt->execute([':token' => $token]);
            $reset = $stmt->fetch();
            
            if (!$reset) {
                throw new AuthException("Lien de réinitialisation invalide ou expiré");
            }
            
            // Valider le nouveau mot de passe
            if (strlen($newPassword) < 8) {
                throw new ValidationException("Le mot de passe doit contenir au moins 8 caractères");
            }
            
            // Hasher le nouveau mot de passe
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Mettre à jour le mot de passe
            $stmt = $this->db->prepare("UPDATE users SET password_hash = :hash WHERE email = :email");
            $stmt->execute([':hash' => $newHash, ':email' => $reset['email']]);
            
            // Supprimer le token utilisé
            $this->db->prepare("DELETE FROM password_resets WHERE token = :token")
                     ->execute([':token' => $token]);
            
            // Invalider toutes les sessions de cet utilisateur
            $this->invalidateAllSessions($reset['email']);
            
            return ['success' => true, 'message' => 'Mot de passe réinitialisé avec succès'];
            
        } catch (PDOException $e) {
            throw new DatabaseException("Erreur lors de la réinitialisation du mot de passe");
        }
    }
    
    // ============================================
    // Méthodes privées
    // ============================================
    
    private function validateRegistration($username, $email, $password, $confirmPassword) {
        $errors = [];
        
        // Validation du nom d'utilisateur
        if (strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'] = "Le nom d'utilisateur doit contenir entre 3 et 50 caractères";
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors['username'] = "Le nom d'utilisateur ne peut contenir que des lettres, chiffres et underscores";
        }
        
        // Validation de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Adresse email invalide";
        }
        
        // Validation du mot de passe
        if (strlen($password) < 8) {
            $errors['password'] = "Le mot de passe doit contenir au moins 8 caractères";
        }
        
        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = "Les mots de passe ne correspondent pas";
        }
        
        return $errors;
    }
    
    private function userExists($username, $email) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM users 
            WHERE username = :username OR email = :email
        ");
        $stmt->execute([':username' => $username, ':email' => $email]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    private function fieldExists($field, $value, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE {$field} = :value";
        $params = [':value' => $value];
        
        if ($excludeUserId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeUserId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    private function createDefaultSettings($userId) {
        $stmt = $this->db->prepare("
            INSERT INTO user_settings (user_id) VALUES (:user_id)
        ");
        $stmt->execute([':user_id' => $userId]);
    }
    
    private function logRegistration($userId, $username) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        error_log("Registration: User {$username} (ID: {$userId}) registered from IP: {$ip}");
    }
    
    private function isAccountLocked($username) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as attempts 
            FROM login_attempts 
            WHERE (username = :username OR ip_address = :ip)
            AND attempt_time > DATE_SUB(NOW(), INTERVAL :lockout_time SECOND)
            AND success = 0
        ");
        
        $stmt->execute([
            ':username' => $username,
            ':ip' => $ip,
            ':lockout_time' => self::LOCKOUT_TIME
        ]);
        
        $result = $stmt->fetch();
        return $result['attempts'] >= self::MAX_LOGIN_ATTEMPTS;
    }
    
    private function logLoginAttempt($username, $success) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $stmt = $this->db->prepare("
            INSERT INTO login_attempts (ip_address, username, success) 
            VALUES (:ip, :username, :success)
        ");
        
        $stmt->execute([
            ':ip' => $ip,
            ':username' => $username,
            ':success' => $success ? 1 : 0
        ]);
    }
    
    private function resetLoginAttempts($username) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $this->db->prepare("
            DELETE FROM login_attempts 
            WHERE username = :username OR ip_address = :ip
        ")->execute([':username' => $username, ':ip' => $ip]);
    }
    
    private function setRememberMeCookie($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + self::REMEMBER_ME_LIFETIME;
        
        // Stocker le token dans la base de données
        $stmt = $this->db->prepare("
            UPDATE users SET remember_token = :token WHERE id = :id
        ");
        $stmt->execute([':token' => $token, ':id' => $userId]);
        
        // Définir les cookies
        setcookie('remember_user', $userId, $expires, '/', '', false, true);
        setcookie('remember_token', $token, $expires, '/', '', false, true);
    }
    
    private function validateRememberMeToken($userId, $token) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email FROM users 
                WHERE id = :id AND remember_token = :token AND is_active = 1
            ");
            
            $stmt->execute([':id' => $userId, ':token' => $token]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return false;
            }
            
            // Créer la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            return true;
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    private function validateSession() {
        // Vérifier la régénération de l'ID de session
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            // Régénérer l'ID de session toutes les 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
    
    private function updateLastLogin($userId) {
        $stmt = $this->db->prepare("
            UPDATE users SET last_login = NOW() WHERE id = :id
        ");
        $stmt->execute([':id' => $userId]);
    }
    
    private function invalidateOtherSessions($userId) {
        // Dans une vraie application, vous pourriez avoir une table des sessions actives
        // Pour cet exemple, on va simplement supprimer le token "remember me"
        $stmt = $this->db->prepare("
            UPDATE users SET remember_token = NULL WHERE id = :id
        ");
        $stmt->execute([':id' => $userId]);
    }
    
    private function invalidateAllSessions($email) {
        // Récupérer l'ID utilisateur
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $this->invalidateOtherSessions($user['id']);
        }
    }
    
    private function getAvatarUrl($user) {
        if (!empty($user['profile_pic'])) {
            return "/assets/images/profiles/{$user['profile_pic']}";
        }
        
        // Générer un avatar SVG avec la couleur personnelle
        $initials = strtoupper(substr($user['username'], 0, 2));
        $color = $user['avatar_color'] ?? '#4F46E5';
        
        // URL encodée pour un avatar SVG
        $svg = rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
            <rect width="100" height="100" fill="' . $color . '" rx="50"/>
            <text x="50" y="55" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="40" font-weight="bold">' . $initials . '</text>
        </svg>');
        
        return 'data:image/svg+xml,' . $svg;
    }
    
    /**
     * Récupérer l'historique des parties d'un utilisateur
     */
    public function getGameHistory($userId, $limit = 20, $page = 1) {
        try {
            $offset = ($page - 1) * $limit;
            
            $stmt = $this->db->prepare("
                SELECT 
                    g.*,
                    DATE_FORMAT(g.played_at, '%d/%m/%Y %H:%i') as formatted_date,
                    TIMESTAMPDIFF(DAY, g.played_at, NOW()) as days_ago
                FROM games g
                WHERE g.user_id = :user_id
                ORDER BY g.played_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $games = $stmt->fetchAll();
            
            // Compter le total
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total FROM games WHERE user_id = :user_id
            ");
            $stmt->execute([':user_id' => $userId]);
            $total = $stmt->fetch()['total'];
            
            // Calculer les statistiques
            $stats = $this->getGameStats($userId);
            
            return [
                'games' => $games,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_games' => $total,
                    'limit' => $limit
                ],
                'stats' => $stats
            ];
            
        } catch (PDOException $e) {
            throw new DatabaseException("Erreur lors de la récupération de l'historique");
        }
    }
    
    /**
     * Obtenir les statistiques de jeu
     */
    private function getGameStats($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_games,
                SUM(bubbles_popped) as total_bubbles,
                AVG(score) as avg_score,
                MAX(score) as best_score,
                AVG(accuracy) as avg_accuracy,
                SUM(duration) as total_play_time,
                AVG(duration) as avg_play_time
            FROM games 
            WHERE user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
        
        $stats = $stmt->fetch();
        
        // Formater les temps
        if ($stats['total_play_time']) {
            $hours = floor($stats['total_play_time'] / 3600);
            $minutes = floor(($stats['total_play_time'] % 3600) / 60);
            $stats['total_play_time_formatted'] = "{$hours}h {$minutes}m";
        } else {
            $stats['total_play_time_formatted'] = "0h 0m";
        }
        
        return $stats;
    }
}

// Helper function pour accéder facilement à l'authentification
function auth() {
    return Auth::getInstance();
}

// Helper function pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return auth()->isLoggedIn();
}

// Helper function pour récupérer l'utilisateur courant
function currentUser() {
    return auth()->getCurrentUser();
}

// Helper function pour rediriger si non connecté
function requireLogin($redirectTo = '/auth') {
    if (!isLoggedIn()) {
        header("Location: {$redirectTo}");
        exit;
    }
}

// Helper function pour rediriger si déjà connecté
function requireGuest($redirectTo = '/') {
    if (isLoggedIn()) {
        header("Location: {$redirectTo}");
        exit;
    }
}
?>