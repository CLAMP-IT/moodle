/**
 * PoodLL Audio Recording SDK
 *
* @author Justin Hunt (@link http://www.poodll.com)
* @copyright 2013 onwards Justin Hunt http://www.poodll.com
* @license JustinsPlainEnglishLicense ( http://www.poodll.com/justinsplainenglishlicense.txt )
 */
//opts = fileselectid, updatecontrolid, progressid, posturl, p1 p2 p3 p4
//see also messages and out and the callback for handling filename

var deferredfile =Array();
var deferredfiledata =Array();
var deferredopts =Array();

// handle file uploads for Mobile
function loadmobileupload(opts) {
	var fileselect = $id(opts['fileselectid']);
	if(fileselect){
		//fileselect.addEventListener("change", FileSelectHandler, false);
		//This 
		fileselect.addEventListener('change', function(theopts) {
				return function(e) {FileSelectHandler(e, theopts); };
				} (opts) , false);
	}
}
//when not autosubmitting, ie via a submit button, we use previously stored data
function deferredexport(recorderid){
	UploadFile(deferredfile[recorderid],deferredfiledata[recorderid],deferredopts[recorderid]);
}

	// file selection
	function FileSelectHandler(e,opts) {

		// fetch FileList object
		var files = e.target.files || e.dataTransfer.files;

		// process all File objects
		for (var i = 0, f; f = files[i]; i++) {
			ParseFile(f,opts);
		}

	}//end of FileSelectHandler
	
	// output file information
	function ParseFile(file,opts) {

	/*
		//output basic file info. good for debugging
		Output(
			"<p>File information: <strong>" + file.name +
			"</strong> type: <strong>" + file.type +
			"</strong> size: <strong>" + file.size +
			"</strong> bytes</p>"
		);
		*/	

			
			// start upload
			var filedata ="";
			var reader = new FileReader();
			//reader.onloadend = UploadFile;
			reader.onloadend = function(e) {
						filedata = e.target.result;
						if(opts['autosubmit']){
							UploadFile(file, filedata,opts);
						}else{
							deferredfile[opts['recorderid']]=file;
							deferredfiledata[opts['recorderid']]=filedata;
							deferredopts[opts['recorderid']]=opts;
							//invoke callbackjs if we have one
							if(opts['callbackjs']!=''){ 
								var ret  = new Array();
								ret[0]=opts['recorderid'];
								ret[1]='statuschanged';
								ret[2]='haverecorded';
								ret[3]=opts['p1'];
								ret[4]=opts['p2'];
								ret[5]=opts['p3'];
								ret[6]=opts['p4'];
								window[opts['callbackjs']](ret);
							}
						}
			}
			reader.readAsDataURL(file);

	}//end of ParseFile

	// upload Media files
	function UploadFile(file, filedata,opts) {


		var xhr = new XMLHttpRequest();
		//if (xhr.upload && file.type == "image/jpeg" && file.size <= $id("MAX_FILE_SIZE").value) {

		//Might need more mimetypes than this, and 3gpp maynot work
		var ext="";
		switch(file.type){
			case "image/jpeg": ext = "jpg";break;
			case "image/png": ext = "png";break;
			case "video/quicktime": ext = "mov";break;
			case "audio/mpeg3": ext = "mp3";break;
			case "audio/x-mpeg-3": ext = "mp3";break;
			case "audio/mpeg3": ext = "mp3";break;
			case "audio/3gpp": ext = "3gpp";break;
			case "video/mpeg3": ext = "3gpp";break;
			case "video/mp4": ext = "mp4";break;	
		}
		
		if(true){
			// create progress bar
			var o = $id(opts['progressid']);
			var progress = o.firstChild;
			if(progress==null){
				progress = o.appendChild(document.createElement("p"));
			}
			//reset/set background position to 0, and label to "uploading
			progress.className="";
			progress.style.display = "block";
			progress.style.backgroundPosition = "100% 0";
			Output("Uploading.",opts['messagesid']);

			// progress bar
			xhr.upload.addEventListener("progress", function(e) {
				var pc = parseInt(100 - (e.loaded / e.total * 100));
				progress.style.backgroundPosition = pc + "% 0";
			}, false);

			// file received/failed
			xhr.onreadystatechange = function(e) {
				
				if (xhr.readyState == 4 ) {
					progress.className = (xhr.status == 200 ? "success" : "failure");
					if(xhr.status==200){
						var resp = xhr.responseText;
						var start= resp.indexOf("success<error>");
						//if error output, return to browser
						if (start<1){
    						var errormatch = resp.match(/<error>([^<]*)<\/error>/);
    						var errormessage = errormatch[1];
							Output("A problem occurred:" + errormessage ,opts['messagesid'] );
							return;
						}
						var end = resp.indexOf("</error>");
						var filename= resp.substring(start+14,end);
						//Output("gotten filename:" + filename);
						Output("File uploaded successfully.",opts['messagesid'] );
						var upc = $id(opts['updatecontrolid']);
						if(!upc){upc = $parentid(opts['updatecontrolid']);}
						if(upc){upc.value=filename;}
						
						//invoke callbackjs if we have one
						if(opts['callbackjs']!=''){ 
							var ret  = new Array();
							ret[0]=opts['recorderid'];
							ret[1]='filesubmitted';
							ret[2]=filename;
							ret[3]=opts['p1'];
							ret[4]=opts['p2'];
							ret[5]=opts['p3'];
							ret[6]=opts['p4'];
							window[opts['callbackjs']](ret);
						}
					}else{
						Output("File could not be uploaded.",opts['messagesid']);
					}
				}
			};	
			
			var params = "action=uploadfile";
			params += "&datatype=uploadfile";
			params += "&p1=" + opts['p1'];
			params += "&p2=" + opts['p2'];
			params += "&p3=" + opts['p3'];
			params += "&p4=" + opts['p4'];
			//We must URI encode the base64 filedata, because otherwise the "+" characters get turned into spaces
			//spent hours tracking that down ...justin 20121012
			params += "&filedata=" + encodeURIComponent(filedata);
			params += "&fileext=" + ext;
			params += "&requestid=12345";
			params += "&p1=" + opts['p1'];
			params += "&p2=" + opts['p2'];
			params += "&p3=" + opts['p3'];
			params += "&p4=" + opts['p4'];
			//console.log("params:" + params);
			
			xhr.open("POST", opts['posturl'], true);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xhr.setRequestHeader("Cache-Control", "no-cache");
			xhr.setRequestHeader("Content-length", params.length);
			xhr.setRequestHeader("Connection", "close");

			xhr.send(params);
			

		}

	}//end of upload file
	
	// output information
	function Output(msg, messagesid) {
		var m = $id(messagesid);
		//m.innerHTML = msg + m.innerHTML;
		m.innerHTML = msg;
	}
	
	// getElementById
	function $id(id) {
		return document.getElementById(id);
	}
	// getElementById
	function $parentid(id) {
		return parent.document.getElementById(id);
	}