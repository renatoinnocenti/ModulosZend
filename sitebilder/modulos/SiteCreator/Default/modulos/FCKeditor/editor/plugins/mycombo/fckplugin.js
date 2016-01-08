//********START********
/*
1st, define the 'command' to execute when an item from the list is selected - ie, insert the text.
*/
var FCKMyCombo_command = function(name) {
	/*
	The 'name' parameter doesn't do anything. You could define above using:
	a) var FCKMyCombo_command = function() {
		or
	b) var FCKMyCombo_command = function(name, var1, var2, var3) {
		or
	c) what ever you like
	Just remember to reflect this at the 'FCKCommands.RegisterCommand( 'mycombocommand' , new FCKMyCombo_command('any_name') ) ;' line below.
	EG:
	a) FCKCommands.RegisterCommand( 'mycombocommand' , new FCKMyCombo_command() ) ;
	b) FCKCommands.RegisterCommand( 'mycombocommand' , new FCKMyCombo_command('any_name', 'var1', 'var2', 'var3') ) ;
	c) what ever you like
	*/
	this.Name = name ; 
} 
//This get's executed when an item from the combo list gets selected
FCKMyCombo_command.prototype.Execute = function(itemText, itemLabel) {  
	 if (itemText != "")
	 	FCK.InsertHtml(itemText);
}
//was getting GetState is not a function (or similar) errors, so added this.
FCKMyCombo_command.prototype.GetState = function() {
	return;
}
FCKCommands.RegisterCommand( 'mycombocommand' , new FCKMyCombo_command('any_name') ) ; 



/*
2nd, create the Combo object
*/
var FCKToolbarMyCombo=function(tooltip,style){
	this.Command=FCKCommands.GetCommand('mycombocommand');//the command to execute when an item is selected
	this.CommandName = 'mycombocommand';
	this.Label=this.GetLabel();
	this.Tooltip=tooltip?tooltip:this.Label; //Doesn't seem to work
	this.Style=style; //FCK_TOOLBARITEM_ICONTEXT OR FCK_TOOLBARITEM_ONLYTEXT
};
FCKToolbarMyCombo.prototype=new FCKToolbarSpecialCombo;
//Label to appear in the FCK toolbar
FCKToolbarMyCombo.prototype.GetLabel=function(){
	return "My&nbsp;Combo";
};
//Add the items to the combo list
FCKToolbarMyCombo.prototype.CreateItems=function(A){
	//this._Combo.AddItem(itemText, itemLabel); //see FCKMyCombo_command.prototype.Execute = function(itemText, itemLabel) above
	this._Combo.AddItem('Item 1 text to insert', '<span style="color:#000000;font-weight: normal; font-size: 10pt;">Item 1</span>');
	this._Combo.AddItem('Item 2 text to insert', '<span style="color:#000000;font-weight: normal; font-size: 10pt;">Item 2</span>');
	this._Combo.AddItem('Item 3 text to insert', '<span style="color:#000000;font-weight: normal; font-size: 10pt;">Item 3</span>');
}

//Register the combo with the FCKEditor
FCKToolbarItems.RegisterItem( 'mycombo'	, new FCKToolbarMyCombo( 'My Combo', FCK_TOOLBARITEM_ICONTEXT ) ) ; //or FCK_TOOLBARITEM_ONLYTEXT

//********END********  