{* 
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//
*}
<header>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />    
</header>
	
{if $auth == "true"}
<div id="divAD">
    
    <table class='tab_cadre_fixe'>
        <tr>
            <th align="center" style="width: 15px">
                {if $trie == "id_item" and $ASCDESC == "ASC"}
                    <a href="{$httpPath}plugins/reforme/front/plugin.form.php?trie=id_item&ASCDESC=ASC">    
                {else}
                    <a href="{$httpPath}plugins/reforme/front/plugin.form.php?trie=id_item&ASCDESC=DESC">    
                {/if}
                id</a>
            </th>
            <th align="center" style="width: 30px">
                {if $trie == "type_item" and $ASCDESC == "ASC"}
                    <a href="{$httpPath}plugins/reforme/front/plugin.form.php?trie=type_item&ASCDESC=ASC">
                {else}
                    <a href="{$httpPath}plugins/reforme/front/plugin.form.php?trie=type_item&ASCDESC=DESC">
                {/if}
                type</a>
            </th>
            <th align="center" style="width: 30px">
                {if $trie == "name" and $ASCDESC == "ASC"}
                    <a href="{$httpPath}plugins/reforme/front/plugin.form.php?trie=name&ASCDESC=ASC">
                {else}
                    <a href="{$httpPath}plugins/reforme/front/plugin.form.php?trie=name&ASCDESC=DESC">
                {/if}
                name</a>
            </th>
            <th align="center" style="width: 30px">
                {if $trie == "technicien" and $ASCDESC == "ASC"}
                    <a href="{$httpPath}plugins/reforme/front/plugin.form.php?trie=technicien&ASCDESC=ASC">
                {else}
                    <a href="{$httpPath}plugins/reforme/front/plugin.form.php?trie=technicien&ASCDESC=DESC">
                {/if}
                technicien</a>
            </th>
            <th align="center" style="width: 100px">
                {if $trie == "date" and $ASCDESC == "ASC"}
                    <a href="{$httpPath}plugins/reforme/front/plugin.form.php?trie=date&ASCDESC=ASC">
                {else}
                    <a href="{$httpPath}plugins/reforme/front/plugin.form.php?trie=date&ASCDESC=DESC">
                {/if}
                date</a>
            </th>
            <th align="center" style="width: 200px">
                {if $trie == "bon_reforme" and $ASCDESC == "ASC"}
                    <a href="{$httpPath}plugins/reforme/front/plugin.form.php?trie=bon_reforme&ASCDESC=ASC">
                {else}
                    <a href="{$httpPath}plugins/reforme/front/plugin.form.php?trie=bon_reforme&ASCDESC=DESC">
                {/if}
                bon réforme</a>
            </th>
        </tr>
        {foreach from=$listeReforme item=reforme}
        <tr>
            <td align="center" style="width: 15px">
                <a href='{$reforme['id_link']}'> {$reforme['id_item']}</a>    
            </td>
            <td align="center" style="width: 30px">
                {$reforme['type_item']}     
            </td>
            <td align="center" style="width: 30px">
                {$reforme['name']}
            </td>
            <td align="center" style="width: 30px">
                {$reforme['technicien']}         
            </td>
            <td align="center" style="width: 100px">
                {$reforme['date']}               
            </td>
            <td align="center" style="width: 200px">
                <a href='{$reforme['reforme_link']}'>{$reforme['bon_reforme']}</a>
            </td>
        </tr>
        {/foreach}
    </table>
</div>
{/if}