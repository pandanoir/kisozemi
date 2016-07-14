<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../../../password.php';
require_unlogined_session();

// ユーザから受け取ったユーザ名とパスワード
$screen_name = filter_input(INPUT_POST, 'screen_name');
$password = filter_input(INPUT_POST, 'password');

// POSTメソッドのときのみ実行
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mysqli = new mysqli('localhost', MYSQL_USER, MYSQL_PASSWORD);
    if ($mysqli->connect_errno) {
        print($mysqli->error);
        exit;
    }
    $db_selected = $mysqli->select_db('rabbitplot');
    if (!$db_selected){
        die('データベース選択失敗です。' . $mysqli->error);
    }
    if ($stmt = $mysqli->prepare('SELECT password FROM user WHERE screen_name=?')) {
        $hashes = [];
        $stmt->bind_param('s', $screen_name);
        $stmt->execute();
        $stmt->bind_result($_password);
        while ($stmt->fetch()) {
            $hashes[$screen_name] = $_password;
        }
        $stmt->close();
    }
    $mysqli->close();

    if (
        validate_token(filter_input(INPUT_POST, 'token')) &&
        password_verify(
            $password,
            isset($hashes[$screen_name])
                ? $hashes[$screen_name]
                : '$2y$10$abcdefghijklmnopqrstuv' // ユーザ名が存在しないときだけ極端に速くなるのを防ぐ
        )
    ) {
        // 認証が成功したとき
        // セッションIDの追跡を防ぐ
        session_regenerate_id(true);
        // ユーザ名をセット
        $_SESSION['screen_name'] = $screen_name;
        // ログイン完了後に / に遷移
        header('Location: ./');
        exit;
    }
    // 認証が失敗したとき
    // 「403 Forbidden」
    http_response_code(403);
}

header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html>
    <head>
        <title>ログインページ</title>
    </head>
    <body>
        <h1>ログインしてください</h1>
        <form method="post" action="">
            ユーザ名: <input type="text" name="screen_name" value=""><br>
            パスワード: <input type="password" name="password" value=""><br>
            <input type="hidden" name="token" value="<?=h(generate_token())?>">
            <input type="submit" value="ログイン">
        </form>
        <a href="./register.php">新規ユーザ登録をする</a>
        <form method="post" action="">
            <input type="hidden" name="screen_name" value="test_user"><br>
            <input type="hidden" name="password" value="password"><br>
            <input type="hidden" name="token" value="<?=h(generate_token())?>">
            <input type="submit" value="テストユーザでログイン">
        </form>
        <?php if (http_response_code() === 403): ?>
        <p style="color: red;">ユーザ名またはパスワードが違います</p>
        <?php endif; ?>
    </body>
</html>