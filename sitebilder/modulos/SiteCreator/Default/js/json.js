/*
* Helma License Notice
*
* The contents of this file are subject to the Helma License
* Version 2.0 (the "License"). You may not use this file except in
* compliance with the License. A copy of the License is available at
* http://adele.helma.org/download/helma/license.txt
*
* Copyright 1998-2006 Helma Software. All Rights Reserved.
*
*/


/*
     json.js
     2006-04-28
     2006-05-27 added prettyPrint argument

     This file adds these methods to JavaScript:

         object.toJSONString(prettyPrint)

             This method produces a JSON text from an object. The
             object must not contain any cyclical references.

         array.toJSONString(prettyPrint)

             This method produces a JSON text from an array. The
             array must not contain any cyclical references.

         string.parseJSON()

             This method parses a JSON text to produce an object or
             array. It will return false if there is an error.

+           added prettyPrint argument
             prettyPrint ... if set to true the resulting string will  
be formated
                             with tabs and returns to be more human  
readable.
                             by Matthias.Platzer at knallgrau.at

*/
(function () {
     var INDENT = "\t";
     var NEWLINE = "\n";
     var pPr = false;
     var indentLevel = 0;
     var indent = function(a) {
         if (!pPr) return a;
         for (var l=0; l<indentLevel; l++) {
             a[a.length] = INDENT;
         }
         return a;
     };

     var newline = function(a) {
         if (pPr) a[a.length] = NEWLINE;
         return a;
     };

     var m = {
             '\b': '\\b',
             '\t': '\\t',
             '\n': '\\n',
             '\f': '\\f',
             '\r': '\\r',
             '"' : '\\"',
             '\\': '\\\\'
         },
         s = {
             array: function (x) {
                 var a = ['['], b, f, i, l = x.length, v;
                 a = newline(a);
                 indentLevel++;
                 for (i = 0; i < l; i += 1) {
                     v = x[i];
                     f = s[typeof v];
                     if (f) {
                         v = f(v);
                         if (typeof v == 'string') {
                             if (b) {
                                 a[a.length] = ',';
                                 a = newline(a);
                             }
                             a = indent(a);
                             a[a.length] = v;
                             b = true;
                         }
                     }
                 }
                 indentLevel--;
                 a = newline(a);
                 a = indent(a);
                 a[a.length] = ']';
                 return a.join('');
             },
             'boolean': function (x) {
                 return String(x);
             },
             'null': function (x) {
                 return "null";
             },
             number: function (x) {
                 return isFinite(x) ? String(x) : 'null';
             },
             object: function (x, formatedOutput) {
                 if (x) {
                     if (x instanceof Array) {
                         return s.array(x);
                     }
                     var a = ['{'], b, f, i, v;
                     a = newline(a);
                     indentLevel++;
                     for (i in x) {
                         v = x[i];
                         f = s[typeof v];
                         if (f) {
                             v = f(v);
                             if (typeof v == 'string') {
                                 if (b) {
                                     a[a.length] = ',';
                                     a = newline(a);
                                 }
                                 a = indent(a);
                                 a.push(s.string(i), ((pPr) ? ' : ' :  
':'), v);
                                 b = true;
                             }
                         }
                     }
                     indentLevel--;
                     a = newline(a);
                     a = indent(a);
                     a[a.length] = '}';
                     return a.join('');
                 }
                 return 'null';
             },
             string: function (x) {
                 if (/["\\\x00-\x1f]/.test(x)) {
                     x = x.replace(/([\x00-\x1f\\"])/g, function(a, b) {
                         var c = m[b];
                         if (c) {
                             return c;
                         }
                         c = b.charCodeAt();
                         return '\\u00' +
                             Math.floor(c / 16).toString(16) +
                             (c % 16).toString(16);
                     });
                 }
                 return '"' + x + '"';
             }
         };

     /**
      * This method produces a JSON text from an object.
      * The object must not contain any cyclical references.
      * @param Boolean if true, formats output with line breaks and  
indentations
      * @return String literal of the serialized object
      */
     Object.prototype.toJSONString = function (prettyPrint) {
         pPr = prettyPrint;
         return s.object(this);
     };

     /**
      * This method produces a JSON text from an array.
      * The object must not contain any cyclical references.
      * @param Boolean if true, formats output with line breaks and  
indentations
      * @return String literal of the serialized object
      */
     Array.prototype.toJSONString = function (prettyPrint) {
         pPr = prettyPrint;
         return s.array(this);
     };
})();

/**
* This method parses a JSON text to produce an object or
* array. It will return false if there is an error.
* @return Object of/with the according prototype and properties
*/
String.prototype.parseJSON = function () {
     try {
         return !(/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/.test(
                 this.replace(/"(\\.|[^"\\])*"/g, ''))) &&
             eval('(' + this + ')');
     } catch (e) {
         return false;
     }
};
