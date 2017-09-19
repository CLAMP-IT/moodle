/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {
	tinymce.create('tinymce.plugins.poodllPlugin', {
		init : function(ed, url) {
			this.editor = ed;
			
			//this logic needs to be centralised, currently in poodll.js too
			var itemid = this.getsimpleitemid();
			if(!itemid){
				itemid=this.getcomplexitemid();
        	}
        	if(itemid){
        		itemid = itemid.value;
        	}else{
        		itemid =0;
        	}
			
			var recorders = Array('audiomp3','audiored5','video', 'whiteboard','snapshot');
			var widths = Array(400,400,380,620,380);
//			var widths = Array('auto','auto','auto',620,380);
			var heights = Array(310,310,500,530,500);
			
			//get contexts
			var modulecontextid= 0;	
			var coursecontextid= 0;	
			if(M.editor_tinymce['filepicker_options']){
				var options = M.editor_tinymce.filepicker_options;						
				if (options.id_message && options.id_message.image) {
					if (options.id_message.image.context && options.id_message.image.context.id) {
						modulecontextid= options.id_message.image.context.id;	
						var thepath = options.id_message.image.context.path.split('/');
						if(thepath.length > 2){
							coursecontextid= thepath[3];
							//modulecontextid= thepath[4]; //should also work
						}
					}
				}
			}
			
			for (var therecorder = 0; therecorder < recorders.length; therecorder++) {

				// Register commands
				ed.addCommand('mcepoodll'+recorders[therecorder], 
					function(rec,wid,hei) {
						return function(){
							ed.windowManager.open({
								file : ed.getParam("moodle_plugin_base") + 'poodll/tinymce/poodll.php?itemid='+itemid + 
									'&recorder=' + rec + '&modulecontextid=' + modulecontextid + '&coursecontextid=' + coursecontextid,
								width : wid,
								height : hei,
								inline : 1
							}, {
								plugin_url : url
							});
						}
					}(recorders[therecorder],widths[therecorder],heights[therecorder])
				);
				
				//add buttons only if NOT in full screen mode
				if(document.getElementById("mce_fullscreen_container")===null){
					// Register buttons
					ed.addButton('poodll'+recorders[therecorder] ,{
						title : 'poodll.' + recorders[therecorder] + '_desc',
						cmd : 'mcepoodll' + recorders[therecorder],
						image: url + '/img/' + recorders[therecorder] + '_icon.png'
					});
				}
				

            
				//you could add a shortcut here
				//ed.addShortcut('ctrl+k', 'poodll.poodll_desc', 'mcepoodll');
			
			}

		
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

		getInfo : function() {
			return {
				longname : 'PoodLL Anywhere',
				author : 'Justin Hunt',
				version : '1.0.0'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('poodll', tinymce.plugins.poodllPlugin);
})();