$(".js-get-val").on("keyup", function () {
  if ($(this).val()) {
    $(".js-add-todo").prop('disabled', false);
    $(".js-inputError").hide();
  } else {
    $(".js-add-todo").prop('disabled', true);
    $(".js-inputError").show();
  }
});

$(".js-add-todo").on("click", function (e) {
  // 画面更新しない
  e.preventDefault();

  // 入力値を取得してから、空文字にする
  var text = $(".js-get-val").val();
  $(".js-get-val").val("");
  // エラーの表示、非表示
  if (!text) {
    $(".js-inputError").show();
    return;
  }

  $(".js-inputError").hide();

  // 入力値を値としてHTMLを作成する
  var listitem =
    '<li class="list__item js-todo_list-item" data-text="' +
    text +
    '">' +
    '<i class = "far fa-circle icon-check js-click-done"  aria - hidden = "true" > </i>' +
    '<span class = "list-text js-todo_list-text" >' +
    text +
    "</span>" +
    '<input type = "text" class = "editText js-todo_list-edit" value = "' +
    text +
    '" >' +
    '<i class = "fas fa-trash-alt icon-trash js-click-trash" aria - hidden = "true" > </i>' +
    "</li>";
  // リストの１番前に挿入する(.js-todo-list直下)
  $(".js-todo_list").prepend(listitem);
});

// HTML全体のDOMを取得し、引数でセレクタを指定する（新規で追加されたリストに反映させるため）
// 未から済
$(document).on("click", ".js-click-done", function () {
  $(this)
    .removeClass("fa-circle")
    .addClass("fa-check-circle")
    .removeClass("js-click-done")
    .addClass("js-click-todo")
    .closest(".js-todo_list-item")
    .addClass("list__item--done")
    .removeClass("list__item--trash");
});
// 済から未
$(document).on("click", ".js-click-todo", function () {
  $(this)
    .addClass("fa-circle")
    .removeClass("fa-check-circle")
    .addClass("js-click-done")
    .removeClass("js-click-todo")
    .closest(".js-todo_list-item")
    .removeClass("list__item--done");
});
// ゴミ箱をクリックするとリストが削除される
// closestは遡って引数をマッチさせる
$(document).on("click", ".js-click-trash", function () {
  $(this)
    .closest(".js-todo_list-item")
    .fadeOut("slow", function () {
      this.remove();
    });
});
// リストをクリックすると編集できるspanを非表示にしてinputを表示
$(document).on("click", ".js-todo_list-text", function () {
  $(this)
    .hide()
    .siblings(".js-todo_list-edit")
    .show();
});
// 編集を確定させるためにエンターキーとシフトキーで判定させる
$(document).on("keyup", ".js-todo_list-edit", function (e) {
  if (e.keyCode === 13 && e.shiftKey === true) {
    var $this = $(this);
    $this
      .hide()
      .siblings(".js-todo_list-text")
      .text($this.val())
      .show()
      .closest(".js-todo_list-item")
      .attr("data-text", $this.val());
  }
});

// 検索
$(".js-search").on("keyup", function () {
  // 入力文字を変数に
  var searchText = $(this).val();

  $(".js-todo_list-item")
    .show()
    .each(function (i, elm) {
      var text = $(elm).data("text");
      var regexp = new RegExp("^" + searchText);
      // matchで正規表現の判定
      if (text && text.match(regexp)) {
        return true;
      }
      $(elm).hide();
    });
});