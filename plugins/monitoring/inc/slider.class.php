<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2014 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Monitoring. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author
   @comment
   @copyright Copyright (c) 2011-2014 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2014

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringSlider extends CommonDBTM {


   const HOMEPAGE         =  1024;
   const DASHBOARD        =  2048;

   static $rightname = 'plugin_monitoring_slider';


   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Slider', 'monitoring');
   }


   /**
    * @since version 0.85
    *
    * @see commonDBTM::getRights()
    **/
   function getRights($interface='central') {

      $values = parent::getRights();
      $values[self::HOMEPAGE]    = __('See in homepage', 'monitoring');
      $values[self::DASHBOARD]   = __('See in dashboard', 'monitoring');

      return $values;
   }



   function post_getFromDB() {

      // Users
      $this->users    = PluginMonitoringSlider_User::getUsers($this->fields['id']);

      // Entities
//      $this->entities = Entity_Reminder::getEntities($this->fields['id']);

      // Group / entities
      $this->groups   = PluginMonitoringSlider_Group::getGroups($this->fields['id']);

      // Profile / entities
//      $this->profiles = Profile_Reminder::getProfiles($this->fields['id']);
   }



   /**
    * Is the login user have access to reminder based on visibility configuration
    *
    * @return boolean
   **/
   function haveVisibilityAccess() {

      // Author
      if ($this->fields['users_id'] == Session::getLoginUserID()) {
         return true;
      }

      // Users
      if (isset($this->users[Session::getLoginUserID()])) {
         return true;
      }

      // Groups
      if (count($this->groups)
          && isset($_SESSION["glpigroups"]) && count($_SESSION["glpigroups"])) {
         foreach ($this->groups as $key => $data) {
            foreach ($data as $group) {
               if (in_array($group['groups_id'], $_SESSION["glpigroups"])) {
                  // All the group
                  if ($group['entities_id'] < 0) {
                     return true;
                  }
                  // Restrict to entities
                  $entities = array($group['entities_id']);
                  if ($group['is_recursive']) {
                     $entities = getSonsOf('glpi_entities', $group['entities_id']);
                  }
                  if (Session::haveAccessToOneOfEntities($entities, true)) {
                     return true;
                  }
               }
            }
         }
      }

//      // Entities
//      if (count($this->entities)
//          && isset($_SESSION["glpiactiveentities"]) && count($_SESSION["glpiactiveentities"])) {
//         foreach ($this->entities as $key => $data) {
//            foreach ($data as $entity) {
//               $entities = array($entity['entities_id']);
//               if ($entity['is_recursive']) {
//                  $entities = getSonsOf('glpi_entities', $entity['entities_id']);
//               }
//               if (Session::haveAccessToOneOfEntities($entities, true)) {
//                  return true;
//               }
//            }
//         }
//      }

//      // Profiles
//      if (count($this->profiles)
//          && isset($_SESSION["glpiactiveprofile"])
//          && isset($_SESSION["glpiactiveprofile"]['id'])) {
//         if (isset($this->profiles[$_SESSION["glpiactiveprofile"]['id']])) {
//            foreach ($this->profiles[$_SESSION["glpiactiveprofile"]['id']] as $profile) {
//               // All the profile
//               if ($profile['entities_id'] < 0) {
//                  return true;
//               }
//               // Restrict to entities
//               $entities = array($profile['entities_id']);
//               if ($profile['is_recursive']) {
//                  $entities = getSonsOf('glpi_entities',$profile['entities_id']);
//               }
//               if (Session::haveAccessToOneOfEntities($entities, true)) {
//                  return true;
//               }
//            }
//         }
//      }

      return false;
   }



   function getSearchOptions() {

      $tab = array();

      $tab['common'] = __('Slider', 'monitoring');

		$tab[1]['table'] = $this->getTable();
		$tab[1]['field'] = 'name';
		$tab[1]['linkfield'] = 'name';
		$tab[1]['name'] = __('Name');
		$tab[1]['datatype'] = 'itemlink';

      return $tab;
   }





   function defineTabs($options=array()){
      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(__CLASS__, $ong, $options);
      return $ong;
   }



   /**
    * Display tab
    *
    * @param CommonGLPI $item
    * @param integer $withtemplate
    *
    * @return varchar name of the tab(s) to display
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      $ong = array();

      if ($item->getType() == 'PluginMonitoringSlider') {
         if ($item->getID() > 0) {
            $ong[1] = 'items';

            if ($item->canUpdate()) {
               $ong[2] = __('Targets');
            }
         }
      } else if ($item->getType() == 'Central') {
         $a_sliders = $this->getSliders(1);
         foreach ($a_sliders as $sliders_id=>$name) {
            $this->getFromDB($sliders_id);
            if (Session::haveRight("plugin_monitoring_slider", PluginMonitoringSlider::HOMEPAGE)
                    && $this->haveVisibilityAccess()) {
               $ong[] = "[".__('Carrousel / slider', 'monitoring')."] ".$this->fields['name'];
            }
         }
      }
      return $ong;
   }



   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'PluginMonitoringSlider') {
         switch($tabnum) {

            case 1:
               $pmSlider_item = new PluginMonitoringSlider_item();
               $pmSlider_item->view($item->getID(), 1);
               break;

            case 2 :
               $item->showVisibility();
               break;

         }
      } else if ($item->getType() == 'Central') {
         if (Session::haveRight("plugin_monitoring_slider", PluginMonitoringSlider::HOMEPAGE)) {
            $pmSlider_item = new PluginMonitoringSlider_item();
            $pmSlider = new PluginMonitoringSlider();
            $a_sliders = $pmSlider->getSliders(1);
            foreach ($a_sliders as $sliders_id=>$name) {
               $pmSlider->getFromDB($sliders_id);
               if ($pmSlider->haveVisibilityAccess()) {
                  $pmSlider_item->view($sliders_id);
              }
            }
         }
      }

      return true;
   }



   /**
   * Display form for agent configuration
   *
   * @param $items_id integer ID
   * @param $options array
   *
   *@return bool true if form is ok
   *
   **/
   function showForm($items_id, $options=array(), $copy=array()) {

      $this->initForm($items_id, $options);
      if ($this->fields['id'] == 0) {
         $this->fields['width'] = 950;
         $this->fields['is_active'] = 1;
      }
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')." :</td>";
      echo "<td>";
      echo "<input type='text' name='name' value='".$this->fields["name"]."' size='30'/>";
      echo "</td>";

      echo "<td>".__('Display carrousel / slider in dashboard', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      Dropdown::showYesNo("is_frontview", $this->fields['is_frontview']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Duration (in seconds)', 'monitoring');
      echo "</td>";
      echo "<td>";
      Dropdown::showNumber('duration', array(
          'value' => $this->fields['duration'],
          'min' => 1,
          'max' => 999
      ));
      echo "</td>";

      echo "<td>";
      echo __('Display in GLPI home page', 'monitoring');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo("in_central", $this->fields['in_central']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo "</td>";
      echo "<td>";
      echo "</td>";
      echo "<td>";
      echo __('Active');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo("is_active", $this->fields['is_active']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Comments')."</td>";
      echo "<td colspan='3' class='middle'>";
      echo "<textarea cols='95' rows='3' name='comment' >".$this->fields["comment"];
      echo "</textarea>";
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }



   function slideSlider($id) {
      global $CFG_GLPI, $DB;

      echo "<script type='text/javascript'>
         function fittext(itemid) {
            document.getElementById(itemid).style.fontSize = '50px';
            var fontsize = 50;
            while(document.getElementById(itemid).offsetWidth > 120) {
               fontsize--;
               if (fontsize > 20) {
                  fontsize--;
               }
               document.getElementById(itemid).style.fontSize = fontsize + 'px';
            }
            while(document.getElementById(itemid).offsetHeight > 67) {
               fontsize--;
               document.getElementById(itemid).style.fontSize = fontsize + 'px';
            }
            if (fontsize > 30) {
               document.getElementById(itemid).style.fontSize = '30px';
            }
            if (fontsize < 7) {
               document.getElementById(itemid).style.fontSize = '7px';
            }
         }
      </script>";
      PluginMonitoringToolbox::loadLib();

      $this->getFromDB($id);
      echo '<div style="display:none"><input type="text" id="custom_date" value="'.date('m/d/Y').'"> '
              . ' <input type="text" id="custom_time" value="'.date('H:i').'"></div>';

      echo '<script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/slider.js-14/js/jssor.slider.mini.js"></script>
<script>
    jQuery(document).ready(function ($) {
        //Define an array of slideshow transition code
        var _SlideshowTransitions = [
        {$Duration:0001,$Opacity:2}
        ];
        var options = {
            $AutoPlay: true,
            $AutoPlayInterval: '.$this->fields['duration'].'000,
            $SlideshowOptions: {
                    $Class: $JssorSlideshowRunner$,
                    $Transitions: _SlideshowTransitions,
                    $TransitionsOrder: 1,
                    $ShowLink: true
        }
        };
        var jssor_slider1 = new $JssorSlider$(\'slider1_container\', options);
    });
</script>';
      echo "<table class='tab_cadre'>";

      echo "<tr>";
      echo "<td>";

      $query = "SELECT * FROM `glpi_plugin_monitoring_sliders_items`
              WHERE `plugin_monitoring_sliders_id`='".$id."'";

      $result = $DB->query($query);
      $maxWidth = 0;
      $maxHeight = 0;
      $is_minemap = 0;
      while ($data=$DB->fetch_array($result)) {
         if ($data['itemtype'] == 'PluginMonitoringServicescatalog'
                 || $data['itemtype'] == 'PluginMonitoringComponentscatalog'
                 || $data['itemtype'] == 'PluginMonitoringCustomitem_Gauge'
                 || $data['itemtype'] == 'PluginMonitoringCustomitem_Counter') {
            if ($maxWidth < 180) {
               $maxWidth = 180;
            }
            if ($maxHeight < 180) {
               $maxHeight = 180;
            }
            if ($data['is_minemap'] == 1) {
               $is_minemap = 1;
            }
         } else if ($data['itemtype'] == 'PluginMonitoringService') {
            if ($maxWidth < 475) {
               $maxWidth = 475;
            }
            if ($maxHeight < 330) {
               $maxHeight = 330;
            }
         } else if ($data['itemtype'] == "PluginMapsMap") {
            if ($maxWidth < 950) {
               $maxWidth = 950;
            }
            if ($maxHeight < 800) {
               $maxHeight = 800;
            }
         } else {
            $itemtype = $data['itemtype'];
            $item = new $itemtype();
            $item->getFromDB($data['items_id']);
            if ($maxWidth < $item->fields['width']) {
               $maxWidth = $item->fields['width'];
            }
            if (isset($item->fields['height'])) {
               if ($maxHeight < $item->fields['width']) {
                  $maxHeight = $item->fields['height'];
               }
            }
         }
      }
      if ($is_minemap) {
         $maxHeight = '1500';
      }

      $pm = new PluginMonitoringComponentscatalog();
      echo '<div id="slider1_container" style="position: relative;
top: 0px; left: 0px; width: '.$maxWidth.'px; height: '.$maxHeight.'px;">
    <!-- Slides Container -->
    <div u="slides" style="cursor: move; position: absolute; overflow: hidden;
    left: 0px; top: 0px; width: '.$maxWidth.'px; height: '.$maxHeight.'px;">';

      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $itemtype = $data['itemtype'];
         $item = new $itemtype();
         if ($itemtype == "PluginMonitoringService") {
            echo '<div>';
            echo $item->showWidget($data['items_id'], $data['extra_infos']);
            echo '</div>';
         } else if ($itemtype == "PluginMonitoringWeathermap") {
            echo '<div>';
            echo '<div id="weathermap-'.$data['items_id'].'"></div>';
            echo '</div>';
         } else if ($itemtype == 'PluginMonitoringDisplayview') {
            echo '<div>';
            $pmDisplayview_item = new PluginMonitoringDisplayview_item();
            echo $pmDisplayview_item->view($data['items_id']);
            echo '</div>';
         } else if ($itemtype == "PluginMapsMap") {
            echo '<div>';
            echo '<div id="pluginmap"></div>';
            echo '</div>';
         } else {
            echo '<div>';
            echo $item->showWidget($data['items_id']);
            echo '</div>';
         }

      }
echo '    </div>
</div>';
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $itemtype = $data['itemtype'];
         $item = new $itemtype();
         // Ajax
         if ($itemtype == "PluginMonitoringService") {
            $pmComponent = new PluginMonitoringComponent();
            $item = new $itemtype();

            $item->getFromDB($data['items_id']);
            $pmComponent->getFromDB($item->fields['plugin_monitoring_components_id']);
            $pmServicegraph = new PluginMonitoringServicegraph();
            $pmServicegraph->displayGraph($pmComponent->fields['graph_template'],
                                          "PluginMonitoringService",
                                          $data['items_id'],
                                          "0",
                                          $data['extra_infos'],
                                          "js");
         } else if($itemtype == "PluginMonitoringComponentscatalog") {
            $pmComponentscatalog = new PluginMonitoringComponentscatalog();
            $pmComponentscatalog->ajaxLoad($data['items_id'], $data['is_minemap']);
         } else if($itemtype == "PluginMonitoringServicescatalog") {
            $pmServicescatalog = new PluginMonitoringServicescatalog();
            $pmServicescatalog->ajaxLoad($data['items_id']);
         } else if($itemtype == "PluginMonitoringDisplayview") {

         } else if($itemtype == "PluginMonitoringCustomitem_Gauge") {
            $pmCustomitem_Gauge = new PluginMonitoringCustomitem_Gauge();
            $pmCustomitem_Gauge->ajaxLoad($data['items_id']);
         } else if($itemtype == "PluginMonitoringCustomitem_Counter") {
            $pmCustomitem_Counter = new PluginMonitoringCustomitem_Counter();
            $pmCustomitem_Counter->ajaxLoad($data['items_id']);
         }
         if ($itemtype == "PluginMonitoringWeathermap") {

            echo "<script type='text/javascript'>
            var mgr = new Ext.UpdateManager('weathermap-".$data['items_id']."');
            mgr.startAutoRefresh(50, \"".$CFG_GLPI["root_doc"].
                    "/plugins/monitoring/ajax/widgetWeathermap.php\","
                    . " \"id=".$data['items_id']."&extra_infos=".
                    $data['extra_infos'].
                    "&glpiID=".$_SESSION['glpiID'].
                    "\", \"\", true);
            </script>";
         }
         if ($itemtype == "PluginMapsMap") {
            echo "<script type='text/javascript'>
            var mgr = new Ext.UpdateManager('pluginmap');
            mgr.startAutoRefresh(50, \"".$CFG_GLPI["root_doc"].
                    "/plugins/monitoring/ajax/widgetPluginmap.php\","
                    . " \"extra_infos=".
                    $data['extra_infos'].
                    "&glpiID=".$_SESSION['glpiID'].
                    "\", \"\", true);
            </script>";
         }
      }
      echo "</td>";
      echo "</tr>";
      echo "</table>";
   }


   function getSliders($central='0') {
      global $DB;

      $wcentral = '';
      if ($central == '1') {
         $wcentral = " AND `in_central`='1' ";
      }

      $a_sliders = array();
      $query = "SELECT * FROM `glpi_plugin_monitoring_sliders`
                WHERE `is_active` = '1'
                  AND (`users_id`='0' OR `users_id`='".$_SESSION['glpiID']."')
                  AND `is_frontview`='1'
                  ".$wcentral."
                  ".getEntitiesRestrictRequest(" AND", 'glpi_plugin_monitoring_sliders', "entities_id",'', true)."
                ORDER BY `users_id`, `name`";
      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetch_array($result)) {
            $a_sliders[$data['id']] = $data['name'];
         }
      }
      return $a_sliders;
   }



   /**
    * Show visibility config for a slider
   **/
   function showVisibility() {
      global $DB, $CFG_GLPI;

      $ID      = $this->fields['id'];
      $canedit = $this->can($ID, UPDATE);

      echo "<div class='center'>";

      $rand = mt_rand();

      $nb = count($this->users) + count($this->groups);

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='slidervisibility_form$rand' id='slidervisibility_form$rand' ";
         echo " method='post' action='".Toolbox::getItemTypeFormURL('PluginMonitoringSlider')."'>";
         echo "<input type='hidden' name='pluginmonitoringsliders_id' value='$ID'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><th colspan='4'>".__('Add a target')."</tr>";
         echo "<tr class='tab_bg_2'><td width='100px'>";

         //$types = array('Entity', 'Group', 'Profile', 'User');
         $types = array('Group', 'User');

         $addrand = Dropdown::showItemTypes('_type', $types);
         $params  = array('type'  => '__VALUE__',
                          'right' => 'all');

         Ajax::updateItemOnSelectEvent("dropdown__type".$addrand,"visibility$rand",
                                       $CFG_GLPI["root_doc"]."/ajax/visibility.php", $params);

         echo "</td>";
         echo "<td><span id='visibility$rand'></span>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }
      echo "<div class='spaced'>";
      if ($canedit && $nb) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $paramsma = array('num_displayed'    => $nb,
                           'specific_actions' => array('deletevisibility'
                                                         => _x('button', 'Delete permanently')) );

         if ($this->fields['users_id'] != Session::getLoginUserID()) {
            $paramsma['confirm'] = __('Caution! You are not the author of this element. Delete targets can result in loss of access to that element.');
         }
         Html::showMassiveActions(__CLASS__, $paramsma);
      }
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr>";
      if ($canedit && $nb) {
         echo "<th width='10'>";
         echo Html::checkAllAsCheckbox('mass'.__CLASS__.$rand);
         echo "</th>";
      }
      echo "<th>".__('Type')."</th>";
      echo "<th>"._n('Recipient', 'Recipients', 2)."</th>";
      echo "</tr>";

      // Users
      if (count($this->users)) {
         foreach ($this->users as $key => $val) {
            foreach ($val as $data) {
               echo "<tr class='tab_bg_2'>";
               if ($canedit) {
                  echo "<td>";
                  echo "<input type='checkbox' name='item[PluginMonitoringSlider_User][".$data["id"]."]'
                          value='1' >";
                  echo "</td>";
               }
               echo "<td>".__('User')."</td>";
               echo "<td>".getUserName($data['users_id'])."</td>";
               echo "</tr>";
            }
         }
      }

      // Groups
      if (count($this->groups)) {
         foreach ($this->groups as $key => $val) {
            foreach ($val as $data) {
               echo "<tr class='tab_bg_2'>";
               if ($canedit) {
                  echo "<td>";
                  echo "<input type='checkbox' name='item[PluginMonitoringSlider_Group][".$data["id"]."]'
                         value='1'>";
                  echo "</td>";
               }
               echo "<td>".__('Group')."</td>";

               $names    = Dropdown::getDropdownName('glpi_groups', $data['groups_id'],1);
               $entname = sprintf(__('%1$s %2$s'), $names["name"],
                                   Html::showToolTip($names["comment"], array('display' => false)));
               if ($data['entities_id'] >= 0) {
                  $entname = sprintf(__('%1$s / %2$s'), $entname,
                                     Dropdown::getDropdownName('glpi_entities',
                                                               $data['entities_id']));
                  if ($data['is_recursive']) {
                     //TRANS: R for Recursive
                     sprintf(__('%1$s %2$s'), $entname,
                             "<span class='b'>(".__('R').")</span>");
                  }
               }
               echo "<td>".$entname."</td>";
               echo "</tr>";
            }
         }
      }
//
//      // Entity
//      if (count($this->entities)) {
//         foreach ($this->entities as $key => $val) {
//            foreach ($val as $data) {
//               echo "<tr class='tab_bg_2'>";
//               if ($canedit) {
//                  echo "<td>";
//                  echo "<input type='checkbox' name='item[Entity_Reminder][".$data["id"]."]'
//                          value='1'>";
//                  echo "</td>";
//               }
//               echo "<td>".__('Entity')."</td>";
//               $names   = Dropdown::getDropdownName('glpi_entities', $data['entities_id'],1);
//               $tooltip = Html::showToolTip($names["comment"], array('display' => false));
//               $entname = sprintf(__('%1$s %2$s'), $names["name"], $tooltip);
//               if ($data['is_recursive']) {
//                  $entname = sprintf(__('%1$s %2$s'), $entname,
//                                     "<span class='b'>(".__('R').")</span>");
//               }
//               echo "<td>".$entname."</td>";
//               echo "</tr>";
//            }
//         }
//      }
//
//      // Profiles
//      if (count($this->profiles)) {
//         foreach ($this->profiles as $key => $val) {
//            foreach ($val as $data) {
//               echo "<tr class='tab_bg_2'>";
//               if ($canedit) {
//                  echo "<td>";
//                  echo "<input type='checkbox' name='item[Profile_Reminder][".$data["id"]."]'
//                         value='1'>";
//                  echo "</td>";
//               }
//               echo "<td>"._n('Profile', 'Profiles', 1)."</td>";
//
//               $names   = Dropdown::getDropdownName('glpi_profiles',$data['profiles_id'],1);
//               $tooltip = Html::showToolTip($names["comment"], array('display' => false));
//               $entname = sprintf(__('%1$s %2$s'), $names["name"], $entname);
//               if ($data['entities_id'] >= 0) {
//                  $entname = sprintf(__('%1$s / %2$s'), $entname,
//                                     Dropdown::getDropdownName('glpi_entities',
//                                                               $data['entities_id']));
//                  if ($data['is_recursive']) {
//                     $entname = sprintf(__('%1$s %2$s'), $entname,
//                                        "<span class='b'>(".__('R').")</span>");
//                  }
//               }
//               echo "<td>".$entname."</td>";
//               echo "</tr>";
//            }
//         }
//      }

      echo "</table>";
      if ($canedit && $nb) {
         $paramsma['ontop'] =false;
         Html::showMassiveActions(__CLASS__, $paramsma);
         Html::closeForm();
      }

      echo "</div>";
      // Add items

      return true;
   }

}

?>