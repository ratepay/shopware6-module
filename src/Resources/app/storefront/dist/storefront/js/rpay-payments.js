(window.webpackJsonp=window.webpackJsonp||[]).push([["rpay-payments"],{DQvN:function(t,e,n){"use strict";n.r(e);var i=n("FGIj"),o=n("5lm9"),r=n("k8s9");function a(t){return(a="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function l(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function s(t,e){for(var n=0;n<e.length;n++){var i=e[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(t,i.key,i)}}function u(t,e){return!e||"object"!==a(e)&&"function"!=typeof e?function(t){if(void 0===t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return t}(t):e}function c(t){return(c=Object.setPrototypeOf?Object.getPrototypeOf:function(t){return t.__proto__||Object.getPrototypeOf(t)})(t)}function p(t,e){return(p=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t})(t,e)}var h,y,f,d=null,m=function(t){function e(){return l(this,e),u(this,c(e).apply(this,arguments))}var n,i,a;return function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function");t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,writable:!0,configurable:!0}}),e&&p(t,e)}(e,t),n=e,(i=[{key:"init",value:function(){this._runtimeSelect=this.el.querySelector("#rp-btn-runtime"),this._rateInput=this.el.querySelector("#rp-rate-value"),this._rateButton=this.el.querySelector("#rp-rate-button"),this._resultContainer=this.el.querySelector("#rp-result-container"),this._typeHolder=this.el.querySelector("#rp-calculation-type"),this._valueHolder=this.el.querySelector("#rp-calculation-value"),this._registerEvents()}},{key:"_registerEvents",value:function(){this._runtimeSelect&&this._runtimeSelect.addEventListener("change",this._onSelectRuntime.bind(this)),this._rateInput&&this._rateInput.addEventListener("input",this._onInputRate.bind(this)),this._rateButton&&this._rateButton.addEventListener("click",this._onSubmitRate.bind(this)),this._registerInstallmentPlanEvents()}},{key:"_registerInstallmentPlanEvents",value:function(){this._showInstallmentPlanDetailsButton=this._resultContainer.querySelector("#rp-show-installment-plan-details"),this._hideInstallmentPlanDetailsButton=this._resultContainer.querySelector("#rp-hide-installment-plan-details"),this._installmentPlanDetails=this._resultContainer.querySelectorAll(".rp-installment-plan-details"),this._showInstallmentPlanDetailsButton.addEventListener("click",this._onShowInstallmentPlanDetailsButtonClicked.bind(this)),this._hideInstallmentPlanDetailsButton.addEventListener("click",this._onHideInstallmentPlanDetailsButtonClicked.bind(this))}},{key:"_onSelectRuntime",value:function(){this._fetchInstallmentPlan(this.options.calculationTypeTime,this._runtimeSelect.value)}},{key:"_onInputRate",value:function(){""===this._rateInput.value?this._rateButton.setAttribute("disabled","disabled"):this._rateButton.removeAttribute("disabled")}},{key:"_onSubmitRate",value:function(){this._fetchInstallmentPlan(this.options.calculationTypeRate,this._rateInput.value)}},{key:"_onShowInstallmentPlanDetailsButtonClicked",value:function(){this._hide([this._showInstallmentPlanDetailsButton]),this._show([this._hideInstallmentPlanDetailsButton]),this._show(this._installmentPlanDetails,"table-row")}},{key:"_onHideInstallmentPlanDetailsButtonClicked",value:function(){this._hide([this._hideInstallmentPlanDetailsButton]),this._show([this._showInstallmentPlanDetailsButton]),this._hide(this._installmentPlanDetails,"table-row")}},{key:"_fetchInstallmentPlan",value:function(t,e){var n=this,i=new r.a(window.accessKey,window.contextToken),o="".concat(window.rpInstallmentCalculateUrl,"?type=").concat(t,"&value=").concat(e);this._activateLoader(),d&&d.abort(),d=i.get(o,this._executeCallback.bind(this,(function(i){n._setContent(i),n._typeHolder.value=t,n._valueHolder.value=e,n._registerInstallmentPlanEvents(),window.PluginManager.initializePlugins()})))}},{key:"_activateLoader",value:function(){this._setContent('<div style="text-align: center">'.concat(o.a.getTemplate(),"</div>"))}},{key:"_executeCallback",value:function(t,e){"function"==typeof t&&t(e)}},{key:"_setContent",value:function(t){this._resultContainer.innerHTML=t}},{key:"_hide",value:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:this.options.showCls,n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:this.options.hiddenCls;t.forEach((function(t){t&&(t.classList.remove(e),t.classList.add(n))}))}},{key:"_show",value:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:this.options.showCls,n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:this.options.hiddenCls;t.forEach((function(t){t&&(t.classList.add(e),t.classList.remove(n))}))}}])&&s(n.prototype,i),a&&s(n,a),e}(i.a);function b(t){return(b="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function _(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function v(t,e){for(var n=0;n<e.length;n++){var i=e[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(t,i.key,i)}}function S(t,e){return!e||"object"!==b(e)&&"function"!=typeof e?function(t){if(void 0===t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return t}(t):e}function w(t){return(w=Object.setPrototypeOf?Object.getPrototypeOf:function(t){return t.__proto__||Object.getPrototypeOf(t)})(t)}function P(t,e){return(P=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t})(t,e)}f={hiddenCls:"d-none",showCls:"d-block",calculationTypeTime:"time",calculationTypeRate:"rate"},(y="options")in(h=m)?Object.defineProperty(h,y,{value:f,enumerable:!0,configurable:!0,writable:!0}):h[y]=f;var g=function(t){function e(){return _(this,e),S(this,w(e).apply(this,arguments))}var n,i,o;return function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function");t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,writable:!0,configurable:!0}}),e&&P(t,e)}(e,t),n=e,(i=[{key:"init",value:function(){var t=this;this._sepaForm=this.el.querySelector("#rp-sepa-form"),this.el.querySelectorAll(this.options.selectorTypeField).forEach((function(e){e.addEventListener("change",t._onChangeType.bind(t))})),this._onChangeType()}},{key:"_onChangeType",value:function(){this.el.querySelector(this.options.selectorTypeField+":checked").value===this.options.paymentTypeDirectDebit?this._showSepaForm():this._hideSepaForm()}},{key:"_hideSepaForm",value:function(){this._sepaForm&&(this._sepaForm.querySelector("#rp-iban-account-holder").removeAttribute("required"),this._sepaForm.querySelector("#rp-iban-account-number").removeAttribute("required"),this._sepaForm.querySelector("#rp-sepa-confirmation").removeAttribute("required"),this._sepaForm.querySelector("#rp-iban-account-holder").setAttribute("disabled","disabled"),this._sepaForm.querySelector("#rp-iban-account-number").setAttribute("disabled","disabled"),this._sepaForm.querySelector("#rp-sepa-confirmation").setAttribute("disabled","disabled"))}},{key:"_showSepaForm",value:function(){this._sepaForm&&(this._sepaForm.querySelector("#rp-iban-account-holder").removeAttribute("disabled"),this._sepaForm.querySelector("#rp-iban-account-number").removeAttribute("disabled"),this._sepaForm.querySelector("#rp-sepa-confirmation").removeAttribute("disabled"),this._sepaForm.querySelector("#rp-iban-account-holder").setAttribute("required","required"),this._sepaForm.querySelector("#rp-iban-account-number").setAttribute("required","required"),this._sepaForm.querySelector("#rp-sepa-confirmation").setAttribute("required","required"))}}])&&v(n.prototype,i),o&&v(n,o),e}(i.a);function k(t){return(k="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}!function(t,e,n){e in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n}(g,"options",{paymentTypeBankTransfer:"BANK-TRANSFER",paymentTypeDirectDebit:"DIRECT-DEBIT",selectorTypeField:'input[name="ratepay[installment][paymentType]"]'});var I=window.PluginManager,q=I.getPluginList();void 0!==k(q.RatepayInstallment)&&void 0!==q.RatepayInstallment||I.register("RatepayInstallment",m,'[data-ratepay-installment="true"]'),void 0!==k(q.RatepayInstallmentPaymentSwitch)&&void 0!==q.RatepayInstallmentPaymentSwitch||I.register("RatepayInstallmentPaymentSwitch",g,'[data-ratepay-installment-payment-switch="true"]')}},[["DQvN","runtime","vendor-node","vendor-shared"]]]);