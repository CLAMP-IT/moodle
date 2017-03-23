/* jshint ignore:start */
define(['jquery','core/log', 'filter_poodll/utils_amd', 'filter_poodll/uploader', 'filter_poodll/drawingboard'], function($, log, utils, uploader, DrawingBoard) {

    "use strict"; // jshint ;_;

    log.debug('Filter PoodLL: drawingboard.js initialising');

    return {
    
    	whiteboard: null,
    
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
			this.whiteboard = db;
			
            //init uploader
        	uploader.init(element, opts);

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

            
            //register the draw and save events that we need to handle
            this.registerEvents();
           
        },
        
        registerEvents: function() {
			//register events. if autosave we need to do more.
			var recid = this.config['recorderid'];
			var that = this;
			var opts = this.config; 
			if(this.config['autosave']){		
					//autosave, clear messages and save callbacks on start drawing
					
					this.whiteboard.ev.bind('board:startDrawing', function(){
									var m = document.getElementById(recid + '_messages');
									if(m){
										m.innerHTML = 'File has not been saved.';
										var savebutton = document.getElementById(recid + '_btn_upload_whiteboard');
										savebutton.disabled=false;
										var th = utils.timeouthandles[recid];
										if(th){clearTimeout(th);}
										utils.timeouthandles[recid] = setTimeout(
											function(){ utils.WhiteboardUploadHandler(recid,that.whiteboard,opts);},
											opts['autosave']);
									}
								}//end of start drawing function						
					);

					//autosave, clear previous callbacks,set new save callbacks on stop drawing
					this.whiteboard.ev.bind('board:stopDrawing', function(){
									var m = document.getElementById(recid + '_messages');
									if(m){
										var th = utils.timeouthandles[recid];
										if(th){clearTimeout(th);}
										utils.timeouthandles[recid] = setTimeout(
											function(){ utils.WhiteboardUploadHandler(recid,that.whiteboard,opts);},
											opts['autosave']);
									}
								}//end of stop drawing function
					);

		
			}else{
				this.whiteboard.ev.bind('board:stopDrawing', function(){
									var m = document.getElementById(recid + '_messages');
									if(m){
										m.innerHTML = 'File has not been saved.';
									}
								}//end of stop drawing function
				);
			}
			
			 //set up the upload/save button
            var uploadbuttonstring = '#' + opts['recorderid'] + '_btn_upload_whiteboard';
            var uploadbutton = $(uploadbuttonstring);
            if(uploadbutton){
                if(opts['autosave']){
                    uploadbutton.click(function(){utils.WhiteboardUploadHandler(recid,that.whiteboard,opts);});
                }else{
                	var cvs = utils.getCvs(opts['recorderid'],that.whiteboard,opts);
                    uploadbutton.click(
                    	function(){
                    		utils.pokeVectorData(opts['recorderid'],that.whiteboard,opts);
                    		uploader.uploadFile(cvs.toDataURL(),'image');
                    	}, 
                    false);
                }
            }//end of if upload button
        }, //end of reg events
    }
});