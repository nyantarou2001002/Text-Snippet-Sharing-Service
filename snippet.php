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
$stmt = $pdo->prepare('SELECT title, content, language, created_at, expiration_at FROM snippets WHERE hash = ?');
$stmt->execute([$hash]);
$snippet = $stmt->fetch();

if ($snippet) {
    // スニペットが有効期限内か確認
    if ($snippet['expiration_at'] !== null && strtotime($snippet['expiration_at']) < time()) {
        echo "<h1>Expired Snippet</h1>";
    } else {
        // スニペットのタイトルとコードをMonaco Editorで表示
?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($snippet['title']); ?></title>
            <style>
                #editor {
                    width: 100%;
                    height: 400px;
                    border: 1px solid #ccc;
                }
            </style>
            <!-- Require.jsとMonaco Editorのスクリプトを読み込み -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/require.js/2.3.6/require.min.js"></script>
            <script>
                window.MonacoEnvironment = {
                    getWorkerUrl: function(workerId, label) {
                        var workerUrl = 'data:text/javascript;charset=utf-8,';
                        workerUrl += encodeURIComponent(`
                            self.MonacoEnvironment = {
                                baseUrl: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.21.2/min/'
                            };
                            importScripts('https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.21.2/min/vs/base/worker/workerMain.js');
                        `);
                        return workerUrl;
                    }
                };
            </script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.21.2/min/vs/loader.js"></script>
        </head>

        <body>
            <h1><?php echo htmlspecialchars($snippet['title']); ?></h1>
            <p><strong>Language:</strong> <?php echo htmlspecialchars($snippet['language']); ?></p>
            <p><strong>Created At:</strong> <?php echo htmlspecialchars($snippet['created_at']); ?></p>

            <!-- Monaco Editorを表示 -->
            <div id="editor"></div>

            <script>
                require.config({
                    paths: {
                        'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.21.2/min/vs'
                    }
                });
                require(['vs/editor/editor.main'], function() {
                    var editor = monaco.editor.create(document.getElementById('editor'), {
                        value: `<?php echo addslashes($snippet['content']); ?>`, // スニペット内容をMonaco Editorに挿入
                        language: '<?php echo htmlspecialchars($snippet['language']); ?>', // 言語を設定
                        theme: 'vs-dark',
                        readOnly: true // 読み取り専用
                    });
                });
            </script>
        </body>

        </html>
<?php
    }
} else {
    echo "Snippet not found.";
}
