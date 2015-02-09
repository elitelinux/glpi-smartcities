<link rel="stylesheet" href="{$httpPath|cat:"css/styles.css"}" type="text/css" />

<div style="height: 200px; overflow-y: scroll; overflow-x: hidden" name="Historique">
    <table class='tab_cadre' style="width: 600px;">
        <TR><TH colspan="4">Historique du clonage</TH></TR>
        <TR bgcolor="#FFF4DF">
            <TD width="25px">Technicien</TD>
            <TD width="50px">Date</TD>
            <TD width="490px">Action</TD>
        </TR> 
        {$ligne = "grey"}
        {foreach from=$historique item=ligne_historique}
            {if $ligne eq "white"} {$ligne = "grey"}
            {else} {$ligne = "white"}
            {/if}
            <TR bgcolor="{$ligne}">
                <TD width="25px">{$ligne_historique.technicien}</TD>
                <TD width="50px">{$ligne_historique.date}</TD>
                <TD width="490px">{$ligne_historique.info}</TD>
            </TR>
        {/foreach}
    </table>    
</div>