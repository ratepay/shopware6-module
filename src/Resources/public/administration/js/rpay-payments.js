(()=>{var Wt=Object.create;var ke=Object.defineProperty;var Kt=Object.getOwnPropertyDescriptor;var Vt=Object.getOwnPropertyNames;var Xt=Object.getPrototypeOf,Zt=Object.prototype.hasOwnProperty;var re=(e,t)=>()=>(t||e((t={exports:{}}).exports,t),t.exports);var Yt=(e,t,a,n)=>{if(t&&typeof t=="object"||typeof t=="function")for(let l of Vt(t))!Zt.call(e,l)&&l!==a&&ke(e,l,{get:()=>t[l],enumerable:!(n=Kt(t,l))||n.enumerable});return e};var be=(e,t,a)=>(a=e!=null?Wt(Xt(e)):{},Yt(t||!e||!e.__esModule?ke(a,"default",{value:e,enumerable:!0}):a,e));var Pe=re((rn,Oe)=>{function ra(e,t={}){t.filter=t.filter||(()=>!0);function a(){return r()||x()||h()||u()}function n(){return R(/\s*/),r(!0)||h()||s()||v(!1)}function l(){let b=p(),w=[],_,E=n();for(;E;){if(E.node.type==="Element"){if(_)throw new Error("Found multiple root nodes");_=E.node}E.excluded||w.push(E.node),E=n()}if(!_)throw new Error("Failed to parse XML");return{declaration:b?b.node:null,root:_,children:w}}function p(){return v(!0)}function v(b){let w=R(b?/^<\?(xml)\s*/:/^<\?([\w-:.]+)\s*/);if(!w)return;let _={name:w[1],type:"ProcessingInstruction",attributes:{}};for(;!(ee()||j("?>"));){let E=L();if(!E)return _;_.attributes[E.name]=E.value}return R(/\?>/),{excluded:b?!1:t.filter(_)===!1,node:_}}function r(b){let w=R(/^<([\w-:.]+)\s*/);if(!w)return;let _={type:"Element",name:w[1],attributes:{},children:[]};for(;!(ee()||j(">")||j("?>")||j("/>"));){let k=L();if(!k)return _;_.attributes[k.name]=k.value}let E=b?!1:t.filter(_)===!1;if(R(/^\s*\/>/))return _.children=null,{excluded:E,node:_};if(R(/\??>/),!E){let k=a();for(;k;)k.excluded||_.children.push(k.node),k=a()}return R(/^<\/[\w-:.]+>/),{excluded:E,node:_}}function s(){let b=R(/^<!DOCTYPE\s+[^>]*>/);if(b){let w={type:"DocumentType",content:b[0]};return{excluded:t.filter(w)===!1,node:w}}}function u(){if(e.startsWith("<![CDATA[")){let b=e.indexOf("]]>");if(b>-1){let w=b+3,_={type:"CDATA",content:e.substring(0,w)};return e=e.slice(w),{excluded:t.filter(_)===!1,node:_}}}}function h(){let b=R(/^<!--[\s\S]*?-->/);if(b){let w={type:"Comment",content:b[0]};return{excluded:t.filter(w)===!1,node:w}}}function x(){let b=R(/^([^<]+)/);if(b){let w={type:"Text",content:b[1]};return{excluded:t.filter(w)===!1,node:w}}}function L(){let b=R(/([\w:-]+)\s*=\s*("[^"]*"|'[^']*'|\w+)\s*/);if(b)return{name:b[1],value:K(b[2])}}function K(b){return b.replace(/^['"]|['"]$/g,"")}function R(b){let w=e.match(b);if(w)return e=e.slice(w[0].length),w}function ee(){return e.length===0}function j(b){return e.indexOf(b)===0}return e=e.trim(),l()}Oe.exports=ra});var Ge=re((sn,je)=>{function oe(e){if(!e.options.indentation&&!e.options.lineSeparator)return;e.content+=e.options.lineSeparator;let t;for(t=0;t<e.level;t++)e.content+=e.options.indentation}function O(e,t){e.content+=t}function Fe(e,t,a){if(typeof e.content=="string")sa(e,t,a);else if(e.type==="Element")oa(e,t,a);else if(e.type==="ProcessingInstruction")He(e,t,a);else throw new Error("Unknown node type: "+e.type)}function sa(e,t,a){a||(e.content=e.content.trim()),e.content.length>0&&(!a&&t.content.length>0&&oe(t),O(t,e.content))}function oa(e,t,a){if(!a&&t.content.length>0&&oe(t),O(t,"<"+e.name),ze(t,e.attributes),e.children===null){let n=t.options.whiteSpaceAtEndOfSelfclosingTag?" />":"/>";O(t,n)}else if(e.children.length===0)O(t,"></"+e.name+">");else{O(t,">"),t.level++;let n=e.attributes["xml:space"]==="preserve";!n&&t.options.collapseContent&&e.children.some(function(p){return p.type==="Text"&&p.content.trim()!==""})&&(n=!0),e.children.forEach(function(l){Fe(l,t,a||n,t.options)}),t.level--,!a&&!n&&oe(t),O(t,"</"+e.name+">")}}function ze(e,t){Object.keys(t).forEach(function(a){let n=t[a].replace(/"/g,"&quot;");O(e," "+a+'="'+n+'"')})}function He(e,t){t.content.length>0&&oe(t),O(t,"<?"+e.name),ze(t,e.attributes),O(t,"?>")}function la(e,t={}){t.indentation="indentation"in t?t.indentation:"    ",t.collapseContent=t.collapseContent===!0,t.lineSeparator="lineSeparator"in t?t.lineSeparator:`\r
`,t.whiteSpaceAtEndOfSelfclosingTag=!!t.whiteSpaceAtEndOfSelfclosingTag;let n=Pe()(e,{filter:t.filter}),l={content:"",level:0,options:t};return n.declaration&&He(n.declaration,l),n.children.forEach(function(p){Fe(p,l,!1)}),l.content}je.exports=la});var nt=re((on,it)=>{function _e(e){return e instanceof Map?e.clear=e.delete=e.set=function(){throw new Error("map is read-only")}:e instanceof Set&&(e.add=e.clear=e.delete=function(){throw new Error("set is read-only")}),Object.freeze(e),Object.getOwnPropertyNames(e).forEach(function(t){var a=e[t];typeof a=="object"&&!Object.isFrozen(a)&&_e(a)}),e}var Ze=_e,da=_e;Ze.default=da;var de=class{constructor(t){t.data===void 0&&(t.data={}),this.data=t.data,this.isMatchIgnored=!1}ignoreMatch(){this.isMatchIgnored=!0}};function W(e){return e.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#x27;")}function F(e,...t){let a=Object.create(null);for(let n in e)a[n]=e[n];return t.forEach(function(n){for(let l in n)a[l]=n[l]}),a}var ca="</span>",qe=e=>!!e.kind,Ce=class{constructor(t,a){this.buffer="",this.classPrefix=a.classPrefix,t.walk(this)}addText(t){this.buffer+=W(t)}openNode(t){if(!qe(t))return;let a=t.kind;t.sublanguage||(a=`${this.classPrefix}${a}`),this.span(a)}closeNode(t){qe(t)&&(this.buffer+=ca)}value(){return this.buffer}span(t){this.buffer+=`<span class="${t}">`}},V=class{constructor(){this.rootNode={children:[]},this.stack=[this.rootNode]}get top(){return this.stack[this.stack.length-1]}get root(){return this.rootNode}add(t){this.top.children.push(t)}openNode(t){let a={kind:t,children:[]};this.add(a),this.stack.push(a)}closeNode(){if(this.stack.length>1)return this.stack.pop()}closeAllNodes(){for(;this.closeNode(););}toJSON(){return JSON.stringify(this.rootNode,null,4)}walk(t){return this.constructor._walk(t,this.rootNode)}static _walk(t,a){return typeof a=="string"?t.addText(a):a.children&&(t.openNode(a),a.children.forEach(n=>this._walk(t,n)),t.closeNode(a)),t}static _collapse(t){typeof t!="string"&&t.children&&(t.children.every(a=>typeof a=="string")?t.children=[t.children.join("")]:t.children.forEach(a=>{V._collapse(a)}))}},ve=class extends V{constructor(t){super(),this.options=t}addKeyword(t,a){t!==""&&(this.openNode(a),this.addText(t),this.closeNode())}addText(t){t!==""&&this.add(t)}addSublanguage(t,a){let n=t.root;n.kind=a,n.sublanguage=!0,this.add(n)}toHTML(){return new Ce(this,this.options).value()}finalize(){return!0}};function pa(e){return new RegExp(e.replace(/[-/\\^$*+?.()|[\]{}]/g,"\\$&"),"m")}function X(e){return e?typeof e=="string"?e:e.source:null}function ua(...e){return e.map(a=>X(a)).join("")}function ha(...e){return"("+e.map(a=>X(a)).join("|")+")"}function ga(e){return new RegExp(e.toString()+"|").exec("").length-1}function fa(e,t){let a=e&&e.exec(t);return a&&a.index===0}var ma=/\[(?:[^\\\]]|\\.)*\]|\(\??|\\([1-9][0-9]*)|\\./;function ba(e,t="|"){let a=0;return e.map(n=>{a+=1;let l=a,p=X(n),v="";for(;p.length>0;){let r=ma.exec(p);if(!r){v+=p;break}v+=p.substring(0,r.index),p=p.substring(r.index+r[0].length),r[0][0]==="\\"&&r[1]?v+="\\"+String(Number(r[1])+l):(v+=r[0],r[0]==="("&&a++)}return v}).map(n=>`(${n})`).join(t)}var ya=/\b\B/,Ye="[a-zA-Z]\\w*",Se="[a-zA-Z_]\\w*",Ee="\\b\\d+(\\.\\d+)?",Qe="(-?)(\\b0[xX][a-fA-F0-9]+|(\\b\\d+(\\.\\d*)?|\\.\\d+)([eE][-+]?\\d+)?)",Je="\\b(0b[01]+)",wa="!|!=|!==|%|%=|&|&&|&=|\\*|\\*=|\\+|\\+=|,|-|-=|/=|/|:|;|<<|<<=|<=|<|===|==|=|>>>=|>>=|>=|>>>|>>|>|\\?|\\[|\\{|\\(|\\^|\\^=|\\||\\|=|\\|\\||~",Ca=(e={})=>{let t=/^#![ ]*\//;return e.binary&&(e.begin=ua(t,/.*\b/,e.binary,/\b.*/)),F({className:"meta",begin:t,end:/$/,relevance:0,"on:begin":(a,n)=>{a.index!==0&&n.ignoreMatch()}},e)},Z={begin:"\\\\[\\s\\S]",relevance:0},va={className:"string",begin:"'",end:"'",illegal:"\\n",contains:[Z]},xa={className:"string",begin:'"',end:'"',illegal:"\\n",contains:[Z]},et={begin:/\b(a|an|the|are|I'm|isn't|don't|doesn't|won't|but|just|should|pretty|simply|enough|gonna|going|wtf|so|such|will|you|your|they|like|more)\b/},ce=function(e,t,a={}){let n=F({className:"comment",begin:e,end:t,contains:[]},a);return n.contains.push(et),n.contains.push({className:"doctag",begin:"(?:TODO|FIXME|NOTE|BUG|OPTIMIZE|HACK|XXX):",relevance:0}),n},_a=ce("//","$"),Sa=ce("/\\*","\\*/"),Ea=ce("#","$"),Ma={className:"number",begin:Ee,relevance:0},Ra={className:"number",begin:Qe,relevance:0},Da={className:"number",begin:Je,relevance:0},Ia={className:"number",begin:Ee+"(%|em|ex|ch|rem|vw|vh|vmin|vmax|cm|mm|in|pt|pc|px|deg|grad|rad|turn|s|ms|Hz|kHz|dpi|dpcm|dppx)?",relevance:0},Aa={begin:/(?=\/[^/\n]*\/)/,contains:[{className:"regexp",begin:/\//,end:/\/[gimuy]*/,illegal:/\n/,contains:[Z,{begin:/\[/,end:/\]/,relevance:0,contains:[Z]}]}]},La={className:"title",begin:Ye,relevance:0},ka={className:"title",begin:Se,relevance:0},Na={begin:"\\.\\s*"+Se,relevance:0},Ta=function(e){return Object.assign(e,{"on:begin":(t,a)=>{a.data._beginMatch=t[1]},"on:end":(t,a)=>{a.data._beginMatch!==t[1]&&a.ignoreMatch()}})},le=Object.freeze({__proto__:null,MATCH_NOTHING_RE:ya,IDENT_RE:Ye,UNDERSCORE_IDENT_RE:Se,NUMBER_RE:Ee,C_NUMBER_RE:Qe,BINARY_NUMBER_RE:Je,RE_STARTERS_RE:wa,SHEBANG:Ca,BACKSLASH_ESCAPE:Z,APOS_STRING_MODE:va,QUOTE_STRING_MODE:xa,PHRASAL_WORDS_MODE:et,COMMENT:ce,C_LINE_COMMENT_MODE:_a,C_BLOCK_COMMENT_MODE:Sa,HASH_COMMENT_MODE:Ea,NUMBER_MODE:Ma,C_NUMBER_MODE:Ra,BINARY_NUMBER_MODE:Da,CSS_NUMBER_MODE:Ia,REGEXP_MODE:Aa,TITLE_MODE:La,UNDERSCORE_TITLE_MODE:ka,METHOD_GUARD:Na,END_SAME_AS_BEGIN:Ta});function $a(e,t){e.input[e.index-1]==="."&&t.ignoreMatch()}function Ba(e,t){t&&e.beginKeywords&&(e.begin="\\b("+e.beginKeywords.split(" ").join("|")+")(?!\\.)(?=\\b|\\s)",e.__beforeBegin=$a,e.keywords=e.keywords||e.beginKeywords,delete e.beginKeywords,e.relevance===void 0&&(e.relevance=0))}function Oa(e,t){Array.isArray(e.illegal)&&(e.illegal=ha(...e.illegal))}function Pa(e,t){if(e.match){if(e.begin||e.end)throw new Error("begin & end are not supported with match");e.begin=e.match,delete e.match}}function Fa(e,t){e.relevance===void 0&&(e.relevance=1)}var za=["of","and","for","in","not","or","if","then","parent","list","value"],Ha="keyword";function tt(e,t,a=Ha){let n={};return typeof e=="string"?l(a,e.split(" ")):Array.isArray(e)?l(a,e):Object.keys(e).forEach(function(p){Object.assign(n,tt(e[p],t,p))}),n;function l(p,v){t&&(v=v.map(r=>r.toLowerCase())),v.forEach(function(r){let s=r.split("|");n[s[0]]=[p,ja(s[0],s[1])]})}}function ja(e,t){return t?Number(t):Ga(e)?0:1}function Ga(e){return za.includes(e.toLowerCase())}function qa(e,{plugins:t}){function a(r,s){return new RegExp(X(r),"m"+(e.case_insensitive?"i":"")+(s?"g":""))}class n{constructor(){this.matchIndexes={},this.regexes=[],this.matchAt=1,this.position=0}addRule(s,u){u.position=this.position++,this.matchIndexes[this.matchAt]=u,this.regexes.push([u,s]),this.matchAt+=ga(s)+1}compile(){this.regexes.length===0&&(this.exec=()=>null);let s=this.regexes.map(u=>u[1]);this.matcherRe=a(ba(s),!0),this.lastIndex=0}exec(s){this.matcherRe.lastIndex=this.lastIndex;let u=this.matcherRe.exec(s);if(!u)return null;let h=u.findIndex((L,K)=>K>0&&L!==void 0),x=this.matchIndexes[h];return u.splice(0,h),Object.assign(u,x)}}class l{constructor(){this.rules=[],this.multiRegexes=[],this.count=0,this.lastIndex=0,this.regexIndex=0}getMatcher(s){if(this.multiRegexes[s])return this.multiRegexes[s];let u=new n;return this.rules.slice(s).forEach(([h,x])=>u.addRule(h,x)),u.compile(),this.multiRegexes[s]=u,u}resumingScanAtSamePosition(){return this.regexIndex!==0}considerAll(){this.regexIndex=0}addRule(s,u){this.rules.push([s,u]),u.type==="begin"&&this.count++}exec(s){let u=this.getMatcher(this.regexIndex);u.lastIndex=this.lastIndex;let h=u.exec(s);if(this.resumingScanAtSamePosition()&&!(h&&h.index===this.lastIndex)){let x=this.getMatcher(0);x.lastIndex=this.lastIndex+1,h=x.exec(s)}return h&&(this.regexIndex+=h.position+1,this.regexIndex===this.count&&this.considerAll()),h}}function p(r){let s=new l;return r.contains.forEach(u=>s.addRule(u.begin,{rule:u,type:"begin"})),r.terminatorEnd&&s.addRule(r.terminatorEnd,{type:"end"}),r.illegal&&s.addRule(r.illegal,{type:"illegal"}),s}function v(r,s){let u=r;if(r.isCompiled)return u;[Pa].forEach(x=>x(r,s)),e.compilerExtensions.forEach(x=>x(r,s)),r.__beforeBegin=null,[Ba,Oa,Fa].forEach(x=>x(r,s)),r.isCompiled=!0;let h=null;if(typeof r.keywords=="object"&&(h=r.keywords.$pattern,delete r.keywords.$pattern),r.keywords&&(r.keywords=tt(r.keywords,e.case_insensitive)),r.lexemes&&h)throw new Error("ERR: Prefer `keywords.$pattern` to `mode.lexemes`, BOTH are not allowed. (see mode reference) ");return h=h||r.lexemes||/\w+/,u.keywordPatternRe=a(h,!0),s&&(r.begin||(r.begin=/\B|\b/),u.beginRe=a(r.begin),r.endSameAsBegin&&(r.end=r.begin),!r.end&&!r.endsWithParent&&(r.end=/\B|\b/),r.end&&(u.endRe=a(r.end)),u.terminatorEnd=X(r.end)||"",r.endsWithParent&&s.terminatorEnd&&(u.terminatorEnd+=(r.end?"|":"")+s.terminatorEnd)),r.illegal&&(u.illegalRe=a(r.illegal)),r.contains||(r.contains=[]),r.contains=[].concat(...r.contains.map(function(x){return Ua(x==="self"?r:x)})),r.contains.forEach(function(x){v(x,u)}),r.starts&&v(r.starts,s),u.matcher=p(u),u}if(e.compilerExtensions||(e.compilerExtensions=[]),e.contains&&e.contains.includes("self"))throw new Error("ERR: contains `self` is not supported at the top-level of a language.  See documentation.");return e.classNameAliases=F(e.classNameAliases||{}),v(e)}function at(e){return e?e.endsWithParent||at(e.starts):!1}function Ua(e){return e.variants&&!e.cachedVariants&&(e.cachedVariants=e.variants.map(function(t){return F(e,{variants:null},t)})),e.cachedVariants?e.cachedVariants:at(e)?F(e,{starts:e.starts?F(e.starts):null}):Object.isFrozen(e)?F(e):e}var Wa="10.7.3";function Ka(e){return!!(e||e==="")}function Va(e){let t={props:["language","code","autodetect"],data:function(){return{detectedLanguage:"",unknownLanguage:!1}},computed:{className(){return this.unknownLanguage?"":"hljs "+this.detectedLanguage},highlighted(){if(!this.autoDetect&&!e.getLanguage(this.language))return console.warn(`The language "${this.language}" you specified could not be found.`),this.unknownLanguage=!0,W(this.code);let n={};return this.autoDetect?(n=e.highlightAuto(this.code),this.detectedLanguage=n.language):(n=e.highlight(this.language,this.code,this.ignoreIllegals),this.detectedLanguage=this.language),n.value},autoDetect(){return!this.language||Ka(this.autodetect)},ignoreIllegals(){return!0}},render(n){return n("pre",{},[n("code",{class:this.className,domProps:{innerHTML:this.highlighted}})])}};return{Component:t,VuePlugin:{install(n){n.component("highlightjs",t)}}}}var Xa={"after:highlightElement":({el:e,result:t,text:a})=>{let n=Ue(e);if(!n.length)return;let l=document.createElement("div");l.innerHTML=t.value,t.value=Za(n,Ue(l),a)}};function xe(e){return e.nodeName.toLowerCase()}function Ue(e){let t=[];return function a(n,l){for(let p=n.firstChild;p;p=p.nextSibling)p.nodeType===3?l+=p.nodeValue.length:p.nodeType===1&&(t.push({event:"start",offset:l,node:p}),l=a(p,l),xe(p).match(/br|hr|img|input/)||t.push({event:"stop",offset:l,node:p}));return l}(e,0),t}function Za(e,t,a){let n=0,l="",p=[];function v(){return!e.length||!t.length?e.length?e:t:e[0].offset!==t[0].offset?e[0].offset<t[0].offset?e:t:t[0].event==="start"?e:t}function r(h){function x(L){return" "+L.nodeName+'="'+W(L.value)+'"'}l+="<"+xe(h)+[].map.call(h.attributes,x).join("")+">"}function s(h){l+="</"+xe(h)+">"}function u(h){(h.event==="start"?r:s)(h.node)}for(;e.length||t.length;){let h=v();if(l+=W(a.substring(n,h[0].offset)),n=h[0].offset,h===e){p.reverse().forEach(s);do u(h.splice(0,1)[0]),h=v();while(h===e&&h.length&&h[0].offset===n);p.reverse().forEach(r)}else h[0].event==="start"?p.push(h[0].node):p.pop(),u(h.splice(0,1)[0])}return l+W(a.substr(n))}var We={},ye=e=>{console.error(e)},Ke=(e,...t)=>{console.log(`WARN: ${e}`,...t)},A=(e,t)=>{We[`${e}/${t}`]||(console.log(`Deprecated as of ${e}. ${t}`),We[`${e}/${t}`]=!0)},we=W,Ve=F,Xe=Symbol("nomatch"),Ya=function(e){let t=Object.create(null),a=Object.create(null),n=[],l=!0,p=/(^(<[^>]+>|\t|)+|\n)/gm,v="Could not find the language '{}', did you forget to load/include a language module?",r={disableAutodetect:!0,name:"Plain text",contains:[]},s={noHighlightRe:/^(no-?highlight)$/i,languageDetectRe:/\blang(?:uage)?-([\w-]+)\b/i,classPrefix:"hljs-",tabReplace:null,useBR:!1,languages:null,__emitter:ve};function u(i){return s.noHighlightRe.test(i)}function h(i){let o=i.className+" ";o+=i.parentNode?i.parentNode.className:"";let m=s.languageDetectRe.exec(o);if(m){let C=B(m[1]);return C||(Ke(v.replace("{}",m[1])),Ke("Falling back to no-highlight mode for this block.",i)),C?m[1]:"no-highlight"}return o.split(/\s+/).find(C=>u(C)||B(C))}function x(i,o,m,C){let M="",z="";typeof o=="object"?(M=i,m=o.ignoreIllegals,z=o.language,C=void 0):(A("10.7.0","highlight(lang, code, ...args) has been deprecated."),A("10.7.0",`Please use highlight(code, options) instead.
https://github.com/highlightjs/highlight.js/issues/2277`),z=i,M=o);let N={code:M,language:z};te("before:highlight",N);let T=N.result?N.result:L(N.language,N.code,m,C);return T.code=N.code,te("after:highlight",T),T}function L(i,o,m,C){function M(d,c){let f=G.case_insensitive?c[0].toLowerCase():c[0];return Object.prototype.hasOwnProperty.call(d.keywords,f)&&d.keywords[f]}function z(){if(!g.keywords){D.addText(S);return}let d=0;g.keywordPatternRe.lastIndex=0;let c=g.keywordPatternRe.exec(S),f="";for(;c;){f+=S.substring(d,c.index);let y=M(g,c);if(y){let[I,ne]=y;if(D.addText(f),f="",ie+=ne,I.startsWith("_"))f+=c[0];else{let Ut=G.classNameAliases[I]||I;D.addKeyword(c[0],Ut)}}else f+=c[0];d=g.keywordPatternRe.lastIndex,c=g.keywordPatternRe.exec(S)}f+=S.substr(d),D.addText(f)}function N(){if(S==="")return;let d=null;if(typeof g.subLanguage=="string"){if(!t[g.subLanguage]){D.addText(S);return}d=L(g.subLanguage,S,!0,Le[g.subLanguage]),Le[g.subLanguage]=d.top}else d=R(S,g.subLanguage.length?g.subLanguage:null);g.relevance>0&&(ie+=d.relevance),D.addSublanguage(d.emitter,d.language)}function T(){g.subLanguage!=null?N():z(),S=""}function $(d){return d.className&&D.openNode(G.classNameAliases[d.className]||d.className),g=Object.create(d,{parent:{value:g}}),g}function P(d,c,f){let y=fa(d.endRe,f);if(y){if(d["on:end"]){let I=new de(d);d["on:end"](c,I),I.isMatchIgnored&&(y=!1)}if(y){for(;d.endsParent&&d.parent;)d=d.parent;return d}}if(d.endsWithParent)return P(d.parent,c,f)}function zt(d){return g.matcher.regexIndex===0?(S+=d[0],1):(me=!0,0)}function Ht(d){let c=d[0],f=d.rule,y=new de(f),I=[f.__beforeBegin,f["on:begin"]];for(let ne of I)if(ne&&(ne(d,y),y.isMatchIgnored))return zt(c);return f&&f.endSameAsBegin&&(f.endRe=pa(c)),f.skip?S+=c:(f.excludeBegin&&(S+=c),T(),!f.returnBegin&&!f.excludeBegin&&(S=c)),$(f),f.returnBegin?0:c.length}function jt(d){let c=d[0],f=o.substr(d.index),y=P(g,d,f);if(!y)return Xe;let I=g;I.skip?S+=c:(I.returnEnd||I.excludeEnd||(S+=c),T(),I.excludeEnd&&(S=c));do g.className&&D.closeNode(),!g.skip&&!g.subLanguage&&(ie+=g.relevance),g=g.parent;while(g!==y.parent);return y.starts&&(y.endSameAsBegin&&(y.starts.endRe=y.endRe),$(y.starts)),I.returnEnd?0:c.length}function Gt(){let d=[];for(let c=g;c!==G;c=c.parent)c.className&&d.unshift(c.className);d.forEach(c=>D.openNode(c))}let ae={};function Ae(d,c){let f=c&&c[0];if(S+=d,f==null)return T(),0;if(ae.type==="begin"&&c.type==="end"&&ae.index===c.index&&f===""){if(S+=o.slice(c.index,c.index+1),!l){let y=new Error("0 width match regex");throw y.languageName=i,y.badRule=ae.rule,y}return 1}if(ae=c,c.type==="begin")return Ht(c);if(c.type==="illegal"&&!m){let y=new Error('Illegal lexeme "'+f+'" for mode "'+(g.className||"<unnamed>")+'"');throw y.mode=g,y}else if(c.type==="end"){let y=jt(c);if(y!==Xe)return y}if(c.type==="illegal"&&f==="")return 1;if(fe>1e5&&fe>c.index*3)throw new Error("potential infinite loop, way more iterations than matches");return S+=f,f.length}let G=B(i);if(!G)throw ye(v.replace("{}",i)),new Error('Unknown language: "'+i+'"');let qt=qa(G,{plugins:n}),ge="",g=C||qt,Le={},D=new s.__emitter(s);Gt();let S="",ie=0,q=0,fe=0,me=!1;try{for(g.matcher.considerAll();;){fe++,me?me=!1:g.matcher.considerAll(),g.matcher.lastIndex=q;let d=g.matcher.exec(o);if(!d)break;let c=o.substring(q,d.index),f=Ae(c,d);q=d.index+f}return Ae(o.substr(q)),D.closeAllNodes(),D.finalize(),ge=D.toHTML(),{relevance:Math.floor(ie),value:ge,language:i,illegal:!1,emitter:D,top:g}}catch(d){if(d.message&&d.message.includes("Illegal"))return{illegal:!0,illegalBy:{msg:d.message,context:o.slice(q-100,q+100),mode:d.mode},sofar:ge,relevance:0,value:we(o),emitter:D};if(l)return{illegal:!1,relevance:0,value:we(o),emitter:D,language:i,top:g,errorRaised:d};throw d}}function K(i){let o={relevance:0,emitter:new s.__emitter(s),value:we(i),illegal:!1,top:r};return o.emitter.addText(i),o}function R(i,o){o=o||s.languages||Object.keys(t);let m=K(i),C=o.filter(B).filter(Ie).map($=>L($,i,!1));C.unshift(m);let M=C.sort(($,P)=>{if($.relevance!==P.relevance)return P.relevance-$.relevance;if($.language&&P.language){if(B($.language).supersetOf===P.language)return 1;if(B(P.language).supersetOf===$.language)return-1}return 0}),[z,N]=M,T=z;return T.second_best=N,T}function ee(i){return s.tabReplace||s.useBR?i.replace(p,o=>o===`
`?s.useBR?"<br>":o:s.tabReplace?o.replace(/\t/g,s.tabReplace):o):i}function j(i,o,m){let C=o?a[o]:m;i.classList.add("hljs"),C&&i.classList.add(C)}let b={"before:highlightElement":({el:i})=>{s.useBR&&(i.innerHTML=i.innerHTML.replace(/\n/g,"").replace(/<br[ /]*>/g,`
`))},"after:highlightElement":({result:i})=>{s.useBR&&(i.value=i.value.replace(/\n/g,"<br>"))}},w=/^(<[^>]+>|\t)+/gm,_={"after:highlightElement":({result:i})=>{s.tabReplace&&(i.value=i.value.replace(w,o=>o.replace(/\t/g,s.tabReplace)))}};function E(i){let o=null,m=h(i);if(u(m))return;te("before:highlightElement",{el:i,language:m}),o=i;let C=o.textContent,M=m?x(C,{language:m,ignoreIllegals:!0}):R(C);te("after:highlightElement",{el:i,result:M,text:C}),i.innerHTML=M.value,j(i,m,M.language),i.result={language:M.language,re:M.relevance,relavance:M.relevance},M.second_best&&(i.second_best={language:M.second_best.language,re:M.second_best.relevance,relavance:M.second_best.relevance})}function k(i){i.useBR&&(A("10.3.0","'useBR' will be removed entirely in v11.0"),A("10.3.0","Please see https://github.com/highlightjs/highlight.js/issues/2559")),s=Ve(s,i)}let ue=()=>{if(ue.called)return;ue.called=!0,A("10.6.0","initHighlighting() is deprecated.  Use highlightAll() instead."),document.querySelectorAll("pre code").forEach(E)};function At(){A("10.6.0","initHighlightingOnLoad() is deprecated.  Use highlightAll() instead."),he=!0}let he=!1;function Re(){if(document.readyState==="loading"){he=!0;return}document.querySelectorAll("pre code").forEach(E)}function Lt(){he&&Re()}typeof window<"u"&&window.addEventListener&&window.addEventListener("DOMContentLoaded",Lt,!1);function kt(i,o){let m=null;try{m=o(e)}catch(C){if(ye("Language definition for '{}' could not be registered.".replace("{}",i)),l)ye(C);else throw C;m=r}m.name||(m.name=i),t[i]=m,m.rawDefinition=o.bind(null,e),m.aliases&&De(m.aliases,{languageName:i})}function Nt(i){delete t[i];for(let o of Object.keys(a))a[o]===i&&delete a[o]}function Tt(){return Object.keys(t)}function $t(i){A("10.4.0","requireLanguage will be removed entirely in v11."),A("10.4.0","Please see https://github.com/highlightjs/highlight.js/pull/2844");let o=B(i);if(o)return o;throw new Error("The '{}' language is required, but not loaded.".replace("{}",i))}function B(i){return i=(i||"").toLowerCase(),t[i]||t[a[i]]}function De(i,{languageName:o}){typeof i=="string"&&(i=[i]),i.forEach(m=>{a[m.toLowerCase()]=o})}function Ie(i){let o=B(i);return o&&!o.disableAutodetect}function Bt(i){i["before:highlightBlock"]&&!i["before:highlightElement"]&&(i["before:highlightElement"]=o=>{i["before:highlightBlock"](Object.assign({block:o.el},o))}),i["after:highlightBlock"]&&!i["after:highlightElement"]&&(i["after:highlightElement"]=o=>{i["after:highlightBlock"](Object.assign({block:o.el},o))})}function Ot(i){Bt(i),n.push(i)}function te(i,o){let m=i;n.forEach(function(C){C[m]&&C[m](o)})}function Pt(i){return A("10.2.0","fixMarkup will be removed entirely in v11.0"),A("10.2.0","Please see https://github.com/highlightjs/highlight.js/issues/2534"),ee(i)}function Ft(i){return A("10.7.0","highlightBlock will be removed entirely in v12.0"),A("10.7.0","Please use highlightElement now."),E(i)}Object.assign(e,{highlight:x,highlightAuto:R,highlightAll:Re,fixMarkup:Pt,highlightElement:E,highlightBlock:Ft,configure:k,initHighlighting:ue,initHighlightingOnLoad:At,registerLanguage:kt,unregisterLanguage:Nt,listLanguages:Tt,getLanguage:B,registerAliases:De,requireLanguage:$t,autoDetection:Ie,inherit:Ve,addPlugin:Ot,vuePlugin:Va(e).VuePlugin}),e.debugMode=function(){l=!1},e.safeMode=function(){l=!0},e.versionString=Wa;for(let i in le)typeof le[i]=="object"&&Ze(le[i]);return Object.assign(e,le),e.addPlugin(b),e.addPlugin(Xa),e.addPlugin(_),e},Qa=Ya({});it.exports=Qa});var lt=re((ln,ot)=>{function st(e){return e?typeof e=="string"?e:e.source:null}function rt(e){return H("(?=",e,")")}function Ja(e){return H("(",e,")?")}function H(...e){return e.map(a=>st(a)).join("")}function ei(...e){return"("+e.map(a=>st(a)).join("|")+")"}function ti(e){let t=H(/[A-Z_]/,Ja(/[A-Z0-9_.-]*:/),/[A-Z0-9_.-]*/),a=/[A-Za-z0-9._:-]+/,n={className:"symbol",begin:/&[a-z]+;|&#[0-9]+;|&#x[a-f0-9]+;/},l={begin:/\s/,contains:[{className:"meta-keyword",begin:/#?[a-z_][a-z1-9_-]+/,illegal:/\n/}]},p=e.inherit(l,{begin:/\(/,end:/\)/}),v=e.inherit(e.APOS_STRING_MODE,{className:"meta-string"}),r=e.inherit(e.QUOTE_STRING_MODE,{className:"meta-string"}),s={endsWithParent:!0,illegal:/</,relevance:0,contains:[{className:"attr",begin:a,relevance:0},{begin:/=\s*/,relevance:0,contains:[{className:"string",endsParent:!0,variants:[{begin:/"/,end:/"/,contains:[n]},{begin:/'/,end:/'/,contains:[n]},{begin:/[^\s"'=<>`]+/}]}]}]};return{name:"HTML, XML",aliases:["html","xhtml","rss","atom","xjb","xsd","xsl","plist","wsf","svg"],case_insensitive:!0,contains:[{className:"meta",begin:/<![a-z]/,end:/>/,relevance:10,contains:[l,r,v,p,{begin:/\[/,end:/\]/,contains:[{className:"meta",begin:/<![a-z]/,end:/>/,contains:[l,p,r,v]}]}]},e.COMMENT(/<!--/,/-->/,{relevance:10}),{begin:/<!\[CDATA\[/,end:/\]\]>/,relevance:10},n,{className:"meta",begin:/<\?xml/,end:/\?>/,relevance:10},{className:"tag",begin:/<style(?=\s|>)/,end:/>/,keywords:{name:"style"},contains:[s],starts:{end:/<\/style>/,returnEnd:!0,subLanguage:["css","xml"]}},{className:"tag",begin:/<script(?=\s|>)/,end:/>/,keywords:{name:"script"},contains:[s],starts:{end:/<\/script>/,returnEnd:!0,subLanguage:["javascript","handlebars","xml"]}},{className:"tag",begin:/<>|<\/>/},{className:"tag",begin:H(/</,rt(H(t,ei(/\/>/,/>/,/\s/)))),end:/\/?>/,contains:[{className:"name",begin:t,relevance:0,starts:s}]},{className:"tag",begin:H(/<\//,rt(H(t,/>/))),contains:[{className:"name",begin:t,relevance:0},{begin:/>/,relevance:0,endsParent:!0}]}]}}ot.exports=ti});var Ne=`{#
  ~ Copyright (c) Ratepay GmbH
  ~
  ~ For the full copyright and license information, please view the LICENSE
  ~ file that was distributed with this source code.
#}
<div>
    <sw-select-field v-model="selectedSalesChannelId"
                     :label="$tc('ratepay.admin.create-order.modal.labels.salesChannel')"
                     @change="selectSalesChannel">
        <option>{{ $t('ratepay.admin.create-order.modal.please-select') }}</option>
        <option v-for="salesChannel in salesChannels"
                :value="salesChannel.id"
                :selected="salesChannel.id === selectedSalesChannelId">
            {{ salesChannel.translated.name }}
        </option>
    </sw-select-field>


    <sw-select-field v-model="selectedSalesChannelDomainId"
                     :label="$tc('ratepay.admin.create-order.modal.labels.salesChannelDomain')"
                     v-if="selectedSalesChannelId">
        <option>{{ $t('ratepay.admin.create-order.modal.please-select') }}</option>
        <option v-for="salesChannelDomain in salesChannelDomains"
                :value="salesChannelDomain.id"
                :selected="salesChannelDomain.id === selectedSalesChannelDomainId">
            {{ salesChannelDomain.url }}
        </option>
    </sw-select-field>

    <sw-button @click="navigateToFrontend" class="sw-button--primary">
        {{ $t('ratepay.admin.create-order.modal.start-session') }}
    </sw-button>
</div>

`;var Te={ratepay:{admin:{"create-order":{modal:{"please-select":"-- Bitte ausw\xE4hlen --","start-session":"Sitzung starten",labels:{salesChannel:"Sales Channel",salesChannelDomain:"Domain"}}}}}};var $e={ratepay:{admin:{"create-order":{modal:{"please-select":"-- Please select --","start-session":"Start session",labels:{salesChannel:"Sales channel",salesChannelDomain:"Domain"}}}}}};var{Component:ta}=Shopware,{Criteria:U}=Shopware.Data;ta.register("ratepay-admin-create-order-form",{template:Ne,snippets:{"de-DE":Te,"en-GB":$e},inject:["repositoryFactory","ratepayAdminOrderLoginTokenService"],data(){return{loading:!1,salesChannels:null,salesChannelDomains:null,selectedSalesChannelId:null,selectedSalesChannelDomainId:null,salesChannelRepository:null,salesChannelDomainRepository:null}},created(){this.salesChannelRepository=this.repositoryFactory.create("sales_channel"),this.salesChannelDomainRepository=this.repositoryFactory.create("sales_channel_domain");let e=new U;e.addFilter(U.not("AND",[U.equals("domains.url",null)])),e.addFilter(U.equals("active",!0)),e.addAssociation("domains"),this.loading=!0,this.salesChannelRepository.search(e,Shopware.Context.api).then(t=>{this.salesChannels=t.filter(a=>a.domains.length>0),this.loading=!1})},methods:{selectSalesChannel(){let e=new U;e.addFilter(U.equals("salesChannelId",this.selectedSalesChannelId)),this.loading=!0,this.salesChannelDomainRepository.search(e,Shopware.Context.api).then(t=>{this.salesChannelDomains=t})},navigateToFrontend(){this.ratepayAdminOrderLoginTokenService.requestTokenUrl(this.selectedSalesChannelId,this.selectedSalesChannelDomainId).then(e=>{console.log(arguments),window.open(e.url)})}}});var{Component:aa}=Shopware;aa.register("ratepay-plugin-icon",{template:`<img class="ratepay-plugin-icon" :src="'rpaypayments/plugin.png' | asset">`});var ia=Shopware.Classes.ApiService,se=class extends ia{constructor(t,a){super(t,a,"ratepay/api-log/distinct-values"),this.httpClient=t,this.name="RatepayApiLogDistinctValuesService"}getDistinctValues(t){return this.httpClient.get(this.getApiBasePath()+"/"+t,{headers:this.getBasicHeaders()}).then(a=>a.data)}};Shopware.Application.addServiceProvider("RatepayLogDistinctValuesService",e=>{let t=Shopware.Application.getContainer("init");return new se(t.httpClient,e.loginService)});var Be=`{#
  ~ Copyright (c) 2020 Ratepay GmbH
  ~
  ~ For the full copyright and license information, please view the LICENSE
  ~ file that was distributed with this source code.
#}

{% block ratepay_api_log_list %}
<sw-page class="ratepay-log-viewer-list">

    {% block ratepay_api_log_list_search_bar %}
        <template #search-bar>
            <sw-search-bar initialSearchType="ratepay_api_log"
                           :initialSearch="term"
                           @search="onSearch">
            </sw-search-bar>
        </template>
    {% endblock %}

    <template slot="smart-bar-header">
        <h2>{{ $t('ratepay.apiLog.componentTitle') }}</h2>
    </template>

    {% block ratepayapilog_list_smart_bar_actions %}
        <template slot="smart-bar-actions">
            <sw-button-process variant="primary"
                               @click="getList"
                               :isLoading="isLoading"
                               :processSuccess="isLoaded"
                               @process-finish="isLoaded = false">
                {{ $t('ratepay.apiLog.page.list.reload') }}
            </sw-button-process>
        </template>
    {% endblock %}

    <template slot="content">
        {% block ratepay_api_log_list_content %}
            <sw-entity-listing
                v-if="entities"
                :items="entities"
                :repository="repository"
                :showSelection="false"
                :columns="columns"
                :showActions="true"
                :identifier="'ratepay-api-log-list'">

                <template slot="column-createdAt" slot-scope="{ item }">
                    {{ item.createdAt| date({hour: '2-digit', minute: '2-digit'}) }}
                </template>

                <template #actions="{ item }">
                    <sw-context-menu-item @click="modalItem = item">
                        {{ $t('ratepay.apiLog.modal.openTitle') }}
                    </sw-context-menu-item>

                    <sw-context-menu-item
                        v-if="item.additionalData.orderId"
                        :router-link="{ name: 'sw.order.detail', params: { id: item.additionalData.orderId } }"
                    >
                        {{ $t('ratepay.apiLog.page.list.openOrder') }}
                    </sw-context-menu-item>
                </template>
            </sw-entity-listing>

            <sw-modal
                :title="$t('ratepay.apiLog.global.labels.viewLog')"
                v-if="modalItem"
                class="ratepay-xml-log-modal"
                @modal-close="modalItem = null">
                <div class="flex-container">
                    <div>
                        <span class="heading">{{ $t('ratepay.apiLog.modal.request.heading') }}</span>
                        <pre class="content" v-html="formatXml(modalItem.request)"></pre>
                    </div>
                    <div>
                        <span class="heading">{{ $t('ratepay.apiLog.modal.response.heading') }}</span>
                        <pre class="content" v-html="formatXml(modalItem.response)"></pre>
                    </div>
                </div>
            </sw-modal>
        {% endblock %}
    </template>

    <template #sidebar>
        <sw-sidebar>
            <sw-sidebar-filter-panel
                entity="ratepay_api_log"
                :store-key="storeKey"
                :filters="listFilters"
                :defaults="defaultFilters"
                :active-filter-number="activeFilterNumber"
                @criteria-changed="updateCriteria"
            />
        </sw-sidebar>
    </template>
</sw-page>
{% endblock %}
`;var ct=be(Ge()),pe=be(nt()),pt=be(lt());var{Component:ai,Mixin:ii}=Shopware,{Criteria:dt}=Shopware.Data;ai.register("ratepay-api-log-list",{template:Be,inject:["repositoryFactory","filterFactory","RatepayLogDistinctValuesService"],mixins:[ii.getByName("listing")],data(){return{entityName:"ratepay_api_log",repository:null,entities:null,modalItem:null,initialLogId:null,isLoading:!1,isLoaded:!1,searchConfigEntity:"ratepay_api_log",storeKey:"grid.filter.ratepay_api_log",activeFilterNumber:0,filterCriteria:[],defaultFilters:["createdAt-filter","subOperation-filter","operation-filter","resultCode-filter","resultText-filter","reasonCode-filter","reasonText-filter","statusCode-filter","statusText-filter"],automaticFilter:["operation","subOperation","resultCode","resultText","reasonCode","reasonText","statusCode","statusText"],filterOptions:{operation:null,subOperation:null,resultCode:null,resultText:null,reasonCode:null,reasonText:null,statusCode:null,statusText:null}}},metaInfo(){return{title:this.$t("ratepay.apiLog.componentTitle")}},computed:{columns(){return[{property:"createdAt",dataIndex:"createdAt",label:this.$t("ratepay.apiLog.global.labels.createdAt"),allowResize:!0},{property:"operation",dataIndex:"operation",label:this.$t("ratepay.apiLog.global.labels.operation"),allowResize:!0},{property:"subOperation",dataIndex:"subOperation",label:this.$t("ratepay.apiLog.global.labels.subOperation"),allowResize:!0},{property:"resultCode",dataIndex:"resultCode",label:this.$t("ratepay.apiLog.global.labels.resultCode"),allowResize:!0,visible:!1},{property:"resultText",dataIndex:"resultText",label:this.$t("ratepay.apiLog.global.labels.resultText"),allowResize:!0,visible:!1},{property:"statusCode",dataIndex:"statusCode",label:this.$t("ratepay.apiLog.global.labels.statusCode"),allowResize:!0,visible:!1},{property:"statusText",dataIndex:"statusText",label:this.$t("ratepay.apiLog.global.labels.statusText"),allowResize:!0,visible:!1},{property:"reasonCode",dataIndex:"reasonCode",label:this.$t("ratepay.apiLog.global.labels.reasonCode"),allowResize:!0,visible:!1},{property:"reasonText",dataIndex:"reasonText",label:this.$t("ratepay.apiLog.global.labels.reasonText"),allowResize:!0,visible:!1},{property:"additionalData.transactionId",dataIndex:"transactionId",label:this.$t("ratepay.apiLog.global.labels.transactionId"),allowResize:!0},{property:"additionalData.orderNumber",dataIndex:"orderNumber",label:this.$t("ratepay.apiLog.global.labels.orderNumber"),allowResize:!0,visible:!1},{property:"additionalData.firstName",dataIndex:"firstName",label:this.$t("ratepay.apiLog.global.labels.firstName"),allowResize:!0,visible:!1},{property:"additionalData.lastName",dataIndex:"lastName",label:this.$t("ratepay.apiLog.global.labels.lastName"),allowResize:!0,visible:!1},{property:"additionalData.descriptor",dataIndex:"descriptor",label:this.$t("ratepay.apiLog.global.labels.descriptor"),allowResize:!0,visible:!1}]},defaultCriteria(){let e=new dt;return this.isValidTerm(this.term)&&e.setTerm(this.term),e.addSorting(dt.sort("createdAt","DESC")),this.filterCriteria.forEach(t=>{e.addFilter(t)}),(e.getLimit()===void 0||e.getLimit()===null||e.getLimit()===0)&&e.setLimit(25),e},listFilters(){let e={"createdAt-filter":{property:"createdAt",type:"date-filter",label:this.$t("ratepay.apiLog.global.labels.createdAt"),dateType:"datetime-local",fromFieldLabel:null,toFieldLabel:null,showTimeframe:!1}};return this.automaticFilter.forEach(t=>{e[t+"-filter"]={property:t,type:"multi-select-filter",label:this.$t("ratepay.apiLog.global.labels."+t),options:this.filterOptions[t]}}),this.filterFactory.create(this.entityName,e)}},created(){this.initalLogId=this.$route.query.logId!==void 0?this.$route.query.logId.trim():null,this.repository=this.repositoryFactory.create(this.entityName),pe.default.registerLanguage("xml",pt.default),pe.default.configure({useBR:!1}),this.RatepayLogDistinctValuesService.getDistinctValues(this.automaticFilter.join("|")).then(e=>{e.results.forEach(t=>{this.filterOptions[t.name]=t.options.map(a=>({label:a,value:a}))})})},watch:{$route(){this.initalLogId=this.$route.query.logId!==void 0?this.$route.query.logId.trim():null}},methods:{formatXml(e){return pe.default.highlight("xml",(0,ct.default)(e,{collapseContent:!0})).value.replaceAll(/\r\n\s*&lt;!\[CDATA\[/gi,"&lt;![CDATA[").replaceAll(/]]&gt;\r\n\s*/gi,"]]&gt;")},async getList(){this.isLoading=!0;let e=await Shopware.Service("filterService").mergeWithStoredFilters(this.storeKey,this.defaultCriteria);this.isValidTerm(this.term)&&(e=await this.addQueryScores(this.term,e)),this.repository.search(e,Shopware.Context.api).then(t=>{this.entities=t,this.initalLogId&&this.entities.has(this.initalLogId)&&(this.modalItem=t.get(this.initalLogId))}).finally(()=>{this.isLoading=!1,this.isLoaded=!1}),this.activeFilterNumber=e.filters.length},onSearch(e){this.initalLogId=null,this.term=e.trim(),this.getList()},updateCriteria(e){this.page=1,this.filterCriteria=e}}});var ut=`{% block sw_search_bar_item_cms_page %}
    {% parent %}

    {% block sw_search_bar_item_ratepay_api_log %}
        <router-link v-else-if="type === 'ratepay_api_log'"
                     v-bind:to="{ name: 'ratepay.api.log.list', query: { logId: item.id, term: searchTerm } }"
                     ref="routerLink"
                     class="sw-search-bar-item__link">
            {% block sw_search_bar_item_ratepay_api_log_label %}
                <span class="sw-search-bar-item__label ratepay-api-log-search-result">
                    <div>
                        <sw-highlight-text v-bind:searchTerm="searchTerm"
                                           v-bind:text="item.additionalData.transactionId">
                        </sw-highlight-text>
                        <span>({{ item.operation }})</span>
                    </div>
                    <div class="order-info" v-if="item.additionalData.orderNumber">
                        <span class="order-info--number">{{ item.additionalData.orderNumber }}</span>
                        <span v-if="item.additionalData.mail" class="order-info--mail">{{ item.additionalData.mail }}</span>
                        <span v-if="item.additionalData.firstName" class="order-info--name">{{ item.additionalData.firstName }} {{ item.additionalData.lastName }}</span>
                    </div>
                </span>
            {% endblock %}
        </router-link>
    {% endblock %}
{% endblock %}
`;var{Application:ri,Component:si}=Shopware;si.override("sw-search-bar-item",{template:ut});ri.addServiceProviderDecorator("searchTypeService",e=>(e.upsertType("ratepay_api_log",{entityName:"ratepay_api_log",placeholderSnippet:"global.placeholderSearchBar.ratepay_api_log",listingRoute:"ratepay.api.log.list"}),e));var oi={_searchable:!0,additionalData:{transactionId:{_searchable:!0,_score:100},descriptor:{_searchable:!0,_score:100},orderNumber:{_searchable:!0,_score:80},firstname:{_searchable:!0,_score:60},lastname:{_searchable:!0,_score:60},mail:{_searchable:!0,_score:60}},operation:{_searchable:!0,_score:80},response:{_searchable:!0,_score:50}},ht=oi;var gt={global:{entities:{ratepay_api_log:"Ratepay API-Logs"},placeholderSearchBar:{ratepay_api_log:"Ratepay API-Logs durchsuchen"}},ratepay:{apiLog:{componentTitle:"Ratepay API-Logs",page:{detail:{cancelButtonText:"Zur\xFCck"},list:{reload:"Aktualisieren",openOrder:"Bestellung \xF6ffnen"}},global:{labels:{id:"Id",operation:"Operation",subOperation:"Suboperation",transactionId:"Transaktions ID",firstName:"Vorname",lastName:"Nachname",createdAt:"Erstellt am",request:"Anfrage",response:"Antwort",viewLog:"Anfrage / Antwort",orderNumber:"Bestellnummer",descriptor:"Verwendungszweck",resultCode:"Result Code",resultText:"Result Text",statusCode:"Status Code",statusText:"Status Text",reasonCode:"Reason Code",reasonText:"Reason Text"}},modal:{openTitle:"Anfrage / Antwort anzeigen",request:{heading:"Anfrage"},response:{heading:"Antwort"}}}}};var ft={global:{entities:{ratepay_api_log:"Ratepay API-Logs"},placeholderSearchBar:{ratepay_api_log:"Search in Ratepay API-Logs"}},ratepay:{apiLog:{componentTitle:"Ratepay API-Logs",page:{detail:{cancelButtonText:"Back"},list:{reload:"Refresh",openOrder:"Open order"}},global:{labels:{id:"Id",operation:"Operation",subOperation:"Suboperation",transactionId:"Transaction ID",firstName:"First name",lastName:"Last name",createdAt:"Created at",request:"Request",response:"Response",viewLog:"Request / Response",orderNumber:"Order number",descriptor:"Descriptor",resultCode:"Result Code",resultText:"Result Text",statusCode:"Status Code",statusText:"Status Text",reasonCode:"Reason Code",reasonText:"Reason Text"}},modal:{openTitle:"show request / response",request:{heading:"Request"},response:{heading:"Response"}}}}};var{Module:ci}=Shopware;ci.register("ratepay-api-log",{type:"plugin",name:"api-log",title:"ratepay.apiLog.componentTitle",icon:"regular-cog",color:"#e4233e",entity:"ratepay_api_log",snippets:{"de-DE":gt,"en-GB":ft},routes:{list:{component:"ratepay-api-log-list",path:"list",meta:{parentPath:"sw.settings.index"}}},settingsItem:[{to:"ratepay.api.log.list",group:"plugins",iconComponent:"ratepay-plugin-icon"}],defaultSearchConfiguration:ht});var mt=`{#
  ~ Copyright (c) 2020 Ratepay GmbH
  ~
  ~ For the full copyright and license information, please view the LICENSE
  ~ file that was distributed with this source code.
#}

{% block ratepay_profile_config_list %}
<sw-page class="ratepay-profile-config-list">

    <template slot="smart-bar-header">
        <h2>{{ $t('ratepay.profileConfig.componentTitle') }}</h2>
    </template>

    {% block ratepay_profile_config_list_smart_bar_actions %}
        <template slot="smart-bar-actions">
            <sw-button variant="primary" :routerLink="{ name: 'ratepay.profile.config.create' }">
                {{ $t('ratepay.profileConfig.page.list.createButton') }}
            </sw-button>
        </template>
    {% endblock %}

    <template slot="content">
        {% block ratepay_profile_config_list_content %}
            <sw-entity-listing
                v-if="entities"
                :items="entities"
                :repository="repository"
                :showSelection="false"
                :columns="columns"
                :identifier="'ratepay-profile-config-list'"
                detailRoute="ratepay.profile.config.detail">

                <template slot="column-status" slot-scope="{ item }">
                    <sw-color-badge color="red" v-if="item.status === false" ></sw-color-badge>
                    <span v-if="item.status === false">&nbsp;&nbsp;{{ $t('ratepay.profileConfig.global.labels.inactive') }}</span>
                    <sw-color-badge color="green" v-if="item.status === true"></sw-color-badge>
                    <span v-if="item.status === true">&nbsp;&nbsp;{{ $t('ratepay.profileConfig.global.labels.active') }}</span>
                </template>

                <template slot="column-onlyAdminOrders" slot-scope="{ item }">
                    <span v-if="item.onlyAdminOrders">{{ $t('global.default.yes') }}</span>
                    <span v-if="!item.onlyAdminOrders">{{ $t('global.default.no') }}</span>
                </template>

                <template slot="column-sandbox" slot-scope="{ item }">
                    <span v-if="item.sandbox">{{ $t('global.default.yes') }}</span>
                    <span v-if="!item.sandbox">{{ $t('global.default.no') }}</span>
                </template>

                <template slot="column-salesChannel" slot-scope="{ item }">
                    {{ item.salesChannel.name }}
                </template>
            </sw-entity-listing>
        {% endblock %}
    </template>
</sw-page>
{% endblock %}
`;var{Component:ui}=Shopware,{Criteria:hi}=Shopware.Data;ui.register("ratepay-profile-config-list",{template:mt,inject:["repositoryFactory"],metaInfo(){return{title:this.$t("ratepay.profileConfig.componentTitle")}},data(){return{repository:null,entities:null}},computed:{columns(){return[{property:"profileId",dataIndex:"profileId",label:this.$t("ratepay.profileConfig.global.labels.profile_id"),routerLink:"ratepay.profile.config.detail",allowResize:!0},{property:"salesChannel.name",dataIndex:"salesChannel.name",label:this.$t("ratepay.profileConfig.global.labels.sales_channel"),allowResize:!0},{property:"sandbox",dataIndex:"sandbox",label:this.$t("ratepay.profileConfig.global.labels.sandbox"),allowResize:!0},{property:"onlyAdminOrders",dataIndex:"onlyAdminOrders",label:this.$t("ratepay.profileConfig.global.labels.onlyAdminOrders"),allowResize:!0},{property:"status",dataIndex:"status",label:this.$t("ratepay.profileConfig.global.labels.status"),allowResize:!0}]}},created(){this.repository=this.repositoryFactory.create("ratepay_profile_config");let e=new hi;e.addAssociation("salesChannel"),(e.getLimit()===void 0||e.getLimit()===null||e.getLimit()===0)&&e.setLimit(25),this.repository.search(e,Shopware.Context.api).then(t=>{this.entities=t})}});var bt=`{#
  ~ Copyright (c) 2020 Ratepay GmbH
  ~
  ~ For the full copyright and license information, please view the LICENSE
  ~ file that was distributed with this source code.
#}

{% block ratepay_profile_config_detail %}
    <sw-page class="ratepay-profile-config-detail">
        <template slot="smart-bar-actions">
            <sw-button :routerLink="{ name: 'ratepay.profile.config.list' }">
                {{ $t('ratepay.profileConfig.page.detail.cancelButtonText') }}
            </sw-button>

            <sw-button-process
                :isLoading="isLoading"
                :processSuccess="processSuccess"
                variant="primary"
                @process-finish="saveFinish"
                @click="onClickSave">
                {{ $t('ratepay.profileConfig.page.detail.saveButtonText') }}
            </sw-button-process>

            <sw-button-process
                v-if="entity && entity.isNew() === false && entity.status"
                :key="entity.status"
                :isLoading="isLoading"
                :processSuccess="processSuccess"
                variant="primary"
                :disabled="disabledReloadButton"
                @click="onClickReloadConfig">
                {{ $t('ratepay.profileConfig.page.detail.reloadConfig') }}
            </sw-button-process>
        </template>

        <template slot="content">
            <sw-card-view>
                <sw-card v-if="entity" :isLoading="isLoading">
                    <sw-field v-on:change="lockReloadButton" :label="$t('ratepay.profileConfig.global.labels.profile_id')" v-model="entity.profileId" required></sw-field>
                    <sw-password-field type="password" v-on:change="lockReloadButton" :label="$t('ratepay.profileConfig.global.labels.security_code')" v-model="entity.securityCode" required></sw-password-field>
                    <sw-switch-field v-on:change="lockReloadButton" :label="$t('ratepay.profileConfig.global.labels.sandbox')" v-model="entity.sandbox"></sw-switch-field>
                    <sw-switch-field v-on:change="lockReloadButton" :label="$t('ratepay.profileConfig.global.labels.onlyAdminOrders')" v-model="entity.onlyAdminOrders"></sw-switch-field>

                    <sw-entity-single-select
                        v-on:change="lockReloadButton"
                        entity="sales_channel"
                        :label="$t('ratepay.profileConfig.global.labels.sales_channel')"
                        v-model="entity.salesChannelId"
                        labelProperty="name"
                        required
                    >
                    </sw-entity-single-select>

                </sw-card>

                <sw-card v-if="entity && entity.status" :isLoading="isLoading">
                        <sw-tabs defaultItem="general">
                            <template #default="{ active }">
                                <sw-tabs-item :activeTab="active" name="general">
                                    {{ $t('ratepay.profileConfig.page.tabs.general.title') }}
                                </sw-tabs-item>

                                <sw-tabs-item v-for="config in entity.paymentMethodConfigs" :activeTab="active" :name="config.paymentMethod.id" :key="config.id">
                                    {{ config.paymentMethod.name }}
                                </sw-tabs-item>
                            </template>

                            <template #content="{ active }">
                                <table style="text-align: left;" v-show="active === 'general'">
                                    <tbody>
                                    <tr>
                                        <th>{{ $t('ratepay.profileConfig.page.tabs.general.table.label.countryCodeBilling') }}</th>
                                        <td>{{ entity.countryCodeBilling.join(', ') }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ $t('ratepay.profileConfig.page.tabs.general.table.label.countryCodeDelivery') }}</th>
                                        <td>{{ entity.countryCodeDelivery.join(', ') }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ $t('ratepay.profileConfig.page.tabs.general.table.label.currency') }}</th>
                                        <td>{{ entity.currency.join(', ') }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ $t('ratepay.profileConfig.page.tabs.general.table.label.errorDefault') }}</th>
                                        <td>{{ entity.errorDefault }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ $t('ratepay.profileConfig.page.tabs.general.table.label.updatedAt') }}</th>
                                        <td>{{ entity.updatedAt|date({hour: '2-digit', minute: '2-digit'}) }}</td>
                                    </tr>
                                    </tbody>
                                </table>

                                <table style="text-align: left;"
                                       v-for="config in entity.paymentMethodConfigs" v-show="active === config.paymentMethod.id">
                                    <tbody>
                                        <tr>
                                            <th>{{ $t('ratepay.profileConfig.page.paymentConfig.table.label.allowB2b') }}</th>
                                            <td>{{ config.allowB2b ? $t('global.default.yes') : $t('global.default.no') }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ $t('ratepay.profileConfig.page.paymentConfig.table.label.limitMin') }}</th>
                                            <td>{{ config.limitMin }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ $t('ratepay.profileConfig.page.paymentConfig.table.label.limitMax') }}</th>
                                            <td>{{ config.limitMax }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ $t('ratepay.profileConfig.page.paymentConfig.table.label.limitMaxB2b') }}</th>
                                            <td>{{ config.limitMaxB2b }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ $t('ratepay.profileConfig.page.paymentConfig.table.label.allowDifferentAddresses') }}</th>
                                            <td>{{ config.allowDifferentAddresses ? $t('global.default.yes') : $t('global.default.no') }}</td>
                                        </tr>
                                        <tr v-if="config.installmentConfig">
                                            <th colspan="2" style="padding-top: 15px; border-bottom: 1px solid #ccc;">{{ $t('ratepay.profileConfig.page.paymentConfig.table.label.installmentHeading') }}</th>
                                        </tr>
                                        <tr v-if="config.installmentConfig">
                                            <th>{{ $t('ratepay.profileConfig.page.paymentConfig.table.label.installment.allowedMonths') }}</th>
                                            <td>{{ config.installmentConfig.allowedMonths.join(', ') }}</td>
                                        </tr>
                                        <tr v-if="config.installmentConfig">
                                            <th>{{ $t('ratepay.profileConfig.page.paymentConfig.table.label.installment.isBankTransferAllowed') }}</th>
                                            <td>{{ config.installmentConfig.isBankTransferAllowed ? $t('global.default.yes') : $t('global.default.no') }}</td>
                                        </tr>
                                        <tr v-if="config.installmentConfig">
                                            <th>{{ $t('ratepay.profileConfig.page.paymentConfig.table.label.installment.isDebitAllowed') }}</th>
                                            <td>{{ config.installmentConfig.isDebitAllowed ? $t('global.default.yes') : $t('global.default.no') }}</td>
                                        </tr>
                                        <tr v-if="config.installmentConfig">
                                            <th>{{ $t('ratepay.profileConfig.page.paymentConfig.table.label.installment.defaultPaymentType') }}</th>
                                            <td>{{ config.installmentConfig.defaultPaymentType }}</td>
                                        </tr>
                                        <tr v-if="config.installmentConfig">
                                            <th>{{ $t('ratepay.profileConfig.page.paymentConfig.table.label.installment.rateMin') }}</th>
                                            <td>{{ config.installmentConfig.rateMin }}</td>
                                        </tr>
                                        <tr v-if="config.installmentConfig">
                                            <th>{{ $t('ratepay.profileConfig.page.paymentConfig.table.label.installment.defaultInterestRate') }}</th>
                                            <td>{{ config.installmentConfig.defaultInterestRate }}</td>
                                        </tr>
                                        <tr v-if="config.installmentConfig">
                                            <th>{{ $t('ratepay.profileConfig.page.paymentConfig.table.label.installment.serviceCharge') }}</th>
                                            <td>{{ config.installmentConfig.serviceCharge }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </template>
                        </sw-tabs>



                </sw-card>
            </sw-card-view>
        </template>
    </sw-page>
{% endblock %}
`;var{Component:fi,Mixin:mi}=Shopware,{Criteria:bi}=Shopware.Data;fi.register("ratepay-profile-config-detail",{template:bt,inject:{repositoryFactory:"repositoryFactory",profileConfigApiService:"ratepay-profile-config"},mixins:[mi.getByName("notification")],metaInfo(){return{title:this.$createTitle()}},data(){return{entity:null,isLoading:!1,processSuccess:!1,repository:null,disabledReloadButton:this.entity===null,currentTab:"general"}},created(){this.repository=this.repositoryFactory.create("ratepay_profile_config");let e=this.loadEntity();this.$route.params.reloadConfig&&e.then(()=>{this.onClickReloadConfig()})},methods:{lockReloadButton(){this.disabledReloadButton=!0},loadEntity(){let e=new bi;return e.addAssociation("paymentMethodConfigs.paymentMethod"),e.addAssociation("paymentMethodConfigs.installmentConfig"),e.setIds([this.$route.params.id]),this.repository.search(e,Shopware.Context.api).then(t=>new Promise((a,n)=>{this.entity=t.first(),a(this.entity)}))},onClickSave(){this.isLoading=!0,this.repository.save(this.entity,Shopware.Context.api).then(()=>{this.loadEntity(),this.onClickReloadConfig(),this.createNotificationSuccess({title:this.$tc("ratepay.profileConfig.messages.save.success"),message:this.$tc("ratepay.profileConfig.messages.save.success")}),this.isLoading=!1,this.processSuccess=!0}).catch(e=>{this.isLoading=!1,this.createNotificationError({title:this.$t("ratepay.profileConfig.messages.save.error.title"),message:e})})},saveFinish(){this.disabledReloadButton=!1,this.processSuccess=!1},onClickReloadConfig(){return this.profileConfigApiService.reloadConfig(this.entity.id).then(e=>{this.loadEntity();for(let[t,a]of Object.entries(e.success))this.createNotificationSuccess({title:t,message:this.$tc("ratepay.profileConfig.messages.reload.success")});for(let[t,a]of Object.entries(e.error))this.createNotificationError({title:t,message:a});return this.$forceUpdate(),new Promise((t,a)=>{t()})})},switchTab(e){this.currentTab=e}}});var{Component:yi}=Shopware;yi.extend("ratepay-profile-config-create","ratepay-profile-config-detail",{methods:{loadEntity(){return this.entity=this.repository.create(Shopware.Context.api),this.entity.onlyAdminOrders=!1,this.entity.sandbox=!1,new Promise((e,t)=>{e(this.entity)})},onClickSave(){this.isLoading=!0,this.repository.save(this.entity,Shopware.Context.api).then(()=>{this.isLoading=!1,this.createNotificationSuccess({title:this.$tc("ratepay.profileConfig.messages.save.success"),message:this.$tc("ratepay.profileConfig.messages.save.success")}),this.$router.push({name:"ratepay.profile.config.detail",params:{id:this.entity.id,reloadConfig:!0}})}).catch(e=>{this.isLoading=!1,this.createNotificationError({title:this.$t("ratepay.profileConfig.messages.save.error.title"),message:this.$t("ratepay.profileConfig.messages.save.error.message")})})}}});var yt={ratepay:{profileConfig:{componentTitle:"Ratepay Profil Konfiguration",global:{labels:{profile_id:"Profile ID",security_code:"Sicherheitsschl\xFCssel",sales_channel:"Sales Channel",sandbox:"Testmodus",status:"Status",active:"Aktiv",inactive:"Inaktiv",onlyAdminOrders:"Nur f\xFCr Admin Bestellungen"}},page:{list:{createButton:"Profil hinzuf\xFCgen"},detail:{cancelButtonText:"Abbrechen",saveButtonText:"Speichern",reloadConfig:"Konfiguration neuladen"},tabs:{general:{title:"Allgemein",table:{label:{countryCodeBilling:"erlaubte L\xE4nder (Rechnungsadresse)",countryCodeDelivery:"erlaubte L\xE4nder (Lieferadresse)",currency:"W\xE4hrung",errorDefault:"Standardfehlermeldung",updatedAt:"Zuletzt aktualisiert"}}},invoice:{title:"Rechnung"},prepayment:{title:"Vorkasse"},debit:{title:"Lastschrift"},installment:{title:"Ratenzahlung"},installment_zero_percent:{title:"0% Finanzierung"}},paymentConfig:{table:{label:{allowB2b:"Erlaube B2B",limitMin:"Mindestbestellwert",limitMax:"Maximalbestellwert",limitMaxB2b:"Maximalbestellwert (B2B)",allowDifferentAddresses:"Erlaube unterschiedliche Adressen",installmentHeading:"Ratenzahlung",installment:{allowedMonths:"Verf\xFCgbare Monate",isBankTransferAllowed:"Erlaube \xDCberweisung",isDebitAllowed:"Erlaube Lastschrift",rateMin:"min. monatliche Rate",defaultPaymentType:"Standard Zahltyp",defaultInterestRate:"Standard Zinssatz",serviceCharge:"Bearbeitungsgeb\xFChr"}}}}},messages:{save:{success:"Die Profile-Konfiguration wurde erfolgreich gespeichert",error:{title:"Beim Speichern der Profile-Konfiguration ist ein Fehler aufgetreten",message:"Bitte pr\xFCfen Sie alle Eingabefelder.",selectSalesChannel:"Bitte w\xE4hlen Sie einen Sales Channel."}},reload:{success:"Die Profile-Konfiguration wurde erfolgreich neu geladen",error:"Beim Neuladen der Profile-Konfiguration ist ein Fehler aufgetreten"}}}}};var wt={ratepay:{profileConfig:{componentTitle:"Ratepay Profile Configuration",global:{labels:{profile_id:"Profile ID",security_code:"Security Code",sales_channel:"Sales Channel",sandbox:"Test-Mode",backend:"For admin orders",status:"Status",active:"Active",inactive:"Inactive",onlyAdminOrders:"Only for admin orders"}},page:{list:{createButton:"Add Profile"},detail:{cancelButtonText:"Cancel",saveButtonText:"Save",reloadConfig:"Reload config"},tabs:{general:{title:"General",table:{label:{countryCodeBilling:"allowed countries (billing address)",countryCodeDelivery:"allowed countries (delivery address)",currency:"currency",errorDefault:"default error message",updatedAt:"last update"}}},invoice:{title:"Invoice"},prepayment:{title:"Prepayment"},debit:{title:"Debit"},installment:{title:"Instalment"},installment_zero_percent:{title:"0% Financing"}},paymentConfig:{table:{label:{allowB2b:"allow B2B",limitMin:"min. amount",limitMax:"max. amount",limitMaxB2b:"max. amount (B2B)",allowDifferentAddresses:"allow different addresses",installmentHeading:"Installment",installment:{allowedMonths:"available months",isBankTransferAllowed:"is bank transfer allowed",isDebitAllowed:"is debit allowed",rateMin:"min. monthly rate",defaultPaymentType:"default payment type",defaultInterestRate:"default interest rate",serviceCharge:"service charge"}}}}},messages:{save:{success:"The profile configuration has been saved",error:{title:"Error while saving the profile configuration",message:"Please verify all input fields.",selectSalesChannel:"Please select a sales channel."}},reload:{success:"The profile configuration has been reloaded",error:"Error while reloading the profile configuration"}}}}};var{Module:vi}=Shopware;vi.register("ratepay-profile-config",{type:"plugin",name:"profile-config",title:"ratepay.profileConfig.componentTitle",icon:"regular-cog",color:"#e4233e",entity:"ratepay_profile_config",snippets:{"de-DE":yt,"en-GB":wt},routes:{list:{component:"ratepay-profile-config-list",path:"list",meta:{parentPath:"sw.settings.index"}},detail:{component:"ratepay-profile-config-detail",path:"detail/:id",meta:{parentPath:"ratepay.profile.config.list"}},create:{component:"ratepay-profile-config-create",path:"create",meta:{parentPath:"ratepay.profile.config.list"}}},settingsItem:[{to:"ratepay.profile.config.list",group:"plugins",iconComponent:"ratepay-plugin-icon"}]});var xi=Shopware.Classes.ApiService,Y=class extends xi{constructor(t,a){super(t,a,"ratepay/order-management"),this.httpClient=t,this.loginService=a,this.name="ratepayOrderManagementService"}load(t){return this.httpClient.get(this.getApiBasePath()+"/load/"+t,{headers:this.getBasicHeaders()}).then(a=>a.data)}doAction(t,a,n,l){return this.httpClient.post(this.getApiBasePath()+"/"+t+"/"+a,{items:n,updateStock:typeof l=="boolean"?l:null},{headers:this.getBasicHeaders()}).then(p=>p.data)}addItem(t,a,n,l){return this.httpClient.post(this.getApiBasePath()+"/addItem/"+t,{name:a,grossAmount:n,taxId:l},{headers:this.getBasicHeaders()}).then(p=>p.data)}};Shopware.Application.addServiceProvider("ratepay-order-management-service",e=>{let t=Shopware.Application.getContainer("init");return new Y(t.httpClient,e.loginService)});var _i=Shopware.Classes.ApiService,Q=class extends _i{constructor(t,a){super(t,a,"ratepay/profile-configuration"),this.httpClient=t,this.loginService=a,this.name="ratepayConfigService"}reloadConfig(t){return this.httpClient.post(this.getApiBasePath()+"/reload-config/",{id:t},{headers:this.getBasicHeaders()}).then(a=>a.data)}};Shopware.Application.addServiceProvider("ratepay-profile-config",e=>{let t=Shopware.Application.getContainer("init");return new Q(t.httpClient,e.loginService)});var Si=Shopware.Classes.ApiService,J=class extends Si{constructor(t,a){super(t,a,"ratepay/admin-order"),this.httpClient=t,this.loginService=a,this.name="RatepayAdminOrderTokenAService"}requestTokenUrl(t,a){return this.httpClient.post(this.getApiBasePath()+"/login-token",{salesChannelId:t,salesChannelDomainId:a},{headers:this.getBasicHeaders()}).then(n=>n.data)}};Shopware.Application.addServiceProvider("ratepayAdminOrderLoginTokenService",e=>{let t=Shopware.Application.getContainer("init");return new J(t.httpClient,e.loginService)});var Ct=`{#
  ~ Copyright (c) Ratepay GmbH
  ~
  ~ For the full copyright and license information, please view the LICENSE
  ~ file that was distributed with this source code.
#}
<div class="sw-order-detail-ratepay" v-if="order && order.extensions.ratepayData">

    <ratepay-order-details :order="order"></ratepay-order-details>

    <ratepay-order-management :order="order" @reload-entity-data="$refs.ratepayOrderHistory.loadOrderHistory()"></ratepay-order-management>

    <ratepay-order-history-log-grid :order="order" ref="ratepayOrderHistory"></ratepay-order-history-log-grid>
</div>
`;var{Component:Mi}=Shopware,{mapState:Ri}=Mi.getComponentHelper();Shopware.Component.register("sw-order-detail-ratepay",{template:Ct,metaInfo(){return{title:"Ratepay"}},computed:{...Ri("swOrderDetail",["order"])},created(){this.$emit("loading-change",!1)}});Shopware.Module.register("sw-order-detail-tab-ratepay",{routeMiddleware(e,t){t.name==="sw.order.detail"&&t.children.push({name:"sw.order.detail.ratepay",path:"/sw/order/detail/:id/ratepay",component:"sw-order-detail-ratepay",meta:{parentPath:"sw.order.detail",meta:{parentPath:"sw.order.index",privilege:"order.viewer"}}}),e(t)}});var vt=`{#
  ~ Copyright (c) 2020 Ratepay GmbH
  ~
  ~ For the full copyright and license information, please view the LICENSE
  ~ file that was distributed with this source code.
#}

{% block sw_order_line_items_grid_actions %}
    {% parent %}

    <sw-modal v-if="showLineItemDeleteRestrictionModal"
              @modal-close="onCloseLineItemDeleteRestrictionModal">
        {{ $t('order.ratepay.modal.lineItemDeleteRestriction.text') }}
    </sw-modal>
{% endblock %}
`;var{Component:Ii}=Shopware;Ii.override("sw-order-line-items-grid",{template:vt,data(){return{showLineItemDeleteRestrictionModal:!1}},methods:{onDeleteSelectedItems(){this.order.extensions.ratepayData?this.showLineItemDeleteRestrictionModal=!0:this.$super("onDeleteSelectedItems")},onDeleteItem(e,t){this.order.extensions.ratepayData?this.showLineItemDeleteRestrictionModal=!0:this.$super("onConfirmDelete",e,t)},onCloseLineItemDeleteRestrictionModal(){this.showLineItemDeleteRestrictionModal=!1}}});var xt=`{#
~ Copyright (c) Ratepay GmbH
~
~ For the full copyright and license information, please view the LICENSE
~ file that was distributed with this source code.
#}

<sw-card
    :title="$tc('ratepay.order-details.title')"
    positionIdentifier="ratepay-order-details-card"
    class="ratepay-order-details-card"
>
    <template #grid>
        <sw-container rows="auto auto">
            <sw-card-section secondary slim>
                <sw-container columns="repeat(auto-fit, minmax(250px, 1fr))" gap="30px 30px">

                    <sw-description-list columns="1fr" grid="1fr">
                        <dt>{{ $tc('ratepay.order-details.transactionId') }}</dt>
                        <dd>
                            <router-link v-bind:to="{ name: 'ratepay.api.log.list', query: { term: order.extensions.ratepayData.transactionId } }"
                                         ref="routerLink">
                                {{ order.extensions.ratepayData.transactionId }}
                            </router-link>
                        </dd>
                        <dt>{{ $tc('ratepay.order-details.descriptor') }}</dt>
                        <dd>{{ order.extensions.ratepayData.descriptor }}</dd>
                    </sw-description-list>

                </sw-container>
            </sw-card-section>
        </sw-container>
    </template>
</sw-card>

`;var{Component:Li}=Shopware;Li.register("ratepay-order-details",{template:xt,props:["order"]});var _t=`{#
  ~ Copyright (c) 2020 Ratepay GmbH
  ~
  ~ For the full copyright and license information, please view the LICENSE
  ~ file that was distributed with this source code.
#}

<sw-card :title="$tc('ratepay.order-log-history.detailBase.ratepayHistoryPanelTitle')" positionIdentifier="ratepay-order-log-history">

        <sw-container slot="grid" type="row">
            <sw-entity-listing
                :items="entities"
                :repository="repository"
                :columns="columns"
                :fullPage="false"
                :showSettings="false"
                :showSelection="false"
                :showActions="false"
                :allowColumnEdit="false"
                :allowInlineEdit="false"
                :compactMode="true">

                <template slot="column-createdAt" slot-scope="{ item }">
                    {{ item.createdAt| date({hour: '2-digit', minute: '2-digit'}) }}
                </template>

            </sw-entity-listing>


        </sw-container>

</sw-card>
`;var{Component:Ni}=Shopware,{Criteria:Me}=Shopware.Data;Ni.register("ratepay-order-history-log-grid",{template:_t,inject:["repositoryFactory"],props:["order"],data(){return{repository:null,entities:null}},computed:{columns(){return[{property:"createdAt",dataIndex:"createdAt",label:this.$tc("ratepay.order-log-history.detailBase.column.date"),allowResize:!1},{property:"user",dataIndex:"user",label:this.$tc("ratepay.order-log-history.detailBase.column.user"),allowResize:!1},{property:"event",dataIndex:"event",label:this.$tc("ratepay.order-log-history.detailBase.column.event"),allowResize:!1},{property:"productName",dataIndex:"productName",label:this.$tc("ratepay.order-log-history.detailBase.column.name"),allowResize:!0},{property:"quantity",dataIndex:"quantity",label:this.$tc("ratepay.order-log-history.detailBase.column.count"),allowResize:!0}]}},created(){this.loadOrderHistory()},methods:{loadOrderHistory(){this.repository=this.repositoryFactory.create("ratepay_order_history");let e=new Me;e.setLimit(20),e.addFilter(Me.equals("orderId",this.order.id)),e.addSorting(Me.sort("createdAt","DESC")),this.repository.search(e,Shopware.Context.api).then(t=>{this.entities=t,this.reload=!1})}}});var St=`{#
  ~ Copyright (c) 2020 Ratepay GmbH
  ~
  ~ For the full copyright and license information, please view the LICENSE
  ~ file that was distributed with this source code.
#}

<sw-card :title="$tc('ratepay.orderManagement.title')" class="ratepay-article-panel" positionIdentifier="ratepay-order-management-card">
    <sw-tabs defaultItem="shippingCancel" positionIdentifier="ratepay-order-management">
        <template slot="default" slot-scope="{ active }">
            <sw-tabs-item :activeTab="active" name="shippingCancel">
                {{ $t('ratepay.orderManagement.tab.shippingCancel') }}
            </sw-tabs-item>
            <sw-tabs-item :activeTab="active" name="rtn">
                {{ $t('ratepay.orderManagement.tab.return') }}
            </sw-tabs-item>
        </template>

        <template slot="content" slot-scope="{ active }">
            <template v-if="active === 'shippingCancel'">
                <div class="button-group">
                    <sw-button-group>
                        <sw-button
                            class="sw-button--small"
                            @click="onClickResetSelections"
                            :isLoading="loading.reload">
                            {{ $t('ratepay.orderManagement.action.setZero') }}
                        </sw-button>
                        <sw-button-process
                            class="sw-button--small"
                            :isLoading="loading.deliver"
                            :processSuccess="processSuccess"
                            @click="onClickButtonDeliver">
                            {{ $t('ratepay.orderManagement.action.deliverSelection') }}
                        </sw-button-process>
                        <sw-button-process
                            class="sw-button--small"
                            :isLoading="loading.cancel"
                            :processSuccess="processSuccess"
                            @click="onClickButtonCancel(false)">
                            {{ $t('ratepay.orderManagement.action.cancelSelection') }}
                        </sw-button-process>
                        <sw-button-process
                            class="sw-button--small"
                            :isLoading="loading.cancelWithStock"
                            :processSuccess="processSuccess"
                            @click="onClickButtonCancel(true)">
                            {{ $t('ratepay.orderManagement.action.cancelSelectionWithStock') }}
                        </sw-button-process>
                    </sw-button-group>
                    <sw-button-group>
                        <sw-button class="sw-button--small" @click="onShowCreditModal">{{ $t('ratepay.orderManagement.action.addCredit') }}</sw-button>
                        <sw-button class="sw-button--small"
                                   @click="onShowDebitModal"
                                   v-if="order.transactions[0].paymentMethod.formattedHandlerIdentifier.indexOf('installment') < 0"
                        >{{ $t('ratepay.orderManagement.action.addDebit') }}</sw-button>
                    </sw-button-group>
                </div>

                <sw-data-grid
                    :dataSource="items"
                    :columns="columns"
                    :showSelection="false"
                    :showActions="false"
                    :showSettings="false"
                    :key="loading.list">

                    <template slot="column-quantity" slot-scope="{ item }">
                        <sw-select-field v-if="item.position.maxDelivery > 0" v-model="item.processDeliveryCancel" value="0">
                            <option value="0">0</option>
                            <option v-for="n in item.position.maxDelivery">{{ n }}</option>
                        </sw-select-field>
                        <span v-if="item.position.maxDelivery == 0">0</span>
                    </template>

                    <template slot="column-unitPrice" slot-scope="{ item }">
                        {{ item.unitPrice | currency(order.currency.translated.shortName) }}
                    </template>
                    <template slot="column-totalPrice" slot-scope="{ item }">
                        {{ item.totalPrice | currency(order.currency.translated.shortName) }}
                    </template>

                </sw-data-grid>
            </template>

            <template v-if="active === 'rtn'">
                <div class="button-group">
                    <sw-button-group>
                        <sw-button
                            class="sw-button--small"
                            @click="onClickResetSelections"
                            :isLoading="loading.reload">
                            {{ $t('ratepay.orderManagement.action.setZero') }}
                        </sw-button>
                        <sw-button-process
                            class="sw-button--small"
                            :isLoading="loading.rtn"
                            :processSuccess="processSuccess"
                            @click="onClickButtonReturn(false)">
                            {{ $t('ratepay.orderManagement.action.returnSelection') }}
                        </sw-button-process>
                        <sw-button-process
                            class="sw-button--small"
                            :isLoading="loading.rtnWithStock"
                            :processSuccess="processSuccess"
                            @click="onClickButtonReturn(true)">
                            {{ $t('ratepay.orderManagement.action.returnSelectionWithStock') }}
                        </sw-button-process>
                    </sw-button-group>
                </div>
                <sw-data-grid
                    :dataSource="items"
                    :columns="columns"
                    :showSelection="false"
                    :showActions="false"
                    :showSettings="false"
                    :key="loading.list"
                >
                    <template slot="column-quantity" slot-scope="{ item }">
                        <sw-select-field v-if="item.position.maxReturn > 0" v-model="item.processReturn" value="0">
                            <option value="0">0</option>
                            <option v-for="n in item.position.maxReturn">{{ n }}</option>
                        </sw-select-field>
                        <span v-if="item.position.maxReturn == 0">0</span>
                    </template>
                </sw-data-grid>
            </template>
        </template>
    </sw-tabs>


    <sw-modal :title="$t('ratepay.orderManagement.action.addDebit')"
              v-if="addDebit.showModal"
              @modal-close="onCloseDebitModal">
        <template>
            <label>{{ $t('ratepay.orderManagement.modal.addDebit.label.name') }}</label>
            <sw-text-field :value="addDebit.data.name" v-model="addDebit.data.name"></sw-text-field>
            <label v-if="order.taxStatus == 'net'">{{ $t('ratepay.orderManagement.modal.addDebit.label.amountNet') }}</label>
            <label v-if="order.taxStatus == 'gross'">{{ $t('ratepay.orderManagement.modal.addDebit.label.amountGross') }}</label>
            <sw-number-field class="rp-price-field" v-model="addDebit.data.amount" min="0.01" allowEmpty="false"></sw-number-field>
            <label>{{ $t('ratepay.orderManagement.modal.addDebit.label.tax') }}</label>
            <sw-field type="select"
                      name="sw-field--product-taxId"
                      :placeholder="$tc('ratepay.orderManagement.modal.addDebit.placeholder.tax')"
                      validation="required"
                      v-model="addDebit.data.taxId"
                      @change="updateDebitTax">
                <option v-for="tax in taxes"
                        :key="tax.id"
                        :value="tax.id"
                        :selected="addDebit.data.taxId === tax.id">
                    {{ getTaxLabel(tax) }}
                </option>
            </sw-field>
        </template>
        <template slot="modal-footer">
            <sw-button-process
                class="sw-button--small"
                :isLoading="loading.addDebit"
                :processSuccess="processSuccess"
                @click="onClickButtonAddDebit">
                {{ $t('ratepay.orderManagement.action.addDebit') }}
            </sw-button-process>
        </template>
    </sw-modal>

    <sw-modal :title="$t('ratepay.orderManagement.action.addCredit')"
              v-if="addCredit.showModal"
              @modal-close="onCloseCreditModal">
        <template>
            <label>{{ $t('ratepay.orderManagement.modal.addCredit.label.name') }}</label>
            <sw-text-field :value="addCredit.data.name" v-model="addCredit.data.name"></sw-text-field>
            <label v-if="order.taxStatus == 'net'">{{ $t('ratepay.orderManagement.modal.addCredit.label.amountNet') }}</label>
            <label v-if="order.taxStatus == 'gross'">{{ $t('ratepay.orderManagement.modal.addCredit.label.amountGross') }}</label>
            <sw-number-field class="rp-price-field" v-model="addCredit.data.amount" min="0.01" allowEmpty="false"></sw-number-field>
            <label>{{ $t('ratepay.orderManagement.modal.addCredit.label.tax') }}</label>
            <sw-field type="select"
                      name="sw-field--product-taxId"
                      :placeholder="$tc('ratepay.orderManagement.modal.addCredit.placeholder.tax')"
                      validation="required"
                      v-model="addCredit.data.taxId"
                      @change="updateCreditTax">
                <option v-for="tax in taxes"
                        :key="tax.id"
                        :value="tax.id"
                        :selected="addDebit.data.taxId === tax.id">
                    {{ getTaxLabel(tax) }}
                </option>
            </sw-field>
        </template>
        <template slot="modal-footer">
            <sw-button-process
                class="sw-button--small"
                :isLoading="loading.addCredit"
                :processSuccess="processSuccess"
                @click="onClickButtonAddCredit">
                {{ $t('ratepay.orderManagement.action.addCredit') }}
            </sw-button-process>
        </template>
    </sw-modal>

</sw-card>

`;var{Component:$i,Mixin:Bi}=Shopware,{Criteria:Oi}=Shopware.Data;$i.register("ratepay-order-management",{template:St,inject:{orderManagementService:"ratepay-order-management-service",repositoryFactory:"repositoryFactory"},props:["order"],mixins:[Bi.getByName("notification")],data(){return{items:[],taxes:[],defaultTax:null,activeTab:"shipping",processSuccess:!1,orderId:null,loading:{list:!0,deliver:!1,cancel:!1,cancelWithStock:!1,rtn:!1,rtnWithStock:!1,reload:!1,addCredit:!1,addDebit:!1},showCreditModal:!1,showDebitModal:!1,addCredit:{showModal:!1,data:{amount:null,name:null,tax:null,taxId:null}},addDebit:{showModal:!1,data:{amount:null,name:null,tax:null,taxId:null}}}},computed:{columns(){return[{property:"quantity",label:this.$t("ratepay.orderManagement.table.count"),allowResize:!1,align:"center"},{property:"name",label:this.$t("ratepay.orderManagement.table.articleName"),allowResize:!1},{property:"ordered",label:this.$t("ratepay.orderManagement.table.ordered"),allowResize:!1,align:"center"},{property:"position.delivered",label:this.$t("ratepay.orderManagement.table.delivered"),allowResize:!1,align:"center"},{property:"position.canceled",label:this.$t("ratepay.orderManagement.table.canceled"),allowResize:!1,align:"center"},{property:"position.returned",label:this.$t("ratepay.orderManagement.table.returned"),allowResize:!1,align:"center"},{property:"unitPrice",label:this.$t("ratepay.orderManagement.table.unitPrice"),allowResize:!1,align:"right"},{property:"totalPrice",label:this.$t("ratepay.orderManagement.table.totalPrice"),allowResize:!1,align:"right"}]},taxRepository(){return this.repositoryFactory.create("tax")}},created(){this.loadList(),this.loadTaxes()},methods:{loadList(){return this.loading.list=!0,this.orderManagementService.load(this.order.id).then(e=>new Promise((t,a)=>{e.data&&(this.items=Object.values(e.data),this.items.map(n=>{n.processDeliveryCancel=n.position.maxDelivery.toString(),n.processReturn=n.position.maxReturn.toString()}),this.loading.list=!1),t()}))},loadTaxes(){return this.taxRepository.search(new Oi(1,500),Shopware.Context.api).then(e=>{this.taxes=e,this.defaultTax=e[0],this.initCredit(),this.initDebit()})},initCredit(){this.addCredit.data.tax=this.defaultTax,this.addCredit.data.taxId=this.defaultTax.id},initDebit(){this.addDebit.data.tax=this.defaultTax,this.addDebit.data.taxId=this.defaultTax.id},updateCreditTax(){this.addCredit.data.taxId&&(this.addCredit.data.tax=this.taxes.get(this.addCredit.data.taxId))},updateDebitTax(){this.addDebit.data.taxId&&(this.addDebit.data.tax=this.taxes.get(this.addDebit.data.taxId))},onClickButtonDeliver(){this.loading.deliver=!0,this.orderManagementService.doAction("deliver",this.order.id,this.getProcessShippingCancelData()).then(e=>{this.showMessage(e,"deliver"),this.loadList().then(()=>{this.loading.deliver=!1,this.$emit("reload-entity-data")})}).catch(e=>{this.loading.deliver=!1,this.showMessage(e,"deliver")})},onClickButtonCancel(e){e===!1?this.loading.cancel=!0:this.loading.cancelWithStock=!0,this.orderManagementService.doAction("cancel",this.order.id,this.getProcessShippingCancelData(),e).then(t=>{this.showMessage(t,"cancel"),this.loadList().then(()=>{this.loading.cancel=!1,this.loading.cancelWithStock=!1,this.$emit("reload-entity-data")})}).catch(t=>{this.loading.cancel=!1,this.loading.cancelWithStock=!1,this.showMessage(t,"cancel")})},onClickButtonReturn(e){e===!1?this.loading.rtn=!0:this.loading.rtnWithStock=!0,this.orderManagementService.doAction("return",this.order.id,this.getProcessReturnData(),e).then(t=>{this.showMessage(t,"return"),this.loadList().then(()=>{this.loading.rtn=!1,this.loading.rtnWithStock=!1,this.$emit("reload-entity-data")})}).catch(t=>{this.loading.rtn=!1,this.loading.rtnWithStock=!1,this.showMessage(t,"return")})},onClickResetSelections(){this.loading.reload=!0,this.loadList().then(()=>{this.loading.reload=!1,this.items.forEach(function(e,t){e.processDeliveryCancel="0",e.processReturn="0"})})},onClickButtonAddDebit(){if(this.loading.addDebit=!0,!this.validateCreditDebit(this.addDebit.data)){this.loading.addDebit=!1;return}this.orderManagementService.addItem(this.order.id,this.addDebit.data.name,this.addDebit.data.amount,this.addDebit.data.taxId).then(e=>{this.showMessage(e,"addDebit"),this.loadList().then(()=>{this.onCloseDebitModal(),this.loading.addDebit=!1,this.$emit("reload-entity-data"),this.initDebit()})}).catch(e=>{this.loading.addDebit=!1,this.showMessage(e,"addDebit")})},onClickButtonAddCredit(){if(this.loading.addCredit=!0,!this.validateCreditDebit(this.addCredit.data)){this.loading.addCredit=!1;return}this.orderManagementService.addItem(this.order.id,this.addCredit.data.name,this.addCredit.data.amount*-1,this.addCredit.data.taxId).then(e=>{this.showMessage(e,"addCredit"),this.loadList().then(()=>{this.onCloseCreditModal(),this.loading.addCredit=!1,this.$emit("reload-entity-data"),this.initCredit()})}).catch(e=>{this.loading.addCredit=!1,this.showMessage(e,"addCredit")})},validateCreditDebit(e){return e.taxId?e.name?e.amount<=0?(this.showMessage({success:!1,message:this.$tc("ratepay.orderManagement.messages.creditDebitValidation.amountTooLow")}),!1):!0:(this.showMessage({success:!1,message:this.$tc("ratepay.orderManagement.messages.creditDebitValidation.missingName")}),!1):(this.showMessage({success:!1,message:this.$tc("ratepay.orderManagement.messages.creditDebitValidation.missingTax")}),!1)},showMessage(e,t){e.success?this.createNotificationSuccess({title:this.$tc("ratepay.orderManagement.messages.successTitle"),message:this.$tc("ratepay.orderManagement.messages."+t+".success")}):(e=e.response?e.response:e,e.data&&e.data.errors?e.data.errors.forEach((a,n)=>{let l=a.detail;this.$te("ratepay.errors."+a.code)&&(l=this.$tc("ratepay.errors."+a.code)),this.createNotificationError({title:a.title??this.$tc("ratepay.orderManagement.messages.failedTitle"),message:l})}):this.createNotificationError({title:this.$tc("ratepay.orderManagement.messages.failedTitle"),message:e.message}))},getProcessShippingCancelData(){let e=[];return this.items.forEach(function(t,a){typeof t.processDeliveryCancel<"u"&&t.processDeliveryCancel>0&&e.push({id:t.id,quantity:t.processDeliveryCancel})}),e},getProcessReturnData(){let e=[];return this.items.forEach(function(t,a){typeof t.processReturn<"u"&&t.processReturn>0&&e.push({id:t.id,quantity:t.processReturn})}),e},onShowDebitModal(){this.addDebit.showModal=!0},onCloseDebitModal(){this.addDebit.showModal=!1},onShowCreditModal(){this.addCredit.showModal=!0},onCloseCreditModal(){this.addCredit.showModal=!1},getTaxLabel(e){return e?this.$te(`global.tax-rates.${e.name}`)?this.$tc(`global.tax-rates.${e.name}`):e.name:""}}});var Et=`{#
  ~ Copyright (c) 2020 Ratepay GmbH
  ~
  ~ For the full copyright and license information, please view the LICENSE
  ~ file that was distributed with this source code.
#}
{% block sw_order_list_smart_bar_actions_add %}
    {% parent %}

    {% block sw_order_list_smart_bar_actions_add_ratepay %}
        <sw-button @click="openRatepayCreateOrderModal">
            {{ $t('order.ratepay.create-order.newButtonText') }}
        </sw-button>
    {% endblock %}
{% endblock %}

{% block sw_order_list_content_slot %}
     {% parent %}
    <sw-modal :title="$tc('order.ratepay.create-order.newButtonText')"
              v-if="ratepayCreateOrderModal"
              @modal-close="ratepayCreateOrderModal = null">
        <ratepay-admin-create-order-form></ratepay-admin-create-order-form>
    </sw-modal>
{% endblock %}

{% block sw_order_list_delete_modal_confirm_delete_text %}
    <p v-if="item.extensions.ratepayData">
        {{ $tc('order.ratepay.modal.orderDeleteRestriction.text') }}
    </p>
    <p v-else class="sw-order-list__confirm-delete-text">
        {{ $tc('sw-order.list.textDeleteConfirm', 0, { orderNumber: \`\${item.orderNumber}\` }) }}
    </p>
{% endblock %}

{% block sw_order_list_delete_modal_confirm %}
    <sw-button @click="onConfirmDelete(item.id)" :disabled="item.extensions.ratepayData !== undefined" variant="danger" size="small">
        {{ $tc('sw-order.list.buttonDelete') }}
    </sw-button>
{% endblock %}

`;var{Component:Fi}=Shopware;Fi.override("sw-order-list",{template:Et,data(){return{ratepayCreateOrderModal:!1,salesChannels:null}},methods:{openRatepayCreateOrderModal(){this.ratepayCreateOrderModal=!0}}});var Mt=`{#
  ~ Copyright (c) Ratepay GmbH
  ~
  ~ For the full copyright and license information, please view the LICENSE
  ~ file that was distributed with this source code.
#}

{% block sw_order_detail_content_tabs_extension %}
    {% parent %}

    {% block sw_order_detail_content_tabs_ratepay %}
        <sw-tabs-item
            v-if="order && 'extensions' in order && 'ratepayData' in order.extensions"
            class="sw-order-detail__tabs-tab-ratepay"
            :route="{ name: 'sw.order.detail.ratepay', params: { id: $route.params.id } }"
            :title="$tc('sw-order.detail.ratepay')"
        >
            {{ $tc('sw-order.detail.ratepay') }}
        </sw-tabs-item>
    {% endblock %}

{% endblock %}
`;var Rt={"sw-order":{detail:{ratepay:"Ratepay"}}};var Dt={"sw-order":{detail:{ratepay:"Ratepay"}}};var{Component:Gi,State:It}=Shopware;Gi.override("sw-order-detail",{template:Mt,snippets:{"de-DE":Rt,"en-GB":Dt},methods:{createdComponent(){this.$super("createdComponent"),this._65to64LoadOrderBackwardCompatibility()},_65to64LoadOrderBackwardCompatibility(){this.versionContext||this.order&&this.order.id===this.orderId||(It.commit("swOrderDetail/setLoading",["order",!0]),this.orderRepository.get(this.orderId,Shopware.Context.api,this.orderCriteria).then(e=>{It.commit("swOrderDetail/setOrder",e)}))}}});})();
