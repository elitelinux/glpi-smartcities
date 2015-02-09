<?php

/*
 * @version $Id: index.php 22657 2014-02-12 16:17:54Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

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
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
* @brief
*/

// Modified by Stevenes Donato
// stevenesdonato@gmail.com

// Check PHP version not to have trouble
if (version_compare(PHP_VERSION, "5.3.0") < 0) {
   die("PHP >= 5.3.0 required");
}

define('DO_NOT_CHECK_HTTP_REFERER', 1);
// If config_db doesn't exist -> start installation
define('GLPI_ROOT', dirname(__FILE__));
include (GLPI_ROOT . "/config/based_config.php");

if (!file_exists(GLPI_CONFIG_DIR . "/config_db.php")) {
   include_once (GLPI_ROOT . "/inc/autoload.function.php");
   Html::redirect("install/install.php");
   die();

} else {
   $TRY_OLD_CONFIG_FIRST = true;

   include (GLPI_ROOT . "/inc/includes.php");
   $_SESSION["glpicookietest"] = 'testcookie';

   // For compatibility reason
   if (isset($_GET["noCAS"])) {
      $_GET["noAUTO"] = $_GET["noCAS"];
   }

   Auth::checkAlternateAuthSystems(true, isset($_GET["redirect"])?$_GET["redirect"]:"");
}

?>
<!DOCTYPE html>
<html lang="en"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo __('GLPI - Authentication'); ?></title>

<!-- Bootstrap -->
<link href="css/bootstrap.css" rel="stylesheet">
<link href="css/css.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<link href="css/style-responsive.css" rel="stylesheet">
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

<script type='text/javascript'>      
window.onload = function() {
  			var input = document.getElementById("login_name").focus();
	}      
</script>

</head>
<body>

<div id="body2"></div>
<div class="login-container">
  <div class="middle-login">
  	<div id='text-login'>
  		<?php echo nl2br(Toolbox::unclean_html_cross_side_scripting_deep($CFG_GLPI['text_login'])); ?>  
  	</div>
    <div class="block-web row-fluid">        
      <div class="head">
        <h3 class="text-center"><img class="logo-img" src="pics/logo.png" alt="" style="left:50%;"></h3>
      </div>

		<div id="logo_big" style="border-right: 1px solid #ccc; width:220px;" >
			<img src="pics/logo_big.png" alt="GLPI" class="logo2" />
		</div>      
      
      <div id="auth" style="background:#fff;">
      
        <form class="form-horizontal" style="margin-bottom: 0px !important;" action='front/login.php' method='post'>
			<?php
			   // Other CAS
			   if (isset($_GET["noAUTO"])) {
			      echo "<input type='hidden' name='noAUTO' value='1'/>";
			   }
			
			   // redirect to ticket
			   if (isset($_GET["redirect"])) {
			      Toolbox::manageRedirect($_GET["redirect"]);
			      echo '<input type="hidden" name="redirect" value="'.$_GET['redirect'].'">';
			   }
			?>        
                
          <div class="content">
            <h4 class="title"><?php echo __('Authentication'); ?></h4>
            <div class="form-group">
              <div class="col-sm-12">
                <div class="input-group"> <span class="input-group-addon"><i class="fa fa-user"></i></span>
                  <input class="form-control" name="login_name" id="login_name" required="required" placeholder="<?php echo __('Login') ?>" type="text">
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-12">
                <div class="input-group"> <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                  <input class="form-control" name="login_password" id="login_password" required="required" placeholder="<?php echo __('Password') ?>" type="password">
                </div>
              </div>
            </div>
          </div>
          <div class="foot">	
            <a href="#"><button type="submit" name="submit" data-dismiss="modal" style="right: 40%;" class="btn btn-success"><?php echo _sx('button','Post'); ?></button></a>
          </div>
			<?php
			    if ($CFG_GLPI["use_mailing"]
			&& countElementsInTable('glpi_notifications',
			                               "`itemtype`='User' AND `event`='passwordforget' AND `is_active`=1")) {
			      echo '<div id="forget"><a href="front/lostpassword.php?lostpassword=1">'.
			             __('Forgotten password?').'</a></div>';
			   }
			   Html::closeForm();          
			          
			//        </form>
			?>        
      </div>
    </div>
    <div class="text-center out-links"><a href="#">
    <?php
          // Display FAQ is enable
   if ($CFG_GLPI["use_public_faq"]) {
      echo '<div id="box-faq">'.
            '<a style="color:#fff;" href="front/helpdesk.faq.php">[ '.__('Access to the Frequently Asked Questions').' ]';
      echo '</a></div>';
   }
   ?>
    </a></div>
	<?php
	  echo "</div>"; // end contenu login
	
	   if (GLPI_DEMO_MODE) {
	      echo "<div class='center'>";
	      Event::getCountLogin();
	      echo "</div>";
	   }
	
	   echo "<div id='footer-login' class='out-links'>";
	   echo "<a href='http://glpi-project.org/' target='_blank' title='Powered By Indepnet'>";
	   echo 'GLPI version '.(isset($CFG_GLPI["version"])?$CFG_GLPI["version"]:"").
	        ' Copyright (C) 2003-'.date("Y").' INDEPNET Development Team.';
	   echo "</a></div>";    
	?>    
  </div>
</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins)  
<script src="css/js/jquery-21.js"></script> -->
<!-- Include all compiled plugins (below), or include individual files as needed --> 
<script src="css/js/bootstrap.js"></script> 
<!--<script src="css/js/accordion.js"></script>--> 
<script src="css/js/common-script.js"></script> 
<script src="lib/jquery/js/jquery-1.10.2.min.js"></script>

</body></html>
