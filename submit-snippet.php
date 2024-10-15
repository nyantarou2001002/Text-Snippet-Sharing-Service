<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Docker内のMySQLデータベースに接続するための設定
$host = '127.0.0.1';
$port = '3307';
$db   = 'Text_Snippet_Sharing_Service';
$user = 'root';
$pass = 'password';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// POSTリクエストからデータを取得
$data = json_decode(file_get_contents('php://input'), true);
$content = $data['content'] ?? '';
$language = $data['language'] ?? '';
$expiration = $data['expiration'] ?? 'never';
$title = $data['title'] ?? 'Untitled';

// 有効期限の設定
switch ($expiration) {
    case '10m':
        $expirationDate = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        break;
    case '1h':
        $expirationDate = date('Y-m-d H:i:s', strtotime('+1 hour'));
        break;
    case '1d':
        $expirationDate = date('Y-m-d H:i:s', strtotime('+1 day'));
        break;
    case 'never':
    default:
        $expirationDate = null;  // 無期限の場合
        break;
}

// スニペットのユニークなハッシュを生成
$hash = hash('sha256', $content . time());

try {
    $stmt = $pdo->prepare('INSERT INTO snippets (title, content, language, hash, expiration_at) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$title, $content, $language, $hash, $expirationDate]);

    // 成功した場合、ユニークなURLを生成して返す
    $url = 'http://localhost:8080/snippet/' . $hash;
    header('Content-Type: application/json');
    echo json_encode(['url' => $url]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to save snippet: ' . $e->getMessage()]);
}
