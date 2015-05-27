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

class PluginMobileSearch extends Search {
	
	
 /**
    * Convert an array to be add in url
    *
    * @param $name                  name of array
    * @param $array  string/array   to be added
    *
    * @return string to add
   **/
   static function getArrayUrlLink($name, $array) {

      $out = "";
      if (is_array($array) && count($array)>0) {
         foreach ($array as $key => $val) {
            $out .= "&amp;".$name."[$key]=".urlencode(stripslashes($val));
         }
      }
      return $out;
   }	
	
	
/**
    * Completion of the URL $_GET values with the $_SESSION values or define default values
    *
    * @param $itemtype        item type to manage
    * @param $usesession      Use datas save in session (true by default)
    * @param $forcebookmark   force trying to load parameters from default bookmark:
    *                         used for global search (false by default)
    *
    * @return nothing
   **/
      static function manageGetValues($itemtype, $usesession=true, $forcebookmark=false) {
   	//static function manageParams($itemtype, $params = Array, $usesession = true, $forcebookmark = false) {
   		
      global $_GET, $DB;

      $redirect = false;

      if (isset($_GET["add_search_count"]) && $_GET["add_search_count"]) {
         $_SESSION["glpisearchcount"][$itemtype]++;
         Html::redirect(str_replace("add_search_count=1&", "", $_SERVER['REQUEST_URI']));
      }

      if (isset($_GET["delete_search_count"]) && $_GET["delete_search_count"]) {
         if ($_SESSION["glpisearchcount"][$itemtype] > 1) {
            $_SESSION["glpisearchcount"][$itemtype]--;
         }
         Html::redirect(str_replace("delete_search_count=1&", "", $_SERVER['REQUEST_URI']));
      }

      if (isset($_GET["add_search_count2"]) && $_GET["add_search_count2"]) {
         $_SESSION["glpisearchcount2"][$itemtype]++;
         Html::redirect(str_replace("add_search_count2=1&", "", $_SERVER['REQUEST_URI']));
      }

      if (isset($_GET["delete_search_count2"]) && $_GET["delete_search_count2"]) {
         if ($_SESSION["glpisearchcount2"][$itemtype] >= 1) {
            $_SESSION["glpisearchcount2"][$itemtype]--;
         }
         Html::redirect(str_replace("delete_search_count2=1&", "", $_SERVER['REQUEST_URI']));
      }

      $default_values = array();

      $default_values["start"]       = 0;
      $default_values["order"]       = "ASC";
      $default_values["is_deleted"]  = 0;
      $default_values["distinct"]    = "N";
      $default_values["link"]        = array();
      $default_values["field"]       = array();
      $default_values["contains"]    = array(0 => "");
      $default_values["searchtype"]  = array(0 => "contains");
      $default_values["link2"]       = array();
      $default_values["field2"]      = array(0 => "view");
      $default_values["contains2"]   = array(0 => "");
      $default_values["itemtype2"]   = "";
      $default_values["searchtype2"] = "";
      $default_values["sort"]        = 1;

      if (($itemtype != 'AllAssets')
          && class_exists($itemtype)
          && method_exists($itemtype,'getDefaultSearchRequest')) {

         $default_values = array_merge($default_values,
                                       call_user_func(array($itemtype,
                                                            'getDefaultSearchRequest')));
      }

      // First view of the page or force bookmark : try to load a bookmark
      if ($forcebookmark
          || ($usesession
              && !isset($_GET["reset"])
              && !isset($_SESSION['glpisearch'][$itemtype]))) {

         $query = "SELECT `bookmarks_id`
                   FROM `glpi_bookmarks_users`
                   WHERE `users_id`='".Session::getLoginUserID()."'
                         AND `itemtype` = '$itemtype'";
         if ($result = $DB->query($query)) {
            if ($DB->numrows($result) > 0) {
               $IDtoload = $DB->result($result, 0, 0);
               // Set session variable
               $_SESSION['glpisearch'][$itemtype] = array();
               // Load bookmark on main window
               $bookmark = new Bookmark();
               // Only get datas for bookmarks
               if ($forcebookmark) {
                  $_GET = $bookmark->getParameters($IDtoload);
               } else {
                  $bookmark->load($IDtoload, false);
               }
            }
         }
      }

      if ($usesession
          && isset($_GET["reset"])) {
         if (isset($_SESSION['glpisearch'][$itemtype])) {
            unset($_SESSION['glpisearch'][$itemtype]);
         }
         if (isset($_SESSION['glpisearchcount'][$itemtype])) {
            unset($_SESSION['glpisearchcount'][$itemtype]);
         }
         if (isset($_SESSION['glpisearchcount2'][$itemtype])) {
            unset($_SESSION['glpisearchcount2'][$itemtype]);
         }

         // Bookmark use
         if (isset($_GET["glpisearchcount"])) {
            $_SESSION["glpisearchcount"][$itemtype] = $_GET["glpisearchcount"];
         } else if (isset($_GET["field"])) {
            $_SESSION["glpisearchcount"][$itemtype] = count($_GET["field"]);
         }

         // Bookmark use
         if (isset($_GET["glpisearchcount2"])) {
            $_SESSION["glpisearchcount2"][$itemtype] = $_GET["glpisearchcount2"];
         } else if (isset($_GET["field2"])) {
            $_SESSION["glpisearchcount2"][$itemtype] = count($_GET["field2"]);
         }
      }

      if (is_array($_GET)
          && $usesession) {
         foreach ($_GET as $key => $val) {
            $_SESSION['glpisearch'][$itemtype][$key] = $val;
         }
      }

      foreach ($default_values as $key => $val) {
         if (!isset($_GET[$key])) {
            if ($usesession
                && isset($_SESSION['glpisearch'][$itemtype][$key])) {
               $_GET[$key] = $_SESSION['glpisearch'][$itemtype][$key];
            } else {
               $_GET[$key]                              = $val;
               $_SESSION['glpisearch'][$itemtype][$key] = $val;
            }
         }
      }

      if (!isset($_SESSION["glpisearchcount"][$itemtype])) {
         if (isset($_GET["glpisearchcount"])) {
            $_SESSION["glpisearchcount"][$itemtype] = $_GET["glpisearchcount"];
         } else {
            $_SESSION["glpisearchcount"][$itemtype] = 1;
         }
      }
      if (!isset($_SESSION["glpisearchcount2"][$itemtype])) {
         // Set in URL for bookmark
         if (isset($_GET["glpisearchcount2"])) {
            $_SESSION["glpisearchcount2"][$itemtype] = $_GET["glpisearchcount2"];
         } else {
            $_SESSION["glpisearchcount2"][$itemtype] = 0;
         }
      }
//       Html::printCleanArray($_GET);
   }	
	
	
   static function show($itemtype) {
      self::manageGetValues($itemtype);      
      //Search::manageParams($itemtype);
      return self::showList($itemtype,$_GET);
   }


    static function showGenericSearch($itemtype, array $params) {
      global $LANG,$CFG_GLPI;

      // Default values of parameters
            
      $p['link']        = array();
      $p['field']       = array();
      $p['contains']    = array();
      $p['searchtype']  = array();
      $p['sort']        = '';
      $p['is_deleted']  = 0;
      $p['link2']       = '';
      $p['contains2']   = '';
      $p['field2']      = '';
      $p['itemtype2']   = '';
      $p['searchtype2']  = '';

      foreach ($params as $key => $val) {
         $p[$key]=$val;
      }

      $options=Search::getCleanedOptions($itemtype);
      //$target = Toolbox::getItemTypeSearchURL($itemtype);

      // Instanciate an object to access method
      $item = NULL;
      if ($itemtype!='States' && class_exists($itemtype)) {
         $item = new $itemtype();
      }


      // Meta search names
      $metaactivated = array('Computer'   => $LANG['Menu'][0],
                     'Printer'    => $LANG['Menu'][2],
                     'Monitor'    => $LANG['Menu'][3],
                     'Peripheral' => $LANG['Menu'][16],
                     'Software'   => $LANG['Menu'][4],
                     'Phone'      => $LANG['Menu'][34],
                     'Ticket'     => $LANG['Menu'][5],);

      //$target = substr_replace($target, '/central.php#', strrpos($target, '/'), 1);
      //echo $target;
      //echo "<form name='searchform$itemtype' method='get' action='$target'>";
      
      echo "<form name='searchform$itemtype' method='get' action='".$CFG_GLPI["root_doc"]."/plugins/mobile/front/search.php'>";

      echo "<input type='hidden' name='menu' value='".$_GET['menu']."' />";
      echo "<input type='hidden' name='ssmenu' value='".$_GET['ssmenu']."' />";

      // Display normal search parameters
      for ($i=0 ; $i<$_SESSION["glpisearchcount"][$itemtype] ; $i++) {

         // Display link item
         if ($i>0) {
            echo "<select name='link[$i]'>";
            echo "<option value='AND' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "AND") {
               echo "selected";
            }
            echo ">AND</option>";

            echo "<option value='OR' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "OR") {
               echo "selected";
            }
            echo ">OR</option>";

            echo "<option value='AND NOT' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "AND NOT") {
               echo "selected";
            }
            echo ">AND NOT</option>";

