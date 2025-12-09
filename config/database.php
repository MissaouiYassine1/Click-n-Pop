<?php
class Database {
    private static $instance = null;
    private $connection;
    private static $useMockData = false;
    
    private function __construct() {
        try {
            // Vérifier si PDO MySQL est disponible
            if (!extension_loaded('pdo_mysql')) {
                error_log("PDO MySQL extension not loaded. Using mock data.");
                self::$useMockData = true;
                return;
            }
            
            $this->connection = new PDO(
                "mysql:host=localhost;dbname=clicknpop;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
        } catch(PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            self::$useMockData = true;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        if (self::$useMockData) {
            return null;
        }
        return $this->connection;
    }
    
    public static function isUsingMockData() {
        return self::$useMockData;
    }
}

// Helper function pour compatibilité
function getDB() {
    $db = Database::getInstance();
    return $db->getConnection();
}

// Fonctions de données mock pour le développement
function getMockPlayers($limit = 50) {
    $players = [];
    $firstNames = ['Alex', 'Sam', 'Jordan', 'Taylor', 'Casey', 'Riley', 'Avery', 'Morgan', 'Quinn', 'Drew'];
    $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'];
    $titles = ['Bubble', 'Pop', 'Click', 'Score', 'Game', 'Master', 'King', 'Queen', 'Ninja', 'Wizard'];
    
    for ($i = 0; $i < $limit; $i++) {
        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        $title = $titles[array_rand($titles)] . $titles[array_rand($titles)];
        
        $players[] = [
            'username' => $firstName . $title . rand(1, 999),
            'profile_pic' => rand(0, 4) > 2 ? 'avatar' . rand(1, 5) . '.png' : null,
            'level' => rand(1, 100),
            'best_score' => rand(1000, 25000),
            'games_played' => rand(10, 1000),
            'avg_accuracy' => rand(65, 99) + (rand(0, 9) / 10),
            'total_bubbles' => rand(5000, 500000),
            'xp' => rand(100, 100000),
            'country' => ['US', 'FR', 'UK', 'DE', 'CA', 'JP', 'AU', 'BR'][array_rand(['US', 'FR', 'UK', 'DE', 'CA', 'JP', 'AU', 'BR'])]
        ];
    }
    
    // Trier par score décroissant
    usort($players, function($a, $b) {
        return $b['best_score'] - $a['best_score'];
    });
    
    return $players;
}

function getMockUserStats($userId = null) {
    if (!isset($_SESSION['user_id']) && !$userId) {
        return null;
    }
    
    $username = $_SESSION['username'] ?? 'Player' . rand(1000, 9999);
    
    return [
        'user_rank' => rand(1, 250),
        'username' => $username,
        'profile_pic' => rand(0, 3) > 1 ? 'user_avatar.png' : null,
        'level' => rand(1, 75),
        'best_score' => rand(5000, 15000),
        'games_played' => rand(25, 500),
        'avg_accuracy' => rand(70, 95) + (rand(0, 9) / 10),
        'total_bubbles' => rand(10000, 250000),
        'xp' => rand(1000, 75000),
        'country' => 'FR',
        'join_date' => date('Y-m-d', strtotime('-' . rand(1, 365) . ' days'))
    ];
}

function getLeaderboardStats() {
    return [
        'total_players' => rand(5000, 20000),
        'total_bubbles' => rand(5000000, 20000000),
        'top_score_month' => rand(15000, 30000),
        'top_player_month' => 'BubbleMaster' . rand(100, 999),
        'avg_play_time' => rand(15, 45) + (rand(0, 59) / 100),
        'total_games_today' => rand(1000, 5000)
    ];
}
?>