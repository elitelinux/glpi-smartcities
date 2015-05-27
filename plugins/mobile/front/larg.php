
<?php
/*
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE
Inventaire
 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

// Entry menu case
define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 

/*
if(isset($_REQUEST['width']) && isset($_REQUEST['height'])) {
    $_SESSION['plugin_mobile']['screen_width'] = $_REQUEST['width'];
    $_SESSION['plugin_mobile']['screen_height'] = $_REQUEST['height'];
}
*/
/*
//session_start();
if(isset($_REQUEST['width']) AND isset($_REQUEST['height'])) {
    $_SESSION['screen_width'] = $_REQUEST['width'];
    $_SESSION['screen_height'] = $_REQUEST['height'];
   // header('Location: ' . $_SERVER['PHP_SELF']);
} else {
    echo '<script type="text/javascript">window.location = "' . $_SERVER['PHP_SELF'] . '?width="+screen.width+"&height="+screen.height;</script>';
}
/*
    $window_width = 'window.screen.width';
    $window_height = 'window.screen.height';
   
    echo '<div id="larg" style="display:none;"></div>';

    echo '<script type="text/javascript">';
    //Print screensize on user's screen
    echo 'document.write('.$window_width.');';
    echo 'document.write("<br />");';
    echo 'document.write('.$window_height.');';
    echo 'document.write("<br />");';
    echo "document.getElementById('larg').innerHTML = '$_SESSION['screen_width'] = '+window.screen.width ;";
    echo '</script>';
*/

//echo $_SESSION['width'];

?>

<?php
$ScreenWidth = "undefined";
if(!isset($_GET['screen_check'])){
	echo <<<JS
		<script>
		document.location="?ID=$PropertyID&screen_check=done&Width="+screen.width+"&Height="+screen.height;
		</script>
JS;
	exit;
}
if(isset($_GET['Width'])){
	$ScreenWidth = $_GET['Width'];
} else {
	$ScreenWidth = 400;
}
echo "Screen width = $ScreenWidth";
?>

<html>
<body>
<!--
<div id="larg" style="display:none;"></div>

<script type="text/javascript">

 document.write(window.screen.width);
 document.write("<br />");
 document.write(window.screen.height);
 document.write("<br />");
 document.getElementById("larg").innerHTML = "$_SESSION['screen_width'] = "+window.screen.width+";" ;
 </script>
-->
<?php  
echo $_SESSION['screen_width'];
?>


</body>
</html> 



