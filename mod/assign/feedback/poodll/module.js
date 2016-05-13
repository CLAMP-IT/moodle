/**
 * Javascript for assignfeedback_poodll
 * 
 *
 * @copyright &copy; 2013 Justin Hunt
 * @author Justin Hunt
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package assignfeedback_poodll
 */

M.assignfeedback_poodll = {};
M.assignfeedback_poodll.deletefeedback = null;
M.assignfeedback_poodll.init = function(Y,opts) {
	M.assignfeedback_poodll.deletefeedback = function() {
		var fc = document.getElementById(opts['filecontrolid']);  
		if(fc){
				if(confirm(opts['reallydeletefeedback'])){
					fc.value='-1';
					var cont =  document.getElementById(opts['currentcontainer']);  
					cont.innerHTML ='';
				}
			}
		return false;//this prevents a jump to page top.
	}
}

