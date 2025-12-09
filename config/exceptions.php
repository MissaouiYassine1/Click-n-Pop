<?php
/**
 * Exceptions personnalisées pour l'application
 */

class AppException extends Exception {
    protected $errorCode;
    protected $details;

    public function __construct($message = "", $code = 0, $errorCode = "APP_ERROR", $details = [], Exception $previous = null) {
        $this->errorCode = $errorCode;
        $this->details = $details;
        parent::__construct($message, $code, $previous);
    }

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getDetails() {
        return $this->details;
    }

    public function toArray() {
        return [
            'error' => $this->errorCode,
            'message' => $this->getMessage(),
            'details' => $this->details,
            'trace' => DEBUG_MODE ? $this->getTrace() : null
        ];
    }
}

class ValidationException extends AppException {
    public function __construct($message = "Validation error", $errors = [], $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, "VALIDATION_ERROR", $errors, $previous);
    }
}

class AuthException extends AppException {
    public function __construct($message = "Authentication error", $code = 0, $errorCode = "AUTH_ERROR", Exception $previous = null) {
        parent::__construct($message, $code, $errorCode, [], $previous);
    }
}

class DatabaseException extends AppException {
    public function __construct($message = "Database error", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, "DATABASE_ERROR", [], $previous);
    }
}

class NotFoundException extends AppException {
    public function __construct($message = "Resource not found", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, "NOT_FOUND", [], $previous);
    }
}

class PermissionException extends AppException {
    public function __construct($message = "Permission denied", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, "PERMISSION_DENIED", [], $previous);
    }
}

// Handler d'exceptions global
class ExceptionHandler {
    public static function handle(Exception $e) {
        // Log l'erreur
        error_log("[" . date('Y-m-d H:i:s') . "] " . get_class($e) . ": " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        
        // Déterminer le type de réponse
        if (php_sapi_name() === 'cli') {
            // Mode CLI
            echo "Error: " . $e->getMessage() . "\n";
            if ($e instanceof AppException && DEBUG_MODE) {
                print_r($e->toArray());
            }
        } else {
            // Mode web
            if ($e instanceof AppException) {
                $data = $e->toArray();
                
                // Si c'est une requête AJAX
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    http_response_code($e->getCode() ?: 400);
                    echo json_encode($data);
                    exit;
                }
                
                // Sinon, afficher une page d'erreur
                self::renderErrorPage($e);
            } else {
                // Erreur non gérée
                self::renderErrorPage($e);
            }
        }
    }
    
    private static function renderErrorPage(Exception $e) {
        $isDebug = defined('DEBUG_MODE') && DEBUG_MODE;
        
        if ($e instanceof NotFoundException) {
            http_response_code(404);
            $title = "Page non trouvée";
            $message = "La page que vous recherchez n'existe pas.";
        } elseif ($e instanceof PermissionException) {
            http_response_code(403);
            $title = "Accès refusé";
            $message = "Vous n'avez pas la permission d'accéder à cette page.";
        } elseif ($e instanceof AuthException) {
            http_response_code(401);
            $title = "Authentification requise";
            $message = $e->getMessage();
        } else {
            http_response_code(500);
            $title = "Erreur serveur";
            $message = "Une erreur est survenue. Veuillez réessayer plus tard.";
        }
        
        // Rendu simple de la page d'erreur
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($title) ?> - Click n' Pop</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .error-container {
                    background: white;
                    border-radius: 20px;
                    padding: 40px;
                    max-width: 500px;
                    width: 100%;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    text-align: center;
                }
                .error-icon {
                    font-size: 80px;
                    color: #667eea;
                    margin-bottom: 20px;
                }
                h1 { color: #333; margin-bottom: 10px; }
                p { color: #666; margin-bottom: 30px; line-height: 1.6; }
                .debug-info {
                    background: #f8f9fa;
                    border-left: 4px solid #667eea;
                    padding: 15px;
                    margin-top: 20px;
                    text-align: left;
                    border-radius: 4px;
                    font-family: monospace;
                    font-size: 12px;
                    display: <?= $isDebug ? 'block' : 'none' ?>;
                }
                .actions { margin-top: 30px; }
                .btn {
                    display: inline-block;
                    padding: 12px 24px;
                    background: #667eea;
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: 600;
                    transition: all 0.3s;
                }
                .btn:hover { background: #5a67d8; transform: translateY(-2px); }
                .btn-secondary {
                    background: #e9ecef;
                    color: #495057;
                    margin-left: 10px;
                }
                .btn-secondary:hover { background: #dee2e6; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">
                    <?php if (http_response_code() == 404): ?>🔍
                    <?php elseif (http_response_code() == 403): ?>🚫
                    <?php elseif (http_response_code() == 401): ?>🔒
                    <?php else: ?>⚠️<?php endif; ?>
                </div>
                <h1><?= htmlspecialchars($title) ?></h1>
                <p><?= htmlspecialchars($message) ?></p>
                
                <?php if ($isDebug && $e instanceof AppException): ?>
                <div class="debug-info">
                    <strong>Détails :</strong><br>
                    <strong>Code :</strong> <?= $e->getErrorCode() ?><br>
                    <strong>Message :</strong> <?= $e->getMessage() ?><br>
                    <strong>Fichier :</strong> <?= $e->getFile() ?>:<?= $e->getLine() ?><br>
                    <?php if ($e->getDetails()): ?>
                    <strong>Détails :</strong><br>
                    <pre><?= htmlspecialchars(print_r($e->getDetails(), true)) ?></pre>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="actions">
                    <a href="/" class="btn">Retour à l'accueil</a>
                    <a href="javascript:history.back()" class="btn btn-secondary">Retour en arrière</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Définir le handler d'exceptions global
set_exception_handler(['ExceptionHandler', 'handle']);

// Configuration debug
define('DEBUG_MODE', $_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1');
?>