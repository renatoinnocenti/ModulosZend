/*
    editor de texto
*/
/****************************************/
var idret
var idret2
var conf = true;
function confirmar(msn,ok,cancel){
    conf = confirm(msn,ok,cancel);
}
function loadPage(id,page){
 //alert(id + redir);
    idret= id;
    if(conf == true)
    x_loadPage(page,loadPage_cb);
}
function loadPage_cb(cb){
    //alert(cb);
     var t = "document.getElementById('"+idret+"').innerHTML ='"+cb+"';";
        eval(t);
}
function CheckForm(id){
    var vec = new Array();
    var setform=document.getElementById(id);
    idret = document.getElementById('retorno').value;
    idret2 = id;
    var jsonvar = '{"'+id+'": [';
    for(var i=0;i<setform.length;i++){
     if(setform[i].name != '' ){
        if((setform[i].type == 'radio' || setform[i].type == 'checkbox') && setform[i].checked == false)continue;
     jsonvar = jsonvar.concat('["'+setform[i].name+'" , "'+setform[i].value+'"]');
     if(i < (setform.length -1))
        jsonvar = jsonvar.concat(',');
    }
     }
    Json = jsonvar.concat(']}');
    x_CheckForm(Json,id,CheckForm_cb);
    }
function CheckForm_cb(cb){
    if(cb){
      var t = "document.getElementById('"+idret+"').innerHTML ='"+cb+"';";
      eval(t);
      }else{
      document.getElementById(idret2).submit();
      }
}
function SemiPost(id,page,retorno){
    var vec = new Array();
    var setform=document.getElementById(id);
    idret = retorno;
    idret2 = id;
    var jsonvar = '{"'+id+'": [';
    for(var i=0;i<setform.length;i++){
     if(setform[i].name != ''){
     jsonvar = jsonvar.concat('["'+setform[i].name+'" , "'+setform[i].value+'"]');
     if(i < (setform.length -1))
        jsonvar = jsonvar.concat(',');
    }
     }
    Json = jsonvar.concat(']}');
    if(conf == true)
    x_SemiPost(Json,id,page,loadPage_cb);
}
function SemiPost_cb(cb){
    //alert(cb);
    if(cb){
      var t = "document.getElementById('"+idret+"').innerHTML ='"+cb+"';";
      eval(t);
      }else{
      document.getElementById(idret2).submit();
      }
    }
function UEFV()
{
        this.UpdateEditorFormValue = function()
        {
                for ( i = 0; i < parent.frames.length; ++i )
                        if ( parent.frames[i].FCK )
                                parent.frames[i].FCK.UpdateLinkedField();
        }
}
// instantiate the class
var ObjUEFV = new UEFV();