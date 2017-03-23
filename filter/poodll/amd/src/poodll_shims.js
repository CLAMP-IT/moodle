/* jshint ignore:start */
define([], function() {
	window.requirejs.config({
		paths: {
		    "db-jquery": M.cfg.wwwroot + '/filter/poodll/3rdparty/jquery/jquery-1.12.4.min',
			"lzflash": M.cfg.wwwroot + '/filter/poodll/flash/embed-compressed',
			"drawingboard": M.cfg.wwwroot + '/filter/poodll/js/drawingboard.js/dist/drawingboard.min'
		},
		shim: {
		    'db-jquery' : {exports: '$'},
			'lzflash' : {exports: 'lz'},
			'drawingboard' : {exports: 'DrawingBoard', deps: ['db-jquery']}
		}
	});//end of window.requirejs.config
});