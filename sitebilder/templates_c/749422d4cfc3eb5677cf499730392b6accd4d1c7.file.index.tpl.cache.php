<?php /* Smarty version Smarty-3.0.6, created on 2011-01-28 04:58:42
         compiled from ".\templates\index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:277144d423ef25d47d9-47317064%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '749422d4cfc3eb5677cf499730392b6accd4d1c7' => 
    array (
      0 => '.\\templates\\index.tpl',
      1 => 1296168045,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '277144d423ef25d47d9-47317064',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<html>
<body>
<div id="main">
<?php if (isset($_smarty_tpl->getVariable('MS_ERROR_SHOW',null,true,false)->value)){?>
	<?php $_template = new Smarty_Internal_Template("ms_error.tpl", $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null);
 echo $_template->getRenderedTemplate();?><?php $_template->updateParentVariables(0);?><?php unset($_template);?>
<?php }?>
    <b><?php echo $_smarty_tpl->getConfigVariable('teste');?>
</b>
</div>

</body>
</html>