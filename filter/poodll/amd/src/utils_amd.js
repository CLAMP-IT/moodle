/* jshint ignore:start */
define(['jquery','core/log', 'filter_poodll/uploader'], function($, log, uploader) {

    "use strict"; // jshint ;_;

    log.debug('Filter PoodLL: utils initialising');

    return {
        timeouthandles: [],
       // Call Upload file from drawingboard a, first handle autosave bits and pieces
        WhiteboardUploadHandler: function(recid,wboard,opts) {
            // Save button disabling a little risky db perm. fails publish "startdrawing" after mode change
            var savebutton = $('#' + recid + '_btn_upload_whiteboard')[0];
            savebutton.disabled=true;
            clearTimeout(this.timeouthandles[recid]);
            //call the file upload
            var cvs = this.getCvs(recid,wboard,opts);
            this.pokeVectorData(recid,wboard,opts);
            uploader.uploadFile(cvs.toDataURL(),'image');
        },        
        getCvs: function(recid,wboard){
            if(recid.indexOf('drawingboard_')==0){
                var cvs = wboard.canvas;
            }else{
                var cvs =wboard.canvasForExport();
            }//end of of drawing board
            return cvs;
        },

        escapeColon: function(thestring){
                return thestring.replace(/:/, '\\:');
        },
        
        pokeVectorData: function(recid,wboard,opts){
            var vectordata = "";
            if(recid.indexOf('drawingboard_')==0){
                vectordata = JSON.stringify(wboard.history , null,2);
            }else{
                //only LC has vector data it seems
                vectordata = JSON.stringify(wboard.getSnapshot());
            }//end of of drawing board
            
            //need to do the poke here
            if(typeof opts['vectorcontrol'] !== 'undefined' && opts['vectorcontrol'] !==''){
                //the moodle question has a colon in the field ids, so we need to escape that away
              $('#' + this.escapeColon(opts['vectorcontrol'])).val(vectordata);
                log.debug('Vectorcontrol:' + opts['vectorcontrol'] );
              //  log.debug('Vectordata:' + vectordata );
           }
        //end of poke vectordata
        },
        
                
        concatenateWavBlobs: function(blobs,callback){
           
           //fetch our header
           var allbytes = []; //this will be an array of arraybuffers
            var loadedblobs=0;
            var totalbytes=0;
           
            // fetch the blob data
            var lng = blobs.length;
            for (var i = 0; i < lng; i++){
                var fileReader = new FileReader();
                fileReader.onload = function() {
                    //load blob into arraybuffer
                    var ab = this.result;
                    
                    //remove header and add audiodata to the all data array
                    //the slice is from(inclusive) to end(exclusive)
                    var audiodata=ab.slice(44);
                    totalbytes+=audiodata.byteLength;
                    allbytes.push(audiodata);
                    loadedblobs++;
                    
                    //finally add the header and do callback if at end
                    if(loadedblobs==lng){
                        //get header from last blob, and just adjust the data length
                        var header = ab.slice(0,44);
                        var headerview = new DataView(header);
                        headerview.setUint32(40,totalbytes, true);
                        allbytes.unshift(header);
                         
                        //make our final binary blob and pass it to callback
                        var wavblob = new Blob (allbytes, { type : 'audio/wav' } );
                        callback(wavblob);
                    }
                };
                fileReader.readAsArrayBuffer(blobs[i]);
                
            }//end of i loop
            
        }, //end of concatenateWavBlobs
        
      concatenateBlobs: function(blobs, type, callback) {
            var buffers = [];
            var index = 0;
    
            function readAsArrayBuffer() {
                if (!blobs[index]) {
                    return concatenateBuffers();
                }
                var reader = new FileReader();
                reader.onload = function(event) {
                    buffers.push(event.target.result);
                    index++;
                    readAsArrayBuffer();
                };
                reader.readAsArrayBuffer(blobs[index]);
            }
    
            function concatenateBuffers() {
                var byteLength = 0;
                buffers.forEach(function(buffer) {
                    byteLength += buffer.byteLength;
                });
    
                var tmp = new Uint16Array(byteLength);
                var lastOffset = 0;
                buffers.forEach(function(buffer) {
                    // BYTES_PER_ELEMENT == 2 for Uint16Array
                    var reusableByteLength = buffer.byteLength;
                    if (reusableByteLength % 2 != 0) {
                        buffer = buffer.slice(0, reusableByteLength - 1);
                    }
                    tmp.set(new Uint16Array(buffer), lastOffset);
                    lastOffset += reusableByteLength;
                });
    
                var blob = new Blob([tmp.buffer], {
                    type: type
                });
    
                callback(blob);
            }
            //commence processing
            readAsArrayBuffer();
        }, //end of Concatenate blobs
        
        simpleConcatenateBlobs: function(blobs, type) {
            return new Blob(blobs,{'type': type});
        },


        bytesToSize: function(bytes) {
            var k = 1000;
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            if (bytes === 0){return '0 Bytes';}
            var i = parseInt(Math.floor(Math.log(bytes) / Math.log(k)), 10);
            return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
        },

        // below function via: http://goo.gl/6QNDcI
        getTimeLength: function(milliseconds) {
            var data = new Date(milliseconds);
            return data.getUTCHours() + " hours, " + data.getUTCMinutes() + " minutes and " + data.getUTCSeconds() + " second(s)";
        },

        is_edge: function(){
            return navigator.userAgent.indexOf('Edge');
        },

        is_ios: function(){
            return  /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        },

        is_android: function(){
            var ua = window.navigator.userAgent;
            var isAndroid = (ua.indexOf("android") > -1) || (ua.indexOf("Android") > -1) ; //&& ua.indexOf("mobile");
            return isAndroid;
        },

        is_ie: function(){
            var ms_ie = false;
            var ua = window.navigator.userAgent;
            var old_ie = ua.indexOf('MSIE ');
            var new_ie = ua.indexOf('Trident/');
            var edge = ua.indexOf('Edge/');
            if ((old_ie > -1) || (new_ie > -1) || (edge > -1)) {
                ms_ie = true;
            }
            return ms_ie;
        },
        
    };//end of return object
});