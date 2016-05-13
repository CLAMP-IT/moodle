LzResourceLibrary.lzfocusbracket_rsrc={ptype:"sr",frames:['lps/components/lz/resources/focus/focus_top_lft.png','lps/components/lz/resources/focus/focus_top_rt.png','lps/components/lz/resources/focus/focus_bot_lft.png','lps/components/lz/resources/focus/focus_bot_rt.png'],width:7,height:7,sprite:'lps/components/lz/resources/focus/focus_top_lft.sprite.png',spriteoffset:0};LzResourceLibrary.lzfocusbracket_shdw={ptype:"sr",frames:['lps/components/lz/resources/focus/focus_top_lft_shdw.png','lps/components/lz/resources/focus/focus_top_rt_shdw.png','lps/components/lz/resources/focus/focus_bot_lft_shdw.png','lps/components/lz/resources/focus/focus_bot_rt_shdw.png'],width:9,height:9,sprite:'lps/components/lz/resources/focus/focus_top_lft_shdw.sprite.png',spriteoffset:7};LzResourceLibrary.play_button={ptype:"ar",frames:['resources/classic_play_button.png'],width:32.0,height:32.0,spriteoffset:16};LzResourceLibrary.stop_button={ptype:"ar",frames:['resources/classic_stop_button.png'],width:32.0,height:32.0,spriteoffset:48};LzResourceLibrary.reset_button={ptype:"ar",frames:['resources/classic_reset_button.png'],width:32.0,height:32.0,spriteoffset:80};LzResourceLibrary.__allcss={path:'usr/local/red5/webapps/openlaszlo/my-apps/timers/stopwatch.sprite.png'};;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;var thestopwatch=null;{
Class.make("$lzc$class__mb",["$m4",function($0){
this.setAttribute("fontheight",lz.Browser.getInitArg("fontheight"))
},"fontheight",void 0,"$m5",function($0){
this.setAttribute("red5url",lz.Browser.getInitArg("red5url"))
},"red5url",void 0,"$m6",function($0){
this.setAttribute("mename",lz.Browser.getInitArg("mename"))
},"mename",void 0,"$m7",function($0){
this.setAttribute("courseid",lz.Browser.getInitArg("courseid"))
},"courseid",void 0,"$m8",function($0){
this.setAttribute("uniqueid",lz.Browser.getInitArg("uniqueid"))
},"uniqueid",void 0,"$m9",function($0){
this.setAttribute("mode",lz.Browser.getInitArg("mode"))
},"mode",void 0,"headeridtag",void 0,"$ma",function($0){
if(canvas.fullscreen==true&&$0==true){
this.thestopwatch.setAttribute("fontheight",this.fontheight)
}else if(canvas.fullscreen==false&&$0==true){
this.thestopwatch.setAttribute("fontheight",this.fontheight)
}},"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzCanvas,["displayName","<anonymous extends='canvas'>","__LZCSSTagSelectors",["canvas","view","node","Instance"],"attributes",new LzInheritedHash(LzCanvas.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",accessible:"boolean",align:"string",allowfullscreen:"boolean",appbuilddate:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",compileroptions:"string",contextmenu:"string",cornerradius:"string",cursor:"token",dataloadtimeout:"numberExpression",datapath:"string",datasets:"string",debug:"boolean",defaultdataprovider:"string",defaultplacement:"string",embedfonts:"boolean",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framerate:"number",framesloadratio:"number",fullscreen:"boolean",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",history:"boolean",httpdataprovider:"string",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",lpsbuild:"string",lpsbuilddate:"string",lpsrelease:"string",lpsversion:"string",mask:"string",mediaerrortimeout:"numberExpression",medialoadtimeout:"numberExpression",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",percentcreated:"number",pixellock:"boolean",placement:"string",playing:"boolean",proxied:"inheritableBoolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",runtime:"string",screenorientation:"boolean",scriptlimits:"css",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",title:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__mb.attributes)
}}})($lzc$class__mb)
};canvas=new $lzc$class__mb(null,{$delegates:["onfullscreen","$ma",null],__LZproxied:"false",allowfullscreen:true,appbuilddate:"2011-12-01T11:48:29Z",bgcolor:16777215,courseid:new LzOnceExpr("$m7",null),embedfonts:true,font:"Verdana,Vera,sans-serif",fontheight:new LzOnceExpr("$m4",null),fontsize:11,fontstyle:"plain",headeridtag:"slaveview01",height:"100%",lpsbuild:"trunk@19126 (19126)",lpsbuilddate:"2011-04-30T08:09:13Z",lpsrelease:"Latest",lpsversion:"5.0.x",mename:new LzOnceExpr("$m6",null),mode:new LzOnceExpr("$m9",null),red5url:new LzOnceExpr("$m5",null),runtime:"dhtml",uniqueid:new LzOnceExpr("$m8",null),width:"100%"});lz.colors.offwhite=15921906;lz.colors.gray10=1710618;lz.colors.gray20=3355443;lz.colors.gray30=5066061;lz.colors.gray40=6710886;lz.colors.gray50=8355711;lz.colors.gray60=10066329;lz.colors.gray70=11776947;lz.colors.gray80=13421772;lz.colors.gray90=15066597;lz.colors.iceblue1=3298963;lz.colors.iceblue2=5472718;lz.colors.iceblue3=12240085;lz.colors.iceblue4=14017779;lz.colors.iceblue5=15659509;lz.colors.palegreen1=4290113;lz.colors.palegreen2=11785139;lz.colors.palegreen3=12637341;lz.colors.palegreen4=13888170;lz.colors.palegreen5=15725032;lz.colors.gold1=9331721;lz.colors.gold2=13349195;lz.colors.gold3=15126388;lz.colors.gold4=16311446;lz.colors.sand1=13944481;lz.colors.sand2=14276546;lz.colors.sand3=15920859;lz.colors.sand4=15986401;lz.colors.ltpurple1=6575768;lz.colors.ltpurple2=12038353;lz.colors.ltpurple3=13353453;lz.colors.ltpurple4=15329264;lz.colors.grayblue=12501704;lz.colors.graygreen=12635328;lz.colors.graypurple=10460593;lz.colors.ltblue=14540287;lz.colors.ltgreen=14548957;{
Class.make("$lzc$class_basefocusview",["active",void 0,"$lzc$set_active",function($0){
this.setActive($0)
},"target",void 0,"$lzc$set_target",function($0){
this.setTarget($0)
},"duration",void 0,"_animatorcounter",void 0,"ontarget",void 0,"_nexttarget",void 0,"onactive",void 0,"_xydelegate",void 0,"_widthdel",void 0,"_heightdel",void 0,"_delayfadeoutDL",void 0,"_dofadeout",void 0,"_onstopdel",void 0,"reset",function(){
this.setAttribute("x",0);this.setAttribute("y",0);this.setAttribute("width",canvas.width);this.setAttribute("height",canvas.height);this.setTarget(null)
},"setActive",function($0){
this.active=$0;if(this.onactive)this.onactive.sendEvent($0)
},"doFocus",function($0){
this._dofadeout=false;this.bringToFront();if(this.target)this.setTarget(null);this.setAttribute("visibility",this.active?"visible":"hidden");this._nexttarget=$0;if(this.visible){
this._animatorcounter+=1;var $1=null;var $2;var $3;var $4;var $5;if($0["getFocusRect"])$1=$0.getFocusRect();if($1){
$2=$1[0];$3=$1[1];$4=$1[2];$5=$1[3]
}else{
$2=$0.getAttributeRelative("x",canvas);$3=$0.getAttributeRelative("y",canvas);$4=$0.getAttributeRelative("width",canvas);$5=$0.getAttributeRelative("height",canvas)
};var $6=this.animate("x",$2,this.duration);this.animate("y",$3,this.duration);this.animate("width",$4,this.duration);this.animate("height",$5,this.duration);if(this.capabilities["minimize_opacity_changes"]){
this.setAttribute("visibility","visible")
}else{
this.animate("opacity",1,500)
};if(!this._onstopdel)this._onstopdel=new LzDelegate(this,"stopanim");this._onstopdel.register($6,"onstop")
};if(this._animatorcounter<1){
this.setTarget(this._nexttarget);var $1=null;var $2;var $3;var $4;var $5;if($0["getFocusRect"])$1=$0.getFocusRect();if($1){
$2=$1[0];$3=$1[1];$4=$1[2];$5=$1[3]
}else{
$2=$0.getAttributeRelative("x",canvas);$3=$0.getAttributeRelative("y",canvas);$4=$0.getAttributeRelative("width",canvas);$5=$0.getAttributeRelative("height",canvas)
};this.setAttribute("x",$2);this.setAttribute("y",$3);this.setAttribute("width",$4);this.setAttribute("height",$5)
}},"stopanim",function($0){
this._animatorcounter-=1;if(this._animatorcounter<1){
this._dofadeout=true;if(!this._delayfadeoutDL)this._delayfadeoutDL=new LzDelegate(this,"fadeout");lz.Timer.addTimer(this._delayfadeoutDL,1000);this.setTarget(this._nexttarget);this._onstopdel.unregisterAll()
}},"fadeout",function($0){
if(this._dofadeout){
if(this.capabilities["minimize_opacity_changes"]){
this.setAttribute("visibility","hidden")
}else{
this.animate("opacity",0,500)
}};this._delayfadeoutDL.unregisterAll()
},"setTarget",function($0){
this.target=$0;if(!this._xydelegate){
this._xydelegate=new LzDelegate(this,"followXY")
}else{
this._xydelegate.unregisterAll()
};if(!this._widthdel){
this._widthdel=new LzDelegate(this,"followWidth")
}else{
this._widthdel.unregisterAll()
};if(!this._heightdel){
this._heightdel=new LzDelegate(this,"followHeight")
}else{
this._heightdel.unregisterAll()
};if(this.target==null)return;var $1=$0;var $2=0;while($1!=canvas){
this._xydelegate.register($1,"onx");this._xydelegate.register($1,"ony");$1=$1.immediateparent;$2++
};this._widthdel.register($0,"onwidth");this._heightdel.register($0,"onheight");this.followXY(null);this.followWidth(null);this.followHeight(null)
},"followXY",function($0){
var $1=null;if(this.target["getFocusRect"])$1=this.target.getFocusRect();if($1){
this.setAttribute("x",$1[0]);this.setAttribute("y",$1[1])
}else{
this.setAttribute("x",this.target.getAttributeRelative("x",canvas));this.setAttribute("y",this.target.getAttributeRelative("y",canvas))
}},"followWidth",function($0){
var $1=null;if(this.target["getFocusRect"])$1=this.target.getFocusRect();if($1){
this.setAttribute("width",$1[2])
}else{
this.setAttribute("width",this.target.width)
}},"followHeight",function($0){
var $1=null;if(this.target["getFocusRect"])$1=this.target.getFocusRect();if($1){
this.setAttribute("height",$1[3])
}else{
this.setAttribute("height",this.target.height)
}},"$mc",function(){
return lz.Focus
},"$md",function($0){
this.setActive(lz.Focus.focuswithkey);if($0){
this.doFocus($0)
}else{
this.reset();if(this.active){
this.setActive(false)
}}},"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzView,["tagname","basefocusview","__LZCSSTagSelectors",["basefocusview","view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$delegates:["onstop","stopanim",null,"onfocus","$md","$mc"],_animatorcounter:0,_delayfadeoutDL:null,_dofadeout:false,_heightdel:null,_nexttarget:null,_onstopdel:null,_widthdel:null,_xydelegate:null,active:false,duration:400,initstage:"late",onactive:LzDeclaredEvent,ontarget:LzDeclaredEvent,options:{ignorelayout:true},target:null,visible:false},$lzc$class_basefocusview.attributes)
}}})($lzc$class_basefocusview)
};{
Class.make("$lzc$class__mu",["$me",function($0){
var $1=-this.classroot.offset;if($1!==this["x"]||!this.inited){
this.setAttribute("x",$1)
}},"$mf",function(){
try{
return [this.classroot,"offset"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$mg",function($0){
var $1=-this.classroot.offset;if($1!==this["y"]||!this.inited){
this.setAttribute("y",$1)
}},"$mh",function(){
try{
return [this.classroot,"offset"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzView,["displayName","<anonymous extends='view'>","children",[{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$classrootdepth:2,opacity:0.25,resource:"lzfocusbracket_shdw",x:1,y:1},"class":LzView},{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$classrootdepth:2,resource:"lzfocusbracket_rsrc"},"class":LzView}],"__LZCSSTagSelectors",["view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__mu.attributes)
}}})($lzc$class__mu)
};{
Class.make("$lzc$class__mv",["$mi",function($0){
var $1=this.parent.width-this.width+this.classroot.offset;if($1!==this["x"]||!this.inited){
this.setAttribute("x",$1)
}},"$mj",function(){
try{
return [this.parent,"width",this,"width",this.classroot,"offset"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$mk",function($0){
var $1=-this.classroot.offset;if($1!==this["y"]||!this.inited){
this.setAttribute("y",$1)
}},"$ml",function(){
try{
return [this.classroot,"offset"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzView,["displayName","<anonymous extends='view'>","children",[{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$classrootdepth:2,frame:2,opacity:0.25,resource:"lzfocusbracket_shdw",x:1,y:1},"class":LzView},{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$classrootdepth:2,frame:2,resource:"lzfocusbracket_rsrc"},"class":LzView}],"__LZCSSTagSelectors",["view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__mv.attributes)
}}})($lzc$class__mv)
};{
Class.make("$lzc$class__mw",["$mm",function($0){
var $1=-this.classroot.offset;if($1!==this["x"]||!this.inited){
this.setAttribute("x",$1)
}},"$mn",function(){
try{
return [this.classroot,"offset"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$mo",function($0){
var $1=this.parent.height-this.height+this.classroot.offset;if($1!==this["y"]||!this.inited){
this.setAttribute("y",$1)
}},"$mp",function(){
try{
return [this.parent,"height",this,"height",this.classroot,"offset"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzView,["displayName","<anonymous extends='view'>","children",[{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$classrootdepth:2,frame:3,opacity:0.25,resource:"lzfocusbracket_shdw",x:1,y:1},"class":LzView},{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$classrootdepth:2,frame:3,resource:"lzfocusbracket_rsrc"},"class":LzView}],"__LZCSSTagSelectors",["view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__mw.attributes)
}}})($lzc$class__mw)
};{
Class.make("$lzc$class__mx",["$mq",function($0){
var $1=this.parent.width-this.width+this.classroot.offset;if($1!==this["x"]||!this.inited){
this.setAttribute("x",$1)
}},"$mr",function(){
try{
return [this.parent,"width",this,"width",this.classroot,"offset"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$ms",function($0){
var $1=this.parent.height-this.height+this.classroot.offset;if($1!==this["y"]||!this.inited){
this.setAttribute("y",$1)
}},"$mt",function(){
try{
return [this.parent,"height",this,"height",this.classroot,"offset"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzView,["displayName","<anonymous extends='view'>","children",[{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$classrootdepth:2,frame:4,opacity:0.25,resource:"lzfocusbracket_shdw",x:1,y:1},"class":LzView},{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$classrootdepth:2,frame:4,resource:"lzfocusbracket_rsrc"},"class":LzView}],"__LZCSSTagSelectors",["view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__mx.attributes)
}}})($lzc$class__mx)
};{
Class.make("$lzc$class_focusoverlay",["offset",void 0,"topleft",void 0,"topright",void 0,"bottomleft",void 0,"bottomright",void 0,"doFocus",function($0){
(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["doFocus"]||this.nextMethod(arguments.callee,"doFocus")).call(this,$0);if(this.visible)this.bounce()
},"bounce",function(){
this.animate("offset",12,this.duration/2);this.animate("offset",5,this.duration)
},"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],$lzc$class_basefocusview,["tagname","focusoverlay","children",[{attrs:{$classrootdepth:1,name:"topleft",x:new LzAlwaysExpr("$me","$mf",null),y:new LzAlwaysExpr("$mg","$mh",null)},"class":$lzc$class__mu},{attrs:{$classrootdepth:1,name:"topright",x:new LzAlwaysExpr("$mi","$mj",null),y:new LzAlwaysExpr("$mk","$ml",null)},"class":$lzc$class__mv},{attrs:{$classrootdepth:1,name:"bottomleft",x:new LzAlwaysExpr("$mm","$mn",null),y:new LzAlwaysExpr("$mo","$mp",null)},"class":$lzc$class__mw},{attrs:{$classrootdepth:1,name:"bottomright",x:new LzAlwaysExpr("$mq","$mr",null),y:new LzAlwaysExpr("$ms","$mt",null)},"class":$lzc$class__mx}],"__LZCSSTagSelectors",["focusoverlay","basefocusview","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_basefocusview.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({offset:5},$lzc$class_focusoverlay.attributes)
}}})($lzc$class_focusoverlay)
};{
Class.make("$lzc$class__componentmanager",["focusclass",void 0,"keyhandlers",void 0,"lastsdown",void 0,"lastedown",void 0,"defaults",void 0,"currentdefault",void 0,"defaultstyle",void 0,"ondefaultstyle",void 0,"init",function(){
var $0=this.focusclass;if(typeof canvas.focusclass!="undefined"){
$0=canvas.focusclass
};if($0!=null){
canvas.__focus=new (lz[$0])(canvas);canvas.__focus.reset()
};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["init"]||this.nextMethod(arguments.callee,"init")).call(this)
},"_lastkeydown",void 0,"upkeydel",void 0,"$my",function(){
return lz.Keys
},"dispatchKeyDown",function($0){
var $1=false;if($0==32){
this.lastsdown=null;var $2=lz.Focus.getFocus();if($2 instanceof lz.basecomponent){
$2.doSpaceDown();this.lastsdown=$2
};$1=true
}else if($0==13&&this.currentdefault){
this.lastedown=this.currentdefault;this.currentdefault.doEnterDown();$1=true
};if($1){
if(!this.upkeydel)this.upkeydel=new LzDelegate(this,"dispatchKeyTimer");this._lastkeydown=$0;lz.Timer.addTimer(this.upkeydel,50)
}},"dispatchKeyTimer",function($0){
if(this._lastkeydown==32&&this.lastsdown!=null){
this.lastsdown.doSpaceUp();this.lastsdown=null
}else if(this._lastkeydown==13&&this.currentdefault&&this.currentdefault==this.lastedown){
this.currentdefault.doEnterUp()
}},"findClosestDefault",function($0){
if(!this.defaults){
return null
};var $1=null;var $2=null;var $3=this.defaults;$0=$0||canvas;var $4=lz.ModeManager.getModalView();for(var $5=0;$5<$3.length;$5++){
var $6=$3[$5];if($4&&!$6.childOf($4)){
continue
};var $7=this.findCommonParent($6,$0);if($7&&(!$1||$7.nodeLevel>$1.nodeLevel)){
$1=$7;$2=$6
}};return $2
},"findCommonParent",function($0,$1){
while($0.nodeLevel>$1.nodeLevel){
$0=$0.immediateparent;if(!$0.visible)return null
};while($1.nodeLevel>$0.nodeLevel){
$1=$1.immediateparent;if(!$1.visible)return null
};while($0!=$1){
$0=$0.immediateparent;$1=$1.immediateparent;if(!$0.visible||!$1.visible)return null
};return $0
},"makeDefault",function($0){
if(!this.defaults)this.defaults=[];this.defaults.push($0);this.checkDefault(lz.Focus.getFocus())
},"unmakeDefault",function($0){
if(!this.defaults)return;for(var $1=0;$1<this.defaults.length;$1++){
if(this.defaults[$1]==$0){
this.defaults.splice($1,1);this.checkDefault(lz.Focus.getFocus());return
}}},"$mz",function(){
return lz.Focus
},"checkDefault",function($0){
if(!($0 instanceof lz.basecomponent)||!$0.doesenter){
if($0 instanceof lz.inputtext&&$0.multiline){
$0=null
}else{
$0=this.findClosestDefault($0)
}};if($0==this.currentdefault)return;if(this.currentdefault){
this.currentdefault.setAttribute("hasdefault",false)
};this.currentdefault=$0;if($0){
$0.setAttribute("hasdefault",true)
}},"$m10",function(){
return lz.ModeManager
},"$m11",function($0){
switch(arguments.length){
case 0:
$0=null;

};if(lz.Focus.getFocus()==null){
this.checkDefault(null)
}},"setDefaultStyle",function($0){
this.defaultstyle=$0;if(this.ondefaultstyle)this.ondefaultstyle.sendEvent($0)
},"getDefaultStyle",function(){
if(this.defaultstyle==null){
this.defaultstyle=new (lz.style)(canvas,{isdefault:true})
};return this.defaultstyle
},"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzNode,["tagname","_componentmanager","__LZCSSTagSelectors",["_componentmanager","node","Instance"],"attributes",new LzInheritedHash(LzNode.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",focusclass:"string",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",name:"token",nodeLevel:"number",options:"css",parent:"string",placement:"string",styleclass:"string",subnodes:"string",transition:"string","with":"string"}},$delegates:["onkeydown","dispatchKeyDown","$my","onfocus","checkDefault","$mz","onmode","$m11","$m10"],_lastkeydown:0,currentdefault:null,defaults:null,defaultstyle:null,focusclass:"focusoverlay",keyhandlers:null,lastedown:null,lastsdown:null,ondefaultstyle:LzDeclaredEvent,upkeydel:null},$lzc$class__componentmanager.attributes)
}}})($lzc$class__componentmanager)
};{
Class.make("$lzc$class_style",["isstyle",void 0,"$m12",function($0){
this.setAttribute("canvascolor",LzColorUtils.convertColor("null"))
},"canvascolor",void 0,"$lzc$set_canvascolor",function($0){
this.setCanvasColor($0)
},"$m13",function($0){
this.setAttribute("textcolor",LzColorUtils.convertColor("gray10"))
},"textcolor",void 0,"$lzc$set_textcolor",function($0){
this.setStyleAttr($0,"textcolor")
},"$m14",function($0){
this.setAttribute("textfieldcolor",LzColorUtils.convertColor("white"))
},"textfieldcolor",void 0,"$lzc$set_textfieldcolor",function($0){
this.setStyleAttr($0,"textfieldcolor")
},"$m15",function($0){
this.setAttribute("texthilitecolor",LzColorUtils.convertColor("iceblue1"))
},"texthilitecolor",void 0,"$lzc$set_texthilitecolor",function($0){
this.setStyleAttr($0,"texthilitecolor")
},"$m16",function($0){
this.setAttribute("textselectedcolor",LzColorUtils.convertColor("black"))
},"textselectedcolor",void 0,"$lzc$set_textselectedcolor",function($0){
this.setStyleAttr($0,"textselectedcolor")
},"$m17",function($0){
this.setAttribute("textdisabledcolor",LzColorUtils.convertColor("gray60"))
},"textdisabledcolor",void 0,"$lzc$set_textdisabledcolor",function($0){
this.setStyleAttr($0,"textdisabledcolor")
},"$m18",function($0){
this.setAttribute("basecolor",LzColorUtils.convertColor("offwhite"))
},"basecolor",void 0,"$lzc$set_basecolor",function($0){
this.setStyleAttr($0,"basecolor")
},"$m19",function($0){
this.setAttribute("bgcolor",LzColorUtils.convertColor("white"))
},"bgcolor",void 0,"$lzc$set_bgcolor",function($0){
this.setStyleAttr($0,"bgcolor")
},"$m1a",function($0){
this.setAttribute("hilitecolor",LzColorUtils.convertColor("iceblue4"))
},"hilitecolor",void 0,"$lzc$set_hilitecolor",function($0){
this.setStyleAttr($0,"hilitecolor")
},"$m1b",function($0){
this.setAttribute("selectedcolor",LzColorUtils.convertColor("iceblue3"))
},"selectedcolor",void 0,"$lzc$set_selectedcolor",function($0){
this.setStyleAttr($0,"selectedcolor")
},"$m1c",function($0){
this.setAttribute("disabledcolor",LzColorUtils.convertColor("gray30"))
},"disabledcolor",void 0,"$lzc$set_disabledcolor",function($0){
this.setStyleAttr($0,"disabledcolor")
},"$m1d",function($0){
this.setAttribute("bordercolor",LzColorUtils.convertColor("gray40"))
},"bordercolor",void 0,"$lzc$set_bordercolor",function($0){
this.setStyleAttr($0,"bordercolor")
},"$m1e",function($0){
this.setAttribute("bordersize",1)
},"bordersize",void 0,"$lzc$set_bordersize",function($0){
this.setStyleAttr($0,"bordersize")
},"$m1f",function($0){
var $1=this.textfieldcolor;if($1!==this["menuitembgcolor"]||!this.inited){
this.setAttribute("menuitembgcolor",$1)
}},"$m1g",function(){
try{
return [this,"textfieldcolor"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"menuitembgcolor",void 0,"isdefault",void 0,"$lzc$set_isdefault",function($0){
this._setdefault($0)
},"onisdefault",void 0,"_setdefault",function($0){
this.isdefault=$0;if(this.isdefault){
lz._componentmanager.service.setDefaultStyle(this);if(this["canvascolor"]!=null){
canvas.setAttribute("bgcolor",this.canvascolor)
}};if(this.onisdefault)this.onisdefault.sendEvent(this)
},"onstylechanged",void 0,"setStyleAttr",function($0,$1){
this[$1]=$0;if(this["on"+$1])this["on"+$1].sendEvent($1);if(this.onstylechanged)this.onstylechanged.sendEvent(this)
},"setCanvasColor",function($0){
if(this.isdefault&&$0!=null){
canvas.setAttribute("bgcolor",$0)
};this.canvascolor=$0;if(this.onstylechanged)this.onstylechanged.sendEvent(this)
},"extend",function($0){
var $1=new (lz.style)();$1.canvascolor=this.canvascolor;$1.textcolor=this.textcolor;$1.textfieldcolor=this.textfieldcolor;$1.texthilitecolor=this.texthilitecolor;$1.textselectedcolor=this.textselectedcolor;$1.textdisabledcolor=this.textdisabledcolor;$1.basecolor=this.basecolor;$1.bgcolor=this.bgcolor;$1.hilitecolor=this.hilitecolor;$1.selectedcolor=this.selectedcolor;$1.disabledcolor=this.disabledcolor;$1.bordercolor=this.bordercolor;$1.bordersize=this.bordersize;$1.menuitembgcolor=this.menuitembgcolor;$1.isdefault=this.isdefault;for(var $2 in $0){
$1[$2]=$0[$2]
};new LzDelegate($1,"_forwardstylechanged",this,"onstylechanged");return $1
},"_forwardstylechanged",function($0){
if(this.onstylechanged)this.onstylechanged.sendEvent(this)
},"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzNode,["tagname","style","__LZCSSTagSelectors",["style","node","Instance"],"attributes",new LzInheritedHash(LzNode.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{basecolor:"color",bgcolor:"color",bordercolor:"color",bordersize:"number",canvascolor:"color",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",disabledcolor:"color",hilitecolor:"color",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isdefault:"boolean",isinited:"boolean",menuitembgcolor:"color",name:"token",nodeLevel:"number",options:"css",parent:"string",placement:"string",selectedcolor:"color",styleclass:"string",subnodes:"string",textcolor:"color",textdisabledcolor:"color",textfieldcolor:"color",texthilitecolor:"color",textselectedcolor:"color",transition:"string","with":"string"}},basecolor:new LzOnceExpr("$m18",null),bgcolor:new LzOnceExpr("$m19",null),bordercolor:new LzOnceExpr("$m1d",null),bordersize:new LzOnceExpr("$m1e",null),canvascolor:new LzOnceExpr("$m12",null),disabledcolor:new LzOnceExpr("$m1c",null),hilitecolor:new LzOnceExpr("$m1a",null),isdefault:false,isstyle:true,menuitembgcolor:new LzAlwaysExpr("$m1f","$m1g",null),onisdefault:LzDeclaredEvent,onstylechanged:LzDeclaredEvent,selectedcolor:new LzOnceExpr("$m1b",null),textcolor:new LzOnceExpr("$m13",null),textdisabledcolor:new LzOnceExpr("$m17",null),textfieldcolor:new LzOnceExpr("$m14",null),texthilitecolor:new LzOnceExpr("$m15",null),textselectedcolor:new LzOnceExpr("$m16",null)},$lzc$class_style.attributes)
}}})($lzc$class_style)
};canvas.LzInstantiateView({"class":lz.script,attrs:{script:function(){
lz._componentmanager.service=new (lz._componentmanager)(canvas,null,null,true)
}}},1);{
Class.make("$lzc$class_statictext",["$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzText,["tagname","statictext","__LZCSSTagSelectors",["statictext","text","view","node","Instance"],"attributes",new LzInheritedHash(LzText.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",antiAliasType:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",cdata:"cdata",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",direction:"string",embedfonts:"boolean",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",gridFit:"string",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",hscroll:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",letterspacing:"number",lineheight:"number",loadratio:"number",mask:"string",maxhscroll:"number",maxlength:"numberExpression",maxscroll:"number",multiline:"boolean",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pattern:"string",pixellock:"boolean",placement:"string",playing:"boolean",resize:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",scroll:"number",scrollevents:"boolean",scrollheight:"number",scrollwidth:"number",selectable:"boolean",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",sharpness:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",text:"html",textalign:"string",textdecoration:"string",textindent:"number",thickness:"number",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",xscroll:"number",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression",yscroll:"number"}}},$lzc$class_statictext.attributes)
}}})($lzc$class_statictext)
};{
Class.make("$lzc$class_basecomponent",["enabled",void 0,"$lzc$set_focusable",function($0){
this._setFocusable($0)
},"_focusable",void 0,"text",void 0,"doesenter",void 0,"$lzc$set_doesenter",function($0){
this._setDoesEnter($0)
},"$m1h",function($0){
var $1=this.enabled&&(this._parentcomponent?this._parentcomponent._enabled:true);if($1!==this["_enabled"]||!this.inited){
this.setAttribute("_enabled",$1)
}},"$m1i",function(){
try{
return [this,"enabled",this,"_parentcomponent",this._parentcomponent,"_enabled"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"_enabled",void 0,"$lzc$set__enabled",function($0){
this._setEnabled($0)
},"_parentcomponent",void 0,"_initcomplete",void 0,"isdefault",void 0,"$lzc$set_isdefault",function($0){
this._setIsDefault($0)
},"onisdefault",void 0,"hasdefault",void 0,"_setEnabled",function($0){
this._enabled=$0;var $1=this._enabled&&this._focusable;if($1!=this.focusable){
this.focusable=$1;if(this.onfocusable.ready)this.onfocusable.sendEvent()
};if(this._initcomplete)this._showEnabled();if(this.on_enabled.ready)this.on_enabled.sendEvent()
},"_setFocusable",function($0){
this._focusable=$0;if(this.enabled){
this.focusable=this._focusable;if(this.onfocusable.ready)this.onfocusable.sendEvent()
}else{
this.focusable=false
}},"construct",function($0,$1){
(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["construct"]||this.nextMethod(arguments.callee,"construct")).call(this,$0,$1);var $2=this.immediateparent;while($2!=canvas){
if(lz.basecomponent["$lzsc$isa"]?lz.basecomponent.$lzsc$isa($2):$2 instanceof lz.basecomponent){
this._parentcomponent=$2;break
};$2=$2.immediateparent
}},"init",function(){
(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["init"]||this.nextMethod(arguments.callee,"init")).call(this);this._initcomplete=true;this._mousedownDel=new LzDelegate(this,"_doMousedown",this,"onmousedown");if(this.styleable){
this._usestyle()
};if(!this["_enabled"])this._showEnabled()
},"_doMousedown",function($0){},"doSpaceDown",function(){
return false
},"doSpaceUp",function(){
return false
},"doEnterDown",function(){
return false
},"doEnterUp",function(){
return false
},"_setIsDefault",function($0){
this.isdefault=this["isdefault"]==true;if(this.isdefault==$0)return;if($0){
lz._componentmanager.service.makeDefault(this)
}else{
lz._componentmanager.service.unmakeDefault(this)
};this.isdefault=$0;if(this.onisdefault.ready){
this.onisdefault.sendEvent($0)
}},"_setDoesEnter",function($0){
this.doesenter=$0;if(lz.Focus.getFocus()==this){
lz._componentmanager.service.checkDefault(this)
}},"updateDefault",function(){
lz._componentmanager.service.checkDefault(lz.Focus.getFocus())
},"$m1j",function($0){
this.setAttribute("style",null)
},"style",void 0,"$lzc$set_style",function($0){
this.styleable?this.setStyle($0):(this.style=null)
},"styleable",void 0,"_style",void 0,"onstyle",void 0,"_styledel",void 0,"_otherstyledel",void 0,"setStyle",function($0){
if(!this.styleable)return;if($0!=null&&!$0["isstyle"]){
var $1=this._style;if(!$1){
if(this._parentcomponent){
$1=this._parentcomponent.style
}else $1=lz._componentmanager.service.getDefaultStyle()
};$0=$1.extend($0)
};this._style=$0;if($0==null){
if(!this._otherstyledel){
this._otherstyledel=new LzDelegate(this,"_setstyle")
}else{
this._otherstyledel.unregisterAll()
};if(this._parentcomponent&&this._parentcomponent.styleable){
this._otherstyledel.register(this._parentcomponent,"onstyle");$0=this._parentcomponent.style
}else{
this._otherstyledel.register(lz._componentmanager.service,"ondefaultstyle");$0=lz._componentmanager.service.getDefaultStyle()
}}else if(this._otherstyledel){
this._otherstyledel.unregisterAll();this._otherstyledel=null
};this._setstyle($0)
},"_usestyle",function($0){
switch(arguments.length){
case 0:
$0=null;

};if(this._initcomplete&&this["style"]&&this.style.isinited){
this._applystyle(this.style)
}},"_setstyle",function($0){
if(!this._styledel){
this._styledel=new LzDelegate(this,"_usestyle")
}else{
this._styledel.unregisterAll()
};if($0){
this._styledel.register($0,"onstylechanged")
};this.style=$0;this._usestyle();if(this.onstyle.ready)this.onstyle.sendEvent(this.style)
},"_applystyle",function($0){},"setTint",function($0,$1,$2){
switch(arguments.length){
case 2:
$2=0;

};if($0.capabilities.colortransform){
if($1!=""&&$1!=null){
var $3=$1;var $4=$3>>16&255;var $5=$3>>8&255;var $6=$3&255;$4+=51;$5+=51;$6+=51;$4=$4/255;$5=$5/255;$6=$6/255;$0.setAttribute("colortransform",{redMultiplier:$4,greenMultiplier:$5,blueMultiplier:$6,redOffset:$2,greenOffset:$2,blueOffset:$2})
}}},"on_enabled",void 0,"_showEnabled",function(){},"acceptValue",function($0,$1){
switch(arguments.length){
case 1:
$1=null;

};this.setAttribute("text",$0)
},"presentValue",function($0){
switch(arguments.length){
case 0:
$0=null;

};return this.text
},"$lzc$presentValue_dependencies",function($0,$1,$2){
switch(arguments.length){
case 2:
$2=null;

};return [this,"text"]
},"applyData",function($0){
this.acceptValue($0)
},"updateData",function(){
return this.presentValue()
},"destroy",function(){
this.styleable=false;this._initcomplete=false;if(this["isdefault"]&&this.isdefault){
lz._componentmanager.service.unmakeDefault(this)
};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["destroy"]||this.nextMethod(arguments.callee,"destroy")).call(this)
},"toString",function(){
var $0="";var $1="";var $2="";if(this["id"]!=null)$0="  id="+this.id;if(this["name"]!=null)$1=' named "'+this.name+'"';if(this["text"]&&this.text!="")$2="  text="+this.text;return this.constructor.tagname+$1+$0+$2
},"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzView,["tagname","basecomponent","__LZCSSTagSelectors",["basecomponent","view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{_focusable:"boolean",aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",doesenter:"boolean",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",text:"html",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},_enabled:new LzAlwaysExpr("$m1h","$m1i",null),_focusable:true,_initcomplete:false,_otherstyledel:null,_parentcomponent:null,_style:null,_styledel:null,doesenter:false,enabled:true,focusable:true,hasdefault:false,on_enabled:LzDeclaredEvent,onfocusable:LzDeclaredEvent,onisdefault:LzDeclaredEvent,onstyle:LzDeclaredEvent,style:new LzOnceExpr("$m1j",null),styleable:true,text:""},$lzc$class_basecomponent.attributes)
}}})($lzc$class_basecomponent)
};Mixin.make("DrawviewShared",["$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
},"lineTo",function($0,$1){},"moveTo",function($0,$1){},"quadraticCurveTo",function($0,$1,$2,$3){},"__radtodegfactor",180/Math.PI,"arc",function($0,$1,$2,$3,$4,$5){
switch(arguments.length){
case 5:
$5=false;

};if($3==null||$4==null)return;$3=-$3;$4=-$4;var $6;if($5==false&&$4-$3>=2*Math.PI||$5==true&&$3-$4>=2*Math.PI){
$6=360
}else if($3==$4||$2==0){
$6=0
}else{
var $7=$3*this.__radtodegfactor;var $8=$4*this.__radtodegfactor;if($5){
if($8<$7){
$6=-($7-$8-360)
}else{
$6=$8-$7+360
}}else{
if($8<$7){
$6=-($7-$8+360)
}else{
$6=$8-$7-360
}};while($6<-360){
$6+=360
};while($6>360){
$6-=360
}};var $9=$0+$2*Math.cos($3);var $a=$1+$2*Math.sin(2*Math.PI-$3);this.moveTo($9,$a);this._drawArc($0,$1,$2,$6,$3*this.__radtodegfactor)
},"rect",function($0,$1,$2,$3,$4,$5,$6,$7){
switch(arguments.length){
case 4:
$4=0;
case 5:
$5=null;
case 6:
$6=null;
case 7:
$7=null;

};LzKernelUtils.rect(this,$0,$1,$2,$3,$4,$5,$6,$7)
},"oval",function($0,$1,$2,$3){
switch(arguments.length){
case 3:
$3=NaN;

};if(isNaN($3)){
$3=$2
};var $4=$2<10&&$3<10?5:8;var $5=Math.PI/($4/2);var $6=$2/Math.cos($5/2);var $7=$3/Math.cos($5/2);this.moveTo($0+$2,$1);var $8=0,$9,$a,$b,$c,$d;for(var $e=0;$e<$4;$e++){
$8+=$5;$9=$8-$5/2;$c=$0+Math.cos($9)*$6;$d=$1+Math.sin($9)*$7;$a=$0+Math.cos($8)*$2;$b=$1+Math.sin($8)*$3;this.quadraticCurveTo($c,$d,$a,$b)
};return {x:$a,y:$b}},"_drawArc",function($0,$1,$2,$3,$4,$5){
switch(arguments.length){
case 5:
$5=NaN;

};if(isNaN($5)){
$5=$2
};if(Math.abs($3)>360){
$3=360
};var $6=Math.ceil(Math.abs($3)/45);var $7,$8;if($6>0){
var $9=$3/$6;var $a=-($9/180)*Math.PI;var $b=-($4/180)*Math.PI;var $c,$d,$e;for(var $f=0;$f<$6;$f++){
$b+=$a;$c=$b-$a/2;$7=$0+Math.cos($b)*$2;$8=$1+Math.sin($b)*$5;$d=$0+Math.cos($c)*($2/Math.cos($a/2));$e=$1+Math.sin($c)*($5/Math.cos($a/2));this.quadraticCurveTo($d,$e,$7,$8)
}};return {x:$7,y:$8}},"distance",function($0,$1){
var $2=$1.x-$0.x;var $3=$1.y-$0.y;return Math.sqrt($2*$2+$3*$3)
},"intersection",function($0,$1,$2,$3){
var $4=($3.x-$2.x)*($0.y-$2.y)-($3.y-$2.y)*($0.x-$2.x);var $5=($3.y-$2.y)*($1.x-$0.x)-($3.x-$2.x)*($1.y-$0.y);if($5==0){
if($4==0){
return -1
}else{
return null
}};$4/=$5;return {x:$0.x+($1.x-$0.x)*$4,y:$0.y+($1.y-$0.y)*$4}},"midpoint",function($0,$1){
return {x:($0.x+$1.x)/2,y:($0.y+$1.y)/2}},"globalAlpha",1,"lineWidth",1,"lineCap","butt","lineJoin","miter","miterLimit",10,"strokeStyle","#000000","fillStyle","#000000"]);Class.make("$lzc$class_drawview",["__globalAlpha",null,"__lineWidth",null,"__lineCap",null,"__lineJoin",null,"__miterLimit",null,"__strokeStyle",null,"__fillStyle",null,"__pathdrawn",-1,"__lastoffset",-1,"__dirty",false,"__pathisopen",false,"_lz",lz,"__contextstates",null,"init",function(){
(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["init"]||this.nextMethod(arguments.callee,"init")).call(this);this.createContext()
},"construct",function($0,$1){
(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["construct"]||this.nextMethod(arguments.callee,"construct")).call(this,$0,$1);this.__contextstates=[]
},"$lzc$set_context",function($0){
this.beginPath();if(this.context){
this.__lineWidth=null;this.__lineCap=null;this.__lineJoin=null;this.__miterLimit=null;this.__fillStyle=null;this.__strokeStyle=null;this.__globalAlpha=null
};if($0["fillText"]&&this._lz.embed.browser.browser!=="iPad"){
this.capabilities["2dcanvastext"]=true
};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzc$set_context"]||this.nextMethod(arguments.callee,"$lzc$set_context")).call(this,$0)
},"__drawImageCnt",0,"getImage",function($0){
var $1=this._lz.drawview.images;if(!$1[$0]){
var $2=$0;if($0.indexOf("http:")!=0&&$0.indexOf("https:")!=0){
$2=this.sprite.getResourceUrls($0)[0]
};var $3=new Image();$3.src=$2;$1[$0]=$3;if($2!=$0){
$1[$2]=$3
}};return $1[$0]
},"drawImage",function(image,x,y,w,h,r){
switch(arguments.length){
case 1:
x=0;
case 2:
y=0;
case 3:
w=null;
case 4:
h=null;
case 5:
r=0;

};if(image==null){
image=this.sprite.__LZcanvas
}else if(typeof image=="string"){
image=this.getImage(image)
};if(!image)return;this.__dirty=true;if(w==null)w=image.width;if(h==null)h=image.height;var $0=image.nodeName;var $1=image&&image.nodeType==1&&$0=="IMG"||$0=="CANVAS";var $2=image&&image.complete||$0=="CANVAS";if(!$1){

}else if(!$2){
var fname="__drawImage"+this.__drawImageCnt++;this[fname]=function(){
this._lz.embed.removeEventHandler(image,"load",this,fname);delete this[fname];this.drawImage(image,x,y,w,h,r)
};this._lz.embed.attachEventHandler(image,"load",this,fname)
}else{
this.__updateFillStyle();var $3=x||y||r;if($3){
this.context.save();if(x||y){
this.context.translate(x,y)
};if(r){
this.context.rotate(r)
}};if(w==null)w=image.width;if(h==null)h=image.height;this.context.drawImage(image,0,0,w,h);if($3){
this.context.restore()
}}},"fillText",function($0,$1,$2,$3){
switch(arguments.length){
case 3:
$3=null;

};if(!this.capabilities["2dcanvastext"]){
return
};this.__styleText();this.__dirty=true;this.__updateFillStyle();if($3){
this.context.fillText($0,$1,$2,$3)
}else{
this.context.fillText($0,$1,$2)
}},"strokeText",function($0,$1,$2,$3){
switch(arguments.length){
case 3:
$3=null;

};if(!this.capabilities["2dcanvastext"]){
return
};this.__styleText();this.__dirty=true;this.__updateLineStyle();if($3){
this.context.strokeText($0,$1,$2,$3)
}else{
this.context.strokeText($0,$1,$2)
}},"measureText",function($0){
if(!this.capabilities["2dcanvastext"]){
return
};this.__styleText();return this.context.measureText($0)
},"__styleText",function(){
var $0=this.font||canvas.font;var $1=(this.fontsize||canvas.fontsize)+"px";var $2=this.fontstyle||"plain";if($2=="plain"){
var $3="normal";var $4="normal"
}else if($2=="bold"){
var $3="bold";var $4="normal"
}else if($2=="italic"){
var $3="normal";var $4="italic"
}else if($2=="bold italic"||$2=="bolditalic"){
var $3="bold";var $4="italic"
};var $5=$4+" "+$3+" "+$1+" "+$0;this.context.font=$5
},"__checkContext",function(){},"beginPath",function(){
this.__path=[[1,0,0]];this.__pathisopen=true;this.__pathdrawn=-1
},"closePath",function(){
if(this.__pathisopen){
this.__path.push([0])
};this.__pathisopen=false
},"moveTo",function($0,$1){
if(this.__pathisopen){
this.__path.push([1,$0,$1])
}},"lineTo",function($0,$1){
if(this.__pathisopen){
this.__path.push([2,$0,$1])
}},"quadraticCurveTo",function($0,$1,$2,$3){
if(this.__pathisopen){
this.__path.push([3,$0,$1,$2,$3])
}},"bezierCurveTo",function($0,$1,$2,$3,$4,$5){
if(this.__pathisopen){
this.__path.push([4,$0,$1,$2,$3,$4,$5])
}},"arc",function($0,$1,$2,$3,$4,$5){
if(this.__pathisopen){
var $6=$0+$2*Math.cos(-$3);var $7=$1+$2*Math.sin(2*Math.PI+$3);this.__path.push([1,$6,$7]);this.__path.push([5,$0,$1,$2,$3,$4,$5])
}},"fill",function(){
this.__updateFillStyle();this.__playPath(0);this.context.fill()
},"__updateFillStyle",function(){
if(this.__globalAlpha!=this.globalAlpha){
this.__globalAlpha=this.context.globalAlpha=this.globalAlpha
};if(this.__fillStyle!=this.fillStyle){
if(this.fillStyle instanceof this._lz.CanvasGradient){
this.fillStyle.__applyFillTo(this.context)
}else{
this.context.fillStyle=this._lz.ColorUtils.torgb(this.fillStyle)
};this.__fillStyle=this.fillStyle
}},"__strokeOffset",0,"__updateLineStyle",function(){
if(this.__globalAlpha!=this.globalAlpha){
this.__globalAlpha=this.context.globalAlpha=this.globalAlpha
};if(this.__lineWidth!=this.lineWidth){
this.__lineWidth=this.context.lineWidth=this.lineWidth;if(this.aliaslines){
this.__strokeOffset=this.lineWidth%2?0.5:0
}};if(this.__lineCap!=this.lineCap){
this.__lineCap=this.context.lineCap=this.lineCap
};if(this.__lineJoin!=this.lineJoin){
this.__lineJoin=this.context.lineJoin=this.lineJoin
};if(this.__miterLimit!=this.miterLimit){
this.__miterLimit=this.context.miterLimit=this.miterLimit
};if(this.__strokeStyle!=this.strokeStyle){
if(this.strokeStyle instanceof this._lz.CanvasGradient){
this.strokeStyle.__applyStrokeTo(this.context)
}else{
this.context.strokeStyle=this._lz.ColorUtils.torgb(this.strokeStyle)
};this.__strokeStyle=this.strokeStyle
}},"__playPath",function($0){
var $1=this.__path;var $2=$1.length;if($2==0)return;if(this.__pathdrawn===$2&&this.__lastoffset===$0){
return
};this.__pathdrawn=$2;this.__lastoffset=$0;if($0){
this.context.translate($0,$0)
};this.__dirty=true;this.context.beginPath();for(var $3=0;$3<$2;$3+=1){
var $4=$1[$3];switch($4[0]){
case 0:
this.context.closePath();break;
case 1:
this.context.moveTo($4[1],$4[2]);break;
case 2:
this.context.lineTo($4[1],$4[2]);break;
case 3:
this.context.quadraticCurveTo($4[1],$4[2],$4[3],$4[4]);break;
case 4:
this.context.bezierCurveTo($4[1],$4[2],$4[3],$4[4],$4[5],$4[6]);break;
case 5:
this.context.arc($4[1],$4[2],$4[3],$4[4],$4[5],$4[6]);break;

}};if($0){
this.context.translate(-$0,-$0)
}},"clipPath",function(){
this.__playPath(0);this.context.clip()
},"clipButton",function(){},"stroke",function(){
this.__updateLineStyle();this.__playPath(this.__strokeOffset);this.context.stroke()
},"clear",function(){
if(this["__dirty"]==false)return;this.__pathdrawn=-1;this.__dirty=false;this.context.clearRect(0,0,this.width,this.height)
},"clearMask",function(){},"createLinearGradient",function($0,$1,$2,$3){
return new (this._lz.CanvasGradient)(this,[$0,$1,$2,$3],false)
},"createRadialGradient",function($0,$1,$2,$3,$4,$5){
return new (this._lz.CanvasGradient)(this,[$0,$1,$2,$3,$4,$5],true)
},"rotate",function($0){
this.context.rotate($0)
},"translate",function($0,$1){
this.context.translate($0,$1)
},"scale",function($0,$1){
this.context.scale($0,$1)
},"save",function(){
this.__contextstates.push({fillStyle:this.fillStyle,strokeStyle:this.strokeStyle,globalAlpha:this.globalAlpha,lineWidth:this.lineWidth,lineCap:this.lineCap,lineJoin:this.lineJoin,miterLimit:this.miterLimit});this.context.save()
},"restore",function(){
var $0=this.__contextstates.pop();if($0){
for(var $1 in $0){
this[$1]=this["__"+$1]=$0[$1]
}};this.context.restore()
},"fillRect",function($0,$1,$2,$3){
this.__dirty=true;this.__updateFillStyle();this.context.fillRect($0,$1,$2,$3)
},"clearRect",function($0,$1,$2,$3){
this.context.clearRect($0,$1,$2,$3)
},"strokeRect",function($0,$1,$2,$3){
this.__dirty=true;this.__updateLineStyle();this.context.strokeRect($0,$1,$2,$3)
}],[DrawviewShared,LzView],["tagname","drawview","attributes",new LzInheritedHash(LzView.attributes),"images",{}]);lz[$lzc$class_drawview.tagname]=$lzc$class_drawview;Class.make("LzCanvasGradient",["__context",null,"__g",null,"$lzsc$initialize",function($0,$1,$2){
this.__context=$0;var $3=$0.context;if($2){
this.__g=$3.createRadialGradient($1[0],$1[1],$1[2],$1[3],$1[4],$1[5])
}else{
this.__g=$3.createLinearGradient($1[0],$1[1],$1[2],$1[3])
}},"addColorStop",function($0,$1){
var $2=lz.ColorUtils.torgb($1);var $3=this.__context.globalAlpha;if($3!=null&&$3!=1){
$2=this.torgba($2,$3)
};this.__g.addColorStop($0,$2)
},"torgba",function($0,$1){
if($0.indexOf("rgba")==-1){
var $2=$0.substring(4,$0.length-1).split(",");$2.push($1);return "rgba("+$2.join(",")+")"
}else{
return $0
}},"__applyFillTo",function($0){
$0.fillStyle=this.__g
},"__applyStrokeTo",function($0){
$0.strokeStyle=this.__g
}]);lz.CanvasGradient=LzCanvasGradient;{
Class.make("LzLayout",["vip",void 0,"locked",void 0,"$lzc$set_locked",function($0){
if(this.locked==$0)return;if($0){
this.lock()
}else{
this.unlock()
}},"subviews",void 0,"updateDelegate",void 0,"construct",function($0,$1){
this.locked=2;(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["construct"]||this.nextMethod(arguments.callee,"construct")).call(this,$0,$1);this.subviews=[];this.vip=this.immediateparent;if(this.vip.layouts==null){
this.vip.layouts=[this]
}else{
this.vip.layouts.push(this)
};this.updateDelegate=new LzDelegate(this,"update");if(this.immediateparent.isinited){
this.__parentInit()
}else{
new LzDelegate(this,"__parentInit",this.immediateparent,"oninit")
}},"$m1k",function($0){
new LzDelegate(this,"gotNewSubview",this.vip,"onaddsubview");new LzDelegate(this,"removeSubview",this.vip,"onremovesubview");var $1=this.vip.subviews.length;for(var $2=0;$2<$1;$2++){
this.gotNewSubview(this.vip.subviews[$2])
}},"destroy",function(){
if(this.__LZdeleted)return;this.releaseLayout(true);(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["destroy"]||this.nextMethod(arguments.callee,"destroy")).call(this)
},"reset",function($0){
switch(arguments.length){
case 0:
$0=null;

};if(this.locked){
return
};this.update($0)
},"addSubview",function($0){
var $1=$0.options["layoutAfter"];if($1){
this.__LZinsertAfter($0,$1)
}else{
this.subviews.push($0)
}},"gotNewSubview",function($0){
if(!$0.options["ignorelayout"]){
this.addSubview($0)
}},"removeSubview",function($0){
var $1=this.subviews;for(var $2=$1.length-1;$2>=0;$2--){
if($1[$2]==$0){
$1.splice($2,1);break
}};this.reset()
},"ignore",function($0){
var $1=this.subviews;for(var $2=$1.length-1;$2>=0;$2--){
if($1[$2]==$0){
$1.splice($2,1);break
}};this.reset()
},"lock",function(){
this.locked=true
},"unlock",function($0){
switch(arguments.length){
case 0:
$0=null;

};this.locked=false;this.reset()
},"__parentInit",function($0){
switch(arguments.length){
case 0:
$0=null;

};if(this.locked==2){
if(this.isinited){
this.unlock()
}else{
new LzDelegate(this,"unlock",this,"oninit")
}}},"releaseLayout",function($0){
switch(arguments.length){
case 0:
$0=null;

};if($0==null&&this.__delegates!=null)this.removeDelegates();if(this.immediateparent&&this.vip.layouts){
for(var $1=this.vip.layouts.length-1;$1>=0;$1--){
if(this.vip.layouts[$1]==this){
this.vip.layouts.splice($1,1)
}}}},"setLayoutOrder",function($0,$1){
var $2=this.subviews;for(var $3=$2.length-1;$3>=0;$3--){
if($2[$3]===$1){
$2.splice($3,1);break
}};if($3==-1){
return
};if($0=="first"){
$2.unshift($1)
}else if($0=="last"){
$2.push($1)
}else{
for(var $4=$2.length-1;$4>=0;$4--){
if($2[$4]===$0){
$2.splice($4+1,0,$1);break
}};if($4==-1){
$2.splice($3,0,$1)
}};this.reset();return
},"swapSubviewOrder",function($0,$1){
var $2=-1;var $3=-1;var $4=this.subviews;for(var $5=$4.length-1;$5>=0&&($2<0||$3<0);$5--){
if($4[$5]===$0){
$2=$5
};if($4[$5]===$1){
$3=$5
}};if($2>=0&&$3>=0){
$4[$3]=$0;$4[$2]=$1
};this.reset();return
},"__LZinsertAfter",function($0,$1){
var $2=this.subviews;for(var $3=$2.length-1;$3>=0;$3--){
if($2[$3]==$1){
$2.splice($3,0,$0)
}}},"update",function($0){
switch(arguments.length){
case 0:
$0=null;

}},"toString",function(){
return "lz.layout for view "+this.immediateparent
},"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzNode,["tagname","layout","__LZCSSTagSelectors",["layout","node","Instance"],"attributes",new LzInheritedHash(LzNode.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",name:"token",nodeLevel:"number",options:"css",parent:"string",placement:"string",styleclass:"string",subnodes:"string",transition:"string","with":"string"}},$delegates:["onconstruct","$m1k",null],locked:2},LzLayout.attributes)
}}})(LzLayout)
};{
Class.make("$lzc$class_simplelayout",["axis",void 0,"$lzc$set_axis",function($0){
this.setAxis($0)
},"inset",void 0,"$lzc$set_inset",function($0){
this.inset=$0;if(this.subviews&&this.subviews.length)this.update();if(this["oninset"])this.oninset.sendEvent(this.inset)
},"spacing",void 0,"$lzc$set_spacing",function($0){
this.spacing=$0;if(this.subviews&&this.subviews.length)this.update();if(this["onspacing"])this.onspacing.sendEvent(this.spacing)
},"setAxis",function($0){
if(this["axis"]==null||this.axis!=$0){
this.axis=$0;this.sizeAxis=$0=="x"?"width":"height";if(this.subviews.length)this.update();if(this["onaxis"])this.onaxis.sendEvent(this.axis)
}},"addSubview",function($0){
this.updateDelegate.register($0,"on"+this.sizeAxis);this.updateDelegate.register($0,"onvisible");if(!this.locked){
var $1=null;var $2=this.subviews;for(var $3=$2.length-1;$3>=0;--$3){
if($2[$3].visible){
$1=$2[$3];break
}};if($1){
var $4=$1[this.axis]+$1[this.sizeAxis]+this.spacing
}else{
var $4=this.inset
};$0.setAttribute(this.axis,$4)
};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["addSubview"]||this.nextMethod(arguments.callee,"addSubview")).call(this,$0)
},"update",function($0){
switch(arguments.length){
case 0:
$0=null;

};if(this.locked)return;var $1=this.subviews.length;var $2=this.inset;for(var $3=0;$3<$1;$3++){
var $4=this.subviews[$3];if(!$4.visible)continue;if($4[this.axis]!=$2){
$4.setAttribute(this.axis,$2)
};if($4.usegetbounds){
$4=$4.getBounds()
};$2+=this.spacing+$4[this.sizeAxis]
}},"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzLayout,["tagname","simplelayout","__LZCSSTagSelectors",["simplelayout","layout","node","Instance"],"attributes",new LzInheritedHash(LzLayout.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$attributeDescriptor:{types:{axis:"string",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",name:"token",nodeLevel:"number",options:"css",parent:"string",placement:"string",styleclass:"string",subnodes:"string",transition:"string","with":"string"}},axis:"y",inset:0,spacing:0},$lzc$class_simplelayout.attributes)
}}})($lzc$class_simplelayout)
};{
Class.make("$lzc$class_loopingtimer",["timer_resolution",void 0,"formertime",void 0,"currenttime",void 0,"timer_state",void 0,"timeevent",void 0,"reactToTimeChange",function($0){
this.timeevent.sendEvent()
},"startTimer",function(){
this.setAttribute("timer_state","COUNTING");var $0=new Date();var $1=$0.getTime();this.setAttribute("formertime",$1);this.doForTime()
},"pauseTimer",function(){
this.setAttribute("timer_state","PAUSED")
},"unpauseTimer",function(){
this.setAttribute("timer_state","COUNTING");var $0=new Date();var $1=$0.getTime();this.setAttribute("formertime",$1-this.currenttime);this.repeat()
},"stopTimer",function(){
this.setAttribute("timer_state","STOPPED")
},"resetTimer",function(){
this.setAttribute("formertime",0);this.setAttribute("currenttime",0);this.setAttribute("timer_state","READY");this.reactToTimeChange(0)
},"doForTime",function($0){
switch(arguments.length){
case 0:
$0=null;

};if(this.timer_state=="PAUSED"||this.timer_state=="STOPPED"||this.timer_state=="READY")return;var $1=new Date();var $2=$1.getTime();if(this.formertime!=0)var $3=$2-this.formertime;this.setAttribute("currenttime",$3);this.reactToTimeChange($3);this.repeat()
},"repeat",function(){
lz.Timer.addTimer(new LzDelegate(this,"doForTime"),this.timer_resolution)
},"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzView,["tagname","loopingtimer","__LZCSSTagSelectors",["loopingtimer","view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",currenttime:"number",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",formertime:"number",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",timer_resolution:"number",timer_state:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},currenttime:0,formertime:0,timeevent:LzDeclaredEvent,timer_resolution:40,timer_state:"READY"},$lzc$class_loopingtimer.attributes)
}}})($lzc$class_loopingtimer)
};{
Class.make("$lzc$class__m22",["$m20",function($0){
this.setAttribute("width",this.parent.width)
},"$m21",function($0){
this.setAttribute("height",this.parent.height)
},"reset",function(){
this.setAttribute("x",this.parent.insetleft);this.setAttribute("y",this.parent.insettop);this.setAttribute("width",this.parent.width-this.parent.insetleft-this.parent.insetright-1);this.setAttribute("height",this.parent.height-this.parent.insettop-this.parent.insetbottom-1)
},"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzView,["displayName","<anonymous extends='view'>","__LZCSSTagSelectors",["view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m22.attributes)
}}})($lzc$class__m22)
};{
Class.make("$lzc$class_roundrect",["inset",void 0,"$lzc$set_inset",function($0){
this.setInset($0)
},"oninset",void 0,"$m1l",function($0){
var $1=null;if($1!==this["insetleft"]||!this.inited){
this.setAttribute("insetleft",$1)
}},"$m1m",function(){
try{
return []
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"insetleft",void 0,"$lzc$set_insetleft",function($0){
this.setInsetLeft($0)
},"oninsetleft",void 0,"$m1n",function($0){
var $1=null;if($1!==this["insetright"]||!this.inited){
this.setAttribute("insetright",$1)
}},"$m1o",function(){
try{
return []
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"insetright",void 0,"$lzc$set_insetright",function($0){
this.setInsetRight($0)
},"oninsetright",void 0,"$m1p",function($0){
var $1=null;if($1!==this["insettop"]||!this.inited){
this.setAttribute("insettop",$1)
}},"$m1q",function(){
try{
return []
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"insettop",void 0,"$lzc$set_insettop",function($0){
this.setInsetTop($0)
},"oninsettop",void 0,"$m1r",function($0){
var $1=null;if($1!==this["insetbottom"]||!this.inited){
this.setAttribute("insetbottom",$1)
}},"$m1s",function(){
try{
return []
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"insetbottom",void 0,"$lzc$set_insetbottom",function($0){
this.setInsetBottom($0)
},"oninsetbottom",void 0,"setInset",function($0){
this.insetleft=$0;this.insetright=$0;this.insettop=$0;this.insetbottom=$0;if(this.context)this.drawStructure();if(this.oninset)this.oninset.sendEvent()
},"setInsetLeft",function($0){
if($0)this.insetleft=$0;if(this.context)this.drawStructure();if(this.oninsetleft)this.oninsetleft.sendEvent()
},"setInsetRight",function($0){
if($0)this.insetright=$0;if(this.context)this.drawStructure();if(this.oninsetright)this.oninsetright.sendEvent()
},"setInsetTop",function($0){
if($0)this.insettop=$0;if(this.context)this.drawStructure();if(this.oninsettop)this.oninsettop.sendEvent()
},"setInsetBottom",function($0){
if($0)this.insetbottom=$0;if(this.context)this.drawStructure();if(this.oninsetbottom)this.oninsetbottom.sendEvent()
},"$m1t",function($0){
if(this.context)this.drawStructure()
},"$m1u",function($0){
if(this.context)this.drawStructure()
},"borderWidth",void 0,"borderRadius",void 0,"borderColor",void 0,"borderOpacity",void 0,"$m1v",function($0){
this.setAttribute("backgroundStartColor",null)
},"backgroundStartColor",void 0,"$m1w",function($0){
this.setAttribute("backgroundStopColor",null)
},"backgroundStopColor",void 0,"backgroundStartOpacity",void 0,"backgroundStopOpacity",void 0,"backgroundGradientOrientation",void 0,"boxShadowX",void 0,"boxShadowY",void 0,"$m1x",function($0){
this.setAttribute("boxShadowColor",null)
},"boxShadowColor",void 0,"boxShadowOpacity",void 0,"$m1y",function($0){
if(this.context)this.drawStructure()
},"$m1z",function($0){
this.drawStructure();this._cache=null
},"drawStructure",function(){
if(!this.context)return;if(!this["_cache"]){
this._cache={borderWidth:this.borderWidth,borderRadius:this.borderRadius,borderColor:this.borderColor,borderOpacity:this.borderOpacity,backgroundStartColor:this.backgroundStartColor,backgroundStopColor:this.backgroundStopColor,backgroundGradientOrientation:this.backgroundGradientOrientation,backgroundStartOpacity:this.backgroundStartOpacity,backgroundStopOpacity:this.backgroundStopOpacity,boxShadowColor:this.boxShadowColor,boxShadowOpacity:this.boxShadowOpacity,boxShadowX:this.boxShadowX,boxShadowY:this.boxShadowY,insetleft:this.insetleft,insettop:this.insettop,insetright:this.insetright,insetbottom:this.insetbottom,inset:this["inset"],height:this.height,width:this.width}}else{
var $0=false;var $1=this._cache;for(var $2 in $1){
if($1[$2]!=this[$2]){
$1[$2]=this[$2];$0=true;break
}};if($0==false)return
};var $3=this.borderWidth;var $4=this.borderRadius;var $5=$3/2;var $6=$3/2;var $7=this.backgroundStartColor;var $8=this.backgroundStopColor;this.clear();if(typeof this.content!="undefined"){
this.content.reset()
};if($3!=0&&this.boxShadowColor!=null&&this.boxShadowOpacity!=0){
var $9=this.boxShadowX;var $a=this.boxShadowY;this.beginPath();this.rect($9+$5,$a+$6,this.width-$3,this.height-$3,$4);this.fillStyle=this.boxShadowColor;this.globalAlpha=this.boxShadowOpacity;this.lineWidth=this.borderWidth;this.fill();if($7==null&&$8==null)$7=$8=16777215
};this.beginPath();this.rect($5,$6,this.width-$3,this.height-$3,$4);if($7!=null||$8!=null){
var $b=this.backgroundGradientOrientation=="vertical"?this.createLinearGradient(0,$3/2,0,this.height-$3):this.createLinearGradient($3/2,0,this.width-$3,0);var $c=this.backgroundStartOpacity;var $d=this.backgroundStopOpacity;if($7==null){
$7=$8;$c=0
};if($8==null){
$8=$7;$d=0
};this.globalAlpha=$c;$b.addColorStop(0,$7);this.globalAlpha=$d;$b.addColorStop(1,$8);this.fillStyle=$b;this.fill()
};this.strokeStyle=this.borderColor;this.lineWidth=this.borderWidth;this.globalAlpha=this.borderOpacity;this.stroke()
},"content",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],$lzc$class_drawview,["tagname","roundrect","children",LzNode.mergeChildren([{attrs:{$classrootdepth:1,height:new LzOnceExpr("$m21",null),name:"content",width:new LzOnceExpr("$m20",null),x:0,y:0},"class":$lzc$class__m22},{attrs:"content","class":$lzc$class_userClassPlacement}],$lzc$class_drawview["children"]),"__LZCSSTagSelectors",["roundrect","drawview","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_drawview.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",aliaslines:"boolean",align:"string",backgroundGradientOrientation:"string",backgroundStartColor:"color",backgroundStopColor:"color",backgroundrepeat:"string",bgcolor:"color",borderColor:"color",boxShadowColor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",fillStyle:"string",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",globalAlpha:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",inset:"size",insetbottom:"size",insetleft:"size",insetright:"size",insettop:"size",isinited:"boolean",layout:"css",lineCap:"string",lineJoin:"string",lineWidth:"number",loadratio:"number",mask:"string",measuresize:"boolean",miterLimit:"number",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",strokeStyle:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$delegates:["onwidth","$m1t",null,"onheight","$m1u",null,"oninit","$m1y",null,"oncontext","$m1z",null],backgroundGradientOrientation:"vertical",backgroundStartColor:new LzOnceExpr("$m1v",null),backgroundStartOpacity:1,backgroundStopColor:new LzOnceExpr("$m1w",null),backgroundStopOpacity:1,borderColor:0,borderOpacity:1,borderRadius:5,borderWidth:1,boxShadowColor:new LzOnceExpr("$m1x",null),boxShadowOpacity:0.5,boxShadowX:5,boxShadowY:5,height:100,inset:5,insetbottom:new LzAlwaysExpr("$m1r","$m1s",null),insetleft:new LzAlwaysExpr("$m1l","$m1m",null),insetright:new LzAlwaysExpr("$m1n","$m1o",null),insettop:new LzAlwaysExpr("$m1p","$m1q",null),oninset:null,oninsetbottom:null,oninsetleft:null,oninsetright:null,oninsettop:null,width:100},$lzc$class_roundrect.attributes)
}}})($lzc$class_roundrect)
};{
Class.make("$lzc$class__m31",["$m23",function($0){
var $1=this.immediateparent.width;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m24",function(){
try{
return [this.immediateparent,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m25",function($0){
var $1=this.immediateparent.height;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m26",function(){
try{
return [this.immediateparent,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m27",function($0){
this.setAttribute("borderColor",this.parent.borderColor)
},"$m28",function($0){
this.setAttribute("borderWidth",this.parent.borderWidth)
},"$m29",function($0){
this.setAttribute("backgroundStartColor",this.parent.upStartColor)
},"$m2a",function($0){
this.setAttribute("backgroundStopColor",this.parent.upStopColor)
},"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],$lzc$class_roundrect,["displayName","<anonymous extends='roundrect'>","children",LzNode.mergeChildren([],$lzc$class_roundrect["children"]),"__LZCSSTagSelectors",["roundrect","drawview","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_roundrect.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",aliaslines:"boolean",align:"string",backgroundGradientOrientation:"string",backgroundStartColor:"color",backgroundStopColor:"color",backgroundrepeat:"string",bgcolor:"color",borderColor:"color",boxShadowColor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",fillStyle:"string",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",globalAlpha:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",inset:"size",insetbottom:"size",insetleft:"size",insetright:"size",insettop:"size",isinited:"boolean",layout:"css",lineCap:"string",lineJoin:"string",lineWidth:"number",loadratio:"number",mask:"string",measuresize:"boolean",miterLimit:"number",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",strokeStyle:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m31.attributes)
}}})($lzc$class__m31)
};{
Class.make("$lzc$class__m32",["$m2b",function($0){
var $1=this.parent.width*0.5;if($1!==this["x"]||!this.inited){
this.setAttribute("x",$1)
}},"$m2c",function(){
try{
return [this.parent,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2d",function($0){
var $1=this.classroot.text;if($1!==this["text"]||!this.inited){
this.setAttribute("text",$1)
}},"$m2e",function(){
try{
return [this.classroot,"text"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2f",function($0){
var $1=this.parent.height*0.2;if($1!==this["fontsize"]||!this.inited){
this.setAttribute("fontsize",$1)
}},"$m2g",function(){
try{
return [this.parent,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2h",function($0){
var $1=this.classroot.enabled?this.classroot.fgcolor:"#CCCCCC";if($1!==this["fgcolor"]||!this.inited){
this.setAttribute("fgcolor",$1)
}},"$m2i",function(){
try{
return [this.classroot,"enabled",this.classroot,"fgcolor"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzText,["displayName","<anonymous extends='text'>","__LZCSSTagSelectors",["text","view","node","Instance"],"attributes",new LzInheritedHash(LzText.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",antiAliasType:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",cdata:"cdata",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",direction:"string",embedfonts:"boolean",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",gridFit:"string",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",hscroll:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",letterspacing:"number",lineheight:"number",loadratio:"number",mask:"string",maxhscroll:"number",maxlength:"numberExpression",maxscroll:"number",multiline:"boolean",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pattern:"string",pixellock:"boolean",placement:"string",playing:"boolean",resize:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",scroll:"number",scrollevents:"boolean",scrollheight:"number",scrollwidth:"number",selectable:"boolean",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",sharpness:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",text:"html",textalign:"string",textdecoration:"string",textindent:"number",thickness:"number",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",xscroll:"number",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression",yscroll:"number"}}},$lzc$class__m32.attributes)
}}})($lzc$class__m32)
};{
Class.make("$lzc$class__m33",["$m2j",function($0){
var $1=this.height;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m2k",function(){
try{
return [this,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2l",function($0){
var $1=this.parent.height*0.8;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m2m",function(){
try{
return [this.parent,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2n",function($0){
var $1=this.parent.height*0.1;if($1!==this["x"]||!this.inited){
this.setAttribute("x",$1)
}},"$m2o",function(){
try{
return [this.parent,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2p",function($0){
var $1=this.parent.height*0.1;if($1!==this["y"]||!this.inited){
this.setAttribute("y",$1)
}},"$m2q",function(){
try{
return [this.parent,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2r",function($0){
var $1=this.classroot.resourcename;if($1!==this["resource"]||!this.inited){
this.setAttribute("resource",$1)
}},"$m2s",function(){
try{
return [this.classroot,"resourcename"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2t",function($0){
var $1=this.classroot.enabled?1:0.2;if($1!==this["opacity"]||!this.inited){
this.setAttribute("opacity",$1)
}},"$m2u",function(){
try{
return [this.classroot,"enabled"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzView,["displayName","<anonymous extends='view'>","__LZCSSTagSelectors",["view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m33.attributes)
}}})($lzc$class__m33)
};{
Class.make("$lzc$class_poodllbigbutton",["downStartColor",void 0,"downStopColor",void 0,"upStartColor",void 0,"upStopColor",void 0,"overStartColor",void 0,"overStopColor",void 0,"borderColor",void 0,"borderWidth",void 0,"resourcename",void 0,"_rr",void 0,"_label",void 0,"_iconview",void 0,"$m2v",function($0){
this._rr.setAttribute("backgroundStartColor",this.overStartColor);this._rr.setAttribute("backgroundStopColor",this.overStopColor);this._rr.drawStructure()
},"_applystyle",function($0){
this.setAttribute("downStartColor",$0.basecolor);this.setAttribute("downStopColor",$0.bgcolor);this.setAttribute("overStartColor",16777215);this.setAttribute("overStopColor",$0.basecolor);this.setAttribute("upStartColor",$0.hilitecolor);this.setAttribute("upStopColor",$0.basecolor);this._rr.setAttribute("backgroundStartColor",this.upStartColor);this._rr.setAttribute("backgroundStopColor",this.upStopColor);this._rr.setAttribute("bordercolor",$0.bordercolor);this._rr.setAttribute("borderwidth",$0.bordersize);this._rr.drawStructure()
},"$m2w",function($0){
this._rr.setAttribute("backgroundStartColor",this.overStartColor);this._rr.setAttribute("backgroundStopColor",this.overStopColor);this._rr.drawStructure()
},"$m2x",function($0){
this._rr.setAttribute("backgroundStartColor",this.upStartColor);this._rr.setAttribute("backgroundStopColor",this.upStopColor);this._rr.drawStructure()
},"$m2y",function($0){
this._rr.setAttribute("backgroundStartColor",this.downStartColor);this._rr.setAttribute("backgroundStopColor",this.downStopColor);this._rr.drawStructure()
},"$m2z",function($0){
if(!this["_rr"])return;this._rr.setAttribute("width",this.width);this._rr.drawStructure()
},"$m30",function($0){
if(!this["_rr"])return;this._rr.setAttribute("height",this.height);this._rr.drawStructure()
},"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],$lzc$class_basecomponent,["tagname","poodllbigbutton","children",[{attrs:{$classrootdepth:1,backgroundStartColor:new LzOnceExpr("$m29",null),backgroundStopColor:new LzOnceExpr("$m2a",null),borderColor:new LzOnceExpr("$m27",null),borderRadius:15,borderWidth:new LzOnceExpr("$m28",null),boxShadowColor:11776947,boxShadowX:0,boxShadowY:2,height:new LzAlwaysExpr("$m25","$m26",null),name:"_rr",width:new LzAlwaysExpr("$m23","$m24",null)},"class":$lzc$class__m31},{attrs:{$classrootdepth:1,clickable:false,fgcolor:new LzAlwaysExpr("$m2h","$m2i",null),fontsize:new LzAlwaysExpr("$m2f","$m2g",null),fontstyle:"bold",name:"_label",resize:true,text:new LzAlwaysExpr("$m2d","$m2e",null),valign:"middle",x:new LzAlwaysExpr("$m2b","$m2c",null)},"class":$lzc$class__m32},{attrs:{$classrootdepth:1,clickable:false,height:new LzAlwaysExpr("$m2l","$m2m",null),name:"_iconview",opacity:new LzAlwaysExpr("$m2t","$m2u",null),resource:new LzAlwaysExpr("$m2r","$m2s",null),stretches:"both",width:new LzAlwaysExpr("$m2j","$m2k",null),x:new LzAlwaysExpr("$m2n","$m2o",null),y:new LzAlwaysExpr("$m2p","$m2q",null)},"class":$lzc$class__m33}],"__LZCSSTagSelectors",["poodllbigbutton","basecomponent","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_basecomponent.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$attributeDescriptor:{types:{_focusable:"boolean",aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",borderColor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",doesenter:"boolean",downStartColor:"color",downStopColor:"color",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",overStartColor:"color",overStopColor:"color",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcename:"html",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",text:"html",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",upStartColor:"color",upStopColor:"color",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$delegates:["onmouseover","$m2v",null,"onmouseup","$m2w",null,"onmouseout","$m2x",null,"onmousedown","$m2y",null,"onwidth","$m2z",null,"onheight","$m30",null],borderColor:10066329,borderWidth:2,clickable:true,downStartColor:16777215,downStopColor:13421772,enabled:true,overStartColor:10066329,overStopColor:7829367,styleable:true,upStartColor:13421772,upStopColor:10066329},$lzc$class_poodllbigbutton.attributes)
}}})($lzc$class_poodllbigbutton)
};{
Class.make("$lzc$class_stopwatch",["countevent",void 0,"stopevent",void 0,"inittime",void 0,"progresstime",void 0,"showmilli",void 0,"showsec",void 0,"showmin",void 0,"showhour",void 0,"reactToTimeChange",function($0){
this.setAttribute("progresstime",$0);var $1=this.progresstime%(60*60*1000);var $2=$1;var $3=(this.progresstime-$2)/(60*60*1000);this.setAttribute("showhour",$3);$2=$1%(60*1000);$3=($1-$2)/(60*1000);$1=$2;this.setAttribute("showmin",$3);$2=$1%1000;$3=($1-$2)/1000;$1=$2;this.setAttribute("showsec",$3);this.setAttribute("showmilli",$1);this.countevent.sendEvent()
},"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],$lzc$class_loopingtimer,["tagname","stopwatch","__LZCSSTagSelectors",["stopwatch","loopingtimer","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_loopingtimer.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",currenttime:"number",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",formertime:"number",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",showhour:"number",showmin:"number",showsec:"number",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",timer_resolution:"number",timer_state:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},countevent:LzDeclaredEvent,inittime:0,progresstime:0,showhour:0,showmilli:0,showmin:0,showsec:0,stopevent:LzDeclaredEvent},$lzc$class_stopwatch.attributes)
}}})($lzc$class_stopwatch)
};Class.make("$lzc$class__m40",["$m36",function($0){
this.classroot.displayview.timeDisplay.format("%02d:%02d:%02d",this.showhour,this.showmin,this.showsec);this.classroot.displayview.milliDisplay.format("%03d",this.showmilli)
},"$m37",function($0){
this.resetTimer()
},"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],$lzc$class_stopwatch,["displayName","<anonymous extends='stopwatch'>","__LZCSSTagSelectors",["stopwatch","loopingtimer","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_stopwatch.attributes)]);{
Class.make("$lzc$class__m42",["$m3c",function($0){
var $1=this.classroot.usefontheight;if($1!==this["fontsize"]||!this.inited){
this.setAttribute("fontsize",$1)
}},"$m3d",function(){
try{
return [this.classroot,"usefontheight"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m3e",function($0){
var $1=this.classroot.ltimer.timer_state=="STOPPED"?16711680:0;if($1!==this["fgcolor"]||!this.inited){
this.setAttribute("fgcolor",$1)
}},"$m3f",function(){
try{
return [this.classroot.ltimer,"timer_state"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzText,["displayName","<anonymous extends='text'>","__LZCSSTagSelectors",["text","view","node","Instance"],"attributes",new LzInheritedHash(LzText.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",antiAliasType:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",cdata:"cdata",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",direction:"string",embedfonts:"boolean",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",gridFit:"string",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",hscroll:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",letterspacing:"number",lineheight:"number",loadratio:"number",mask:"string",maxhscroll:"number",maxlength:"numberExpression",maxscroll:"number",multiline:"boolean",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pattern:"string",pixellock:"boolean",placement:"string",playing:"boolean",resize:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",scroll:"number",scrollevents:"boolean",scrollheight:"number",scrollwidth:"number",selectable:"boolean",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",sharpness:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",text:"html",textalign:"string",textdecoration:"string",textindent:"number",thickness:"number",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",xscroll:"number",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression",yscroll:"number"}}},$lzc$class__m42.attributes)
}}})($lzc$class__m42)
};{
Class.make("$lzc$class__m43",["$m3g",function($0){
var $1=this.parent.timeDisplay.y+this.parent.timeDisplay.height+4;if($1!==this["y"]||!this.inited){
this.setAttribute("y",$1)
}},"$m3h",function(){
try{
return [this.parent.timeDisplay,"y",this.parent.timeDisplay,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m3i",function($0){
var $1=this.parent.timeDisplay.x+this.parent.timeDisplay.width-this.width;if($1!==this["x"]||!this.inited){
this.setAttribute("x",$1)
}},"$m3j",function(){
try{
return [this.parent.timeDisplay,"x",this.parent.timeDisplay,"width",this,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m3k",function($0){
var $1=this.parent.timeDisplay.fgcolor;if($1!==this["fgcolor"]||!this.inited){
this.setAttribute("fgcolor",$1)
}},"$m3l",function(){
try{
return [this.parent.timeDisplay,"fgcolor"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m3m",function($0){
var $1=this.classroot.usefontheight/8*3;if($1!==this["fontsize"]||!this.inited){
this.setAttribute("fontsize",$1)
}},"$m3n",function(){
try{
return [this.classroot,"usefontheight"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzText,["displayName","<anonymous extends='text'>","__LZCSSTagSelectors",["text","view","node","Instance"],"attributes",new LzInheritedHash(LzText.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",antiAliasType:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",cdata:"cdata",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",direction:"string",embedfonts:"boolean",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",gridFit:"string",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",hscroll:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",letterspacing:"number",lineheight:"number",loadratio:"number",mask:"string",maxhscroll:"number",maxlength:"numberExpression",maxscroll:"number",multiline:"boolean",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pattern:"string",pixellock:"boolean",placement:"string",playing:"boolean",resize:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",scroll:"number",scrollevents:"boolean",scrollheight:"number",scrollwidth:"number",selectable:"boolean",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",sharpness:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",text:"html",textalign:"string",textdecoration:"string",textindent:"number",thickness:"number",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",xscroll:"number",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression",yscroll:"number"}}},$lzc$class__m43.attributes)
}}})($lzc$class__m43)
};Class.make("$lzc$class__m41",["$m38",function($0){
var $1=this.parent.width;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m39",function(){
try{
return [this.parent,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m3a",function($0){
var $1=this.parent.height-this.parent.buttonsview.height-30;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m3b",function(){
try{
return [this.parent,"height",this.parent.buttonsview,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"timeDisplay",void 0,"milliDisplay",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],$lzc$class_roundrect,["displayName","<anonymous extends='roundrect'>","children",LzNode.mergeChildren([{attrs:{$classrootdepth:2,align:"center",fgcolor:new LzAlwaysExpr("$m3e","$m3f",null),fontsize:new LzAlwaysExpr("$m3c","$m3d",null),name:"timeDisplay",valign:"middle"},"class":$lzc$class__m42},{attrs:{$classrootdepth:2,fgcolor:new LzAlwaysExpr("$m3k","$m3l",null),fontsize:new LzAlwaysExpr("$m3m","$m3n",null),name:"milliDisplay",x:new LzAlwaysExpr("$m3i","$m3j",null),y:new LzAlwaysExpr("$m3g","$m3h",null)},"class":$lzc$class__m43}],$lzc$class_roundrect["children"]),"__LZCSSTagSelectors",["roundrect","drawview","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_roundrect.attributes)]);Class.make("$lzc$class__m45",["$m3o",function($0){
var $1=this.classroot.ltimer.timer_state=="READY"?"play_button":(this.classroot.ltimer.timer_state=="PAUSED"?"play_button":"stop_button");if($1!==this["resourcename"]||!this.inited){
this.setAttribute("resourcename",$1)
}},"$m3p",function(){
try{
return [this.classroot.ltimer,"timer_state"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m3q",function($0){
var $1=this.parent.parent.displayview.width*0.5-5;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m3r",function(){
try{
return [this.parent.parent.displayview,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m3s",function($0){
var $1=this.classroot.ltimer.timer_state=="READY"?"START":(this.classroot.ltimer.timer_state=="PAUSED"?"CONT.":"STOP");if($1!==this["text"]||!this.inited){
this.setAttribute("text",$1)
}},"$m3t",function(){
try{
return [this.classroot.ltimer,"timer_state"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m3u",function($0){
switch(this.classroot.ltimer.timer_state){
case "READY":
this.parent.dostart();break;
case "COUNTING":
this.parent.dopause();break;
case "PAUSED":
this.parent.dounpause();break;

}},"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],$lzc$class_poodllbigbutton,["displayName","<anonymous extends='poodllbigbutton'>","children",LzNode.mergeChildren([],$lzc$class_poodllbigbutton["children"]),"__LZCSSTagSelectors",["poodllbigbutton","basecomponent","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_poodllbigbutton.attributes)]);Class.make("$lzc$class__m46",["$m3v",function($0){
var $1=this.parent.parent.displayview.width*0.5-5;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m3w",function(){
try{
return [this.parent.parent.displayview,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m3x",function($0){
var $1=this.classroot.ltimer.timer_state!="READY";if($1!==this["enabled"]||!this.inited){
this.setAttribute("enabled",$1)
}},"$m3y",function(){
try{
return [this.classroot.ltimer,"timer_state"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m3z",function($0){
if(this.enabled){
this.parent.doreset()
}},"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],$lzc$class_poodllbigbutton,["displayName","<anonymous extends='poodllbigbutton'>","children",LzNode.mergeChildren([],$lzc$class_poodllbigbutton["children"]),"__LZCSSTagSelectors",["poodllbigbutton","basecomponent","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_poodllbigbutton.attributes)]);{
Class.make("$lzc$class__m44",["dostart",function(){
this.classroot.ltimer.startTimer()
},"dopause",function(){
this.classroot.ltimer.pauseTimer()
},"dounpause",function(){
this.classroot.ltimer.unpauseTimer()
},"doreset",function(){
this.classroot.ltimer.resetTimer();this.classroot.ltimer.setAttribute("progresstime",0)
},"actionButton",void 0,"resetButton",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzView,["displayName","<anonymous extends='view'>","children",[{attrs:{$classrootdepth:2,axis:"x",spacing:10},"class":$lzc$class_simplelayout},{attrs:{$classrootdepth:2,$delegates:["onclick","$m3u",null],clickable:true,height:40,name:"actionButton",resourcename:new LzAlwaysExpr("$m3o","$m3p",null),text:new LzAlwaysExpr("$m3s","$m3t",null),width:new LzAlwaysExpr("$m3q","$m3r",null)},"class":$lzc$class__m45},{attrs:{$classrootdepth:2,$delegates:["onclick","$m3z",null],clickable:true,enabled:new LzAlwaysExpr("$m3x","$m3y",null),height:40,name:"resetButton",resourcename:"reset_button",text:"Reset",width:new LzAlwaysExpr("$m3v","$m3w",null)},"class":$lzc$class__m46}],"__LZCSSTagSelectors",["view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m44.attributes)
}}})($lzc$class__m44)
};{
Class.make("$lzc$class_stopwatchview",["fontheight",void 0,"$m34",function($0){
var $1=this.fontheight==null||this.fontheight==0?this.height*0.3:this.fontheight;if($1!==this["usefontheight"]||!this.inited){
this.setAttribute("usefontheight",$1)
}},"$m35",function(){
try{
return [this,"fontheight",this,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"usefontheight",void 0,"red5url",void 0,"mename",void 0,"courseid",void 0,"uniquename",void 0,"actionbuttondel",void 0,"resetbuttondel",void 0,"mode",void 0,"headeridtag",void 0,"ltimer",void 0,"displayview",void 0,"buttonsview",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],LzView,["tagname","stopwatchview","children",[{attrs:{$classrootdepth:1,$delegates:["countevent","$m36",null,"oninit","$m37",null],name:"ltimer"},"class":$lzc$class__m40},{attrs:{$classrootdepth:1,axis:"y",spacing:10},"class":$lzc$class_simplelayout},{attrs:{$classrootdepth:1,align:"center",backgroundStartColor:15658734,backgroundStopColor:14540253,borderColor:255,borderWidth:5,height:new LzAlwaysExpr("$m3a","$m3b",null),milliDisplay:void 0,name:"displayview",timeDisplay:void 0,width:new LzAlwaysExpr("$m38","$m39",null)},"class":$lzc$class__m41},{attrs:{$classrootdepth:1,actionButton:void 0,align:"center",name:"buttonsview",resetButton:void 0},"class":$lzc$class__m44}],"__LZCSSTagSelectors",["stopwatchview","view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},headeridtag:"slaveview01",usefontheight:new LzAlwaysExpr("$m34","$m35",null)},$lzc$class_stopwatchview.attributes)
}}})($lzc$class_stopwatchview)
};Class.make("$lzc$class__m4n",["$m47",function($0){
var $1=canvas.mode;if($1!==this["mode"]||!this.inited){
this.setAttribute("mode",$1)
}},"$m48",function(){
try{
return [canvas,"mode"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m49",function($0){
var $1=canvas.red5url;if($1!==this["red5url"]||!this.inited){
this.setAttribute("red5url",$1)
}},"$m4a",function(){
try{
return [canvas,"red5url"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m4b",function($0){
var $1=canvas.mename;if($1!==this["mename"]||!this.inited){
this.setAttribute("mename",$1)
}},"$m4c",function(){
try{
return [canvas,"mename"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m4d",function($0){
var $1=canvas.courseid;if($1!==this["courseid"]||!this.inited){
this.setAttribute("courseid",$1)
}},"$m4e",function(){
try{
return [canvas,"courseid"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m4f",function($0){
var $1=canvas.uniquename;if($1!==this["uniquename"]||!this.inited){
this.setAttribute("uniquename",$1)
}},"$m4g",function(){
try{
return [canvas,"uniquename"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m4h",function($0){
var $1=canvas.fontheight;if($1!==this["fontheight"]||!this.inited){
this.setAttribute("fontheight",$1)
}},"$m4i",function(){
try{
return [canvas,"fontheight"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m4j",function($0){
var $1=this.parent.width;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m4k",function(){
try{
return [this.parent,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m4l",function($0){
var $1=this.parent.height;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m4m",function(){
try{
return [this.parent,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$lzsc$initialize",function($0,$1,$2,$3){
switch(arguments.length){
case 0:
$0=null;
case 1:
$1=null;
case 2:
$2=null;
case 3:
$3=false;

};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzsc$initialize"]||this.nextMethod(arguments.callee,"$lzsc$initialize")).call(this,$0,$1,$2,$3)
}],$lzc$class_stopwatchview,["displayName","<anonymous extends='stopwatchview'>","children",LzNode.mergeChildren([],$lzc$class_stopwatchview["children"]),"__LZCSSTagSelectors",["stopwatchview","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_stopwatchview.attributes)]);canvas.LzInstantiateView({attrs:{$lzc$bind_name:function($0,$1){
switch(arguments.length){
case 1:
$1=true;

};if($1){
thestopwatch=$0
}else if(thestopwatch===$0){
thestopwatch=null
}},courseid:new LzAlwaysExpr("$m4d","$m4e",null),fontheight:new LzAlwaysExpr("$m4h","$m4i",null),height:new LzAlwaysExpr("$m4l","$m4m",null),mename:new LzAlwaysExpr("$m4b","$m4c",null),mode:new LzAlwaysExpr("$m47","$m48",null),name:"thestopwatch",red5url:new LzAlwaysExpr("$m49","$m4a",null),uniquename:new LzAlwaysExpr("$m4f","$m4g",null),width:new LzAlwaysExpr("$m4j","$m4k",null)},"class":$lzc$class__m4n},19);lz["basefocusview"]=$lzc$class_basefocusview;lz["focusoverlay"]=$lzc$class_focusoverlay;lz["_componentmanager"]=$lzc$class__componentmanager;lz["style"]=$lzc$class_style;lz["statictext"]=$lzc$class_statictext;lz["basecomponent"]=$lzc$class_basecomponent;lz["layout"]=LzLayout;lz["simplelayout"]=$lzc$class_simplelayout;lz["loopingtimer"]=$lzc$class_loopingtimer;lz["roundrect"]=$lzc$class_roundrect;lz["poodllbigbutton"]=$lzc$class_poodllbigbutton;lz["stopwatch"]=$lzc$class_stopwatch;lz["stopwatchview"]=$lzc$class_stopwatchview;canvas.initDone();