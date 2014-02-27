(function($) {
  var $searchBox = $.querySelector('#id_search');
  var $divs = $.querySelectorAll('div[id^=fitem_id_view_]');

  var toggleDiv = function(div, show) {
    if (!show && !div.style.display) {
      div.style.display = 'none';
    } else if (show) {
      div.style.display = '';
    }
  };

  var filterByName = function(name) {
    var reg = new RegExp(name, 'i');
    for (var i = 0; i < $divs.length; i++) {
      var $div = $divs.item(i);
      toggleDiv($div, $div.innerText.match(reg));
    };
  };

  $searchBox.addEventListener('keyup', function(e) {
    filterByName($searchBox.value);
  });
})(document);
