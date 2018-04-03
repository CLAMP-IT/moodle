
M.block_annotate = {};

M.block_annotate.init = function(Y, tgts, rt) {

	var activated = false;

	var targets = tgts;
	var root = rt;

	Y.on("domready", activate);



	function activate() {

		// only run once
		if (activated) {
			return;
		}
		console.log("activated");
		activated = true;


		var shareuser = "";
		var suelt = document.getElementById("annotate_shareuser");
		if (suelt) {
			shareuser = suelt.value;
		}


		var bpdf = tgtMatch(targets, "pdf");
		var bdoc = tgtMatch(targets, "doc");
		var bxls = tgtMatch(targets, "xls");
		var bppt = tgtMatch(targets, "ppt");
		var bjpg = tgtMatch(targets, "jpg");

		var applyTo = {
				"pdf.gif":bpdf,
				"word.gif":bdoc,
				"docx.gif":bdoc, 
				"xlsx.gif":bxls,
				"xls.gif":bxls,
				"excel.gif":bxls,
				"powerpoint.gif":bppt,
				"pptx.gif":bppt, 
				"odt.gif":bdoc, 
				"image.gif":bjpg,
				"pdf.png":bpdf,
				"word.png":bdoc,
				"docx.png":bdoc,
				"xlsx.png":bxls,
				"xls.png":bxls,
				"excel.png":bxls,
				"powerpoint.png":bppt,
				"pptx.png":bppt,
				"odt.png":bdoc,
				"image.png":bjpg,
				"pdf.jpg":bpdf,
				"word.jpg":bdoc,
				"docx.jpg":bdoc,
				"xlsx.jpg":bxls,
				"xls.jpg":bxls,
				"excel.jpg":bxls,
				"powerpoint.jpg":bppt,
				"pptx.jpg":bppt,
				"odt.jpg":bdoc,
				"image.jpg":bjpg,
				"pdf.jpeg":bpdf,
				"word.jpeg":bdoc,
				"docx.jpeg":bdoc,
				"xlsx.jpeg":bxls,
				"xls.jpeg":bxls,
				"excel.jpeg":bxls,
				"powerpoint.jpeg":bppt,
				"pptx.jpeg":bppt,
				"odt.jpeg":bdoc,
				"image.jpeg":bjpg,
				"pdf.ico":bpdf,
				"word.ico":bdoc,
				"docx.ico":bdoc,
				"xlsx.ico":bxls,
				"xls.ico":bxls,
				"excel.ico":bxls,
				"powerpoint.ico":bppt,
				"pptx.ico":bppt,
				"odt.ico":bdoc,
				"image.ico":bjpg
		};

		//TODO the below list needs to completed for all file types		
		var applyToPrefixes = {
				"pdf-":bpdf,
				"word-":bdoc,
				"docx-":bdoc,
				"xlsx-":bxls,
				"xls-":bxls,
				"excel.-":bxls,
				"pptx-":bppt,
				"odt-":bdoc,
				"image-":bjpg,
				"document-":bdoc,
				"spreadsheet-":bxls,
				"powerpoint-":bppt, 
				"jpeg-":bjpg
		};



		// find the anchors we need to add links to. Don't mess with them here so we don't change the document while
		// we are iterating over it
		var toActivate = [];
		var imgnames = "";

		var as = document.getElementsByTagName("A");

		for (var i = 0; i < as.length; i++) {
			var shouldLink = false;

			var cn = as[i].childNodes;
			var cls=""+as[i].className;
			var txt=""+as[i].innerText;
			var ref=""+as[i].href;
			// we spot resource links because they have an image child where the image src reflects the resource type
			// this is a bit fragile, particularly with the late move in moodle 2 away from directly referring to images
			// to calling image.php for everything which seems a bit costly - why not call that before shipping the page
			// to the browser and actually put the thing in there.
			// It would be much nicer to edit the Moodle code a bit to systematically put 
			// well-defined attributes on resource links for this kind of use
			if (cn.length > 0 && cn[0].tagName == "IMG") {
				var src = "" + cn[0].src;
				var bits = src.split("/");
				var fnm = bits[bits.length-1];


				// sometimes it has "image=pdf.gif" instead of "/pdf.gif"
				var iim = fnm.indexOf("image=");
				if (iim > 0) {
					fnm = fnm.substring(iim + 10);
					var ia = fnm.indexOf("&");
					if (ia > 0) {
						fnm = fnm.substring(0, ia);
					}
					fnm = fnm + ".gif";
				}
				if (applyTo[fnm] === true) {
					shouldLink = true;
				}


				// also try prefixes, as used in Moodle 2.4
				var ihy = fnm.indexOf("-");
				if (ihy > 1) {
					fnm = fnm.substring(0, (ihy+1));				
				}		 
				if (applyToPrefixes[fnm] === true) {
					shouldLink = true;
				}
			}
			//if we have not found it, check the link text 
		
			if(shouldLink==false){
				if(ref.indexOf('.pdf')!=-1){
					shouldLink=true;
				}else if(ref.indexOf('.doc')!=-1){
					shouldLink=true;
				}else if(ref.indexOf('.xls')!=-1){
					shouldLink=true;
				}else if(ref.indexOf('.ppt')!=-1){
					shouldLink=true;
					//	}else if(txt&&((txt.indexOf('.ico')!=-1)||(txt.indexOf('.png')!=-1)||(txt.indexOf('.jpg')!=-1)||(txt.indexOf('.jpeg')!=-1)))&&bjpg)){
					//	shouldLink=true;
					//	}
				}
				console.log(ref+"  ==> "+shouldLink);
			}
			// to be safe, set the tx_added property on a link so we don't duplicate
			if (shouldLink && !as[i].tx_added) {
				toActivate[toActivate.length] = as[i];
				as[i].tx_added = true;						
			}
		}
		createIcons(toActivate,shareuser);		
	}
	function createIcons(_array,_shareuser){
		console.log("SHOW ANNOTATE ON :");
		console.log(_array);
		for (var i = 0; i < _array.length; i++) {
			var a = _array[i];
			var href = a.href;	
			var bits = href.split("/");
			var last = bits[bits.length-1];
			var lsplit = last.split("?");
			var phpfnm = lsplit[0];
			var arg = false;
			if (lsplit.length > 1) {
				arg = lsplit[1];
			}
			var ifile = href.indexOf("file.php/");

			// there are two ways a resource is linked: view.php with an id argument, or file.php with a path after it
			// these both map to our view.php function, either with the original argument used for Moodle's view.php, or
			// with a "p=path" argument.

			var evhr = "";
			if (phpfnm == "view.php" && arg) {
				// occurs for links on the course page. arg holds the id of the resource within the course
				evhr = root + "/blocks/annotate/view.php?" + arg;

			} else if (ifile > 0){
				// occurs for links in the files area. The remainder of the url after file.php is the path to the resource 
				var pth = href.substr(ifile + 10);
				evhr = root + "/blocks/annotate/view.php?" + "p=" + encodeURI(pth);
			}
			if (evhr) {
				if (_shareuser) {
					evhr += "&owner=" + encodeURIComponent(_shareuser);
				}

				// make a new anchor
				var anew = document.createElement("a");
				anew.href = evhr;
				// to open in a new page
				anew.target = "_blank";
				// it just contains our A.nnotaet image
				var img = document.createElement("img");
				img.src = root + "/blocks/annotate/annotate.png";
				img.root = root + "/blocks/annotate/";
				//	img.style.verticalAlign = "middle";
				img.style.paddingRight = "4px";
				//	img.style.paddingBottom="10px";
				//	img.style.paddingLeft="3px";
				// alt text for text only displays or screen readers. Its a shame we can't say anything about 
				// the resource beyond what is already in the href
				img.alt = "View and make notes on-line with Annotate";
				anew.title = "View and make notes on-line with Annotate. Try it out!";

				// for now, no rollover on the annotate image
				//	img.onmouseover = evover;
				//	img.onmouseout = evout;
				anew.appendChild(img);
				a.parentNode.insertBefore(anew, a);
			}
		}
	}

	function evover(e) {
		var img = getTarget(e);
		if (img.root) {
			img.src = img.root + "annotate-on.png";
		}
	}

	function evout(e) {
		var img = getTarget(e);
		if (img.root) {
			img.src = img.root + "annotate.png";
		}
	}


	function getTarget(evt) {
		var x = evt;
		x = (x || window.event);
		var ret =  (x.target || x.srcElement);
		return ret;
	}



	function tgtMatch(ck, ext) {
		var ret = false;
		var eck = "-" + ck + "-";
		if (eck.indexOf("-" + ext + "-") >= 0) {
			ret = true;
		}
		return ret;
	}

}


