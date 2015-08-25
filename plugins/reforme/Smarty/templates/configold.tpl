{* 
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//
*}
<body>{literal}
    <link rel="stylesheet" type="text/css" href="{/literal}{$targetCSS}{literal}" media="all"/>
</head>{/literal}

<div id="info">
    <table class='tab_cadre_fixe'>
        <tr>
            <th colspan="4">Configuration du plugin Reforme</th>
        </tr>
        <tr>
            <td align="center" colspan="4" class="blue">
                La configuration du module doit être renseignée avant son utilisation. <br>
                Pour chaque Active Directory disponible, vous devez entrer les informations demandées.<br>
            </td>
        </tr>
    </table>
</div>

<div id="configAdministrative">
    <table class='tab_cadre_fixe'>
        <tr>
            <td>
                <table>
                    <tr>
                        <td align="right"><label rel=tooltip title="Nom de la structure ou de l'entreprise">Structure:</label> </td>
                        <td colspan="4"><input type="text" size='45' id='structure' name='structure' value="{$infoAdministrative['structure']}" /></td>
                    </tr>
                    <tr>    
                        <td align="right"><label rel=tooltip title="Nom du service chargé de la réforme, ex: DSI">Service:</label> </td>
                        <td colspan="4"><input type="text" size='45' id='service' name='service' value="{$infoAdministrative['service']}" /></td>
                    </tr>
                    <tr>
                        <td align="right"><label rel=tooltip title="mail vers lequel sera envoyé le bon de réforme (généralement une liste)">Mail:</label> </td>
                        <td colspan="4"><input type="text" size='45' id='mail_reforme' name='mail' value="{$infoAdministrative['mail']}" /></td>
                        <td width="30px"><button rel=tooltip title="tester le mail" id="btn-modifStructure" onclick="testMail()">
                            <img alt='' title="Tester" src='{$httpPath}plugins/reforme/images/test.png'></button>
                        </td>
                    </tr>
                    <tr>
                        <td align="right"><label rel=tooltip title="statut a mettre lors de la réforme">Statut:</label> </td>
                        <td>{html_options id=statut name=statut options=$infoStatut selected=$infoAdministrative['statut']}</td>
                        <td align="right"><label rel=tooltip title="supprimer la machine de l'inventaire après la réforme">Suppression:</label> </td>
                        <td align="center" colspan="2">
                            {html_radios id=supp name=supp values=$supp_ids output=$supp_names
                            selected=$infoAdministrative['supp']  separator='  '}
                        </td>
                        <td></td>
                    </tr>                     
                    <tr>
                        <td colspan="5" align="center"><button rel=tooltip title="valider toutes les informations" id="btn-validerInfo" onclick="change()">Valider</button></td>
                    </tr>
                </table>
            </td>
            <td>
                <table>
                    <tr>
                        <td align="center">
                            <label rel=tooltip title="Logo de la structure ou de l'entreprise. La taille de l'image ne doit pas dépasser 165x180, le format de l'image doit être en PNG">
                                <IMG id='logoView' src="{$httpPath|cat:"plugins/reforme/images/logo.png"}" ALT="Relancer le formulaire de configuration pour recharger l'image, puis faites F5">
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <FORM METHOD=POST name="form_config" ACTION={$targetForm}  enctype="multipart/form-data">
                            <input type="file" id="logo" name="logo" accept="image/*" onchange="this.form.submit()"/>
                            {$endform} 
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
        
