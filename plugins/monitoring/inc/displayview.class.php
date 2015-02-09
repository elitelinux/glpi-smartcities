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
   @since     2012

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringDisplayview extends CommonDBTM {

   // For visibility checks
   protected $users     = array();
   protected $groups    = array();
   protected $profiles  = array();
   protected $entities  = array();

   const HOMEPAGE         =  1024;
   const DASHBOARD        =  2048;

   static $rightname = 'plugin_monitoring_displayview';


   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return _n('View', 'Views', $nb, 'monitoring');
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
      $this->users    = PluginMonitoringDisplayview_User::getUsers($this->fields['id']);

      // Entities
//      $this->entities = Entity_Reminder::getEntities($this->fields['id']);

      // Group / entities
      $this->groups   = PluginMonitoringDisplayview_Group::getGroups($this->fields['id']);

      // Profile / entities
//      $this->profiles = Profile_Reminder::getProfiles($this->fields['id']);
   }



   /**
    * Is the login user have access to reminder based on visibility configuration
    *
    * @return boolean
   **/
   function haveVisibilityAccess() {

      // No public reminder right : no visibility check
//      if (!PluginMonitoringProfile::haveRight("config_views", 'r')) {
//         return false;
//      }

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

      // Entities
      if (count($this->entities)
          && isset($_SESSION["glpiactiveentities"]) && count($_SESSION["glpiactiveentities"])) {
         foreach ($this->entities as $key => $data) {
            foreach ($data as $entity) {
               $entities = array($entity['entities_id']);
               if ($entity['is_recursive']) {
                  $entities = getSonsOf('glpi_entities', $entity['entities_id']);
               }
               if (Session::haveAccessToOneOfEntities($entities, true)) {
                  return true;
               }
            }
         }
      }

      // Profiles
      if (count($this->profiles)
          && isset($_SESSION["glpiactiveprofile"])
          && isset($_SESSION["glpiactiveprofile"]['id'])) {
         if (isset($this->profiles[$_SESSION["glpiactiveprofile"]['id']])) {
            foreach ($this->profiles[$_SESSION["glpiactiveprofile"]['id']] as $profile) {
               // All the profile
               if ($profile['entities_id'] < 0) {
                  return true;
               }
               // Restrict to entities
               $entities = array($profile['entities_id']);
               if ($profile['is_recursive']) {
                  $entities = getSonsOf('glpi_entities',$profile['entities_id']);
               }
               if (Session::haveAccessToOneOfEntities($entities, true)) {
                  return true;
               }
            }
         }
      }

      return false;
   }



   function getSearchOptions() {
      $tab = array();

      $tab['common'] = __('Views', 'monitoring');

		$tab[1]['table'] = $this->getTable();
		$tab[1]['field'] = 'name';
		$tab[1]['linkfield'] = 'name';
		$tab[1]['name'] = __('Name');
		$tab[1]['datatype'] = 'itemlink';

		$tab[2]['table'] = $this->getTable();
		$tab[2]['field'] = 'is_frontview';
		$tab[2]['linkfield'] = 'is_frontview';
		$tab[2]['name'] = __('Display view in dashboard', 'monitoring');
		$tab[2]['datatype'] = 'bool';

      $tab[3]['table']          = $this->getTable();
      $tab[3]['field']          = 'comment';
      $tab[3]['name']           = __('Comments');
      $tab[3]['datatype']       = 'text';

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

      if ($item->getType() == 'PluginMonitoringDisplayview') {
         if ($item->getID() > 0) {
            $ong[1] = 'items';

            if ($item->canUpdate()) {
               $ong[2] = __('Targets');
            }
            $pmDisplayview_rule = new PluginMonitoringDisplayview_rule();
            $ong = $pmDisplayview_rule->addRulesTabs($item->getID(), $ong);
         }
      } else if ($item->getType() == 'Central') {
         $a_views = $this->getViews(1);
         foreach ($a_views as $views_id=>$name) {
            $this->getFromDB($views_id);
            if (Session::haveRight("plugin_monitoring_displayview", PluginMonitoringDisplayview::HOMEPAGE)
                    && $this->haveVisibilityAccess()) {
               $ong[] = "["._n('View', 'Views', 1, 'monitoring')."] ".$this->fields['name'];
            }
         }
      }
      return $ong;
   }



   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'PluginMonitoringDisplayview') {
         switch($tabnum) {

            case 1:
               $pmDisplayview_item = new PluginMonitoringDisplayview_item();
               $pmDisplayview_item->view($item->getID(), 1);
               break;

            case 2 :
               $item->showVisibility();
               break;

         }
         if ($tabnum >= 20) {
            $pmDisplayview_rule = new PluginMonitoringDisplayview_rule();
            $pmDisplayview_rule->ShowRulesTabs($item->getID(), $tabnum);
         }
      } else if ($item->getType() == 'Central') {
         if (Session::haveRight("plugin_monitoring_displayview", PluginMonitoringDisplayview::HOMEPAGE)) {
            $pmDisplayview_item = new PluginMonitoringDisplayview_item();
            $pmDisplayview = new PluginMonitoringDisplayview();
            $a_views = $pmDisplayview->getViews(1);
            foreach ($a_views as $views_id=>$name) {
               $pmDisplayview->getFromDB($views_id);
               if ($pmDisplayview->haveVisibilityAccess()) {
                  $pmDisplayview_item->view($views_id);
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
   function showForm($items_id, $options=array()) {

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

      echo "<td>".__('Display view in dashboard', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      Dropdown::showYesNo("is_frontview", $this->fields['is_frontview']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Header counter (critical/warning/ok)', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      $elements = array();
      $elements['NULL'] = Dropdown::EMPTY_VALUE;
      $elements['Businessrules'] = __('Business rules', 'monitoring');
      $elements['Componentscatalog'] = __('Components catalog', 'monitoring');
      $elements['Ressources'] = __('Resources', 'monitoring');
      Dropdown::showFromArray('counter', $elements, array('value'=>$this->fields['counter']));
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
      echo __('Width', 'monitoring')." (px) :";
      echo "</td>";
      echo "<td>";
      Dropdown::showNumber("width", array(
                'value' => $this->fields['width'],
                'min'   => 950,
                'max'   => 3000,
                'step'   => 5)
      );
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



   function getViews($central='0') {
      global $DB;

      $wcentral = '';
      if ($central == '1') {
         $wcentral = " AND `in_central`='1' ";
      }

      $a_views = array();
      $query = "SELECT * FROM `glpi_plugin_monitoring_displayviews`
                WHERE `is_active` = '1'
                  AND (`users_id`='0' OR `users_id`='".$_SESSION['glpiID']."')
                  AND `is_frontview`='1'
                  ".$wcentral."
                  ".getEntitiesRestrictRequest(" AND", 'glpi_plugin_monitoring_displayviews', "entities_id",'', true)."
                ORDER BY `users_id`, `name`";
      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetch_array($result)) {
            $a_views[$data['id']] = $data['name'];
         }
      }
      return $a_views;
   }



   /**
    * Show visibility config for a view
   **/
   function showVisibility() {
      global $DB, $CFG_GLPI;

      $ID      = $this->fields['id'];
      $canedit = $this->can($ID, CREATE);

      echo "<div class='center'>";

      $rand = mt_rand();

      $nb = count($this->users) + count($this->groups) + count($this->profiles) + count($this->entities);

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='displayviewvisibility_form$rand' id='displayviewvisibility_form$rand' ";
         echo " method='post' action='".Toolbox::getItemTypeFormURL('PluginMonitoringDisplayview')."'>";
         echo "<input type='hidden' name='pluginmonitoringdisplayviews_id' value='$ID'>";
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
                  echo "<input type='checkbox' name='item[PluginMonitoringDisplayview_User][".$data["id"]."]'
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
                  echo "<input type='checkbox' name='item[PluginMonitoringDisplayview_Group][".$data["id"]."]'
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



   function doSpecificMassiveActions($input=array()) {

      $res = array('ok'      => 0,
                   'ko'      => 0,
                   'noright' => 0);

      switch ($input['action']) {
         case "deletevisibility":
            foreach ($input['item'] as $type => $items) {
               if (in_array($type, array('PluginMonitoringDisplayview_Group',
                                         'PluginMonitoringDisplayview_User'))) {
                  $item = new $type();
                  foreach ($items as $key => $val) {
                     if ($item->can($key, PURGE)) {
                        if ($item->delete(array('id' => $key))) {
                           $res['ok']++;
                        } else {
                           $res['ko']++;
                        }
                     } else {
                        $res['noright']++;
                     }
                  }
               }
            }

            break;

         default :
            return parent::doSpecificMassiveActions($input);
      }
      return $res;
   }



   function showWidget($id) {
      return "<div id=\"updatedisplayview".$id."\"></div>";
   }



  function showWidget2($id) {
      return "<div id=\"updatedisplayview2-".$id."\"></div>";
   }



   function ajaxLoad($id) {
      global $CFG_GLPI;

      $sess_id = session_id();
      PluginMonitoringSecurity::updateSession();

      echo "<script type=\"text/javascript\">

      var elcc".$id." = Ext.get(\"updatedisplayview".$id."\");
      var mgrcc".$id." = elcc".$id.".getUpdateManager();
      mgrcc".$id.".loadScripts=true;
      mgrcc".$id.".showLoadIndicator=false;
      mgrcc".$id.".startAutoRefresh(50, \"".$CFG_GLPI["root_doc"].
              "/plugins/monitoring/ajax/updateWidgetDisplayview.php\","
              . " \"id=".$id."&sess_id=".$sess_id.
              "&glpiID=".$_SESSION['glpiID'].
              "&plugin_monitoring_securekey=".$_SESSION['plugin_monitoring_securekey'].
              "\", \"\", true);
      </script>";
   }



   function ajaxLoad2($id, $is_minemap) {
      global $CFG_GLPI;

      $sess_id = session_id();
      PluginMonitoringSecurity::updateSession();

      echo "<script type=\"text/javascript\">

      var elcc".$id." = Ext.get(\"updatedisplayview2-".$id."\");
      var mgrcc".$id." = elcc".$id.".getUpdateManager();
      mgrcc".$id.".loadScripts=true;
      mgrcc".$id.".showLoadIndicator=false;
      mgrcc".$id.".startAutoRefresh(50, \"".$CFG_GLPI["root_doc"].
              "/plugins/monitoring/ajax/updateWidgetDisplayview2.php\","
              . " \"id=".$id."&is_minemap=".$is_minemap."&sess_id=".$sess_id.
              "&glpiID=".$_SESSION['glpiID'].
              "&plugin_monitoring_securekey=".$_SESSION['plugin_monitoring_securekey'].
              "\", \"\", true);
      </script>";
   }



   /**
    * Display info of a views
    *
    * @param type $id
    */
   function showWidgetFrame($id) {

      $this->getFromDB($id);
      $data = $this->fields;

      $pmDisplayview_item = new PluginMonitoringDisplayview_item();
      $a_counter = $pmDisplayview_item->getCounterOfViews($id, array('ok'          => 0,
                                                                     'warning'     => 0,
                                                                     'critical'    => 0,
                                                                     'acknowledge' => 0));
      $nb_ressources = 0;
      $class = 'ok';
      if ($a_counter['critical'] > 0) {
         $nb_ressources = $a_counter['critical'];
         $class = 'crit';
      } else if ($a_counter['warning'] > 0) {
         $nb_ressources = $a_counter['warning'];
         $class = 'warn';
      } else {
         $nb_ressources = $a_counter['ok'];
      }
      echo '<div class="ch-item" style="background-image:url(\'../pics/picto_view.png\');
         background-repeat:no-repeat;background-position:center center;">
         <div class="ch-info-'.$class.'">
         <h1><a href="javascript:;" onclick="document.getElementById(\'updatefil\').value = \''.$id.'!\';'.
              'document.getElementById(\'updateviewid\').value = \''.$id.'\';reloadfil();reloadview();"'
              .'><span id="viewa-'.$id.'">'
              .$data['name'].'</span></a></h1>
			<p>'.$nb_ressources.'<font style="font-size: 14px;"> / '.array_sum($a_counter).'</font></p>
         </div>
		</div>';

      echo "<script>
         fittext('viewa-".$id."');
      </script>";
   }



   /**
    * Display info of device
    *
    * @global type $DB
    * @param type $id
    */
   function showWidget2Frame($id, $is_minemap=FALSE) {
      global $DB, $CFG_GLPI;

      $pmDisplayview_item = new PluginMonitoringDisplayview_item();
      $pmDisplayview_item->getFromDB($id);

      $itemtype = $pmDisplayview_item->fields['extra_infos'];
      $item = new $itemtype();
      $item->getFromDB($pmDisplayview_item->fields['items_id']);

      $critical = 0;
      $warning = 0;
      $ok = 0;
      $acknowledge = 0;

      $query = "SELECT * FROM `glpi_plugin_monitoring_services`"
              . " LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`"
              . "    ON `plugin_monitoring_componentscatalogs_hosts_id`="
              . " `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`"
              . " WHERE `items_id`='".$item->fields['id']."'"
              . " AND `itemtype`='".$itemtype."'"
              . " AND `glpi_plugin_monitoring_services`.`id` IS NOT NULL"
              . " ORDER BY `glpi_plugin_monitoring_services`.`name`";

      $result = $DB->query($query);
      $services  = array();
      $resources = array();
      $i = 0;
      while ($data=$DB->fetch_array($result)) {
         $ret = PluginMonitoringHost::getState($data['state'],
                                                  $data['state_type'],
                                                  '',
                                                  $data['is_acknowledged']);
         if (strstr($ret, '_soft')) {
            $ok++;
            $resources[$data['id']]['state'] = 'OK';
         } else if ($ret == 'red') {
            $critical++;
            $resources[$data['id']]['state'] = 'CRITICAL';
         } else if ($ret == 'redblue') {
            $acknowledge++;
            $resources[$data['id']]['state'] = 'ACKNOWLEDGE';
         } else if ($ret == 'orange'
                 || $ret == 'yellow') {
            $warning++;
            $resources[$data['id']]['state'] = 'WARNING';
         } else {
            $ok++;
            $resources[$data['id']]['state'] = 'OK';
         }
         $services[$i++] = $data['id'];
         $resources[$data['id']]['last_check'] = $data['last_check'];
         $resources[$data['id']]['event'] = $data['event'];
         $resources[$data['id']]['name'] = $data['name'];
         $resources[$data['id']]['plugin_monitoring_components_id'] = $data['plugin_monitoring_components_id'];

      }

      $class = 'ok';
      if ($critical > 0) {
         $nb_ressources = $critical;
         $class = 'crit';
      } else if ($warning > 0) {
         $nb_ressources = $warning;
         $class = 'warn';
      } else {
         $nb_ressources = $ok;
      }

      echo '<div class="ch-item">
         <div class="ch-info-'.$class.'">
			<h1><a href="';
      if ($item->can($item->getID(), READ)) {
         echo $item->getFormURL().'?id='.$item->getID().'&forcetab=PluginMonitoringHost$0';
      } else {
         echo $CFG_GLPI['root_doc']."/plugins/monitoring/front/displayhost.php?itemtype=".$itemtype
                 ."&items_id=".$item->getID();
      }
         echo '">'
              . '<span id="devicea-'.$id.'">'.$item->getName().'</span></a></h1>
			<p><a>'.$nb_ressources.'</a><font style="font-size: 14px;"> / '.($ok + $warning + $critical + $acknowledge).'</font></p>
         </div>
		</div>';

      echo "<script>
         fittext('devicea-".$id."');
      </script>";

      echo "<div class='minemapdiv' align='center'>"
      ."<a onclick='Ext.get(\"minemapdisplayview2-".$id."\").toggle()'>"
              ."Minemap</a></div>";
      if (!$is_minemap) {
         echo '<div class="minemapdiv" id="minemapdisplayview2-'.$id.'" style="display: none; z-index: 1500">';
      } else {
         echo '<div class="minemapdiv" id="minemapdisplayview2-'.$id.'">';
      }
      echo '<table class="tab_cadrehov" >';

      // Get services list ...
      echo '<div class="minemapdiv">';
      echo '<table class="tab_cadrehov">';

      // Header with services name and link to services list ...
      echo '<tr class="tab_bg_2">';
      echo '<th colspan="2">';
      echo __('Services', 'monitoring');
      echo '</th>';
      echo '</tr>';

      // Content with host/service status and link to services list ...
      foreach ($services as $services_id) {
         $field_id = 20;
         if ($itemtype == 'Printer') {
            $field_id = 21;
         } else if ($itemtype == 'NetworkEquipment') {
            $field_id = 22;
         }

         $link = $CFG_GLPI['root_doc'].
            "/plugins/monitoring/front/service.php?hidesearch=1"
//                 . "&reset=reset"
                 . "&criteria[0][field]=".$field_id.""
                 . "&criteria[0][searchtype]=equals"
                 . "&criteria[0][value]=".$item->getID()

                 . "&criteria[1][link]=AND"
                 . "&criteria[1][field]=2"
                 . "&criteria[1][searchtype]=equals"
                 . "&criteria[1][value]=".$resources[$services_id]['plugin_monitoring_components_id']

                 . "&itemtype=PluginMonitoringService"
                 . "&start=0'";

         echo "<tr class='tab_bg_2'>";
         echo "<td class='left'><a href='".$link."'>".$resources[$services_id]['name']."</a></td>";
         echo '<td>';
         echo '<a href="'.$link.'" title="'.$resources[$services_id]['state'].
                 " - ".$resources[$services_id]['last_check']." - ".
                 $resources[$services_id]['event'].'">'
                 . '<div class="service'.$resources[$services_id]['state'].'"></div></a>';
         echo '</td>';
         echo '</tr>';
      }
      echo '</table>';
      echo '</div>';
   }


}

?>