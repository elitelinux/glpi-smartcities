{* 
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//
*}
<style type="text/css">
    {literal}
        .form_Fond_Gris 
            {
            background-color:silver;
            }
    {/literal}
</style>

{if $reforme eq 'true'}
<FORM METHOD=POST ACTION={$post_Form_Reforme}>
    <table class='tab_cadre_fixe'>
        <tr>
            <th colspan="2">Réforme</th>
        </tr>
        <tr>
            <td align="center" colspan="2" class="blue">
                Ce module vous permet de réformer une machine. <br>
                Si la machine est inscrite dans l'active directory elle en sera supprimée. <br>
                Un mail sera envoyé à la liste reforme associé au bon généré grace aux informations suivantes:<br>
            </td>
        </tr>
        <tr>
            <td align="right"><label>Id:</label> <input type="text" class="form_Fond_Gris" size='15' name='id' value="{$info.id}" readonly/></td>
            <td align="left"><input type="text" class="form_Fond_Gris" size='15' name='name' value="{$info.name}" readonly/> <label>:Nom</label></td>
        </tr>
        <tr>
            <td align="right"><label>Lieu:</label> <input type="text" class="form_Fond_Gris" size='15' name="lieu" value="{$info.lieu}" readonly/></td>
            <td align="left"><input type="text" class="form_Fond_Gris" size='15' name="numSerie" value="{$info.numserie}" readonly/> <label>:Numéro de série</label> </td>
        </tr>    
        <tr>
            <td align="right"><label>Fournisseur:</label> <input type="text" class="form_Fond_Gris" size='15' name="fournisseur" value="{$info.fournisseur}" readonly/></td>
            <td align="left"><input type="text" class="form_Fond_Gris" size='15' name="dateAchat" value="{$info.dateachat}" readonly/> <label>:Date d'achat</label> </td>
        </tr>   
        <tr>
            <td align="right"><label>Numéro Immobilisation:</label> <input type="text" class="form_Fond_Gris" size='15' name="immobilisation" value="{$info.immo}" readonly/></td>
            <td align="left">
                <input type='hidden' name='typeItem' value={$typeItem}>
                <input type='submit' name='Reformer' class='submit' value="Réformer">
            </td>
        </tr>           
    </table>
{$endform}
{elseif $reforme eq 'false'}
    
    <table class='tab_cadre_fixe'>
        <tr>
            <th>Réforme</th>
        </tr>
        <tr>
            <td align="center">
                Cette machine a été réformée le {$date} par {$technicien}. <br>
                Le bon de réforme est disponible <a href={$bon_reforme}>ici</a> <br>
                {if $domaine != ""}
                    La machine a été désactivé de l'AD: {$domaine} <br>
                {else}
                    La machine n'était pas intégrée dans un domaine <br>
                {/if}
            </td>
        </tr>
        {if $callback == "on"}
        <tr>
            <td align="center">
                <FORM METHOD=POST ACTION={$target}>
                    <input type='hidden' name='id' value={$id}>
                    <input type='hidden' name='typeItem' value={$typeItem}>
                    <input type='submit' name='Restaurer' class='submit' value="Restaurer">
                {$endform}
            </td>
        </tr>
        {/if}
    </table>
{/if}