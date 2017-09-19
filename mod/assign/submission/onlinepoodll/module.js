/**
 * Javascript for assignsubmission_onlinepoodll
 * 
 *
 * @copyright &copy; 2013 Justin Hunt
 * @author Justin Hunt
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package assignsubmission_onlinepoodll
 */

M.assignsubmission_onlinepoodll = {};
M.assignsubmission_onlinepoodll.deletesubmission = null;
M.assignsubmission_onlinepoodll.init = function(Y,opts) {
	M.assignsubmission_onlinepoodll.deletesubmission = function() {
		var fc = document.getElementById(opts['filecontrolid']);  
		if(fc){
				if(confirm(opts['reallydeletesubmission'])){
					fc.value='-1';
					var cont =  document.getElementById(opts['currentcontainer']);
					cont.innerHTML ='';
				}
			}
		return false;//this prevents a jump to page top.
	}
}

