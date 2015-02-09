<?php /* Smarty version Smarty-3.1.14, created on 2014-12-11 14:02:08
         compiled from "/var/www/glpiold/plugins/twins/Smarty/templates/twinsold.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1893578363548862c04ced75-93162832%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ce68ca4925be3c96fadcf07ec5af6d6b51f663fa' => 
    array (
      0 => '/var/www/glpiold/plugins/twins/Smarty/templates/twinsold.tpl',
      1 => 1418302923,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1893578363548862c04ced75-93162832',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.14',
  'unifunc' => 'content_548862c0528f68_16898876',
  'variables' => 
  array (
    'targetCSS' => 0,
    'httpPath' => 0,
    'idOrdinateur' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_548862c0528f68_16898876')) {function content_548862c0528f68_16898876($_smarty_tpl) {?>
<body>
    <link rel="stylesheet" type="text/css" href="<?php echo $_smarty_tpl->tpl_vars['targetCSS']->value;?>
" media="all"/>
</head>

<table class='tab_cadre_fixe'>
    <tr>
        <td align="center">             
            <button rel=tooltip title="Cloner" id="btn-cloner" onclick="cloner('on')">
            <img alt='' title="Cloner" src='<?php echo $_smarty_tpl->tpl_vars['httpPath']->value;?>
plugins/twins/pics/clone.png'>        
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <button id="btn-log" onclick="log()"><img alt='' title="Historique" src='<?php echo $_smarty_tpl->tpl_vars['httpPath']->value;?>
pics/reservation-3.png'>Historique</button></TD>
        </td>
    </tr>
    <tr>
        <td colspan="2" align="center">
            <button id="btn-log" onclick="impression()"><img alt='' title="Imprimer" src='<?php echo $_smarty_tpl->tpl_vars['httpPath']->value;?>
plugins/twins/pics/print.png'>Imprimer</button></TD>
        </td>
    </tr>
</table>                        
<div id="content"></div>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script> 
<script type="text/javascript">  
    
    /**
     * Fonction ajax qui envoie l'impression de l'étiquette
     * @param {type} action
     * @returns {undefined}
     */
    function impression()
    {
        var DATA = 'version=old&action=impression&idOrdinateur=<?php echo $_smarty_tpl->tpl_vars['idOrdinateur']->value;?>
';  
        
        if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
        else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}

        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
                {window.open("<?php echo ($_smarty_tpl->tpl_vars['httpPath']->value).("plugins/twins/etiquette/etiquette.pdf");?>
");}
        }
        xmlhttp.open("POST","<?php echo ($_smarty_tpl->tpl_vars['httpPath']->value).("plugins/twins/ajax/twins.ajax.php");?>
",true);
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
            var DATA = 'version=old&action=on&idOrdinateur=<?php echo $_smarty_tpl->tpl_vars['idOrdinateur']->value;?>
';        
            if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
            else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}

            xmlhttp.onreadystatechange=function(){
                if (xmlhttp.readyState==4 && xmlhttp.status==200)
                    {document.getElementById("content").innerHTML=xmlhttp.responseText;}
                }
            xmlhttp.open("POST","<?php echo ($_smarty_tpl->tpl_vars['httpPath']->value).("plugins/twins/ajax/twins.ajax.php");?>
",true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send(DATA);   
        }
        if(action == 'validation'){
            var selectComputer = document.getElementById('computer').options[document.getElementById('computer').selectedIndex].value;
            var DATA = 'version=old&action=validation&idOrdinateur='+selectComputer;
            alert(selectComputer)
            //if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
            //else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}

            //xmlhttp.onreadystatechange=function(){
            //    if (xmlhttp.readyState==4 && xmlhttp.status==200)
            //        {document.getElementById("content").innerHTML=xmlhttp.responseText;}
            //    }
            //xmlhttp.open("POST","<?php echo ($_smarty_tpl->tpl_vars['httpPath']->value).("plugins/twins/ajax/twins.ajax.php");?>
",true);
            //xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            //xmlhttp.send(DATA);
        }
        if(action == 'cloner'){
            var selectComputer = document.getElementById('idClonage').value;
            var groupe = document.getElementById('groupe').value;
            var DATA = 'version=old&action=cloner&idOrdinateur=<?php echo $_smarty_tpl->tpl_vars['idOrdinateur']->value;?>
&idCloner='+selectComputer; 
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
            xmlhttp.open("POST","<?php echo ($_smarty_tpl->tpl_vars['httpPath']->value).("plugins/twins/ajax/twins.ajax.php");?>
",true);
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
        xmlhttp.open("POST","<?php echo ($_smarty_tpl->tpl_vars['httpPath']->value).("plugins/twins/ajax/twins.ajax.php");?>
",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(DATA);
    }
        
    function log()
    {
        window.open ("<?php echo ($_smarty_tpl->tpl_vars['httpPath']->value).("plugins/twins/popup/twinsold.popup.php?id=".((string)$_smarty_tpl->tpl_vars['idOrdinateur']->value));?>
",
        'Historique du peuplement des groupes', 
        config='height=200, width=600, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no')
    }     
    
</script><?php }} ?>