(()=>{"use strict";var t={857:t=>{var e=function(t){var e;return!!t&&"object"==typeof t&&"[object RegExp]"!==(e=Object.prototype.toString.call(t))&&"[object Date]"!==e&&t.$$typeof!==r},r="function"==typeof Symbol&&Symbol.for?Symbol.for("react.element"):60103;function i(t,e){return!1!==e.clone&&e.isMergeableObject(t)?o(Array.isArray(t)?[]:{},t,e):t}function n(t,e,r){return t.concat(e).map(function(t){return i(t,r)})}function s(t){return Object.keys(t).concat(Object.getOwnPropertySymbols?Object.getOwnPropertySymbols(t).filter(function(e){return Object.propertyIsEnumerable.call(t,e)}):[])}function a(t,e){try{return e in t}catch(t){return!1}}function o(t,r,l){(l=l||{}).arrayMerge=l.arrayMerge||n,l.isMergeableObject=l.isMergeableObject||e,l.cloneUnlessOtherwiseSpecified=i;var c,u,h=Array.isArray(r);return h!==Array.isArray(t)?i(r,l):h?l.arrayMerge(t,r,l):(u={},(c=l).isMergeableObject(t)&&s(t).forEach(function(e){u[e]=i(t[e],c)}),s(r).forEach(function(e){(!a(t,e)||Object.hasOwnProperty.call(t,e)&&Object.propertyIsEnumerable.call(t,e))&&(a(t,e)&&c.isMergeableObject(r[e])?u[e]=(function(t,e){if(!e.customMerge)return o;var r=e.customMerge(t);return"function"==typeof r?r:o})(e,c)(t[e],r[e],c):u[e]=i(r[e],c))}),u)}o.all=function(t,e){if(!Array.isArray(t))throw Error("first argument should be an array");return t.reduce(function(t,r){return o(t,r,e)},{})},t.exports=o}},e={};function r(i){var n=e[i];if(void 0!==n)return n.exports;var s=e[i]={exports:{}};return t[i](s,s.exports,r),s.exports}(()=>{r.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return r.d(e,{a:e}),e}})(),(()=>{r.d=(t,e)=>{for(var i in e)r.o(e,i)&&!r.o(t,i)&&Object.defineProperty(t,i,{enumerable:!0,get:e[i]})}})(),(()=>{r.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e)})(),(()=>{var t=r(857),e=r.n(t);class i{static ucFirst(t){return t.charAt(0).toUpperCase()+t.slice(1)}static lcFirst(t){return t.charAt(0).toLowerCase()+t.slice(1)}static toDashCase(t){return t.replace(/([A-Z])/g,"-$1").replace(/^-/,"").toLowerCase()}static toLowerCamelCase(t,e){let r=i.toUpperCamelCase(t,e);return i.lcFirst(r)}static toUpperCamelCase(t,e){return e?t.split(e).map(t=>i.ucFirst(t.toLowerCase())).join(""):i.ucFirst(t.toLowerCase())}static parsePrimitive(t){try{return/^\d+(.|,)\d+$/.test(t)&&(t=t.replace(",",".")),JSON.parse(t)}catch(e){return t.toString()}}}class n{static isNode(t){return"object"==typeof t&&null!==t&&(t===document||t===window||t instanceof Node)}static hasAttribute(t,e){if(!n.isNode(t))throw Error("The element must be a valid HTML Node!");return"function"==typeof t.hasAttribute&&t.hasAttribute(e)}static getAttribute(t,e){let r=!(arguments.length>2)||void 0===arguments[2]||arguments[2];if(r&&!1===n.hasAttribute(t,e))throw Error('The required property "'.concat(e,'" does not exist!'));if("function"!=typeof t.getAttribute){if(r)throw Error("This node doesn't support the getAttribute function!");return}return t.getAttribute(e)}static getDataAttribute(t,e){let r=!(arguments.length>2)||void 0===arguments[2]||arguments[2],s=e.replace(/^data(|-)/,""),a=i.toLowerCamelCase(s,"-");if(!n.isNode(t)){if(r)throw Error("The passed node is not a valid HTML Node!");return}if(void 0===t.dataset){if(r)throw Error("This node doesn't support the dataset attribute!");return}let o=t.dataset[a];if(void 0===o){if(r)throw Error('The required data attribute "'.concat(e,'" does not exist on ').concat(t,"!"));return o}return i.parsePrimitive(o)}static querySelector(t,e){let r=!(arguments.length>2)||void 0===arguments[2]||arguments[2];if(r&&!n.isNode(t))throw Error("The parent node is not a valid HTML Node!");let i=t.querySelector(e)||!1;if(r&&!1===i)throw Error('The required element "'.concat(e,'" does not exist in parent node!'));return i}static querySelectorAll(t,e){let r=!(arguments.length>2)||void 0===arguments[2]||arguments[2];if(r&&!n.isNode(t))throw Error("The parent node is not a valid HTML Node!");let i=t.querySelectorAll(e);if(0===i.length&&(i=!1),r&&!1===i)throw Error('At least one item of "'.concat(e,'" must exist in parent node!'));return i}}class s{publish(t){let e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r=arguments.length>2&&void 0!==arguments[2]&&arguments[2],i=new CustomEvent(t,{detail:e,cancelable:r});return this.el.dispatchEvent(i),i}subscribe(t,e){let r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},i=this,n=t.split("."),s=r.scope?e.bind(r.scope):e;if(r.once&&!0===r.once){let e=s;s=function(r){i.unsubscribe(t),e(r)}}return this.el.addEventListener(n[0],s),this.listeners.push({splitEventName:n,opts:r,cb:s}),!0}unsubscribe(t){let e=t.split(".");return this.listeners=this.listeners.reduce((t,r)=>([...r.splitEventName].sort().toString()===e.sort().toString()?this.el.removeEventListener(r.splitEventName[0],r.cb):t.push(r),t),[]),!0}reset(){return this.listeners.forEach(t=>{this.el.removeEventListener(t.splitEventName[0],t.cb)}),this.listeners=[],!0}get el(){return this._el}set el(t){this._el=t}get listeners(){return this._listeners}set listeners(t){this._listeners=t}constructor(t=document){this._el=t,t.$emitter=this,this._listeners=[]}}class a{init(){throw Error('The "init" method for the plugin "'.concat(this._pluginName,'" is not defined.'))}update(){}_init(){this._initialized||(this.init(),this._initialized=!0)}_update(){this._initialized&&this.update()}_mergeOptions(t){let r=i.toDashCase(this._pluginName),s=n.getDataAttribute(this.el,"data-".concat(r,"-config"),!1),a=n.getAttribute(this.el,"data-".concat(r,"-options"),!1),o=[this.constructor.options,this.options,t];s&&o.push(window.PluginConfigManager.get(this._pluginName,s));try{a&&o.push(JSON.parse(a))}catch(t){throw console.error(this.el),Error('The data attribute "data-'.concat(r,'-options" could not be parsed to json: ').concat(t.message))}return e().all(o.filter(t=>t instanceof Object&&!(t instanceof Array)).map(t=>t||{}))}_registerInstance(){window.PluginManager.getPluginInstancesFromElement(this.el).set(this._pluginName,this),window.PluginManager.getPlugin(this._pluginName,!1).get("instances").push(this)}_getPluginName(t){return t||(t=this.constructor.name),t}constructor(t,e={},r=!1){if(!n.isNode(t))throw Error("There is no valid element given.");this.el=t,this.$emitter=new s(this.el),this._pluginName=this._getPluginName(r),this.options=this._mergeOptions(e),this._initialized=!1,this._registerInstance(),this._init()}}class o{static iterate(t,e){if(t instanceof Map||Array.isArray(t))return t.forEach(e);if(t instanceof FormData){for(var r of t.entries())e(r[1],r[0]);return}if(t instanceof NodeList)return t.forEach(e);if(t instanceof HTMLCollection)return Array.from(t).forEach(e);if(t instanceof Object)return Object.keys(t).forEach(r=>{e(t[r],r)});throw Error("The element type ".concat(typeof t," is not iterable!"))}}let l="loader",c={BEFORE:"before",INNER:"inner"};class u{create(){if(!this.exists()){if(this.position===c.INNER){this.parent.innerHTML=u.getTemplate();return}this.parent.insertAdjacentHTML(this._getPosition(),u.getTemplate())}}remove(){let t=this.parent.querySelectorAll(".".concat(l));o.iterate(t,t=>t.remove())}exists(){return this.parent.querySelectorAll(".".concat(l)).length>0}_getPosition(){return this.position===c.BEFORE?"afterbegin":"beforeend"}static getTemplate(){return'<div class="'.concat(l,'" role="status">\n                    <span class="').concat("visually-hidden",'">Loading...</span>\n                </div>')}static SELECTOR_CLASS(){return l}constructor(t,e=c.BEFORE){this.parent=t instanceof Element?t:document.body.querySelector(t),this.position=e}}class h{get(t,e){let r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"application/json",i=this._createPreparedRequest("GET",t,r);return this._sendRequest(i,null,e)}post(t,e,r){let i=arguments.length>3&&void 0!==arguments[3]?arguments[3]:"application/json";i=this._getContentType(e,i);let n=this._createPreparedRequest("POST",t,i);return this._sendRequest(n,e,r)}delete(t,e,r){let i=arguments.length>3&&void 0!==arguments[3]?arguments[3]:"application/json";i=this._getContentType(e,i);let n=this._createPreparedRequest("DELETE",t,i);return this._sendRequest(n,e,r)}patch(t,e,r){let i=arguments.length>3&&void 0!==arguments[3]?arguments[3]:"application/json";i=this._getContentType(e,i);let n=this._createPreparedRequest("PATCH",t,i);return this._sendRequest(n,e,r)}abort(){if(this._request)return this._request.abort()}_registerOnLoaded(t,e){e&&t.addEventListener("loadend",()=>{e(t.responseText,t)})}_sendRequest(t,e,r){return this._registerOnLoaded(t,r),t.send(e),t}_getContentType(t,e){return t instanceof FormData&&(e=!1),e}_createPreparedRequest(t,e,r){return this._request=new XMLHttpRequest,this._request.open(t,e),this._request.setRequestHeader("X-Requested-With","XMLHttpRequest"),r&&this._request.setRequestHeader("Content-type",r),this._request}constructor(){this._request=null}}let d=null;class p extends a{init(){this._runtimeSelect=this.el.querySelector("#rp-btn-runtime"),this._rateInput=this.el.querySelector("#rp-rate-value"),this._rateButton=this.el.querySelector("#rp-rate-button"),this._resultContainer=this.el.querySelector("#rp-result-container"),this._typeHolder=this.el.querySelector("#rp-calculation-type"),this._valueHolder=this.el.querySelector("#rp-calculation-value"),this._registerEvents()}_registerEvents(){this._runtimeSelect&&this._runtimeSelect.addEventListener("change",this._onSelectRuntime.bind(this)),this._rateInput&&this._rateInput.addEventListener("input",this._onInputRate.bind(this)),this._rateButton&&this._rateButton.addEventListener("click",this._onSubmitRate.bind(this)),this._registerInstallmentPlanEvents()}_registerInstallmentPlanEvents(){this._showInstallmentPlanDetailsButton=this._resultContainer.querySelector("#rp-show-installment-plan-details"),this._hideInstallmentPlanDetailsButton=this._resultContainer.querySelector("#rp-hide-installment-plan-details"),this._installmentPlanDetails=this._resultContainer.querySelectorAll(".rp-installment-plan-details"),this._showInstallmentPlanDetailsButton.addEventListener("click",this._onShowInstallmentPlanDetailsButtonClicked.bind(this)),this._hideInstallmentPlanDetailsButton.addEventListener("click",this._onHideInstallmentPlanDetailsButtonClicked.bind(this))}_onSelectRuntime(){this._fetchInstallmentPlan(this.options.calculationTypeTime,this._runtimeSelect.value)}_onInputRate(){""===this._rateInput.value?this._rateButton.setAttribute("disabled","disabled"):this._rateButton.removeAttribute("disabled")}_onSubmitRate(){this._fetchInstallmentPlan(this.options.calculationTypeRate,this._rateInput.value)}_onShowInstallmentPlanDetailsButtonClicked(){this._hide([this._showInstallmentPlanDetailsButton]),this._show([this._hideInstallmentPlanDetailsButton]),this._show(this._installmentPlanDetails,"table-row")}_onHideInstallmentPlanDetailsButtonClicked(){this._hide([this._hideInstallmentPlanDetailsButton]),this._show([this._showInstallmentPlanDetailsButton]),this._hide(this._installmentPlanDetails,"table-row")}_fetchInstallmentPlan(t,e){let r=new h(window.accessKey,window.contextToken),i="".concat(window.rpInstallmentCalculateUrl,"?type=").concat(t,"&value=").concat(e);this._activateLoader(),d&&d.abort(),d=r.get(i,this._executeCallback.bind(this,r=>{this._setContent(r),this._typeHolder.value=t,this._valueHolder.value=e,this._registerInstallmentPlanEvents(),window.PluginManager.initializePlugins()}))}_activateLoader(){this._setContent('<div style="text-align: center">'.concat(u.getTemplate(),"</div>"))}_executeCallback(t,e){"function"==typeof t&&t(e)}_setContent(t){this._resultContainer.innerHTML=t}_hide(t){let e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:this.options.showCls,r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:this.options.hiddenCls;t.forEach(t=>{t&&(t.classList.remove(e),t.classList.add(r))})}_show(t){let e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:this.options.showCls,r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:this.options.hiddenCls;t.forEach(t=>{t&&(t.classList.add(e),t.classList.remove(r))})}}p.options={hiddenCls:"d-none",showCls:"d-block",calculationTypeTime:"time",calculationTypeRate:"rate"};class m extends a{init(){this._sepaForm=this.el.querySelector("#rp-sepa-form"),this.el.querySelectorAll(this.options.selectorTypeField).forEach(t=>{t.addEventListener("change",this._onChangeType.bind(this))}),this._onChangeType()}_onChangeType(){this.el.querySelector(this.options.selectorTypeField+":checked").value===this.options.paymentTypeDirectDebit?this._showSepaForm():this._hideSepaForm()}_hideSepaForm(){this._sepaForm&&(this._sepaForm.querySelector("#rp-iban-account-holder").removeAttribute("required"),this._sepaForm.querySelector("#rp-iban-account-number").removeAttribute("required"),this._sepaForm.querySelector("#rp-sepa-confirmation").removeAttribute("required"),this._sepaForm.querySelector("#rp-iban-account-holder").setAttribute("disabled","disabled"),this._sepaForm.querySelector("#rp-iban-account-number").setAttribute("disabled","disabled"),this._sepaForm.querySelector("#rp-sepa-confirmation").setAttribute("disabled","disabled"))}_showSepaForm(){this._sepaForm&&(this._sepaForm.querySelector("#rp-iban-account-holder").removeAttribute("disabled"),this._sepaForm.querySelector("#rp-iban-account-number").removeAttribute("disabled"),this._sepaForm.querySelector("#rp-sepa-confirmation").removeAttribute("disabled"),this._sepaForm.querySelector("#rp-iban-account-holder").setAttribute("required","required"),this._sepaForm.querySelector("#rp-iban-account-number").setAttribute("required","required"),this._sepaForm.querySelector("#rp-sepa-confirmation").setAttribute("required","required"))}}m.options={paymentTypeBankTransfer:"BANK-TRANSFER",paymentTypeDirectDebit:"DIRECT-DEBIT",selectorTypeField:'input[name="ratepay[installment][paymentType]"]'};let _=window.PluginManager,b=_.getPluginList();"RatepayInstallment"in b||_.register("RatepayInstallment",p,'[data-ratepay-installment="true"]'),"RatepayInstallmentPaymentSwitch"in b||_.register("RatepayInstallmentPaymentSwitch",m,'[data-ratepay-installment-payment-switch="true"]')})()})();