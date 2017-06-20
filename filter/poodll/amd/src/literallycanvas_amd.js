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

            //zoom feature
            var zoomMax = 4;
            var zoomMin = 0.2;

            if(opts['whiteboardnozoom']>0){
                zoomMax =1;
                zoomMin = 1;
            }

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
                    recorderid: opts['recorderid'],
                    zoomMax: zoomMax,
                    zoomMin: zoomMin

                });
            }else{
                var lc = LC.init(lc_element,{imageURLPrefix: opts['imageurlprefix'],
                    backgroundColor: opts['backgroundcolor'],
                    recorderid: opts['recorderid'],
                    zoomMax: zoomMax,
                    zoomMin: zoomMin
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
                this.lc.on('drawingChange',function(){
                        var m = $('#' + recid + '_messages');
                        if(m){
                            m.innerHTML = 'File has not been saved.';
                        }
                });
            }//end of handling autosave
        
             //set up the upload/save button
            var uploadbuttonstring = '#' + recid + '_btn_upload_whiteboard';
            var uploadbutton = $(uploadbuttonstring);
            if(uploadbutton){
                if(opts['autosave']){
                    uploadbutton.click(function(){utils.WhiteboardUploadHandler(recid,mfp.lc,opts);});
                }else{
                    uploadbutton.click(
                        function(){
                            var cvs = utils.getCvs(recid,mfp.lc,opts);
                            utils.pokeVectorData(recid,mfp.lc,opts);
                            uploader.uploadFile(cvs.toDataURL(),'image');
                        });
                }
            }
        },

    }
});