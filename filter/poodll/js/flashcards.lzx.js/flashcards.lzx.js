LzResourceLibrary.lzfocusbracket_rsrc={ptype:"sr",frames:['lps/components/lz/resources/focus/focus_top_lft.png','lps/components/lz/resources/focus/focus_top_rt.png','lps/components/lz/resources/focus/focus_bot_lft.png','lps/components/lz/resources/focus/focus_bot_rt.png'],width:7,height:7,sprite:'lps/components/lz/resources/focus/focus_top_lft.sprite.png',spriteoffset:0};LzResourceLibrary.lzfocusbracket_shdw={ptype:"sr",frames:['lps/components/lz/resources/focus/focus_top_lft_shdw.png','lps/components/lz/resources/focus/focus_top_rt_shdw.png','lps/components/lz/resources/focus/focus_bot_lft_shdw.png','lps/components/lz/resources/focus/focus_bot_rt_shdw.png'],width:9,height:9,sprite:'lps/components/lz/resources/focus/focus_top_lft_shdw.sprite.png',spriteoffset:7};LzResourceLibrary.lzbutton_face_rsc={ptype:"sr",frames:['lps/components/lz/resources/button/simpleface_up.png','lps/components/lz/resources/button/simpleface_mo.png','lps/components/lz/resources/button/simpleface_dn.png','lps/components/lz/resources/button/autoPng/simpleface_dsbl.png'],width:2,height:18,sprite:'lps/components/lz/resources/button/simpleface_up.sprite.png',spriteoffset:16};LzResourceLibrary.lzbutton_bezel_inner_rsc={ptype:"sr",frames:['lps/components/lz/resources/autoPng/bezel_inner_up.png','lps/components/lz/resources/autoPng/bezel_inner_up.png','lps/components/lz/resources/autoPng/bezel_inner_dn.png','lps/components/lz/resources/autoPng/outline_dsbl.png'],width:500,height:500,sprite:'lps/components/lz/resources/autoPng/bezel_inner_up.sprite.png',spriteoffset:34};LzResourceLibrary.lzbutton_bezel_outer_rsc={ptype:"sr",frames:['lps/components/lz/resources/autoPng/bezel_outer_up.png','lps/components/lz/resources/autoPng/bezel_outer_up.png','lps/components/lz/resources/autoPng/bezel_outer_dn.png','lps/components/lz/resources/autoPng/transparent.png','lps/components/lz/resources/autoPng/default_outline.png'],width:500,height:500,sprite:'lps/components/lz/resources/autoPng/bezel_outer_up.sprite.png',spriteoffset:534};LzResourceLibrary.__allcss={path:'usr/local/red5/webapps/openlaszlo/my-apps/flashcards/flashcards.sprite.png'};;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;var flashcards=null;{
Class.make("$lzc$class__m8",["$m4",function($0){
this.setAttribute("cardset",lz.Browser.getInitArg("cardset"))
},"cardset",void 0,"$m5",function($0){
this.setAttribute("cardwidth",lz.Browser.getInitArg("cardwidth"))
},"cardwidth",void 0,"$m6",function($0){
this.setAttribute("cardheight",lz.Browser.getInitArg("cardheight"))
},"cardheight",void 0,"$m7",function($0){
this.setAttribute("randomize",lz.Browser.getInitArg("randomize")=="yes")
},"randomize",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",accessible:"boolean",align:"string",allowfullscreen:"boolean",appbuilddate:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",compileroptions:"string",contextmenu:"string",cornerradius:"string",cursor:"token",dataloadtimeout:"numberExpression",datapath:"string",datasets:"string",debug:"boolean",defaultdataprovider:"string",defaultplacement:"string",embedfonts:"boolean",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framerate:"number",framesloadratio:"number",fullscreen:"boolean",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",history:"boolean",httpdataprovider:"string",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",lpsbuild:"string",lpsbuilddate:"string",lpsrelease:"string",lpsversion:"string",mask:"string",mediaerrortimeout:"numberExpression",medialoadtimeout:"numberExpression",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",percentcreated:"number",pixellock:"boolean",placement:"string",playing:"boolean",proxied:"inheritableBoolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",runtime:"string",screenorientation:"boolean",scriptlimits:"css",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",title:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m8.attributes)
}}})($lzc$class__m8)
};canvas=new $lzc$class__m8(null,{__LZproxied:"false",appbuilddate:"2011-12-01T15:33:45Z",bgcolor:16777215,cardheight:new LzOnceExpr("$m6",null),cardset:new LzOnceExpr("$m4",null),cardwidth:new LzOnceExpr("$m5",null),embedfonts:true,font:"Verdana,Vera,sans-serif",fontsize:11,fontstyle:"plain",height:"100%",lpsbuild:"trunk@19126 (19126)",lpsbuilddate:"2011-04-30T08:09:13Z",lpsrelease:"Latest",lpsversion:"5.0.x",randomize:new LzOnceExpr("$m7",null),runtime:"dhtml",width:"100%"});Mixin.make("DrawviewShared",["$lzsc$initialize",function($0,$1,$2,$3){
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
}]);lz.CanvasGradient=LzCanvasGradient;lz.colors.offwhite=15921906;lz.colors.gray10=1710618;lz.colors.gray20=3355443;lz.colors.gray30=5066061;lz.colors.gray40=6710886;lz.colors.gray50=8355711;lz.colors.gray60=10066329;lz.colors.gray70=11776947;lz.colors.gray80=13421772;lz.colors.gray90=15066597;lz.colors.iceblue1=3298963;lz.colors.iceblue2=5472718;lz.colors.iceblue3=12240085;lz.colors.iceblue4=14017779;lz.colors.iceblue5=15659509;lz.colors.palegreen1=4290113;lz.colors.palegreen2=11785139;lz.colors.palegreen3=12637341;lz.colors.palegreen4=13888170;lz.colors.palegreen5=15725032;lz.colors.gold1=9331721;lz.colors.gold2=13349195;lz.colors.gold3=15126388;lz.colors.gold4=16311446;lz.colors.sand1=13944481;lz.colors.sand2=14276546;lz.colors.sand3=15920859;lz.colors.sand4=15986401;lz.colors.ltpurple1=6575768;lz.colors.ltpurple2=12038353;lz.colors.ltpurple3=13353453;lz.colors.ltpurple4=15329264;lz.colors.grayblue=12501704;lz.colors.graygreen=12635328;lz.colors.graypurple=10460593;lz.colors.ltblue=14540287;lz.colors.ltgreen=14548957;{
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
}},"$m9",function(){
return lz.Focus
},"$ma",function($0){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$delegates:["onstop","stopanim",null,"onfocus","$ma","$m9"],_animatorcounter:0,_delayfadeoutDL:null,_dofadeout:false,_heightdel:null,_nexttarget:null,_onstopdel:null,_widthdel:null,_xydelegate:null,active:false,duration:400,initstage:"late",onactive:LzDeclaredEvent,ontarget:LzDeclaredEvent,options:{ignorelayout:true},target:null,visible:false},$lzc$class_basefocusview.attributes)
}}})($lzc$class_basefocusview)
};{
Class.make("$lzc$class__mr",["$mb",function($0){
var $1=-this.classroot.offset;if($1!==this["x"]||!this.inited){
this.setAttribute("x",$1)
}},"$mc",function(){
try{
return [this.classroot,"offset"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$md",function($0){
var $1=-this.classroot.offset;if($1!==this["y"]||!this.inited){
this.setAttribute("y",$1)
}},"$me",function(){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__mr.attributes)
}}})($lzc$class__mr)
};{
Class.make("$lzc$class__ms",["$mf",function($0){
var $1=this.parent.width-this.width+this.classroot.offset;if($1!==this["x"]||!this.inited){
this.setAttribute("x",$1)
}},"$mg",function(){
try{
return [this.parent,"width",this,"width",this.classroot,"offset"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$mh",function($0){
var $1=-this.classroot.offset;if($1!==this["y"]||!this.inited){
this.setAttribute("y",$1)
}},"$mi",function(){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__ms.attributes)
}}})($lzc$class__ms)
};{
Class.make("$lzc$class__mt",["$mj",function($0){
var $1=-this.classroot.offset;if($1!==this["x"]||!this.inited){
this.setAttribute("x",$1)
}},"$mk",function(){
try{
return [this.classroot,"offset"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$ml",function($0){
var $1=this.parent.height-this.height+this.classroot.offset;if($1!==this["y"]||!this.inited){
this.setAttribute("y",$1)
}},"$mm",function(){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__mt.attributes)
}}})($lzc$class__mt)
};{
Class.make("$lzc$class__mu",["$mn",function($0){
var $1=this.parent.width-this.width+this.classroot.offset;if($1!==this["x"]||!this.inited){
this.setAttribute("x",$1)
}},"$mo",function(){
try{
return [this.parent,"width",this,"width",this.classroot,"offset"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$mp",function($0){
var $1=this.parent.height-this.height+this.classroot.offset;if($1!==this["y"]||!this.inited){
this.setAttribute("y",$1)
}},"$mq",function(){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__mu.attributes)
}}})($lzc$class__mu)
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
}],$lzc$class_basefocusview,["tagname","focusoverlay","children",[{attrs:{$classrootdepth:1,name:"topleft",x:new LzAlwaysExpr("$mb","$mc",null),y:new LzAlwaysExpr("$md","$me",null)},"class":$lzc$class__mr},{attrs:{$classrootdepth:1,name:"topright",x:new LzAlwaysExpr("$mf","$mg",null),y:new LzAlwaysExpr("$mh","$mi",null)},"class":$lzc$class__ms},{attrs:{$classrootdepth:1,name:"bottomleft",x:new LzAlwaysExpr("$mj","$mk",null),y:new LzAlwaysExpr("$ml","$mm",null)},"class":$lzc$class__mt},{attrs:{$classrootdepth:1,name:"bottomright",x:new LzAlwaysExpr("$mn","$mo",null),y:new LzAlwaysExpr("$mp","$mq",null)},"class":$lzc$class__mu}],"__LZCSSTagSelectors",["focusoverlay","basefocusview","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_basefocusview.attributes)]);(function($0){
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
},"_lastkeydown",void 0,"upkeydel",void 0,"$mv",function(){
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
}}},"$mw",function(){
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
}},"$mx",function(){
return lz.ModeManager
},"$my",function($0){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",focusclass:"string",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",name:"token",nodeLevel:"number",options:"css",parent:"string",placement:"string",styleclass:"string",subnodes:"string",transition:"string","with":"string"}},$delegates:["onkeydown","dispatchKeyDown","$mv","onfocus","checkDefault","$mw","onmode","$my","$mx"],_lastkeydown:0,currentdefault:null,defaults:null,defaultstyle:null,focusclass:"focusoverlay",keyhandlers:null,lastedown:null,lastsdown:null,ondefaultstyle:LzDeclaredEvent,upkeydel:null},$lzc$class__componentmanager.attributes)
}}})($lzc$class__componentmanager)
};{
Class.make("$lzc$class_style",["isstyle",void 0,"$mz",function($0){
this.setAttribute("canvascolor",LzColorUtils.convertColor("null"))
},"canvascolor",void 0,"$lzc$set_canvascolor",function($0){
this.setCanvasColor($0)
},"$m10",function($0){
this.setAttribute("textcolor",LzColorUtils.convertColor("gray10"))
},"textcolor",void 0,"$lzc$set_textcolor",function($0){
this.setStyleAttr($0,"textcolor")
},"$m11",function($0){
this.setAttribute("textfieldcolor",LzColorUtils.convertColor("white"))
},"textfieldcolor",void 0,"$lzc$set_textfieldcolor",function($0){
this.setStyleAttr($0,"textfieldcolor")
},"$m12",function($0){
this.setAttribute("texthilitecolor",LzColorUtils.convertColor("iceblue1"))
},"texthilitecolor",void 0,"$lzc$set_texthilitecolor",function($0){
this.setStyleAttr($0,"texthilitecolor")
},"$m13",function($0){
this.setAttribute("textselectedcolor",LzColorUtils.convertColor("black"))
},"textselectedcolor",void 0,"$lzc$set_textselectedcolor",function($0){
this.setStyleAttr($0,"textselectedcolor")
},"$m14",function($0){
this.setAttribute("textdisabledcolor",LzColorUtils.convertColor("gray60"))
},"textdisabledcolor",void 0,"$lzc$set_textdisabledcolor",function($0){
this.setStyleAttr($0,"textdisabledcolor")
},"$m15",function($0){
this.setAttribute("basecolor",LzColorUtils.convertColor("offwhite"))
},"basecolor",void 0,"$lzc$set_basecolor",function($0){
this.setStyleAttr($0,"basecolor")
},"$m16",function($0){
this.setAttribute("bgcolor",LzColorUtils.convertColor("white"))
},"bgcolor",void 0,"$lzc$set_bgcolor",function($0){
this.setStyleAttr($0,"bgcolor")
},"$m17",function($0){
this.setAttribute("hilitecolor",LzColorUtils.convertColor("iceblue4"))
},"hilitecolor",void 0,"$lzc$set_hilitecolor",function($0){
this.setStyleAttr($0,"hilitecolor")
},"$m18",function($0){
this.setAttribute("selectedcolor",LzColorUtils.convertColor("iceblue3"))
},"selectedcolor",void 0,"$lzc$set_selectedcolor",function($0){
this.setStyleAttr($0,"selectedcolor")
},"$m19",function($0){
this.setAttribute("disabledcolor",LzColorUtils.convertColor("gray30"))
},"disabledcolor",void 0,"$lzc$set_disabledcolor",function($0){
this.setStyleAttr($0,"disabledcolor")
},"$m1a",function($0){
this.setAttribute("bordercolor",LzColorUtils.convertColor("gray40"))
},"bordercolor",void 0,"$lzc$set_bordercolor",function($0){
this.setStyleAttr($0,"bordercolor")
},"$m1b",function($0){
this.setAttribute("bordersize",1)
},"bordersize",void 0,"$lzc$set_bordersize",function($0){
this.setStyleAttr($0,"bordersize")
},"$m1c",function($0){
var $1=this.textfieldcolor;if($1!==this["menuitembgcolor"]||!this.inited){
this.setAttribute("menuitembgcolor",$1)
}},"$m1d",function(){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{basecolor:"color",bgcolor:"color",bordercolor:"color",bordersize:"number",canvascolor:"color",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",disabledcolor:"color",hilitecolor:"color",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isdefault:"boolean",isinited:"boolean",menuitembgcolor:"color",name:"token",nodeLevel:"number",options:"css",parent:"string",placement:"string",selectedcolor:"color",styleclass:"string",subnodes:"string",textcolor:"color",textdisabledcolor:"color",textfieldcolor:"color",texthilitecolor:"color",textselectedcolor:"color",transition:"string","with":"string"}},basecolor:new LzOnceExpr("$m15",null),bgcolor:new LzOnceExpr("$m16",null),bordercolor:new LzOnceExpr("$m1a",null),bordersize:new LzOnceExpr("$m1b",null),canvascolor:new LzOnceExpr("$mz",null),disabledcolor:new LzOnceExpr("$m19",null),hilitecolor:new LzOnceExpr("$m17",null),isdefault:false,isstyle:true,menuitembgcolor:new LzAlwaysExpr("$m1c","$m1d",null),onisdefault:LzDeclaredEvent,onstylechanged:LzDeclaredEvent,selectedcolor:new LzOnceExpr("$m18",null),textcolor:new LzOnceExpr("$m10",null),textdisabledcolor:new LzOnceExpr("$m14",null),textfieldcolor:new LzOnceExpr("$m11",null),texthilitecolor:new LzOnceExpr("$m12",null),textselectedcolor:new LzOnceExpr("$m13",null)},$lzc$class_style.attributes)
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
},"$m1e",function($0){
var $1=this.enabled&&(this._parentcomponent?this._parentcomponent._enabled:true);if($1!==this["_enabled"]||!this.inited){
this.setAttribute("_enabled",$1)
}},"$m1f",function(){
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
},"$m1g",function($0){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{_focusable:"boolean",aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",doesenter:"boolean",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",text:"html",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},_enabled:new LzAlwaysExpr("$m1e","$m1f",null),_focusable:true,_initcomplete:false,_otherstyledel:null,_parentcomponent:null,_style:null,_styledel:null,doesenter:false,enabled:true,focusable:true,hasdefault:false,on_enabled:LzDeclaredEvent,onfocusable:LzDeclaredEvent,onisdefault:LzDeclaredEvent,onstyle:LzDeclaredEvent,style:new LzOnceExpr("$m1g",null),styleable:true,text:""},$lzc$class_basecomponent.attributes)
}}})($lzc$class_basecomponent)
};{
Class.make("$lzc$class_basebutton",["normalResourceNumber",void 0,"overResourceNumber",void 0,"downResourceNumber",void 0,"disabledResourceNumber",void 0,"$m1h",function($0){
this.setAttribute("maxframes",this.totalframes)
},"maxframes",void 0,"resourceviewcount",void 0,"$lzc$set_resourceviewcount",function($0){
this.setResourceViewCount($0)
},"respondtomouseout",void 0,"$m1i",function($0){
this.setAttribute("reference",this)
},"reference",void 0,"$lzc$set_reference",function($0){
this.setreference($0)
},"onresourceviewcount",void 0,"_msdown",void 0,"_msin",void 0,"setResourceViewCount",function($0){
this.resourceviewcount=$0;if(this._initcomplete){
if($0>0){
if(this.subviews){
this.maxframes=this.subviews[0].totalframes;if(this.onresourceviewcount){
this.onresourceviewcount.sendEvent()
}}}}},"_callShow",function(){
if(this._msdown&&this._msin&&this.maxframes>=this.downResourceNumber){
this.showDown()
}else if(this._msin&&this.maxframes>=this.overResourceNumber){
this.showOver()
}else this.showUp()
},"$m1j",function(){
return lz.ModeManager
},"$m1k",function($0){
if($0&&(this._msdown||this._msin)&&!this.childOf($0)){
this._msdown=false;this._msin=false;this._callShow()
}},"$lzc$set_frame",function($0){
if(this.resourceviewcount>0){
for(var $1=0;$1<this.resourceviewcount;$1++){
this.subviews[$1].setAttribute("frame",$0)
}}else{
(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzc$set_frame"]||this.nextMethod(arguments.callee,"$lzc$set_frame")).call(this,$0)
}},"doSpaceDown",function(){
if(this._enabled){
this.showDown()
}},"doSpaceUp",function(){
if(this._enabled){
this.onclick.sendEvent();this.showUp()
}},"doEnterDown",function(){
if(this._enabled){
this.showDown()
}},"doEnterUp",function(){
if(this._enabled){
if(this.onclick){
this.onclick.sendEvent()
};this.showUp()
}},"$m1l",function($0){
if(this.isinited){
this.maxframes=this.totalframes;this._callShow()
}},"init",function(){
(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["init"]||this.nextMethod(arguments.callee,"init")).call(this);this.setResourceViewCount(this.resourceviewcount);this._callShow()
},"$m1m",function($0){
this.setAttribute("_msin",true);this._callShow()
},"$m1n",function($0){
this.setAttribute("_msin",false);this._callShow()
},"$m1o",function($0){
this.setAttribute("_msdown",true);this._callShow()
},"$m1p",function($0){
this.setAttribute("_msdown",false);this._callShow()
},"_showEnabled",function(){
this.reference.setAttribute("clickable",this._enabled);this.showUp()
},"showDown",function($0){
switch(arguments.length){
case 0:
$0=null;

};this.setAttribute("frame",this.downResourceNumber)
},"showUp",function($0){
switch(arguments.length){
case 0:
$0=null;

};if(!this._enabled&&this.disabledResourceNumber){
this.setAttribute("frame",this.disabledResourceNumber)
}else{
this.setAttribute("frame",this.normalResourceNumber)
}},"showOver",function($0){
switch(arguments.length){
case 0:
$0=null;

};this.setAttribute("frame",this.overResourceNumber)
},"setreference",function($0){
this.reference=$0;if($0!=this)this.setAttribute("clickable",false)
},"_applystyle",function($0){
this.setTint(this,$0.basecolor)
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
}],$lzc$class_basecomponent,["tagname","basebutton","__LZCSSTagSelectors",["basebutton","basecomponent","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_basecomponent.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$attributeDescriptor:{types:{_focusable:"boolean",aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",disabledResourceNumber:"number",doesenter:"boolean",downResourceNumber:"number",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",maxframes:"number",name:"token",nodeLevel:"number",normalResourceNumber:"number",opacity:"number",options:"css",overResourceNumber:"number",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourceviewcount:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",text:"html",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$delegates:["onmode","$m1k","$m1j","ontotalframes","$m1l",null,"onmouseover","$m1m",null,"onmouseout","$m1n",null,"onmousedown","$m1o",null,"onmouseup","$m1p",null],_msdown:false,_msin:false,clickable:true,disabledResourceNumber:4,downResourceNumber:3,focusable:false,maxframes:new LzOnceExpr("$m1h",null),normalResourceNumber:1,onclick:LzDeclaredEvent,onresourceviewcount:LzDeclaredEvent,overResourceNumber:2,reference:new LzOnceExpr("$m1i",null),resourceviewcount:0,respondtomouseout:true,styleable:false},$lzc$class_basebutton.attributes)
}}})($lzc$class_basebutton)
};{
Class.make("$lzc$class_swatchview",["ctransform",void 0,"color",void 0,"construct",function($0,$1){
(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["construct"]||this.nextMethod(arguments.callee,"construct")).call(this,$0,$1);this.capabilities=new LzInheritedHash(this.capabilities);this.capabilities.colortransform=true;if($1["width"]==null){
$1["width"]=this.immediateparent.width
};if($1["height"]==null){
$1["height"]=this.immediateparent.height
};if($1["fgcolor"]==null&&$1["bgcolor"]==null){
$1["fgcolor"]=16777215
}},"$lzc$set_fgcolor",function($0){
this.setAttribute("bgcolor",$0)
},"$lzc$set_bgcolor",function($0){
this.color=$0;if(this["ctransform"]!=null){
$0=LzColorUtils.applyTransform($0,this.ctransform)
};(arguments.callee["$superclass"]&&arguments.callee.$superclass.prototype["$lzc$set_bgcolor"]||this.nextMethod(arguments.callee,"$lzc$set_bgcolor")).call(this,$0)
},"$lzc$set_colortransform",function($0){
this.ctransform=$0;this.setAttribute("bgcolor",this.color)
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
}],LzView,["tagname","swatchview","__LZCSSTagSelectors",["swatchview","view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},color:16777215,ctransform:null},$lzc$class_swatchview.attributes)
}}})($lzc$class_swatchview)
};{
Class.make("$lzc$class__m2z",["$m21",function($0){
var $1=this.parent.width-1;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m22",function(){
try{
return [this.parent,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m23",function($0){
var $1=this.parent.height-1;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m24",function(){
try{
return [this.parent,"height"]
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m2z.attributes)
}}})($lzc$class__m2z)
};{
Class.make("$lzc$class__m30",["$m25",function($0){
var $1=this.parent.width-3;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m26",function(){
try{
return [this.parent,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m27",function($0){
var $1=this.parent.height-3;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m28",function(){
try{
return [this.parent,"height"]
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m30.attributes)
}}})($lzc$class__m30)
};{
Class.make("$lzc$class__m31",["$m29",function($0){
var $1=this.parent.width-4;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m2a",function(){
try{
return [this.parent,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2b",function($0){
var $1=this.parent.height-4;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m2c",function(){
try{
return [this.parent,"height"]
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m31.attributes)
}}})($lzc$class__m31)
};{
Class.make("$lzc$class__m32",["$m2d",function($0){
var $1=this.parent.parent.width-2;if($1!==this["x"]||!this.inited){
this.setAttribute("x",$1)
}},"$m2e",function(){
try{
return [this.parent.parent,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2f",function($0){
var $1=this.parent.parent.height-2;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m2g",function(){
try{
return [this.parent.parent,"height"]
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m32.attributes)
}}})($lzc$class__m32)
};{
Class.make("$lzc$class__m33",["$m2h",function($0){
var $1=this.parent.parent.height-2;if($1!==this["y"]||!this.inited){
this.setAttribute("y",$1)
}},"$m2i",function(){
try{
return [this.parent.parent,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2j",function($0){
var $1=this.parent.parent.width-3;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m2k",function(){
try{
return [this.parent.parent,"width"]
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
Class.make("$lzc$class__m34",["$m2l",function($0){
var $1=this.parent.parent.width-1;if($1!==this["x"]||!this.inited){
this.setAttribute("x",$1)
}},"$m2m",function(){
try{
return [this.parent.parent,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2n",function($0){
var $1=this.parent.parent.height;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m2o",function(){
try{
return [this.parent.parent,"height"]
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m34.attributes)
}}})($lzc$class__m34)
};{
Class.make("$lzc$class__m35",["$m2p",function($0){
var $1=this.parent.parent.height-1;if($1!==this["y"]||!this.inited){
this.setAttribute("y",$1)
}},"$m2q",function(){
try{
return [this.parent.parent,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2r",function($0){
var $1=this.parent.parent.width-1;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m2s",function(){
try{
return [this.parent.parent,"width"]
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m35.attributes)
}}})($lzc$class__m35)
};{
Class.make("$lzc$class__m36",["$m2t",function($0){
var $1=this.parent.text_x+this.parent.titleshift;if($1!==this["x"]||!this.inited){
this.setAttribute("x",$1)
}},"$m2u",function(){
try{
return [this.parent,"text_x",this.parent,"titleshift"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2v",function($0){
var $1=this.parent.text_y+this.parent.titleshift;if($1!==this["y"]||!this.inited){
this.setAttribute("y",$1)
}},"$m2w",function(){
try{
return [this.parent,"text_y",this.parent,"titleshift"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m2x",function($0){
var $1=this.parent.text;if($1!==this["text"]||!this.inited){
this.setAttribute("text",$1)
}},"$m2y",function(){
try{
return [this.parent,"text"]
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",antiAliasType:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",cdata:"cdata",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",direction:"string",embedfonts:"boolean",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",gridFit:"string",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",hscroll:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",letterspacing:"number",lineheight:"number",loadratio:"number",mask:"string",maxhscroll:"number",maxlength:"numberExpression",maxscroll:"number",multiline:"boolean",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pattern:"string",pixellock:"boolean",placement:"string",playing:"boolean",resize:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",scroll:"number",scrollevents:"boolean",scrollheight:"number",scrollwidth:"number",selectable:"boolean",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",sharpness:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",text:"html",textalign:"string",textdecoration:"string",textindent:"number",thickness:"number",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",xscroll:"number",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression",yscroll:"number"}}},$lzc$class__m36.attributes)
}}})($lzc$class__m36)
};{
Class.make("$lzc$class_button",["text_padding_x",void 0,"text_padding_y",void 0,"$m1q",function($0){
var $1=this.width/2-this._title.width/2;if($1!==this["text_x"]||!this.inited){
this.setAttribute("text_x",$1)
}},"$m1r",function(){
try{
return [this,"width",this._title,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"text_x",void 0,"$m1s",function($0){
var $1=this.height/2-this._title.height/2;if($1!==this["text_y"]||!this.inited){
this.setAttribute("text_y",$1)
}},"$m1t",function(){
try{
return [this,"height",this._title,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"text_y",void 0,"$m1u",function($0){
var $1=this._title.width+2*this.text_padding_x;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m1v",function(){
try{
return [this._title,"width",this,"text_padding_x"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m1w",function($0){
var $1=this._title.height+2*this.text_padding_y;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m1x",function(){
try{
return [this._title,"height",this,"text_padding_y"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"buttonstate",void 0,"$m1y",function($0){
var $1=this.buttonstate==1?0:1;if($1!==this["titleshift"]||!this.inited){
this.setAttribute("titleshift",$1)
}},"$m1z",function(){
try{
return [this,"buttonstate"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"titleshift",void 0,"leftalign",void 0,"_showEnabled",function(){
this.showUp();this.setAttribute("clickable",this._enabled)
},"showDown",function($0){
switch(arguments.length){
case 0:
$0=null;

};if(this.hasdefault){
this._outerbezel.setAttribute("frame",5)
}else{
this._outerbezel.setAttribute("frame",this.downResourceNumber)
};this._face.setAttribute("frame",this.downResourceNumber);this._innerbezel.setAttribute("frame",this.downResourceNumber);this.setAttribute("buttonstate",2)
},"showUp",function($0){
switch(arguments.length){
case 0:
$0=null;

};if(this._enabled){
if(this.hasdefault){
this._outerbezel.setAttribute("frame",5)
}else{
this._outerbezel.setAttribute("frame",this.normalResourceNumber)
};this._face.setAttribute("frame",this.normalResourceNumber);this._innerbezel.setAttribute("frame",this.normalResourceNumber);if(this.style)this._title.setAttribute("fgcolor",this.style.textcolor)
}else{
if(this.style)this._title.setAttribute("fgcolor",this.style.textdisabledcolor);this._face.setAttribute("frame",this.disabledResourceNumber);this._outerbezel.setAttribute("frame",this.disabledResourceNumber);this._innerbezel.setAttribute("frame",this.disabledResourceNumber)
};this.setAttribute("buttonstate",1)
},"showOver",function($0){
switch(arguments.length){
case 0:
$0=null;

};if(this.hasdefault){
this._outerbezel.setAttribute("frame",5)
}else{
this._outerbezel.setAttribute("frame",this.overResourceNumber)
};this._face.setAttribute("frame",this.overResourceNumber);this._innerbezel.setAttribute("frame",this.overResourceNumber);this.setAttribute("buttonstate",1)
},"$m20",function($0){
if(this._initcomplete){
if(this.buttonstate==1)this.showUp()
}},"_applystyle",function($0){
if(this.style!=null){
this.textcolor=$0.textcolor;this.textdisabledcolor=$0.textdisabledcolor;if(this.enabled){
this._title.setAttribute("fgcolor",$0.textcolor)
}else{
this._title.setAttribute("fgcolor",$0.textdisabledcolor)
};this.setTint(this._outerbezel,$0.basecolor);this.setTint(this._innerbezel,$0.basecolor);this.setTint(this._face,$0.basecolor)
}},"_outerbezel",void 0,"_innerbezel",void 0,"_face",void 0,"_innerbezelbottom",void 0,"_outerbezelbottom",void 0,"_title",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],$lzc$class_basebutton,["tagname","button","children",[{attrs:{$classrootdepth:1,bgcolor:9539985,height:new LzAlwaysExpr("$m23","$m24",null),name:"_outerbezel",width:new LzAlwaysExpr("$m21","$m22",null),x:0,y:0},"class":$lzc$class__m2z},{attrs:{$classrootdepth:1,bgcolor:16777215,height:new LzAlwaysExpr("$m27","$m28",null),name:"_innerbezel",width:new LzAlwaysExpr("$m25","$m26",null),x:1,y:1},"class":$lzc$class__m30},{attrs:{$classrootdepth:1,height:new LzAlwaysExpr("$m2b","$m2c",null),name:"_face",resource:"lzbutton_face_rsc",stretches:"both",width:new LzAlwaysExpr("$m29","$m2a",null),x:2,y:2},"class":$lzc$class__m31},{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$classrootdepth:1,name:"_innerbezelbottom"},children:[{attrs:{$classrootdepth:2,bgcolor:5789784,height:new LzAlwaysExpr("$m2f","$m2g",null),width:1,x:new LzAlwaysExpr("$m2d","$m2e",null),y:1},"class":$lzc$class__m32},{attrs:{$classrootdepth:2,bgcolor:5789784,height:1,width:new LzAlwaysExpr("$m2j","$m2k",null),x:1,y:new LzAlwaysExpr("$m2h","$m2i",null)},"class":$lzc$class__m33}],"class":LzView},{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$classrootdepth:1,name:"_outerbezelbottom"},children:[{attrs:{$classrootdepth:2,bgcolor:16777215,height:new LzAlwaysExpr("$m2n","$m2o",null),opacity:0.7,width:1,x:new LzAlwaysExpr("$m2l","$m2m",null),y:0},"class":$lzc$class__m34},{attrs:{$classrootdepth:2,bgcolor:16777215,height:1,opacity:0.7,width:new LzAlwaysExpr("$m2r","$m2s",null),x:0,y:new LzAlwaysExpr("$m2p","$m2q",null)},"class":$lzc$class__m35}],"class":LzView},{attrs:{$classrootdepth:1,name:"_title",resize:true,text:new LzAlwaysExpr("$m2x","$m2y",null),x:new LzAlwaysExpr("$m2t","$m2u",null),y:new LzAlwaysExpr("$m2v","$m2w",null)},"class":$lzc$class__m36}],"__LZCSSTagSelectors",["button","basebutton","basecomponent","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_basebutton.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$attributeDescriptor:{types:{_focusable:"boolean",aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",buttonstate:"number",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",disabledResourceNumber:"number",doesenter:"boolean",downResourceNumber:"number",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",leftalign:"number",loadratio:"number",mask:"string",maxframes:"number",name:"token",nodeLevel:"number",normalResourceNumber:"number",opacity:"number",options:"css",overResourceNumber:"number",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourceviewcount:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",text:"html",text_padding_x:"number",text_padding_y:"number",text_x:"number",text_y:"number",tintcolor:"string",titleshift:"number",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$delegates:["onhasdefault","$m20",null],buttonstate:1,clickable:true,doesenter:true,focusable:true,height:new LzAlwaysExpr("$m1w","$m1x",null),leftalign:false,maxframes:4,pixellock:true,styleable:true,text_padding_x:11,text_padding_y:4,text_x:new LzAlwaysExpr("$m1q","$m1r",null),text_y:new LzAlwaysExpr("$m1s","$m1t",null),titleshift:new LzAlwaysExpr("$m1y","$m1z",null),width:new LzAlwaysExpr("$m1u","$m1v",null)},$lzc$class_button.attributes)
}}})($lzc$class_button)
};{
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
}},"$m37",function($0){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",name:"token",nodeLevel:"number",options:"css",parent:"string",placement:"string",styleclass:"string",subnodes:"string",transition:"string","with":"string"}},$delegates:["onconstruct","$m37",null],locked:2},LzLayout.attributes)
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
Class.make("$lzc$class__m3p",["$m3n",function($0){
this.setAttribute("width",this.parent.width)
},"$m3o",function($0){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m3p.attributes)
}}})($lzc$class__m3p)
};{
Class.make("$lzc$class_roundrect",["inset",void 0,"$lzc$set_inset",function($0){
this.setInset($0)
},"oninset",void 0,"$m38",function($0){
var $1=null;if($1!==this["insetleft"]||!this.inited){
this.setAttribute("insetleft",$1)
}},"$m39",function(){
try{
return []
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"insetleft",void 0,"$lzc$set_insetleft",function($0){
this.setInsetLeft($0)
},"oninsetleft",void 0,"$m3a",function($0){
var $1=null;if($1!==this["insetright"]||!this.inited){
this.setAttribute("insetright",$1)
}},"$m3b",function(){
try{
return []
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"insetright",void 0,"$lzc$set_insetright",function($0){
this.setInsetRight($0)
},"oninsetright",void 0,"$m3c",function($0){
var $1=null;if($1!==this["insettop"]||!this.inited){
this.setAttribute("insettop",$1)
}},"$m3d",function(){
try{
return []
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"insettop",void 0,"$lzc$set_insettop",function($0){
this.setInsetTop($0)
},"oninsettop",void 0,"$m3e",function($0){
var $1=null;if($1!==this["insetbottom"]||!this.inited){
this.setAttribute("insetbottom",$1)
}},"$m3f",function(){
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
},"$m3g",function($0){
if(this.context)this.drawStructure()
},"$m3h",function($0){
if(this.context)this.drawStructure()
},"borderWidth",void 0,"borderRadius",void 0,"borderColor",void 0,"borderOpacity",void 0,"$m3i",function($0){
this.setAttribute("backgroundStartColor",null)
},"backgroundStartColor",void 0,"$m3j",function($0){
this.setAttribute("backgroundStopColor",null)
},"backgroundStopColor",void 0,"backgroundStartOpacity",void 0,"backgroundStopOpacity",void 0,"backgroundGradientOrientation",void 0,"boxShadowX",void 0,"boxShadowY",void 0,"$m3k",function($0){
this.setAttribute("boxShadowColor",null)
},"boxShadowColor",void 0,"boxShadowOpacity",void 0,"$m3l",function($0){
if(this.context)this.drawStructure()
},"$m3m",function($0){
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
}],$lzc$class_drawview,["tagname","roundrect","children",LzNode.mergeChildren([{attrs:{$classrootdepth:1,height:new LzOnceExpr("$m3o",null),name:"content",width:new LzOnceExpr("$m3n",null),x:0,y:0},"class":$lzc$class__m3p},{attrs:"content","class":$lzc$class_userClassPlacement}],$lzc$class_drawview["children"]),"__LZCSSTagSelectors",["roundrect","drawview","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_drawview.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",aliaslines:"boolean",align:"string",backgroundGradientOrientation:"string",backgroundStartColor:"color",backgroundStopColor:"color",backgroundrepeat:"string",bgcolor:"color",borderColor:"color",boxShadowColor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",fillStyle:"string",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",globalAlpha:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",inset:"size",insetbottom:"size",insetleft:"size",insetright:"size",insettop:"size",isinited:"boolean",layout:"css",lineCap:"string",lineJoin:"string",lineWidth:"number",loadratio:"number",mask:"string",measuresize:"boolean",miterLimit:"number",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",strokeStyle:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$delegates:["onwidth","$m3g",null,"onheight","$m3h",null,"oninit","$m3l",null,"oncontext","$m3m",null],backgroundGradientOrientation:"vertical",backgroundStartColor:new LzOnceExpr("$m3i",null),backgroundStartOpacity:1,backgroundStopColor:new LzOnceExpr("$m3j",null),backgroundStopOpacity:1,borderColor:0,borderOpacity:1,borderRadius:5,borderWidth:1,boxShadowColor:new LzOnceExpr("$m3k",null),boxShadowOpacity:0.5,boxShadowX:5,boxShadowY:5,height:100,inset:5,insetbottom:new LzAlwaysExpr("$m3e","$m3f",null),insetleft:new LzAlwaysExpr("$m38","$m39",null),insetright:new LzAlwaysExpr("$m3a","$m3b",null),insettop:new LzAlwaysExpr("$m3c","$m3d",null),oninset:null,oninsetbottom:null,oninsetleft:null,oninsetright:null,oninsettop:null,width:100},$lzc$class_roundrect.attributes)
}}})($lzc$class_roundrect)
};{
Class.make("$lzc$class__m4z",["$m3r",function($0){
var $1=-1*this.parent.target.cardwidth;if($1!==this["from"]||!this.inited){
this.setAttribute("from",$1)
}},"$m3s",function(){
try{
return [this.parent.target,"cardwidth"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m3t",function($0){
var $1=(this.parent.target.classroot.width-this.parent.target.cardwidth)/2;if($1!==this["to"]||!this.inited){
this.setAttribute("to",$1)
}},"$m3u",function(){
try{
return [this.parent.target.classroot,"width",this.parent.target,"cardwidth"]
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
}],LzAnimator,["displayName","<anonymous extends='animator'>","__LZCSSTagSelectors",["animator","animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimator.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m4z.attributes)
}}})($lzc$class__m4z)
};{
Class.make("$lzc$class__m4y",["$m3q",function($0){
this.setAttribute("target",this.classroot.cardtarget)
},"topanim",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],LzAnimatorGroup,["displayName","<anonymous extends='animatorgroup'>","children",[{attrs:{$classrootdepth:2,attribute:"x",duration:1000,from:new LzAlwaysExpr("$m3r","$m3s",null),motion:"easeout",name:"topanim",to:new LzAlwaysExpr("$m3t","$m3u",null)},"class":$lzc$class__m4z}],"__LZCSSTagSelectors",["animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimatorGroup.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m4y.attributes)
}}})($lzc$class__m4y)
};{
Class.make("$lzc$class__m51",["$m3w",function($0){
var $1=(this.parent.target.classroot.width-this.parent.target.cardwidth)/2;if($1!==this["from"]||!this.inited){
this.setAttribute("from",$1)
}},"$m3x",function(){
try{
return [this.parent.target.classroot,"width",this.parent.target,"cardwidth"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m3y",function($0){
var $1=this.parent.target.classroot.width;if($1!==this["to"]||!this.inited){
this.setAttribute("to",$1)
}},"$m3z",function(){
try{
return [this.parent.target.classroot,"width"]
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
}],LzAnimator,["displayName","<anonymous extends='animator'>","__LZCSSTagSelectors",["animator","animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimator.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m51.attributes)
}}})($lzc$class__m51)
};{
Class.make("$lzc$class__m52",["$m40",function($0){
var $1=true;if($1!==this["to"]||!this.inited){
this.setAttribute("to",$1)
}},"$m41",function(){
try{
return []
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
}],LzAnimator,["displayName","<anonymous extends='animator'>","__LZCSSTagSelectors",["animator","animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimator.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m52.attributes)
}}})($lzc$class__m52)
};{
Class.make("$lzc$class__m50",["$m3v",function($0){
this.setAttribute("target",this.classroot.cardtarget)
},"topanim",void 0,"returnanim",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],LzAnimatorGroup,["displayName","<anonymous extends='animatorgroup'>","children",[{attrs:{$classrootdepth:2,attribute:"x",duration:1000,from:new LzAlwaysExpr("$m3w","$m3x",null),motion:"easeout",name:"topanim",to:new LzAlwaysExpr("$m3y","$m3z",null)},"class":$lzc$class__m51},{attrs:{$classrootdepth:2,attribute:"showingfront",duration:1,motion:"linear",name:"returnanim",to:new LzAlwaysExpr("$m40","$m41",null)},"class":$lzc$class__m52}],"__LZCSSTagSelectors",["animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimatorGroup.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m50.attributes)
}}})($lzc$class__m50)
};{
Class.make("$lzc$class__m54",["$m43",function($0){
var $1=this.parent.target.classroot.width;if($1!==this["from"]||!this.inited){
this.setAttribute("from",$1)
}},"$m44",function(){
try{
return [this.parent.target.classroot,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m45",function($0){
var $1=(this.parent.target.classroot.width-this.parent.target.cardwidth)/2;if($1!==this["to"]||!this.inited){
this.setAttribute("to",$1)
}},"$m46",function(){
try{
return [this.parent.target.classroot,"width",this.parent.target,"cardwidth"]
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
}],LzAnimator,["displayName","<anonymous extends='animator'>","__LZCSSTagSelectors",["animator","animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimator.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m54.attributes)
}}})($lzc$class__m54)
};{
Class.make("$lzc$class__m53",["$m42",function($0){
this.setAttribute("target",this.classroot.cardtarget)
},"topanim",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],LzAnimatorGroup,["displayName","<anonymous extends='animatorgroup'>","children",[{attrs:{$classrootdepth:2,attribute:"x",duration:1000,from:new LzAlwaysExpr("$m43","$m44",null),motion:"easeout",name:"topanim",to:new LzAlwaysExpr("$m45","$m46",null)},"class":$lzc$class__m54}],"__LZCSSTagSelectors",["animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimatorGroup.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m53.attributes)
}}})($lzc$class__m53)
};{
Class.make("$lzc$class__m56",["$m48",function($0){
var $1=(this.parent.target.classroot.width-this.parent.target.cardwidth)/2;if($1!==this["from"]||!this.inited){
this.setAttribute("from",$1)
}},"$m49",function(){
try{
return [this.parent.target.classroot,"width",this.parent.target,"cardwidth"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m4a",function($0){
var $1=-1*this.parent.target.cardwidth;if($1!==this["to"]||!this.inited){
this.setAttribute("to",$1)
}},"$m4b",function(){
try{
return [this.parent.target,"cardwidth"]
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
}],LzAnimator,["displayName","<anonymous extends='animator'>","__LZCSSTagSelectors",["animator","animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimator.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m56.attributes)
}}})($lzc$class__m56)
};{
Class.make("$lzc$class__m57",["$m4c",function($0){
var $1=true;if($1!==this["to"]||!this.inited){
this.setAttribute("to",$1)
}},"$m4d",function(){
try{
return []
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
}],LzAnimator,["displayName","<anonymous extends='animator'>","__LZCSSTagSelectors",["animator","animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimator.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m57.attributes)
}}})($lzc$class__m57)
};{
Class.make("$lzc$class__m55",["$m47",function($0){
this.setAttribute("target",this.classroot.cardtarget)
},"topanim",void 0,"returnanim",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],LzAnimatorGroup,["displayName","<anonymous extends='animatorgroup'>","children",[{attrs:{$classrootdepth:2,attribute:"x",duration:1000,from:new LzAlwaysExpr("$m48","$m49",null),motion:"easeout",name:"topanim",to:new LzAlwaysExpr("$m4a","$m4b",null)},"class":$lzc$class__m56},{attrs:{$classrootdepth:2,attribute:"showingfront",duration:1,motion:"linear",name:"returnanim",to:new LzAlwaysExpr("$m4c","$m4d",null)},"class":$lzc$class__m57}],"__LZCSSTagSelectors",["animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimatorGroup.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m55.attributes)
}}})($lzc$class__m55)
};{
Class.make("$lzc$class__m5a",["$m4g",function($0){
var $1=this.parent.target.cardwidth;if($1!==this["from"]||!this.inited){
this.setAttribute("from",$1)
}},"$m4h",function(){
try{
return [this.parent.target,"cardwidth"]
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
}],LzAnimator,["displayName","<anonymous extends='animator'>","__LZCSSTagSelectors",["animator","animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimator.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m5a.attributes)
}}})($lzc$class__m5a)
};{
Class.make("$lzc$class__m5b",["$m4i",function($0){
var $1=this.parent.target.cardwidth/2;if($1!==this["to"]||!this.inited){
this.setAttribute("to",$1)
}},"$m4j",function(){
try{
return [this.parent.target,"cardwidth"]
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
}],LzAnimator,["displayName","<anonymous extends='animator'>","__LZCSSTagSelectors",["animator","animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimator.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m5b.attributes)
}}})($lzc$class__m5b)
};{
Class.make("$lzc$class__m59",["$m4f",function($0){
this.setAttribute("target",this.classroot.cardtarget)
},"anmThin",void 0,"anmThinCentre",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],LzAnimatorGroup,["displayName","<anonymous extends='animatorgroup'>","children",[{attrs:{$classrootdepth:3,attribute:"width",duration:150,from:new LzAlwaysExpr("$m4g","$m4h",null),motion:"linear",name:"anmThin",to:0},"class":$lzc$class__m5a},{attrs:{$classrootdepth:3,attribute:"x",duration:150,motion:"linear",name:"anmThinCentre",relative:true,to:new LzAlwaysExpr("$m4i","$m4j",null)},"class":$lzc$class__m5b}],"__LZCSSTagSelectors",["animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimatorGroup.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m59.attributes)
}}})($lzc$class__m59)
};{
Class.make("$lzc$class__m5c",["$m4k",function($0){
var $1=!this.classroot.cardtarget.showingfront;if($1!==this["to"]||!this.inited){
this.setAttribute("to",$1)
}},"$m4l",function(){
try{
return [this.classroot.cardtarget,"showingfront"]
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
}],LzAnimator,["displayName","<anonymous extends='animator'>","__LZCSSTagSelectors",["animator","animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimator.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m5c.attributes)
}}})($lzc$class__m5c)
};{
Class.make("$lzc$class__m5e",["$m4n",function($0){
var $1=this.parent.target.cardwidth/-2;if($1!==this["to"]||!this.inited){
this.setAttribute("to",$1)
}},"$m4o",function(){
try{
return [this.parent.target,"cardwidth"]
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
}],LzAnimator,["displayName","<anonymous extends='animator'>","__LZCSSTagSelectors",["animator","animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimator.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m5e.attributes)
}}})($lzc$class__m5e)
};{
Class.make("$lzc$class__m5f",["$m4p",function($0){
var $1=this.parent.target.cardwidth;if($1!==this["to"]||!this.inited){
this.setAttribute("to",$1)
}},"$m4q",function(){
try{
return [this.parent.target,"cardwidth"]
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
}],LzAnimator,["displayName","<anonymous extends='animator'>","__LZCSSTagSelectors",["animator","animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimator.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m5f.attributes)
}}})($lzc$class__m5f)
};{
Class.make("$lzc$class__m5d",["$m4m",function($0){
this.setAttribute("target",this.classroot.cardtarget)
},"anmFatCentre",void 0,"anmFat",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],LzAnimatorGroup,["displayName","<anonymous extends='animatorgroup'>","children",[{attrs:{$classrootdepth:3,attribute:"x",duration:150,motion:"linear",name:"anmFatCentre",relative:true,to:new LzAlwaysExpr("$m4n","$m4o",null)},"class":$lzc$class__m5e},{attrs:{$classrootdepth:3,attribute:"width",duration:150,from:0,motion:"linear",name:"anmFat",to:new LzAlwaysExpr("$m4p","$m4q",null)},"class":$lzc$class__m5f}],"__LZCSSTagSelectors",["animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimatorGroup.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m5d.attributes)
}}})($lzc$class__m5d)
};{
Class.make("$lzc$class__m58",["$m4e",function($0){
this.setAttribute("target",this.classroot.cardtarget)
},"anmSwivelOut",void 0,"anmToggle",void 0,"anmSwivelIn",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],LzAnimatorGroup,["displayName","<anonymous extends='animatorgroup'>","children",[{attrs:{$classrootdepth:2,anmThin:void 0,anmThinCentre:void 0,name:"anmSwivelOut",process:"simultaneous",start:false,target:new LzOnceExpr("$m4f",null)},"class":$lzc$class__m59},{attrs:{$classrootdepth:2,attribute:"showingfront",duration:1,motion:"linear",name:"anmToggle",to:new LzAlwaysExpr("$m4k","$m4l",null)},"class":$lzc$class__m5c},{attrs:{$classrootdepth:2,anmFat:void 0,anmFatCentre:void 0,name:"anmSwivelIn",process:"simultaneous",start:false,target:new LzOnceExpr("$m4m",null)},"class":$lzc$class__m5d}],"__LZCSSTagSelectors",["animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimatorGroup.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m58.attributes)
}}})($lzc$class__m58)
};{
Class.make("$lzc$class__m5g",["$m4r",function($0){
this.setAttribute("target",this.classroot.cardtarget)
},"topanim",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],LzAnimatorGroup,["displayName","<anonymous extends='animatorgroup'>","children",[{attrs:{$CSSDescriptor:{},$attributeDescriptor:{properties:{from:"fadeopacity"},types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}},$classrootdepth:2,attribute:"opacity",duration:1000,from:LzStyleConstraintExpr.StyleConstraintExpr,motion:"linear",name:"topanim",to:0},"class":LzAnimator}],"__LZCSSTagSelectors",["animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimatorGroup.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m5g.attributes)
}}})($lzc$class__m5g)
};{
Class.make("$lzc$class__m5i",["$m4t",function($0){
var $1=this.parent.target.height;if($1!==this["from"]||!this.inited){
this.setAttribute("from",$1)
}},"$m4u",function(){
try{
return [this.parent.target,"height"]
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
}],LzAnimator,["displayName","<anonymous extends='animator'>","__LZCSSTagSelectors",["animator","animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimator.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m5i.attributes)
}}})($lzc$class__m5i)
};{
Class.make("$lzc$class__m5h",["$m4s",function($0){
this.setAttribute("target",this.classroot.cardtarget)
},"topanim",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],LzAnimatorGroup,["displayName","<anonymous extends='animatorgroup'>","children",[{attrs:{$classrootdepth:2,attribute:"y",duration:1000,from:new LzAlwaysExpr("$m4t","$m4u",null),motion:"easeout",name:"topanim",to:1},"class":$lzc$class__m5i}],"__LZCSSTagSelectors",["animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimatorGroup.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m5h.attributes)
}}})($lzc$class__m5h)
};{
Class.make("$lzc$class__m5k",["$m4w",function($0){
var $1=-1*this.parent.target.height;if($1!==this["from"]||!this.inited){
this.setAttribute("from",$1)
}},"$m4x",function(){
try{
return [this.parent.target,"height"]
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
}],LzAnimator,["displayName","<anonymous extends='animator'>","__LZCSSTagSelectors",["animator","animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimator.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m5k.attributes)
}}})($lzc$class__m5k)
};{
Class.make("$lzc$class__m5j",["$m4v",function($0){
this.setAttribute("target",this.classroot.cardtarget)
},"topanim",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],LzAnimatorGroup,["displayName","<anonymous extends='animatorgroup'>","children",[{attrs:{$classrootdepth:2,attribute:"y",duration:1000,from:new LzAlwaysExpr("$m4w","$m4x",null),motion:"easeout",name:"topanim",to:1},"class":$lzc$class__m5k}],"__LZCSSTagSelectors",["animatorgroup","node","Instance"],"attributes",new LzInheritedHash(LzAnimatorGroup.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}}},$lzc$class__m5j.attributes)
}}})($lzc$class__m5j)
};{
Class.make("$lzc$class_animations",["cardtarget",void 0,"anmSlideInRight",void 0,"anmSlideAwayRight",void 0,"anmSlideInLeft",void 0,"anmSlideAwayLeft",void 0,"anmSwivelCard",void 0,"anmFadeIn",void 0,"anmFadeOut",void 0,"anmSlideUp",void 0,"anmSlideDown",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],LzView,["tagname","animations","children",[{attrs:{$classrootdepth:1,name:"anmSlideInRight",process:"simultaneous",start:false,target:new LzOnceExpr("$m3q",null),topanim:void 0},"class":$lzc$class__m4y},{attrs:{$classrootdepth:1,name:"anmSlideAwayRight",process:"sequential",returnanim:void 0,start:false,target:new LzOnceExpr("$m3v",null),topanim:void 0},"class":$lzc$class__m50},{attrs:{$classrootdepth:1,name:"anmSlideInLeft",process:"sequential",start:false,target:new LzOnceExpr("$m42",null),topanim:void 0},"class":$lzc$class__m53},{attrs:{$classrootdepth:1,name:"anmSlideAwayLeft",process:"sequential",returnanim:void 0,start:false,target:new LzOnceExpr("$m47",null),topanim:void 0},"class":$lzc$class__m55},{attrs:{$classrootdepth:1,anmSwivelIn:void 0,anmSwivelOut:void 0,anmToggle:void 0,name:"anmSwivelCard",process:"sequential",start:false,target:new LzOnceExpr("$m4e",null)},"class":$lzc$class__m58},{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}},$classrootdepth:1,name:"anmFadeIn",process:"simultaneous",start:false,topanim:void 0},children:[{attrs:{$CSSDescriptor:{},$attributeDescriptor:{properties:{to:"fadeopacity"},types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}},$classrootdepth:2,attribute:"opacity",duration:1000,from:0,motion:"linear",name:"topanim",to:LzStyleConstraintExpr.StyleConstraintExpr},"class":LzAnimator}],"class":LzAnimatorGroup},{attrs:{$classrootdepth:1,name:"anmFadeOut",process:"simultaneous",start:false,target:new LzOnceExpr("$m4r",null),topanim:void 0},"class":$lzc$class__m5g},{attrs:{$classrootdepth:1,name:"anmSlideUp",process:"simultaneous",start:false,target:new LzOnceExpr("$m4s",null),topanim:void 0},"class":$lzc$class__m5h},{attrs:{$classrootdepth:1,name:"anmSlideDown",process:"simultaneous",start:false,target:new LzOnceExpr("$m4v",null),topanim:void 0},"class":$lzc$class__m5j}],"__LZCSSTagSelectors",["animations","view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},cardtarget:null},$lzc$class_animations.attributes)
}}})($lzc$class_animations)
};Class.make("$lzc$class__m5z",["$m5x",function($0){
var $1=this.parent;if($1!==this["cardtarget"]||!this.inited){
this.setAttribute("cardtarget",$1)
}},"$m5y",function(){
try{
return [this,"parent"]
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
}],$lzc$class_animations,["displayName","<anonymous extends='animations'>","children",LzNode.mergeChildren([],$lzc$class_animations["children"]),"__LZCSSTagSelectors",["animations","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_animations.attributes)]);{
Class.make("$lzc$class_animatedview",["$m5l",function($0){
this.setAttribute("_transishion",this.transishions.anmSlideInRight)
},"_transishion",void 0,"$m5m",function($0){
this.setAttribute("_swiveltransishion",this.transishions.anmSwivelCard)
},"_swiveltransishion",void 0,"maincolor",void 0,"onscreen",void 0,"defaultfontsize",void 0,"cardwidth",void 0,"cardheight",void 0,"showingfront",void 0,"$m5n",function($0){
var $1=this.maincolor;if($1!==this["backgroundStartColor"]||!this.inited){
this.setAttribute("backgroundStartColor",$1)
}},"$m5o",function(){
try{
return [this,"maincolor"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m5p",function($0){
var $1=this.maincolor*0.95;if($1!==this["backgroundStopColor"]||!this.inited){
this.setAttribute("backgroundStopColor",$1)
}},"$m5q",function(){
try{
return [this,"maincolor"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"flatopacity",void 0,"$m5r",function($0){
var $1=this.flatopacity;if($1!==this["backgroundStartOpacity"]||!this.inited){
this.setAttribute("backgroundStartOpacity",$1)
}},"$m5s",function(){
try{
return [this,"flatopacity"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m5t",function($0){
var $1=this.flatopacity;if($1!==this["backgroundStopOpacity"]||!this.inited){
this.setAttribute("backgroundStopOpacity",$1)
}},"$m5u",function(){
try{
return [this,"flatopacity"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"bordersize",void 0,"$m5v",function($0){
this.setAttribute("backgroundStartColor",this.maincolor);this.setAttribute("backgroundStopColor",this.maincolor*0.9);this.drawStructure()
},"setTransishion",function($0){
switch($0){
case "anmSlideInLeft":
this._transishion=this.transishions.anmSlideInLeft;break;
case "anmSlideAwayLeft":
this._transishion=this.transishions.anmSlideAwayLeft;break;
case "anmSlideInRight":
this._transishion=this.transishions.anmSlideInRight;break;
case "anmSlideAwayRight":
this._transishion=this.transishions.anmSlideAwayRight;break;

}},"swivelcard",function(){
if(!this._transishion.isactive){
this._swiveltransishion.doStart()
}},"$m5w",function($0){
if(!this._swiveltransishion.isactive){
this._transishion.doStart()
}},"transishions",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],$lzc$class_roundrect,["tagname","animatedview","children",LzNode.mergeChildren([{attrs:{$classrootdepth:1,cardtarget:new LzAlwaysExpr("$m5x","$m5y",null),name:"transishions"},"class":$lzc$class__m5z}],$lzc$class_roundrect["children"]),"__LZCSSTagSelectors",["animatedview","roundrect","drawview","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_roundrect.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",aliaslines:"boolean",align:"string",backgroundGradientOrientation:"string",backgroundStartColor:"color",backgroundStopColor:"color",backgroundrepeat:"string",bgcolor:"color",borderColor:"color",boxShadowColor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",fillStyle:"string",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",globalAlpha:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",inset:"size",insetbottom:"size",insetleft:"size",insetright:"size",insettop:"size",isinited:"boolean",layout:"css",lineCap:"string",lineJoin:"string",lineWidth:"number",loadratio:"number",maincolor:"color",mask:"string",measuresize:"boolean",miterLimit:"number",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",strokeStyle:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$delegates:["onmaincolor","$m5v",null,"ononscreen","$m5w",null],_swiveltransishion:new LzOnceExpr("$m5m",null),_transishion:new LzOnceExpr("$m5l",null),backgroundStartColor:new LzAlwaysExpr("$m5n","$m5o",null),backgroundStartOpacity:new LzAlwaysExpr("$m5r","$m5s",null),backgroundStopColor:new LzAlwaysExpr("$m5p","$m5q",null),backgroundStopOpacity:new LzAlwaysExpr("$m5t","$m5u",null),bordersize:2,cardheight:100,cardwidth:150,clip:true,defaultfontsize:14,flatopacity:0.6,maincolor:16777215,onscreen:false,showingfront:true},$lzc$class_animatedview.attributes)
}}})($lzc$class_animatedview)
};;(function(){
var $0=LzCSSStyle,$1=LzCSSStyleRule;$0._addRule(new $1([{a:"name",s:11,t:"view",v:"topbuttoncontainer"},{s:1,t:"consoleiconbutton"},{s:1,t:"text"}],{fgcolor:"#FFFFFF"},"flashcardslib.lzx",7));$0._addRule(new $1({a:"name",s:11,t:"view",v:"opacityfilter"},{bgcolor:"#FFFFFF",opacity:"0.6"},"flashcardslib.lzx",1));$0._addRule(new $1({a:"name",s:11,t:"view",v:"canvasbackground"},{backgroundimage:"null",bgcolor:"#111111",opacity:"1.0"},"flashcardslib.lzx",2));$0._addRule(new $1({a:"name",s:11,t:"boxview",v:"assigned"},{opacity:"1.0"},"flashcardslib.lzx",3));$0._addRule(new $1({a:"name",s:11,t:"boxview",v:"unassigned"},{opacity:"1.0"},"flashcardslib.lzx",4));$0._addRule(new $1({a:"name",s:11,t:"boxview",v:"offline"},{opacity:"1.0"},"flashcardslib.lzx",5));$0._addRule(new $1({a:"name",s:11,t:"view",v:"background"},{bgcolor:"#AAAAAA",opacity:"0.6"},"flashcardslib.lzx",6));$0._addRule(new $1({s:1,t:"view"},{bgcolor:"#AAAAAA",fgcolor:"#FFFFFF",opacity:"1.0"},"flashcardslib.lzx",0));$0._addRule(new $1({s:1,t:"animatedview"},{flatopacity:"0.6",interviewcolor:"#99CCCC",jumpcolor:"#FF3333",moodlestatuscolor:"#CC6666",opacity:"1.0",paircolor:"#FFCCFF",screencastcolor:"#3333FF",scribblecolor:"#33FF33",settingscolor:"#FFFF99",soundcolor:"#3F3F3F"},"flashcardslib.lzx",8));$0._addRule(new $1({s:1,t:"screensubscribe"},{bgcolor:"#FF0000"},"flashcardslib.lzx",9));$0._addRule(new $1({s:1,t:"animator"},{fadeopacity:"1.0"},"flashcardslib.lzx",10))
})();{
Class.make("$lzc$class__m7d",["$m60",function($0){
var $1=unescape(this.classroot.cardset);if($1!==this["src"]||!this.inited){
this.setAttribute("src",$1)
}},"$m61",function(){
try{
return [this.classroot,"cardset"].concat($lzc$getFunctionDependencies("unescape",this,this,[this.classroot.cardset],null))
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m62",function($0){
var $1=this.getPointer();$1.selectChild();this.classroot.totalCards=$1.getNodeCount();Debug.write("Node Count:",this.classroot.totalCards);if(this.classroot.randomize){
$1.p.childNodes.sort(function(){
return Math.random()>0.5?1:-1
})
};this.classroot.setAttribute("frontfgcolor",Number($1.xpathQuery("@frontfgcolor")));this.classroot.setAttribute("frontbgcolor",Number($1.xpathQuery("@frontbgcolor")));this.classroot.setAttribute("backfgcolor",Number($1.xpathQuery("@backfgcolor")));this.classroot.setAttribute("backbgcolor",Number($1.xpathQuery("@backbgcolor")));Debug.write("cardfgcolor:",this.classroot.frontfgcolor);this.classroot.deckview.completeInstantiation()
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
}],LzDataset,["displayName","<anonymous extends='dataset'>","__LZCSSTagSelectors",["dataset","node","Instance"],"attributes",new LzInheritedHash(LzDataset.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{acceptencodings:"boolean",autorequest:"boolean",cacheable:"boolean",classroot:"string",clientcacheable:"boolean",cloneManager:"string",datafromchild:"boolean",datapath:"string",defaultplacement:"string",getresponseheaders:"boolean",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",multirequest:"boolean",name:"token",nodeLevel:"number",nsprefix:"boolean",options:"css",params:"string",parent:"string",placement:"string",postbody:"string",proxied:"inheritableBoolean",proxyurl:"string",querystring:"string",querytype:"string",queuerequests:"boolean",rawdata:"string",request:"boolean",secureport:"number",src:"string",styleclass:"string",subnodes:"string",timeout:"number",transition:"string",trimwhitespace:"boolean",type:"string","with":"string"}}},$lzc$class__m7d.attributes)
}}})($lzc$class__m7d)
};{
Class.make("$lzc$class__m7g",["$m6i",function($0){
var $1=this.classroot.frontfgcolor;if($1!==this["fgcolor"]||!this.inited){
this.setAttribute("fgcolor",$1)
}},"$m6j",function(){
try{
return [this.classroot,"frontfgcolor"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m6k",function($0){
this.dataBindAttribute("fontsize","@fontsize","size")
},"$m6l",function($0){
var $1=this.innerheight?this.classroot.cardheight*this.innerheight:null;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m6m",function(){
try{
return [this,"innerheight",this.classroot,"cardheight"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m6n",function($0){
var $1=this.classroot.cardwidth*0.8;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m6o",function(){
try{
return [this.classroot,"cardwidth"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m6p",function($0){
var $1=this.parent.showingfront;if($1!==this["visible"]||!this.inited){
this.setAttribute("visible",$1)
}},"$m6q",function(){
try{
return [this.parent,"showingfront"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m6r",function($0){
this.dataBindAttribute("innerheight","@innerheight","expression")
},"innerheight",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",antiAliasType:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",cdata:"cdata",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",direction:"string",embedfonts:"boolean",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",gridFit:"string",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",hscroll:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",letterspacing:"number",lineheight:"number",loadratio:"number",mask:"string",maxhscroll:"number",maxlength:"numberExpression",maxscroll:"number",multiline:"boolean",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pattern:"string",pixellock:"boolean",placement:"string",playing:"boolean",resize:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",scroll:"number",scrollevents:"boolean",scrollheight:"number",scrollwidth:"number",selectable:"boolean",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",sharpness:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",text:"html",textalign:"string",textdecoration:"string",textindent:"number",thickness:"number",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",xscroll:"number",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression",yscroll:"number"}}},$lzc$class__m7g.attributes)
}}})($lzc$class__m7g)
};{
Class.make("$lzc$class__m7h",["$m6s",function($0){
var $1=this.classroot.backfgcolor;if($1!==this["fgcolor"]||!this.inited){
this.setAttribute("fgcolor",$1)
}},"$m6t",function(){
try{
return [this.classroot,"backfgcolor"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m6u",function($0){
this.dataBindAttribute("fontsize","@fontsize","size")
},"$m6v",function($0){
var $1=this.innerheight?this.classroot.cardheight*this.innerheight:null;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m6w",function(){
try{
return [this,"innerheight",this.classroot,"cardheight"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m6x",function($0){
var $1=this.classroot.cardwidth*0.8;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m6y",function(){
try{
return [this.classroot,"cardwidth"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m6z",function($0){
var $1=!this.parent.showingfront;if($1!==this["visible"]||!this.inited){
this.setAttribute("visible",$1)
}},"$m70",function(){
try{
return [this.parent,"showingfront"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m71",function($0){
this.dataBindAttribute("innerheight","@innerheight","expression")
},"innerheight",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",antiAliasType:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",cdata:"cdata",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",direction:"string",embedfonts:"boolean",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",gridFit:"string",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",hscroll:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",letterspacing:"number",lineheight:"number",loadratio:"number",mask:"string",maxhscroll:"number",maxlength:"numberExpression",maxscroll:"number",multiline:"boolean",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pattern:"string",pixellock:"boolean",placement:"string",playing:"boolean",resize:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",scroll:"number",scrollevents:"boolean",scrollheight:"number",scrollwidth:"number",selectable:"boolean",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",sharpness:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",text:"html",textalign:"string",textdecoration:"string",textindent:"number",thickness:"number",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",xscroll:"number",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression",yscroll:"number"}}},$lzc$class__m7h.attributes)
}}})($lzc$class__m7h)
};Class.make("$lzc$class__m7f",["$m67",function($0){
this.setAttribute("x",-1*this.classroot.cardwidth)
},"$m68",function($0){
var $1=this.classroot.cardwidth;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m69",function(){
try{
return [this.classroot,"cardwidth"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m6a",function($0){
var $1=this.classroot.cardheight;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m6b",function(){
try{
return [this.classroot,"cardheight"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m6c",function($0){
var $1=this.showingfront?this.classroot.frontbgcolor:this.classroot.backbgcolor;if($1!==this["maincolor"]||!this.inited){
this.setAttribute("maincolor",$1)
}},"$m6d",function(){
try{
return [this,"showingfront",this.classroot,"frontbgcolor",this.classroot,"backbgcolor"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m6e",function($0){
this.setAttribute("defaultfontsize",Math.round(this.classroot.cardwidth*0.1))
},"$m6f",function($0){
this.setAttribute("cardwidth",this.classroot.cardwidth)
},"$m6g",function($0){
this.setAttribute("cardheight",this.classroot.cardheight)
},"fronttext",void 0,"backtext",void 0,"$m72",function($0){
this.swivelcard()
},"$classrootdepth",void 0,"$datapath",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],$lzc$class_animatedview,["displayName","<anonymous extends='animatedview'>","children",LzNode.mergeChildren([{attrs:{$classrootdepth:3,align:"center",datapath:"front/text()",fgcolor:new LzAlwaysExpr("$m6i","$m6j",null),fontsize:new LzOnceExpr("$m6k",null),height:new LzAlwaysExpr("$m6l","$m6m",null),innerheight:new LzOnceExpr("$m6r",null),multiline:true,name:"fronttext",resize:true,textalign:"center",valign:"middle",visible:new LzAlwaysExpr("$m6p","$m6q",null),width:new LzAlwaysExpr("$m6n","$m6o",null)},"class":$lzc$class__m7g},{attrs:{$classrootdepth:3,align:"center",datapath:"back/text()",fgcolor:new LzAlwaysExpr("$m6s","$m6t",null),fontsize:new LzOnceExpr("$m6u",null),height:new LzAlwaysExpr("$m6v","$m6w",null),innerheight:new LzOnceExpr("$m71",null),multiline:true,name:"backtext",resize:true,textalign:"center",valign:"middle",visible:new LzAlwaysExpr("$m6z","$m70",null),width:new LzAlwaysExpr("$m6x","$m6y",null)},"class":$lzc$class__m7h}],$lzc$class_animatedview["children"]),"__LZCSSTagSelectors",["animatedview","roundrect","drawview","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_animatedview.attributes)]);{
Class.make("$lzc$class__m7e",["$m63",function($0){
var $1=this.parent.width;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m64",function(){
try{
return [this.parent,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m65",function($0){
var $1=this.parent.height-this.parent.buttonsview.height-10;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m66",function(){
try{
return [this.parent,"height",this.parent.buttonsview,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"cardview",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],LzView,["displayName","<anonymous extends='view'>","children",[{attrs:{$classrootdepth:2,$datapath:{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{axis:"string",classroot:"string",cloneManager:"string",context:"string",datapath:"string",defaultplacement:"string",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",name:"token",nodeLevel:"number",options:"css",p:"string",parent:"string",placement:"string",pooling:"boolean",replication:"string",rerunxpath:"boolean",sortorder:"string",sortpath:"string",spacing:"number",styleclass:"string",subnodes:"string",transition:"string","with":"string",xpath:"string"}},$delegates:["onclones","$m6h",null],$m6h:function($0){
if(!this["doneDel"]){
this.doneDel=new LzDelegate(this,"showFirstCard");this.doneDel.register(this.clones[this.clones.length-1],"oninit")
}},showFirstCard:function($0){
switch(arguments.length){
case 0:
$0=null;

};this.classroot.switchCard(this.getCloneNumber(0))
},xpath:"local:classroot.cards:/stack/card"},"class":LzDatapath},$delegates:["onclick","$m72",null],backtext:void 0,bordersize:10,cardheight:new LzOnceExpr("$m6g",null),cardwidth:new LzOnceExpr("$m6f",null),clickable:true,clip:true,datapath:LzNode._ignoreAttribute,defaultfontsize:new LzOnceExpr("$m6e",null),fronttext:void 0,height:new LzAlwaysExpr("$m6a","$m6b",null),maincolor:new LzAlwaysExpr("$m6c","$m6d",null),name:"cardview",onscreen:false,showingfront:true,valign:"middle",width:new LzAlwaysExpr("$m68","$m69",null),x:new LzOnceExpr("$m67",null)},"class":$lzc$class__m7f}],"__LZCSSTagSelectors",["view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m7e.attributes)
}}})($lzc$class__m7e)
};Class.make("$lzc$class__m7j",["$m77",function($0){
var $1=(this.parent.width-5)/2;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m78",function(){
try{
return [this.parent,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m79",function($0){
Debug.write("started",this.classroot.deckview.cardview.clones[this.classroot.currentIndex]._swiveltransishion.isactive);if(this.classroot.deckview.cardview.clones[this.classroot.currentIndex]._swiveltransishion.isactive){
return
};this.classroot.deckview.cardview.clones[this.classroot.currentIndex].setTransishion("anmSlideAwayLeft");this.classroot.deckview.cardview.clones[this.classroot.currentIndex].setAttribute("onscreen",false);this.classroot.currentIndex--;if(this.classroot.currentIndex<0){
this.classroot.setAttribute("currentIndex",this.classroot.totalCards-1)
};this.classroot.deckview.cardview.clones[this.classroot.currentIndex].setTransishion("anmSlideInLeft");this.classroot.switchCard(this.classroot.deckview.cardview.clones[this.classroot.currentIndex]);Debug.write("currentIndex",this.classroot.currentIndex)
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
}],$lzc$class_button,["displayName","<anonymous extends='button'>","children",LzNode.mergeChildren([],$lzc$class_button["children"]),"__LZCSSTagSelectors",["button","basebutton","basecomponent","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_button.attributes)]);Class.make("$lzc$class__m7k",["$m7a",function($0){
var $1=this.parent.backButton.width;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m7b",function(){
try{
return [this.parent.backButton,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m7c",function($0){
if(this.classroot.deckview.cardview.clones[this.classroot.currentIndex]._swiveltransishion.isactive){
return
};this.classroot.deckview.cardview.clones[this.classroot.currentIndex].setTransishion("anmSlideAwayRight");this.classroot.deckview.cardview.clones[this.classroot.currentIndex].setAttribute("onscreen",false);this.classroot.currentIndex++;if(this.classroot.currentIndex>this.classroot.totalCards-1){
this.classroot.setAttribute("currentIndex",0)
};this.classroot.deckview.cardview.clones[this.classroot.currentIndex].setTransishion("anmSlideInRight");this.classroot.switchCard(this.classroot.deckview.cardview.clones[this.classroot.currentIndex]);Debug.write("currentIndex",this.classroot.currentIndex)
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
}],$lzc$class_button,["displayName","<anonymous extends='button'>","children",LzNode.mergeChildren([],$lzc$class_button["children"]),"__LZCSSTagSelectors",["button","basebutton","basecomponent","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_button.attributes)]);{
Class.make("$lzc$class__m7i",["$m73",function($0){
var $1=this.parent.height-this.height-5;if($1!==this["y"]||!this.inited){
this.setAttribute("y",$1)
}},"$m74",function(){
try{
return [this.parent,"height",this,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m75",function($0){
var $1=this.classroot.cardwidth;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m76",function(){
try{
return [this.classroot,"cardwidth"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"backButton",void 0,"nextButton",void 0,"$classrootdepth",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],LzView,["displayName","<anonymous extends='view'>","children",[{attrs:{$classrootdepth:2,axis:"x",spacing:5},"class":$lzc$class_simplelayout},{attrs:{$classrootdepth:2,$delegates:["onclick","$m79",null],clickable:true,height:30,name:"backButton",text:"Back",width:new LzAlwaysExpr("$m77","$m78",null)},"class":$lzc$class__m7j},{attrs:{$classrootdepth:2,$delegates:["onclick","$m7c",null],clickable:true,height:30,name:"nextButton",text:"Next",valign:"middle",width:new LzAlwaysExpr("$m7a","$m7b",null)},"class":$lzc$class__m7k}],"__LZCSSTagSelectors",["view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m7i.attributes)
}}})($lzc$class__m7i)
};{
Class.make("$lzc$class_flashcards",["cardset",void 0,"randomize",void 0,"showingFront",void 0,"cardElement",void 0,"totalCards",void 0,"currentIndex",void 0,"selectedCard",void 0,"cardwidth",void 0,"cardheight",void 0,"frontfgcolor",void 0,"frontbgcolor",void 0,"backfgcolor",void 0,"backbgcolor",void 0,"cards",void 0,"cardorder",void 0,"deckview",void 0,"buttonsview",void 0,"switchCard",function($0){
if(this.selectedCard==$0)return;if(this.selectedCard!=null){
this.selectedCard.setAttribute("onscreen",false)
};this.setAttribute("selectedCard",$0);this.selectedCard.setAttribute("onscreen",true)
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
}],LzView,["tagname","flashcards","children",[{attrs:{$classrootdepth:1,$delegates:["ondata","$m62",null],name:"cards",request:true,src:new LzAlwaysExpr("$m60","$m61",null),type:"http"},"class":$lzc$class__m7d},{attrs:{$classrootdepth:1,cardview:void 0,height:new LzAlwaysExpr("$m65","$m66",null),initstage:"defer",name:"deckview",width:new LzAlwaysExpr("$m63","$m64",null),y:0},"class":$lzc$class__m7e},{attrs:{$classrootdepth:1,align:"center",backButton:void 0,name:"buttonsview",nextButton:void 0,width:new LzAlwaysExpr("$m75","$m76",null),y:new LzAlwaysExpr("$m73","$m74",null)},"class":$lzc$class__m7i}],"__LZCSSTagSelectors",["flashcards","view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},backbgcolor:0,backfgcolor:0,cardElement:null,cardheight:100,cardorder:null,cardset:null,cardwidth:150,currentIndex:0,frontbgcolor:16711680,frontfgcolor:65280,randomize:true,selectedCard:null,showingFront:true,totalCards:0},$lzc$class_flashcards.attributes)
}}})($lzc$class_flashcards)
};Class.make("$lzc$class__m7x",["$m7l",function($0){
var $1=canvas.cardset;if($1!==this["cardset"]||!this.inited){
this.setAttribute("cardset",$1)
}},"$m7m",function(){
try{
return [canvas,"cardset"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m7n",function($0){
var $1=canvas.cardwidth;if($1!==this["cardwidth"]||!this.inited){
this.setAttribute("cardwidth",$1)
}},"$m7o",function(){
try{
return [canvas,"cardwidth"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m7p",function($0){
var $1=canvas.cardheight;if($1!==this["cardheight"]||!this.inited){
this.setAttribute("cardheight",$1)
}},"$m7q",function(){
try{
return [canvas,"cardheight"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m7r",function($0){
var $1=canvas.height;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m7s",function(){
try{
return [canvas,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m7t",function($0){
var $1=canvas.width;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m7u",function(){
try{
return [canvas,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m7v",function($0){
var $1=canvas.randomize;if($1!==this["randomize"]||!this.inited){
this.setAttribute("randomize",$1)
}},"$m7w",function(){
try{
return [canvas,"randomize"]
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
}],$lzc$class_flashcards,["displayName","<anonymous extends='flashcards'>","children",LzNode.mergeChildren([],$lzc$class_flashcards["children"]),"__LZCSSTagSelectors",["flashcards","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_flashcards.attributes)]);canvas.LzInstantiateView({attrs:{$lzc$bind_id:function($0,$1){
switch(arguments.length){
case 1:
$1=true;

};if($1){
$0.id="flashcards";flashcards=$0
}else if(flashcards===$0){
flashcards=null;$0.id=null
}},align:"center",cardheight:new LzAlwaysExpr("$m7p","$m7q",null),cardset:new LzAlwaysExpr("$m7l","$m7m",null),cardwidth:new LzAlwaysExpr("$m7n","$m7o",null),clip:true,height:new LzAlwaysExpr("$m7r","$m7s",null),id:"flashcards",randomize:new LzAlwaysExpr("$m7v","$m7w",null),width:new LzAlwaysExpr("$m7t","$m7u",null)},"class":$lzc$class__m7x},26);lz["basefocusview"]=$lzc$class_basefocusview;lz["focusoverlay"]=$lzc$class_focusoverlay;lz["_componentmanager"]=$lzc$class__componentmanager;lz["style"]=$lzc$class_style;lz["statictext"]=$lzc$class_statictext;lz["basecomponent"]=$lzc$class_basecomponent;lz["basebutton"]=$lzc$class_basebutton;lz["swatchview"]=$lzc$class_swatchview;lz["button"]=$lzc$class_button;lz["layout"]=LzLayout;lz["simplelayout"]=$lzc$class_simplelayout;lz["roundrect"]=$lzc$class_roundrect;lz["animations"]=$lzc$class_animations;lz["animatedview"]=$lzc$class_animatedview;lz["flashcards"]=$lzc$class_flashcards;canvas.initDone();