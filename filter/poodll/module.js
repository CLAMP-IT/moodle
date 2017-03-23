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
	
	getwhiteboard: Array(),
	
	whiteboards: Array(),
	
	whiteboardopts: Array(),
	
	timeouthandles: Array(),
	
	poodllopts: Array(),
	
	gyui: null,
	

// Called by PoodLL recorders to update filename field on page
	updatepage: function(args) {
	
			//record the url on the html page,							
			var filenamecontrol = document.getElementById(args[3]);
			if(filenamecontrol==null){ filenamecontrol = parent.document.getElementById(args[3]);} 			
			if(filenamecontrol){
				filenamecontrol.value = args[2];
			}
	},
	

	// load drawingboard whiteboard for Moodle
	loaddrawingboard: function(Y,opts) {
		
		//stash our opts array
		this.whiteboardopts[opts['recorderid']] = opts;
		
		//stash our Y for later use
		this.gyui = Y;

			if(opts['bgimage'] ){
				var erasercolor = 'transparent';
			}else{
				var erasercolor = 'background';
				opts['bgimage'] = '#FFF';
			}

		   // load the whiteboard and save the canvas reference
		   var db = new DrawingBoard.Board(opts['recorderid'] + '_drawing-board-id',{
		   			recorderid: opts['recorderid'],
					size: 3,
					background: opts['bgimage'],
					controls: ['Color',
								{ Size: { type: 'auto' } },
								{ DrawingMode: { filler: false,eraser: true,pencil: true } },
								'Navigation'
							],
					droppable: true,
					webStorage: false,
					enlargeYourContainer: true,
					eraserColor: erasercolor
				});
				
			//stash our whiteboard	
			M.filter_poodll.whiteboards[opts['recorderid']] = db;
			
			//restore vectordata
			var vectordata = opts['vectordata'];
			if(vectordata){
				//dont do anything if its not JSON (ie it coule be from LC)
				if(vectordata.indexOf('{"shapes"')!=0 && vectordata.indexOf('{"colors"')!=0){
					db.history = Y.JSON.parse(vectordata);
					db.setImg(db.history.values[db.history.position-1]);
				}
			}
			
			//register events. if autosave we need to do more.
			if(opts['autosave']){		
					//autosave, clear messages and save callbacks on start drawing
					db.ev.bind('board:startDrawing', (function(mfp,recid){
								return function(){
									var m = document.getElementById(recid + '_messages');
									if(m){
										m.innerHTML = 'File has not been saved.';
										var savebutton = document.getElementById(recid + '_btn_upload_whiteboard');
										savebutton.disabled=false;
										var th = M.filter_poodll.timeouthandles[recid];
										if(th){clearTimeout(th);}
									}
								}
							})(this,opts['recorderid'])							
					);

					//autosave, clear previous callbacks,set new save callbacks on stop drawing
					db.ev.bind('board:stopDrawing', (function(mfp,recid){
								return function(){
									var m = document.getElementById(recid + '_messages');
									if(m){
										var th = M.filter_poodll.timeouthandles[recid];
										if(th){clearTimeout(th);}
										M.filter_poodll.timeouthandles[recid] = setTimeout(
															function(){ M.filter_poodll.WhiteboardUploadHandler(recid);},
															M.filter_poodll.whiteboardopts[recid]['autosave']);
									}
								}
							})(this,opts['recorderid'])
					);

		
			}else{
				db.ev.bind('board:stopDrawing', (function(mfp,recid){
								return function(){
									var m = document.getElementById(recid + '_messages');
									if(m){
										m.innerHTML = 'File has not been saved.';
									}
								}
							})(this,opts['recorderid'])
				);
			}
			
			
		//set up the upload/save button
		var uploadbutton = this.getbyid(opts['recorderid'] + '_btn_upload_whiteboard');
		if(uploadbutton){
			if(opts['autosave']){
				uploadbutton.addEventListener("click", function(){M.filter_poodll.WhiteboardUploadHandler(opts['recorderid']);}, false);
			}else{
				uploadbutton.addEventListener("click", function(){M.filter_poodll.CallFileUpload(opts['recorderid']);}, false);
			}
		}
	
	},

	// handle literallycanvas whiteboard saves for Moodle
	loadliterallycanvas: function(Y,opts) {
	
		//stash our opts array
		this.whiteboardopts[opts['recorderid']] = opts;
		
		//stash our Y for later use
		this.gyui = Y;

			
			// load the whiteboard and save the canvas reference
			//logic a bit diff if we have a background image
			if(opts['bgimage']){
				var bgimg = new Image();
				bgimg.src = opts['bgimage'];
				bgimg.crossOrigin = "Anonymous";
			}else{
				var bgimg = null;
			}


			//init the whiteboard	(diff logic if have a background image)
			var lc_element = document.getElementById('#' + opts['recorderid'] + '_literally');
			
			if(opts['backgroundimage']){
				var backgroundimage= new Image();
				backgroundimage.src = opts['backgroundimage'];
				backgroundimage.crossOrigin = "Anonymous";
				
				var lc = LC.init(lc_element,{imageURLPrefix: opts['imageurlprefix'], 
					backgroundColor: opts['backgroundcolor'],
					recorderid: opts['recorderid'],
					backgroundShapes: [LC.createShape('Image', {x: 0, y: 0, image: backgroundimage, scale: 1})]
					});
			}else{
				var lc = LC.init(lc_element,{imageURLPrefix: opts['imageurlprefix'], 
					backgroundColor: opts['backgroundcolor'],
					recorderid: opts['recorderid']
					});
			}
		
			//restore previous drawing if any
			var vectordata = M.filter_poodll.whiteboardopts[opts['recorderid']]['vectordata'];
			if(vectordata){
				//don't restore drawingboardjs vector if its there, goes to error
				if(vectordata.indexOf('{"shapes"')==0){
					lc.loadSnapshotJSON(vectordata);
				}
			}
						
			//handle autosave
			if(opts['autosave']){
				//if user starts drawing, cancel the countdown to save
				lc.on('drawingStart',(function(mfp,recid){
					return function(){
						var m = document.getElementById(recid + '_messages');
						if(m){
							m.innerHTML = 'File has not been saved.';
							var savebutton = document.getElementById(recid + '_btn_upload_whiteboard');
							savebutton.disabled=false;
							var th = M.filter_poodll.timeouthandles[recid];
							if(th){clearTimeout(th);}
						}
					}
				})(this,opts['recorderid']));
				
				//if user has drawn commence countdown to save
				lc.on('drawingChange',(function(mfp,recid){
					return function(){
						var m = document.getElementById(recid + '_messages');
						if(m){
							var th = M.filter_poodll.timeouthandles[recid];
							if(th){clearTimeout(th);}
							M.filter_poodll.timeouthandles[recid] = setTimeout(
												function(){ M.filter_poodll.WhiteboardUploadHandler(recid);},
												M.filter_poodll.whiteboardopts[recid]['autosave']);
						}
					}
				})(this,opts['recorderid']));
			
			//if no autosave
			}else{
				//lc.on('drawingChange',(function(mfp){return function(){mfp.setUnsavedWarning;}})(this));
				//if user has drawn, alert to unsaved state
				lc.on('drawingChange',(function(mfp,recid){
					return function(){
						var m = document.getElementById(recid + '_messages');
						if(m){
							m.innerHTML = 'File has not been saved.';
						}
					}
				})(this,opts['recorderid']));
			}//end of handling autosave
			
			//store a handle to this whiteboard			
			M.filter_poodll.whiteboards[opts['recorderid']] = lc;
			
		//set up the upload/save button
		var uploadbutton = this.getbyid(opts['recorderid'] + '_btn_upload_whiteboard');
		if(uploadbutton){
			if(opts['autosave']){
				uploadbutton.addEventListener("click", function(){M.filter_poodll.WhiteboardUploadHandler(opts['recorderid']);}, false);
			}else{
				uploadbutton.addEventListener("click", function(){M.filter_poodll.CallFileUpload(opts['recorderid']);}, false);
			}
		}
	
	},

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

	// Call Upload file from drawingboard a, first handle autosave bits and pieces
	WhiteboardUploadHandler: function(recid) {
		// Save button disabling a little risky db perm. fails publish "startdrawing" after mode change
		var savebutton = this.getbyid(recid + '_btn_upload_whiteboard');
		savebutton.disabled=true;
		clearTimeout(this.timeouthandle);
		//call the file upload
		this.CallFileUpload(recid);
	},

	// Call Upload file from whiteboard canvas
	CallFileUpload: function(recid) {
	
		var wboard = this.whiteboards[recid];
		var cvs = null;
		var vectordata = "";
		if(recid.indexOf('drawingboard_')==0){
			cvs = wboard.canvas;	
			var vectordata = this.gyui.JSON.stringify(wboard.history , null,2);	
		}else{
			//we no longer use this LC technique, and will soon remove the css background logic
			if(this.whiteboardopts[recid]['bgimage']){
				cvs = wboard.canvasWithBackground($('#' + recid + '_separate-background-image').get(0))
			}else{
				cvs = wboard.canvasForExport();
			}
			//only LC has vector data it seems
			var vectordata = wboard.getSnapshotJSON();
		}

		//stash vectordata
		if(this.whiteboardopts[recid]['vectorcontrol']){
			var vc = this.getbyid(this.whiteboardopts[recid]['vectorcontrol']);
			if (vc){
				vc.value = vectordata;
			}
		}
		
		//prepare the upload
		var filedata =  cvs.toDataURL().split(',')[1];
		var file = {type:  'image/png'};
		this.UploadFile(file, filedata,recid);
	},

	// output information
	Output: function(recid,msg) {
		var m = this.getbyid(recid + '_messages');
		//m.innerHTML = msg + m.innerHTML;
		m.innerHTML = msg;
	},
	
	// getElementById
	getbyid: function(id) {
		return document.getElementById(id);
	},
	
	// getElementById
	getbyidinparent: function(id) {
		return parent.document.getElementById(id);
	},

	// upload Media files
	UploadFile: function(file, filedata,recid) {
		var opts = this.whiteboardopts[recid];
		var xhr = new XMLHttpRequest();

		//Might need more mimetypes than this, and 3gpp maynot work
		var ext="";
		switch(file.type){
			case "image/jpeg": ext = "jpg";break;
			case "image/png": ext = "png";break;
			case "video/quicktime": ext = "mov";break;
			case "audio/mpeg3": ext = "mp3";break;
			case "audio/x-mpeg-3": ext = "mp3";break;
			case "audio/mpeg3": ext = "mp3";break;
			case "audio/3gpp": ext = "3gpp";break;
			case "video/mpeg3": ext = "3gpp";break;
			case "video/mp4": ext = "mp4";break;	
		}
		
		if(true){
			// create progress bar if we have a container for it
			var o = this.getbyid(recid + "_progress");
			if(o!=null){
				var progress = o.firstChild;
				if(progress==null){
					progress = o.appendChild(document.createElement("p"));
				}
				//reset/set background position to 0, and label to "uploading
				progress.className="";
				progress.style.display = "block";
				progress.style.backgroundPosition = "100% 0";
				
				// progress bar
				xhr.upload.addEventListener("progress", function(e) {
					var pc = parseInt(100 - (e.loaded / e.total * 100));
					progress.style.backgroundPosition = pc + "% 0";
				}, false);
			}else{
				var progress=false;
			}
			this.Output(recid,"Uploading.");


			// file received/failed
			xhr.onreadystatechange = (function(mfp){return function(e) {
				
				if (xhr.readyState == 4 ) {
					if(progress){
						progress.className = (xhr.status == 200 ? "success" : "failure");
					}
					if(xhr.status==200){
						var resp = xhr.responseText;
						var start= resp.indexOf("success<filename>");
						if (start<1){return;}
						var end = resp.indexOf("</filename>");
						var filename= resp.substring(start+17,end);
						
						//invoke callbackjs if we have one, otherwise just update the control(default behav.)
						if(opts['callbackjs'] && opts['callbackjs']!=''){ 
							var callbackargs  = new Array();
							callbackargs[0]=opts['recorderid'];
							callbackargs[1]='filesubmitted';
							callbackargs[2]=filename;
							callbackargs[3]=opts['updatecontrol'];
							//window[opts['callbackjs']](callbackargs);
							mfp.Output(recid, "File saved successfully.");
							mfp.executeFunctionByName(opts['callbackjs'],window,callbackargs);
							
						}else{
							mfp.Output(recid, "File saved successfully.");
							var upc = mfp.getbyid(mfp.getbyid(recid + "_updatecontrol").value);
							if(!upc){upc = mfp.getbyidinparent(mfp.getbyid(recid + "_updatecontrol").value);}
							upc.value=filename;
						}
					}else{
						mfp.Output(recid, "File could not be uploaded.");
					}
				}
			}})(this);

			var params = "datatype=uploadfile";
			//We must URI encode the base64 filedata, because otherwise the "+" characters get turned into spaces
			//spent hours tracking that down ...justin 20121012
			params += "&paramone=" + encodeURIComponent(filedata);
			params += "&paramtwo=" + ext;
			params += "&paramthree=" + this.getbyid(recid + "_mediatype").value;
			params += "&requestid=" + recid;
			params += "&contextid=" + this.getbyid(recid + "_contextid").value;
			params += "&component=" + this.getbyid(recid + "_component").value;
			params += "&filearea=" + this.getbyid(recid + "_filearea").value;
			params += "&itemid=" + this.getbyid(recid + "_itemid").value;
					
			xhr.open("POST", this.getbyid(recid + "_fileliburl").value, true);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xhr.setRequestHeader("Cache-Control", "no-cache");
			xhr.setRequestHeader("Content-length", params.length);
			xhr.setRequestHeader("Connection", "close");
			xhr.send(params);
		}

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
	},
	
	// Start of text 
	loadscroller: function(Y,opts) {
		//stash our Y for later use
		if(!this.gyui){
			this.gyui = Y;
		}
	
		if(typeof window.scrollopts== 'undefined'){
				window.scrollopts = new Array();
			}
		window.scrollopts[opts['scrollerid']] = opts;
	},
	 
	KickOff: function(scrollerid){
	
		if(typeof AreaHeight == 'undefined'){
			AreaHeight = new Array();
			AreaWidth = new Array();
		}
		AreaHeight[scrollerid]=dataobj[scrollerid].offsetHeight;
		AreaWidth[scrollerid]=dataobj[scrollerid].offsetWidth;
		
		if(scrollopts[scrollerid]['axis']=="y"){
			this.DoScrollAxisY(scrollerid);
		}else{
			this.DoScrollAxisX(scrollerid);
		}
	
	},
	 
	ScrollBoxStart: function(scrollerid){
		if(typeof dataobj == 'undefined'){
			dataobj = new Array();
		}
		dataobj[scrollerid]= document.getElementById("p_scrollbox" + scrollerid );
		dataobj[scrollerid].style.top=scrollopts[scrollerid]['topspace'];
		dataobj[scrollerid].style.left=scrollopts[scrollerid]['leftspace'];
		var startbutton = document.getElementById("p_scrollstartbutton" + scrollerid );
		startbutton.style.display='none';
		this.KickOff(scrollerid);

	},
	 
	DoScrollAxisY: function(scrollerid){
		var scroller = dataobj[scrollerid];
		var opts = scrollopts[scrollerid];
		scroller.style.top=(parseInt(scroller.style.top)- opts['pixelshift']) + "px";
		if (parseInt(scroller.style.top)<AreaHeight[scrollerid]*(-1)) {
			scroller.style.top=opts['framesize'];
			if(opts['repeat']=='yes'){
				var startbutton = document.getElementById("p_scrollstartbutton" + scrollerid );
				startbutton.style.display='';
			}
		}else {

			//setTimeout(function() {DoScrollAxisY(scrollerid);},opts['scrollspeed']);
			
			setTimeout(function(thescrollerid) {return function() {
						M.filter_poodll.DoScrollAxisY(thescrollerid);}}(scrollerid),
						opts['scrollspeed']);
		}
	},
	
	DoScrollAxisX: function(scrollerid){
		var scroller = dataobj[scrollerid];
		var opts = scrollopts[scrollerid];
		scroller.style.left=(parseInt(scroller.style.left)- opts['pixelshift']) + "px";
		if (parseInt(scroller.style.left)<AreaWidth[scrollerid]*(-1)) {
			scroller.style.left=opts['framesize'];
			if(opts['repeat']=='yes'){
				var startbutton = document.getElementById("p_scrollstartbutton" + scrollerid);
				startbutton.style.display='';
			}
		}else {
			//setTimeout(function() {DoScrollAxisX(scrollerid);},opts['scrollspeed']);
			
			setTimeout(function(thescrollerid) {return function() {
						M.filter_poodll.DoScrollAxisX(thescrollerid);}}(scrollerid),
						opts['scrollspeed']);
			
		}
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
 
