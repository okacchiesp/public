<header class="header">
  <div class="site-width">
    <h1 class="header__title"><a href="index.php">Fashion Links</a></h1>
    <nav class="nav-menu">
      <ul class="menu">
        <?php
          if(empty($_SESSION['user_id'])){
        ?>
            <li class="menu-link"><a href="login.php">ログイン</a></li>
            <li class="btn menu-link"><a href="signup.php">ユーザー登録</a></li>
        <?php
          }else{
        ?>
            <li class="menu-link"><a href="style.php">スタイル登録</a></li>
            <li class="btn menu-link"><a href="logout.php">ログアウト</a></li>
        <?php
          }
        ?>
      </ul>
    </nav>
  </div>
</header>
