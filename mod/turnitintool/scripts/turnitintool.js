var choiceUserString;
var choiceCountString;
if (getcookie('turnitintool_choice_user')==null) {
    choiceUserString = '';
    choiceCountString = '';
} else {
    choiceUserString = getcookie('turnitintool_choice_user');
    choiceCountString = getcookie('turnitintool_choice_count');
}
var refreshcount = 0;
var enrollcount = 0;
var sid;
var openurl;
var newwindow;
function updateSubForm(submissionArray,stringsArray,thisForm,genspeed,user) {
    if (user==null) {
        user="tutor";
    }
    // submissionArray(userid,partid,title)
    // stringsArray('addsubmission','resubmit','resubmission','resubmissionnotenabled');
    var userid = thisForm.userid.value;
    var partid = thisForm.submissionpart.value;
    if (genspeed==1) {
        thisForm.submissiontitle.value='';
        thisForm.submissiontitle.readOnly=false;
        thisForm.submissiontitle.style.color='inherit';
        document.getElementById('submissionnotice').innerHTML='';
    } else {
        thisForm.submissiontitle.value='';
        thisForm.submissiontitle.readOnly=false;
        thisForm.submissiontitle.style.color='inherit';
    }
    thisForm.submitbutton.value=stringsArray[0];
    thisForm.submitbutton.disabled=false;
    if (thisForm.submissiontype.value==1) {
        thisForm.submissionfile.disabled=false;
    } else {
        thisForm.submissiontext.disabled=false;
        thisForm.submissiontext.readOnly=false;
    }

    var userFound=false;
    for (i=0;i<submissionArray.length;i++) {
        submission = submissionArray[i];
        if (genspeed==1 && submission[4]!=1) {
            if (submission[0]===userid && submission[1]===partid && !userFound) {
                userFound=true;
                if (submission[3]!="1" && user=="tutor") {
                    thisForm.submissiontitle.value=stringsArray[4];
                    thisForm.submissiontitle.readOnly=true;
                    thisForm.submissiontitle.style.color='#666666';
                } else {
                    thisForm.submissiontitle.value=Encoder.htmlDecode(submission[2]);
                }
                thisForm.submitbutton.value=stringsArray[0];
                thisForm.submitbutton.disabled=false;
                if (thisForm.submissiontype.value==1) {
                    thisForm.submissionfile.disabled=false;
                } else {
                    thisForm.submissiontext.disabled=false;
                    thisForm.submissiontext.readOnly=false;
                }
                document.getElementById('submissionnotice').innerHTML='<i>('+stringsArray[2]+')</i>';
            }
        } else {
            if (submission[0]===userid && submission[1]===partid && !userFound) {
                userFound=true;
                thisForm.submissiontitle.value==Encoder.htmlDecode(submission[2]);
                thisForm.submissiontitle.readOnly=true;
                thisForm.submissiontitle.style.color='#666666';
                thisForm.submitbutton.value=stringsArray[3];
                thisForm.submitbutton.disabled=true;
                if (thisForm.submissiontype.value==1) {
                    thisForm.submissionfile.disabled=true;
                } else {
                    thisForm.submissiontext.disabled=true;
                    thisForm.submissiontext.readOnly=true;
                }
            }
        }
        submission=null;
    }
}
function screenOpen(url,sidin,autoupdate,warn,options) {
    if (warn && !confirm(warn)) {
        return false;
    } else {
        openurl=url;
        newwindow=window.open('','GradeMark',options+',resizable=1');
        newwindow.document.write('<frameset><frame id="newWindow" name="newWindow"></frame></frameset>');
        newwindow.document.getElementById('newWindow').src = url;
        newwindow.focus();
        if ( autoupdate === '1' ) {
            var refreshSubmission = function() {
                refreshSubmissionsAjax( sidin, 2 );
            }
            window.newwindow.onunload = refreshSubmission;
            window.newwindow.onbeforeunload = refreshSubmission;
        }
    }
}
function refreshSubmissionsAjax( sidin, priority ) {
    if ( refreshcount < 1 ) {
        refreshcount++;
        var update = undefined != priority ? '&update='+priority : '&update=1';
        var subid = undefined != sidin ? '&subid='+sidin : '';
        jQuery('#inboxNotice').css( 'display', 'block' );
        jQuery.ajax( { 
            'url': location.href+update+subid,
            'success': function() {
                refreshcount--;
                if ( enrollcount < 1 ) window.location.reload();
            }
        } );
    }
}
function enrolStudentsAjax( users, message ) {
    if ( enrollcount < 1 ) {
        enrollcount++;
        var count = 0;
        var messages = {};
        messages.err_msg = '';
        messages.message = message;
        jQuery('#inboxNotice').css( 'display', 'block' );
        enrolStudent( users, count, messages, 0 );
    }
}
function enrolStudent( users, count, messages, error_count ) {
    jQuery.ajax( {
        'url': location.href+"&enrollstudent="+users[count],
        'success': function( data ) {
            count++;
            var percent = ( count / users.length ) * 100;
            var percentdone = Math.ceil( percent ) + "%";
            jQuery('#inboxNotice span span').html( messages.message + ' (' + percentdone + ')' );
            var obj = jQuery.parseJSON( data );
            if ( obj.status == 'error' ) {
                error_count++;
                messages.err_msg += obj.msg;
                messages.description = obj.description;
            }
            if ( count < users.length ) {
                enrolStudent( users, count, messages, error_count );
            } else if ( error_count > 0 ) {
                output = messages.description + "\n\n" + messages.err_msg;
                alert( output );
                enrollcount--;
                if ( refreshcount < 1 ) window.location.reload();
            } else {
                enrollcount--;
                if ( refreshcount < 1 ) window.location.reload();
            }
        },
        'error': function () {
            enrollcount--;
            if ( refreshcount < 1 ) window.location.reload();
        }
    } );
}
function turnitintool_countchars(thisarea,outputblock,maxchars,errormsg) {
    var outblock = document.getElementById(outputblock);
    outblock.innerHTML=thisarea.value.length+'/'+maxchars;
    if (thisarea.value.length>maxchars) {
        alert(errormsg);
    }
}
function unloadFunction() {
    window.location.href=location.href+'&update=2';
}
function turnitintool_jumptopage(url) {
    window.location.href=url;
}
function roundNumber(number,places) {
    var output = Math.round(number*Math.pow(10,places))/Math.pow(10,places);
    return output;
}
function turnitintool_pauseFor(secs) {
    time=secs*100;
    settimeout(turnitintool_pauseFor,time);
}
function turnitintool_editpart(idstring,thisid,inner) {
    document.getElementById('partsummary').innerHTML=Encoder.htmlDecode(inner);
}
function turnitintool_addvalues(thisid,values) {
    document.getElementById('partnametext_'+thisid).innerHTML=values['partnametext'];
    document.getElementById('dtstarttext_'+thisid).innerHTML=values['dtstarttext'];
    document.getElementById('dtduetext_'+thisid).innerHTML=values['dtduetext'];
    document.getElementById('dtposttext_'+thisid).innerHTML=values['dtposttext'];
    document.getElementById('maxmarkstext_'+thisid).innerHTML=values['maxmarkstext'];
    document.getElementById('ticktext_'+thisid).innerHTML=values['ticktext'];
    document.getElementById('partname_'+thisid).style.display='none';
    document.getElementById('dtstart_'+thisid).style.display='none';
    document.getElementById('dtdue_'+thisid).style.display='none';
    document.getElementById('dtpost_'+thisid).style.display='none';
    document.getElementById('maxmarks_'+thisid).style.display='none';
    document.getElementById('tick_'+thisid).style.display='none';
}
function dopercents(thisform,totalnum) {
    var totalweights=0;
    for (i=1;i<=totalnum;i++) {
        thisvalue=document.getElementById('weightinput_'+i).value;
        if (isNaN(thisvalue)) {
            thisvalue=0;
        }
        totalweights=parseInt(totalweights)+parseInt(thisvalue);
    }
    for (i=1;i<=totalnum;i++) {
        thisvalue=document.getElementById('weightinput_'+i).value;
        if (isNaN(thisvalue)) {
            thisvalue=0;
        }
        thispercent=roundNumber(thisvalue/totalweights*100,2);
        output='<i style="color: #888888;">('+thispercent+'%)*</i>';
        document.getElementById('weight_'+i).innerHTML=output;
    }
}
function assignmentcheck(turnitintoolid) {
    if (turnitintoolid==getcookie('turnitintoolid')) {
        setcookie('turnitintoolid',turnitintoolid,null,'/','','');
    } else {
        togglehideall();
        setcookie('turnitintoolid',turnitintoolid,null,'/','','');
    }
}
function setuserchoice() {
    if ( choiceUserString == '' ) {
        // Hide everything if there is no cookie
        togglehideall();
        return;
    }
    //alert(document.cookie);
    choiceUserArray=choiceUserString.split('_');
    choiceCountArray=choiceCountString.split('_');
    if (choiceUserArray.length==0 && choiceUserString.length>0) {
        choiceUserArray=new Array(choiceUserString);
        choiceCountArray=new Array(choiceCountString);
    }
    for (i=0;i<choiceUserArray.length;i++) {
        for (n=1;n<=choiceCountArray[i];n++) {
            try {
                document.getElementById("row_"+choiceUserArray[i]+"_sub_"+n).style.display="";
                document.getElementById('userblock_'+choiceUserArray[i]).src="pix/minus.gif";
            } catch(err) {
            // Nothing
            }
        }
    }
}
function toggleview(userid,count,force) {
    if (force==null) {
        i=1;
        while (blockDisplay1=document.getElementById("row_"+userid+"_sub_"+i)) {
            if (blockDisplay1.style.display=="none") {
                blockDisplay1.style.display="";
                document.getElementById('userblock_'+userid).src="pix/minus.gif";
                if (i==count) {
                    if (choiceUserString=='') {
                        sep='';
                    } else {
                        sep='_';
                    }
                    choiceUserString += sep+userid;
                    choiceCountString += sep+i;
                }
            } else {
                blockDisplay1.style.display="none";
                document.getElementById('userblock_'+userid).src="pix/plus.gif";
                if (i==count) {
                    removefromchoice(userid,choiceUserString,choiceCountString);
                }
            }
            i++;
        }
    } else {
        for (i=1;i<=count;i++) {
            document.getElementById("row_"+userid+"_sub_"+i).style.display=force;
            if (force=='none') {
                document.getElementById('userblock_'+userid).src="pix/plus.gif";
            } else {
                document.getElementById('userblock_'+userid).src="pix/minus.gif";
            }
            if (force=="none" && i==count) {
                removefromchoice(userid,choiceUserString,choiceCountString);
            } else if (i==count) {
                if (choiceUserString=='') {
                    sep='';
                } else {
                    sep='_';
                }
                choiceUserString += sep+userid;
                choiceCountString += sep+i;
            }
        }
    }
    setcookie('turnitintool_choice_user',choiceUserString,null,'/','','');
    setcookie('turnitintool_choice_count',choiceCountString,null,'/','','');
}
function toggleshowall() {
    for (i=0;i<users.length;i++) {
        for (n=1;n<=count[i];n++) {
            document.getElementById("row_"+users[i]+"_sub_"+n).style.display="";
            document.getElementById('userblock_'+users[i]).src="pix/minus.gif";
        }
    }
    choiceUserString=users.join('_');
    choiceCountString=count.join('_');
    setcookie('turnitintool_choice_user',choiceUserString,null,'/','','');
    setcookie('turnitintool_choice_count',choiceCountString,null,'/','','');
}
function togglehideall(docookie) {
    for (i=0;i<users.length;i++) {
        for (n=1;n<=count[i];n++) {
            document.getElementById("row_"+users[i]+"_sub_"+n).style.display="none";
            document.getElementById('userblock_'+users[i]).src="pix/plus.gif";
        }
    }
    if (docookie==null) {
        choiceUserString = '';
        choiceCountString = '';
        setcookie('turnitintool_choice_user',choiceUserString,null,'/','','');
        setcookie('turnitintool_choice_count',choiceCountString,null,'/','','');
    }
}
function removefromchoice(findvar,string1,string2) {
    var keymatch = null;
    var newarray1 = new Array();
    var newarray2 = new Array();
    array1=string1.split('_');
    array2=string2.split('_');
    for (j=0;j<array1.length;j++) {
        // remove the find var from array1
        if (array1[j]!=findvar) {
            newarray1.push(array1[j]);
        } else {
            keymatch = j;
        }
    }
    for (j=0;j<array2.length;j++) {
        // Remove the same key from array2
        if (j!=keymatch) {
            newarray2.push(array2[j]);
        }
    }
    choiceUserString = newarray1.join('_');
    choiceCountString = newarray2.join('_');
}
function setcookie( name, value, expires, path, domain, secure ) {
    var today = new Date();
    today.setTime( today.getTime() );

    if ( expires ) {
        expires = expires * 1000 * 60 * 60 * 24;
    }
    var expires_date = new Date( today.getTime() + (expires) );

    document.cookie = name + "=" +escape( value ) +
    ( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) +
    ( ( path ) ? ";path=" + path : "" ) +
    ( ( domain ) ? ";domain=" + domain : "" ) +
    ( ( secure ) ? ";secure" : "" );
}
function getcookie( check_name ) {
    var a_all_cookies = document.cookie.split( ';' );
    var a_temp_cookie = '';
    var cookie_name = '';
    var cookie_value = '';
    var b_cookie_found = false;

    for ( i = 0; i < a_all_cookies.length; i++ ) {
        a_temp_cookie = a_all_cookies[i].split( '=' );
        cookie_name = a_temp_cookie[0].replace(/^\s+|\s+$/g, '');
        if ( cookie_name == check_name ) {
            b_cookie_found = true;
            if ( a_temp_cookie.length > 1 ) {
                cookie_value = unescape( a_temp_cookie[1].replace(/^\s+|\s+$/g, '') );
            }
            return cookie_value;
            break;
        }
        a_temp_cookie = null;
        cookie_name = '';
    }
    if ( !b_cookie_found ) {
        return null;
    }
}
function deletecookie( name, path, domain ) {
    if ( getcookie( name ) ) document.cookie = name + "=" +
        ( ( path ) ? ";path=" + path : "") +
        ( ( domain ) ? ";domain=" + domain : "" ) +
        ";expires=Thu, 01-Jan-1970 00:00:01 GMT";
}
function editgrade(submission,textcolor,background,warn) {
    if (warn && !confirm(warn)) {
        return false;
    } else {
        var edititem=document.getElementById("edit_"+submission);
        var gradeitem=document.getElementById("grade_"+submission);
        var tickitem=document.getElementById("tick_"+submission);
        var hideshowitem=document.getElementById("hideshow_"+submission);
        gradeitem.style.backgroundColor=background;
        gradeitem.style.color=textcolor;
        gradeitem.style.border='1px inset';
        gradeitem.readOnly=false;
        edititem.style.display='none';
        tickitem.style.display='inline';
        hideshowitem.style.display='inline';
    }
}
function viewgrade(submission,textcolor,background,warn) {
    var edititem=document.getElementById("edit_"+submission);
    var gradeitem=document.getElementById("grade_"+submission);
    var tickitem=document.getElementById("tick_"+submission);
    var hideshowitem=document.getElementById("hideshow_"+submission);
    gradeitem.style.backgroundColor=background;
    gradeitem.style.color=textcolor;
    gradeitem.style.border='none';
    gradeitem.readOnly=true;
    tickitem.style.display='none';
    var addwarn='';
    if (warn) {
        addwarn = ',\''+warn+'\'';
    }
    document.write('<a href="javascript:;" onclick="editgrade(\''+submission+'\',\'black\',\'white\''+addwarn+');" id="edit_'+submission+'"><img src="pix/edit.png" class="tiiicons" /></a>');
    if (gradeitem.value=='') {
        hideshowitem.style.display='none';
    }
}
Encoder = {

    // When encoding do we convert characters into html or numerical entities
    EncodeType : "entity",  // entity OR numerical

    isEmpty : function(val){
        if(val){
            return ((val===null) || val.length==0 || /^\s+$/.test(val));
        }else{
            return true;
        }
    },
    // Convert HTML entities into numerical entities
    HTML2Numerical : function(s){
        var arr1 = new Array('&nbsp;','&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&shy;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&agrave;','&aacute;','&acirc;','&atilde;','&Auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&Ouml;','&times;','&oslash;','&ugrave;','&uacute;','&ucirc;','&Uuml;','&yacute;','&thorn;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&Oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;','&quot;','&amp;','&lt;','&gt;','&oelig;','&oelig;','&scaron;','&scaron;','&yuml;','&circ;','&tilde;','&ensp;','&emsp;','&thinsp;','&zwnj;','&zwj;','&lrm;','&rlm;','&ndash;','&mdash;','&lsquo;','&rsquo;','&sbquo;','&ldquo;','&rdquo;','&bdquo;','&dagger;','&dagger;','&permil;','&lsaquo;','&rsaquo;','&euro;','&fnof;','&alpha;','&beta;','&gamma;','&delta;','&epsilon;','&zeta;','&eta;','&theta;','&iota;','&kappa;','&lambda;','&mu;','&nu;','&xi;','&omicron;','&pi;','&rho;','&sigma;','&tau;','&upsilon;','&phi;','&chi;','&psi;','&omega;','&alpha;','&beta;','&gamma;','&delta;','&epsilon;','&zeta;','&eta;','&theta;','&iota;','&kappa;','&lambda;','&mu;','&nu;','&xi;','&omicron;','&pi;','&rho;','&sigmaf;','&sigma;','&tau;','&upsilon;','&phi;','&chi;','&psi;','&omega;','&thetasym;','&upsih;','&piv;','&bull;','&hellip;','&prime;','&prime;','&oline;','&frasl;','&weierp;','&image;','&real;','&trade;','&alefsym;','&larr;','&uarr;','&rarr;','&darr;','&harr;','&crarr;','&larr;','&uarr;','&rarr;','&darr;','&harr;','&forall;','&part;','&exist;','&empty;','&nabla;','&isin;','&notin;','&ni;','&prod;','&sum;','&minus;','&lowast;','&radic;','&prop;','&infin;','&ang;','&and;','&or;','&cap;','&cup;','&int;','&there4;','&sim;','&cong;','&asymp;','&ne;','&equiv;','&le;','&ge;','&sub;','&sup;','&nsub;','&sube;','&supe;','&oplus;','&otimes;','&perp;','&sdot;','&lceil;','&rceil;','&lfloor;','&rfloor;','&lang;','&rang;','&loz;','&spades;','&clubs;','&hearts;','&diams;');
        var arr2 = new Array('&#160;','&#161;','&#162;','&#163;','&#164;','&#165;','&#166;','&#167;','&#168;','&#169;','&#170;','&#171;','&#172;','&#173;','&#174;','&#175;','&#176;','&#177;','&#178;','&#179;','&#180;','&#181;','&#182;','&#183;','&#184;','&#185;','&#186;','&#187;','&#188;','&#189;','&#190;','&#191;','&#192;','&#193;','&#194;','&#195;','&#196;','&#197;','&#198;','&#199;','&#200;','&#201;','&#202;','&#203;','&#204;','&#205;','&#206;','&#207;','&#208;','&#209;','&#210;','&#211;','&#212;','&#213;','&#214;','&#215;','&#216;','&#217;','&#218;','&#219;','&#220;','&#221;','&#222;','&#223;','&#224;','&#225;','&#226;','&#227;','&#228;','&#229;','&#230;','&#231;','&#232;','&#233;','&#234;','&#235;','&#236;','&#237;','&#238;','&#239;','&#240;','&#241;','&#242;','&#243;','&#244;','&#245;','&#246;','&#247;','&#248;','&#249;','&#250;','&#251;','&#252;','&#253;','&#254;','&#255;','&#34;','&#38;','&#60;','&#62;','&#338;','&#339;','&#352;','&#353;','&#376;','&#710;','&#732;','&#8194;','&#8195;','&#8201;','&#8204;','&#8205;','&#8206;','&#8207;','&#8211;','&#8212;','&#8216;','&#8217;','&#8218;','&#8220;','&#8221;','&#8222;','&#8224;','&#8225;','&#8240;','&#8249;','&#8250;','&#8364;','&#402;','&#913;','&#914;','&#915;','&#916;','&#917;','&#918;','&#919;','&#920;','&#921;','&#922;','&#923;','&#924;','&#925;','&#926;','&#927;','&#928;','&#929;','&#931;','&#932;','&#933;','&#934;','&#935;','&#936;','&#937;','&#945;','&#946;','&#947;','&#948;','&#949;','&#950;','&#951;','&#952;','&#953;','&#954;','&#955;','&#956;','&#957;','&#958;','&#959;','&#960;','&#961;','&#962;','&#963;','&#964;','&#965;','&#966;','&#967;','&#968;','&#969;','&#977;','&#978;','&#982;','&#8226;','&#8230;','&#8242;','&#8243;','&#8254;','&#8260;','&#8472;','&#8465;','&#8476;','&#8482;','&#8501;','&#8592;','&#8593;','&#8594;','&#8595;','&#8596;','&#8629;','&#8656;','&#8657;','&#8658;','&#8659;','&#8660;','&#8704;','&#8706;','&#8707;','&#8709;','&#8711;','&#8712;','&#8713;','&#8715;','&#8719;','&#8721;','&#8722;','&#8727;','&#8730;','&#8733;','&#8734;','&#8736;','&#8743;','&#8744;','&#8745;','&#8746;','&#8747;','&#8756;','&#8764;','&#8773;','&#8776;','&#8800;','&#8801;','&#8804;','&#8805;','&#8834;','&#8835;','&#8836;','&#8838;','&#8839;','&#8853;','&#8855;','&#8869;','&#8901;','&#8968;','&#8969;','&#8970;','&#8971;','&#9001;','&#9002;','&#9674;','&#9824;','&#9827;','&#9829;','&#9830;');
        return this.swapArrayVals(s,arr1,arr2);
    },

    // Convert Numerical entities into HTML entities
    NumericalToHTML : function(s){
        var arr1 = new Array('&#160;','&#161;','&#162;','&#163;','&#164;','&#165;','&#166;','&#167;','&#168;','&#169;','&#170;','&#171;','&#172;','&#173;','&#174;','&#175;','&#176;','&#177;','&#178;','&#179;','&#180;','&#181;','&#182;','&#183;','&#184;','&#185;','&#186;','&#187;','&#188;','&#189;','&#190;','&#191;','&#192;','&#193;','&#194;','&#195;','&#196;','&#197;','&#198;','&#199;','&#200;','&#201;','&#202;','&#203;','&#204;','&#205;','&#206;','&#207;','&#208;','&#209;','&#210;','&#211;','&#212;','&#213;','&#214;','&#215;','&#216;','&#217;','&#218;','&#219;','&#220;','&#221;','&#222;','&#223;','&#224;','&#225;','&#226;','&#227;','&#228;','&#229;','&#230;','&#231;','&#232;','&#233;','&#234;','&#235;','&#236;','&#237;','&#238;','&#239;','&#240;','&#241;','&#242;','&#243;','&#244;','&#245;','&#246;','&#247;','&#248;','&#249;','&#250;','&#251;','&#252;','&#253;','&#254;','&#255;','&#34;','&#38;','&#60;','&#62;','&#338;','&#339;','&#352;','&#353;','&#376;','&#710;','&#732;','&#8194;','&#8195;','&#8201;','&#8204;','&#8205;','&#8206;','&#8207;','&#8211;','&#8212;','&#8216;','&#8217;','&#8218;','&#8220;','&#8221;','&#8222;','&#8224;','&#8225;','&#8240;','&#8249;','&#8250;','&#8364;','&#402;','&#913;','&#914;','&#915;','&#916;','&#917;','&#918;','&#919;','&#920;','&#921;','&#922;','&#923;','&#924;','&#925;','&#926;','&#927;','&#928;','&#929;','&#931;','&#932;','&#933;','&#934;','&#935;','&#936;','&#937;','&#945;','&#946;','&#947;','&#948;','&#949;','&#950;','&#951;','&#952;','&#953;','&#954;','&#955;','&#956;','&#957;','&#958;','&#959;','&#960;','&#961;','&#962;','&#963;','&#964;','&#965;','&#966;','&#967;','&#968;','&#969;','&#977;','&#978;','&#982;','&#8226;','&#8230;','&#8242;','&#8243;','&#8254;','&#8260;','&#8472;','&#8465;','&#8476;','&#8482;','&#8501;','&#8592;','&#8593;','&#8594;','&#8595;','&#8596;','&#8629;','&#8656;','&#8657;','&#8658;','&#8659;','&#8660;','&#8704;','&#8706;','&#8707;','&#8709;','&#8711;','&#8712;','&#8713;','&#8715;','&#8719;','&#8721;','&#8722;','&#8727;','&#8730;','&#8733;','&#8734;','&#8736;','&#8743;','&#8744;','&#8745;','&#8746;','&#8747;','&#8756;','&#8764;','&#8773;','&#8776;','&#8800;','&#8801;','&#8804;','&#8805;','&#8834;','&#8835;','&#8836;','&#8838;','&#8839;','&#8853;','&#8855;','&#8869;','&#8901;','&#8968;','&#8969;','&#8970;','&#8971;','&#9001;','&#9002;','&#9674;','&#9824;','&#9827;','&#9829;','&#9830;');
        var arr2 = new Array('&nbsp;','&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&shy;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&agrave;','&aacute;','&acirc;','&atilde;','&Auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&Ouml;','&times;','&oslash;','&ugrave;','&uacute;','&ucirc;','&Uuml;','&yacute;','&thorn;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&Oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;','&quot;','&amp;','&lt;','&gt;','&oelig;','&oelig;','&scaron;','&scaron;','&yuml;','&circ;','&tilde;','&ensp;','&emsp;','&thinsp;','&zwnj;','&zwj;','&lrm;','&rlm;','&ndash;','&mdash;','&lsquo;','&rsquo;','&sbquo;','&ldquo;','&rdquo;','&bdquo;','&dagger;','&dagger;','&permil;','&lsaquo;','&rsaquo;','&euro;','&fnof;','&alpha;','&beta;','&gamma;','&delta;','&epsilon;','&zeta;','&eta;','&theta;','&iota;','&kappa;','&lambda;','&mu;','&nu;','&xi;','&omicron;','&pi;','&rho;','&sigma;','&tau;','&upsilon;','&phi;','&chi;','&psi;','&omega;','&alpha;','&beta;','&gamma;','&delta;','&epsilon;','&zeta;','&eta;','&theta;','&iota;','&kappa;','&lambda;','&mu;','&nu;','&xi;','&omicron;','&pi;','&rho;','&sigmaf;','&sigma;','&tau;','&upsilon;','&phi;','&chi;','&psi;','&omega;','&thetasym;','&upsih;','&piv;','&bull;','&hellip;','&prime;','&prime;','&oline;','&frasl;','&weierp;','&image;','&real;','&trade;','&alefsym;','&larr;','&uarr;','&rarr;','&darr;','&harr;','&crarr;','&larr;','&uarr;','&rarr;','&darr;','&harr;','&forall;','&part;','&exist;','&empty;','&nabla;','&isin;','&notin;','&ni;','&prod;','&sum;','&minus;','&lowast;','&radic;','&prop;','&infin;','&ang;','&and;','&or;','&cap;','&cup;','&int;','&there4;','&sim;','&cong;','&asymp;','&ne;','&equiv;','&le;','&ge;','&sub;','&sup;','&nsub;','&sube;','&supe;','&oplus;','&otimes;','&perp;','&sdot;','&lceil;','&rceil;','&lfloor;','&rfloor;','&lang;','&rang;','&loz;','&spades;','&clubs;','&hearts;','&diams;');
        return this.swapArrayVals(s,arr1,arr2);
    },


    // Numerically encodes all unicode characters
    numEncode : function(s){

        if(this.isEmpty(s)) return "";

        var e = "";
        for (var i = 0; i < s.length; i++)
        {
            var c = s.charAt(i);
            if (c < " " || c > "~")
            {
                c = "&#" + c.charCodeAt() + ";";
            }
            e += c;
        }
        return e;
    },

    // HTML Decode numerical and HTML entities back to original values
    htmlDecode : function(s){

        var c,m,d = s;

        if(this.isEmpty(d)) return "";

        // convert HTML entites back to numerical entites first
        d = this.HTML2Numerical(d);

        // look for numerical entities &#34;
        arr=d.match(/&#[0-9]{1,5};/g);

        // if no matches found in string then skip
        if(arr!=null){
            for(var x=0;x<arr.length;x++){
                m = arr[x];
                c = m.substring(2,m.length-1); //get numeric part which is refernce to unicode character
                // if its a valid number we can decode
                if(c >= -32768 && c <= 65535){
                    // decode every single match within string
                    d = d.replace(m, String.fromCharCode(c));
                }else{
                    d = d.replace(m, ""); //invalid so replace with nada
                }
            }
        }

        return d;
    },

    // encode an input string into either numerical or HTML entities
    htmlEncode : function(s,dbl){

        if(this.isEmpty(s)) return "";

        // do we allow double encoding? E.g will &amp; be turned into &amp;amp;
        dbl = dbl | false; //default to prevent double encoding

        // if allowing double encoding we do ampersands first
        if(dbl){
            if(this.EncodeType=="numerical"){
                s = s.replace(/&/g, "&#38;");
            }else{
                s = s.replace(/&/g, "&amp;");
            }
        }

        // convert the xss chars to numerical entities ' " < >
        s = this.XSSEncode(s,false);

        if(this.EncodeType=="numerical" || !dbl){
            // Now call function that will convert any HTML entities to numerical codes
            s = this.HTML2Numerical(s);
        }

        // Now encode all chars above 127 e.g unicode
        s = this.numEncode(s);

        // now we know anything that needs to be encoded has been converted to numerical entities we
        // can encode any ampersands & that are not part of encoded entities
        // to handle the fact that I need to do a negative check and handle multiple ampersands &&&
        // I am going to use a placeholder

        // if we don't want double encoded entities we ignore the & in existing entities
        if(!dbl){
            s = s.replace(/&#/g,"##AMPHASH##");

            if(this.EncodeType=="numerical"){
                s = s.replace(/&/g, "&#38;");
            }else{
                s = s.replace(/&/g, "&amp;");
            }

            s = s.replace(/##AMPHASH##/g,"&#");
        }

        // replace any malformed entities
        s = s.replace(/&#\d*([^\d;]|$)/g, "$1");

        if(!dbl){
            // safety check to correct any double encoded &amp;
            s = this.correctEncoding(s);
        }

        // now do we need to convert our numerical encoded string into entities
        if(this.EncodeType=="entity"){
            s = this.NumericalToHTML(s);
        }

        return s;
    },

    // Encodes the basic 4 characters used to malform HTML in XSS hacks
    XSSEncode : function(s,en){
        if(!this.isEmpty(s)){
            en = en || true;
            // do we convert to numerical or html entity?
            if(en){
                s = s.replace(/\'/g,"&#39;"); //no HTML equivalent as &apos is not cross browser supported
                s = s.replace(/\"/g,"&quot;");
                s = s.replace(/</g,"&lt;");
                s = s.replace(/>/g,"&gt;");
            }else{
                s = s.replace(/\'/g,"&#39;"); //no HTML equivalent as &apos is not cross browser supported
                s = s.replace(/\"/g,"&#34;");
                s = s.replace(/</g,"&#60;");
                s = s.replace(/>/g,"&#62;");
            }
            return s;
        }else{
            return "";
        }
    },

    // returns true if a string contains html or numerical encoded entities
    hasEncoded : function(s){
        if(/&#[0-9]{1,5};/g.test(s)){
            return true;
        }else if(/&[A-Z]{2,6};/gi.test(s)){
            return true;
        }else{
            return false;
        }
    },

    // will remove any unicode characters
    stripUnicode : function(s){
        return s.replace(/[^\x20-\x7E]/g,"");

    },

    // corrects any double encoded &amp; entities e.g &amp;amp;
    correctEncoding : function(s){
        return s.replace(/(&amp;)(amp;)+/,"$1");
    },


    // Function to loop through an array swaping each item with the value from another array e.g swap HTML entities with Numericals
    swapArrayVals : function(s,arr1,arr2){
        if(this.isEmpty(s)) return "";
        var re;
        if(arr1 && arr2){
            //ShowDebug("in swapArrayVals arr1.length = " + arr1.length + " arr2.length = " + arr2.length)
            // array lengths must match
            if(arr1.length == arr2.length){
                for(var x=0,i=arr1.length;x<i;x++){
                    re = new RegExp(arr1[x], 'g');
                    s = s.replace(re,arr2[x]); //swap arr1 item with matching item from arr2
                }
            }
        }
        return s;
    },

    inArray : function( item, arr ) {
        for ( var i = 0, x = arr.length; i < x; i++ ){
            if ( arr[i] === item ){
                return i;
            }
        }
        return -1;
    }

}
function checkUncheckAll(toggle,field) {
    var fieldid;
    var i=0;
    fieldid = field + '_' + i;
    while ( undefined != document.getElementById( fieldid ) ) {
        if ( toggle.checked ) {
            document.getElementById( fieldid ).checked = true;
        } else {
            document.getElementById( fieldid ).checked = false;
        }
        i++;
        fieldid = field + '_' + i;
    }
}