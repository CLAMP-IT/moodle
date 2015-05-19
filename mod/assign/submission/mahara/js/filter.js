/**
 * Provides the code for filtering the list of pages & collections
 * from Mahara
 *
 * @package   mod-assign-submission-mahara
 * @author    Philip Cali <philip.cali@gmail.com>
 * @author    Tony Box <box@up.edu>
 * @copyright 2013 University of Portland
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

(function($) {
    var $searchBox = $.querySelector('#id_search');
    var $divs = $.querySelectorAll('div[id^=fitem_id_viewid_]');

    var toggleDiv = function(div, show) {
        if (!show && !div.style.display) {
            div.style.display = 'none';
        } else if (show) {
            div.style.display = '';
        }
    };

    var filterByName = function(name) {
        var reg = new RegExp(name, 'i');
        for ( var i = 0; i < $divs.length; i++) {
            var $div = $divs.item(i);
            var text;
            if (typeof $div.innerText != 'undefined') {
                text = $div.innerText;
            } else {
                text = $div.textContent;
            }
            toggleDiv($div, text.match(reg));
        }
        ;
    };

    $searchBox.addEventListener('keyup', function(e) {
        filterByName($searchBox.value);
    });
})(document);
