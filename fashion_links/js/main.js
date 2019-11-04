$(function () {
  // footer位置調整
  var $ftr = $('#footer');
  var $offset = $ftr.offset().top
  if (window.innerHeight > $offset + $ftr.outerHeight()) {
    $ftr.attr({
      'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;'
    });
  }
  // メッセージの表示
  var $jsMsg = $('#js-show-msg');
  var msg = $jsMsg.text();
  if (msg.replace(/^[\s　]+|[\s　]+$/g, "").length) {
    $jsMsg.slideToggle('slow');
    setTimeout(function () {
      $jsMsg.slideToggle('slow')
    }, 3000);
  }
  // 画像プレビュー
  var $dropArea = $('.area-drop');
  var $fileInput = $('.input-file');
  $dropArea.on('dragover', function (e) {
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', '3px #ccc dashed');
  });
  $dropArea.on('dragleave', function (e) {
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', 'none');
  });

  $fileInput.on('change', function (e) {
    $dropArea.css('border', 'none');
    var file = this.files[0],
      $img = $(this).siblings('.prev-img'),
      fileReader = new FileReader();

    fileReader.onload = function (event) {
      // 読み込んだデータをimgに設定
      $img.attr('src', event.target.result).show();
    };

    fileReader.readAsDataURL(file);
  });
});
