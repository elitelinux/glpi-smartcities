<?php
/*
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

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
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------


if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

/**
 * Get form URL for itemtype
 *
 * @param $itemtype string: item type
 * @param $full path or relative one
 *
 * return string itemtype Form URL
 */
function getItemTypeFormURLMobile($itemtype, $full=true) {
   $item = strtolower($itemtype);
   return "item.php?itemtype=$itemtype&amp;menu=".$_GET['menu']."&amp;ssmenu=".$_GET['ssmenu'];

}

function formatUserNameMobile($ID,$login,$realname,$firstname,$link=0,$cut=0) {
   global $CFG_GLPI;

   $before="";
   $after="";
   $viewID="";
   if (strlen($realname)>0) {
      $temp=$realname;
      if (strlen($firstname)>0) {
         if ($CFG_GLPI["names_format"]==FIRSTNAME_BEFORE) {
            $temp=$firstname." ".$temp;
         } else {
            $temp.=" ".$firstname;
         }
      }
      if($cut>0 && utf8_strlen($temp)>$cut) {
      $temp=utf8_substr($temp,0,$cut);
      $temp.=" ...";
      }
   } else {
      $temp=$login;
   }
   if ($ID>0 && (strlen($temp)==0 || $_SESSION["glpiis_ids_visible"])) {
      $viewID="&nbsp;($ID)";
   }

   if ($link==1&&$ID>0) {
      /*$before="<a title=\"".$temp."\"
                  href=\"".$CFG_GLPI["root_doc"]."/front/user.form.php?id=".$ID."\">";*/
      $before="<a title=\"".$temp."\"
                  href=\"item.php?itemtype=user&menu=".$_GET['menu']."&ssmenu=".$_GET['ssmenu']."&id=".$ID."\" data-back='false'>";
      $after="</a>";
   }

   //$username=$before.$temp.$viewID.$after;
   $username=$temp.$viewID;
   return $username;
}

function getUserNameMobile($ID,$link=0) {
   global $DB,$CFG_GLPI,$LANG;

   $user="";
   if ($link==1) {
      $user=array("name"=>"",
                  "link"=>"",
                  "comment"=>"");
   }
   if ($ID) {
      $query="SELECT *
              FROM `glpi_users`
              WHERE `id`='$ID'";
      $result=$DB->query($query);

      if ($link==2) {
         $user=array("name"=>"",
                     "comment"=>"",
                     "link"=>"");
      }
      if ($DB->numrows($result)==1) {
         $data=$DB->fetch_assoc($result);
         $username=formatUserNameMobile($data["id"],$data["name"],$data["realname"],$data["firstname"],
                                  $link);
         if ($link==2) {
            $user["name"]=$username;
            //$user["link"]=$CFG_GLPI["root_doc"]."/front/user.form.php?id=".$ID;
            $user["link"]="item.php?itemtype=user&menu=".$_GET['menu']."&ssmenu=".$_GET['ssmenu']."&id=".$ID;
            $user["comment"]=$LANG['common'][16]."&nbsp;: ".$username."<br>";
            $user["comment"].=$LANG['setup'][18]."&nbsp;: ".$data["name"]."<br>";
            if (!empty($data["email"])) {
               $user["comment"].=$LANG['setup'][14]."&nbsp;: ".$data["email"]."<br>";
            }
            if (!empty($data["phone"])) {
               $user["comment"].=$LANG['help'][35]."&nbsp;: ".$data["phone"]."<br>";
            }
            if (!empty($data["mobile"])) {
               $user["comment"].=$LANG['common'][42]."&nbsp;: ".$data["mobile"]."<br>";
            }
            if ($data["locations_id"]>0) {
               $user["comment"].=$LANG['common'][15]."&nbsp;: ".
                                 Dropdown::getDropdownName("glpi_locations",$data["locations_id"])."<br>";
            }
            if ($data["usertitles_id"]>0) {
               $user["comment"].=$LANG['users'][1]."&nbsp;: ".
                                 Dropdown::getDropdownName("glpi_usertitles",$data["usertitles_id"])."<br>";
            }
            if ($data["usercategories_id"]>0) {
               $user["comment"].=$LANG['users'][2]."&nbsp;: ".
                                 Dropdown::getDropdownName("glpi_usercategories",$data["usercategories_id"]).
                                 "<br>";
            }
         } else {
            $user=$username;
         }
      }
   }
   return $user;
}


