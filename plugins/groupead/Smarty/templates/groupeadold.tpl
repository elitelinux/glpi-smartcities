{* 
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//
*}
<div id="txtHint">
{if $erreur eq "null"}
    <table class='tab_cadre_fixe'>
        <tr>
            <th colspan="2">Gestion des groupes de l'ordinateur</th>
        </tr>
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
                        <TD width="30px"><button id="btn-suppGroupe" onclick="change('supp','{$groupeOrdi}','{$id}')"><img alt='' title="Retirer" src='{$httpPath}pics/right.png'></button></TD>
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
                        <TD width="30px"><button id="btn-addGroupe" onclick="change('add','{$groupeLDAP}','{$id}')"><img alt='' title="Ajouter" src='{$httpPath}pics/left.png'></button></TD>
                        <TD width="400px">{$groupeLDAP}</TD>
                    </TR>
                    {/foreach}
                    </table>    
                </div>
            </td>
        </tr>  
{else}
    <table class='tab_cadre_fixe'>
        <tr>
            <th colspan="3">Gestion des groupes de l'ordinateur</th>
        </tr>
        <tr>
            <td colspan="3" align="center">Aucun domain référencé pour cet ordinateur</td>
        </tr> 
        <tr>
            <td align="right">Créer la machine dans le domaine: </td>
            <td align="center">
                <SELECT id="domaine" size="1">
                    {foreach from=$listeAD item=item}<OPTION>{$item}{/foreach}
                </SELECT>
            </td>
            <td align="left">
                <button id="btn-domaine" onclick="addDomaine('{$id}')"><img alt='' title="Domaine" src='{$httpPath}pics/add.png'>Valider</button></TD>
            </td>
        </tr>
{/if}
        <tr>
            <td colspan="2" align="center">
                <button id="btn-log" onclick="log()"><img alt='' title="Historique" src='{$httpPath}pics/reservation-3.png'>Historique</button></TD>
            </td>
        </tr>
    </table>
</div>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>  
<script type="text/javascript">  
    {literal}    
    function change(action,groupe,id)
        {
        var DATA = "action="+action+"&groupe="+groupe+"&id="+id+"&version=old";  
        
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
        var DATA = "action=addDomaine&id="+id+"&domaine="+$('#domaine option:selected').text()+"&version=old";  
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

