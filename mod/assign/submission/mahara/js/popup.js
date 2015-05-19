/**
 * Provides the code for the display of Mahara pages in a shadow box
 *
 * @package   mod-assign-submission-mahara
 * @author    Philip Cali <philip.cali@gmail.com>
 * @author    Tony Box <box@up.edu>
 * @copyright 2013 University of Portland
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

(function() {
  var body = document.querySelector('body');

  var usePageOrBody = function() {
    var page = document.querySelector("#page");
    if (typeof page === 'undefined') {
      page = body;
    } else {
      var chosenWidth = Math.max(page.offsetWidth, body.offsetWidth);
      var chosenHeight = Math.max(page.offsetHeight, body.offsetHeight);
      page = {
        offsetWidth: chosenWidth,
        offsetHeight: chosenHeight,
        offsetTop: page.offsetHeight == chosenHeight ? page.offsetTop: 0,
        offsetLeft: page.offsetWidth == chosenWidth ? page.offsetLeft : 0
      };
    }

    return page;
  };

  var resizeDialog = function(e) {
    var dialog = document.querySelector('div.portfolio-dialog');
    var overlay = document.querySelector('div.portfolio-overlay');
    var page = usePageOrBody();

    dialog.style.top = ((window.innerHeight - Math.min(480, window.innerHeight)) / 2) + window.pageYOffset + 'px';
    dialog.style.left = ((window.innerWidth - Math.min(800, window.innerWidth)) / 2) + 'px';

    overlay.style.width = (page.offsetWidth) + 'px';
    overlay.style.height = (page.offsetHeight) + 'px';
    overlay.style.top = page.offsetTop + 'px';
    overlay.style.left = page.offsetLeft + 'px';
  };

  var removeElement = function() {
    body.style.overflow = '';
    body.removeChild(document.querySelector('div.portfolio-overlay'));
    body.removeChild(document.querySelector('div.portfolio-dialog'));
    window.removeEventListener('resize', resizeDialog);
  };

  document.addEventListener('keyup', function(e) {
    if (e.keyCode === 27) removeElement();
  });

  var createOverlay = function() {
    body.style.overflow = 'hidden';

    var page = usePageOrBody();
    var overlay = document.createElement('div');
    overlay.setAttribute('class', 'portfolio-overlay');
    overlay.style.zIndex = 10000;
    overlay.style.width = (page.offsetWidth) + 'px';
    overlay.style.height = (page.offsetHeight) + 'px';
    overlay.style.backgroundColor = '#000';
    overlay.style.position = 'absolute';
    overlay.style.top = page.offsetTop + 'px';
    overlay.style.left = page.offsetLeft + 'px';

    overlay.style.opacity = 0.5;
    overlay.style.filter = 'alpha(opacity=50)';

    body.appendChild(overlay);
    overlay.addEventListener('click', removeElement);

    return overlay;
  };

  var openDialog = function(url) {
    var overlay = createOverlay();
    var dialog = document.createElement('div');
    dialog.setAttribute('class', 'portfolio-dialog');
    dialog.style.padding = '12px';
    dialog.style.position = 'absolute';
    dialog.style.top = ((window.innerHeight - Math.min(480, window.innerHeight)) / 2) + window.pageYOffset + 'px';
    dialog.style.left = ((window.innerWidth - Math.min(800, window.innerWidth)) / 2) + 'px';
    dialog.style.borderRadius = '5px';
    dialog.style.MozBorderRadius = '5px';
    dialog.style.width = '800px';
    dialog.style.height = '480px';
    dialog.style.backgroundColor = '#eee';
    dialog.style.zIndex = overlay.style.zIndex + 1;

    var iframe = document.createElement('iframe');
    iframe.style.width = '100%';
    iframe.style.height = '100%';
    iframe.src = url;

    dialog.appendChild(iframe);
    body.appendChild(dialog);
    window.addEventListener('resize', resizeDialog);

    return dialog;
  };

  var links = document.querySelectorAll('a.portfolio.popup');

  for(var i = 0; i < links.length; i++) {
    var link = links.item(i);
    link.addEventListener('click', function(e) {
      openDialog(e.target.parentNode.getAttribute('href'));

      if (e.preventDefault) {
        e.preventDefault();
      }

      if (e.stopPropagation) {
        e.stopPropagation();
      }

      return false;
    });
  }
})();
