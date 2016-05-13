/* Functions for the PoodLL Anywhere plugin popup */

tinyMCEPopup.requireLangPack();


var tinymce_poodll_Dialog = {
	init : function() {
		this.resize();
	},

	insert : function(recorder,filename) {
		//there must be a better way than this to get itemid
		var itemid = this.getsimpleitemid();
		if(!itemid){
			itemid=this.getcomplexitemid();
        }
        
        var contextid = document.getElementById('context_id');
        var thefilename = document.getElementById(filename);
        var wwwroot = document.getElementById('wwwroot');
        //if no file is there to insert, don't do it
        if(!thefilename.value){
        	alert(tinyMCEPopup.getLang('poodll.nothingtoinsert'));
        	return;
        }

        if (itemid) {
           itemid = itemid.value;
           contextid = contextid.value;
           thefilename = thefilename.value;
           wwwroot = wwwroot.value
           
		   // It will store in mdl_question with the "@@PLUGINFILE@@/myfile.mp3" for the filepath.
		   var filesrc =wwwroot+'/draftfile.php/'+contextid+'/user/draft/'+itemid+'/'+thefilename;
			//if image file, don't insert link, insert image
		   if(recorder=='snapshot' ||recorder=='whiteboard'){
				this.insertimage(filesrc);
				return;
			}

           var h = '<a href="'+filesrc+'">'+thefilename+'</a>';
           // Insert the contents from the input into the document.
           tinyMCEPopup.execCommand('mceInsertContent', false,h);
        }
        tinyMCEPopup.restoreSelection();
        tinyMCEPopup.close();
	},
	
	enableinsert: function(){
		var insertbutton = document.getElementById('insert');
		insertbutton.disabled = false;
	},
	
	getsimpleitemid : function(){
        var formtextareaid = tinyMCE.activeEditor.id;
        var formtextareaname = formtextareaid.substr(0,formtextareaid.length-3);
        var itemidname =  formtextareaname + ':itemid';
        var itemid = window.top.document.getElementsByName(itemidname).item(0);
        return itemid;
	},
	
	getcomplexitemid : function(){
			var formtextareaid = tinyMCE.activeEditor.id.substr(3);
			var formtextareatmp = formtextareaid.split("_");
			if (formtextareatmp.length == 2 && !isNaN(formtextareatmp[1])) {
			   var itemidname = formtextareatmp[0] + '[' + formtextareatmp[1] + '][itemid]';
			}
			else {
			   var itemidname = formtextareaid + '[itemid]';   
			}
			var itemid = window.top.document.getElementsByName(itemidname).item(0);
			return itemid;
	},

	insertimage : function(imgsrc) {
		var ed = tinyMCEPopup.editor, args = {}, el;
		tinyMCEPopup.restoreSelection();

		// Fixes crash in Safari
		if (tinymce.isWebKit)
			ed.getWin().focus();

		if (!ed.settings.inline_styles) {
			args = {
				vspace : 0,
				hspace : 0,
				border : 0,
				align : 'left'
			};
		} else {
			// Remove deprecated values
			args = {
				vspace : '',
				hspace : '',
				border : '',
				align : ''
			};
		}

		tinymce.extend(args, {
			src : imgsrc.replace(/ /g, '%20'),
		});


		el = ed.selection.getNode();

		if (el && el.nodeName == 'IMG') {
			ed.dom.setAttribs(el, args);
		} else {
			tinymce.each(args, function(value, name) {
				if (value === "") {
					delete args[name];
				}
			});

			ed.execCommand('mceInsertContent', false, tinyMCEPopup.editor.dom.createHTML('img', args), {skip_undo : 1});
			ed.undoManager.add();
		}

		tinyMCEPopup.editor.execCommand('mceRepaint');
		tinyMCEPopup.editor.focus();
		tinyMCEPopup.close();
	},
	resize : function() {
		var vp = tinyMCEPopup.dom.getViewPort(window), el;
		/* donothing 
		el = document.getElementById('content');
		el.style.width  = (vp.w - 20) + 'px';
		el.style.height = (vp.h - 90) + 'px';
		*/
		//console.log('vp/w:' + vp.w);
		//console.log('vp/h:' + vp.h);
		
	},
	// Called by PoodLL recorders to update filename field on page
	updatefilename : function(args) {
		//record the url on the html page,							
		var filenamecontrol = document.getElementById(args[3]);
		if(filenamecontrol==null){ filenamecontrol = parent.document.getElementById(args[3]);} 			
		if(filenamecontrol){
			filenamecontrol.value = args[2];
			var insertbutton = document.getElementById('insert');
			insertbutton.disabled = false;
		}
		
		//console.log("just  updated: " + args[3] + ' with ' + args[2]);
	}
};

tinyMCEPopup.onInit.add(tinymce_poodll_Dialog.init, tinymce_poodll_Dialog);