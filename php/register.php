<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../../../password.php';
require_unlogined_session();

// POSTメソッドのときのみ実行
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mysqli = new mysqli('localhost', MYSQL_USER, MYSQL_PASSWORD);
    if ($mysqli -> connect_errno) {
        print($mysqli->error);
        exit;
    }
    $db_selected = $mysqli->select_db('rabbitplot');
    if (!$db_selected){
        die('データベース選択失敗です。' . $mysqli->error);
    }
    if ($stmt = $mysqli->prepare('SELECT COUNT(*) FROM user WHERE screen_name=?')) {
        $hashes = [];
        $stmt->bind_param('s', $screen_name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
    }

    if (
        validate_token(filter_input(INPUT_POST, 'token')) &&
        $count === 0
    ) {
        $screen_name = filter_input(INPUT_POST, 'screen_name');
        $name = filter_input(INPUT_POST, 'name');
        $password = filter_input(INPUT_POST, 'password');
        $password = password_hash($password, PASSWORD_DEFAULT);
        $userID = 0;

        $result = $mysqli->query('SELECT MAX(userID) AS max FROM user');
        if (!$result) {
            die('クエリーが失敗しました。' . $mysqli->error);
        }
        $row = $result->fetch_assoc();
        $userID = intval($row['max']) + 1;

        // セッションIDの追跡を防ぐ
        session_regenerate_id(true);
        // ユーザ名をセット
        if ($stmt = $mysqli->prepare("INSERT INTO user VALUES(?,?,?,?)")) {
            $stmt->bind_param('isss', $userID} $screen_name, $name, $password);
            $stmt->execute();
            $stmt->close();
            $_SESSION['screen_name'] = $screen_name;
        }
        $mysqli->close();
        // ログイン完了後に / に遷移
        header('Location: ./');
        exit;
    }
    $mysqli->close();
}

header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html>
    <head>
        <title>新規ユーザ登録</title>
    </head>
    <body>
        <h1>新規ユーザ登録</h1>
        <form method="post" action="">
            ユーザID: <input type="text" name="screen_name" value="">(登録後変更できません。英数字、アンダーバーのみ使用可能です)<br>
            ユーザ名: <input type="text" name="name" value=""><br>
            パスワード: <input type="password" name="password" value=""><br>
            <input type="hidden" name="token" value="<?=h(generate_token())?>">
            <input type="submit" value="登録">
        </form>
        <a href="./register.php">新規ユーザ登録をする</a>
        <?php if (http_response_code() === 403): ?>
        <p style="color: red;">ユーザ名またはパスワードが違います</p>
        <?php endif; ?>
    </body>
</html>