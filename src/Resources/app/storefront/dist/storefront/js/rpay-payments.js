(window.webpackJsonp=window.webpackJsonp||[]).push([["rpay-payments"],{DQvN:function(t,e,n){"use strict";n.r(e);var i=n("FGIj"),r=n("5lm9"),o=n("k8s9");function s(t){return(s="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function a(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function l(t,e){for(var n=0;n<e.length;n++){var i=e[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(t,i.key,i)}}function u(t,e){return!e||"object"!==s(e)&&"function"!=typeof e?function(t){if(void 0===t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return t}(t):e}function c(t){return(c=Object.setPrototypeOf?Object.getPrototypeOf:function(t){return t.__proto__||Object.getPrototypeOf(t)})(t)}function h(t,e){return(h=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t})(t,e)}var p,y,d,f=null,b=function(t){function e(){return a(this,e),u(this,c(e).apply(this,arguments))}var n,i,s;return function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function");t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,writable:!0,configurable:!0}}),e&&h(t,e)}(e,t),n=e,(i=[{key:"init",value:function(){this._runtimeSelect=this.el.querySelector("#rp-btn-runtime"),this._rateInput=this.el.querySelector("#rp-rate-value"),this._rateButton=this.el.querySelector("#rp-rate-button"),this._resultContainer=this.el.querySelector("#rp-result-container"),this._typeHolder=this.el.querySelector("#rp-calculation-type"),this._valueHolder=this.el.querySelector("#rp-calculation-value"),this._registerEvents()}},{key:"_registerEvents",value:function(){this._runtimeSelect&&this._runtimeSelect.addEventListener("change",this._onSelectRuntime.bind(this)),this._rateInput&&this._rateInput.addEventListener("input",this._onInputRate.bind(this)),this._rateButton&&this._rateButton.addEventListener("click",this._onSubmitRate.bind(this)),this._registerInstallmentPlanEvents()}},{key:"_registerInstallmentPlanEvents",value:function(){this._showInstallmentPlanDetailsButton=this._resultContainer.querySelector("#rp-show-installment-plan-details"),this._hideInstallmentPlanDetailsButton=this._resultContainer.querySelector("#rp-hide-installment-plan-details"),this._installmentPlanDetails=this._resultContainer.querySelectorAll(".rp-installment-plan-details"),this._showInstallmentPlanDetailsButton.addEventListener("click",this._onShowInstallmentPlanDetailsButtonClicked.bind(this)),this._hideInstallmentPlanDetailsButton.addEventListener("click",this._onHideInstallmentPlanDetailsButtonClicked.bind(this))}},{key:"_onSelectRuntime",value:function(){this._fetchInstallmentPlan(this.options.calculationTypeTime,this._runtimeSelect.value)}},{key:"_onInputRate",value:function(){""===this._rateInput.value?this._rateButton.setAttribute("disabled","disabled"):this._rateButton.removeAttribute("disabled")}},{key:"_onSubmitRate",value:function(){this._fetchInstallmentPlan(this.options.calculationTypeRate,this._rateInput.value)}},{key:"_onShowInstallmentPlanDetailsButtonClicked",value:function(){this._hide([this._showInstallmentPlanDetailsButton]),this._show([this._hideInstallmentPlanDetailsButton]),this._show(this._installmentPlanDetails,"table-row")}},{key:"_onHideInstallmentPlanDetailsButtonClicked",value:function(){this._hide([this._hideInstallmentPlanDetailsButton]),this._show([this._showInstallmentPlanDetailsButton]),this._hide(this._installmentPlanDetails,"table-row")}},{key:"_fetchInstallmentPlan",value:function(t,e){var n=this,i=new o.a(window.accessKey,window.contextToken),r="".concat(window.rpInstallmentCalculateUrl,"?type=").concat(t,"&value=").concat(e);this._activateLoader(),f&&f.abort(),f=i.get(r,this._executeCallback.bind(this,(function(i){n._setContent(i),n._typeHolder.value=t,n._valueHolder.value=e,n._registerInstallmentPlanEvents()})))}},{key:"_activateLoader",value:function(){this._setContent('<div style="text-align: center">'.concat(r.a.getTemplate(),"</div>"))}},{key:"_executeCallback",value:function(t,e){"function"==typeof t&&t(e)}},{key:"_setContent",value:function(t){this._resultContainer.innerHTML=t}},{key:"_hide",value:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:this.options.showCls,n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:this.options.hiddenCls;t.forEach((function(t){t&&(t.classList.remove(e),t.classList.add(n))}))}},{key:"_show",value:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:this.options.showCls,n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:this.options.hiddenCls;t.forEach((function(t){t&&(t.classList.add(e),t.classList.remove(n))}))}}])&&l(n.prototype,i),s&&l(n,s),e}(i.a);function m(t){return(m="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function _(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function v(t,e){for(var n=0;n<e.length;n++){var i=e[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(t,i.key,i)}}function w(t,e){return!e||"object"!==m(e)&&"function"!=typeof e?function(t){if(void 0===t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return t}(t):e}function S(t){return(S=Object.setPrototypeOf?Object.getPrototypeOf:function(t){return t.__proto__||Object.getPrototypeOf(t)})(t)}function k(t,e){return(k=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t})(t,e)}d={hiddenCls:"d-none",showCls:"d-block",calculationTypeTime:"time",calculationTypeRate:"rate"},(y="options")in(p=b)?Object.defineProperty(p,y,{value:d,enumerable:!0,configurable:!0,writable:!0}):p[y]=d;var P=function(t){function e(){return _(this,e),w(this,S(e).apply(this,arguments))}var n,i,r;return function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function");t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,writable:!0,configurable:!0}}),e&&k(t,e)}(e,t),n=e,(i=[{key:"init",value:function(){this._sepaForm=this.el.querySelector("#rp-sepa-form"),this._bankTransferLink=this.el.querySelector("#rp-switch-payment-type-bank-transfer"),this._directDebitLink=this.el.querySelector("#rp-switch-payment-type-direct-debit"),this._paymentTypeHolder=this.el.querySelector("#rp-calculation-payment-type"),this._registerEvents()}},{key:"_registerEvents",value:function(){this._bankTransferLink&&this._bankTransferLink.addEventListener("click",this._onSelectBankTransfer.bind(this)),this._directDebitLink&&this._directDebitLink.addEventListener("click",this._onSelectDirectDebit.bind(this))}},{key:"_onSelectBankTransfer",value:function(){this._hideSepaForm(),this._hide(this._bankTransferLink),this._show(this._directDebitLink),this._paymentTypeHolder.value=this.options.paymentTypeBankTransfer}},{key:"_onSelectDirectDebit",value:function(){this._showSepaForm(),this._show(this._bankTransferLink),this._hide(this._directDebitLink),this._paymentTypeHolder.value=this.options.paymentTypeDirectDebit}},{key:"_hideSepaForm",value:function(){this._sepaForm&&(this._hide(this._sepaForm),this._sepaForm.querySelector("#rp-iban-account-holder").removeAttribute("required"),this._sepaForm.querySelector("#rp-iban-account-number").removeAttribute("required"),this._sepaForm.querySelector("#sepaconfirmation").removeAttribute("required"),this._sepaForm.querySelector("#rp-iban-account-holder").setAttribute("disabled","disabled"),this._sepaForm.querySelector("#rp-iban-account-number").setAttribute("disabled","disabled"),this._sepaForm.querySelector("#sepaconfirmation").setAttribute("disabled","disabled"))}},{key:"_showSepaForm",value:function(){this._sepaForm&&(this._show(this._sepaForm),this._sepaForm.querySelector("#rp-iban-account-holder").removeAttribute("disabled"),this._sepaForm.querySelector("#rp-iban-account-number").removeAttribute("disabled"),this._sepaForm.querySelector("#sepaconfirmation").removeAttribute("disabled"),this._sepaForm.querySelector("#rp-iban-account-holder").setAttribute("required","required"),this._sepaForm.querySelector("#rp-iban-account-number").setAttribute("required","required"),this._sepaForm.querySelector("#sepaconfirmation").setAttribute("required","required"))}},{key:"_hide",value:function(t){t&&(t.classList.remove(this.options.showCls),t.classList.add(this.options.hiddenCls))}},{key:"_show",value:function(t){t&&(t.classList.add(this.options.showCls),t.classList.remove(this.options.hiddenCls))}}])&&v(n.prototype,i),r&&v(n,r),e}(i.a);function I(t){return(I="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}!function(t,e,n){e in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n}(P,"options",{hiddenCls:"d-none",showCls:"d-block",paymentTypeBankTransfer:"BANK-TRANSFER",paymentTypeDirectDebit:"DIRECT-DEBIT"});var q=window.PluginManager,g=q.getPluginList();void 0!==I(g.RatepayInstallment)&&void 0!==g.RatepayInstallment||q.register("RatepayInstallment",b,'[data-ratepay-installment="true"]'),void 0!==I(g.RatepayInstallmentPaymentSwitch)&&void 0!==g.RatepayInstallmentPaymentSwitch||q.register("RatepayInstallmentPaymentSwitch",P,'[data-ratepay-installment-payment-switch="true"]')}},[["DQvN","runtime","vendor-node","vendor-shared"]]]);