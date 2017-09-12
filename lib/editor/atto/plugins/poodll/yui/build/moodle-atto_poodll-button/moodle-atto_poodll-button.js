YUI.add('moodle-atto_poodll-button', function (Y, NAME) {

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * @package    atto_poodll
 * @copyright  2016 Justin Hunt  <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_poodll-button
 */

/**
 * Atto text editor poodll plugin.
 *
 * @namespace M.atto_poodll
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

var COMPONENTNAME = 'atto_poodll';
var POODLLFILENAME = 'poodllfilename';
var LOGNAME = 'atto_poodll';



var CSS = {
        INPUTSUBMIT: 'atto_media_urlentrysubmit',
        INPUTCANCEL: 'atto_media_urlentrycancel',
        NAMEBUTTON: 'atto_poodll_templatebutton',
        HEADERTEXT: 'atto_poodll_headertext',
        INSTRUCTIONSTEXT: 'atto_poodll_instructionstext',
        TEMPLATEVARIABLE: 'atto_poodll_templatevariable'     
    };

var TEMPLATE = '' +
  '<form class="atto_form">' +
   '<div id="{{elementid}}_{{innerform}}" class="mdl-align">' +
   '<input id="{{elementid}}_{{poodllfilename}}" type="hidden" name="{{elementid}}_{{poodllfilename}}" />' +
	'<button class="{{CSS.INPUTSUBMIT}}">{{get_string "insert" component}}</button>' +
    '</div>' +
	'</form>';

var IMAGETEMPLATE = '' + '<img src="{{url}}" alt="{{alt}}"/>';

var FIELDSHEADERTEMPLATE = '' +
        '<div id="{{elementid}}_{{innerform}}" class="mdl-align">' +
            '<h4 class="' + CSS.HEADERTEXT + '">{{headertext}} {{key}}</h4>' +
            '<div class="' + CSS.INSTRUCTIONSTEXT + '">{{instructions}}</div>' +
        '</div>';

var BUTTONSHEADERTEMPLATE = '' +
        '<div id="{{elementid}}_{{innerform}}" class="mdl-align">' +
            '<h4 class="' + CSS.HEADERTEXT + '">{{headertext}}</h4>' +
        '</div>';
        
var BUTTONTEMPLATE = '' +
        '<div id="{{elementid}}_{{innerform}}" class="atto_widget_buttons mdl-align">' +
            '<button class="' + CSS.NAMEBUTTON + '_{{templateindex}}">{{name}}</button>' +
        '</div>';
		
var FIELDTEMPLATE = '' +
        '<div id="{{elementid}}_{{innerform}}" class="mdl-align">{{variable}}' +
            '&nbsp;<input type="text" class="' + CSS.TEMPLATEVARIABLE + '_{{variableindex}} atto_widget_field" value="{{defaultvalue}}"></input>' +
        '</div>';
var SELECTCONTAINERTEMPLATE = '' +
            '<div id="{{elementid}}_{{innerform}}" class="mdl-align">{{variable}}</div>';
			
var SELECTTEMPLATE = '' +
            '<select class="' + CSS.TEMPLATEVARIABLE + '_{{variableindex}} atto_widget_field"></select>';

var OPTIONTEMPLATE ='' +
		'<option value="{{option}}">{{option}}</option>';

var SUBMITTEMPLATE = '' +
  '<form class="atto_form">' +
   '<div id="{{elementid}}_{{innerform}}" class="mdl-align">' +
	'<button class="' + CSS.INPUTSUBMIT +'">{{inserttext}}</button>' +
    '</div>' +
	'</form>';



Y.namespace('M.atto_poodll').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

    /**
     * A reference to the current selection at the time that the dialogue
     * was opened.
     *
     * @property _currentSelection
     * @type Range
     * @private
     */
    _currentSelection: null,

    /**
     * A reference to the dialogue content.
     *
     * @property _content
     * @type Node
     * @private
     */
    _content: null,
    _currentrecorder: null,
    _itemid: null,
    _usercontextid: null,
    _coursecontextid: null,
    _modulecontextid: null,
    _usewhiteboard: null,

    initializer: function(config) {
   		this._usercontextid = config.usercontextid;
   		this._coursecontextid = config.coursecontextid;
   		this._usewhiteboard = config.usewhiteboard;
   		
        var host = this.get('host');
        var options = host.get('filepickeroptions');
        if (options.image && options.image.itemid) {
            this._itemid =  options.image.itemid;
            if (options.image.context && options.image.context.id) {
            	this._modulecontextid = options.image.context.id;
            }
        } else {
            return;
        }
        
        //if we don't have the capability, or no file uploads allowed, give up.
        if(config.disabled){
        	return;
        }
      
     
    	var recorders = new Array('audiomp3','audiored5','video', 'whiteboard','snapshot','widgets');
    	for (var therecorder = 0; therecorder < recorders.length; therecorder++) {
			// Add the poodll button first (if we are supposed to)
			if(config.hasOwnProperty(recorders[therecorder])){
				this.addButton({
					icon: recorders[therecorder],
					iconComponent: 'atto_poodll',
					title: recorders[therecorder] + '_desc',
					buttonName: recorders[therecorder],
					callback: this._displayDialogue,
					callbackArgs: recorders[therecorder]
				});
			}
        }
        
       
    },
 
     /**
     * Display the PoodLL Recorder files.
     *
     * @method _displayDialogue
     * @private
     */
    _displayDialogue: function(e, therecorder) {
    	e.preventDefault();
    	this._currentrecorder = therecorder;
    	
    	if(therecorder=='widgets'){
    		this._displayWidgetsDialogue(e,therecorder);
    		return;
    	}
    	
    	var width=400;
    	var height=260;
    	switch(therecorder){
    		case 'audiomp3': 
    		case 'audiored5': width=360;
    						height=260;
    						break;
    		case 'video': 
    		case 'snapshot':width=360;
    						height=450;
    						break;
    		case 'whiteboard': width=680;
    						height=540;
    						break;
    	}
    	
		//the dialogue widths are a bit bogus
        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('dialogtitle', COMPONENTNAME),
            width: width + 'px',
            focusAfterHide: therecorder
        });
        if(dialogue.width != width + 'px'){
        	dialogue.set('width',width + 30 +'px');
        }

        var iframeid = 'atto_poodll_dialog_iframe_' + new Date().getTime();
        //var iframe = Y.Node.create('<iframe id="' + iframeid + '" width="300px" height="150px"></iframe>');
       // var iframe = Y.Node.create('<iframe id="' + iframeid + '" width="auto" height="' + height +  'px"></iframe>');
        var iframe = Y.Node.create('<iframe id="' + iframeid + '" width="' + width +  'px" height="' + height +  'px"></iframe>');
        
        iframe.setStyles({
            border: 'none',
            overflow: 'hidden'
        });

	//set attributes on the iframe
        iframe.setAttribute('src', this._getIframeURL(therecorder, iframeid));
        iframe.setAttribute('scrolling', 'no');
        
        //append buttons to iframe
        var buttonform = this._getFormContent();
        
        var bodycontent =  Y.Node.create('<div class="atto_poodll_iframe_container"></div>');
        bodycontent.append(iframe).append(buttonform);

        //set to bodycontent
        dialogue.set('bodyContent', bodycontent);
        dialogue.show();
        this.markUpdated();
    },

    /** 
     * Returns the URL to the file manager.
     *
     * @param _getIframeURL
     * @return {String} URL
     * @private
     */
    _getIframeURL: function(therecorder, iframeid) {
        return M.cfg.wwwroot + '/lib/editor/atto/plugins/poodll/dialog/poodll.php?' + 
          'itemid='+ this._itemid + '&recorder=' + therecorder + '&usewhiteboard=' + this._usewhiteboard  + 
          '&iframeid=' + iframeid + '&coursecontextid=' + this._coursecontextid + '&modulecontextid=' + this._modulecontextid +
          '&updatecontrol=' + this._getFilenameControlName();
    },
    
        /**
     * Return the dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getDialogueContent
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getFormContent: function() {
        var template = Y.Handlebars.compile(TEMPLATE),
            content = Y.Node.create(template({
                elementid: this.get('host').get('elementid'),
                CSS: CSS,
                poodllfilename: POODLLFILENAME,
                component: COMPONENTNAME
            }));

        this._form = content;
        this._form.one('.' + CSS.INPUTSUBMIT).on('click', this._doInsert, this);
        return content;
    },

	
        /**
     * Get the id of the filename control where poodll stores filename
     *
     * @method _getFilenameControlName
     * @return {String} the name/id of the filename form field
     * @private
     */
	_getFilenameControlName: function(){
		return(this.get('host').get('elementid') + '_' + POODLLFILENAME);
	},
	
	
        /**
     * Inserts the url/link onto the page
     * @method _getDialogueContent
     * @private
     */
	_doInsert : function(e){
		e.preventDefault();
		this.getDialogue({
            focusAfterHide: null
        }).hide();
        
        var thefilename = document.getElementById(this._getFilenameControlName());
        //if no file is there to insert, don't do it
        if(!thefilename.value){
        	return;
        }

         var thefilename = thefilename.value;
         var wwwroot = M.cfg.wwwroot;
         var mediahtml='';
           
		   // It will store in mdl_question with the "@@PLUGINFILE@@/myfile.mp3" for the filepath.
		   var filesrc =wwwroot+'/draftfile.php/'+  this._usercontextid +'/user/draft/'+this._itemid+'/'+thefilename;

		//if this is an image, insert the image
		if(this._currentrecorder==='snapshot' ||this._currentrecorder==='whiteboard'){
			template = Y.Handlebars.compile(IMAGETEMPLATE);
            mediahtml = template({
                url: filesrc,
                alt: thefilename
            });		
        //otherwise insert the link
		}else{
			mediahtml = '<a href="'+filesrc+'">'+thefilename+'</a>';
		}

		this.editor.focus();
		this.get('host').insertContentAtFocusPoint(mediahtml);
		this.markUpdated();
		
	},

	    /**
     * Called by PoodLL recorders directly to update filename field on page
     * @method updatefilename
     * @public
     */
	updatefilename : function(args) {
		//record the url on the html page						
		//var filenamecontrol = document.getElementById(args[3]);
		var filenamecontrol = document.getElementById(this._getFilenameControlName());
		if(filenamecontrol===null){ filenamecontrol = parent.document.getElementById(args[3]);} 			
		if(filenamecontrol){
			filenamecontrol.value = args[2];
			//var insertbutton = document.getElementById('insert');
			 this._form.one('.' + CSS.INPUTSUBMIT).disabled=false;
			//insertbutton.disabled = false;
		}
		
		//console.log("just  updated: " + args[3] + ' with ' + args[2]);
	},
	
	
	/**
     * Display the widgets dialog
     *
     * @method _displayDialogue
     * @private
     */
    _displayWidgetsDialogue: function(e, clickedicon) {
        e.preventDefault();
        var width=400;


        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('dialogtitle', COMPONENTNAME),
            width: width + 'px',
            focusAfterHide: clickedicon
        });
		//dialog doesn't detect changes in width without this
		//if you reuse the dialog, this seems necessary
        if(dialogue.width !== width + 'px'){
            dialogue.set('width',width+'px');
        }
        
        //create content container
        var bodycontent =  Y.Node.create('<div></div>');
        
        //create and append header
        var template = Y.Handlebars.compile(BUTTONSHEADERTEMPLATE),
            	content = Y.Node.create(template({
                headertext: M.util.get_string('chooseinsert', COMPONENTNAME)
            }));
         bodycontent.append(content);

        //get button nodes
        var buttons = this._getButtonsForNames(clickedicon);

        
         Y.Array.each(buttons, function(button) {  	 
            //loop start
                bodycontent.append(button);
            //loop end
        }, bodycontent);
     

        //set to bodycontent
        dialogue.set('bodyContent', bodycontent);
        dialogue.show();
        this.markUpdated();
    },

	    /**
     * Display the chosen widgets template form
     *
     * @method _showTemplateForm
     * @private
     */
    _showTemplateForm: function(e,templateindex) {
        e.preventDefault();
        var width=400;

		
        var dialogue = this.getDialogue({
            headerContent: M.util.get_string('dialogtitle', COMPONENTNAME),
            width: width + 'px'
        });
		//dialog doesn't detect changes in width without this
		//if you reuse the dialog, this seems necessary
        if(dialogue.width !== width + 'px'){
            dialogue.set('width',width+'px');
        }

        //get fields , 1 per variable
        var fields = this._getTemplateFields(templateindex);
        var instructions = this.get('instructions')[templateindex];
            instructions = decodeURIComponent(instructions);
	
		//get header node. It will be different if we have no fields
		if(fields && fields.length>0){
			var useheadertext  = M.util.get_string('fieldsheader', COMPONENTNAME);
		}else{
			var useheadertext =  M.util.get_string('nofieldsheader', COMPONENTNAME);
		}
		var template = Y.Handlebars.compile(FIELDSHEADERTEMPLATE),
            	content = Y.Node.create(template({
                key: this.get('keys')[templateindex],
                headertext: useheadertext,
                instructions: instructions
            }));
        var header = content;
		
		//set container for our nodes (header, fields, buttons)
        var bodycontent =  Y.Node.create('<div></div>');
        
        //add our header
         bodycontent.append(header);
        
        //add fields
         Y.Array.each(fields, function(field) {  	 
            //loop start
                bodycontent.append(field);
            //loop end
        }, bodycontent);
     
     	//add submit button
     	var submitbuttons = this._getSubmitButtons(templateindex);
     	bodycontent.append(submitbuttons)

        //set to bodycontent
        dialogue.set('bodyContent', bodycontent);
        dialogue.show();
        this.markUpdated();
    },

  /**
     * Return the widget dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getSubmitButtons
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getSubmitButtons: function(templateindex) {
  
        var template = Y.Handlebars.compile(SUBMITTEMPLATE),
        	
            content = Y.Node.create(template({
                elementid: this.get('host').get('elementid'),
                inserttext:  M.util.get_string('insert', COMPONENTNAME)
            }));
     
		content.one('.' + CSS.INPUTSUBMIT).on('click', this._doWidgetsInsert, this, templateindex);
        return content;
    },


   /**
     * Return a field (yui node) for each variable in the template
     *
     * @method _getTemplateFields
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getTemplateFields: function(templateindex) {
    
    	var allcontent=[];
    	var thekey=this.get('keys')[templateindex];
    	var thevariables=this.get('variables')[templateindex];
    	var thedefaults=this.get('defaults')[templateindex];
    	
    	//defaults array 
    	//var defaultsarray=this._getDefArray(thedefaults);
		var defaultsarray=thedefaults;
		
    	 Y.Array.each(thevariables, function(thevariable, currentindex) { 	 
            //loop start
			if((thevariable in defaultsarray) && defaultsarray[thevariable].indexOf('|')>-1){
			
				var containertemplate = Y.Handlebars.compile(SELECTCONTAINERTEMPLATE),
					content = Y.Node.create(containertemplate({
					elementid: this.get('host').get('elementid'),
					variable: thevariable,
					defaultvalue: defaultsarray[thevariable],
					variableindex: currentindex
				}));
			
				var selecttemplate = Y.Handlebars.compile(SELECTTEMPLATE),
					selectbox = Y.Node.create(selecttemplate({
					variable: thevariable,
					defaultvalue: defaultsarray[thevariable],
					variableindex: currentindex
				}));
			
				var opts = defaultsarray[thevariable].split('|');
				var htmloptions="";
				var opttemplate = Y.Handlebars.compile(OPTIONTEMPLATE);
				Y.Array.each(opts, function(opt, optindex) {
					var optcontent = Y.Node.create(opttemplate({
							option: opt
						}));
					selectbox.appendChild(optcontent);
				});
				content.appendChild(selectbox);
				
			}else{
			
				 var template = Y.Handlebars.compile(FIELDTEMPLATE),
					content = Y.Node.create(template({
					elementid: this.get('host').get('elementid'),
					variable: thevariable,
					defaultvalue: defaultsarray[thevariable],
					variableindex: currentindex
				}));
			}
			
			
            allcontent.push(content);
            //loop end
        }, this);


        return allcontent;
    },


     /**
     * Return the dialogue content for the tool, attaching any required
     * events.
     *
     * @method _getButtonsForNames
     * @return {Node} The content to place in the dialogue.
     * @private
     */
    _getButtonsForNames: function(clickedicon) {
    
    	var allcontent=[];
    	 Y.Array.each(this.get('names'), function(thename, currentindex) { 	 
            //loop start
             var template = Y.Handlebars.compile(BUTTONTEMPLATE),
            	content = Y.Node.create(template({
            	elementid: this.get('host').get('elementid'),
                name: thename,
                templateindex: currentindex
            }));
            this._form = content;
            content.one('.' + CSS.NAMEBUTTON + '_' + currentindex).on('click', this._showTemplateForm, this,currentindex);
            allcontent.push(content);
            //loop end
        }, this);

        return allcontent;
    },
    
    _getDefArray: function(thedefaults){
    	//defaults array 
    	var defaultsarray=[];
    	var defaultstemparray = thedefaults.match(/([^=,]*)=("[^"]*"|[^,"]*)/g);//thedefaults.split(',');
    	Y.Array.each(defaultstemparray, function(defset){
    		//loop start
    		var defsetarray = defset.split('=');
    		if(defsetarray && defsetarray.length>1){
    			defaultsarray[defsetarray[0]] = defsetarray[1].replace(/"/g,'');
    		}
    	 //loop end
        }, this);
        return defaultsarray;
    
    },

    /**
     * Inserts the users input onto the page
     * @method _getDialogueContent
     * @private
     */
    _doWidgetsInsert : function(e,templateindex){
        e.preventDefault();
        this.getDialogue({
            focusAfterHide: null
        }).hide();
        
        var retstring = "{POODLL:type=";
        var thekey = this.get('keys')[templateindex];
        var thevariables=this.get('variables')[templateindex];
        var thedefaults=this.get('defaults')[templateindex];
        var theend=this.get('ends')[templateindex];
          var defaultsarray=thedefaults;
        
        //add key to return string
        retstring += '"' + thekey + '"';
        
        //add variables to return string
         Y.Array.each(thevariables, function(variable, currentindex) {
        //loop start
        	var thefield = Y.one('.' + CSS.TEMPLATEVARIABLE + '_' + currentindex);
        	var thevalue = thefield.get('value');
        	if(thevalue && thevalue!=defaultsarray[variable]){
        		retstring += ',' + variable + '="' + thevalue + '"';
        	}
        //loop end
        }, this);
        
        //close out return string
        retstring += "}";
        
        //add an end tag, if we need to
        if(theend){
        	retstring += '<br/>{POODLL:type="' + thekey + '_end"}';
        }

        this.editor.focus();
        this.get('host').insertContentAtFocusPoint(retstring);
        this.markUpdated();

    }	
	
}, {ATTRS: {
		names: {
			value: null
		},
		
		keys: {
			value: null
		},

		variables: {
			value: null
		},

		defaults: {
			value: null
		}
		,
		instructions: {
			value: null
		},
		customicon: {
			value: null
		},
		ends: {
			value: null
		}
    }
});


}, '@VERSION@', {"requires": ["moodle-editor_atto-plugin"]});
