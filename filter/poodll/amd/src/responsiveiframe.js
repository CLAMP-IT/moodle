/* jshint ignore:start */
define(['jquery','core/log'], function($, log) {

  "use strict"; // jshint ;_;

  log.debug('responsive iframe initialising');

  return {
         defheight: 150,
         defwidth: 300,
      
            init: function(config){
                var theiframe = $('#' + config.iframeid);
                if(theiframe.length == 0){
                    theiframe = $('#' + config.iframeid,window.parent.document);
                }
                if(theiframe.length > 0){
                    var iframeprops = {
                        currentheight: this.defheight,
                        currentwidth:  this.defwidth,
                        theiframe: theiframe,
                        thisref: this
                    };
                    this._iframeResize(iframeprops);
                }
            },
             /**
            * Scan for resize of iframe content and resize iframe
            *
            * @param theiframe
            * @return null
            * @private
            */
            _iframeResize: function(iframeprops){
                var theiframe = iframeprops.theiframe;
                
                debugger;
                
                if(!theiframe ||theiframe.length < 1 ){
                    return;
                }
                var newheight = theiframe[0].contentWindow.document.body.scrollHeight;
                var newwidth = theiframe[0].contentWindow.document.body.scrollWidth;
                if(newheight != iframeprops.currentheight || newwidth != iframeprops.currentwidth){
                        theiframe.attr('height',(newheight) + "px");
                        theiframe.attr("width", (newwidth) + "px");
                }
                
                iframeprops.currentheight= newheight;
                iframeprops.currentwidth= newwidth;

                setTimeout(iframeprops.thisref._iframeResize,100,iframeprops);
                
           }//end of iframe resize
	};//end of return object
});
/* jshint ignore:end */