<div id="configAd">
    <table class='tab_cadre_fixe'>
        <tr>
            <th colspan="4">Configuration des Actives Directory</th>
        </tr>
        <tr>
            <td width="30px" align="center">
                <label rel=tooltip title="Enregistrer un nouvel Active Directory">
                <button id="btn-ajoutAD" onclick="ajoutAD()"><img alt='' title="Ajouter AD" src='{$httpPath}pics/menu_add.png'></button>
                </label>
            </td>
        </tr>
    </table>
    <div id="divAD">
    <table class='tab_cadre_AD tab_cadre'>
        <tr>
            {foreach from=$infoAD item=AD}
                <td align="center" style="width: 20px">
                    <table >
                        {foreach from=$AD key=k item=valueAD}
                            <tr>
                                {if $k=="id"}
                                    {assign var=val value=$valueAD}
                                    <td class="tab_td_AD1">
                                        <label rel=tooltip title="ID de l'Active Directory enregistré, <br> non modifiable">
                                            Active Directory n°
                                        </label>
                                    </td>
                                    <td class="tab_td_AD2" colspan="2" align="center">{$valueAD}</td>
                                {else}
                                    <td class="tab_td_AD1">
                                        {if $k=="serveur"}
                                            <label rel=tooltip title="Nom (DNS) ou adresse IP du serveur">{$k}</label>
                                        {/if}
                                        {if $k=="dc"}
                                            <label rel=tooltip title="DC du domaine, <br> ex: DC=UGR3,DC=lan">{$k}</label>
                                        {/if}
                                        {if $k=="suffix"}
                                            <label rel=tooltip title="Suffix du domaine, ex: @ugr3.lan">{$k}</label>
                                        {/if}
                                        {if $k=="login"}
                                            <label rel=tooltip title="Login d'un administrateur ou opérateur de compte du domaine ">{$k}</label>
                                        {/if}
                                        {if $k=="passwd"}
                                            <label rel=tooltip title="Mot de passe de l'administrateur ou opérateur de compte <br> du domaine ">{$k}</label>
                                        {/if}
                                    </td>
                                    <td>
                                        {if $k=="passwd"}
                                            <input type="password" size='20' id='{$k|cat:$val}' value="{$valueAD}" />
                                        {else}
                                            <input type="text" size='20' id='{$k|cat:$val}' value="{$valueAD}" />
                                        {/if}
                                    </td>
                                    <td width="30px"><button id="btn-modifAD" onclick="changeAD('{$k|cat:$val}')"><img alt='' title="Modifier" src='{$httpPath}pics/actualiser.png'></button></td>
                                {/if}
                                
                            </tr>
                        {/foreach}
                        <tr>
                            <td colspan="3" align="center">
                                <button rel=tooltip title="tester la connexion" id="btn-testerAD" onclick="testerAd('{$val}')">tester</button>
                                <button rel=tooltip title="valider toutes les informations" id="btn-validerAD" onclick="changeAD('valider:{$val}')">Valider</button>
                            </td>
                        </tr>
                    </table>
                </td>
            {/foreach}
        </tr>
    </table></div>
</div>
                            
<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>  

