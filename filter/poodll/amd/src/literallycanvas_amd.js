/* jshint ignore:start */
define(['jquery','core/log', 'filter_poodll/utils_amd', 'filter_poodll/react_amd', 'filter_poodll/literallycanvas'], function($, log, utils, React, LC) {

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
                $(theid).remove();
            }

            //stash our opts array
            utils.whiteboardopts[opts['recorderid']] = opts;


            //init the whiteboard	(diff logic if have a background image)
            var lc_element = $('#' + opts['recorderid'] + '_literally')[0];

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

            //restore previous drawing if any
            var vectordata = utils.whiteboardopts[opts['recorderid']]['vectordata'];
            if(vectordata){
                //don't restore drawingboardjs vector if its there, goes to error
                if(vectordata.indexOf('{"shapes"')==0 || vectordata.indexOf('{"colors"')==0){
                    lc.loadSnapshot(JSON.parse(vectordata));
                }
            }

            //handle autosave
            if(opts['autosave']){
                //if user starts drawing, cancel the countdown to save
                //drawinfStart event deprecated in lc4.9
                /*
                lc.on('drawingStart',(function(mfp,recid){
                    return function(){
                        var m = $('#' + recid + '_messages')[0];
                        log.debug("dstart1");
                        if(m){
                            log.debug("dstart2");
                            m.innerHTML = 'File has not been saved.';
                            var savebutton = $('#' + recid + '_btn_upload_whiteboard')[0];
                            savebutton.disabled=false;
                            var th = utils.timeouthandles[recid];
                            if(th){clearTimeout(th);}
                        }
                    }
                })(this,opts['recorderid']));
                */

                //if user has drawn commence countdown to save
                lc.on('drawingChange',(function(mfp,recid){
                    return function(){
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
                                function(){ utils.WhiteboardUploadHandler(recid);},
                                utils.whiteboardopts[recid]['autosave']);
                        }
                    }
                })(this,opts['recorderid']));

                //if no autosave
            }else{
                //lc.on('drawingChange',(function(mfp){return function(){mfp.setUnsavedWarning;}})(this));
                //if user has drawn, alert to unsaved state
                lc.on('drawingChange',(function(mfp,recid){
                    return function(){
                        var m = $('#' + recid + '_messages');
                        if(m){
                            m.innerHTML = 'File has not been saved.';
                        }
                    }
                })(this,opts['recorderid']));
            }//end of handling autosave

            //store a handle to this whiteboard
            utils.whiteboards[opts['recorderid']] = lc;

            //set up the upload/save button
            var uploadbuttonstring = '#' + opts['recorderid'] + '_btn_upload_whiteboard';
            var uploadbutton = $(uploadbuttonstring);
            if(uploadbutton){
                if(opts['autosave']){
                    uploadbutton.click(function(){utils.WhiteboardUploadHandler(opts['recorderid']);}, false);
                }else{
                    uploadbutton.click(function(){utils.CallFileUpload(opts['recorderid']);}, false);
                }
            }

        },

    }
});