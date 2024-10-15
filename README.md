# Text Snippet Sharing Service

ユーザーがプレーンテキストやコードスニペットを共有できるオンラインコード＆テキストスニペット共有サービスです。

# URL
https://snippet.mdtohtml.com/

# デモ

https://github.com/user-attachments/assets/80bb5c40-5c63-42d2-b9fc-ff4c0b167191

# 有効期限が過ぎた場合

<img width="1440" alt="スクリーンショット 2024-10-16 3 57 29" src="https://github.com/user-attachments/assets/c0f6761b-5085-466e-ad0b-18750f714e75">

# 概要

ユーザーは、テキストエリアにテキストやコードを貼り付け、タイトルとプログラミング言語と有効期限を設定してsubmit snippetボタンをクリックすると共有コンテンツの一意の URL が生成されます。この URL は、他の人とコンテンツを共有するために使用できます。

共有画面では選択したプログラミング言語に応じてシンタックハイライトが適用されたテキストが表示されます。
設定した有効期限が過ぎたらsnippetは自動的に削除されて「Expired Snippet」というメッセージが画面上に表示されます。

# 使い方
1. テキストエリアに共有したいテキストを貼り付けます。
2. 「Snippet Title」にタイトルを記入します。
3. 「Language」のメニューから使用している言語を選択します。
4. 「Expiration」のメニューからsnippetの有効期限を選択します。
5. 「submit snippet」ボタンをクリックして共有用のURLを入手します。
6. このURLを他の人と共有することができます。

# 機能一覧
- snippet作成
- URL作成
- 有効期限管理
- コードエディターの使用
- 複数言語対応のシンタックスハイライトを表示
- エラーハンドリング

# 使用技術
言語：HTML,CSS Javascript, PHP
データベース：MySQL(Amazon RDB)
サーバー：Amazon EC2
その他：Monaco Editor, NGINX
