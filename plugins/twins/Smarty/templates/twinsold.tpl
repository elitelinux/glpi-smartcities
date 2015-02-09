{* 
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//
*}
<body>{literal}
    <link rel="stylesheet" type="text/css" href="{/literal}{$targetCSS}{literal}" media="all"/>
</head>{/literal}

<table class='tab_cadre_fixe'>
    <tr>
        <td align="center">             
            <button rel=tooltip title="Cloner" id="btn-cloner" onclick="cloner('on')">
            <img alt='' title="Cloner" src='{$httpPath}plugins/twins/pics/clone.png'>        
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <button id="btn-log" onclick="log()"><img alt='' title="Historique" src='{$httpPath}pics/reservation-3.png'>Historique</button></TD>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <button id="btn-log" onclick="impression()"><img alt='' title="Imprimer" src='{$httpPath}plugins/twins/pics/print.png'>Imprimer</button></TD>
        </td>
    </tr>
</table>                        
<div id="content"></div>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script> 
<script type="text/javascript">  
    {literal}
    /**
     * Fonction ajax qui envoie l'impression de l'étiquette
     * @param {type} action
     * @returns {undefined}
     */
    function impression()
    {
        var DATA = 'version=old&action=impression&idOrdinateur={/literal}{$idOrdinateur}{literal}';  
        
        if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
        else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}

        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
                {window.open("{/literal}{$httpPath|cat:"plugins/twins/etiquette/etiquette.pdf"}{literal}");}
        }
        xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/twins/ajax/twins.ajax.php"}{literal}",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(DATA);
    }   
        
    /**
     * Fonction de gestion du clonage machine
     * @param {type} action
     * @returns {undefined}
     */
    function cloner(action)
    {
        if(action == 'on'){    
            var DATA = 'version=old&action=on&idOrdinateur={/literal}{$idOrdinateur}{literal}';        
            if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
            else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}

            xmlhttp.onreadystatechange=function(){
                if (xmlhttp.readyState==4 && xmlhttp.status==200)
                    {document.getElementById("content").innerHTML=xmlhttp.responseText;}
                }
            xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/twins/ajax/twins.ajax.php"}{literal}",true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send(DATA);   
        }
        if(action == 'validation'){
            var selectComputer = document.getElementById('computer').options[document.getElementById('computer').selectedIndex].value;
            var DATA = 'version=old&action=validation&idOrdinateur='+selectComputer;
            if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
            else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}

            xmlhttp.onreadystatechange=function(){
                if (xmlhttp.readyState==4 && xmlhttp.status==200)
                    {document.getElementById("content").innerHTML=xmlhttp.responseText;}
                }
            xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/twins/ajax/twins.ajax.php"}{literal}",true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send(DATA);
        }
        if(action == 'cloner'){
            var selectComputer = document.getElementById('idClonage').value;
            var groupe = document.getElementById('groupe').value;
            var DATA = 'version=old&action=cloner&idOrdinateur={/literal}{$idOrdinateur}{literal}&idCloner='+selectComputer; 
            DATA = DATA + '&groupe='+groupe;

            if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
            else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}

            xmlhttp.onreadystatechange=function(){
                if (xmlhttp.readyState==4 && xmlhttp.status==200)
                    {
                    alert(xmlhttp.responseText);
                    location.reload();
                    }
            }
            xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/twins/ajax/twins.ajax.php"}{literal}",true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send(DATA);
        }            
    }            
        
    /*
     * Gestion des infos bulles
     */
    $(document).ready(function(){
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
        
    /**
     * Change de groupe un ordinateur
     * @param {type} action (add/supp)
     * @param {type} groupe 
     * @param {type} id
     * @returns modification div via ajax
     */   
    function change(action,groupe,groupeModif,id)
    {
        var DATA = "version=old&action="+action+"&groupe="+groupe+"&groupeModif="+groupeModif+"&id="+id;  
        
        if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
        else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
                 
        xmlhttp.onreadystatechange=function(){
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {document.getElementById("txtHint").innerHTML=xmlhttp.responseText;}
        }
        xmlhttp.open("POST","{/literal}{$httpPath|cat:"plugins/twins/ajax/twins.ajax.php"}{literal}",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(DATA);
    }
        
    function log()
    {
        window.open ("{/literal}{$httpPath|cat:"plugins/twins/popup/twinsold.popup.php?id=$idOrdinateur"}{literal}",
        'Historique du peuplement des groupes', 
        config='height=200, width=600, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no')
    }     
    {/literal}
</script>