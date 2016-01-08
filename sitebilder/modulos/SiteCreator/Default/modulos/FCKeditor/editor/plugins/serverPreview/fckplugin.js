/*
 * File Name: serverPreview\fckPlugin.js
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * 	Plugin for FCKeditor to send the current data to the server so it can be previewed in a custom way
 *
 *  version 0.1 15/02/2006
 *		
 * 
 * File Author:
 * 		Alfonso Martínez de Lizarrondo  
 *
 * Developed for Digital Impact
 *
 * How to Use:
 *
 * Add the plugin in your fckconfig.js like other plugins:
 *    FCKConfig.Plugins.Add( 'serverPreview' ) ;
 *
 * Change here the theForm.action = FCKPlugins.Items['serverPreview'].Path + 'preview.asp' ; 
 * to suit your needs if it will always use the same url,
 * or you can override this setting with a FCKConfig.ServerPreviewPath variable
 *
 * Add a 'serverPreview' button to your toolbar instead of the normal 'Preview'
 *
 * Put your magic in the preview.asp (or whatever you set the action of the form)
 *
 * Done!
 *
 */
 

// Register the related command.
var oServerPreview = function(name) 
{ 
	this.Name = name; 
}

//This is the function that creates a temporary form, gets the data and sends it to the server
oServerPreview.prototype.Execute = function() 
{
	//get the form to submit the data (a custom one, not the real)
	var theForm = document.getElementById('serverPreviewForm') ;
	if (!theForm) {
		//it doesn't exist still, we create it here
		theForm = document.createElement('FORM') ;
		theForm.method = 'POST' ;
		theForm.name = 'serverPreviewForm' ;
		theForm.id=theForm.name ;
		theForm.style.display = 'none' ;

		//this sets the default page where the data will be posted.
		//change as needed -->
		//path is relative to the editor, so append for this example the current path
		theForm.action = FCKConfig.BaseHref + 'index.php' ;
		//we can override that path for each user with a custom setting in FCKConfig
		// example: FCKConfig.ServerPreviewPath = 'customPreview.asp?Id=3' ;
		if (typeof(FCKConfig.ServerPreviewPath) == 'string')
			theForm.action = FCKConfig.ServerPreviewPath;

		//new window please
		theForm.target='_blank';
		document.body.appendChild( theForm );
	}

	//clear previous data
	theForm.innerHTML = '' ;
	//set the new content
	var input = document.createElement('INPUT') ;
	input.type = 'hidden';
	//change the name as needed -->
	input.name = 'htmlData' ;
	//set the data
	input.value = FCK.GetXHTML() ;
	//append the new input to the form
	theForm.appendChild( input );

	//that's all, append additional fields as needed, or set the variables in the previewPath

	//send the data to the server
	theForm.submit();
} 

//nothing interesting below this line, just registration of the plugin using the same data than the Preview Command

// manage the plugins' button behavior 
oServerPreview.prototype.GetState = function() 
{ 
  // default behavior, sometimes you wish to have some kind of if statement here 
  return FCK_TRISTATE_OFF; 
}
 

// Register the related command. 
FCKCommands.RegisterCommand( 'serverPreview', new oServerPreview('serverPreview'));

var oServerPreviewButton = new FCKToolbarButton( 'serverPreview', FCKLang.Preview ) ;
oServerPreviewButton.IconPath = FCKConfig.PluginsPath + 'serverPreview/preview.gif' ;
oServerPreviewButton.SourceView	= true ;

FCKToolbarItems.RegisterItem( 'serverPreview', oServerPreviewButton ) ;

