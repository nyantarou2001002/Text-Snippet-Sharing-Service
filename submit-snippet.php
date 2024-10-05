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

if (empty($content) || empty($language)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Content or language is missing']);
    exit;
}

// スニペットのユニークなハッシュを生成
$hash = hash('sha256', $content . time());

try {
    $stmt = $pdo->prepare('INSERT INTO snippets (content, language, hash) VALUES (?, ?, ?)');
    $stmt->execute([$content, $language, $hash]);

    // 成功した場合、ユニークなURLを生成して返す
    $url = 'http://localhost:8080/snippet/' . $hash;
    header('Content-Type: application/json');
    echo json_encode(['url' => $url]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to save snippet: ' . $e->getMessage()]);
}
