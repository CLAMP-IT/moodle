/* jshint ignore:start */
define(['jquery','core/log','filter_poodll/upskin_plain'], function($, log, upskin_plain) {

    "use strict"; // jshint ;_;

    log.debug('Universal Uploader: initialising');

    return {
    
        config: null,

        //for making multiple instances
        clone: function(){
            return $.extend(true,{},this);
        },

        init: function(element,config,upskin){
            this.config = config;
            if(upskin){
                this.upskin= upskin;
            }else{
                this.upskin=upskin_plain.clone();
                this.upskin.init(config,element,false,false);
            }
            this.upskin.initControls();
        },
        
        uploadBlob: function(blob,filetype){
            this.uploadFile(blob, filetype);
            return;
        },
        //extract filename from the text returned as response to upload
        extractFilename: function(returntext){
            var searchkey ="success<filename>";
             var start= returntext.indexOf(searchkey);
            if (start<1){return false;}
            var end = returntext.indexOf("</filename>");
            var filename= returntext.substring(start+(searchkey.length),end);
            return filename;
        },

        //fetch file extension from the filetype
        fetchFileExtension: function(filetype){
            var ext="";
            //Might need more mimetypes than this, and 3gpp maynot work
            switch(filetype){
                case "image/jpeg": ext = "jpg";break;
                case "image/png": ext = "png";break;
                case "audio/wav": ext = "wav";break;
                case "video/quicktime": ext = "mov";break;
                case "audio/mpeg3": ext = "mp3";break;
                case "audio/webm": ext = "webm";break;
                case "audio/x-mpeg-3": ext = "mp3";break;
                case "audio/3gpp": ext = "3gpp";break;
                case "video/mpeg3": ext = "3gpp";break;
                case "video/mp4": ext = "mp4";break;
                case "video/webm": ext = "webm";break;
            }
            return ext;
        },

        postMessage: function(messageObject, allowedURL){
            window.parent.postMessage(messageObject,allowedURL);
        },
        
        pokeFilename: function(filename,uploader){

            var upc = '';
            if(typeof uploader.config.updatecontrol !== 'undefined' && uploader.config.updatecontrol !==''){
              upc=$('[id="' + uploader.config.updatecontrol + '"]');
               //the code below used to work until odd chars in question id annoyed jquery 3 
              //upc = $('#' + uploader.config.updatecontrol);
            }
            if(upc.length<1){
                upc = $('[id="' + uploader.config.updatecontrol + '"]', window.parent.document);
            }
            if (upc.length > 0) {
                upc.get(0).value = filename;
            }else{
                log.debug('upload failed #2');
                uploader.upskin.showMessage(M.util.get_string('recui_uploaderror', 'filter_poodll'));
                return false;
            }
            upc.trigger('change');
            return true;
        },
        
        alertRecorderSuccess: function(widgetid){
            if(this.config.hasOwnProperty('onuploadsuccess')){
                this.config.onuploadsuccess(widgetid);
            }
        },
        
        alertRecorderFailure: function(widgetid){
            if(this.config.hasOwnProperty('onuploadfailure')){
                this.config.onuploadfailure(widgetid);
            }
        },

        //We can detect conversion by pinging the s3 out filename
        //this is only done in the iFrame
        completeAfterConversion: function(uploader,filename, waitms){

            //alert the skin that we are awaiting conversion
            this.upskin.showMessage(M.util.get_string('recui_awaitingconversion', 'filter_poodll'));

            //We alert the iframe host that a file is now awaiting conversion
            var messageObject ={};
            messageObject.type = "awaitingconversion";
            messageObject.filename = filename;
            messageObject.cloudfilename = uploader.config.cloudfilename;
            messageObject.cloudroot = uploader.config.cloudroot;
            messageObject.s3filename = uploader.config.s3filename;
            messageObject.s3root = uploader.config.s3root;

            messageObject.id = uploader.config.id;
            messageObject.updatecontrol = uploader.config.updatecontrol;

            uploader.postMessage(messageObject, uploader.config.allowedURL);

            //we commence a series of ping and retries until the recorded file is available
            var that = this;
            $.ajax({
                url: uploader.config.s3root + uploader.config.s3filename,
                method:'HEAD',
                cache: false,
                error: function()
                {
                    //We get here if its a 404 or 403. So settimout here and wait for file to arrive
                    //we increment the timeout period each time to prevent bottlenecks
                    log.debug('403 errors are normal here, till the file arrives back from conversion');
                    setTimeout(function(){that.completeAfterConversion(uploader,filename,waitms+500);},waitms);
                },
                success: function(data, textStatus, xhr)
                {
                    switch(xhr.status){
                        case 200:
                            that.doUploadCompleteCallback(uploader,filename);
                            break;
                        default:
                            setTimeout(function(){that.completeAfterConversion(uploader,filename,waitms+500);},waitms);
                    }

                }
            });
        },

        doUploadCompleteCallback: function(uploader,filename){

            //in the case of an iframeembed we need a full URL not just a filename
            if(uploader.config.iframeembed){
                filename = uploader.config.cloudroot + uploader.config.cloudfilename;
            }

            //For callbackjs and for postmessage we need an array of stuff
            var callbackObject = new Array();
            callbackObject[0] = uploader.config.widgetid;
            callbackObject[1] = "filesubmitted";
            callbackObject[2] = filename;
            callbackObject[3] = uploader.config.updatecontrol;
            callbackObject[4] = uploader.config.s3filename;

            //alert the skin that we were successful
            this.upskin.showMessage(M.util.get_string('recui_uploadsuccess', 'filter_poodll'));

            //invoke callbackjs if we have one, otherwise just update the control(default behav.)
            if(!uploader.config.iframeembed) {
                if (uploader.config.callbackjs && uploader.config.callbackjs != '') {
                    if (typeof(uploader.config.callbackjs) === 'function') {
                        uploader.config.callbackjs(callbackObject);
                    } else {
                        //this was the old rubbish way, where the callback was a function name
                        this.executeFunctionByName(uploader.config.callbackjs, window, callbackObject);
                    }
                } else {
                    //by default we just poke the filename
                    uploader.pokeFilename(filename, uploader);
                }
            }else{
                //in the case of an iframeembed we will also post a message to the host, they can choose to handle it or not
                //The callback object above scan prob. be phased out. But not all integrations will use iframes either.
                var messageObject ={};
                messageObject.type = "filesubmitted";
                messageObject.filename = filename;
                messageObject.cloudfilename = uploader.config.cloudfilename;
                messageObject.cloudroot = uploader.config.cloudroot;
                messageObject.s3filename = uploader.config.s3filename;
                messageObject.s3root = uploader.config.s3root;

                messageObject.id = uploader.config.id;
                messageObject.updatecontrol = uploader.config.updatecontrol;

                uploader.postMessage(messageObject, uploader.config.allowedURL);
            }
        },
        
        //after an upload handle the filename poke and callback call
        postProcessUpload: function(e,uploader){
              var xhr = e.currentTarget;
              if (xhr.readyState == 4 ) {

                  uploader.upskin.deactivateProgressSession();

                  if(xhr.status==200) {
                      var filename = uploader.config.filename;
                      if (!filename) {
                          filename = uploader.extractFilename(xhr.responseText);
                      }
                      if (!filename) {
                          log.debug('upload failed #1');
                          log.debug(xhr);
                          return;
                      }

                      //Alert any listeners about the upload complete
                      //in an iframeembed we only  do this after conversion is complete. so we run a poll to check compl.
                      //in standard Moodle we have a placeholder file to deal with any slow conversions. so we don't poll
                      if (uploader.config.iframeembed) {
                          this.completeAfterConversion(uploader, filename,1000);
                      }else{
                          this.doUploadCompleteCallback(uploader, filename);
                      }

                    //alert the recorder that this was successful
                    this.alertRecorderSuccess(uploader.config.widgetid);

                }else{
                    log.debug('upload failed #3');
                    log.debug(xhr);
                    uploader.upskin.showMessage(M.util.get_string('recui_uploaderror', 'filter_poodll'));

                    //alert the recorder that this failed
                    this.alertRecorderFailure(uploader.config.widgetid);

                } //end of if status 200
            }//end of if ready state 4
        
        },
       
        // upload Media file to wherever
        uploadFile: function(filedata,filetype) {
      
            var xhr = new XMLHttpRequest();
            var config = this.config;
            var uploader = this;

            //get the file extension from the filetype
            var ext = this.fetchFileExtension(filetype);

            //is this an iframe embed
            if(typeof config.iframeembed == 'undefined'){
                config.iframeembed=false;
            }

            //are we using s3
            var using_s3 = config.using_s3;

            //Handle UI display of this upload
            this.upskin.initProgressSession(xhr);

            //alert user that we are now uploading    
            this.upskin.showMessage(M.util.get_string('recui_uploading', 'filter_poodll'));

            xhr.onreadystatechange = function(e){
                if(using_s3 && this.readyState===4) {
                    if (config.iframeembed) {
                        uploader.postprocess_uploadfromiframeembed(uploader);
                    } else {
                        //ping Moodle and inform that we have a new file
                        uploader.postprocess_s3_upload(uploader);
                    }
                }
                uploader.postProcessUpload(e,uploader);
                
            };

            if(using_s3){
                    xhr.open("put",config.posturl, true);
                    xhr.setRequestHeader("Content-Type", 'application/octet-stream');
                    xhr.send(filedata);
            }else{

                   //We NEED to redo this bit of code ..
                   //its duplicating!!!
                   if(!(filedata instanceof Blob)){
                        var params = "datatype=uploadfile";
                        //We must URI encode the filedata, because otherwise the "+" characters get turned into spaces
                        //spent hours tracking that down ...justin 20121012
                        params += "&paramone=" + encodeURIComponent(filedata);
                        params += "&paramtwo=" + ext;
                        params += "&paramthree=" + config.mediatype;
                        params += "&requestid=" + config.widgetid;
                        params += "&contextid=" + config.p2;
                        params += "&component=" + config.p3;
                        params += "&filearea=" + config.p4;
                        params += "&itemid=" + config.p5;

                        xhr.open("POST",config.posturl, true);
                        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                        xhr.setRequestHeader("Cache-Control", "no-cache");
                        //xhr.setRequestHeader("Content-length", params.length);
                        //xhr.setRequestHeader("Connection", "close");
                        xhr.send(params);
                   }else{
                        //we have to base64 string the blob  before sending it
                        var reader = new window.FileReader();
                        reader.readAsDataURL(filedata);
                        reader.onloadend = function() {
                            var base64filedata = reader.result;
                            //log.debug(params);
                            var params = "datatype=uploadfile";
                            //We must URI encode the filedata, because otherwise the "+" characters get turned into spaces
                            //spent hours tracking that down ...justin 20121012
                            params += "&paramone=" + encodeURIComponent(base64filedata);
                            params += "&paramtwo=" + ext;
                            params += "&paramthree=" + config.mediatype;
                            params += "&requestid=" + config.widgetid;
                            params += "&contextid=" + config.p2;
                            params += "&component=" + config.p3;
                            params += "&filearea=" + config.p4;
                            params += "&itemid=" + config.p5;

                            xhr.open("POST",config.posturl, true);
                            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                            xhr.setRequestHeader("Cache-Control", "no-cache");
                           // xhr.setRequestHeader("Content-length", params.length);
                           // xhr.setRequestHeader("Connection", "close");
                            xhr.send(params);
                        };//end of fileread on load end
                }//end of if blob                 
         }//end of if using_s3
        },

        postprocess_uploadfromiframeembed: function(uploader){
            var config = uploader.config;
            var xhr = new XMLHttpRequest();
            var that = this;

            //lets do a little error checking
            //if its a self signed error or rotten permissions on poodllfilelib.php we might error here.
            xhr.onreadystatechange = function(){
                if(this.readyState===4){
                    if(xhr.status!=200){
                        that.upskin.showMessage('Post Process Upload from IframeEmbed Error:' + xhr.status);
                        $('#' + that.config.widgetid + '_messages').show();
                    }else{

                    }
                }
            };

            //log.debug(params);
            var params = "datatype=handleuploadfromiframeembed";
            params += "&filename=" + config.filename;
            params += "&mediatype=" + config.mediatype;

            xhr.open("POST",M.cfg.wwwroot + '/filter/poodll/poodllfilelib.php', true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
           // xhr.setRequestHeader("Cache-Control", "no-cache");
            xhr.send(params);
        },

        postprocess_s3_upload: function(uploader){
            var config = uploader.config;
            var xhr = new XMLHttpRequest();
            var that = this;
            
            //lets do a little error checking
            //if its a self signed error or rotten permissions on poodllfilelib.php we might error here.
            xhr.onreadystatechange = function(){
                if(this.readyState===4){
                    if(xhr.status!=200){
                       that.upskin.showMessage('Post Process s3 Upload Error:' + xhr.status);
                       $('#' + that.config.widgetid + '_messages').show();
                     }
                }
            };

            //log.debug(params);
            var params = "datatype=handles3upload";
            params += "&contextid=" + config.p2;
            params += "&component=" + config.p3;
            params += "&filearea=" + config.p4;
            params += "&itemid=" + config.p5;
            params += "&filename=" + config.filename;
            params += "&mediatype=" + config.mediatype;

            xhr.open("POST",M.cfg.wwwroot + '/filter/poodll/poodllfilelib.php', true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("Cache-Control", "no-cache");
          //  xhr.setRequestHeader("Content-length", params.length);
          //  xhr.setRequestHeader("Connection", "close");
            xhr.send(params);
            
        },
            
            //function to call the callback function with arguments
        executeFunctionByName: function(functionName, context , args ) {

            //var args = Array.prototype.slice.call(arguments).splice(2);
            var namespaces = functionName.split(".");
            var func = namespaces.pop();
            for(var i = 0; i < namespaces.length; i++) {
                context = context[namespaces[i]];
            }
            return context[func].call(this, args);
        },
        
        dataURItoBlob: function(dataURI, mimetype) {
            var byteString = atob(dataURI.split(',')[1]);
            var ab = new ArrayBuffer(byteString.length);
            var ia = new Uint8Array(ab);
            for (var i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }
            return new Blob([ab], { type: mimetype });
        },//end of dataURItoBlob

        //some recorder skins call this directly, so we just pass it through to the upskin
        Output: function(msg){
            this.upskin.showMessage(msg);
        }
    };//end of returned object
});//total end
