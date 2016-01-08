<html>
    {include file="header.tpl"}
    <body>
        <div id="geral">
            <div id="topo">
                <div id="logo"><img alt="logo" src="templates/Image/logo.gif" /></div>
                <div id="flash"><img src="templates/Image/flash.jpg" alt="" /></div>
            </div>
            {insert name="menu" id="menu"}
            <div id="centro">{insert name="fncpages"}</div>
            {include file="feet.tpl"}
        </div>
    </body>
</html>