<script type="text/javascript">  
    {literal}  
    /*
     * Fonction qui envoie au serveur via ajax le champ à modifier.
     */
    function change()
        {
        var structure = document.getElementById('structure').value;
        var service = document.getElementById('service').value;
        var mail = document.getElementById('mail_reforme').value;
        var statut = document.getElementById('statut').options[
            document.getElementById('statut').selectedIndex];
        if(typeof statut === "undefined")
            {statut = 0;}
        else {statut = statut.value;}

        var supp = $('input[name=supp]:checked').val();
        var DATA = "action=modifierADM&identification=valider&structure="+structure
            +"&service="+service+"&mail="+mail+"&statut="+statut+"&supp="+supp;

        if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
        else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
                  
        xmlhttp.onreadystatechange=function()
          {
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
              {alert(xmlhttp.responseText);}
          }
        xmlhttp.open("POST","{/literal}{$targetConfigAjax}{literal}",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(DATA);
        }
        
    function testerAd(ad)
        {
            var serveur = document.getElementById('serveur'+ad).value;
            var dc = document.getElementById('dc'+ad).value;
            var suffix = document.getElementById('suffix'+ad).value;
            var login = document.getElementById('login'+ad).value;
            var passwd = document.getElementById('passwd'+ad).value;

            var DATA = "action=testerAD&serveur="+serveur+"&dc="+dc+
                    "&suffix="+suffix+"&login="+login+"&passwd="+passwd;
            if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
            else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}

            xmlhttp.onreadystatechange=function()
              {
              if (xmlhttp.readyState==4 && xmlhttp.status==200)
                  {alert(xmlhttp.responseText);}
              }
            xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/reforme/ajax/config.ajax.php"}{literal}",true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send(DATA);
        }
        
    /*
     * Fonction qui envoie au serveur via ajax le champ à modifier.
     */
    function testMail()
        {
        var mail = document.getElementById('mail_reforme').value;
        var DATA = "action=testerMail&mail="+mail;

        if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
        else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
                  
        xmlhttp.onreadystatechange=function()
          {
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
              {alert(xmlhttp.responseText);}
          }
        xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/reforme/ajax/config.ajax.php"}{literal}",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(DATA);
        }

    /*
     * Fonction qui envoie au serveur via ajax le champ AD à modifier.
     */
    function changeAD(objet)
        {
        var valider = objet.split(":");
        if(valider[0] === "valider")    
            {
            var serveur = document.getElementById('serveur'+valider[1]).value;
            var dc = document.getElementById('dc'+valider[1]).value;
            var suffix = document.getElementById('suffix'+valider[1]).value;
            var login = document.getElementById('login'+valider[1]).value;
            var passwd = document.getElementById('passwd'+valider[1]).value;
            var DATA = "action=modifierAD&identification="+objet+"&serveur="+serveur+
                    "&dc="+dc+"&suffix="+suffix+"&login="+login+"&passwd="+passwd;
            }
        else
            {
            var valeur = document.getElementById(objet).value; 
            var DATA = "action=modifierAD&identification="+objet+"&valeur="+valeur;
            }
        if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
        else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}          
        xmlhttp.onreadystatechange=function()
          {
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
              {alert(xmlhttp.responseText);}
          }
        xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/reforme/ajax/config.ajax.php"}{literal}",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(DATA);
        }

    function ajoutAD()
        { 
        var DATA = "action=ajoutAD";
        
        if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
        else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
                  
        xmlhttp.onreadystatechange=function()
          {
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
              {location.reload();}
          }
        xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/reforme/ajax/config.ajax.php"}{literal}",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(DATA);
        }
    /*
     * Gestion des infos bulles
     */
    $(document).ready(function() 
        {
        // Sélectionner tous les liens ayant l'attribut rel valant tooltip
        $('label[rel=tooltip]').mouseover(
            function(e) 
                {
                // Récupérer la valeur de l'attribut title et l'assigner à une variable
                var tip = $(this).attr('title');   
                // Supprimer la valeur de l'attribut title pour éviter l'infobulle native
                $(this).attr('title','');
                // Insérer notre infobulle avec son texte dans la page
                $(this).append('<div id="tooltip"><div class="tipHeader"></div><div class="tipBody">' + tip + '</div><div class="tipFooter"></div></div>');    

                // Ajuster les coordonnées de l'infobulle
                $('#tooltip').css('top', e.pageY + 10 );
                $('#tooltip').css('left', e.pageX + 20 );

                // Faire apparaitre l'infobulle avec un effet fadeIn
                $('#tooltip').fadeIn('500');
                $('#tooltip').fadeTo('10',0.8);

                }
            ).mousemove(
                function(e) 
                    {
                    // Ajuster la position de l'infobulle au déplacement de la souris
                    $('#tooltip').css('top', e.pageY + 10 );
                    $('#tooltip').css('left', e.pageX + 20 );
                    }
                ).mouseout(
                    function() 
                        {
                        // Réaffecter la valeur de l'attribut title
                        $(this).attr('title',$('.tipBody').html());

                        // Supprimer notre infobulle
                        $(this).children('div#tooltip').remove();

                        }
                    );
        });        
    {/literal}
    
    
</script>