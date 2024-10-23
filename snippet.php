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
        echo "<h1 class='text-center text-2xl font-bold text-red-500 mt-8'>Expired Snippet</h1>";
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
                    border-radius: 0.375rem;
                    /* Rounded corners */
                }
            </style>
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
            <script src="https://cdn.tailwindcss.com"></script>
        </head>

        <body class="bg-gray-100 text-gray-800">
            <div class="max-w-4xl mx-auto p-6 bg-white shadow-md rounded-lg mt-10">
                <h1 class="text-2xl font-bold mb-4 text-center"><?php echo htmlspecialchars($snippet['title']); ?></h1>
                <div class="mb-4">
                    <p class="text-sm text-gray-600"><strong>Language:</strong> <?php echo htmlspecialchars($snippet['language']); ?></p>
                    <p class="text-sm text-gray-600"><strong>Created At:</strong> <?php echo htmlspecialchars($snippet['created_at']); ?></p>
                </div>

                <!-- Monaco Editorを表示 -->
                <div id="editor" class="rounded"></div>

                <script>
                    require.config({
                        paths: {
                            'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.21.2/min/vs'
                        }
                    });
                    require(['vs/editor/editor.main'], function() {
                        var editor = monaco.editor.create(document.getElementById('editor'), {
                            value: `<?php echo addslashes($snippet['content']); ?>`,
                            language: '<?php echo htmlspecialchars($snippet['language']); ?>',
                            theme: 'vs-dark',
                            readOnly: true
                        });
                    });
                </script>
            </div>
        </body>

        </html>
<?php
    }
} else {
    echo "<h1 class='text-center text-2xl font-bold text-red-500 mt-8'>Snippet not found.</h1>";
}
