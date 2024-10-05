<?php

$request = $_SERVER['REQUEST_URI'];

if ($request === '/submit-snippet.php') {
    // submit-snippet.php へのリクエストはこのファイルに振り分け
    include 'submit-snippet.php';
} elseif (preg_match('/^\/snippet\/([a-zA-Z0-9]+)$/', $request, $matches)) {
    // スニペットのハッシュが含まれるリクエストは snippet.php へ
    $_GET['hash'] = $matches[1];
    include 'snippet.php';
} else {
    // それ以外は index.html を表示
    include 'index.html';
}
