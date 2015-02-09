{* 
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//
*}
<body>{literal}
    <link rel="stylesheet" type="text/css" href="{/literal}{$targetCSS}{literal}" media="all"/>
</head>{/literal}
<div id="txtHint">{#$erreur#}  {#$test#}
    <table class='tab_cadre_fixe'>
        <tr>
            <th colspan="3">Gestion des groupes de l'ordinateur</th>
        </tr>
{if $erreur eq "null"}
    <tr>
        <td><table width="430px"><TH>Membre de</TH></table></td>
        <td><table width="430px"><TH>Groupes disponibles</TH></table></td>
    </tr>
    <tr>
        <td>
            <div style="height: 200px; width: 450px; overflow-y: scroll; overflow-x: hidden" name="groupeOrdinateur">
                <table width="430px">
                {foreach from=$groupeOrdinateur item=groupeOrdi}
                <TR>
                    <TD width="400px" align="right">{$groupeOrdi}</TD>
                    <TD width="30px"><button id="btn-suppGroupe" onclick="change('supp','{$groupeOrdi}','{$id}')">
                        <img alt='' title="Retirer" src='{$httpPath}pics/right.png'></button></TD>
                </TR>
                {/foreach}
                </table>    
            </div>
        </td>
        <td>
            <div style="height: 200px; width: 450px; overflow-y: scroll; overflow-x: hidden" name="groupeAD">
                <table width="430px">
                {foreach from=$groupeAD item=groupeLDAP}
                <TR>
                    <TD width="30px"><button id="btn-addGroupe" onclick="change('add','{$groupeLDAP}','{$id}')">
                        <img alt='' title="Ajouter" src='{$httpPath}pics/left.png'></button></TD>
                    <TD width="400px">{$groupeLDAP}</TD>
                </TR>
                {/foreach}
                </table>    
            </div>
        </td>
    </tr> 
    {if $listeInDomain}
    <tr>
        <td colspan="2" align="center">
            <br>
            <img alt='' title="Attention" src='{$httpPath}pics/warning.png'>
            La machine est référencée pour faire partie de ce domaine : <span class="color_info">{$domain}</span> 
            <br>mais elle appartient également aux domaines suivants:
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <div style="height: 60px; width: 350px; overflow-y: scroll; overflow-x: hidden" name="groupeAD">
                <table width="330px">
                    <TR>
                        {foreach from=$listeInDomain item=autreDomain}
                            <TD align="center"><span class="color_info_value">{$autreDomain}</span></TD>
                            <td align="center">
                                <FORM METHOD=POST ACTION={$target}>
                                <input type='hidden' name='id' value={$id}>
                                <input type='hidden' name='domain' value={$autreDomain}>
                                <input type='hidden' name='identifiant' value="domain">
                                <input type='hidden' name='action' value="basculer">
                                <input type='submit' name='Basculer' class='submit' value="Basculer">
                               {$endform}                                
                            </td>
                            <td align="center">
                                <FORM METHOD=POST ACTION={$target}>
                                <input type='hidden' name='id' value={$id}>
                                <input type='hidden' name='domain' value={$autreDomain}>
                                <input type='hidden' name='identifiant' value="domain">
                                <input type='hidden' name='action' value="supprimer">
                                <input type='submit' name='Supprimer' class='submit' value="Supprimer">
                               {$endform}
                            </td>
                        {/foreach}
                    </TR>
                </table> 
            </div>

        </td>
    </tr>
    {/if}
    {if $listeDomain}
        <tr>
            <td colspan="3">
                <table class='tab_cadre_fixe' style="height: 60px; width: 400px;">
                    <tr>
                        <th colspan="3">D'autres domaines sont disponibles:</th>
                    </tr>
                    <tr>
                        <td align="right">Basculer la machine dans le domaine: </td>
                        <td align="center">
                            <SELECT id="adddomaine" size="1">
                            {foreach from=$listeDomain item=item}<OPTION value="{$item}">{$item}{/foreach}
                            </SELECT>
                        </td>
                        <td align="left">
                            <button id="btn-domaine" onclick="addDomaine('{$id}')">
                            <img alt='' title="Domaine" src='{$httpPath}pics/actualiser.png'>Valider</button></TD>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    {/if}
{elseif $erreur eq "notInAD"}
    <tr>
        <td colspan="3" align="center">Le domaine {$domain} est référencé dans GLPI mais la machine n'existe pas dans ce domaine!</td>
    </tr>
    <tr>
        <td colspan="3">
            <table class='tab_cadre_fixe' style="height: 60px; width: 400px;">
                <tr>
                    <th colspan="3">Créer la machine dans le domaine {$domain}</th>
                </tr>
                <tr>
                    <td align="center">
                        <SELECT id="adddomaine" size="1">
                            <OPTION value="{$domain}">{$domain}
                        </SELECT>
                    </td>
                    <td align="left">
                        <button id="btn-domaine" onclick="addDomaine('{$id}')"><img alt='' title="Domaine" src='{$httpPath}pics/add.png'>Valider</button></TD>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    {if $listeDomain}
        <tr>
            <td colspan="3" align="center">La machine existe dans ces domaines :</td>
        </tr> 
        <tr>
            <td align="right">Enregistrer ce domaine dans GLPI: </td>
            <td align="center">
                <SELECT id="adddomaine" size="1">
                    {foreach from=$listeDomain item=item}<OPTION>{$item}{/foreach}
                </SELECT>
            </td>
            <td align="left">
                <button id="btn-domaine" onclick="addDomaine('{$id}')"><img alt='' title="Domaine" src='{$httpPath}pics/add.png'>Valider</button></TD>
            </td>
        </tr>
    {/if}
{else}
    <tr>
        <td colspan="3" align="center">Aucun domain référencé dans GLPI pour cet ordinateur</td>
    </tr> 
    {if $listeInDomain}
        <tr>
            <td colspan="3">
                <table class='tab_cadre_fixe' style="height: 60px; width: 400px;">
                    <tr>
                        <th colspan="3">La machine existe dans ces domaines :</th>
                    </tr>
                    <tr>
                        <td align="right">
                            Référencer ce domaine dans GLPI:
                        </td>
                        <td align="center">
                            <SELECT id="domaine" size="1">
                                {foreach from=$listeInDomain item=item}<OPTION>{$item}{/foreach}
                            </SELECT>
                        </td>
                        <td align="left">
                            <button id="btn-domaine" onclick="addDomaineGLPI('{$id}')"><img alt='' title="Domaine" src='{$httpPath}pics/edit.png'>Valider</button></TD>
                        </td>
                    </tr>
                </table>
                </td>
        </tr>
    {/if}
    {if $listeAD}
        <tr>
            <td colspan="3">
                <table class='tab_cadre_fixe' style="height: 60px; width: 400px;">
                    <tr>
                        <th colspan="3">La machine peut être créé dans ces domaines :</th>
                    </tr>
                    <tr>
                        <td align="right">
                            Créer la machine dans le domaine:
                        </td>
                        <td align="center">
                            <SELECT id="adddomaine" size="1">
                                {foreach from=$listeAD item=item}<OPTION>{$item}{/foreach}
                            </SELECT>
                        </td>
                        <td align="left">
                            <button id="btn-domaine" onclick="addDomaine('{$id}')"><img alt='' title="Domaine" src='{$httpPath}pics/add.png'>Valider</button></TD>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    {/if}
{/if}
    <tr>
        <td colspan="3" align="center">
            <button id="btn-log" onclick="log()"><img alt='' title="Historique" src='{$httpPath}pics/reservation-3.png'>Historique</button></TD>
        </td>
    </tr>
</table>
</div>

<script type="text/javascript">  
    {literal}    
    function change(action,groupe,id)
        {
        var DATA = "action="+action+"&groupe="+groupe+"&id="+id;  
        
        if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
        else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
                  
        xmlhttp.onreadystatechange=function()
          {
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {document.getElementById("txtHint").innerHTML=xmlhttp.responseText;}
          }
        xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/groupead/ajax/groupead.ajax.php"}{literal}",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(DATA);
        }
        
    function log()
        {
        window.open ("{/literal}{$httpPath|cat:"plugins/groupead/popup/groupead.popup.php?id=$id"}{literal}",
        'Historique du peuplement des groupes', 
        config='height=200, width=600, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no')
        }    
    
    /**
     * Ajoute la machine au domaine sélectionné
     * @param int id de la machine
     * @returns affiche la liste des groupes
     */
    function addDomaine(id){
        var DATA = "action=addDomaine&id="+id+"&domaine="+$('#adddomaine option:selected').val();  

        if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
        else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
                  
        xmlhttp.onreadystatechange=function()
          {
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {document.getElementById("txtHint").innerHTML=xmlhttp.responseText;}
          }
        xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/groupead/ajax/groupead.ajax.php"}{literal}",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(DATA);
    }
    
    /**
     * Ajoute la machine au domaine sélectionné
     * @param int id de la machine
     * @returns affiche la liste des groupes
     */
    function addDomaineGLPI(id){
        var DATA = "action=addDomaineGLPI&id="+id+"&domaine="+$('#domaine option:selected').val();  

        if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
        else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
                  
        xmlhttp.onreadystatechange=function()
          {
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {document.getElementById("txtHint").innerHTML=xmlhttp.responseText;}
          }
        xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/groupead/ajax/groupead.ajax.php"}{literal}",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(DATA);
    }
    {/literal}           
</script>