function checkParams() {
   if (!isset($_SESSION['plugin_mobile']) && isset($_SESSION['glpiID'])) {

      $option = new PluginMobileOption;
      if (!$option->getOptions()) {

         $navigator = navigatorDetect();

         switch ($navigator) {
            case "iPad":
               $_SESSION['plugin_mobile']['cols_limit'] = 6;
               $_SESSION['plugin_mobile']['rows_limit'] = 17;
               break;
            case "iPhone":
               $_SESSION['plugin_mobile']['cols_limit'] = 3;
               $_SESSION['plugin_mobile']['rows_limit'] = 6;
               break;
            case "Android":
               $_SESSION['plugin_mobile']['cols_limit'] = 3;
               $_SESSION['plugin_mobile']['rows_limit'] = 10;
               break;
            default:
               $_SESSION['plugin_mobile']['cols_limit'] = 6;
               $_SESSION['plugin_mobile']['rows_limit'] = 17;
               break;
         }
      }
   }
}


function navigatorDetect() {
   if (isset($_SERVER['HTTP_USER_AGENT'])) {

      if (stripos($_SERVER['HTTP_USER_AGENT'], 'iPad')) return "iPad";
      elseif (stripos($_SERVER['HTTP_USER_AGENT'], 'iPhone')) return "iPhone";
      elseif (stripos($_SERVER['HTTP_USER_AGENT'], 'Android')) return "Android";
      elseif (stripos($_SERVER['HTTP_USER_AGENT'], 'Firefox')) return "Desktop";
      elseif (stripos($_SERVER['HTTP_USER_AGENT'], 'Chrome')) return "Desktop";
      else return "default";
   }
   return;
}


function getOsVersion() {
   $version = "";
   if (isset($_SERVER['HTTP_USER_AGENT'])) {
      $agent = $_SERVER['HTTP_USER_AGENT'];

      if(stripos($agent,'Android') !== false ) {
         $result = explode(' ',stristr($agent,'Android'));
         if(isset($result[1])) $version = substr($result[1], 0, -1);

      } elseif(stripos($agent,'iPhone') !== false ) {
         $result = explode('/',stristr($agent,'Version'));
         if(isset($result[1])) {
            $aversion = explode(' ',$result[1]);
            $version = $aversion[0];
         }

      } elseif( stripos($agent,'iPad') !== false ) {
         $result = explode('/',stristr($agent,'Version'));
         if(isset($aresult[1])) {
            $aversion = explode(' ',$result[1]);
            $version = $aversion[0];
         }
      }
   }

   return $version;
}

function largeScreen() {
   $navigator = navigatorDetect();
   if(in_array($navigator, array('iPad', 'Desktop'))) return true;
   elseif ($navigator == 'Android' && isAndroidTablet()) return true;
   else return false;
}

function isAndroidTablet() {
   if (isset($_SERVER['HTTP_USER_AGENT'])) {
      if(stripos($_SERVER['HTTP_USER_AGENT'],'mobile') === false) return true;
   }
   return false;
}

function isNavigatorMobile() {
   return in_array(navigatorDetect(), array(
      'iPhone',
      'iPad',
      'Android',
      'Fennec'
   ));
}

function redirectMobile()  {
  if (!isset($_SESSION['glpiactiveprofile'])
   && strpos($_SERVER['SCRIPT_FILENAME'], 'plugins/mobile') === false
   && strpos($_SERVER['SCRIPT_FILENAME'], 'login.php') === false) {
      //check if alternate auth is available
      Auth::checkAlternateAuthSystems(true, "plugin_mobile_1");

      //else redirect login page
      
      //header("location: ".GLPI_ROOT."/plugins/mobile/index.php");
            header("location: ".$CFG_GLPI["root_doc"]."plugins/mobile/index.php");
   }
}

function saveActiveProfileAndApplyRead() {
   $_SESSION['temp_glpiactiveprofile'] = $_SESSION['glpiactiveprofile'];

   foreach($_SESSION['glpiactiveprofile'] as &$val) {
      if ($val == 'w') $val = 'r';
   }
}

function restoreActiveProfile() {
   $_SESSION['glpiactiveprofile'] = $_SESSION['temp_glpiactiveprofile'];
   unset($_SESSION['temp_glpiactiveprofile']);
}

function saveCFG() {
   global $CFG_GLPI;

   $_SESSION['temp_CFG_GLPI'] = $CFG_GLPI;
}

function restoreCFG () {
   global $CFG_GLPI;

   $CFG_GLPI = $_SESSION['temp_CFG_GLPI'];
   unset ($_SESSION['temp_CFG_GLPI']);
}

function editMode() {
   if (isset($_SESSION['plugin_mobile']['edit_mode'])
      && $_SESSION['plugin_mobile']['edit_mode'] == 1) return true;
   return false;
}

