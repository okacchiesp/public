$(function() {
  /*ページフェードイン*/
  $("body").fadeMover(1000);

  /*文字カウント*/
  const MSG = "(100文字を超えています。)";

  $(".count-text").keyup(function() {
    var count = $(this).val().length;

    var textbox = $(this).closest(".count-text");

    $(".counter").text(count);

    if ($(this).val().length > 100) {
      $(".msg").text(MSG);
      textbox.removeClass(".has_success").addClass(".has_error");
    } else {
      $(".msg").text("");
      textbox.removeClass(".has_error").addClass(".has_success");
    }
  });
  // ページリンク
  $('a[href^="#"]').click(function() {
    var speed = 500;
    var href = $(this).attr("href");
    var target = $(href == "#" || href == "" ? "html" : href);
    var position = target.offset().top - 200;
    $("body,html").animate({ scrollTop: position }, speed, "swing");
    return false;
  });
  // レスポンシブメニュー
  $(".js-menu-sp").on("click", function() {
    $(this).toggleClass("active");
    $(".js-menu-sp-target").toggleClass("active");
  });

  $(".js-menu-sp-target a").on("click", function() {
    $(".js-menu-sp").toggleClass("active");
    $(".js-menu-sp-target").toggleClass("active");
  });
});
