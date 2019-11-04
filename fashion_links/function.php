<?php
//ログ
ini_set('log_errors', 'on');

ini_set('error_log', 'php_log');

// デバッグ
$debug_flg = true;

function debug($str)
{
  global $debug_flg;
  if (!empty($debug_flg)) {
    error_log('デバッグ：' . $str);
  }
}

// セッション準備
session_save_path("/var/tmp/");

ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);

ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);

session_start();

session_regenerate_id();

// 画面表示処理開始ログ吐き出し
function debugLogStart()
{
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID：' . session_id());
  debug('セッション変数の中身：' . print_r($_SESSION, true));
  debug('現在日時タイムスタンプ：' . time());
  if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
    debug('ログイン期限日時タイムスタンプ：' . ($_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}

// 定数
// エラーメッセージ
define('MSG01', '：入力必須です');
define('MSG02', '；Email形式で入力して下さい');
define('MSG03', '：パスワード（再入力）が合っていません');
define('MSG04', '：半角英数字のみご利用頂けます');
define('MSG05', '：6文字以上で入力して下さい');
define('MSG06', '：エラーが発生しました。しばらく経ってからやり直して下さい');
define('MSG07', '：Emailは既に登録されています');
define('MSG08', '：メールアドレスまたはパスワードが違います');
define('MSG09', '：255字以内で入力して下さい');
define('MSG10', '：正しくありません');
define('SUC01', 'ログインしました');
define('SUC02', '登録しました');

$err_msg = array();

//バリデーション
//空白
function validRequired($str, $key)
{
  if ($str === '') {
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}
// Email形式
function validEmail($str, $key)
{
  if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}
// パスワード同値
function validMatch($str1, $str2, $key)
{
  if ($str1 !== $str2) {
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}
// 半角英数字
function validHalf($str, $key)
{
  if (!preg_match("/^[a-zA-Z0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}
// 最小文字数
function validMin($str, $key, $min = 6)
{
  if (mb_strlen($str) < $min) {
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}

// 最大文字数
function validMax($str, $key, $max = 255)
{
  if (mb_strlen($str) > $max) {
    global $err_msg;
    $err_msg[$key] = MSG09;
  }
}

// Email重複
function validEmailDup($email)
{
  global $err_msg;

  try {
    $dbh = dbConnect();

    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);

    $stmt = queryPost($dbh, $sql, $data);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty(array_shift($result))) {
      $err_msg['email'] = MSG07;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG06;
  }
}

// セレクトボックス
function validSelect($str, $key)
{
  if (!preg_match("/^[0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}
// ======================================================
// データベース
// ======================================================

// DB接続関数
function dbConnect()
{
  $dsn = 'mysql:dbname=okacchiesp_fashionlinks;host=mysql8042.xserver.jp;charset=utf8';
  $user = 'okacchiesp_root';
  $password = 'password';
  // $dsn = 'mysql:dbname=fashion_links;host=localhost;charset=utf8';
  // $user = 'root';
  // $password = 'root';

  $options = array(
    // SQL実行失敗時に例外をスロー
    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}
// SQL実行
function queryPost($dbh, $sql, $data)
{
  $stmt = $dbh->prepare($sql);
  if (!$stmt->execute($data)) {
    debug('クエリに失敗しました。');
    debug('失敗したSQL：' . print_r($stmt, true));
    $err_msg['common'] = MSG07;
    return 0;
  }
  debug('クエリ成功。');
  return $stmt;
}
// DBからスタイル情報を取得
function getStyle($u_id, $s_id)
{
  debug('スタイル情報を取得');
  debug('ユーザーID' . $u_id);
  debug('スタイルID' . $s_id);
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    //SQL作成
    $sql = 'SELECT * FROM style WHERE user_id = :u_id AND id = :s_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id, ':s_id' => $s_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC); //結果に返されたカラム名で添字をつけた配列
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

function getStyleList($currentMinNum, $category, $sort, $span = 30)
{
  debug('商品情報を取得します');

  try {
    $dbh = dbConnect();

    $sql = 'SELECT id FROM style';
    if (!empty($category)) $sql .= ' WHERE category_id = ' . $category;
    if (!empty($sort)) {
      switch ($sort) {
        case 1:
          $sql .= ' ORDER BY id DESC ';
          break;
        case 2:
          $sql .= ' ORDER BY id ASC ';
          break;
      }
    }
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);
    // 総レコード数
    $rst['total'] = $stmt->rowCount();
    $rst['total_page'] = ceil($rst['total'] / $span);
    if (!$stmt) {
      return false;
    }

    // ページングのSQL
    $sql = 'SELECT * FROM style AS s LEFT JOIN category AS c ON s.category_id = c.id LEFT JOIN users AS u ON s.user_id = u.id';
    if (!empty($category)) $sql .= ' WHERE s.category_id = ' . $category;
    if (!empty($sort)) {
      switch ($sort) {
        case 1:
          $sql .= ' ORDER BY s.id DESC ';
          break;
        case 2:
          $sql .= ' ORDER BY s.id ASC ';
          break;
      }
    }

    $sql .= ' LIMIT ' . $span . ' OFFSET ' . $currentMinNum;
    $data = array();
    debug('SQL：' . $sql);

    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

// DBからカテゴリー情報を取得
function getCategory()
{
  debug('カテゴリー情報を取得');
  // 例外処理
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM category';
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetchAll(); //結果に返された全ての行
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}
// ======================================================
// その他
// ======================================================
function sanitize($str)
{
  return htmlspecialchars($str, ENT_QUOTES);
}
// フォームの入力保持
function getFormData($str, $flg = false)
{
  if ($flg) {
    $method = $_GET;
  } else {
    $method = $_POST;
  }
  global $dbFormData;
  // DBにデータがあるか
  if (!empty($dbFormData)) {
    // フォームエラーがあるか
    if (!empty($err_msg)) {
      // POSTにデータがあるか
      if (isset($method[$str])) {
        return sanitize($method[$str]);
      } else {
        // POSTにデータがない(基本はありえない)DBの情報を表示
        return sanitize($dbFormData[$str]);
      }
    } else {
      // エラーがなくPOSTにデータがあり、DBと違う場合
      if (isset($method[$str]) && $method[$str] !== $dbFormData[$str]) {
        return sanitize($method[$str]);
      } else {
        // 元から変更していない
        return sanitize($dbFormData[$str]);
      }
    }
  } else {
    // DBにデータがない場合POSTデータを表示
    if (isset($method[$str])) {
      return sanitize($method[$str]);
    }
  }
}
function getSessionFlash($key)
{
  if (!empty($_SESSION[$key])) {
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}
function uploadPhoto($file, $key)
{
  debug('画像アップロード処理');
  debug('FILE情報：' . print_r($file, true));

  if (isset($file['error']) && is_int($file['error'])) {
    try { //バリデーション
      switch ($file['error']) {
        case UPLOAD_ERR_OK: //OK
          break;
        case UPLOAD_ERR_NO_FILE: //ファイル未選択
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE: //php.ini定義のサイズを超えている
        case UPLOAD_ERR_FORM_SIZE: //フォーム定義のサイズを超えている
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default:
          throw new RuntimeException('その他のエラーが発生しました');
      }
      // 画像形式のチェック
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
        throw new RuntimeException('画像形式が未対応です');
      }
      // アップロードする画像の名前をハッシュ化して、拡張子をつけて指定フォルダに保存する
      $path = 'images/' . sha1_file($file['tmp_name']) . image_type_to_extension($type);
      // アップロード
      if (!move_uploaded_file($file['tmp_name'], $path)) {
        throw new RuntimeException('ファイル保存時にエラーは発生');
      }
      // 保存したファイルパスの権限を変更する
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス' . $path);
      return $path;
    } catch (RuntimeException $e) {
      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}
// ページネーション
function pagination($currentPageNum, $totalPageNum, $link = '', $pageColNum = 5)
{
  if ($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 4;
    $maxPagenum = $currentPageNum;
  } elseif ($currentPageNum == ($totalPageNum - 1) && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 3;
    $maxPagenum = $currentPageNum + 1;
  } elseif ($currentPageNum == 2 && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 1;
    $maxPagenum = $currentPageNum + 3;
  } elseif ($currentPageNum == 1 && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum;
    $maxPagenum = $currentPageNum + 4;
  } elseif ($totalPageNum < $pageColNum) {
    $minPageNum = 1;
    $maxPagenum = $totalPageNum;
  } else {
    $minPageNum = $currentPageNum - 2;
    $maxPagenum = $currentPageNum + 2;
  }

  echo '<div class="pagination">';
  echo '<ul class="pagination-list">';
  if ($currentPageNum != 1) {
    echo '<li class="list-item"><a href="?p=1' . $link . '">&lt;</a></li>';
  }
  for ($i = $minPageNum; $i <= $maxPagenum; $i++) {
    echo '<li class="list-item ';
    if ($currentPageNum == $i) {
      echo 'active';
    }
    echo '"><a href="?p=' . $i . $link . '">' . $i . '</a></li>';
  }
  if ($currentPageNum != $maxPagenum && $maxPagenum > 1) {
    echo '<li class="list-item"><a href="?p=' . $maxPagenum . $link . '">&gt;</a></li>';
  }
  echo '</ul>';
  echo '</div>';
}
