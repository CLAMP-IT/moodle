/* jshint ignore:start */
define(['jquery','core/log','filter_poodll/utils_amd', 'filter_poodll/uploader','filter_poodll/lzflash'], function($, log, utils, uploader, lz) {

    "use strict"; // jshint ;

    log.debug('PoodLL Flash Snapshot Recorder: initialising');

    return {
    
        savebutton: null,
        snapshotdatacontrol: null,
    
        // This recorder supports the current browser
        supports_current_browser: function(config) { 
            var iOS = utils.is_ios();
            var isAndroid = utils.is_android();
            if (iOS || (!config.flashonandroid && isAndroid)){
                return false;
            }else{
                if(config.mediatype!='snapshot'){return false;}

                log.debug('PoodLL Flash Snapshot Recorder: supports this browser');
                return true;
            }
        },
        
        // Perform the embed of this recorder on the page
        //into the element passed in. with config
        embed: function(element, config) { 

           //swf recorder
            var swfopts = $.parseJSON(config.flashsnapshot_widgetjson);        
            lz.embed.swf(swfopts);
            //the save part fails quieltly with this .. atto_poodll: No filename control or value could be found.
            //I think it cant poke outside the iframe ..but not sure.. all the params to teh recoder are ok. J 20160910
        }
    }//end of returned object
});//total end
