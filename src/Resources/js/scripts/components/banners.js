!function(n){var e={};function t(Q){if(e[Q])return e[Q].exports;var i=e[Q]={i:Q,l:!1,exports:{}};return n[Q].call(i.exports,i,i.exports,t),i.l=!0,i.exports}t.m=n,t.c=e,t.d=function(n,e,Q){t.o(n,e)||Object.defineProperty(n,e,{enumerable:!0,get:Q})},t.r=function(n){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(n,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(n,"__esModule",{value:!0})},t.t=function(n,e){if(1&e&&(n=t(n)),8&e)return n;if(4&e&&"object"==typeof n&&n&&n.__esModule)return n;var Q=Object.create(null);if(t.r(Q),Object.defineProperty(Q,"default",{enumerable:!0,value:n}),2&e&&"string"!=typeof n)for(var i in n)t.d(Q,i,function(e){return n[e]}.bind(null,i));return Q},t.n=function(n){var e=n&&n.__esModule?function(){return n.default}:function(){return n};return t.d(e,"a",e),e},t.o=function(n,e){return Object.prototype.hasOwnProperty.call(n,e)},t.p="",t(t.s=13)}({13:function(module,exports){eval('$(function () {\n  $(".alert-dismissible a.close").on({\n    click: function click(event) {\n      var link = event.target.href;\n      var $alert = $(event.target).parents(".alert:first");\n      Craft.postActionRequest(link, {}, function () {\n        return $alert.remove();\n      });\n      event.stopPropagation();\n      event.preventDefault();\n      return false;\n    }\n  });\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvY29tcG9uZW50cy9iYW5uZXJzLmpzPzk4NmIiXSwibmFtZXMiOlsiJCIsIm9uIiwiY2xpY2siLCJldmVudCIsImxpbmsiLCJ0YXJnZXQiLCJocmVmIiwiJGFsZXJ0IiwicGFyZW50cyIsIkNyYWZ0IiwicG9zdEFjdGlvblJlcXVlc3QiLCJyZW1vdmUiLCJzdG9wUHJvcGFnYXRpb24iLCJwcmV2ZW50RGVmYXVsdCJdLCJtYXBwaW5ncyI6IkFBQUFBLENBQUMsQ0FBQyxZQUFNO0FBQ05BLEdBQUMsQ0FBQyw0QkFBRCxDQUFELENBQWdDQyxFQUFoQyxDQUFtQztBQUNqQ0MsU0FBSyxFQUFFLGVBQUNDLEtBQUQsRUFBVztBQUNoQixVQUFNQyxJQUFJLEdBQUdELEtBQUssQ0FBQ0UsTUFBTixDQUFhQyxJQUExQjtBQUNBLFVBQU1DLE1BQU0sR0FBR1AsQ0FBQyxDQUFDRyxLQUFLLENBQUNFLE1BQVAsQ0FBRCxDQUFnQkcsT0FBaEIsQ0FBd0IsY0FBeEIsQ0FBZjtBQUVBQyxXQUFLLENBQUNDLGlCQUFOLENBQXdCTixJQUF4QixFQUE4QixFQUE5QixFQUFrQztBQUFBLGVBQU1HLE1BQU0sQ0FBQ0ksTUFBUCxFQUFOO0FBQUEsT0FBbEM7QUFFQVIsV0FBSyxDQUFDUyxlQUFOO0FBQ0FULFdBQUssQ0FBQ1UsY0FBTjtBQUNBLGFBQU8sS0FBUDtBQUNEO0FBVmdDLEdBQW5DO0FBWUQsQ0FiQSxDQUFEIiwiZmlsZSI6IjEzLmpzIiwic291cmNlc0NvbnRlbnQiOlsiJCgoKSA9PiB7XG4gICQoXCIuYWxlcnQtZGlzbWlzc2libGUgYS5jbG9zZVwiKS5vbih7XG4gICAgY2xpY2s6IChldmVudCkgPT4ge1xuICAgICAgY29uc3QgbGluayA9IGV2ZW50LnRhcmdldC5ocmVmO1xuICAgICAgY29uc3QgJGFsZXJ0ID0gJChldmVudC50YXJnZXQpLnBhcmVudHMoXCIuYWxlcnQ6Zmlyc3RcIik7XG5cbiAgICAgIENyYWZ0LnBvc3RBY3Rpb25SZXF1ZXN0KGxpbmssIHt9LCAoKSA9PiAkYWxlcnQucmVtb3ZlKCkpO1xuXG4gICAgICBldmVudC5zdG9wUHJvcGFnYXRpb24oKTtcbiAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICByZXR1cm4gZmFsc2U7XG4gICAgfSxcbiAgfSk7XG59KTtcbiJdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///13\n')}});