            echo "<option value='OR NOT' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "OR NOT") {
               echo "selected";
            }
            echo ">OR NOT</option>";
            echo "</select>";
         }


         // display select box to define search item
         
         echo "<select id='Search$itemtype$i' name=\"field[$i]\" size='1'>";
         echo "<option value='view' ";
         if (is_array($p['field']) && isset($p['field'][$i]) && $p['field'][$i] == "view") {
            echo "selected";
         }
     
         echo ">".$LANG['search'][11]."</option>";

         reset($options);
         $first_group=true;
         $selected='view';
         foreach ($options as $key => $val) {
            // print groups
                        
            if (!is_array($val)) {
               if (!$first_group) {
                  echo "</optgroup>";
               } else {
                  $first_group=false;
               }
               echo "<optgroup label='$val'>";
            } else {
               if (!isset($val['nosearch']) || $val['nosearch']==false) {
                  echo "<option title=\"".Html::cleanInputText($val["name"])."\" value='$key'";
                  if (is_array($p['field']) && isset($p['field'][$i]) && $key == $p['field'][$i]) {
                     echo "selected";
                     $selected=$key;
                  }
                  echo ">". Toolbox::substr($val["name"],0,28) ."</option>";
               }
            }
         }
         if (!$first_group) {
            echo "</optgroup>";
         }
         echo "<option value='all' ";
         if (is_array($p['field']) && isset($p['field'][$i]) && $p['field'][$i] == "all") {
            echo "selected";
         }
         echo ">".$LANG['common'][66]."</option>";
         echo "</select>";

         echo "<span id='SearchSpan$itemtype$i'>\n";
         $_POST['itemtype']=$itemtype;
         $_POST['num']=$i;
         $_POST['field']=$selected;
         
         //$_POST['meta']="type='search'";         
         $_POST['searchtype']=(is_array($p['searchtype']) && isset($p['searchtype'][$i])?$p['searchtype'][$i]:"" );
         $_POST['value']=(is_array($p['contains']) && isset($p['contains'][$i])?stripslashes($p['contains'][$i]):"" );
         include(GLPI_ROOT."/plugins/mobile/inc/ajax.function.php");
         include (GLPI_ROOT."/plugins/mobile/ajax/searchoption.php");
         echo "</span>\n";

         $params = array('field'       => '__VALUE__',
                      'itemtype'    => $itemtype,
                      'num'         => $i,
                      'value'       => $_POST["value"],
                      'searchtype'  => $_POST["searchtype"]);
         mobileAjaxUpdateItemOnSelectEvent("Search$itemtype$i","SearchSpan$itemtype$i",
                                  $CFG_GLPI["root_doc"]."/plugins/mobile/ajax/searchoption.php",$params,false);

      }

      // Display meta search items      
      $linked=array();
      if ($_SESSION["glpisearchcount2"][$itemtype]>0) {
         
         // Define meta search items to linked
         
         switch ($itemtype) {
            case 'Computer' :
               $linked = array('Printer', 'Monitor', 'Peripheral', 'Software', 'Phone');
               break;

            case 'Ticket' :
               if (Session::haveRight("ticket",CREATE)) {
                  $linked = array_keys(Ticket::getAllTypesForHelpdesk());
               }
               break;

            case 'Printer' :
            case 'Monitor' :
            case 'Peripheral' :
            case 'Software' :
            case 'Phone' :
               $linked = array('Computer');
               break;
         }
      }
      $metanames=array();

      if (is_array($linked) && count($linked)>0) {
         for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
            $rand=mt_rand();

            // Display link item (not for the first item)
            
            echo "<select name='link2[$i]'>";
            echo "<option value='AND' ";
            if (is_array($p['link2']) && isset($p['link2'][$i]) && $p['link2'][$i] == "AND") {
               echo "selected";
            }
            echo ">AND</option>";

            echo "<option value='OR' ";
            if (is_array($p['link2']) && isset($p['link2'][$i]) && $p['link2'][$i] == "OR") {
               echo "selected";
            }
            echo ">OR</option>";

            echo "<option value='AND NOT' ";
            if (is_array($p['link2']) && isset($p['link2'][$i]) && $p['link2'][$i] == "AND NOT") {
               echo "selected";
            }
            echo ">AND NOT</option>";

            echo "<option value='OR NOT' ";
            if (is_array($p['link2'] )&& isset($p['link2'][$i]) && $p['link2'][$i] == "OR NOT") {
               echo "selected";
            }
            echo ">OR NOT</option>";
            echo "</select>";

            // Display select of the linked item type available
            echo "<select name='itemtype2[$i]' id='itemtype2_".$itemtype."_".$i."_$rand'>";
            echo "<option value=''>".DROPDOWN_EMPTY_VALUE."</option>";
            foreach ($linked as $key) {
               if (!isset($metanames[$key])) {
                  $linkitem=new $key();
                  $metanames[$key]=$linkitem->getTypeName();
               }
               echo "<option value='$key'>".Toolbox::substr($metanames[$key],0,20)."</option>\n";
            }
            echo "</select>";

         }
      }


      // Display submit button
      echo "<br /><input type='submit' value=\"".$LANG['buttons'][0]."\" class='submit' data-theme='a' data-inline='true'>";

      // For dropdown
      echo "<input type='hidden' name='itemtype' value='$itemtype'>";

      // Reset to start when submit new search
      echo "<input type='hidden' name='start' value='0'>";
      //echo "</form>";
      Html::closeForm();
   }


   static function showList ($itemtype, $params) {
      global $DB,$CFG_GLPI,$LANG, $PLUGIN_HOOKS;

      // Instanciate an object to access method
      $item = NULL;

      if ($itemtype!='States' && class_exists($itemtype)) {
         $item = new $itemtype();
      }

      $_SESSION['plugin_mobile']['rows_limit']=10; // sdb38l
      $_SESSION['plugin_mobile']['cols_limit']=5; // sdb38l
      $LIST_LIMIT=$_SESSION['plugin_mobile']['rows_limit'];

      // Default values of parameters
      $p['link']        = array();//
      $p['field']       = array();//
      $p['contains']    = array();//
      $p['searchtype']  = array();//
      $p['sort']        = '1'; //
      $p['order']       = 'ASC';//
      $p['start']       = 0;//
      $p['is_deleted']  = 0;
      $p['export_all']  = 0;
      $p['link2']       = '';//
      $p['contains2']   = '';//
      $p['field2']      = '';//
      $p['itemtype2']   = '';
      $p['searchtype2']  = '';
      $p['showheader']  = true;

      foreach ($params as $key => $val) {
            $p[$key]=$val;
      }

      if ($p['export_all']) {
         $p['start']=0;
      }

      // Manage defautll seachtype value : for bookmark compatibility
      if (count($p['contains'])) {
         foreach ($p['contains'] as $key => $val) {
            if (!isset($p['searchtype'][$key])) {
               $p['searchtype'][$key]='contains';
            }
         }
      }
      if (is_array($p['contains2']) && count($p['contains2'])) {
         foreach ($p['contains2'] as $key => $val) {
            if (!isset($p['searchtype2'][$key])) {
               $p['searchtype2'][$key]='contains';
            }
         }
      }

      $target= Toolbox::getItemTypeSearchURL($itemtype);

      $limitsearchopt=Search::getCleanedOptions($itemtype);

      if (isset($CFG_GLPI['union_search_type'][$itemtype])) {
         $itemtable=$CFG_GLPI['union_search_type'][$itemtype];
      } else {
         $itemtable=getTableForItemType($itemtype);
      }


      // Set display type for export if define
      $output_type=Search::HTML_OUTPUT;
      if (isset($_GET['display_type'])) {
         $output_type=$_GET['display_type'];
         // Limit to 10 element
         if ($_GET['display_type']==Search::GLOBAL_SEARCH) {
            $LIST_LIMIT=Search::GLOBAL_SEARCH_DISPLAY_COUNT;
         }
      }
      // hack for States
      if (isset($CFG_GLPI['union_search_type'][$itemtype])) {
         $entity_restrict = true;
      } else {
         $entity_restrict = $item->isEntityAssign();
      }

      $metanames = array();

      // Get the items to display
      $toview=Search::addDefaultToView($itemtype);

      // Add items to display depending of personal prefs
      $displaypref=DisplayPreference::getForTypeUser($itemtype,Session::getLoginUserID());
      if (count($displaypref)) {
         foreach ($displaypref as $val) {
            array_push($toview,$val);
         }
      }

      // Add searched items
      if (count($p['field'])>0) {
         foreach($p['field'] as $key => $val) {
            if (!in_array($val,$toview) && $val!='all' && $val!='view') {
               array_push($toview,$val);
            }
         }
      }

      // Add order item
      if (!in_array($p['sort'],$toview)) {
         array_push($toview,$p['sort']);
      }

      // Special case for Ticket : put ID in front
      if ($itemtype=='Ticket') {
         array_unshift($toview,2);
      }

      // Clean toview array
      $toview=array_unique($toview);
      foreach ($toview as $key => $val) {
         if (!isset($limitsearchopt[$val])) {
            unset($toview[$key]);
         }
      }

      // delete entities display
      //var_dump($toview);
      if (array_search('80', $toview) !== false) unset($toview[array_search('80', $toview)]);

      $toview_count=count($toview);

      // Construct the request

      //// 1 - SELECT
      //$SELECT = "SELECT ".PluginMobileSearch::addDefaultSelect($itemtype);
      $SELECT = "SELECT '".$_SESSION['glpiname']."' AS currentuser, ".PluginMobileSearch::addDefaultSelect($itemtype);

      // Add select for all toview item
      foreach ($toview as $key => $val) {
         $SELECT.= Search::addSelect($itemtype,$val,$key,0);
      }


      //// 2 - FROM AND LEFT JOIN
      // Set reference table
      $FROM = " FROM `$itemtable`";

      // Init already linked tables array in order not to link a table several times
      $already_link_tables=array();
      // Put reference table
      array_push($already_link_tables,$itemtable);

      // Add default join
      $COMMONLEFTJOIN = Search::addDefaultJoin($itemtype,$itemtable,$already_link_tables);
      $FROM .= $COMMONLEFTJOIN;

      $searchopt=array();
      $searchopt[$itemtype]=&Search::getOptions($itemtype);
      // Add all table for toview items
      //foreach ($toview as $key => $val) {
      //   $FROM .= Search::addLeftJoin($itemtype,$itemtable,$already_link_tables,
      //                        $searchopt[$itemtype][$val]["table"],
      //                        $searchopt[$itemtype][$val]["linkfield"]);
      //}
      foreach ($toview as $key => $val) {
         $FROM .= self::addLeftJoin($itemtype, $itemtable, $already_link_tables,
                                    $searchopt[$itemtype][$val]["table"],
                                    $searchopt[$itemtype][$val]["linkfield"], 0, 0,
                                    $searchopt[$itemtype][$val]["joinparams"]);
      }


      // Search all case :
      //if (in_array("all",$p['field'])) {
      //   foreach ($searchopt[$itemtype] as $key => $val) {
      //      // Do not search on Group Name
      //      if (is_array($val)) {
      //         $FROM .= Search::addLeftJoin($itemtype,$itemtable,$already_link_tables,
      //                              $searchopt[$itemtype][$key]["table"],
      //                              $searchopt[$itemtype][$key]["linkfield"]);
      //      }
      //   }
      //}
      // Search all case :
      if (in_array("all",$p['field'])) {
         foreach ($searchopt[$itemtype] as $key => $val) {
            // Do not search on Group Name
            if (is_array($val)) {
               $FROM .= self::addLeftJoin($itemtype, $itemtable, $already_link_tables,
                                          $searchopt[$itemtype][$key]["table"],
                                          $searchopt[$itemtype][$key]["linkfield"], 0, 0,
                                          $searchopt[$itemtype][$key]["joinparams"]);
            }
         }
      }



      //// 3 - WHERE

      // default string
      $COMMONWHERE = self::addDefaultWhere($itemtype);
      $first=empty($COMMONWHERE);

      // Add deleted if item have it
      if ($item && $item->maybeDeleted()) {
         $LINK= " AND " ;
         if ($first) {
            $LINK=" ";
            $first=false;
         }
         $COMMONWHERE .= $LINK."`$itemtable`.`is_deleted` = '".$p['is_deleted']."' ";
      }

      // Remove template items
      if ($item && $item->maybeTemplate()) {
         $LINK= " AND " ;
         if ($first) {
            $LINK=" ";
            $first=false;
         }
         $COMMONWHERE .= $LINK."`$itemtable`.`is_template` = '0' ";
      }

      // Add Restrict to current entities
      if ($entity_restrict) {
         $LINK= " AND " ;
         if ($first) {
            $LINK=" ";
            $first=false;
         }

         if ($itemtype == 'Entity') {
            $COMMONWHERE .= getEntitiesRestrictRequest($LINK,$itemtable,'id','',true);
         } else if (isset($CFG_GLPI["union_search_type"][$itemtype])) {

            // Will be replace below in Union/Recursivity Hack
            $COMMONWHERE .= $LINK." ENTITYRESTRICT ";
         } else {
            $COMMONWHERE .= getEntitiesRestrictRequest($LINK,$itemtable,'','',$item->maybeRecursive());
         }
      }
      $WHERE="";
      $HAVING="";

      // Add search conditions
      // If there is search items
      if ($_SESSION["glpisearchcount"][$itemtype]>0 && count($p['contains'])>0) {
         for ($key=0 ; $key<$_SESSION["glpisearchcount"][$itemtype] ; $key++) {
            // if real search (strlen >0) and not all and view search
            if (isset($p['contains'][$key]) && strlen($p['contains'][$key])>0) {
               // common search
               if ($p['field'][$key]!="all" && $p['field'][$key]!="view") {
                  $LINK=" ";
                  $NOT=0;
                  $tmplink="";
                  if (is_array($p['link']) && isset($p['link'][$key])) {
                     if (strstr($p['link'][$key],"NOT")) {
                        $tmplink=" ".str_replace(" NOT","",$p['link'][$key]);
                        $NOT=1;
                     } else {
                        $tmplink=" ".$p['link'][$key];
                     }
                  } else {
                     $tmplink=" AND ";
                  }

                  if (isset($searchopt[$itemtype][$p['field'][$key]]["usehaving"])) {
                     // Manage Link if not first item
                     if (!empty($HAVING)) {
                        $LINK=$tmplink;
                     }
                     // Find key
                     $item_num=array_search($p['field'][$key],$toview);
                     $HAVING .= Search::addHaving($LINK,$NOT,$itemtype,$p['field'][$key],$p['searchtype'][$key],$p['contains'][$key],0,$item_num);
                  } else {
                     // Manage Link if not first item
                     if (!empty($WHERE)) {
                        $LINK=$tmplink;
                     }
                     $WHERE .= self::addWhere($LINK,$NOT,$itemtype,$p['field'][$key],$p['searchtype'][$key],$p['contains'][$key]);
                  }

               // view and all search
               } else {
                  $LINK=" OR ";
                  $NOT=0;
                  $globallink=" AND ";
                  if (is_array($p['link']) && isset($p['link'][$key])) {
                     switch ($p['link'][$key]) {
                        case "AND" :
                           $LINK=" OR ";
                           $globallink=" AND ";
                           break;

                        case "AND NOT" :
                           $LINK=" AND ";
                           $NOT=1;
                           $globallink=" AND ";
                           break;

                        case "OR" :
                           $LINK=" OR ";
                           $globallink=" OR ";
                           break;

                        case "OR NOT" :
                           $LINK=" AND ";
                           $NOT=1;
                           $globallink=" OR ";
                           break;
                     }
                  } else {
                     $tmplink=" AND ";
                  }

                  // Manage Link if not first item
                  if (!empty($WHERE)) {
                     $WHERE .= $globallink;
                  }
                  $WHERE.= " ( ";
                  $first2=true;

                  $items=array();
                  if ($p['field'][$key]=="all") {
                     $items=$searchopt[$itemtype];
                  } else { // toview case : populate toview
                     foreach ($toview as $key2 => $val2) {
                        $items[$val2]=$searchopt[$itemtype][$val2];
                     }
                  }

                  foreach ($items as $key2 => $val2) {
                     if (is_array($val2)) {
                        // Add Where clause if not to be done in HAVING CLAUSE
                        if (!isset($val2["usehaving"])) {
                           $tmplink=$LINK;
                           if ($first2) {
                              $tmplink=" ";
                              $first2=false;
                           }
                           $WHERE .= self::addWhere($tmplink,$NOT,$itemtype,$key2,$p['searchtype'][$key],$p['contains'][$key]);
                        }
                     }
                  }
                  $WHERE.=" ) ";
               }
            }
         }
      }

      //// 4 - ORDER
      $ORDER=" ORDER BY `id` ";
      foreach($toview as $key => $val) {
         if ($p['sort']==$val) {
            $ORDER= self::addOrderBy($itemtype,$p['sort'],$p['order'],$key);
         }
      }


      //// 5 - META SEARCH
      // Preprocessing
      if ($_SESSION["glpisearchcount2"][$itemtype]>0 && is_array($p['itemtype2'])) {

         // a - SELECT
         for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
            if (isset($p['itemtype2'][$i]) && !empty($p['itemtype2'][$i]) && isset($p['contains2'][$i])
               && strlen($p['contains2'][$i])>0) {

               $SELECT .= self::addSelect($p['itemtype2'][$i],$p['field2'][$i],$i,1,$p['itemtype2'][$i]);
            }
         }

         // b - ADD LEFT JOIN
         // Already link meta table in order not to linked a table several times
         $already_link_tables2=array();
         // Link reference tables
         for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
            if (isset($p['itemtype2'][$i]) && !empty($p['itemtype2'][$i]) && isset($p['contains2'][$i])
               && strlen($p['contains2'][$i])>0) {
               if (!in_array(getTableForItemType($p['itemtype2'][$i]),$already_link_tables2)) {
                  $FROM .= Search::addMetaLeftJoin($itemtype,$p['itemtype2'][$i],$already_link_tables2,
                                          (($p['contains2'][$i]=="NULL")||(strstr($p['link2'][$i],"NOT"))));
               }
            }
         }
         // Link items tables
         for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
            if (isset($p['itemtype2'][$i]) && !empty($p['itemtype2'][$i]) && isset($p['contains2'][$i])
               && strlen($p['contains2'][$i])>0) {
               if (!isset($searchopt[$p['itemtype2'][$i]])) {
                  $searchopt[$p['itemtype2'][$i]]=&self::getOptions($p['itemtype2'][$i]);
               }
               if (!in_array($searchopt[$p['itemtype2'][$i]][$p['field2'][$i]]["table"]."_".$p['itemtype2'][$i],
                           $already_link_tables2)) {

                  $FROM .= self::addLeftJoin($p['itemtype2'][$i],getTableForItemType($p['itemtype2'][$i]),$already_link_tables2,
                                       $searchopt[$p['itemtype2'][$i]][$p['field2'][$i]]["table"],
                                       $searchopt[$p['itemtype2'][$i]][$p['field2'][$i]]["linkfield"],
                                       1,$p['itemtype2'][$i],
                                       $searchopt[$p['itemtype2'][$i]][$p['field2'][$i]]["joinparams"]);
               }
            }
         }
      }


      //// 6 - Add item ID
      // Add ID to the select
      if (!empty($itemtable)) {
         $SELECT .= "`$itemtable`.`id` AS id ";
      }


      //// 7 - Manage GROUP BY
      $GROUPBY = "";
      // Meta Search / Search All / Count tickets
      if ($_SESSION["glpisearchcount2"][$itemtype]>0 || !empty($HAVING) || in_array('all',$p['field'])) {
         $GROUPBY = " GROUP BY `$itemtable`.`id`";
      }

      if (empty($GROUPBY)) {
         foreach ($toview as $key2 => $val2) {
            if (!empty($GROUPBY)) {
               break;
            }
            if (isset($searchopt[$itemtype][$val2]["forcegroupby"])) {
               $GROUPBY = " GROUP BY `$itemtable`.`id`";
            }
         }
      }

      // Specific search for others item linked  (META search)
      if (is_array($p['itemtype2'])) {
         for ($key=0 ; $key<$_SESSION["glpisearchcount2"][$itemtype] ; $key++) {
            if (isset($p['itemtype2'][$key]) && !empty($p['itemtype2'][$key]) && isset($p['contains2'][$key])
               && strlen($p['contains2'][$key])>0) {
               $LINK="";

               // For AND NOT statement need to take into account all the group by items
               if (strstr($p['link2'][$key],"AND NOT")
                  || isset($searchopt[$p['itemtype2'][$key]][$p['field2'][$key]]["usehaving"])) {

                  $NOT=0;
                  if (strstr($p['link2'][$key],"NOT")) {
                     $tmplink = " ".str_replace(" NOT","",$p['link2'][$key]);
                     $NOT=1;
                  } else {
                     $tmplink = " ".$p['link2'][$key];
                  }
                  if (!empty($HAVING)) {
                     $LINK=$tmplink;
                  }
                  $HAVING .= self::addHaving($LINK,$NOT,$p['itemtype2'][$key],$p['field2'][$key],$p['searchtype2'][$key],$p['contains2'][$key],1,$key);
               } else { // Meta Where Search
                  $LINK=" ";
                  $NOT=0;
                  // Manage Link if not first item
                  if (is_array($p['link2']) && isset($p['link2'][$key]) && strstr($p['link2'][$key],"NOT")) {
                     $tmplink = " ".str_replace(" NOT","",$p['link2'][$key]);
                     $NOT=1;
                  } else if (is_array($p['link2']) && isset($p['link2'][$key])) {
                     $tmplink = " ".$p['link2'][$key];
                  } else {
                     $tmplink = " AND ";
                  }
                  if (!empty($WHERE)) {
                     $LINK=$tmplink;
                  }
                  $WHERE .= self::addWhere($LINK,$NOT,$p['itemtype2'][$key],$p['field2'][$key],$p['searchtype2'][$key],$p['contains2'][$key],1);
               }
            }
         }
      }


      // Use a ReadOnly connection if available and configured to be used
      $DBread = DBConnection::getReadConnection();


      // If no research limit research to display item and compute number of item using simple request
      $nosearch=true;
      for ($i=0 ; $i<$_SESSION["glpisearchcount"][$itemtype] ; $i++) {
         if (isset($p['contains'][$i]) && strlen($p['contains'][$i])>0) {
            $nosearch=false;
         }
      }

      if ($_SESSION["glpisearchcount2"][$itemtype]>0) {
         $nosearch=false;
      }

      $LIMIT="";
      $numrows=0;
      //No search : count number of items using a simple count(ID) request and LIMIT search
      if ($nosearch) {
         $LIMIT= " LIMIT ".$p['start'].", ".$LIST_LIMIT;

         // Force group by for all the type -> need to count only on table ID
         if (!isset($searchopt[$itemtype][1]['forcegroupby'])) {
            $count = "count(*)";
         } else {
            $count = "count(DISTINCT `$itemtable`.`id`)";
         }
         // request currentuser for SQL supervision, not displayed
         $query_num = "SELECT $count, '".$_SESSION['glpiname']."' AS currentuser
                       FROM `$itemtable`".
                       $COMMONLEFTJOIN;

         $first=true;

         if (!empty($COMMONWHERE)) {
            $LINK= " AND " ;
            if ($first) {
               $LINK = " WHERE ";
               $first=false;
            }
            $query_num .= $LINK.$COMMONWHERE;
         }
         // Union Search :
         if (isset($CFG_GLPI["union_search_type"][$itemtype])) {
            $tmpquery=$query_num;
            $numrows=0;

            foreach ($CFG_GLPI[$CFG_GLPI["union_search_type"][$itemtype]] as $ctype) {
               $ctable=getTableForItemType($ctype);
               $citem=new $ctype();
               if ($citem->canView()) {
                  // State case
                  if ($itemtype == 'States') {
                     $query_num=str_replace($CFG_GLPI["union_search_type"][$itemtype],
                                          $ctable,$tmpquery);
                     $query_num .= " AND $ctable.`states_id` > '0' ";
                    // Add deleted if item have it
                     if ($citem && $citem->maybeDeleted()) {
                        $query_num .= " AND `$ctable`.`is_deleted` = '0' ";
                     }

                     // Remove template items
                     if ($citem && $citem->maybeTemplate()) {
                        $query_num .= " AND `$ctable`.`is_template` = '0' ";
                     }

                  } else {// Ref table case
                     $reftable=getTableForItemType($itemtype);
                     $replace = "FROM `$reftable`
                                 INNER JOIN `$ctable`
                                 ON (`$reftable`.`items_id`=`$ctable`.`id`
                                    AND `$reftable`.`itemtype` = '$ctype')";

                     $query_num=str_replace("FROM `".$CFG_GLPI["union_search_type"][$itemtype]."`",
                                          $replace,$tmpquery);
                     $query_num=str_replace($CFG_GLPI["union_search_type"][$itemtype],
                                          $ctable,$query_num);
                  }
                  $query_num=str_replace("ENTITYRESTRICT",
                                       getEntitiesRestrictRequest('',$ctable,'','',$citem->maybeRecursive()),
                                       $query_num);
                  $result_num = $DBread->query($query_num);
                  $numrows+= $DBread->result($result_num,0,0);
               }
            }
         } else {
            $result_num = $DBread->query($query_num);
            $numrows= $DBread->result($result_num,0,0);
         }
      }

      // If export_all reset LIMIT condition
      if ($p['export_all']) {
         $LIMIT="";
      }

      if (!empty($WHERE) || !empty($COMMONWHERE)) {
         if (!empty($COMMONWHERE)) {
            $WHERE =' WHERE '.$COMMONWHERE.(!empty($WHERE)?' AND ( '.$WHERE.' )':'');
         } else {
            $WHERE =' WHERE '.$WHERE.' ';
         }
         $first=false;
      }

      if (!empty($HAVING)) {
         $HAVING=' HAVING '.$HAVING;
      }

      $DB->query("SET SESSION group_concat_max_len = 9999999;");

      // Create QUERY
      if (isset($CFG_GLPI["union_search_type"][$itemtype])) {
         $first=true;
         $QUERY="";
         foreach ($CFG_GLPI[$CFG_GLPI["union_search_type"][$itemtype]] as $ctype) {
            $ctable = getTableForItemType($ctype);
            $citem = new $ctype();
            if ($citem->canView()) {
               if ($first) {
                  $first=false;
               } else {
                  $QUERY.=" UNION ";
               }
               $tmpquery="";
               // State case
               if ($itemtype == 'States') {
                  $tmpquery = $SELECT.", '$ctype' AS TYPE ".
                              $FROM.
                              $WHERE;
                  $tmpquery = str_replace($CFG_GLPI["union_search_type"][$itemtype],
                                          $ctable,$tmpquery);
                  $tmpquery .= " AND `$ctable`.`states_id` > '0' ";
                  // Add deleted if item have it
                  if ($citem && $citem->maybeDeleted()) {
                     $tmpquery .= " AND `$ctable`.`is_deleted` = '0' ";
                  }

                  // Remove template items
                  if ($citem && $citem->maybeTemplate()) {
                     $tmpquery .= " AND `$ctable`.`is_template` = '0' ";
                  }


               } else {// Ref table case
                  $reftable=getTableForItemType($itemtype);

                  $tmpquery = $SELECT.", '$ctype' AS TYPE, `$reftable`.`id` AS refID, ".
                                    "`$ctable`.`entities_id` AS ENTITY ".
                              $FROM.
                              $WHERE;
                  $replace = "FROM `$reftable`".
                     " INNER JOIN `$ctable`".
                     " ON (`$reftable`.`items_id`=`$ctable`.`id`".
                     " AND `$reftable`.`itemtype` = '$ctype')";
                  $tmpquery = str_replace("FROM `".$CFG_GLPI["union_search_type"][$itemtype]."`",$replace,
                                          $tmpquery);
                  $tmpquery = str_replace($CFG_GLPI["union_search_type"][$itemtype],
                                          $ctable,$tmpquery);
               }
               $tmpquery = str_replace("ENTITYRESTRICT",
                                    getEntitiesRestrictRequest('',$ctable,'','',$citem->maybeRecursive()),
                                    $tmpquery);

               // SOFTWARE HACK
               if ($ctype == 'Software') {
                  $tmpquery = str_replace("glpi_softwares.serial","''",$tmpquery);
                  $tmpquery = str_replace("glpi_softwares.otherserial","''",$tmpquery);
               }
               $QUERY .= $tmpquery;
            }
         }
         if (empty($QUERY)) {
            echo Search::showError($output_type);
            return;
         }
         $QUERY .= str_replace($CFG_GLPI["union_search_type"][$itemtype].".","",$ORDER).
                  $LIMIT;
      } else {
         $QUERY = $SELECT.
                  $FROM.
                  $WHERE.
                  $GROUPBY.
                  $HAVING.
                  $ORDER.
                  $LIMIT;
      }

      $DBread->query("SET SESSION group_concat_max_len = 4096;");
      $result = $DBread->query($QUERY);
      if ($result2 = $DBread->query('SHOW WARNINGS')) {
       if ($DBread->numrows($result2) > 0) {
            $data = $DBread->fetch_assoc($result2);
            if ($data['Code'] == 1260) {
               $DBread->query("SET SESSION group_concat_max_len = 4194304;");
               $result = $DBread->query($QUERY);
            }
         }
      }

      if ($result) {
         // if real search or complete export : get numrows from request
         if (!$nosearch||$p['export_all']) {
            $numrows= $DBread->numrows($result);
         }

         // Contruct Pager parameters
         $globallinkto = "";
         if (count($p['field']) > 0) $globallinkto .= self::getArrayUrlLink("field",$p['field']);
         if ($p['link'] != '' ) $globallinkto .= self::getArrayUrlLink("link",$p['link']);
         if ($p['contains'] != array("")) $globallinkto .= self::getArrayUrlLink("contains",$p['contains']);
         if (count($p['field2']) > 0) $globallinkto .= self::getArrayUrlLink("field2",$p['field2']);
         if ($p['contains2'] != array("")) $globallinkto .= self::getArrayUrlLink("contains2",$p['contains2']);
         if ($p['itemtype2'] != '' ) $globallinkto .= self::getArrayUrlLink("itemtype2",$p['itemtype2']);
         if ($p['link2'] != '' ) $globallinkto .= self::getArrayUrlLink("link2",$p['link2']);



         $parameters = "sort=".$p['sort']."&amp;order=".$p['order'].$globallinkto;

         if ($output_type==Search::GLOBAL_SEARCH) {
            if (class_exists($itemtype)) {
               echo "<div class='center'><h2>".$item->getTypeName();
               // More items
               if ($numrows>$p['start']+Search::GLOBAL_SEARCH_DISPLAY_COUNT) {
                  echo " <a href='$target?$parameters' data-back='false'>".$LANG['common'][66]."</a>";
               }
               echo "</h2></div>\n";
            } else {
               return false;
            }
         }

         // If the begin of the view is before the number of items
         if ($p['start']<$numrows) {

            // Form to massive actions
            $isadmin=false;

            // Compute number of columns to display
            // Add toview elements
            $nbcols=$toview_count;
            $already_printed = array();
            // Add meta search elements if real search (strlen>0) or only NOT search
            if ($_SESSION["glpisearchcount2"][$itemtype]>0 && is_array($p['itemtype2'])) {
               for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
                  if (isset($p['itemtype2'][$i])
                     && isset($p['contains2'][$i])
                     && strlen($p['contains2'][$i])>0
                     && !empty($p['itemtype2'][$i])
                     && (!isset($p['link2'][$i]) || !strstr($p['link2'][$i],"NOT"))) {
                        if (!isset($already_printed[$p['itemtype2'][$i].$p['field2'][$i]])) {
                          $nbcols++;
                        $already_printed[$p['itemtype2'][$i].$p['field2'][$i]] = 1;
                     }
                  }
               }
            }

            if ($output_type==Search::HTML_OUTPUT) { // HTML display - massive modif
               $nbcols++;
            }

            // Define begin and end var for loop
            // Search case
            $begin_display=$p['start'];
            $end_display=$p['start']+$LIST_LIMIT;

            // No search Case
            if ($nosearch) {
               $begin_display=0;
               $end_display=min($numrows-$p['start'],$LIST_LIMIT);
            }

            // Export All case
            if ($p['export_all']) {
               $begin_display=0;
               $end_display=$numrows;
            }

            // Display List Header
            echo PluginMobileSearch::showHeader($output_type,$end_display-$begin_display+1,$nbcols);

            // New Line for Header Items Line
            echo self::showNewLine($output_type);
            $header_num=1;

            if ($output_type==Search::HTML_OUTPUT) { // HTML display - massive modif
               $search_config="";

               echo PluginMobileSearch::showHeaderItem($output_type,$search_config,$header_num,"",0,$p['order']);
            }

            if ($p['showheader']) {
               // Display column Headers for toview items
               echo "<div data-type='horizontal' data-role='controlgroup' class='mobile_list_header'>";
               $cpt=0;
               foreach ($toview as $key => $val) {
                  $linkto='';
                  if (!isset($searchopt[$itemtype][$val]['nosort'])
                        || !$searchopt[$itemtype][$val]['nosort']) {
                     /*$linkto = "$target?itemtype=$itemtype&amp;sort=".$val."&amp;order=".($p['order']=="ASC"?"DESC":"ASC").
                              "&amp;start=".$p['start'].$globallinkto;*/
                     $linkto = "search.php?itemtype=$itemtype&amp;menu=".$_GET['menu']
                              ."&amp;ssmenu=".$_GET['ssmenu']."&amp;sort=".$val
                              ."&amp;order=".($p['order']=="ASC"?"DESC":"ASC")
                              ."&amp;start=".$p['start'].$globallinkto;
                  }
                  echo PluginMobileSearch::showHeaderItem($output_type,$searchopt[$itemtype][$val]["name"],
                                             $header_num,$linkto,$p['sort']==$val,$p['order']);
                  $cpt++;
                  if ($cpt == $_SESSION['plugin_mobile']['cols_limit']) break;
               }
               echo "<a href='searchconfig.php?type=global&amp;itemtype=$itemtype&amp;rand=".mt_rand()."' data-icon='plus'
                  data-role='button' class='button-header'>&nbsp;</a>";
               echo "</div>";

               // Display columns Headers for meta items
               $already_printed = array();
               if ($_SESSION["glpisearchcount2"][$itemtype]>0 && is_array($p['itemtype2'])) {
                  for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
                     if (isset($p['itemtype2'][$i]) && !empty($p['itemtype2'][$i]) && isset($p['contains2'][$i])
                        && strlen($p['contains2'][$i])>0) {

                      if (!isset($already_printed[$p['itemtype2'][$i].$p['field2'][$i]])) {
                        if (!isset($metanames[$p['itemtype2'][$i]])) {
                           $metaitem = new $p['itemtype2'][$i]();


                        echo PluginMobileSearch::showHeaderItem($output_type,$metanames[$p['itemtype2'][$i]]." - ".
                                                   $searchopt[$p['itemtype2'][$i]][$p['field2'][$i]]["name"],
                                                   $header_num);
                        $already_printed[$p['itemtype2'][$i].$p['field2'][$i]] = 1;
                        }
                      }
                     }
                  }
               }


               // End Line for column headers
               echo PluginMobileSearch::showEndLine($output_type);
            }


            // if real search seek to begin of items to display (because of complete search)
            if (!$nosearch) {
               $DB->data_seek($result,$p['start']);
            }

            // Define begin and end var for loop
            // Search case
            $i=$begin_display;

            // Init list of items displayed
            if ($output_type==Search::HTML_OUTPUT) {
               Session::initNavigateListItems($itemtype);
            }

            // Num of the row (1=header_line)
            $row_num=1;
            // Display Loop
            while ($i < $numrows && $i<($end_display)) {
               // Column num
               $item_num=1;
               // Get data and increment loop variables
               $data=$DBread->fetch_assoc($result);
               $i++;
               $row_num++;
               // New line

               // Add item in item list
               Session::addToNavigateListItems($itemtype,$data["id"]);

               /*if ($output_type==Search::HTML_OUTPUT) { // HTML display - massive modif
                  $tmpcheck="";
                  if ($isadmin) {
                     if ($itemtype == 'Entity'
                        && !in_array($data["id"],$_SESSION["glpiactiveentities"])) {

                        $tmpcheck="&nbsp;";
                     } else if ($item->maybeRecursive()
                              && !in_array($data["entities_id"],$_SESSION["glpiactiveentities"])) {
                        $tmpcheck="&nbsp;";
                     } else {
                        $sel="";
                        if (isset($_GET["select"]) && $_GET["select"]=="all") {
                           $sel="checked";
                        }
                        if (isset($_SESSION['glpimassiveactionselected'][$data["id"]])) {
                           $sel="checked";
                        }
                        $tmpcheck="<input type='checkbox' name='item[".$data["id"]."]' value='1' $sel>";
                     }
                  }
                  echo PluginMobileSearch::showItem($output_type,$tmpcheck,$item_num,$row_num,"width='10'");
               }*/


               // Print other toview items
               
               $itemsToShow = array();
               $cpt=0;
               foreach ($toview as $key => $val) {
                  $itemsToShow[] = PluginMobileSearch::showItem($output_type,PluginMobileSearch::giveItem($itemtype,$val,$data,$key),$item_num,
                                       $row_num,
                           self::displayConfigItem($itemtype,$val,$data,$key));
                  $cpt++;
                  if ($cpt == $_SESSION['plugin_mobile']['cols_limit']) break;
               }

              // Print Meta Item
               if ($_SESSION["glpisearchcount2"][$itemtype]>0 && is_array($p['itemtype2'])) {
                  for ($j=0 ; $j<$_SESSION["glpisearchcount2"][$itemtype] ; $j++) {
                     if (isset($p['itemtype2'][$j]) && !empty($p['itemtype2'][$j]) && isset($p['contains2'][$j])
                        && strlen($p['contains2'][$j])>0) {

                        // General case
                        if (strpos($data["META_$j"],"$$$$")===false) {
                           $out=self::giveItem ($p['itemtype2'][$j],$p['field2'][$j],$data,$j,1);
                           $itemsToShow[] = PluginMobileSearch::showItem($output_type,$out,$item_num,$row_num);

                        // Case of GROUP_CONCAT item : split item and multilline display
                        } else {
                           $split=explode("$$$$",$data["META_$j"]);
                           $count_display=0;
                           $out="";
                           $unit="";
                           $separate='<br>';
                           if (isset($searchopt[$p['itemtype2'][$j]][$p['field2'][$j]]['splititems'])
                              && $searchopt[$p['itemtype2'][$j]][$p['field2'][$j]]['splititems']) {
                              $separate='<hr>';
                           }

                           if (isset($searchopt[$p['itemtype2'][$j]][$p['field2'][$j]]['unit'])) {
                              $unit=$searchopt[$p['itemtype2'][$j]][$p['field2'][$j]]['unit'];
                           }
                           for ($k=0 ; $k<count($split) ; $k++) {
                              if ($p['contains2'][$j]=="NULL" || strlen($p['contains2'][$j])==0
                                 ||preg_match('/'.$p['contains2'][$j].'/i',$split[$k])
                                 || isset($searchopt[$p['itemtype2'][$j]][$p['field2'][$j]]['forcegroupby'])) {

                                 if ($count_display) {
                                    $out.= $separate;
                                 }
                                 $count_display++;

                                 // Manage Link to item
                                 $split2=explode("$$",$split[$k]);
                                 if (isset($split2[1])) {
                                    $out .= "<a href=\"".getItemTypeFormURLMobile($p['itemtype2'][$j])."?id=".$split2[1]."\" data-back='false'>";
                                    $out .= $split2[0].$unit;
                                    if ($_SESSION["glpiis_ids_visible"] || empty($split2[0])) {
                                       $out .= " (".$split2[1].")";
                                    }
                                    $out .= "</a>";
                                 } else {
                                    $out .= $split[$k].$unit;
                                 }
                              }
                           }
                           $itemsToShow[] = PluginMobileSearch::showItem($output_type,$out,$item_num,$row_num);
                        }
                     }
                  }
               }
               // Specific column display
               
               if ($itemtype == 'CartridgeItem') {
                  $itemsToShow[] = PluginMobileSearch::showItem($output_type,
                                       Cartridge::getCount($data["id"],$data["ALARM"],$output_type),
                                       $item_num,$row_num);
               }
               if ($itemtype == 'ConsumableItem') {
                  $itemsToShow[] = PluginMobileSearch::showItem($output_type,
                                       Consumable::getCount($data["id"],$data["ALARM"],$output_type),
                                       $item_num,$row_num);
               }
               if ($itemtype == 'States' || $itemtype == 'ReservationItem') {
                  $typename=$data["TYPE"];
                  if (class_exists($data["TYPE"])) {
                     $itemtmp = new $data["TYPE"]();
                     $typename=$itemtmp->getTypeName();
                  }
                  $itemsToShow[] = PluginMobileSearch::showItem($output_type,$typename,$item_num,$row_num);
               }
               if ($itemtype == 'ReservationItem' && $output_type == Search::HTML_OUTPUT) {
                  if (Session::haveRight("reservation_central",UPDATE)) {
                     if (!haveAccessToEntity($data["ENTITY"])) {
                        $itemsToShow[] = PluginMobileSearch::showItem($output_type,"&nbsp;",$item_num,$row_num);
                        $itemsToShow[] = PluginMobileSearch::showItem($output_type,"&nbsp;",$item_num,$row_num);
                     } else {
                        $itemsToShow[] = PluginMobileSearch::showItem($output_type,
                              "<a href=\"".getItemTypeFormURLMobile($itemtype)."?id=".$data["refID"].
                              "&amp;is_active=".($data["ACTIVE"]?0:1)."&amp;update=update\" ".
                              "title='".($data["ACTIVE"]?$LANG['buttons'][42]:$LANG['buttons'][41])."' data-back='false'><img src=\"".
                              $CFG_GLPI["root_doc"]."/pics/".($data["ACTIVE"]?"moins":"plus").
                              ".png\" alt='' title=''></a>",
                              $item_num,$row_num,"class='center'");
                        /*echo PluginMobileSearch::showItem($output_type,"<a href=\"javascript:confirmAction('".
                                       addslashes($LANG['reservation'][38])."\\n".
                                       addslashes($LANG['reservation'][39])."','".
                                       Toolbox::getItemTypeFormURL($itemtype)."?id=".$data["refID"].
                                       "&amp;delete=delete')\" title='".
                                       $LANG['reservation'][6]."'><img src=\"".
                                       $CFG_GLPI["root_doc"]."/pics/delete.png\" alt='' title=''></a>",
                                       $item_num,$row_num,"class='center'");*/
                     }
                  }
                  if ($data["ACTIVE"]) {
                  	
                     $itemsToShow[] = PluginMobileSearch::showItem($output_type,"<a href='reservation.php?reservationitems_id=".
                                    $data["refID"]."' title='".$LANG['reservation'][21]."' data-back='false'><img src=\"".
                                    $CFG_GLPI["root_doc"]."/pics/reservation-3.png\" alt='' title=''></a>",
                                    $item_num,$row_num,"class='center'");
                  } else {
                     $itemsToShow[] = PluginMobileSearch::showItem($output_type,"&nbsp;",$item_num,$row_num);
                  }
               }

               echo PluginMobileSearch::showNewLine($output_type,($i%2));
               foreach ($itemsToShow as $item)
                  echo $item;
               // End Line
               echo PluginMobileSearch::showEndLine($output_type);
            }

            $title="";
            // Create title
            
            if ($output_type==Search::PDF_OUTPUT_LANDSCAPE || $output_type==Search::PDF_OUTPUT_PORTRAIT) {
               if ($_SESSION["glpisearchcount"][$itemtype]>0 && count($p['contains'])>0) {
                  for ($key=0 ; $key<$_SESSION["glpisearchcount"][$itemtype] ; $key++) {
                     if (strlen($p['contains'][$key])>0) {
                        if (isset($p["link"][$key])) {
                           $title.=" ".$p["link"][$key]." ";
                        }
                        switch ($p['field'][$key]) {
                           case "all" :
                              $title .= $LANG['common'][66];
                              break;

                           case "view" :
                              $title .= $LANG['search'][11];
                              break;

                           default :
                              $title .= $searchopt[$itemtype][$p['field'][$key]]["name"];
                        }
                        $title .= " = ".$p['contains'][$key];
                     }
                  }
               }
               if ($_SESSION["glpisearchcount2"][$itemtype]>0 && count($p['contains2'])>0) {
                  for ($key=0 ; $key<$_SESSION["glpisearchcount2"][$itemtype] ; $key++) {
                     if (strlen($p['contains2'][$key])>0) {
                        if (isset($p['link2'][$key])) {
                           $title .= " ".$p['link2'][$key]." ";
                        }
                        $title .= $metanames[$p['itemtype2'][$key]]."/";
                        $title .= $searchopt[$p['itemtype2'][$key]][$p['field2'][$key]]["name"];
                        $title .= " = ".$p['contains2'][$key];
                     }
                  }
               }
            }

            // Display footer
            
            echo PluginMobileSearch::showFooter($output_type,$title);

            // Delete selected item
            
            if ($output_type==Search::HTML_OUTPUT) {
               if ($isadmin) {
                  openArrowMassives("massiveaction_form");
                  Dropdown::showForMassiveAction($itemtype,$p['is_deleted']);
                  closeArrowMassives();

                  // End form for delete item
                  //echo "</form>\n";
                  Html::closeForm();
               } else {
                  echo "<br>";
               }
            }
         } else {
            echo Search::showError($output_type);
         }
      } else {
         echo $DBread>error();
      }
      // Clean selection
      $_SESSION['glpimassiveactionselected']=array();

      //echo $QUERY;

      return $numrows;
   }

   static function showHeader($type,$rows,$cols,$fixed=0) {
      $out="<ul data-role='listview' data-inset='true' data-theme='c' data-dividertheme='a'>\n";
      return $out;
   }

   static function showFooter($type,$title="") {
      $out= "</ul>\n";
      return $out;
   }

   static function showHeaderItem($type,$value,&$num,$linkto="",$issort=0,$order="", $cols_limit = 0) {
      global $CFG_GLPI, $id;

      if ($cols_limit == 0) $cols_limit = $_SESSION['plugin_mobile']['cols_limit'];
      $classlist = "list".$cols_limit;

      //$linkto = "search.php?itemtype=".$_GET['itemtype']."&menu=".$_GET['menu']."&ssmenu=".$_GET['ssmenu'];

      $out="";

      //$out="<li data-role='list-divider'>";

      if (!empty($linkto)) {
         $out.= "<a href=\"$linkto\" data-role='button' data-theme='d' class='$classlist' data-transition='flip' title='$value'>";
      }
      if ($issort) {
         if ($order=="DESC") {
            $out.="<img src=\"".$CFG_GLPI["root_doc"]."/plugins/mobile/pics/puce-down.png\" alt='' title=''>";
         } else {
            $out.="<img src=\"".$CFG_GLPI["root_doc"]."/plugins/mobile/pics/puce-up.png\" alt='' title=''>";
         }
      }
      $out.= $value;
      if (!empty($linkto)) {
         $out.="</a>";
      }
      //$out.="</li>\n";

      $num++;
      return $out;
   }

   static function showNewLine($type, $odd=false, $is_deleted= false) {
      $alternate = '';
      if ($odd) $alternate = 'alternate';

      if (!isset($_SESSION['mobileSearchLastLink'])) $_SESSION['mobileSearchLastLink'] = '#';
//if (!isset($_SESSION['mobileSearchLastLink'])) $_SESSION['mobileSearchLastLink'] = $CFG_GLPI["root_doc"]."/plugins/mobile/front/item.php?itemtype=user&menu=admin&ssmenu=user&id=".$_REQUEST['id'];

      return "<li class='mobile_list $alternate'><a href='".$_SESSION['mobileSearchLastLink']."'>";
   }

   static function showEndLine($type) {
      return "</a></li>";
   }

   static function showItem($type,$value,&$num,$row,$extraparam='', $cols_limit = 0) {
      if ($cols_limit == 0) $cols_limit = $_SESSION['plugin_mobile']['cols_limit'];
      $classlist = "list".$cols_limit;

      $out="<p class='mobile_list_item $classlist' $extraparam>";
      if (strpos($value, '<a') !== false) {
         $anchorObject = getObjectAnchor($value);
         $_SESSION['mobileSearchLastLink'] = $anchorObject['href'];
         $value = $anchorObject['value'];    
  			 }
  		
      if ($value != "") {
         $out.= $value;
         $num++;
      }
      else $out.="&nbsp;";
      $out.= "</p>";
      return $out;
   }


   /**
   * Generic Function to display Items
   *
   *@param $itemtype item type
   *@param $ID ID of the SEARCH_OPTION item
   *@param $data array containing data results
   *@param $num item num in the request
   *@param $meta is a meta item ?
   *
   *@return string to print
   *
   **/
   
   //static function giveItem ($itemtype,$ID,$data,$num,$meta=0) {

   static function giveItem($itemtype, $ID, array $data, $num, $meta=0,array $addobjectparams=array()) {
      global $CFG_GLPI,$LANG,$PLUGIN_HOOKS;

      $searchopt=&Search::getOptions($itemtype);
      if (isset($CFG_GLPI["union_search_type"][$itemtype])
         && $CFG_GLPI["union_search_type"][$itemtype]==$searchopt[$ID]["table"]) {
         return PluginMobileSearch::giveItem ($data["TYPE"],$ID,$data,$num,$meta);
      }

      // Plugin can override core definition for its type
      if ($plug=isPluginItemType($itemtype)) {
         $function='plugin_'.$plug['plugin'].'_giveItem';
         if (function_exists($function)) {
            $out=$function($itemtype,$ID,$data,$num);
            if (!empty($out)) {
               return $out;
            }
         }
      }

      $NAME="ITEM_";
      if ($meta) {
         $NAME="META_";
      }
      $table=$searchopt[$ID]["table"];
      $field=$searchopt[$ID]["field"];
      $linkfield=$searchopt[$ID]["linkfield"];

      switch ($table.'.'.$field) {
         case "glpi_users_validation.name" :
         case "glpi_users.name" :
            
            // USER search case
                       
            if ($itemtype != 'User' && isset($searchopt[$ID]["forcegroupby"]) && $searchopt[$ID]["forcegroupby"]) {            
               $out="";
               $split=explode("$$$$",$data[$NAME.$num]);
               $count_display=0;
               $added=array();
               for ($k=0 ; $k<count($split) ; $k++) {
                  if ($split[$k]>0) {
                     if ($count_display) {
                        $out.= "<br>";
                     }
                     $count_display++;
                     if ($itemtype=='Ticket') {
                        $userdata = getUserNameMobile($split[$k],2);
                        $out .= $userdata['name']."&nbsp;";
                     } else {
                        $out .= getUserNameMobile($split[$k],1);
                     }
                  }
               }           
               return $out;

            } else {         	
            
               if (!empty($linkfield)) {
                  $toadd='';
                  if ($itemtype == 'Ticket' && $data[$NAME.$num."_3"]>0) {
                     $userdata = getUserNameMobile($data[$NAME.$num."_3"],2);
                     $toadd = "&nbsp;";

                  }
//Stevenes
              
		$name1 = explode("$$",$data[$NAME.$num]);
		//	return $name1['0'].' ('.$name1['1'].')';

		$link_user = $_SESSION['mobileSearchLastLink'] = "<a href=".$CFG_GLPI["root_doc"]."/plugins/mobile/front/item.php?itemtype=user&menu=admin&ssmenu=user&id=".$name1['1'].">".$name1['0']." (".$name1['1'].")</a>";
      return $link_user;
     
     // return formatUserNameMobile($data[$NAME.$num."_3"],$data[$NAME.$num],$data[$NAME.$num."_2"], $data[$NAME.$num."_4"],1).$toadd;

               }           
            }
        
            break;
         case "glpi_profiles.interface" :
            return Profile::getInterfaceName($data[$NAME.$num]);
            break;

         case "glpi_profiles.name" :
            if ($itemtype == 'User') {
               $out="";
               $split=explode("$$$$",$data[$NAME.$num]);
               $split2=explode("$$$$",$data[$NAME.$num."_2"]);
               $split3=explode("$$$$",$data[$NAME.$num."_3"]);
               $count_display=0;
               $added=array();
               for ($k=0 ; $k<count($split) ; $k++) {
                  if (strlen(trim($split[$k]))>0) {
                     $text=$split[$k]." - ".$split2[$k];
                     if ($split3[$k]) {
                        $text .= " (R)";
                     }
                     if (!in_array($text,$added)) {
                        if ($count_display) {
                           $out.= "<br>";
                        }
                        $count_display++;
                        $out .= $text;
                        $added[]=$text;
                     }
                  }
               }
               return $out;
            }
            break;

         case "glpi_entities.completename" :
            if ($itemtype == 'User') {
               $out="";
               $split=explode("$$$$",$data[$NAME.$num]);
               $split2=explode("$$$$",$data[$NAME.$num."_2"]);
               $split3=explode("$$$$",$data[$NAME.$num."_3"]);
               $added=array();
               $count_display=0;
               for ($k=0 ; $k<count($split) ; $k++) {
                  if (strlen(trim($split[$k]))>0) {
                     $text=$split[$k]." - ".$split2[$k];
                     if ($split3[$k]) {
                        $text .= " (R)";
                     }
                     if (!in_array($text,$added)) {
                        if ($count_display) {
                           $out.= "<br>";
                        }
                        $count_display++;
                        $out .= $text;
                        $added[]=$text;
                     }
                  }
               }
               return $out;
            } else if ($data[$NAME.$num."_2"]==0) {  // Set name for Root entity
               $data[$NAME.$num]=$LANG['entity'][2];
            }
            break;

         case "glpi_documenttypes.icon" :
            if (!empty($data[$NAME.$num])) {
               return "<img class='middle' alt='' src='".$CFG_GLPI["typedoc_icon_dir"]."/".
                        $data[$NAME.$num]."'>";
            }
            return "&nbsp;";

         case "glpi_documents.filename" :
            $doc = new Document();
            if ($doc->getFromDB($data['id'])) {
               return $doc->getDownloadLink();
            }
            return NOT_AVAILABLE;

         case "glpi_deviceharddrives.specificity" :
         case "glpi_devicememories.specificity" :
         case "glpi_deviceprocessors.specificity" :
            return $data[$NAME.$num];

         case "glpi_networkports.mac" :
            $out = "";
            if ($itemtype == 'Computer') {
               $displayed=array();
               if (!empty($data[$NAME.$num."_2"])) {
                  $split=explode("$$$$",$data[$NAME.$num."_2"]);
                  $count_display=0;
                  for ($k=0 ; $k<count($split) ; $k++) {
                     $lowstr=utf8_strtolower($split[$k]);
                     if (strlen(trim($split[$k]))>0 && !in_array($lowstr,$displayed)) {
                        if ($count_display) {
                           $out .= "<br>";
                        }
                        $count_display++;
                        $out .= $split[$k];
                        $displayed[]=$lowstr;
                     }
                  }
                  if (!empty($data[$NAME.$num])) {
                     $out .= "<br>";
                  }
               }
               if (!empty($data[$NAME.$num])) {
                  $split=explode("$$$$",$data[$NAME.$num]);
                  $count_display=0;
                  for ($k=0 ; $k<count($split) ; $k++){
                     $lowstr=utf8_strtolower($split[$k]);
                     if (strlen(trim($split[$k]))>0 && !in_array($lowstr,$displayed)) {
                        if ($count_display) {
                           $out .= "<br>";
                        }
                        $count_display++;
                        $out.= $split[$k];
                        $displayed[]=$lowstr;
                     }
                  }
               }
               return $out;
            }
            break;

         case "glpi_contracts.duration" :
         case "glpi_contracts.notice" :
         case "glpi_contracts.periodicity" :
         case "glpi_contracts.billing" :
            if (!empty($data[$NAME.$num])) {
               $split=explode('$$$$', $data[$NAME.$num]);
               $output = "";
               foreach ($split as $duration) {
                  $output .= (empty($output)?'':'<br>') . $duration . " " . $LANG['financial'][57];
               }
               return $output;
            }
            return "&nbsp;";

         case "glpi_contracts.renewal" :
            return Contract::getContractRenewalName($data[$NAME.$num]);

         case "glpi_infocoms.sink_time" :
            if (!empty($data[$NAME.$num])) {
               $split=explode("$$$$",$data[$NAME.$num]);
               $out='';
               foreach($split as $val) {
                  $out .= (empty($out)?'':'<br>');
                  if ($val>0) {
                     $out .= $val." ".$LANG['financial'][9];
                  }
               }
               return $out;
            }
            return "&nbsp;";

         case "glpi_infocoms.warranty_duration" :
            if (!empty($data[$NAME.$num])) {
               $split=explode("$$$$",$data[$NAME.$num]);
               $out='';
               foreach($split as $val) {
                  $out .= (empty($out)?'':'<br>');
                  if ($val>0) {
                     $out .= $val." ".$LANG['financial'][57];
                  }
                  if ($val<0) {
                     $out .= $LANG['financial'][2];
                  }
               }
               return $out;
            }
            return "&nbsp;";

         case "glpi_infocoms.sink_type" :
            $split=explode("$$$$",$data[$NAME.$num]);
            $out='';
            foreach($split as $val) {
               $out .= (empty($out)?'':'<br>').Infocom::getAmortTypeName($val);
            }
            return $out;

         case "glpi_infocoms.alert" :
            if ($data[$NAME.$num]==pow(2,Alert::END)) {
               return $LANG['financial'][80];
            }
            return "";

         case "glpi_contracts.alert" :
            switch ($data[$NAME.$num]) {
               case pow(2,Alert::END):
                  return $LANG['buttons'][32];

               case pow(2,Alert::NOTICE):
                  return $LANG['financial'][10];

               case pow(2,Alert::END) + pow(2,Alert::NOTICE):
                  return $LANG['buttons'][32]." + ".$LANG['financial'][10];
            }
            return "";

         case "glpi_tickets.count" :
            if ($data[$NAME.$num]>0 && Session::haveRight("ticket", Ticket::READALL)) {

               $options['field'][0]      = 12;
               $options['searchtype'][0] = 'equals';
               $options['contains'][0]   = 'all';
               $options['link'][0]       = 'AND';

               $options['itemtype2'][0]   = $itemtype;
               $options['field2'][0]      = self::getOptionNumber($itemtype, 'name');
               $options['searchtype2'][0] = 'equals';
               $options['contains2'][0]   = $data['id'];
               $options['link2'][0]       = 'AND';

               $options['reset']='reset';

               $out= "<a href=\"".$CFG_GLPI["root_doc"]."/front/ticket.php?".Toolbox::append_params($options)."\" data-back='false'>";
               $out .= $data[$NAME.$num];
               $out .= "</a>";
            } else {
               $out= $data[$NAME.$num];
            }
            return $out;

         case "glpi_softwarelicenses.number" :
            if ($data[$NAME.$num."_2"]==-1) {
               return $LANG['software'][4];
            }
            if (empty($data[$NAME.$num])) {
               return 0;
            }
            return $data[$NAME.$num];

         case "glpi_auth_tables.name" :
            return Auth::getMethodName($data[$NAME.$num], $data[$NAME.$num."_2"], 1,
                                    $data[$NAME.$num."_3"].$data[$NAME.$num."_4"]);

         case "glpi_reservationitems.comment" :
            if (empty($data[$NAME.$num])) {
               return "<a title='".$LANG['reservation'][22]."'
                        href='".$CFG_GLPI["root_doc"]."/front/reservationitem.form.php?id=".
                        $data["refID"]."' data-back='false'>".$LANG['common'][49].
                     "</a>";
            }
            return "<a title='".$LANG['reservation'][22]."'
                     href='".$CFG_GLPI["root_doc"]."/front/reservationitem.form.php?id=".
                     $data['refID']."' data-back='false'>".
                     resume_text($data[$NAME.$num])."</a>";

         case 'glpi_notifications.mode' :
               return Notification::getMode($data[$NAME.$num]);
         case 'glpi_notifications.event' :
               $item = NotificationTarget::getInstanceByType($data['itemtype']);
               if ($item) {
                  $events = $item->getAllEvents();
                  return $events[$data[$NAME.$num]];
               }
               return '';
         case 'glpi_crontasks.description' :
            $tmp = new CronTask();
            return $tmp->getDescription($data['id']);

         case 'glpi_crontasks.state':
            return CronTask::getStateName($data[$NAME.$num]);

         case 'glpi_crontasks.mode':
            return CronTask::getModeName($data[$NAME.$num]);
         case 'glpi_crontasks.itemtype':
            if ($plug=isPluginItemType($data[$NAME.$num])) {
               return $plug['plugin'];
            }
            return '';
         case 'glpi_tickets.status':
            $status=Ticket::getStatus($data[$NAME.$num]);
				
				// status Stevenes Donato				
				
				if($data[$NAME.$num] == "1") { $data[$NAME.$num] = "new";}
				if($data[$NAME.$num] == "2") { $data[$NAME.$num] = "assign";} 
				if($data[$NAME.$num] == "3") { $data[$NAME.$num] = "plan";} 
				if($data[$NAME.$num] == "4") { $data[$NAME.$num] = "waiting";} 
				if($data[$NAME.$num] == "5") { $data[$NAME.$num] = "solved";}  	            
				if($data[$NAME.$num] == "6") { $data[$NAME.$num] = "closed";}
            //
                       
            return "<img src=\"".$CFG_GLPI["root_doc"]."/pics/".$data[$NAME.$num].".png\"
                        alt='$status' title='$status'>&nbsp;$status";
         case 'glpi_tickets.priority':
            return Ticket::getPriorityName($data[$NAME.$num]);

         case 'glpi_tickets.urgency':
            return Ticket::getUrgencyName($data[$NAME.$num]);
         case 'glpi_tickets.impact':
            return Ticket::getImpactName($data[$NAME.$num]);

         case 'glpi_tickets.items_id':
            if (!empty($data[$NAME.$num."_2"]) && class_exists($data[$NAME.$num."_2"])) {
               $item= new $data[$NAME.$num."_2"];
               if ($item->getFromDB($data[$NAME.$num])) {
                  return $item->getLink(true);
               }
            }
            return '&nbsp;';
                    

         case 'glpi_tickets.id':
            $link=getItemTypeFormURLMobile('Ticket');
            $out  = "<a id='ticket".$data[$NAME.$num."_2"]."' href=\"".$link;
            $out .= (strstr($link,'?') ?'&amp;' :  '?');
            $out .= 'id='.$data[$NAME.$num];
            //$out .= 'id='.$data[$NAME.$num."_2"];            

            $out .= "\" data-back='false'>".$data[$NAME.$num];
            if ($_SESSION["glpiis_ids_visible"] || empty($data[$NAME.$num])) {
               //$out .= " (".$data[$NAME.$num."_2"].")";
               $out .= $data[$NAME.$num."_2"];
            }
            $out .= "</a>";

            return $out;
            
         case 'glpi_ticketvalidations.status':
         case "glpi_tickets.global_validation" :

            $split=explode("$$$$",$data[$NAME.$num]);
            $out='';
            foreach($split as $val) {
               $status=TicketValidation::getStatus($val);
               $bgcolor=TicketValidation::getStatusColor($val);
               $out .= (empty($out)?'':'<br>')."<div style=\"background-color:".$bgcolor.";\">".$status.'</div>';
            }
            return $out;

         case 'glpi_notimportedemails.reason':
            return NotImportedEmail::getReason($data[$NAME.$num]);
         case 'glpi_notimportedemails.messageid':
            $clean=array('<'=>'','>'=>'');
            return strtr($data[$NAME.$num],$clean);
      }


      //// Default case

      // Link with plugin tables : need to know left join structure
      if (preg_match("/^glpi_plugin_([a-z0-9]+)/", $table.'.'.$field, $matches)) {
         if (count($matches)==2) {
            $plug=$matches[1];
            $function='plugin_'.$plug.'_giveItem';
            if (function_exists($function)) {
               $out=$function($itemtype,$ID,$data,$num);
               if (!empty($out)) {
                  return $out;
               }
            }
         }
      }
      $unit='';
      if (isset($searchopt[$ID]['unit'])) {
         $unit=$searchopt[$ID]['unit'];
      }

      // Preformat items
      
      if (isset($searchopt[$ID]["datatype"])) {
         switch ($searchopt[$ID]["datatype"]) {
            case "itemlink" :
               if (!empty($data[$NAME.$num."_2"])) {
                  if (isset($searchopt[$ID]["itemlink_type"])) {
                     $link=getItemTypeFormURLMobile($searchopt[$ID]["itemlink_type"]);
                  } else {
                     $link=getItemTypeFormURLMobile($itemtype);
                  }
                  $out  = "<a id='".$itemtype."_".$data[$NAME.$num."_2"]."' href=\"".$link;
                  $out .= (strstr($link,'?') ?'&amp;' :  '?');
                  $out .= 'id='.$data[$NAME.$num."_2"]."\" data-back='false'>";
                  $out .= $data[$NAME.$num].$unit;
                  if ($_SESSION["glpiis_ids_visible"] || empty($data[$NAME.$num])) {
                     $out .= " (".$data[$NAME.$num."_2"].")";
                  }
                  $out .= "</a>";
                  return $out;
               } else if (isset($searchopt[$ID]["itemlink_type"])) {
                  $out="";
                  $split=explode("$$$$",$data[$NAME.$num]);
                  $count_display=0;

                  $separate='<br>';
                  if (isset($searchopt[$ID]['splititems']) && $searchopt[$ID]['splititems']) {
                     $separate='<hr>';
                  }


                  for ($k=0 ; $k<count($split) ; $k++) {
                     if (strlen(trim($split[$k]))>0) {
                        $split2=explode("$$",$split[$k]);
                        if (isset($split2[1]) && $split2[1]>0) {
                           if ($count_display) {
                              $out .= $separate;
                           }
                           $count_display++;
                           $page=getItemTypeFormURLMobile($searchopt[$ID]["itemlink_type"]);
                           $page .= (strpos($page,'?') ? '&id' : '?id');
                           $out .= "<a id='".$searchopt[$ID]["itemlink_type"]."_".$split2[1]."'
                                       href='$page=".$split2[1]."' data-back='false'>";
                           $out .= $split2[0].$unit;
                           if ($_SESSION["glpiis_ids_visible"] || empty($split2[0])) {
                              $out .= " (".$split2[1].")";
                           }
                           $out .= "</a>";
                        }
                     }
                  }
                  return $out;
               }
               break;

            case "text" :
               $separate='<br>';
               if (isset($searchopt[$ID]['splititems']) && $searchopt[$ID]['splititems']) {
                  $separate='<hr>';
               }
               return str_replace('$$$$',$separate,nl2br($data[$NAME.$num]));

            case "date" :
               $split=explode("$$$$",$data[$NAME.$num]);
               $out='';
               foreach($split as $val) {
                  $out .= (empty($out)?'':'<br>').convDate($val);
               }
               return $out;

            case "datetime" :
               $split=explode("$$$$",$data[$NAME.$num]);
               $out='';
               foreach($split as $val) {
                  $out .= (empty($out)?'':'<br>').Html::convDateTime($val);
               }
               return $out;

            case "timestamp" :
               return timestampToString($data[$NAME.$num]);

            case "realtime" :
               return Ticket::getRealtime($data[$NAME.$num]);

            case "date_delay" :
               $split = explode('$$$$',$data[$NAME.$num]);
               $out='';

               foreach($split as $val) {
                  if (strpos($val,',')) {
                     list($dat,$dur)=explode(',',$val);
                     if (!empty($dat)) {
                        $out .= (empty($out)?'':'<br>').getWarrantyExpir($dat,$dur);
                     }
                  }
               }
               return (empty($out) ? "&nbsp;" : $out);

            case "email" :
// Stevenes
//               $email=trim($data[$NAME.$num]);
  					$email = explode('$$',$data[$NAME.$num]);             
               if (!empty($email['0'])) {
                  return $email['0'];
                  //return "<a href='mailto:$email'>$email</a>";
               }
               return "&nbsp;";

            case "weblink" :
               $orig_link=trim($data[$NAME.$num]);
               if (!empty($orig_link)) {
                  // strip begin of link
                  $link=preg_replace('/https?:\/\/(www[^\.]*\.)?/','',$orig_link);
                  $link=preg_replace('/\/$/','',$link);
                  if (utf8_strlen($link)>30) {
                     $link=Toolbox::substr($link,0,30)."...";
                  }
                  //return "<a href=\"".formatOutputWebLink($orig_link)."\" target='_blank'>$link</a>";
                  return $orig_link;
               }
               return "&nbsp;";

            case "number" :
               if (isset($searchopt[$ID]['forcegroupby'])
                  && $searchopt[$ID]['forcegroupby']) {
                  $out="";
                  $split=explode("$$$$",$data[$NAME.$num]);
                  $count_display=0;
                  for ($k=0 ; $k<count($split) ; $k++) {
                     if (strlen(trim($split[$k]))>0) {
                        if ($count_display) {
                           $out.= "<br>";
                        }
                        $count_display++;
                        $out .= str_replace(' ','&nbsp;',Html::formatNumber($split[$k],false,0)).$unit;
                     }
                  }
                  return $out;
               }
               return str_replace(' ','&nbsp;',Html::formatNumber($data[$NAME.$num],false,0)).$unit;

            case "decimal" :
               if (isset($searchopt[$ID]['forcegroupby'])
                  && $searchopt[$ID]['forcegroupby']) {
                  $out="";
                  $split=explode("$$$$",$data[$NAME.$num]);
                  $count_display=0;
                  for ($k=0 ; $k<count($split) ; $k++) {
                     if (strlen(trim($split[$k]))>0) {
                        if ($count_display) {
                           $out.= "<br>";
                        }
                        $count_display++;
                        $out .= str_replace(' ','&nbsp;',Html::formatNumber($split[$k])).$unit;
                     }
                  }
                  return $out;
               }
               return str_replace(' ','&nbsp;',Html::formatNumber($data[$NAME.$num])).$unit;

            case "bool" :
               return Dropdown::getYesNo($data[$NAME.$num]).$unit;

            case "right":
               return Profile::getRightValue($data[$NAME.$num]);

            case "itemtypename":
               if (class_exists($data[$NAME.$num])) {
                  $obj = new $data[$NAME.$num] ();
                  return $obj->getTypeName();
               }
               else {
                  return "";
               }
            case "language":
               if (isset($CFG_GLPI['languages'][$data[$NAME.$num]])) {
                  return $CFG_GLPI['languages'][$data[$NAME.$num]][0];
               }
               else {
                  return $LANG['setup'][46];
               }
            break;
         }
      }

      // Manage items with need group by / group_concat
      if (isset($searchopt[$ID]['forcegroupby'])
         && $searchopt[$ID]['forcegroupby']) {
         $out="";
         $split=explode("$$$$",$data[$NAME.$num]);
         $count_display=0;
         $separate='<br>';
         if (isset($searchopt[$ID]['splititems']) && $searchopt[$ID]['splititems']) {
            $separate='<hr>';
         }
         for ($k=0 ; $k<count($split) ; $k++) {
            if (strlen(trim($split[$k]))>0) {
               if ($count_display) {
                  $out.= $separate;
               }
               $count_display++;
               $out .= $split[$k].$unit;
            }
         }
         return $out;
      }

      return $data[$NAME.$num].$unit;
   }

   public static function displayFooterNavBar($url = '',$numrows) {
      global $LANG, $CFG_GLPI;

      if ($url != '') $url = $CFG_GLPI["root_doc"]."/plugins/mobile/front/".$url;

      $step = $_SESSION['plugin_mobile']['rows_limit'];

      if (!isset($_GET['start'])) $start = 0;
      else $start = $_GET['start'];

      $get_str = $_SERVER['QUERY_STRING'];
      $get_str = substr($get_str, 0, strpos($get_str, '&start='));

      $first = 0;
      $prev = $start - $step;
      if ($prev < 0) $prev = 0;
      $next = $start + $step;
      $last = floor($numrows / $step) * $step;

      $disable_first = false;
      $disable_prev = false;
      $disable_next = false;
      $disable_end = false;

      $start_str = "start=";
      if (strlen(trim($get_str)) > 0) $start_str = "&".$start_str;

      //disable unnecessary navigation element
      
      if ($start == 0) {
      $disable_first = true;
      $disable_prev = true;
      }

      if (($numrows - $start) <= $step) {
      $disable_next = true;
      $disable_end = true;
      }

      //display footer navigation bar
      
      echo "<div data-role='footer' data-position='fixed' data-theme='d'>";

      // display navigation position      
      echo "<span id='nav_position'>"
        . $LANG['plugin_mobile']['common'][0] ." "
        . ($_GET['start']+1) ." "
        . $LANG['plugin_mobile']['common'][1] ." "
        . ($_GET['start']+$step) ." "
        . $LANG['plugin_mobile']['common'][2] ." "
        . $numrows
        . "</span>";
      echo "<div data-role='navbar'>";
      echo "<ul>";

         echo "<li><a href='".$CFG_GLPI["root_doc"]."/plugins/mobile/front/searchbox.php?itemtype="         
               .$_GET['itemtype']."&menu=".$_GET['menu']."&ssmenu=".$_GET['ssmenu']."' data-icon='search' data-rel='dialog'>"
               .$LANG['buttons'][0]."</a></li>";

         echo "<li><a ";
         if (!$disable_first) echo "href='".$url."&".$get_str.$start_str.$first."' rel='external'";
         else echo "class='ui-disabled'";
         echo " data-icon='back'>".$LANG['buttons'][33]."</a></li>";

         echo "<li><a ";
         if (!$disable_prev) echo "href='".$url."&".$get_str.$start_str.$prev."' rel='external'";
         else echo "class='ui-disabled'";
         echo " data-icon='arrow-l'>".$LANG['buttons'][12]."</a></li>";

         echo "<li><a ";
         if (!$disable_next) echo "href='".$url."&".$get_str.$start_str.$next."' rel='external'";
         else echo "class='ui-disabled'";
         echo " data-icon='arrow-r'>".$LANG['buttons'][11]."</a></li>";

         echo "<li><a ";
         if (!$disable_end) echo "href='".$url."&".$get_str.$start_str.$last."' rel='external'";
         else echo "class='ui-disabled'";
         echo " data-icon='forward'>".$LANG['buttons'][32]."</a></li>";

      echo "</ul>";
      echo "</div>";
      echo "</div>";
   }

}


