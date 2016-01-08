var variable            = null;
var FCK                 = window.opener.FCK;
        function ok() {
                if(variable != null) {
                        FCK.Focus();
                        var B = FCK.EditorDocument.selection.createRange(); //only works in IE
                        B.text = variable;
                }
                window.close();
        }