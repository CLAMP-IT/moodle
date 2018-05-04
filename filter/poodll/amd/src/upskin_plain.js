/* jshint ignore:start */
define(['jquery','core/log'], function($, log) {

    "use strict"; // jshint ;_;

    log.debug('upskin_plain: initialising');

    return {
        element: null,
        cvs: null,
        cvsctx: null,

        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        //init
        init: function (config, element, cvs,messagearea) {
            this.config = config;
            this.element = element;
            this.cvs = cvs;
            this.messagearea = messagearea;
        },

        initControls: function(){
            //progress
            var skin_style = this.config.media_skin_style;
            var controls='<div id="' + this.config.widgetid + '_progress" class="p_progress x progress_' + skin_style + '"><p></p></div>';
            controls += '<div id="' + this.config.widgetid + '_messages" class="p_messages x messages_' + skin_style + '"></div>';
            $(this.element).append(controls);
        },

        //create a progress bar
        initProgressSession: function(xhr){
            var progress=false;
            var o_query = $("#" + this.config.widgetid + "_progress");
            //if we got one
            if(o_query.length){
                //get the dom object so we can use direct manip.
                var o = o_query.get(0);
                progress = o.firstChild;
                if(progress===null){
                    progress = o.appendChild(document.createElement("p"));
                }
                //reset/set background position to 0, and label to "uploading
                progress.className="";
                progress.style.display = "block";
                progress.style.backgroundPosition = "100% 0";

                // Handle event
                xhr.upload.addEventListener("progress", function(e) {
                    var pc = parseInt(100 - (e.loaded / e.total * 100));
                    progress.style.backgroundPosition = pc + "% 0";
                });
            }
        },

        deactivateProgressSession: function(){
            //do nothing
        },


        showMessage: function(msg){
            var m = $("#" + this.config.widgetid + "_messages");
            m.text(msg);
        }//End of send message
    };//end of returned object
});//total end
