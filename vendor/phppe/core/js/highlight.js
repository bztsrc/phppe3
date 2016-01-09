/*
 *   PHP Portal Engine v2.0.0
 *   http://phppe.org/
 *
 *   Copyright 2016 LGPL bzt
 *
 *   Code highlight support
 */

/*PRIVATE VARS*/
var highlight_loaded="";
var highlight_types=new Array();

/*PUBLIC METHODS*/
function highlight_init(width,height){
    var i,obj=document.getElementsByTagName("pre");
    for(i=0;i<obj.length;i++){
	var j,txt="",hl="",cls=obj[i].getAttribute("class");
	if(cls==null || cls=="lineno" || cls.indexOf("nohl")>-1) continue;
	var pre=document.createElement('pre');pre.innerHTML="";
	var pre2=document.createElement('pre');pre2.innerHTML="";
	if(cls!=null && cls!="init" && cls!="dohl" && cls!="skiphl") {
		hl=highlight_dohl(cls,obj[i]);
		if(window["highlight_"+cls]!=null) window["highlight_"+cls](obj[i]);
	}
	var numlines=hl.split("\n").length+(hl.substr(-1)=='\n'?-1:0);
	for(j=0;j<numlines;j++) txt+=(j+1)+"\n";
	pre.setAttribute("class","lineno");
	pre.appendChild(document.createTextNode(txt));
	pre2.setAttribute("class","skiphl");
	pre2.innerHTML=hl;
	var div=document.createElement("div");
	div.setAttribute("style","overflow:auto;"+(obj[i].title&&obj[i].title!=""?obj[i].title+";":("width:"+(width>0?width:obj[i].offsetWidth)+"px;height:"+(height>0?height:obj[i].offsetHeight)+"px;")));
	div.appendChild(pre);
	div.appendChild(pre2);
	obj[i].parentNode.replaceChild(div,obj[i]);
	i++;
    }
}

/*PRIVATE METHODS*/
function highlight_dohl(cls,obj) {
	var i,j,k,txt=obj.innerHTML,ret="";
	var c=highlight_types[cls];
	if(c==null) return;
	var reg=(c.variables!=null?new RegExp(c.variables[0]):null);
	for(i=0;i<txt.length;i++) {
		if(txt.substr(i,4)=="<br>") {
		    i+=3;
		    ret+="\n";
		} else
		if(txt.substr(i,5)=="<br/>") {
		    i+=4;
		    ret+="\n";
		} else
		if(txt.substr(i,1)=="&") {
			if(txt.substr(i,2)=="&&") { ret+="<span class='condition'>&amp;&amp;</span>"; i++; was=1; } else
			if(txt.substr(i,10)=="&amp;&amp;") { ret+="<span class='condition'>&amp;&amp;</span>"; i+=9; was=1; } else
			{
			    j=i;
			    while(i<txt.length&&txt.substr(i,1)!=";")i++;
			    ret+=txt.substr(j,i-j);
			}
		} else
		if(txt.substr(i,c.comment[0].length)==c.comment[0]) {
			j=i;
			while(i<txt.length&&txt.substr(i,1)!="\n")i++;
			ret+="<span class='comment'>"+txt.substr(j,i-j)+"</span>";
			i--;
		} else
		if(c.comment[2] && txt.substr(i,c.comment[0].length)==c.comment[1]) {
			j=i;
			while(i<txt.length&&txt.substr(i,c.comment[2].length)!=c.comment[2])i++;
			ret+="<span class='comment'>"+txt.substr(j,i-j)+c.comment[2]+"</span>";
			i+=c.comment[2].length; i--;
		} else
		if(c.stringconst.length && c.stringconst.indexOf(txt.substr(i,1))>-1) {
			var term=txt.substr(i,1);
			j=i++;
			while(i<txt.length&&txt.substr(i,1)!=term) { if(txt.substr(i,1)=="\\") i++; i++; }
			ret+="<span class='stringconst'>"+txt.substr(j,i-j)+term+"</span>";
		} else
		if(c.index[1] && txt.substr(i,c.index[0].length)==c.index[0]) {
			i+=c.index[0].length;
			j=i;
			while(i<txt.length&&txt.substr(i,c.index[1].length)!=c.index[1])i++;
			while(i+1<txt.length&&txt.substr(i+1,c.index[1].length)==c.index[1])i++;
			ret+="<span class='type'>"+c.index[0]+"</span><span class='index'>"+txt.substr(j,i-j)+"</span><span class='type'>"+c.index[1]+"</span>";
		} else
		if(c.index[1] && txt.substr(i,c.index[1].length)==c.index[1]) {
			ret+="</span>"+c.index[1];
		} else
		if(txt.substr(i,1).match(/[0-9]+/)) {
			j=i;
			while(i<txt.length&&txt.substr(i,1).match(/[0-9\.\,\ \+\-a-fA-F]+/))i++;
			if(i!=j) { ret+="<span class='number'>"+txt.substr(j,i-j)+"</span>";i--; }
		} else {
			var was=0,groups=new Array("keyword","type","control","condition","let","operator");
			for(j=0;j<groups.length;j++) {
				if(j>=0&&j<groups.length&&c[groups[j]+"s"]!=null)
				  for(k=0;k<c[groups[j]+"s"].length;k++) {
					if(c[groups[j]+"s"][k].length>0 && txt.substr(i,c[groups[j]+"s"][k].length)==c[groups[j]+"s"][k]) {
						ret+="<span class='"+groups[j]+"'>"+c[groups[j]+"s"][k]+"</span>";
						i+=c[groups[j]+"s"][k].length;
						j=groups.length; was=1; i--; break;
					}
				}
			}
			if(!was&&reg!=null&&((c.variables[1]&&txt.substr(i,c.variables[1].length)==c.variables[1])||txt.substr(i,1).match(reg))) {
			    j=i;i++;
			    while(i<txt.length&&txt.substr(i,1).match(reg))i++;
			    if(i!=j) { ret+="<span class='"+(txt.substr(i,1)=='('?"function":"variable")+"'>"+txt.substr(j,i-j)+"</span>"; was=1; i--; }
			}
			if(!was)
			    ret+=txt.substr(i,1);
		}
	}
	return ret;
}