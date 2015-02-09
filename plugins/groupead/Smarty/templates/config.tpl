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
            <th colspan="4">Configuration du plugin GroupeAD</th>
        </tr>
        <tr>
            <td align="center" colspan="4" class="color_info">
                La configuration du module doit être renseignée avant son utilisation. <br>
                Pour chaque Active Directory disponible, vous devez entrer les informations demandées.<br>
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
                <button id="btn-ajoutAD" onclick="ajoutAD()">{$menuaddIMG}</button>
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
                                        <label rel=tooltip title="ID de l'Active Directory enregistré, non modifiable">
                                            Active Directory n°
                                        </label>
                                    </td>
                                    <td class="tab_td_AD2" colspan="2" align="center">{$valueAD}</td>
                                {else}
                                    <td class="tab_td_AD1">
                                        {if $k=="serveur"}
                                            <label rel=tooltip title="Nom (DNS) ou adresse IP <br> du serveur">{$k}</label>
                                        {/if}
                                        {if $k=="dc"}
                                            <label rel=tooltip title="DC du domaine, ex: DC=UGR3,DC=lan">{$k}</label>
                                        {/if}
                                        {if $k=="suffix"}
                                            <label rel=tooltip title="Suffix du domaine, ex: @ugr3.lan">{$k}</label>
                                        {/if}
                                        {if $k=="login"}
                                            <label rel=tooltip title="Login d'un administrateur ou opérateur de compte du domaine ">{$k}</label>
                                        {/if}
                                        {if $k=="passwd"}
                                            <label rel=tooltip title="Mot de passe de l'administrateur ou opérateur de compte du domaine ">{$k}</label>
                                        {/if}
                                        {if $k=="groupe"}
                                            <label rel=tooltip title="Nom du groupe contenant les groupes pour ordinateur pouvant être peuplés ">{$k}</label>
                                        {/if}
                                    </td>
                                    <td>
                                        {if $k=="passwd"}
                                            <input type="password" size='20' id='{$k|cat:$val}' value="{$valueAD}" />
                                        {else}
                                            <input type="text" size='20' id='{$k|cat:$val}' value="{$valueAD}" />
                                        {/if}
                                    </td>
                                    <td width="30px"><button id="btn-modifAD" onclick="changeAD('{$k|cat:$val}')">{$actualiserIMG}</button></td>
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
    /**
     * Fonction qui test la connexion à un ad
     * @param int ad
     * @returns message ajax
     */
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
            xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/groupead/ajax/config.ajax.php"}{literal}",true);
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
            var groupe = document.getElementById('groupe'+valider[1]).value;
            var DATA = "action=modifierAD&identification="+objet+"&serveur="+serveur+
                    "&dc="+dc+"&suffix="+suffix+"&login="+login+"&passwd="+passwd+"&groupe="+groupe;
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
        xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/groupead/ajax/config.ajax.php"}{literal}",true);
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
        xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/groupead/ajax/config.ajax.php"}{literal}",true);
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