FCKCommands.RegisterCommand(
   'Teste',
    new FCKDialogCommand(
        "Teste",
        "Teste",
        'http://localhost/guia/index.php?page=traducao', 500, 400
        )
    );
   var oTesteItem = new FCKToolbarButton('Teste', 'Teste');
oTesteItem.IconPath = FCKConfig.PluginsPath + 'teste/cake.gif' ;
FCKToolbarItems.RegisterItem( 'Teste', oTesteItem ) ;    