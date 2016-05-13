LzResourceLibrary.dice_resource={ptype:"ar",frames:['dicepics/dice-1.png','dicepics/dice-2.png','dicepics/dice-3.png','dicepics/dice-4.png','dicepics/dice-5.png','dicepics/dice-6.png'],width:557,height:557,sprite:'dicepics/dice-1.sprite.png',spriteoffset:0};LzResourceLibrary.__allcss={path:'usr/local/red5/webapps/openlaszlo/my-apps/dice/dice.sprite.png'};;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;{
Class.make("$lzc$class__m6",["$m4",function($0){
this.setAttribute("dicecount",lz.Browser.getInitArg("dicecount"))
},"dicecount",void 0,"$m5",function($0){
this.setAttribute("dicesize",lz.Browser.getInitArg("dicesize"))
},"dicesize",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",accessible:"boolean",align:"string",allowfullscreen:"boolean",appbuilddate:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",compileroptions:"string",contextmenu:"string",cornerradius:"string",cursor:"token",dataloadtimeout:"numberExpression",datapath:"string",datasets:"string",debug:"boolean",defaultdataprovider:"string",defaultplacement:"string",embedfonts:"boolean",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framerate:"number",framesloadratio:"number",fullscreen:"boolean",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",history:"boolean",httpdataprovider:"string",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",lpsbuild:"string",lpsbuilddate:"string",lpsrelease:"string",lpsversion:"string",mask:"string",mediaerrortimeout:"numberExpression",medialoadtimeout:"numberExpression",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",percentcreated:"number",pixellock:"boolean",placement:"string",playing:"boolean",proxied:"inheritableBoolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",runtime:"string",screenorientation:"boolean",scriptlimits:"css",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",title:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__m6.attributes)
}}})($lzc$class__m6)
};canvas=new $lzc$class__m6(null,{__LZproxied:"false",appbuilddate:"2011-12-03T12:16:34Z",bgcolor:16777215,dicecount:new LzOnceExpr("$m4",null),dicesize:new LzOnceExpr("$m5",null),embedfonts:true,font:"Verdana,Vera,sans-serif",fontsize:11,fontstyle:"plain",height:"100%",lpsbuild:"trunk@19126 (19126)",lpsbuilddate:"2011-04-30T08:09:13Z",lpsrelease:"Latest",lpsversion:"5.0.x",runtime:"dhtml",width:"100%"});Mixin.make("DrawviewShared",["$lzsc$initialize",function($0,$1,$2,$3){
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
Class.make("$lzc$class__mo",["$mm",function($0){
this.setAttribute("width",this.parent.width)
},"$mn",function($0){
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
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}}},$lzc$class__mo.attributes)
}}})($lzc$class__mo)
};{
Class.make("$lzc$class_roundrect",["inset",void 0,"$lzc$set_inset",function($0){
this.setInset($0)
},"oninset",void 0,"$m7",function($0){
var $1=null;if($1!==this["insetleft"]||!this.inited){
this.setAttribute("insetleft",$1)
}},"$m8",function(){
try{
return []
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"insetleft",void 0,"$lzc$set_insetleft",function($0){
this.setInsetLeft($0)
},"oninsetleft",void 0,"$m9",function($0){
var $1=null;if($1!==this["insetright"]||!this.inited){
this.setAttribute("insetright",$1)
}},"$ma",function(){
try{
return []
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"insetright",void 0,"$lzc$set_insetright",function($0){
this.setInsetRight($0)
},"oninsetright",void 0,"$mb",function($0){
var $1=null;if($1!==this["insettop"]||!this.inited){
this.setAttribute("insettop",$1)
}},"$mc",function(){
try{
return []
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"insettop",void 0,"$lzc$set_insettop",function($0){
this.setInsetTop($0)
},"oninsettop",void 0,"$md",function($0){
var $1=null;if($1!==this["insetbottom"]||!this.inited){
this.setAttribute("insetbottom",$1)
}},"$me",function(){
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
},"$mf",function($0){
if(this.context)this.drawStructure()
},"$mg",function($0){
if(this.context)this.drawStructure()
},"borderWidth",void 0,"borderRadius",void 0,"borderColor",void 0,"borderOpacity",void 0,"$mh",function($0){
this.setAttribute("backgroundStartColor",null)
},"backgroundStartColor",void 0,"$mi",function($0){
this.setAttribute("backgroundStopColor",null)
},"backgroundStopColor",void 0,"backgroundStartOpacity",void 0,"backgroundStopOpacity",void 0,"backgroundGradientOrientation",void 0,"boxShadowX",void 0,"boxShadowY",void 0,"$mj",function($0){
this.setAttribute("boxShadowColor",null)
},"boxShadowColor",void 0,"boxShadowOpacity",void 0,"$mk",function($0){
if(this.context)this.drawStructure()
},"$ml",function($0){
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
}],$lzc$class_drawview,["tagname","roundrect","children",LzNode.mergeChildren([{attrs:{$classrootdepth:1,height:new LzOnceExpr("$mn",null),name:"content",width:new LzOnceExpr("$mm",null),x:0,y:0},"class":$lzc$class__mo},{attrs:"content","class":$lzc$class_userClassPlacement}],$lzc$class_drawview["children"]),"__LZCSSTagSelectors",["roundrect","drawview","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_drawview.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",aliaslines:"boolean",align:"string",backgroundGradientOrientation:"string",backgroundStartColor:"color",backgroundStopColor:"color",backgroundrepeat:"string",bgcolor:"color",borderColor:"color",boxShadowColor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",fillStyle:"string",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",globalAlpha:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",inset:"size",insetbottom:"size",insetleft:"size",insetright:"size",insettop:"size",isinited:"boolean",layout:"css",lineCap:"string",lineJoin:"string",lineWidth:"number",loadratio:"number",mask:"string",measuresize:"boolean",miterLimit:"number",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",strokeStyle:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$delegates:["onwidth","$mf",null,"onheight","$mg",null,"oninit","$mk",null,"oncontext","$ml",null],backgroundGradientOrientation:"vertical",backgroundStartColor:new LzOnceExpr("$mh",null),backgroundStartOpacity:1,backgroundStopColor:new LzOnceExpr("$mi",null),backgroundStopOpacity:1,borderColor:0,borderOpacity:1,borderRadius:5,borderWidth:1,boxShadowColor:new LzOnceExpr("$mj",null),boxShadowOpacity:0.5,boxShadowX:5,boxShadowY:5,height:100,inset:5,insetbottom:new LzAlwaysExpr("$md","$me",null),insetleft:new LzAlwaysExpr("$m7","$m8",null),insetright:new LzAlwaysExpr("$m9","$ma",null),insettop:new LzAlwaysExpr("$mb","$mc",null),oninset:null,oninsetbottom:null,oninsetleft:null,oninsetright:null,oninsettop:null,width:100},$lzc$class_roundrect.attributes)
}}})($lzc$class_roundrect)
};{
Class.make("$lzc$class_diceclass",["$mp",function($0){
var $1=this.width/2;if($1!==this["xoffset"]||!this.inited){
this.setAttribute("xoffset",$1)
}},"$mq",function(){
try{
return [this,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$mr",function($0){
var $1=this.height/2;if($1!==this["yoffset"]||!this.inited){
this.setAttribute("yoffset",$1)
}},"$ms",function(){
try{
return [this,"height"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$mt",function($0){
var $1=(this.spinner+1)/6;if($1!==this["frame"]||!this.inited){
this.setAttribute("frame",$1)
}},"$mu",function(){
try{
return [this,"spinner"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"degree",void 0,"spinner",void 0,"seed",void 0,"diceAnim",void 0,"roll",function(){
if(this.diceAnim.isActive)return;Debug.write("spinner is: ",this.spinner);this.diceAnim.animrotation.setAttribute("to",this.randget(3601));this.diceAnim.animspinner.setAttribute("to",this.randget(37));this.diceAnim.doStart()
},"randget",function($0){
return Math.floor(Math.random()*$0)
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
}],LzView,["tagname","diceclass","children",[{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}},$classrootdepth:1,animrotation:void 0,animspinner:void 0,name:"diceAnim",process:"simultaneous",start:false},children:[{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}},$classrootdepth:2,attribute:"rotation",duration:500,from:0,motion:"easeout",name:"animrotation",relative:false,to:3600},"class":LzAnimator},{attrs:{$CSSDescriptor:{},$attributeDescriptor:{types:{attribute:"token",classroot:"string",cloneManager:"string",datapath:"string",defaultplacement:"string",duration:"number",from:"number",id:"ID",ignoreplacement:"boolean",immediateparent:"string",indirect:"boolean",inited:"boolean",initstage:"string",isactive:"boolean",isinited:"boolean",motion:"string",name:"token",nodeLevel:"number",options:"css",parent:"string",paused:"boolean",placement:"string",process:"string",relative:"boolean",repeat:"number",start:"boolean",started:"boolean",styleclass:"string",subnodes:"string",target:"reference",to:"number",transition:"string","with":"string"}},$classrootdepth:2,attribute:"spinner",duration:400,from:0,motion:"easeout",name:"animspinner",relative:false,to:37},"class":LzAnimator}],"class":LzAnimatorGroup}],"__LZCSSTagSelectors",["diceclass","view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},degree:0,frame:new LzAlwaysExpr("$mt","$mu",null),resource:"dice_resource",seed:0,spinner:0,stretches:"both",xoffset:new LzAlwaysExpr("$mp","$mq",null),yoffset:new LzAlwaysExpr("$mr","$ms",null)},$lzc$class_diceclass.attributes)
}}})($lzc$class_diceclass)
};Class.make("$lzc$class__m1l",["$mx",function($0){
var $1=this.classroot.dicesize;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$my",function(){
try{
return [this.classroot,"dicesize"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$mz",function($0){
var $1=this.classroot.dicesize;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m10",function(){
try{
return [this.classroot,"dicesize"]
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
}],$lzc$class_diceclass,["displayName","<anonymous extends='diceclass'>","children",LzNode.mergeChildren([],$lzc$class_diceclass["children"]),"__LZCSSTagSelectors",["diceclass","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_diceclass.attributes)]);Class.make("$lzc$class__m1m",["$m11",function($0){
var $1=this.classroot.dicesize;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m12",function(){
try{
return [this.classroot,"dicesize"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m13",function($0){
var $1=this.classroot.dicesize;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m14",function(){
try{
return [this.classroot,"dicesize"]
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
}],$lzc$class_diceclass,["displayName","<anonymous extends='diceclass'>","children",LzNode.mergeChildren([],$lzc$class_diceclass["children"]),"__LZCSSTagSelectors",["diceclass","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_diceclass.attributes)]);Class.make("$lzc$class__m1n",["$m15",function($0){
var $1=this.classroot.dicesize;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m16",function(){
try{
return [this.classroot,"dicesize"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m17",function($0){
var $1=this.classroot.dicesize;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m18",function(){
try{
return [this.classroot,"dicesize"]
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
}],$lzc$class_diceclass,["displayName","<anonymous extends='diceclass'>","children",LzNode.mergeChildren([],$lzc$class_diceclass["children"]),"__LZCSSTagSelectors",["diceclass","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_diceclass.attributes)]);Class.make("$lzc$class__m1o",["$m19",function($0){
var $1=this.classroot.dicesize;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m1a",function(){
try{
return [this.classroot,"dicesize"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m1b",function($0){
var $1=this.classroot.dicesize;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m1c",function(){
try{
return [this.classroot,"dicesize"]
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
}],$lzc$class_diceclass,["displayName","<anonymous extends='diceclass'>","children",LzNode.mergeChildren([],$lzc$class_diceclass["children"]),"__LZCSSTagSelectors",["diceclass","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_diceclass.attributes)]);Class.make("$lzc$class__m1p",["$m1d",function($0){
var $1=this.classroot.dicesize;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m1e",function(){
try{
return [this.classroot,"dicesize"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m1f",function($0){
var $1=this.classroot.dicesize;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m1g",function(){
try{
return [this.classroot,"dicesize"]
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
}],$lzc$class_diceclass,["displayName","<anonymous extends='diceclass'>","children",LzNode.mergeChildren([],$lzc$class_diceclass["children"]),"__LZCSSTagSelectors",["diceclass","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_diceclass.attributes)]);Class.make("$lzc$class__m1q",["$m1h",function($0){
var $1=this.classroot.dicesize;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m1i",function(){
try{
return [this.classroot,"dicesize"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m1j",function($0){
var $1=this.classroot.dicesize;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m1k",function(){
try{
return [this.classroot,"dicesize"]
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
}],$lzc$class_diceclass,["displayName","<anonymous extends='diceclass'>","children",LzNode.mergeChildren([],$lzc$class_diceclass["children"]),"__LZCSSTagSelectors",["diceclass","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_diceclass.attributes)]);{
Class.make("$lzc$class_diceview",["dicecount",void 0,"dicesize",void 0,"usepresets",void 0,"fetchdicepos",function($0,$1){
var $2=this.dicesize/4*3;var $3=this.dicesize/3;switch($1){
case "x":
return $2+($0-1)*(parseInt(this.dicesize)+parseInt($3));
case "y":
return $2;break;

}},"$mv",function($0){
Debug.write("entering loop");var $1=this.dicesize/4*3;var $2=this.dicesize/4;for(var $3=0;$3<this.dicecount;$3++){
switch($3){
case 0:
this.diceone.setAttribute("visible",true);this.diceone.setAttribute("y",$1);this.diceone.setAttribute("x",this.fetchdicepos(1,"x"));this.diceone.roll();Debug.write("diceone x:",this.diceone.x);Debug.write("diceone should be visible now");break;
case 1:
this.dicetwo.setAttribute("visible",true);this.dicetwo.setAttribute("y",$1);this.dicetwo.setAttribute("x",this.fetchdicepos(2,"x"));this.dicetwo.roll();Debug.write("dicetwo x:",this.dicetwo.x);Debug.write("dicetwo should be visible now");break;
case 2:
this.dicethree.setAttribute("visible",true);this.dicethree.setAttribute("y",$1);this.dicethree.setAttribute("x",this.fetchdicepos(3,"x"));this.dicethree.roll();Debug.write("dicethree x:",this.dicethree.x);Debug.write("dicethree should be visible now");break;
case 3:
this.dicefour.setAttribute("visible",true);this.dicefour.setAttribute("y",$1);this.dicefour.setAttribute("x",this.fetchdicepos(4,"x"));this.dicefour.roll();Debug.write("dicefour should be visible now");break;
case 4:
this.dicefive.setAttribute("visible",true);this.dicefive.setAttribute("y",$1);this.dicefive.setAttribute("x",this.fetchdicepos(5,"x"));this.dicefive.roll();Debug.write("dicefive should be visible now");break;
case 5:
this.dicesix.setAttribute("visible",true);this.dicesix.setAttribute("y",$1);this.dicesix.setAttribute("x",this.fetchdicepos(6,"x"));this.dicesix.roll();Debug.write("dicesix should be visible now");break;

}}},"$mw",function($0){
this.diceone.roll();this.dicetwo.roll();this.dicethree.roll();this.dicefour.roll();this.dicefive.roll();this.dicesix.roll()
},"diceone",void 0,"dicetwo",void 0,"dicethree",void 0,"dicefour",void 0,"dicefive",void 0,"dicesix",void 0,"$lzsc$initialize",function($0,$1,$2,$3){
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
}],LzView,["tagname","diceview","children",[{attrs:{$classrootdepth:1,height:new LzAlwaysExpr("$mz","$m10",null),name:"diceone",visible:false,width:new LzAlwaysExpr("$mx","$my",null)},"class":$lzc$class__m1l},{attrs:{$classrootdepth:1,height:new LzAlwaysExpr("$m13","$m14",null),name:"dicetwo",visible:false,width:new LzAlwaysExpr("$m11","$m12",null)},"class":$lzc$class__m1m},{attrs:{$classrootdepth:1,height:new LzAlwaysExpr("$m17","$m18",null),name:"dicethree",visible:false,width:new LzAlwaysExpr("$m15","$m16",null)},"class":$lzc$class__m1n},{attrs:{$classrootdepth:1,height:new LzAlwaysExpr("$m1b","$m1c",null),name:"dicefour",visible:false,width:new LzAlwaysExpr("$m19","$m1a",null)},"class":$lzc$class__m1o},{attrs:{$classrootdepth:1,height:new LzAlwaysExpr("$m1f","$m1g",null),name:"dicefive",visible:false,width:new LzAlwaysExpr("$m1d","$m1e",null)},"class":$lzc$class__m1p},{attrs:{$classrootdepth:1,height:new LzAlwaysExpr("$m1j","$m1k",null),name:"dicesix",visible:false,width:new LzAlwaysExpr("$m1h","$m1i",null)},"class":$lzc$class__m1q}],"__LZCSSTagSelectors",["diceview","view","node","Instance"],"attributes",new LzInheritedHash(LzView.attributes)]);(function($0){
with($0)with($0.prototype){
{
LzNode.mergeAttributes({$CSSDescriptor:{},$attributeDescriptor:{types:{aaactive:"boolean",aadescription:"string",aaname:"string",aasilent:"boolean",aatabindex:"number",align:"string",backgroundrepeat:"string",bgcolor:"color",cachebitmap:"boolean",capabilities:"string",classroot:"string",clickable:"boolean",clickregion:"string",clip:"boolean",cloneManager:"string",contextmenu:"string",cornerradius:"string",cursor:"token",datapath:"string",defaultplacement:"string",fgcolor:"color",focusable:"boolean",focustrap:"boolean",font:"string",fontsize:"size",fontstyle:"string",frame:"numberExpression",framesloadratio:"number",hasdirectionallayout:"boolean",hassetheight:"boolean",hassetwidth:"boolean",height:"size",id:"ID",ignoreplacement:"boolean",immediateparent:"string",inited:"boolean",initstage:"string",isinited:"boolean",layout:"css",loadratio:"number",mask:"string",name:"token",nodeLevel:"number",opacity:"number",options:"css",parent:"string",pixellock:"boolean",placement:"string",playing:"boolean",resource:"string",resourceheight:"number",resourcewidth:"number",rotation:"numberExpression",shadowangle:"number",shadowblurradius:"number",shadowcolor:"color",shadowdistance:"number",showhandcursor:"boolean",source:"string",stretches:"string",styleclass:"string",subnodes:"string",subviews:"string",tintcolor:"string",totalframes:"number",transition:"string",unstretchedheight:"number",unstretchedwidth:"number",usegetbounds:"boolean",valign:"string",visibility:"string",visible:"boolean",width:"size","with":"string",x:"numberExpression",xoffset:"numberExpression",xscale:"numberExpression",y:"numberExpression",yoffset:"numberExpression",yscale:"numberExpression"}},$delegates:["oninit","$mv",null,"onclick","$mw",null],clickable:true,dicecount:3,dicesize:200,usepresets:false},$lzc$class_diceview.attributes)
}}})($lzc$class_diceview)
};Class.make("$lzc$class__m1z",["$m1r",function($0){
var $1=canvas.dicecount;if($1!==this["dicecount"]||!this.inited){
this.setAttribute("dicecount",$1)
}},"$m1s",function(){
try{
return [canvas,"dicecount"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m1t",function($0){
var $1=canvas.dicesize;if($1!==this["dicesize"]||!this.inited){
this.setAttribute("dicesize",$1)
}},"$m1u",function(){
try{
return [canvas,"dicesize"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m1v",function($0){
var $1=this.parent.width;if($1!==this["width"]||!this.inited){
this.setAttribute("width",$1)
}},"$m1w",function(){
try{
return [this.parent,"width"]
}
catch($lzsc$e){
if(Error["$lzsc$isa"]?Error.$lzsc$isa($lzsc$e):$lzsc$e instanceof Error){
lz.$lzsc$thrownError=$lzsc$e
};throw $lzsc$e
}},"$m1x",function($0){
var $1=this.parent.height;if($1!==this["height"]||!this.inited){
this.setAttribute("height",$1)
}},"$m1y",function(){
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
}],$lzc$class_diceview,["displayName","<anonymous extends='diceview'>","children",LzNode.mergeChildren([],$lzc$class_diceview["children"]),"__LZCSSTagSelectors",["diceview","view","node","Instance"],"attributes",new LzInheritedHash($lzc$class_diceview.attributes)]);canvas.LzInstantiateView({attrs:{dicecount:new LzAlwaysExpr("$m1r","$m1s",null),dicesize:new LzAlwaysExpr("$m1t","$m1u",null),height:new LzAlwaysExpr("$m1x","$m1y",null),usepresets:false,width:new LzAlwaysExpr("$m1v","$m1w",null)},"class":$lzc$class__m1z},25);lz["roundrect"]=$lzc$class_roundrect;lz["diceclass"]=$lzc$class_diceclass;lz["diceview"]=$lzc$class_diceview;canvas.initDone();