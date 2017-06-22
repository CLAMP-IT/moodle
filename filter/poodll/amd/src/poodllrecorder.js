/* jshint ignore:start */
define(['jquery',
    'core/log',
    'filter_poodll/poodll_flashrecorder',
    'filter_poodll/poodll_mediarecorder',
    'filter_poodll/poodll_uploadrecorder',
    'filter_poodll/poodll_mobilerecorder',
    'filter_poodll/poodll_flashsnapshotrecorder',
    'filter_poodll/poodll_snapshotrecorder',
    'filter_poodll/poodll_red5recorder'], 
    function($, log, flashrec, mediarec,uploadrec,mobilerec,flashsnapshot,snapshot,red5) {

    "use strict"; // jshint ;_;

    log.debug('PoodLL Recorder: initialising');

    return {
    
        //init the poodll recorder
        //basically we check the users preferred recorders and if the rec supports the browser
        init: function(config){

             //pick up opts from html
            var theid='#amdopts_' + config.widgetid;
            var configcontrol = $(theid).get(0);
            if(configcontrol){
                this.config = JSON.parse(configcontrol.value);
                $(theid).remove();
            }else{
                //if there is no config we might as well give up
                log.debug('PoodLL Recorder: No config found on page. Giving up.');
                return;
            }

            //we are going to need the site url and the sess key
            this.config.wwwroot = M.cfg.wwwroot;
            this.config.sesskey = M.cfg.sesskey;

            var element = this.config.selector;
            if(!element){
                log.debug("unable to fetch element with selector:" + this.config.selector);
                return;
            }
            var use_rec =false;
            for(var i=0;i< this.config['rec_order'].length;i++){
                switch(this.config['rec_order'][i]){
                      case 'red5': use_rec = red5;break;
                      case 'flashaudio': use_rec = flashrec;break;
                      case 'media': use_rec = mediarec;break;
                      case 'upload': use_rec = uploadrec;break;
                      case 'mobile': use_rec = mobilerec;break;
                      case 'flashsnapshot': use_rec = flashsnapshot;break;
                      case 'snapshot': use_rec = snapshot;break;
                }//end of switch

                //if current browser supported by rec, then embed and return
                if(use_rec.supports_current_browser(this.config)){
                             use_rec.embed(element,this.config);
                             return;
                } // end of current browser support check
            }//end of each

            //if we got here no recorder was preferred AND supported the browser
            log.debug('none of available recorders works on this browser');
        }
    };//end of returned object
});//total end
