/*!
 * jQuery Form Plugin
 * version: 2.92 (22-NOV-2011)
 * @requires jQuery v1.3.2 or later
 *
 * Examples and documentation at: http://malsup.com/jquery/form/
 * Dual licensed under the MIT and GPL licenses:
 *	http://www.opensource.org/licenses/mit-license.php
 *	http://www.gnu.org/licenses/gpl.html
 */
(function(b){b.fn.ajaxSubmit=function(d){if(!this.length){a("ajaxSubmit: skipping submit process - no element selected");return this}var c,t,f,h=this;if(typeof d=="function"){d={success:d}}c=this.attr("method");t=this.attr("action");f=(typeof t==="string")?b.trim(t):"";f=f||window.location.href||"";if(f){f=(f.match(/^([^#]+)/)||[])[1]}d=b.extend(true,{url:f,success:b.ajaxSettings.success,type:c||"GET",iframeSrc:/^https/i.test(window.location.href||"")?"javascript:false":"about:blank"},d);var m={};this.trigger("form-pre-serialize",[this,d,m]);if(m.veto){a("ajaxSubmit: submit vetoed via form-pre-serialize trigger");return this}if(d.beforeSerialize&&d.beforeSerialize(this,d)===false){a("ajaxSubmit: submit aborted via beforeSerialize callback");return this}var g=d.traditional;if(g===undefined){g=b.ajaxSettings.traditional}var x,s,j,y=this.formToArray(d.semantic);if(d.data){d.extraData=d.data;x=b.param(d.data,g)}if(d.beforeSubmit&&d.beforeSubmit(y,this,d)===false){a("ajaxSubmit: submit aborted via beforeSubmit callback");return this}this.trigger("form-submit-validate",[y,this,d,m]);if(m.veto){a("ajaxSubmit: submit vetoed via form-submit-validate trigger");return this}var r=b.param(y,g);if(x){r=(r?(r+"&"+x):x)}if(d.type.toUpperCase()=="GET"){d.url+=(d.url.indexOf("?")>=0?"&":"?")+r;d.data=null}else{d.data=r}var A=[];if(d.resetForm){A.push(function(){h.resetForm()})}if(d.clearForm){A.push(function(){h.clearForm(d.includeHidden)})}if(!d.dataType&&d.target){var e=d.success||function(){};A.push(function(q){var n=d.replaceTarget?"replaceWith":"html";b(d.target)[n](q).each(e,arguments)})}else{if(d.success){A.push(d.success)}}d.success=function(C,q,D){var B=d.context||d;for(var v=0,n=A.length;v<n;v++){A[v].apply(B,[C,q,D||h,h])}};var w=b("input:file:enabled[value]",this);var i=w.length>0;var u="multipart/form-data";var p=(h.attr("enctype")==u||h.attr("encoding")==u);var o=!!(i&&w.get(0).files&&window.FormData);a("fileAPI :"+o);var k=(i||p)&&!o;if(d.iframe!==false&&(d.iframe||k)){if(d.closeKeepAlive){b.get(d.closeKeepAlive,function(){z(y)})}else{z(y)}}else{if((i||p)&&o){d.progress=d.progress||b.noop;l(y)}else{b.ajax(d)}}this.trigger("form-submit-notify",[this,d]);return this;function l(v){var n=new FormData();for(var B=0;B<v.length;B++){if(v[B].type=="file"){continue}n.append(v[B].name,v[B].value)}h.find("input:file:enabled").each(function(){var C=b(this).attr("name"),E=this.files;if(C){for(var D=0;D<E.length;D++){n.append(C,E[D])}}});d.data=null;var q=d.beforeSend;d.beforeSend=function(D,C){C.data=n;if(D.upload){D.upload.onprogress=function(E){C.progress(E.position,E.total)}}if(q){q.call(C,D,C)}};b.ajax(d)}function z(Z){var E=h[0],D,V,P,X,S,G,K,I,J,T,W,N;var H=!!b.fn.prop;if(Z){if(H){for(V=0;V<Z.length;V++){D=b(E[Z[V].name]);D.prop("disabled",false)}}else{for(V=0;V<Z.length;V++){D=b(E[Z[V].name]);D.removeAttr("disabled")}}}if(b(":input[name=submit],:input[id=submit]",E).length){alert('Error: Form elements must not have name or id of "submit".');return}P=b.extend(true,{},b.ajaxSettings,d);P.context=P.context||P;S="jqFormIO"+(new Date().getTime());if(P.iframeTarget){G=b(P.iframeTarget);T=G.attr("name");if(T==null){G.attr("name",S)}else{S=T}}else{G=b('<iframe name="'+S+'" src="'+P.iframeSrc+'" />');G.css({position:"absolute",top:"-1000px",left:"-1000px"})}K=G[0];I={aborted:0,responseText:null,responseXML:null,status:0,statusText:"n/a",getAllResponseHeaders:function(){},getResponseHeader:function(){},setRequestHeader:function(){},abort:function(n){var ac=(n==="timeout"?"timeout":"aborted");a("aborting upload... "+ac);this.aborted=1;G.attr("src",P.iframeSrc);I.error=ac;P.error&&P.error.call(P.context,I,ac,n);X&&b.event.trigger("ajaxError",[I,P,ac]);P.complete&&P.complete.call(P.context,I,ac)}};X=P.global;if(X&&!b.active++){b.event.trigger("ajaxStart")}if(X){b.event.trigger("ajaxSend",[I,P])}if(P.beforeSend&&P.beforeSend.call(P.context,I,P)===false){if(P.global){b.active--}return}if(I.aborted){return}J=E.clk;if(J){T=J.name;if(T&&!J.disabled){P.extraData=P.extraData||{};P.extraData[T]=J.value;if(J.type=="image"){P.extraData[T+".x"]=E.clk_x;P.extraData[T+".y"]=E.clk_y}}}var O=1;var L=2;function M(ac){var n=ac.contentWindow?ac.contentWindow.document:ac.contentDocument?ac.contentDocument:ac.document;return n}var C=b("meta[name=csrf-token]").attr("content");var B=b("meta[name=csrf-param]").attr("content");if(B&&C){P.extraData=P.extraData||{};P.extraData[B]=C}function U(){var ae=h.attr("target"),ac=h.attr("action");E.setAttribute("target",S);if(!c){E.setAttribute("method","POST")}if(ac!=P.url){E.setAttribute("action",P.url)}if(!P.skipEncodingOverride&&(!c||/post/i.test(c))){h.attr({encoding:"multipart/form-data",enctype:"multipart/form-data"})}if(P.timeout){N=setTimeout(function(){W=true;R(O)},P.timeout)}function af(){try{var n=M(K).readyState;a("state = "+n);if(n.toLowerCase()=="uninitialized"){setTimeout(af,50)}}catch(ah){a("Server abort: ",ah," (",ah.name,")");R(L);N&&clearTimeout(N);N=undefined}}var ad=[];try{if(P.extraData){for(var ag in P.extraData){ad.push(b('<input type="hidden" name="'+ag+'">').attr("value",P.extraData[ag]).appendTo(E)[0])}}if(!P.iframeTarget){G.appendTo("body");K.attachEvent?K.attachEvent("onload",R):K.addEventListener("load",R,false)}setTimeout(af,15);E.submit()}finally{E.setAttribute("action",ac);if(ae){E.setAttribute("target",ae)}else{h.removeAttr("target")}b(ad).remove()}}if(P.forceSync){U()}else{setTimeout(U,10)}var aa,ab,Y=50,F;function R(ag){if(I.aborted||F){return}try{ab=M(K)}catch(aj){a("cannot access response document: ",aj);ag=L}if(ag===O&&I){I.abort("timeout");return}else{if(ag==L&&I){I.abort("server abort");return}}if(!ab||ab.location.href==P.iframeSrc){if(!W){return}}K.detachEvent?K.detachEvent("onload",R):K.removeEventListener("load",R,false);var ae="success",ai;try{if(W){throw"timeout"}var ad=P.dataType=="xml"||ab.XMLDocument||b.isXMLDoc(ab);a("isXml="+ad);if(!ad&&window.opera&&(ab.body==null||ab.body.innerHTML=="")){if(--Y){a("requeing onLoad callback, DOM not available");setTimeout(R,250);return}}var ak=ab.body?ab.body:ab.documentElement;I.responseText=ak?ak.innerHTML:null;I.responseXML=ab.XMLDocument?ab.XMLDocument:ab;if(ad){P.dataType="xml"}I.getResponseHeader=function(an){var am={"content-type":P.dataType};return am[an]};if(ak){I.status=Number(ak.getAttribute("status"))||I.status;I.statusText=ak.getAttribute("statusText")||I.statusText}var n=(P.dataType||"").toLowerCase();var ah=/(json|script|text)/.test(n);if(ah||P.textarea){var af=ab.getElementsByTagName("textarea")[0];if(af){I.responseText=af.value;I.status=Number(af.getAttribute("status"))||I.status;I.statusText=af.getAttribute("statusText")||I.statusText}else{if(ah){var ac=ab.getElementsByTagName("pre")[0];var al=ab.getElementsByTagName("body")[0];if(ac){I.responseText=ac.textContent?ac.textContent:ac.innerText}else{if(al){I.responseText=al.textContent?al.textContent:al.innerText}}}}}else{if(n=="xml"&&!I.responseXML&&I.responseText!=null){I.responseXML=Q(I.responseText)}}try{aa=q(I,n,P)}catch(ag){ae="parsererror";I.error=ai=(ag||ae)}}catch(ag){a("error caught: ",ag);ae="error";I.error=ai=(ag||ae)}if(I.aborted){a("upload aborted");ae=null}if(I.status){ae=(I.status>=200&&I.status<300||I.status===304)?"success":"error"}if(ae==="success"){P.success&&P.success.call(P.context,aa,"success",I);X&&b.event.trigger("ajaxSuccess",[I,P])}else{if(ae){if(ai==undefined){ai=I.statusText}P.error&&P.error.call(P.context,I,ae,ai);X&&b.event.trigger("ajaxError",[I,P,ai])}}X&&b.event.trigger("ajaxComplete",[I,P]);if(X&&!--b.active){b.event.trigger("ajaxStop")}P.complete&&P.complete.call(P.context,I,ae);F=true;if(P.timeout){clearTimeout(N)}setTimeout(function(){if(!P.iframeTarget){G.remove()}I.responseXML=null},100)}var Q=b.parseXML||function(n,ac){if(window.ActiveXObject){ac=new ActiveXObject("Microsoft.XMLDOM");ac.async="false";ac.loadXML(n)}else{ac=(new DOMParser()).parseFromString(n,"text/xml")}return(ac&&ac.documentElement&&ac.documentElement.nodeName!="parsererror")?ac:null};var v=b.parseJSON||function(n){return window["eval"]("("+n+")")};var q=function(ag,ae,ad){var ac=ag.getResponseHeader("content-type")||"",n=ae==="xml"||!ae&&ac.indexOf("xml")>=0,af=n?ag.responseXML:ag.responseText;if(n&&af.documentElement.nodeName==="parsererror"){b.error&&b.error("parsererror")}if(ad&&ad.dataFilter){af=ad.dataFilter(af,ae)}if(typeof af==="string"){if(ae==="json"||!ae&&ac.indexOf("json")>=0){af=v(af)}else{if(ae==="script"||!ae&&ac.indexOf("javascript")>=0){b.globalEval(af)}}}return af}}};b.fn.ajaxForm=function(c){if(this.length===0){var d={s:this.selector,c:this.context};if(!b.isReady&&d.s){a("DOM not ready, queuing ajaxForm");b(function(){b(d.s,d.c).ajaxForm(c)});return this}a("terminating; zero elements found by selector"+(b.isReady?"":" (DOM not ready)"));return this}return this.ajaxFormUnbind().bind("submit.form-plugin",function(f){if(!f.isDefaultPrevented()){f.preventDefault();b(this).ajaxSubmit(c)}}).bind("click.form-plugin",function(j){var i=j.target;var g=b(i);if(!(g.is(":submit,input:image"))){var f=g.closest(":submit");if(f.length==0){return}i=f[0]}var h=this;h.clk=i;if(i.type=="image"){if(j.offsetX!=undefined){h.clk_x=j.offsetX;h.clk_y=j.offsetY}else{if(typeof b.fn.offset=="function"){var k=g.offset();h.clk_x=j.pageX-k.left;h.clk_y=j.pageY-k.top}else{h.clk_x=j.pageX-i.offsetLeft;h.clk_y=j.pageY-i.offsetTop}}}setTimeout(function(){h.clk=h.clk_x=h.clk_y=null},100)})};b.fn.ajaxFormUnbind=function(){return this.unbind("submit.form-plugin click.form-plugin")};b.fn.formToArray=function(q){var p=[];if(this.length===0){return p}var d=this[0];var g=q?d.getElementsByTagName("*"):d.elements;if(!g){return p}var k,h,f,r,e,m,c;for(k=0,m=g.length;k<m;k++){e=g[k];f=e.name;if(!f){continue}if(q&&d.clk&&e.type=="image"){if(!e.disabled&&d.clk==e){p.push({name:f,value:b(e).val(),type:e.type});p.push({name:f+".x",value:d.clk_x},{name:f+".y",value:d.clk_y})}continue}r=b.fieldValue(e,true);if(r&&r.constructor==Array){for(h=0,c=r.length;h<c;h++){p.push({name:f,value:r[h]})}}else{if(r!==null&&typeof r!="undefined"){p.push({name:f,value:r,type:e.type})}}}if(!q&&d.clk){var l=b(d.clk),o=l[0];f=o.name;if(f&&!o.disabled&&o.type=="image"){p.push({name:f,value:l.val()});p.push({name:f+".x",value:d.clk_x},{name:f+".y",value:d.clk_y})}}return p};b.fn.formSerialize=function(c){return b.param(this.formToArray(c))};b.fn.fieldSerialize=function(d){var c=[];this.each(function(){var h=this.name;if(!h){return}var f=b.fieldValue(this,d);if(f&&f.constructor==Array){for(var g=0,e=f.length;g<e;g++){c.push({name:h,value:f[g]})}}else{if(f!==null&&typeof f!="undefined"){c.push({name:this.name,value:f})}}});return b.param(c)};b.fn.fieldValue=function(h){for(var g=[],e=0,c=this.length;e<c;e++){var f=this[e];var d=b.fieldValue(f,h);if(d===null||typeof d=="undefined"||(d.constructor==Array&&!d.length)){continue}d.constructor==Array?b.merge(g,d):g.push(d)}return g};b.fieldValue=function(c,j){var e=c.name,p=c.type,q=c.tagName.toLowerCase();if(j===undefined){j=true}if(j&&(!e||c.disabled||p=="reset"||p=="button"||(p=="checkbox"||p=="radio")&&!c.checked||(p=="submit"||p=="image")&&c.form&&c.form.clk!=c||q=="select"&&c.selectedIndex==-1)){return null}if(q=="select"){var k=c.selectedIndex;if(k<0){return null}var m=[],d=c.options;var g=(p=="select-one");var l=(g?k+1:d.length);for(var f=(g?k:0);f<l;f++){var h=d[f];if(h.selected){var o=h.value;if(!o){o=(h.attributes&&h.attributes.value&&!(h.attributes.value.specified))?h.text:h.value}if(g){return o}m.push(o)}}return m}return b(c).val()};b.fn.clearForm=function(c){return this.each(function(){b("input,select,textarea",this).clearFields(c)})};b.fn.clearFields=b.fn.clearInputs=function(c){var d=/^(?:color|date|datetime|email|month|number|password|range|search|tel|text|time|url|week)$/i;return this.each(function(){var f=this.type,e=this.tagName.toLowerCase();if(d.test(f)||e=="textarea"||(c&&/hidden/.test(f))){this.value=""}else{if(f=="checkbox"||f=="radio"){this.checked=false}else{if(e=="select"){this.selectedIndex=-1}}}})};b.fn.resetForm=function(){return this.each(function(){if(typeof this.reset=="function"||(typeof this.reset=="object"&&!this.reset.nodeType)){this.reset()}})};b.fn.enable=function(c){if(c===undefined){c=true}return this.each(function(){this.disabled=!c})};b.fn.selected=function(c){if(c===undefined){c=true}return this.each(function(){var d=this.type;if(d=="checkbox"||d=="radio"){this.checked=c}else{if(this.tagName.toLowerCase()=="option"){var e=b(this).parent("select");if(c&&e[0]&&e[0].type=="select-one"){e.find("option").selected(false)}this.selected=c}}})};b.fn.ajaxSubmit.debug=false;function a(){if(!b.fn.ajaxSubmit.debug){return}var c="[jquery.form] "+Array.prototype.join.call(arguments,"");if(window.console&&window.console.log){window.console.log(c)}else{if(window.opera&&window.opera.postError){window.opera.postError(c)}}}})(jQuery);