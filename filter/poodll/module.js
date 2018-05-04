/**
 * Javascript for loading swf widgets , espec flowplayer for PoodLL
 *
 * @copyright &copy; 2012 Justin Hunt
 * @author poodllsupport@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package filter_poodll
 */

M.filter_poodll = {

	getwhiteboardcanvas: Array(),
	
	timeouthandles: Array(),
	
	poodllopts: Array(),
	
	gyui: null,
	


/*
	 * Image methods: To download an image to desktop
	 */
	getCanvasBackgroundImage: function() {
		var cvs = this.getwhiteboardcanvas();
		return cvs.toDataURL("image/png");
	},

	downloadCanvasBackgroundImage: function() {
		var img = this.getImg();
		img = img.replace("image/png", "image/octet-stream");
		window.location.href = img;
	},


	//function to call the callback function with arguments	
	executeFunctionByName: function(functionName, context , args ) {
	
		  //var args = Array.prototype.slice.call(arguments).splice(2);
		  var namespaces = functionName.split(".");
		  var func = namespaces.pop();
		  for(var i = 0; i < namespaces.length; i++) {
			context = context[namespaces[i]];
		  }
		  return context[func].call(this, args);
	}
	

};//end of M.filter_poodll

M.filter_poodll.laszlohelper = {

	init: function (Y, opts) {
		lz.embed.swf(Y.JSON.parse(opts['widgetjson']));
	}
};

//PoodLL Templates
M.filter_poodll_templates = {
	
	csslinks: Array(),
	
	gyui: null,
	
	injectcss: function(csslink){
		var link = document.createElement("link");
		link.href = csslink;
		if(csslink.toLowerCase().lastIndexOf('.html')==csslink.length-5){
			link.rel = 'import';
		}else{
			link.type = "text/css";
			link.rel = "stylesheet";	
		}
		document.getElementsByTagName("head")[0].appendChild(link);	
	},
	
	// load templates CSS for poodll filter templates, if AMD not ok
	loadtemplate: function(Y,opts) {
		//stash our Y and opts for later use
		this.gyui = Y;
		//console.log(opts);
		//load our css in head if required
		//only do it once per extension though
		if(opts['CSSLINK']){
			if (this.csslinks.indexOf(opts['CSSLINK'])<0){
				this.csslinks.push(opts['CSSLINK']);
				this.injectcss(opts['CSSLINK']);
			}
		}
		//load our css in head if required
		//only do it once per extension though
		if(opts['CSSUPLOAD']){
			if (this.csslinks.indexOf(opts['CSSUPLOAD'])<0){
				this.csslinks.push(opts['CSSUPLOAD']);
				this.injectcss(opts['CSSUPLOAD']);
			}
		}
		
		//load our css in head if required
		//only do it once per extension though
		if(opts['CSSCUSTOM']){
			if (this.csslinks.indexOf(opts['CSSCUSTOM'])<0){
				this.csslinks.push(opts['CSSCUSTOM']);
				this.injectcss(opts['CSSCUSTOM']);
			}
		}
		
		if(typeof filter_poodll_extfunctions != 'undefined'){ 
			if(typeof filter_poodll_extfunctions[opts['TEMPLATEID']] == 'function'){ 
				filter_poodll_extfunctions[opts['TEMPLATEID']](opts);
			}
		}
		
	}//end of function
}//end of class
 
