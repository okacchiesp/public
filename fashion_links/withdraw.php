<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　退会ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

if(!empty($_POST)){
  debug('POST送信があります。');

  try{
    $dbh = dbConnect();
    // 論理削除
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :us_id';
    $sql2 = 'UPDATE style SET delete_flg = 1 WHERE user_id = :us_id';
    $sql3 = 'UPDATE message SET delete_flg = 1 WHERE user_id = :us_id';

    $data = array(':us_id' => $_SESSION['user_id']);

    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);

    if($stmt1){
      // セッション削除
      session_destroy();
      debug('セッション変数の中身：'.print_r($_SESSION,true));
      debug('トップページへ遷移します');
      header("Location:index.php");
    }else{
      debug('クエリが失敗しました');
      $err_msg['common'] = MSG06;
    }
  }catch(Exception $e){
    error_log('エラー発生：'. $e->getMessage());
    $err_msg['common'] = MSG06;
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
  require('head.php');
?>
  <body>
    <?php
      require('header.php');
    ?>
    <div id="contents" class="site-width">
      <h2>退会</h2>
      <section id="form-container" class="withdraw">
        <form class="form" method="post">
          <div class="errmsg">
            <?php
              if(!empty($err_msg['common'])) echo $err_msg['common'];
            ?>
          </div>
          <input type="submit" name="submit" value="退会する" class="btn-middle">
        </form>
      </section>
    </div>
    <footer>
      Copyright Fashion Links. All Rights Reserved.
    </footer>
  </body>
</html>
