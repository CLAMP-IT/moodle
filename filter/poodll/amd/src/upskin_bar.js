/* jshint ignore:start */
define(['jquery','core/log', 'filter_poodll/anim_progress_bar'], function($, log, bar) {

    "use strict"; // jshint ;_;

    log.debug('upskin_bar: initialising');

    return {
        element: null,
        cvs: null,
        percent: 0,
        progressbar: null,
        messagearea: null,


        //for making multiple instances
        clone: function () {
            return $.extend(true, {}, this);
        },

        //init
        init: function (config, element, cvs, messagearea) {
            this.config = config;
            this.element = element;
            this.cvs = cvs;
            this.progressbar=bar.clone();
            this.messagearea = messagearea;
        },

        initControls: function(){
            var self= this;
            this.progressbar.init(this.cvs, '#BCCCCC');
            this.progressbar.fetchCurrent = function(){return self.percent;};
        },

        //create a progress bar
        initProgressSession: function(xhr){
            var self = this;
            self.percent =0;
            var uploadingmessage = M.util.get_string('recui_uploading', 'filter_poodll');
            // Handle event
            xhr.upload.addEventListener("progress", function(e) {
                if (e.lengthComputable) {
                    self.percent = e.loaded / e.total;
                    self.showMessage(uploadingmessage + '(' + Math.floor(self.percent *100) + '%)');
                }
            });
            this.progressbar.start();
        },

        deactivateProgressSession: function(){
            this.progressbar.stopthere();
            log.debug('deactivating session');
        },

        showMessage: function(msg){
            this.messagearea.html(msg);
        }//End of send message
    };//end of returned object
});//total end
