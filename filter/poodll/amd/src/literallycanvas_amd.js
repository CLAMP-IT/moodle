/* jshint ignore:start */
define(['jquery','core/log', 'filter_poodll/utils_amd', 'filter_poodll/uploader','filter_poodll/react_amd', 'filter_poodll/literallycanvas'], function($, log, utils, uploader,React, LC) {

    "use strict"; // jshint ;_;

    log.debug('Filter PoodLL: literallycanvas.js initialising');

    return {
    
        // handle literallycanvas whiteboard saves for Moodle
        loadliterallycanvas: function(opts) {

            //pick up opts from html
            var theid='#amdopts_' + opts['recorderid'];
            var optscontrol = $(theid).get(0);
            if(optscontrol){
                opts = JSON.parse(optscontrol.value);
                this.config = opts;
                $(theid).remove();
            }


            //init the whiteboard	(diff logic if have a background image)
            var element = '#' + opts['recorderid'] + '_literally';
            var lc_element = $(element)[0];
            

            if(opts['backgroundimage']){
                //simple using opts['backgroundimage'] as src would be better than using a buffer image, but LC won't show it.
                var backimagebuffer = $('#' + opts['recorderid'] + '_separate-background-image')[0];
                var backgroundimage= new Image();
                backgroundimage.src = backimagebuffer.src;
                backgroundimage.crossOrigin = "Anonymous";
                var backgroundshape= LC.createShape('Image', {x: 0, y: 0, image: backgroundimage, scale: 1});

                var lc = LC.init(lc_element,{imageURLPrefix: opts['imageurlprefix'],
                    backgroundColor: opts['backgroundcolor'],
                    backgroundShapes: [backgroundshape],
                    recorderid: opts['recorderid']

                });
            }else{
                var lc = LC.init(lc_element,{imageURLPrefix: opts['imageurlprefix'],
                    backgroundColor: opts['backgroundcolor'],
                    recorderid: opts['recorderid']
                });
            }
            this.lc = lc;
            
            //init uploader
        	uploader.init(element, opts);

            //restore previous drawing if any
            var vectordata = opts['vectordata'];
            if(vectordata){
                //don't restore drawingboardjs vector if its there, goes to error
                if(vectordata.indexOf('{"shapes"')==0 || vectordata.indexOf('{"colors"')==0){
                    lc.loadSnapshot(JSON.parse(vectordata));
                }
            }

            
            //register the draw and save events that we need to handle
            this.registerEvents();
           
        },
        
        registerEvents: function() {
        
        	var mfp = this;
            
            var opts = this.config;
            var recid = opts['recorderid'];
        
        	//handle autosave
            if(opts['autosave']){
                //if user has drawn, commence countdown to save
                this.lc.on('drawingChange',function(){
                        var m = $('#' + recid + '_messages')[0];
                        var savebutton = $('#' + recid + '_btn_upload_whiteboard')[0];
                        if(m) {
                            if (savebutton) {
                                savebutton.disabled = false;
                            }
                            m.innerHTML = '';
                            var th = utils.timeouthandles[recid];
                            if(th){clearTimeout(th);}
                            utils.timeouthandles[recid] = setTimeout(
                                function(){ utils.WhiteboardUploadHandler(recid,mfp.lc,opts);},
                                opts['autosave']);
                        }
                });

                //if no autosave
            }else{
                //lc.on('drawingChange',(function(mfp){return function(){mfp.setUnsavedWarning;}})(this));
                //if user has drawn, alert to unsaved state
                lc.on('drawingChange',function(){
                        var m = $('#' + recid + '_messages');
                        if(m){
                            m.innerHTML = 'File has not been saved.';
                        }
                });
            }//end of handling autosave
        
        	 //set up the upload/save button
            var uploadbuttonstring = '#' + opts['recorderid'] + '_btn_upload_whiteboard';
            var uploadbutton = $(uploadbuttonstring);
            if(uploadbutton){
                if(opts['autosave']){
                    uploadbutton.click(function(){utils.WhiteboardUploadHandler(opts['recorderid'],mfp.lc,opts);});
                }else{
                	var cvs = utils.getCvs(opts['recorderid'],mfp.lc,opts);
                    uploadbutton.click(
                    	function(){
                    		utils.pokeVectorData(opts['recorderid'],mfp.lc,opts);
                    		uploader.uploadFile(cvs.toDataURL(),'image');
                    	}, 
                    false);
                }
            }
        },

    }
});