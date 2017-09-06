/* jshint ignore:start */
define(['jquery','core/log', 'filter_poodll/utils_amd', 'filter_poodll/uploader', 'filter_poodll/drawingboard'], function($, log, utils, uploader, DrawingBoard) {

    "use strict"; // jshint ;_;

    log.debug('Filter PoodLL: drawingboard.js initialising');

    return {

        instanceprops: [],

    
        // handle drawingboard whiteboard saves for Moodle
        loaddrawingboard: function(opts) {

            //pick up opts from html
            var theid='#amdopts_' + opts['recorderid'];
            var optscontrol = $(theid).get(0);
            if(optscontrol){
                opts = JSON.parse(optscontrol.value);
                if(opts['bgimage']){
                    var erasercolor = 'transparent';
                }else{
                    var erasercolor = 'background';
                    opts['bgimage'] = '#FFF';
                }

                this.config = opts;
                $(theid).remove();
            }



           // load the whiteboard and save the canvas reference
           var element = '#' + opts['recorderid'] + 'drawing-board-id';
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
            opts.db = db;


            //restore previous drawing if any
            //restore vectordata
            var vectordata = opts['vectordata'];
            if(vectordata){
                //dont do anything if its not JSON (ie it coule be from LC)
                if(vectordata.indexOf('{"shapes"')!=0 && vectordata.indexOf('{"colors"')!=0){
                    db.history = Y.JSON.parse(vectordata);
                    db.setImg(db.history.values[db.history.position-1]);
                }
            }

            //init uploader
           opts.uploader  = uploader.clone();
           opts.uploader.init(element, opts);

           //store opts in instance props, cos this is a singleton
            this.instanceprops[opts['recorderid']]=opts;
            
            //register the draw and save events that we need to handle
            this.registerEvents(opts['recorderid']);
           
        },
        
        registerEvents: function(recid) {
            //register events. if autosave we need to do more.
            var opts = this.instanceprops[recid];
            if(opts['autosave']){
                    //autosave, clear messages and save callbacks on start drawing

                    opts.db.ev.bind('board:startDrawing', function(){
                                    var m = document.getElementById(recid + '_messages');
                                    if(m){
                                        m.innerHTML = 'File has not been saved.';
                                        var savebutton = document.getElementById(recid + '_btn_upload_whiteboard');
                                        savebutton.disabled=false;
                                        var th = utils.timeouthandles[recid];
                                        if(th){clearTimeout(th);}
                                        utils.timeouthandles[recid] = setTimeout(
                                            function(){ utils.WhiteboardUploadHandler(recid,opts.db,opts, opts.uploader);},
                                            opts['autosave']);
                                    }
                                }//end of start drawing function
                    );

                    //autosave, clear previous callbacks,set new save callbacks on stop drawing
                    opts.db.ev.bind('board:stopDrawing', function(){
                                    var m = document.getElementById(recid + '_messages');
                                    if(m){
                                        var th = utils.timeouthandles[recid];
                                        if(th){clearTimeout(th);}
                                        utils.timeouthandles[recid] = setTimeout(
                                            function(){ utils.WhiteboardUploadHandler(recid,opts.db,opts,opts.uploader);},
                                            opts['autosave']);
                                    }
                                }//end of stop drawing function
                    );


            }else{
                opts.db.ev.bind('board:stopDrawing', function(){
                                    var m = document.getElementById(recid + '_messages');
                                    if(m){
                                        m.innerHTML = 'File has not been saved.';
                                    }
                                }//end of stop drawing function
                );
            }

             //set up the upload/save button
            var uploadbuttonstring = '#' + recid + '_btn_upload_whiteboard';
            var uploadbutton = $(uploadbuttonstring);
            if(uploadbutton){
                if(opts.autosave){
                    uploadbutton.click(function(){utils.WhiteboardUploadHandler(recid,opts.db,opts,opts.uploader);});
                }else{
                    uploadbutton.click(
                        function(){
                            var cvs = utils.getCvs(recid,opts.db,opts);
                            utils.pokeVectorData(recid,opts.db,opts);
                            opts.uploader.uploadFile(cvs.toDataURL(),'image');
                        });
                }
            }//end of if upload button
        } //end of reg events
    }
});