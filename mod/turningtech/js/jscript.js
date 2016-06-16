    function getInternetExplorerVersion() {
         var rv = -1; // Return value assumes failure.
         if (navigator.appName == 'Microsoft Internet Explorer') {
             var ua = navigator.userAgent;
             var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
             if (re.exec(ua) != null)
             rv = parseFloat( RegExp.$1 );
             }
         return rv;
    }
    function getBrowserType() {   
    var isOpera = !!window.opera || navigator.userAgent.indexOf('Opera') >= 0;
 // Opera 8.0+ (UA detection to detect Blink/v8-powered Opera)
 var isFirefox = typeof InstallTrigger !== 'undefined';   // Firefox 1.0+
 var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0;
 // At least Safari 3+: "[object HTMLElementConstructor]"
 var isChrome = !!window.chrome;                          // Chrome 1+
 var isIE = /*@cc_on!@*/false;                            // At least IE6
   if(isOpera) {
	   return 2;
   } else if (isFirefox) {
	   return 3;	   
   } else if (isChrome) {
	   return 4;	   
   } else if (isIE) {
	   return 5;	   
   }
    }
    
    function EditResponseCard(caller) {
        var str = caller.id;
        var n = str.split("_");
        var divToHideId = "divEditRC_" + n[1];
        var divToShowId = "divUpdateCancelRC_" + n[1];
        var textDivToShowId = "divTextResponseCard_" + n[1];
        var lblDivToHideId = "divResponseCard_" + n[1];
        var divToHide = document.getElementById(divToHideId);
        var divToShow = document.getElementById(divToShowId);
        var lblToHide = document.getElementById(lblDivToHideId);
        var textdivToShow = document.getElementById(textDivToShowId);
        var deviceid = document.getElementById(lblDivToHideId).innerHTML;
        document.getElementById('txtResponseCard_' + n[1]).value = deviceid;
        divToHide.style.display = 'none';
        lblToHide.style.display = 'none';
        divToShow.style.display = 'inline';
        textdivToShow.style.display = 'inline';

    }

    function EditResponseWare(caller) {
        var str = caller.id;
        var n = str.split("_");
        var divToHideId = "divEditRW_" + n[1];
        var divToShowId = "divUpdateCancelRW_" + n[1];
        var textDivToShowId = "divTextResponseWare_" + n[1];
        var lblDivToHideId = "divResponseWare_" + n[1];
        var divToHide = document.getElementById(divToHideId);
        var divToShow = document.getElementById(divToShowId);
        var lblToHide = document.getElementById(lblDivToHideId);
        var textdivToShow = document.getElementById(textDivToShowId);
        divToHide.style.display = 'none';
        lblToHide.style.display = 'none';
        divToShow.style.display = 'inline';
        textdivToShow.style.display = 'inline';

    }

    function DeleteResponseCard(caller) {
        var canDelete = true;
        var str = caller.id;
        var n = str.split("_");
        if (canDelete) {
            var r = confirm("Are you sure you want to delete this Device ID?");
            if (r) {
                window.location = document.getElementById('lnkDeleteRC_1').href;
            }
        } else {}
    }

    function DeleteResponseWare(caller) {
        var canDelete = true;
        var str = caller.id;
        var n = str.split("_");
        if (canDelete) {
            var r = confirm("Are you sure you want to delete this Device ID?");
            if (r) {
                window.location = document.getElementById('lnkDeleteRW_1').href;
            }
        } else {}
    }

    function validateRC() {
        var registerval = document.getElementById("id_deviceid");
        var alphanum = /^[0-9a-fA-F]+$/;
        if (registerval.value == "") {
            alert("Device ID Invalid - Device IDs can only be 6 or 8 characters (0-9, A-F)");
            return false;
            
        } 
        else if ((registerval.value.length != '6') && (registerval.value.length != '8')) {
            alert("Device ID Invalid - Device IDs can only be 6 or 8 characters (0-9, A-F)");
            return false;
        } else if (registerval.value.match(alphanum)) {
            return true;
        } else {
            alert("Device ID Invalid - Device IDs can only be 6 or 8 characters (0-9, A-F)");
            return false;
        }
    }

    function UpdateResponseCard() {
        var updated = document.getElementById('txtResponseCard_1').value;
        var alphanum = /^[0-9a-fA-F]+$/;
        if (updated == "") {
            alert("Device ID Invalid - Device IDs can only be 6 or 8 characters (0-9, A-F)");
            return false;
        } else if ((updated.length != '6') && (updated.length != '8')) {
            alert("Device ID Invalid - Device IDs can only be 6 or 8 characters (0-9, A-F)");
            return false;
        } else if (updated.match(alphanum)) {
            var updatedurl = document.getElementById('lnkUpdateRC_1').href;
            document.getElementById('lnkUpdateRC_1').href = updatedurl + '&deviceid=' + updated;
            //alert("Update Response Card");
            return true;
        } else {
            alert("Device ID Invalid - Device IDs can only be 6 or 8 characters (0-9, A-F)");
            return false;
        }
    }

    function validateDeviceInput(eventRef) {
        var updated = document.getElementById('id_deviceid').value;
        var charCode = eventRef.keyCode ? eventRef.keyCode : ((eventRef.charCode) ? eventRef.charCode : eventRef.which);
        if (updated == '' && eventRef.keyCode == 13) {
            alert("Device ID Invalid - Device IDs can only be 6 or 8 characters (0-9, A-F)");
            return false;
        }
        if (charCode == 8 || charCode == 27 || charCode == 9 || charCode == 13) {

            return true;

        } else if (eventRef.keyCode != null && (eventRef.keyCode == 46 || eventRef.keyCode == 39) && eventRef.charCode != null && eventRef.charCode == 0) {

            return true;

        }

        var character = String.fromCharCode(charCode);
        var alphanum = /^[0-9a-fA-F]+$/;
        if (character.match(alphanum) && updated.length < '8') {
            return true;
        } else {
            return false;
        }
    }

    function validateDeviceInputupdate(eventRef) {

        var updated = document.getElementById('txtResponseCard_1').value;
        var charCode = eventRef.keyCode ? eventRef.keyCode : ((eventRef.charCode) ? eventRef.charCode : eventRef.which);
        if (eventRef.keyCode == 13 || eventRef.which == 13) {
            var cle = document.createEvent("MouseEvent");
            cle.initEvent("click", true, true);
            var elem = document.getElementById('lnkUpdateRC_1');
            elem.dispatchEvent(cle);
        }

       

        if (charCode == 8 || charCode == 27 || charCode == 9 || charCode == 13) {

            return true;

        } else if (eventRef.keyCode != null && (eventRef.keyCode == 46 || eventRef.keyCode == 39) && eventRef.charCode != null && eventRef.charCode == 0) {

            return true;

        }

        var character = String.fromCharCode(charCode);
        var alphanum = /^[0-9a-fA-F]+$/;
        if (character.match(alphanum) && updated.length < '8') {
            return true;
        } else {
            return false;
        }


    }

    function UpdateResponseWare(caller) {
        var updated = document.getElementById('txtResponseWare_1').value;
        var alphanum = /^[0-9a-fA-F]+$/;
        if (updated == "") {
            alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
            return false;
        } else if (updated.length != '6') {
            alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
            return false;
        } else if (updated.match(alphanum)) {
            var updatedurl = document.getElementById('lnkUpdateRW_1').href;
            document.getElementById('lnkUpdateRW_1').href = updatedurl + '&deviceid=' + updated;
            alert("Update Response Ware");
            return true;
        } else {
            alert("Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F)");
            return false;
        }

    }

    function CancelResponseCard(caller) {
        var str = caller.id;
        var n = str.split("_");
        var divToShowId = "divEditRC_" + n[1];
        var divToHideId = "divUpdateCancelRC_" + n[1];
        var divToHide = document.getElementById(divToHideId);
        var divToShow = document.getElementById(divToShowId);
        var textDivToHideId = "divTextResponseCard_" + n[1];
        var lblDivToShowId = "divResponseCard_" + n[1];
        var lblToShow = document.getElementById(lblDivToShowId);
        var textdivToHide = document.getElementById(textDivToHideId);
        divToHide.style.display = 'none';
        divToShow.style.display = 'inline';
        textdivToHide.style.display = 'none';
        lblToShow.style.display = 'inline';
        //alert(document.getElementById('divResponseCard_1').innerHTML;);
        //document.getElementById(txtResponseCard_1).value =  document.getElementById('divResponseCard_1').innerHTML;

    }

    function CancelResponseWare(caller) {
        var str = caller.id;
        var n = str.split("_");
        var divToShowId = "divEditRW_" + n[1];
        var divToHideId = "divUpdateCancelRW_" + n[1];
        var divToHide = document.getElementById(divToHideId);
        var divToShow = document.getElementById(divToShowId);
        var textDivToHideId = "divTextResponseWare_" + n[1];
        var lblDivToShowId = "divResponseWare_" + n[1];
        var lblToShow = document.getElementById(lblDivToShowId);
        var textdivToHide = document.getElementById(textDivToHideId);
        divToHide.style.display = 'none';
        divToShow.style.display = 'inline';
        textdivToHide.style.display = 'none';
        lblToShow.style.display = 'inline';
    }

    function RegisterRC() {
        var registerText = document.getElementById("txtRegisterDeviceId");
        if (registerText.value == "") {
            document.getElementById("spnMessage").style.display = "inline";
            document.getElementById("spnMessage").innerHTML = "Device ID Invalid - Device IDs can only be 6 characters (0-9, A-F).";
        } else {
            document.getElementById("spnMessage").style.display = "inline";
            document.getElementById("spnMessage").innerHTML = "Your Device ID has been successfully registered for all courses.";
        }
    }

    function RegisterRW() {
        var eMail = document.getElementById("txtResponseWareEmail");
        var password = document.getElementById("txtResponseWarePassword");

        if (eMail.value == "" && password.value == "") {
            document.getElementById("spnEmailMessage").style.display = "inline";
            document.getElementById("spnEmailMessage").innerHTML = "You must provide a ResponseWare Email Address.";
            document.getElementById("spnPasswordMessage").style.display = "inline";
            document.getElementById("spnPasswordMessage").innerHTML = "Please provide a Password.";
            document.getElementById("spnResponseWareMessage").style.display = "none";

        } else if (password.value == "" && eMail.value != "") {
            document.getElementById("spnPasswordMessage").style.display = "inline";
            document.getElementById("spnPasswordMessage").innerHTML = "Please provide a Password.";
            document.getElementById("spnEmailMessage").style.display = "none";
            document.getElementById("spnEmailMessage").innerHTML = "";
            document.getElementById("spnResponseWareMessage").style.display = "none";

        } else if (eMail.value == "" && password.value != "") {
            document.getElementById("spnEmailMessage").style.display = "inline";
            document.getElementById("spnEmailMessage").innerHTML = "You must provide a ResponseWare Email Address.";
            document.getElementById("spnPasswordMessage").style.display = "none";
            document.getElementById("spnPasswordMessage").innerHTML = "";
            document.getElementById("spnResponseWareMessage").style.display = "none";
        } else {
            document.getElementById("spnPasswordMessage").style.display = "none";
            document.getElementById("spnPasswordMessage").innerHTML = "";
            document.getElementById("spnEmailMessage").style.display = "none";
            document.getElementById("spnEmailMessage").innerHTML = "";
            document.getElementById("spnResponseWareMessage").style.display = "inline";
            document.getElementById("spnResponseWareMessage").innerHTML = "Your Device ID has been successfully registered for all courses.";
        }

    }

    function HideAllUpdateTexts() {
        var hideDivsRW = document.getElementsByRegex('^divUpdateCancelRW_.*');
        var displayDivsRW = document.getElementsByRegex('^divEditRW_.*');
        var hideDivsRC = document.getElementsByRegex('^divUpdateCancelRC_.*');
        var displayDivsRC = document.getElementsByRegex('^divEditRC_.*');
        for (var i = 0; hideDivsRW[i]; i++) {
            hideDivsRW[i].style.display = 'none';
        }
        for (var j = 0; displayDivsRW[j]; j++) {
            displayDivsRW[j].style.display = 'inline';
        }
        for (var k = 0; hideDivsRC[k]; k++) {
            hideDivsRC[k].style.display = 'none';
        }
        for (var l = 0; displayDivsRC[l]; l++) {
            displayDivsRC[l].style.display = 'inline';
        }

        var hideTextsRW = document.getElementsByRegex('^divTextResponseWare_.*');
        var displayLinksRW = document.getElementsByRegex('^divResponseWare_.*');
        var hideTextRC = document.getElementsByRegex('^divTextResponseCard_.*');
        var displayLinkRC = document.getElementsByRegex('^divResponseCard_.*');
        for (var i = 0; hideTextRC[i]; i++) {
            hideTextRC[i].style.display = 'none';
        }
        for (var i = 0; displayLinkRC[i]; i++) {
            displayLinkRC[i].style.display = 'inline';
        }
        for (var i = 0; hideTextsRW[i]; i++) {
            hideTextsRW[i].style.display = 'none';
        }
        for (var i = 0; displayLinksRW[i]; i++) {
            displayLinksRW[i].style.display = 'inline';
        }
    }

    function unhidett(divID) {
        var item = document.getElementById(divID);
        var itemToHide;
        if (item) {
            HideAllUpdateTexts();
            switch (divID) {
                case "divResponseCard":
                    itemToHide = document.getElementById("divResponseWare");
                    itemToHide.className = 'hiddens';
                    item.className = (item.className == 'hiddens') ? 'unhidden' : 'hiddens';
                    break;
                case "divResponseWare":
                    itemToHide = document.getElementById("divResponseCard");
                    itemToHide.className = 'hiddens';
                    item.className = (item.className == 'hiddens') ? 'unhidden' : 'hiddens';
                    break;
                default:
                    item.className = 'hiddens';
            }
        } else {
            var hideRw = document.getElementById("divResponseWare");
            var hideRc = document.getElementById("divResponseCard");
            hideRw.className = 'hiddens';
            hideRc.className = 'hiddens';
        }
    }
    document['getElementsByRegex'] = function (pattern) {
        var arrElements = []; // To accumulate matching elements.
        var re = new RegExp(pattern); // The regex to match with.

        function findRecursively(aNode) { // Recursive function to traverse DOM.
            if (!aNode) return;
            if (aNode.id !== undefined && aNode.id.search(re) != -1) arrElements.push(aNode); // FOUND ONE!
            for (var idx in aNode.childNodes) // search children...
            findRecursively(aNode.childNodes[idx]);
        };

        findRecursively(document); // Initiate recursive matching.
        return arrElements; // Return matching elements.
    };

    function ToggleDivDisplay(hideDivs, displayDivs, divToShowId, device, txtToShowId, lnkToHideId) {
        var deviceClick = device;
        if (deviceClick == "ResponseCard") {
            var hideRegisterDivs = document.getElementsByRegex('^divUpdateCancelRC_.*');
            var displayLnkDivs = document.getElementsByRegex('^divEditRC_.*');
            for (var i = 0; hideRegisterDivs[i]; i++) {
                hideRegisterDivs[i].style.display = 'none';
            }
            for (var i = 0; displayLnkDivs[i]; i++) {
                displayLnkDivs[i].style.display = 'inline';
            }
            var hideRegisterDivsRW = document.getElementsByRegex('^divUpdateCancelRW_.*');
            var displayLnkDivsRW = document.getElementsByRegex('^divEditRW_.*');
            var hideTextsRW = document.getElementsByRegex('^divTextResponseWare_.*');
            var displayLinksRW = document.getElementsByRegex('^divResponseWare_.*');
            var hideTextRC = document.getElementsByRegex('^divTextResponseCard_.*');
            var displayLinkRC = document.getElementsByRegex('^divResponseCard_.*');
            for (var i = 0; hideTextRC[i]; i++) {
                if (hideTextRC[i].id == txtToShowId) {} else {
                    hideTextRC[i].style.display = 'none';
                }
            }
            for (var i = 0; hideTextRC[i]; i++) {
                if (hideTextRC[i].id == txtToShowId) {} else {
                    displayLinkRC[i].style.display = 'inline';
                }
            }
            for (var i = 0; hideRegisterDivsRW[i]; i++) {
                hideRegisterDivsRW[i].style.display = 'none';
            }
            for (var i = 0; displayLnkDivsRW[i]; i++) {
                displayLnkDivsRW[i].style.display = 'inline';
            }
            for (var i = 0; hideTextsRW[i]; i++) {
                hideTextsRW[i].style.display = 'none';
            }
            for (var i = 0; displayLinksRW[i]; i++) {
                displayLinksRW[i].style.display = 'inline';
            }
        } else {
            var hideUpdateDivs = document.getElementsByRegex('^divUpdateCancelRW_.*');
            var displayDeviceDivs = document.getElementsByRegex('^divEditRW_.*');
            for (var i = 0; hideUpdateDivs[i]; i++) {
                hideUpdateDivs[i].style.display = 'none';
            }
            for (var i = 0; displayDeviceDivs[i]; i++) {
                displayDeviceDivs[i].style.display = 'inline';
            }
            var hideRegisterDivsRC = document.getElementsByRegex('^divUpdateCancelRC_.*');
            var displayLnkDivsRC = document.getElementsByRegex('^divEditRC_.*');
            var hideTextsRC = document.getElementsByRegex('^divTextResponseCard_.*');
            var displayLinksRC = document.getElementsByRegex('^divResponseCard_.*');
            var hideTextRW = document.getElementsByRegex('^divTextResponseWare_.*');
            var displayLinkRW = document.getElementsByRegex('^divResponseWare_.*');
            for (var i = 0; hideTextRW[i]; i++) {
                if (hideTextRW[i].id == txtToShowId) {} else {
                    hideTextRW[i].style.display = 'none';
                }
            }
            for (var i = 0; hideTextRW[i]; i++) {
                if (hideTextRW[i].id == txtToShowId) {} else {
                    displayLinkRW[i].style.display = 'inline';
                }
            }
            for (var i = 0; hideRegisterDivsRC[i]; i++) {
                hideRegisterDivsRC[i].style.display = 'none';
            }
            for (var i = 0; displayLnkDivsRC[i]; i++) {
                displayLnkDivsRC[i].style.display = 'inline';
            }
            for (var i = 0; hideTextsRC[i]; i++) {
                hideTextsRC[i].style.display = 'none';
            }
            for (var i = 0; displayLinksRC[i]; i++) {
                displayLinksRC[i].style.display = 'inline';
            }
        }
        for (var i = 0; hideDivs[i]; i++) {
            if (hideDivs[i].id == divToShowId) {} else {
                hideDivs[i].style.display = 'none';
            }
        }
        for (var i = 0; displayDivs[i]; i++) {
            if (hideDivs[i].id == divToShowId) {} else {
                displayDivs[i].style.display = 'inline';
            }
        }
    }