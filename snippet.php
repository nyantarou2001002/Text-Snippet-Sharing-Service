<?php
// Docker内のMySQLデータベースに接続するための設定
$host = '127.0.0.1';
$port = '3307';
$db   = 'Text_Snippet_Sharing_Service';
$user = 'root';
$pass = 'password';

// データソースネーム (DSN) の設定
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // PDOを使ってデータベースに接続
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

// URLのハッシュ部分を取得
$hash = basename($_SERVER['REQUEST_URI']);

// スニペットをデータベースから取得
$stmt = $pdo->prepare('SELECT content, language, created_at FROM snippets WHERE hash = ?');
$stmt->execute([$hash]);
$snippet = $stmt->fetch();

if ($snippet) {
    echo "<h1>Snippet Details</h1>";
    echo "<p><strong>Language:</strong> " . htmlspecialchars($snippet['language']) . "</p>";
    echo "<p><strong>Created At:</strong> " . htmlspecialchars($snippet['created_at']) . "</p>";
    echo "<pre><code>" . htmlspecialchars($snippet['content']) . "</code></pre>";
} else {
    echo "Snippet not found.";
}
