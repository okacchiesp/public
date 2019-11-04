<?php

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ログインページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

//ログイン画面処理
if (!empty($_POST)) {
  debug('POST送信があります。');

  $email = $_POST['email'];
  $pass = $_POST['pass'];
  // ログイン保持変数
  $pass_save = (!empty($_POST['pass_save'])) ? true : false;

  validEmail($email, 'email');
  validMax($email, 'email');

  validHalf($pass, 'pass');
  validMax($pass, 'pass');
  validMin($pass, 'pass');

  validRequired($email, 'email');
  validRequired($pass, 'pass');

  if (empty($err_msg)) {
    debug('バリデーションOKです。');

    try {
      $dbh = dbConnect();
      $sql = 'SELECT password, id FROM users WHERE email = :email AND delete_flg = 0';
      $data = array('email' => $email);

      $stmt = queryPost($dbh, $sql, $data);

      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      debug('クエリの中身：' . print_r($result, true));

      // パスワード照合
      if (!empty($result) && password_verify($pass, array_shift($result))) {
        debug('パスワード照合しました');

        // ログイン有効期限
        $sesLimit = 60 * 60;

        // 最終ログイン日時を現在の日時にする
        $_SESSION['login_date'] = time();

        // ログイン保持ありなし
        if ($pass_save) {
          // ログイン有効期限を30日にする
          debug('ログイン保持あり');
          $_SESSION['login_limit'] = $sesLimit * 24 * 30;
        } else {
          // ログイン有効期限を1時間にする
          debug('ログイン保持なし');
          $_SESSION['login_limit'] = $sesLimit;
        }

        $_SESSION['user_id'] = $result['id'];
        if ($stmt) {
          $_SESSION['msg_success'] = SUC01;
          debug('セッションの中身：' . print_r($_SESSION, true));
          debug('トップページへ遷移します');
          header("Location:index.php");
        }
      } else {
        debug('パスワードが合っていません');
        $err_msg['common'] = MSG08;
      }
    } catch (Exception $e) {
      error_log('エラー発生：' . $e->getMessage());
      $err_msg['common'] = MSG06;
    }
  }
}

?>

<?php
require('head.php');
?>

<body>
  <?php
  require('header.php');
  ?>

  <h2 class="title">ログイン</h2>
  <div class="site-width main-form">

    <section class="form-container">

      <form class="form" method="post">
        <!-- エラーメッセージ表示 -->
        <div class="msg">
          <?php
          if (!empty($err_msg['common'])) echo $err_msg['common'];
          ?>
        </div>

        <label class="<?php if (!empty($err_msg['email'])) echo 'err'; ?>">
          E-mail
          <!-- バリデーションエラーがあればエラー定数を表示させる -->
          <div class="errmsg">
            <?php
            if (!empty($err_msg['email'])) echo $err_msg['email'];
            ?>
          </div>
          <input type="text" name="email" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>">
        </label>

        <label class="<?php if (!empty($err_msg['pass'])) echo 'err'; ?>">
          Password
          <!-- バリデーションエラーがあればエラー定数を表示させる -->
          <div class="errmsg">
            <?php
            if (!empty($err_msg['pass'])) echo $err_msg['pass'];
            ?>
          </div>
          <input type="password" name="pass" value="<?php if (!empty($_POST['pass'])) echo $_POST['pass']; ?>">
        </label>

        <label>
          <input type="checkbox" name="pass_save">ログイン状態を保持する
        </label>

        <input type="submit" name="" value="ログイン">
      </form>

    </section>
  </div>
  <footer id="footer">
    Copyright Fashion Links. All Rights Reserved.
  </footer>
</body>

</html>