<?php
$servername = $_ENV['MYSQLHOST'] ?? 'interchange.proxy.rlwy.net';
$username   = $_ENV['MYSQLUSER'] ?? 'root';
$password   = $_ENV['MYSQLPASSWORD'] ?? 'rmXNncOSgkLHoEderbeBGbyzvHVPfFju';
$dbname     = $_ENV['MYSQLDATABASE'] ?? 'railway';
$port       = $_ENV['MYSQLPORT'] ?? 50611;

/* ---- Create PDO connection ---- */
try {
    $pdo = new PDO(
        "mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    error_log("DB Connection Failed: " . $e->getMessage());
    die("Database connection failed.");
}

/* ---- mysqli compatibility wrapper ---- */
class mysqli_compat {
    private $pdo;
    private $stmt;
    private $vars = [];

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function prepare($query) {
        $this->stmt = $this->pdo->prepare($query);
        return $this;
    }

    public function bind_param($types, &...$vars) {
        $this->vars = &$vars;
        return true;
    }

    public function execute() {
        return $this->stmt->execute($this->vars);
    }

    public function get_result() {
        return $this;
    }

    public function fetch_assoc() {
        return $this->stmt->fetch();
    }

    public function close() {
        return true;
    }
}

/* ---- expose $conn like mysqli ---- */
$conn = new mysqli_compat($pdo);
