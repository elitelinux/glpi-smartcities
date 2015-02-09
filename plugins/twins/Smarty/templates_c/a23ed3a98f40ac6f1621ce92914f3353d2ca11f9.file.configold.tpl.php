<?php /* Smarty version Smarty-3.1.14, created on 2014-12-10 16:04:07
         compiled from "/var/www/glpiold/plugins/twins/Smarty/templates/configold.tpl" */ ?>
<?php /*%%SmartyHeaderCode:737501396548860e793c274-86998748%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a23ed3a98f40ac6f1621ce92914f3353d2ca11f9' => 
    array (
      0 => '/var/www/glpiold/plugins/twins/Smarty/templates/configold.tpl',
      1 => 1416412045,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '737501396548860e793c274-86998748',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'targetCSS' => 0,
    'httpPath' => 0,
    'infoAD' => 0,
    'AD' => 0,
    'k' => 0,
    'valueAD' => 0,
    'val' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.14',
  'unifunc' => 'content_548860e79e6ae5_47989790',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_548860e79e6ae5_47989790')) {function content_548860e79e6ae5_47989790($_smarty_tpl) {?>
<body>
    <link rel="stylesheet" type="text/css" href="<?php echo $_smarty_tpl->tpl_vars['targetCSS']->value;?>
" media="all"/>
</head>

<div id="info">
    <table class='tab_cadre_fixe'>
        <tr>
            <th colspan="4">Configuration du plugin Twins</th>
        </tr>
        <tr>
            <td align="center" colspan="4" class="blue">
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
                <button id="btn-ajoutAD" onclick="ajoutAD()"><img alt='' title="Ajouter AD" src='<?php echo $_smarty_tpl->tpl_vars['httpPath']->value;?>
pics/menu_add.png'></button>
                </label>
            </td>
        </tr>
    </table>
    <div id="divAD">
    <table class='tab_cadre_AD tab_cadre'>
        <tr>
            <?php  $_smarty_tpl->tpl_vars['AD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['AD']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['infoAD']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['AD']->key => $_smarty_tpl->tpl_vars['AD']->value){
$_smarty_tpl->tpl_vars['AD']->_loop = true;
?>
                <td align="center" style="width: 20px">
                    <table >
                        <?php  $_smarty_tpl->tpl_vars['valueAD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['valueAD']->_loop = false;
 $_smarty_tpl->tpl_vars['k'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['AD']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['valueAD']->key => $_smarty_tpl->tpl_vars['valueAD']->value){
$_smarty_tpl->tpl_vars['valueAD']->_loop = true;
 $_smarty_tpl->tpl_vars['k']->value = $_smarty_tpl->tpl_vars['valueAD']->key;
?>
                            <tr>
                                <?php if ($_smarty_tpl->tpl_vars['k']->value=="id"){?>
                                    <?php $_smarty_tpl->tpl_vars['val'] = new Smarty_variable($_smarty_tpl->tpl_vars['valueAD']->value, null, 0);?>
                                    <td class="tab_td_AD1">
                                        <label rel=tooltip title="ID de l'Active Directory enregistré, <br> non modifiable">
                                            Active Directory n°
                                        </label>
                                    </td>
                                    <td class="tab_td_AD2" colspan="2" align="center"><?php echo $_smarty_tpl->tpl_vars['valueAD']->value;?>
</td>
                                <?php }else{ ?>
                                    <td class="tab_td_AD1">
                                        <?php if ($_smarty_tpl->tpl_vars['k']->value=="serveur"){?>
                                            <label rel=tooltip title="Nom (DNS) ou adresse IP <br> du serveur"><?php echo $_smarty_tpl->tpl_vars['k']->value;?>
</label>
                                        <?php }?>
                                        <?php if ($_smarty_tpl->tpl_vars['k']->value=="dc"){?>
                                            <label rel=tooltip title="DC du domaine, <br> ex: DC=UGR3,DC=lan"><?php echo $_smarty_tpl->tpl_vars['k']->value;?>
</label>
                                        <?php }?>
                                        <?php if ($_smarty_tpl->tpl_vars['k']->value=="suffix"){?>
                                            <label rel=tooltip title="Suffix du domaine, <br> ex: @ugr3.lan"><?php echo $_smarty_tpl->tpl_vars['k']->value;?>
</label>
                                        <?php }?>
                                        <?php if ($_smarty_tpl->tpl_vars['k']->value=="login"){?>
                                            <label rel=tooltip title="Login d'un administrateur <br> ou opérateur de compte <br> du domaine "><?php echo $_smarty_tpl->tpl_vars['k']->value;?>
</label>
                                        <?php }?>
                                        <?php if ($_smarty_tpl->tpl_vars['k']->value=="passwd"){?>
                                            <label rel=tooltip title="Mot de passe de l'administrateur <br> ou opérateur de compte <br> du domaine "><?php echo $_smarty_tpl->tpl_vars['k']->value;?>
</label>
                                        <?php }?>
                                        <?php if ($_smarty_tpl->tpl_vars['k']->value=="groupe"){?>
                                            <label rel=tooltip title="Nom du groupe contenant <br> les groupes pour ordinateur <br> pouvant être peuplés "><?php echo $_smarty_tpl->tpl_vars['k']->value;?>
</label>
                                        <?php }?>
                                    </td>
                                    <td>
                                        <?php if ($_smarty_tpl->tpl_vars['k']->value=="passwd"){?>
                                            <input type="password" size='20' id='<?php echo ($_smarty_tpl->tpl_vars['k']->value).($_smarty_tpl->tpl_vars['val']->value);?>
' value="<?php echo $_smarty_tpl->tpl_vars['valueAD']->value;?>
" />
                                        <?php }else{ ?>
                                            <input type="text" size='20' id='<?php echo ($_smarty_tpl->tpl_vars['k']->value).($_smarty_tpl->tpl_vars['val']->value);?>
' value="<?php echo $_smarty_tpl->tpl_vars['valueAD']->value;?>
" />
                                        <?php }?>
                                    </td>
                                    <td width="30px"><button id="btn-modifAD" onclick="changeAD('<?php echo ($_smarty_tpl->tpl_vars['k']->value).($_smarty_tpl->tpl_vars['val']->value);?>
')"><img alt='' title="Modifier" src='<?php echo $_smarty_tpl->tpl_vars['httpPath']->value;?>
pics/actualiser.png'></button></td>
                                <?php }?>
                                
                            </tr>
                        <?php } ?>
                        <tr>
                            
                            <td colspan="3" align="center">
                                <button rel=tooltip title="tester la connexion" id="btn-testerAD" onclick="testerAd('<?php echo $_smarty_tpl->tpl_vars['val']->value;?>
')">tester</button>
                                <button rel=tooltip title="valider toutes les informations" id="btn-validerAD" onclick="changeAD('valider:<?php echo $_smarty_tpl->tpl_vars['val']->value;?>
')">Valider</button>
                            </td>
                        </tr>
                    </table>
                </td>
            <?php } ?>
        </tr>
    </table></div>
</div>
                            
<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>  

<script type="text/javascript">  
         
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

        var DATA = "version=old&action=testerAD&serveur="+serveur+"&dc="+dc+
                "&suffix="+suffix+"&login="+login+"&passwd="+passwd;
        if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
        else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}

        xmlhttp.onreadystatechange=function(){
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
              {alert(xmlhttp.responseText);}
        }
        xmlhttp.open("POST","<?php echo ($_smarty_tpl->tpl_vars['httpPath']->value).("plugins/twins/ajax/config.ajax.php");?>
",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(DATA);
    } 
        
    /*
     * Fonction qui envoie au serveur via ajax le champ AD à modifier.
     */
    function changeAD(objet)
    {
        var valider = objet.split(":");
        if(valider[0] === "valider"){
            var serveur = document.getElementById('serveur'+valider[1]).value;
            var dc = document.getElementById('dc'+valider[1]).value;
            var suffix = document.getElementById('suffix'+valider[1]).value;
            var login = document.getElementById('login'+valider[1]).value;
            var passwd = document.getElementById('passwd'+valider[1]).value;
            var groupe = document.getElementById('groupe'+valider[1]).value;
            var DATA = "version=old&action=modifierAD&identification="+objet+"&serveur="+serveur+
                    "&dc="+dc+"&suffix="+suffix+"&login="+login+"&passwd="+passwd+"&groupe="+groupe;
        }
        else{
            var valeur = document.getElementById(objet).value; 
            var DATA = "action=modifierAD&identification="+objet+"&valeur="+valeur;
        }
        if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
        else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}          
        xmlhttp.onreadystatechange=function(){
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
              {alert(xmlhttp.responseText);}
        }
        xmlhttp.open("POST","<?php echo ($_smarty_tpl->tpl_vars['httpPath']->value).("plugins/twins/ajax/config.ajax.php");?>
",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(DATA);
    }

    function ajoutAD()
    { 
        var DATA = "version=old&action=ajoutAD";
        
        if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
        else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
                  
        xmlhttp.onreadystatechange=function(){
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
              {location.reload();}
        }
        xmlhttp.open("POST","<?php echo ($_smarty_tpl->tpl_vars['httpPath']->value).("plugins/twins/ajax/config.ajax.php");?>
",true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(DATA);
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
                $(this).append('<div id="tooltip"><div class="tipHeaderTwins"></div><div class="tipBodyTwins">' + tip + '</div><div class="tipFooterTwins"></div></div>');    

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
    
</script><?php }} ?>