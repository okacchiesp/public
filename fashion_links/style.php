<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　スタイル登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

// 画面表示用データ取得
// ======================================================
// GETデータを格納
$s_id = (!empty($_GET['s_id'])) ? $_GET['s_id'] : '';

// DBからデータを取得
$dbFormData = (!empty($s_id)) ? getStyle($_SESSION['user_id'], $s_id) : '';

// 新規登録か編集画面か
$edit_flg = (empty($dbFormData)) ? false : true;

// DBからカテゴリー情報を取得
$dbCategoryData = getCategory();
debug('スタイルID：' . $s_id);
debug('フォーム用DBデータ：' . print_r($dbFormData, true));
debug('カテゴリーデータ：' . print_r($dbCategoryData, true));

// パラメータ改ざんチェック
// ======================================================
// GETパラメータはあるが、URLを弄った場合は正しいデータが取れないので遷移させる
if (!empty($s_id) && empty($dbFormData)) {
  debug('GETパラメータのIDが違います。');
  header("Location:index.php");
}

// POST送信時処理
// ======================================================
if (!empty($_POST)) {
  debug('POST送信があります');
  debug('POST情報：' . print_r($_POST, true));
  debug('FILE情報：' . print_r($_FILES, true));

  $category = $_POST['category_id'];
  $photo = (!empty($_FILES['photo']['name'])) ? uploadPhoto($_FILES['photo'], 'photo') : '';
  $photo = (empty($photo) && !empty($dbFormData['photo'])) ? $dbFormData['photo'] : $photo;

  if (empty($dbFormData)) {
    validSelect($category, 'category_id');
  } else {
    if ($dbFormData !== $category) {
      validSelect($category, 'category_id');
    }
  }

  if (empty($err_msg)) {
    debug('バリデーションOK');

    try {
      $dbh = dbConnect();

      if ($edit_flg) {
        debug('更新です');
        $sql = 'UPDATE style SET photo = :photo, category_id = :category WHERE user_id = :user_id AND id = :s_id';
        $data = array(':photo' => $photo, ':category' => $category, ':u_id' => $_SESSION['user_id'], ':s_id' => $s_id);
      } else {
        debug('新規登録です');
        $sql = 'INSERT INTO style(photo, category_id, user_id, create_date) VALUES (:photo, :category, :u_id, :date)';
        $data = array(':photo' => $photo, ':category' => $category, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
      }
      debug('SQL：' . $sql);
      debug('流し込みデータ：' . print_r($data, true));

      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        $_SESSION['msg_success'] = SUC02;
        debug('トップページへ遷移します');
        header("Location:index.php");
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
  <h2 class="title">スタイル登録</h2>

  <div class="site-width main-form">
    <section class="form-container">

      <form class="form" method="post" enctype="multipart/form-data">
        <div class="msg">
          <?php
          if (!empty($err_msg['common'])) echo $err_msg['common'];
          ?>
        </div>
        <div class="photo-drop">
          <label class="area-drop <?php if (!empty($err_msg['photo'])) echo 'err'; ?>">
            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
            <input type="file" name="photo" class="input-file">
            <img src="<?php echo getFormData('photo'); ?>" alt="" class="prev-img" style="<?php if (empty(getFormData('photo'))) echo 'display:none;'; ?>">
            ドラック＆ドロップ
          </label>
          <div class="msg">
            <?php
            if (!empty($err_msg['photo'])) echo $err_msg['photo'];
            ?>
          </div>
        </div>
        <div class="form-select">
          <label class="<?php if (!empty($err_msg['category_id'])) echo 'err'; ?>">
            <select class="" name="category_id">
              <option value="0" <?php if (getFormData('category_id') == 0) {
                                  echo 'selected';
                                } ?>>
                選択してください
              </option>
              <?php foreach ($dbCategoryData as $key => $val) { ?>
                <option value="<?php echo $val['id'] ?>" <?php if (getFormData('category_id') == $val['id']) {
                                                              echo 'selected';
                                                            } ?>>
                  <?php echo $val['category_name']; ?>
                </option>
              <?php } ?>
            </select>
          </label>
          <div class="msg">
            <?php
            if (!empty($err_msg['category_id'])) echo $err_msg['category_id'];
            ?>
          </div>

          <input type="submit" name="" value="登録">

        </div>
      </form>

    </section>
  </div>
  <footer id="footer">
    Copyright Fashion Links. All Rights Reserved.
  </footer>
</body>

</html>