<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if (!empty($_POST)) {
  // フォームの内容を変数に
  $name = $_POST['name'];
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];

  // 未入力チェック
  validRequired($name, 'name');
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');

  if (empty($err_msg)) {
    // $nameの半角英数字
    validHalf($name, 'name');
    // $nameの最大文字数
    validMax($name, 'name');

    // $emailの形式チェック
    validEmail($email, 'email');
    // $emailの最大文字数
    validMax($email, 'email');
    // $emailの重複
    validEmailDup($email);

    // $passの半角英数字
    validHalf($pass, 'pass');
    // $passの最小文字数
    validMin($pass, 'pass');
    // $passの最大文字数
    validMax($pass, 'pass');

    // $passの最小文字数
    validMin($pass_re, 'pass_re');
    // $passの最大文字数
    validMax($pass_re, 'pass_re');



    if (empty($err_msg)) {
      validMatch($pass, $pass_re, 'pass_re');

      if (empty($err_msg)) {
        // 例外処理
        try {
          $dbh = dbConnect();

          $sql = 'INSERT INTO users (name, email, password, login_time, create_date) VALUES (:name, :email, :password, :login_time, :create_date)';
          $data = array(
            ':name' => $name, ':email' => $email,
            ':password' => password_hash($pass, PASSWORD_DEFAULT),
            ':login_time' => date('Y-m-d H:i:s'),
            ':create_date' => date('Y-m-d H:i:s')
          );
          $stmt = queryPost($dbh, $sql, $data);
          if ($stmt) {
            // ログイン有効期限
            $sesLimit = 60 * 60;
            // 最終ログイン日時を現在日時に
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $sesLimit;
            // ユーザーIDを格納
            $_SESSION['user_id'] = $dbh->lastInsertId();

            debug('セッション変数の中身：' . print_r($_SESSION, true));
            header("Location:index.php");
          }
        } catch (Exception $e) {
          error_log('エラー発生：' . $e->getMessage());
          $err_msg['common'] = MSG06;
        }
      }
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
  <h2 class="title">ユーザー登録</h2>

  <div class="site-width main-form">
    <section class="form-container">
      <form class="form" method="post">
        <!-- エラーメッセージ表示 -->
        <div class="msg">
          <?php
          if (!empty($err_msg['common'])) echo $err_msg['common'];
          ?>
        </div>

        <!-- バリデーションエラーがあればclassにerrを追加する -->
        <label class="<?php if (!empty($err_msg['name'])) echo 'err'; ?>">
          ID
          <!-- バリデーションエラーがあればエラー定数を表示させる -->
          <div class="errmsg">
            <?php
            if (!empty($err_msg['name'])) echo $err_msg['name'];
            ?>
          </div>
          <!-- フォームPOST値を入力しておく -->
          <input type="text" name="name" value="<?php if (!empty($_POST['name'])) echo $_POST['name']; ?>">
        </label>


        <label class="<?php if (!empty($err_msg['email'])) echo 'err'; ?>">
          E-mail
          <div class="errmsg">
            <?php
            if (!empty($err_msg['email'])) echo $err_msg['email'];
            ?>
          </div>
          <input type="text" name="email" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>">
        </label>


        <label class="<?php if (!empty($err_msg['pass'])) echo 'err'; ?>">
          Password
          <div class="errmsg">
            <?php
            if (!empty($err_msg['pass'])) echo $err_msg['pass'];
            ?>
          </div>
          <input type="password" name="pass" value="<?php if (!empty($_POST['pass'])) echo $_POST['pass']; ?>">
        </label>


        <label class="<?php if (!empty($err_msg['pass_re'])) echo 'err'; ?>">
          Password(再入力)
          <div class="errmsg">
            <?php
            if (!empty($err_msg['pass_re'])) echo $err_msg['pass_re'];
            ?>
          </div>
          <input type="password" name="pass_re" value="<?php if (!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
        </label>


        <input type="submit" name="" value="登録">
      </form>
    </section>

  </div>
  <footer id="footer">
    Copyright Fashion Links. All Rights Reserved.
  </footer>
</body>

</html>