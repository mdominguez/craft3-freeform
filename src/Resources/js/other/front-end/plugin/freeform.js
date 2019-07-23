"use strict";function _objectSpread(e){for(var r=1;r<arguments.length;r++){var t=null!=arguments[r]?arguments[r]:{},n=Object.keys(t);"function"==typeof Object.getOwnPropertySymbols&&(n=n.concat(Object.getOwnPropertySymbols(t).filter(function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.forEach(function(r){_defineProperty(e,r,t[r])})}return e}function _classCallCheck(e,r){if(!(e instanceof r))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,r){for(var t=0;t<r.length;t++){var n=r[t];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function _createClass(e,r,t){return r&&_defineProperties(e.prototype,r),t&&_defineProperties(e,t),e}function _defineProperty(e,r,t){return r in e?Object.defineProperty(e,r,{value:t,enumerable:!0,configurable:!0,writable:!0}):e[r]=t,e}var Freeform=function(){function e(r,t){var n=this;if(_classCallCheck(this,e),_defineProperty(this,"form",void 0),_defineProperty(this,"options",{ajax:!1,disableSubmit:!1,scrollToAnchor:!1,prevButtonName:"form_previous_page_button",successBannerMessage:"Form has been submitted successfully!",errorBannerMessage:"Error! Please review the form and try submitting again.",errorClassBanner:"ff-form-errors",errorClassList:"ff-errors",errorClassField:"ff-has-errors",successClassBanner:"ff-form-success",removeMessages:null,renderSuccess:null,renderFormErrors:null,renderFieldErrors:null}),_defineProperty(this,"_beforeSubmitCallbackStack",[]),_defineProperty(this,"_successfulAjaxSubmitCallbackStack",[]),_defineProperty(this,"_failedAjaxSubmitCallbackStack",[]),_defineProperty(this,"_afterAjaxSubmitCallbackStack",[]),_defineProperty(this,"setOption",function(e,r){n.options[e]=r}),_defineProperty(this,"lockSubmit",function(){n._getSubmitButtons().forEach(function(e){e.disabled=!0})}),_defineProperty(this,"unlockSubmit",function(){n._getSubmitButtons().forEach(function(e){e.disabled=!1})}),_defineProperty(this,"_setInstances",function(){var r=n.form;e.instances.set(r,n),r.freeform=n}),_defineProperty(this,"_emitInitEvent",function(){var e=n.form,r=n._createNewEvent("freeform-ready");e.dispatchEvent(r)}),_defineProperty(this,"_attachListeners",function(){n.form.addEventListener("submit",n._onSubmit);var e=n.form.querySelectorAll("input, select, textarea");e.forEach(function(e){e.addEventListener("blur",function(e){n._removeMessageFrom(e.target)})})}),_defineProperty(this,"_onSubmit",function(e){n.lockSubmit();var r=n.form,t=n.options,o=!0;return n._beforeSubmitCallbackStack.forEach(function(e){var s=e.bind(n);s(r,t)||(o=!1)}),o?t.ajax?(e.preventDefault(),e.stopPropagation(),n._onSubmitAjax(e)):(n.form.submit(),!0):(e.preventDefault(),e.stopPropagation(),!1)}),_defineProperty(this,"_removeMessages",function(){if("function"==typeof n.options.removeMessages)return n.options.removeMessages=n.options.removeMessages.bind(n),n.options.removeMessages();var e=n.form,r=n.options,t=r.successClassBanner,o=r.errorClassBanner,s=r.errorClassList,i=r.errorClassField;e.querySelectorAll(".".concat(n._getClassArray(s).join("."))).remove();for(var a=e.querySelectorAll(".".concat(n._getClassArray(i).join("."))),c=0;c<a.length;c++){var u=a[c];n._removeMessageFrom(u)}e.querySelectorAll(".".concat(n._getClassArray(t).join("."))).remove(),document.querySelectorAll(".".concat(n._getClassArray(o).join("."))).remove()}),_defineProperty(this,"_removeMessageFrom",function(e){var r=n.options,t=r.errorClassList,o=r.errorClassField;n._removeClass(e,o);var s=e.parentNode.querySelector(".".concat(t));s&&s.remove()}),_defineProperty(this,"_renderSuccessBanner",function(){if("function"==typeof n.options.renderSuccess)return n.options.renderSuccess=n.options.renderSuccess.bind(n),n.options.renderSuccess();var e=n.form,r=n.options,t=r.successBannerMessage,o=r.successClassBanner,s=document.createElement("div");n._addClass(s,o);var i=document.createElement("p");i.appendChild(document.createTextNode(t)),s.appendChild(i),e.insertBefore(s,e.childNodes[0])}),_defineProperty(this,"_renderFieldErrors",function(e){if("function"==typeof n.options.renderFieldErrors)return n.options.renderFieldErrors=n.options.renderFieldErrors.bind(n),n.options.renderFieldErrors(e);var r=n.form,t=n.options,o=t.errorClassList,s=t.errorClassField;for(var i in e)if(e.hasOwnProperty(i)&&i){var a=e[i],c=document.createElement("ul");n._addClass(c,o);for(var u=0;u<a.length;u++){var l=a[u],f=document.createElement("li");f.appendChild(document.createTextNode(l)),c.appendChild(f)}for(var d=r.querySelectorAll("*[name="+i+"], *[name='"+i+"[]']"),m=0;m<d.length;m++){var p=d[m];n._addClass(p,s),p.parentElement.appendChild(c)}}}),_defineProperty(this,"_renderFormErrors",function(e){if("function"==typeof n.options.renderFormErrors)return n.options.renderFormErrors=n.options.renderFormErrors.bind(n),n.options.renderFormErrors(e);var r=n.form,t=n.options,o=t.errorClassBanner,s=t.errorBannerMessage,i=document.createElement("div");n._addClass(i,o);var a=document.createElement("p");if(a.appendChild(document.createTextNode(s)),i.appendChild(a),e.length){for(var c=document.createElement("ul"),u=0;u<e.length;u++){var l=e[u],f=document.createElement("li");f.appendChild(document.createTextNode(l)),c.appendChild(f)}i.appendChild(c)}r.insertBefore(i,r.childNodes[0])}),_defineProperty(this,"_onSuccessfulSubmit",function(e,r,t){n._successfulAjaxSubmitCallbackStack.forEach(function(n){return n(e,r,t)})}),_defineProperty(this,"_onFailedSubmit",function(e,r,t){n._failedAjaxSubmitCallbackStack.forEach(function(n){return n(e,r,t)})}),_defineProperty(this,"_onAfterSubmit",function(e,r,t){n._afterAjaxSubmitCallbackStack.forEach(function(n){return n(e,r,t)})}),_defineProperty(this,"_onSubmitAjax",function(e){var r=n.form,t=new FormData(r),o=new XMLHttpRequest;if(n._isSafari())for(var s=0;s<r.elements.length;s++)if("file"===r.elements[s].type&&""===r.elements[s].value){var i=r.elements[s];t["delete"](i.name)}var a=r.getAttribute("method"),c=r.getAttribute("action");return o.open(a,c?c:window.location.href,!0),o.setRequestHeader("Cache-Control","no-cache"),o.setRequestHeader("X-Requested-With","XMLHttpRequest"),o.setRequestHeader("HTTP_X_REQUESTED_WITH","XMLHttpRequest"),o.onload=function(){if(n._removeMessages(),200===o.status){var t=JSON.parse(o.response),s=t.success,i=t.finished,a=t.errors,c=t.formErrors,u=t.honeypot;if(s&&i?(r.reset(),n._onSuccessfulSubmit(e,r,t),n._renderSuccessBanner()):(a||c)&&(n._onFailedSubmit(e,r,t),n._renderFieldErrors(a),n._renderFormErrors(c)),u){var l=r.querySelector("input[name^=freeform_form_handle]");l&&(l.setAttribute("name",u.name),l.setAttribute("id",u.name),l.value=u.hash)}n._onAfterSubmit(e,r,t)}else console.error(o);n.unlockSubmit(r)},o.send(t),!1}),_defineProperty(this,"_getSubmitButtons",function(){var e=n.options,r=e.disableSubmit,t=e.prevButtonName;return r?n.form.querySelectorAll('*[type=submit]:not([name="'.concat(t,'"])')):[]}),_defineProperty(this,"_isSafari",function(){return navigator.userAgent.indexOf("Safari")>-1}),_defineProperty(this,"_getClassArray",function(e){return"string"==typeof e&&(e=e.split(" ")),e}),_defineProperty(this,"_addClass",function(e,r){n._getClassArray(r).forEach(function(r){return e.classList.add(r)})}),_defineProperty(this,"_removeClass",function(e,r){"string"==typeof r&&(r=r.split(" ")),r.forEach(function(r){return e.classList.remove(r)})}),_defineProperty(this,"_createNewEvent",function(e){var r;return"function"==typeof Event?r=new Event(e):(r=document.createEvent("Event"),r.initEvent(e,!0,!0)),r}),this.form=r,this.options=_objectSpread({},this.options,t),!this.form)return void console.error('Could not find a form with ID "'.concat(id,'"'));if(this._attachListeners(),this._setInstances(),this._emitInitEvent(),this.options.scrollToAnchor)var o=setInterval(function(){"complete"===document.readyState&&(clearInterval(o),document.getElementById(r.dataset.id).scrollIntoView())},300)}return _createClass(e,[{key:"addOnSubmitCallback",value:function(e){"function"==typeof e&&this._beforeSubmitCallbackStack.push(e)}},{key:"addOnSuccessfulAjaxSubmit",value:function(e){"function"==typeof e&&this._successfulAjaxSubmitCallbackStack.push(e)}},{key:"addOnFailedAjaxSubmit",value:function(e){"function"==typeof e&&this._failedAjaxSubmitCallbackStack.push(e)}},{key:"addOnAfterAjaxSubmit",value:function(e){"function"==typeof e&&this._afterAjaxSubmitCallbackStack.push(e)}}]),e}();_defineProperty(Freeform,"instances",new WeakMap),_defineProperty(Freeform,"getInstance",function(e){return Freeform.instances.get(e)}),Element.prototype.remove=function(){this.parentElement.removeChild(this)},NodeList.prototype.remove=HTMLCollection.prototype.remove=function(){for(var e=this.length-1;e>=0;e--)this[e]&&this[e].parentElement&&this[e].parentElement.removeChild(this[e])},window.NodeList&&!NodeList.prototype.forEach&&(NodeList.prototype.forEach=function(e,r){r=r||window;for(var t=0;t<this.length;t++)e.call(r,this[t],t,this)});