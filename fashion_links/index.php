<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　トップページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
// カテゴリー取得
$category = (!empty($_GET['category_id'])) ? $_GET['category_id'] : '';
// ソート順取得
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';

if (!is_int((int) $currentPageNum)) {
  error_log('エラー発生：指定ページに不正な値が入りました');
  header("Location:index.php");
}

$listSpan = 30;

// 表示レコードの先頭を算出
// 1ページ目なら(1-1)*30 = 0
$currentMinNum = (($currentPageNum - 1) * $listSpan);
// データベースからカテゴリを取得
$dbCategoryData = getCategory();

$dbStyleData = getStyleList($currentMinNum, $category, $sort);
debug('現在のページ：' . $currentPageNum);

?>

<?php
require('head.php');
?>

<body>
  <?php
  require('header.php');
  ?>
  <p id="js-show-msg" style="display:none;" class="msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>

  <div class="contents">
    <div class="site-width">
      <section class="side-bar">
        カテゴリ選択：
        <form method="get" class="side-bar__form">

          <select class="selectbox" name="category_id">
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

          並び替え：
          <select class="selectbox" name="sort">
            <option value="0" <?php if (getFormData('sort') == 0) {
                                echo 'selected';
                              } ?>>
              選択してください
            </option>
            <option value="1" <?php if (getFormData('sort') == 1) {
                                echo 'selected';
                              } ?>>
              新しい順
            </option>
            <option value="2" <?php if (getFormData('sort') == 2) {
                                echo 'selected';
                              } ?>>
              古い順
            </option>
          </select>
          <input type="submit" name="" value="検索">
        </form>
      </section>

      <section class="main">
        <div class="search-title">
          <div class="search-left">
            <span class="total-num"><?php echo sanitize($dbStyleData['total']); ?></span>件見つかりました
          </div>
          <div class="search-right">
            <span class="num"><?php echo (!empty($dbStyleData['data'])) ? $currentMinNum + 1 : 0; ?></span> - <span class="num"><?php echo $currentMinNum + count($dbStyleData['data']); ?></span>件 / <span class="num"><?php echo sanitize($dbStyleData['total']); ?></span>件中
          </div>
        </div>

        <div class="panel-list">
          <?php foreach ($dbStyleData['data'] as $key => $val) : ?>
            <div class="panel">
              <div class="photo">
                <img src="<?php echo sanitize($val['photo']); ?>" alt="">
              </div>
              <div class="text">
                <p class="name"><?php echo sanitize($val['name']); ?></p>
                <p class="category <?php if ($val['category_id'] == 1) {
                                        echo 'gray';
                                      } else {
                                        echo 'orange';
                                      } ?>"><?php echo sanitize($val['category_name']); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php pagination($currentPageNum, $dbStyleData['total_page']); ?>

      </section>

    </div>
  </div>
  <footer id="footer">
    Copyright Fashion Links. All Rights Reserved.
  </footer>
</body>

</html>