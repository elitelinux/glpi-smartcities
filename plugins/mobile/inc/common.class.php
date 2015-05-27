<?php
/*
 * @version $Id: HEADER 10411 2010-02-09 07:58:26Z moyo $
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

class PluginMobileCommon extends CommonDBTM {


   function __construct () {
      $this->checkMobileLogin();
      //$_SESSION['glpilist_limit'] = 5;
   }

   function displayCommonHtmlHeader(){
      $this->includeCommonHtmlHeader('mobile');
      echo "<body>";
   }

   function getMobileExtranetRoot() {
      return $this->_mobile_extranet_root;
   }

   function checkMobileLogin() {
      //check Profile
      
      if (
         isset($_SESSION['glpi_plugin_mobile_profile'])
         && $_SESSION['glpi_plugin_mobile_profile']['mobile_user'] == ''
      )  Html::redirect($CFG_GLPI["root_doc"]."/plugins/front/central.php");

      //check glpi login && redirect to plugin mobile
      
      if (!isset ($_SESSION["glpiactiveprofile"])
      || $_SESSION["glpiactiveprofile"]["interface"] != "central") {
         
         // Gestion timeout session
                 
         if (!Session::getLoginUserID()) {

            if (strpos($_SERVER['PHP_SELF'], 'index.php') === false
            && strpos($_SERVER['PHP_SELF'], 'login.php') === false
            && strpos($_SERVER['PHP_SELF'], 'logout.php') === false
            && strpos($_SERVER['PHP_SELF'], 'recoverpassword.form.php') === false
            ) {
               //Html::redirect($CFG_GLPI["root_doc"]."/plugins/mobile/index.php");
               Html::redirect("/glpi/plugins/mobile/index.php");
               exit ();
            }
         }
      }
   }
   
   

function getUserName($ID, $link=0) {
   global $DB, $CFG_GLPI;

   $user = "";
   if ($link == 2) {
      $user = array("name"    => "",
                    "link"    => "",
                    "comment" => "");
   }

   if ($ID) {
      $query  = "SELECT *
                 FROM `glpi_users`
                 WHERE `id` = '$ID'";
      $result = $DB->query($query);

      if ($link == 2) {
         $user = array("name"    => "",
                       "comment" => "",
                       "link"    => "");
      }

      if ($DB->numrows($result) == 1) {
         $data     = $DB->fetch_assoc($result);
         $username = formatUserName($data["id"], $data["name"], $data["realname"],
                                    $data["firstname"], $link);

         if ($link == 2) {
            $user["name"]    = $username;
            $user["link"]    = $CFG_GLPI["root_doc"]."/front/user.form.php?id=".$ID;
            $user['comment'] = '';

            $comments        = array();
            $comments[]      = array('name'  => __('Name'),
                                     'value' => $username);
            $comments[]      = array('name'  => __('Login'),
                                     'value' => $data["name"]);


            $email           = UserEmail::getDefaultForUser($ID);
            if (!empty($email)) {
               $comments[] = array('name'  => __('Email'),
                                   'value' => $email);
            }

            if (!empty($data["phone"])) {
               $comments[] = array('name'  => __('Phone'),
                                   'value' => $data["phone"]);
            }

            if (!empty($data["mobile"])) {
               $comments[] = array('name'  => __('Mobile phone'),
                                   'value' => $data["mobile"]);
            }

            if ($data["locations_id"] > 0) {
               $comments[] = array('name'  => __('Location'),
                                   'value' => Dropdown::getDropdownName("glpi_locations",
                                                                        $data["locations_id"]));
            }

            if ($data["usertitles_id"] > 0) {
               $comments[] = array('name'  => _x('person','Title'),
                                   'value' => Dropdown::getDropdownName("glpi_usertitles",
                                                                        $data["usertitles_id"]));
            }

            if ($data["usercategories_id"] > 0) {
               $comments[] = array('name'  => __('Category'),
                                   'value' => Dropdown::getDropdownName("glpi_usercategories",
                                                                        $data["usercategories_id"]));
            }
            if (count($comments)) {
               foreach ($comments as $data) {
               // Do not use SPAN here
               $user['comment'] .= sprintf(__('%1$s: %2$s')."<br>",
                                   "<strong>".$data['name']."</strong>", $data['value']);
               }
            }
         } else {
            $user = $username;
         }
      }
   }
   return $user;
}   
   
      function getAuthorName($link=0) {
      return getUserName($this->fields["users_id"], $link);
   }
   
   

   function includeCommonHtmlHeader($title='') {
      global $CFG_GLPI,$PLUGIN_HOOKS,$LANG;

      // Send UTF8 Headers
      
      header("Content-Type: text/html; charset=UTF-8");
    
      // Send extra expires header
      
	   Html::header_nocache();

      // Start the page
      
      echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"
         \"http://www.w3.org/TR/html4/loose.dtd\">";
      echo "\n<html><head><title>GLPI - ".$title."</title>";
      echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8 \" >";
      // Send extra expires header
      
      echo "<meta http-equiv=\"Expires\" content=\"Fri, Jun 12 1981 08:20:00 GMT\" >\n";
      echo "<meta http-equiv=\"Pragma\" content=\"no-cache\">\n";
      echo "<meta http-equiv=\"Cache-Control\" content=\"no-cache\">\n";

      // FAV & APPLE DEVICE ICON
      
      echo "<link rel='apple-touch-icon' type='image/png' href='".$CFG_GLPI["root_doc"]."/plugins/mobile/pics/apple-touch-icon.png' />";
      echo "<link rel='icon' type='image/png' href='".$CFG_GLPI["root_doc"]."/plugins/mobile/pics/favicon.png' />";

      // CSS link JQUERY MOBILE
      
      echo "<link rel='stylesheet'  href='".
         $CFG_GLPI["root_doc"]."/plugins/mobile/lib/jquery.mobile-1.0a4.1/jquery.mobile-1.0a4.1.css' type='text/css' media='screen' >\n";


      // CSS link MOBILE GLPI PLUGIN
      
      echo "<link rel='stylesheet'  href='".
         $CFG_GLPI["root_doc"]."/plugins/mobile/mobile.css' type='text/css' media='screen' >\n";

      // CSS link DATEBOX PLUGIN
      
      echo "<link rel='stylesheet' href='".
         $CFG_GLPI["root_doc"]."/plugins/mobile/lib/datebox/jquery.mobile.datebox.css' />\n";


      // LOAD JS JQUERY
      
      echo "<script type=\"text/javascript\" src='".
         $CFG_GLPI["root_doc"]."/plugins/mobile/lib/jquery-1.5.2.min.js'></script>\n";

//busca Stevenes
		//echo "<script type=\"text/javascript\" src='".$CFG_GLPI["root_doc"]."/plugins/mobile/lib/busca.js'></script>";

      // EXTEND JQUERY MOBILE OPTIONS
      
      echo "<script type='text/javascript'>";
      echo "$(document).bind('mobileinit', function(){\n";

         // DISABLE JQUERY MOBILE AJAX SUBMIT
         
         echo "$.extend(  $.mobile, { ajaxFormsEnabled: false });\n";

         // change back button text
         
         echo "$.mobile.page.prototype.options.backBtnText = '".$LANG['buttons'][13]."';\n";

         //change loading message
         
         echo "$.extend(  $.mobile, { loadingMessage: '".$LANG['common'][80]."' });\n";

         if (navigatorDetect() == 'Android' && getOsVersion() < "3.0")
            echo "$.mobile.defaultTransition = 'none';\n";

         //echo "alert($.mobile.nonHistorySelectors);";
         // disable history on data-rel navigation
         //echo "$.mobile.nonHistorySelectors = 'dialog][data-rel=navigation';\n";

         //reset type=date inputs to text
         //echo "$.mobile.page.prototype.options.degradeInputs.date = true;\n";

         echo "$.mobile.selectmenu.prototype.options.nativeMenu = true;";

         if (nativeSelect()) echo "$.mobile.page.prototype.options.keepNative = 'select'";

      echo "});\n";
      echo "</script>\n";

      // LOAD JS JQUERY MOBILE
      
      echo "<script type=\"text/javascript\" src='".
            $CFG_GLPI["root_doc"]."/plugins/mobile/lib/jquery.mobile-1.0a4.1/jquery.mobile-1.0a4.1.min.js'></script>\n";
      /*echo "<script type=\"text/javascript\" src='".
            $CFG_GLPI["root_doc"]."/plugins/mobile/lib/jquery.mobile-1.0a4.1/jquery.mobile-1.0a4.1.js'></script>\n";*/

      // LOAD DATEBOX PLUGIN (JS)
      
      echo "<script type=\"text/javascript\" src='".
            $CFG_GLPI["root_doc"]."/plugins/mobile/lib/datebox/jquery.mobile.datebox.js'></script>\n";


      //DOM READY
      
      echo "<script type='text/javascript'>";
      echo "$(document).ready(function() {

         //post screen resolution
         $.post('".$CFG_GLPI["root_doc"]."/plugins/mobile/lib/resolution.php', { width: $(document).width(), height: $(document).height() });

         //INIT DATEBOX PLUGIN
         
         ".getDateBoxOptions()."

         $('input[type=date], input[data-role=date]', this ).each(function() {
            $(this).datebox(opts);
         });

         $('.ui-page').live('pagecreate', function() {
            $('input[type=date], input[data-role=date]', this ).each(function() {
               $(this).datebox(opts);
            });
         });

      });\n";
      echo "</script>\n";

      // End of Head
      echo "</head>\n";

  }

  function displayHeader($title="&nbsp;", $back = '', $external = false, $title2 = '', $id_attr='')
  {
      global $CFG_GLPI, $LANG;
      /*if ($external)  $external = "rel='external'";
      else */$external = "";

      if ($back != '') $back = $CFG_GLPI["root_doc"]."/plugins/mobile/front/".$back;

      if (strlen($title2) > 0) $title2 = " " . $title2;

      $this->displayCommonHtmlHeader($title);

      echo "<div data-role='page' data-theme='c' id='$id_attr' ";
      if (nativeSelect()) echo "class='native-select'";
      echo ">";

      if (!$this->checkDisplayHeaderBar()) {
      echo "<div data-role='header' data-theme='c'>";
      echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/mobile/front/central.php' rel='external'>";
      echo "<img src='"
         .$CFG_GLPI["root_doc"]
         ."/plugins/mobile/pics/logo.png' alt='Logo' width='62' height='30' />";
      echo "</a>";
      echo "<h1>".$title.$title2."</h1>";

      $dataTransition = "data-transition='slide'";
      if (navigatorDetect() == 'Android' && getOsVersion() < "3.0") $dataTransition = "";
      

      if ($back != '')
         echo "<a href='".$back."' ".$external." data-icon='arrow-l' data-back='true' $dataTransition class='ui-btn-right' style='margin-top:6px;'>"
            .$LANG['buttons'][13]."</a>";
      elseif (strpos($_SERVER['PHP_SELF'], 'central.php') === false)
         echo "<a href='#' onclick='history.back();' data-icon='arrow-l' class='ui-btn-right' $dataTransition data-back='true' style='margin-top:6px;'>"
            .$LANG['buttons'][13]."</a>";

      if (strpos($_SERVER['PHP_SELF'], 'central.php') !== false)
         echo "<a href='".$CFG_GLPI["root_doc"]. "/plugins/mobile/front/option.php' data-icon=\"gear\" class='ui-btn-right' data-rel='dialog' style='margin-top:6px;'>"
            .$LANG['plugin_mobile']['navigation']['options']."</a>";

      echo "</div>";

      }
      
      
   }

   function displayPopHeader($title, $id='popup') {
      global $LANG;

      echo "<div data-role='page'>";
      echo "<div data-role='header' data-theme='c'>";
         echo "<h1>$title : </h1>";
      echo "</div>";
      echo "<div data-role='content' data-theme='c' id='$id'>";
   }

   function displayPopFooter() {
      echo "</div>";
      echo "</div>";
   }

   function checkDisplayHeaderBar() {
      if (
         strpos($_SERVER['PHP_SELF'], 'index.php') !== false ||
         strpos($_SERVER['PHP_SELF'], 'login.php') !== false
      ) return true;
      else return false;
   }

   function displayLoginBox($error = '', $REDIRECT = "") {
      global $CFG_GLPI, $LANG;


      echo "<div data-role='header' data-theme='c'>";
      echo "<a href='#'><img src='".$CFG_GLPI["root_doc"]."/plugins/mobile/pics/logo.png' alt='Logo' /></a>";
         echo "<h1>".$LANG['login'][10]."</h1>";
      echo "</div>";

      echo "<div data-role='content' class='login-box'>";
      if (trim($error) != "") {
      echo '<div class="center b">' . $error . '<br><br>';
      }

      echo "<form action='login.php' method='post'>";
      echo "<fieldset>";

      echo "<div data-role='fieldcontain'>";
      echo "<label for='login_name'>".$LANG['login'][6].":</label>";
      echo "<input type='text' name='login_name' id='login_name' value=''  />";
      echo "</div>";

      echo "<div data-role='fieldcontain'>";
      echo "<label for='login_password'>".$LANG['login'][7].":</label>";
      echo "<input type='password' name='login_password' id='login_password' value='' />";
      echo "</div>";

      echo "<button type='submit' data-theme='a'>".$LANG['plugin_mobile']["login"]."</button>";

      echo "</fieldset>";
      //echo "</form>";
      Html::closeForm();

      echo "</div>";
   }

   function displayFooter() {
      echo "</div>";
      echo "</body>";
      echo "</html>";
   }


   public function showCentralFooter() {
      global $LANG;

      //display footer central bar
      
      echo "<div data-role='footer' data-position='fixed' data-theme='d'>";
         echo "<div data-role='navbar'>";
         echo "<ul>";

          echo "<li><a href='#' data-icon='search'>".$LANG['buttons'][0]."</a></li>";

          echo "<li><a href='#' data-icon='custom' id='icon-preference'>".$LANG['Menu'][11]."</a></li>";

         echo "</ul>";
         echo "</div>";
      echo "</div>";
   }
   
   

   

};

?>