function nativeSelect() {
   //if (/*isset($_SESSION['plugin_mobile']['native_select']) && */ 
   if($_SESSION['plugin_mobile']['native_select']== 1) return true;
   return false;
}

function mobileTimestampToString($sec,$display_sec=true) {
   global $LANG;
   /// TODO : rewrite to have simple code
   $sec=floor($sec);
   if ($sec<0) {
      $sec=0;
   }

   if ($sec < MINUTE_TIMESTAMP) {
      return $sec." ".$LANG['stats'][34];
   } else if ($sec < HOUR_TIMESTAMP) {
      $min = floor($sec/MINUTE_TIMESTAMP);
      $sec = $sec%MINUTE_TIMESTAMP;
      $out = $min." ".$LANG['stats'][33];
      if ($display_sec && $sec >0) {
         $out .= " ".$sec." ".$LANG['stats'][34];
      }
      return $out;
   } else if ($sec <  DAY_TIMESTAMP) {
      $heure = floor($sec/HOUR_TIMESTAMP);
      $min = floor(($sec%HOUR_TIMESTAMP)/(MINUTE_TIMESTAMP));
      $sec = $sec%MINUTE_TIMESTAMP;
      $out = $heure." ".$LANG['job'][21];
      if ($min>0) {
         $out .= " ".$min." ".$LANG['stats'][33];
      }
      if ($display_sec && $sec >0) {
         $out.=" ".$sec." ".$LANG['stats'][34];
      }
      return $out;
   } else {
      $jour = floor($sec/DAY_TIMESTAMP);
      $heure = floor(($sec%DAY_TIMESTAMP)/(HOUR_TIMESTAMP));
      $min = floor(($sec%HOUR_TIMESTAMP)/(MINUTE_TIMESTAMP));
      $sec = $sec%MINUTE_TIMESTAMP;
      $out = $jour." ".$LANG['stats'][31];
      if ($heure>0) {
         $out .= " ".$heure." ".$LANG['job'][21];
      }

      if ($min>0) {
         $out.=" ".$min." ".$LANG['stats'][33];
      }

      if ($display_sec && $sec >0) {
         $out.=" ".$sec." ".$LANG['stats'][34];
      }
      return $out;
   }
}

function getObjectAnchor($anchor)  {
   $dom = new DOMDocument();
   $dom->loadHTML($anchor);

   if ($dom) {
      $xpath = new DOMXPath($dom);
      $result = $xpath->query("//a");

      if ($result->length == 1) {
         return array(
            'href' => $result->item(0)->getAttribute('href'),
            'value' => utf8_decode($result->item(0)->textContent)
         );
      }
   }
   return false;
}


function getDateBoxOptions() {
   global $LANG;

   return "var opts = {};
   opts.fieldsOrder = ['d', 'm', 'y'];
   opts.pickPageTheme = 'a';
   opts.pickPageInputTheme = 'c';
   opts.pickPageButtonTheme = 'c';
   opts.title = '".$LANG['common'][27]."';
   opts.setDateButtonLabel = '".$LANG['buttons'][2]."';
   opts.daysOfWeek = [
      '".$LANG['calendarDay'][0]."',
      '".$LANG['calendarDay'][1]."',
      '".$LANG['calendarDay'][2]."',
      '".$LANG['calendarDay'][3]."',
      '".$LANG['calendarDay'][4]."',
      '".$LANG['calendarDay'][5]."',
      '".$LANG['calendarDay'][6]."'
   ];

   opts.daysOfWeekShort = [
      '".$LANG['calendarD'][0]."',
      '".$LANG['calendarD'][1]."',
      '".$LANG['calendarD'][2]."',
      '".$LANG['calendarD'][3]."',
      '".$LANG['calendarD'][4]."',
      '".$LANG['calendarD'][5]."',
      '".$LANG['calendarD'][6]."'
   ];

   opts.monthsOfYear = [
      '".$LANG['calendarM'][0]."',
      '".$LANG['calendarM'][1]."',
      '".$LANG['calendarM'][2]."',
      '".$LANG['calendarM'][3]."',
      '".$LANG['calendarM'][4]."',
      '".$LANG['calendarM'][5]."',
      '".$LANG['calendarM'][6]."',
      '".$LANG['calendarM'][7]."',
      '".$LANG['calendarM'][8]."',
      '".$LANG['calendarM'][9]."',
      '".$LANG['calendarM'][10]."',
      '".$LANG['calendarM'][11]."'
   ];";
}
