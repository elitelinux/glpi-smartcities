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
   @since     2011

   ------------------------------------------------------------------------
 */

function plugin_monitoring_giveItem($type,$id,$data,$num) {

//   $searchopt = &Search::getOptions($type);
//   $table = $searchopt[$id]["table"];
//   $field = $searchopt[$id]["field"];
//
//   switch ($table.'.'.$field) {
//
//   }

   return "";
}



function plugin_monitoring_getAddSearchOptions($itemtype) {

   $sopt = array();

   if ($itemtype == 'Computer') {
      $sopt[9100]['table']          = 'glpi_plugin_monitoring_computers_deviceprocessors';
      $sopt[9100]['field']          = 'count';
      $sopt[9100]['forcegroupby']   = true;
      $sopt[9100]['usehaving']      = true;
      $sopt[9100]['datatype']       = 'number';
      $sopt[9100]['width']          = 64;
      $sopt[9100]['name']           = __('Processor number', 'monitoring');
      $sopt[9100]['massiveaction']  = false;
      $sopt[9100]['joinparams']     = array('jointype' => 'child');
   }

   if ($itemtype == 'Entity') {
      // Shinken tag for the entity
      $sopt[9101]['table']          ='glpi_plugin_monitoring_entities';
      $sopt[9101]['field']          ='tag';
      $sopt[9100]['datatype']       = 'number';
      $sopt[9101]['name']           =__('Entity tag', 'monitoring');
      $sopt[9101]['joinparams']     = array('jointype' => 'child');
      $sopt[9101]['massiveaction']  = false;
   }

   if ($itemtype == 'User') {
      // Contact template for user
      $sopt[9102]['table']          ='glpi_plugin_monitoring_contacttemplates';
      $sopt[9102]['field']          ='name';
      $sopt[9102]['datatype']       = 'itemtype';
      $sopt[9102]['name']           =__('User template', 'monitoring');
      $sopt[9102]['joinparams']    = array('beforejoin'
                                         => array('table'      => 'glpi_plugin_monitoring_contacts',
                                                  'joinparams' => array('jointype' => 'child')));

      $sopt[9102]['massiveaction']  = false;
   }

   return $sopt;
}



/* Cron */
function cron_plugin_monitoring() {
   return 1;
}



function plugin_monitoring_install() {

   require_once GLPI_ROOT . "/plugins/monitoring/install/update.php";
   $version_detected = pluginMonitoringGetCurrentVersion(PLUGIN_MONITORING_VERSION);

   $_SESSION['plugin_monitoring_installation'] = 1;
   if ((isset($version_detected))
	   AND ($version_detected != PLUGIN_MONITORING_VERSION)
	   AND $version_detected != '0') {
      pluginMonitoringUpdate($version_detected);
   } else if ((isset($version_detected))
	   AND ($version_detected == PLUGIN_MONITORING_VERSION)) {
      // Yet at right version
   } else {
      include (GLPI_ROOT . "/plugins/monitoring/install/install.php");
      pluginMonitoringInstall(PLUGIN_MONITORING_VERSION);
   }
   unset($_SESSION['plugin_monitoring_installation']);
   return true;
}

// Uninstall process for plugin : need to return true if succeeded
function plugin_monitoring_uninstall() {
   include (GLPI_ROOT . "/plugins/monitoring/install/install.php");
   pluginMonitoringUninstall();
   return true;
}




function plugin_headings_monitoring_status($item) {

   echo "<br/>Http :<br/>";

   $pmHostevent = new PluginMonitoringHostevent();
   $pmHostevent->showForm($item);

}



function plugin_headings_monitoring_dashboadservicecatalog($item) {
   $pmServicescatalog   = new PluginMonitoringServicescatalog();
   $pmDisplay           = new PluginMonitoringDisplay();

   $pmDisplay->showCounters("Businessrules");
   $pmServicescatalog->showChecks();
}




function plugin_headings_monitoring_tasks($item, $itemtype='', $items_id=0) {

}



function plugin_headings_monitoring($item, $withtemplate=0) {

}



function plugin_monitoring_MassiveActionsFieldsDisplay($options=array()) {

   return false;
}



function plugin_monitoring_MassiveActions($type) {

   switch ($type) {

      case "Computer":
	 return array (
	    "plugin_monitoring_activatehosts" => __('Add these hosts to monitoring', 'monitoring')
	 );
	 break;

      case "PluginMonitoringComponentscatalog":
	 return array (
	    "plugin_monitoring_playrule_componentscatalog" => __('Force play rules', 'monitoring')
	 );
	 break;

      case "PluginMonitoringDisplayview":
	 return array (
	    "plugin_monitoring_playrule_displayview" => __('Force play rules', 'monitoring')
	 );
	 break;

   }

   return array ();
}



function plugin_monitoring_MassiveActionsDisplay($options=array()) {
   global $CFG_GLPI;

   switch ($options['itemtype']) {
      case "Computer":
	 switch ($options['action']) {
	    case "plugin_monitoring_activatehosts" :
	       $pmHost = new PluginMonitoringHost();
	       $a_list = $pmHost->find("`is_template`='1'");
	       $a_elements = array();
	       foreach ($a_list as $data) {
		  $a_elements[$data['id']] = $data['template_name'];
	       }
	       $rand = Dropdown::showFromArray("template_id", $a_elements);
	       echo "<img alt='' title=\"".__('add')."\" src='".$CFG_GLPI["root_doc"].
		     "/pics/add_dropdown.png' style='cursor:pointer; margin-left:2px;'
		     onClick=\"var w = window.open('".$pmHost->getFormURL()."?withtemplate=1&popup=1&amp;rand=".
		     $rand."' ,'glpipopup', 'height=400, ".
		     "width=1000, top=100, left=100, scrollbars=yes' );w.focus();\">";
	       echo "<input name='add' value='Post' class='submit' type='submit'>";
	       break;
	 }
	 break;

      case "PluginMonitoringComponentscatalog":
	 switch ($options['action']) {

	    case "plugin_monitoring_playrule_componentscatalog":
	       echo "<input name='add' value='Post' class='submit' type='submit'>";
	       break;

	 }
	 break;

      case "PluginMonitoringDisplayview":
	 switch ($options['action']) {

	    case "plugin_monitoring_playrule_displayview":
	       echo "<input name='add' value='Post' class='submit' type='submit'>";
	       break;

	 }
	 break;
   }

   return "";
}



function plugin_monitoring_MassiveActionsProcess($data) {

   switch ($data['action']) {
      case "plugin_monitoring_activatehosts" :
	 if ($data['itemtype'] == "Computer") {
	    $pmHost = new PluginMonitoringHost();
	    foreach ($data['item'] as $key => $val) {
	       if ($val == '1') {
		  $pmHost->massiveactionAddHost($data['itemtype'], $key, $data['template_id']);
	       }
	    }
	 }
	 break;

      case 'plugin_monitoring_playrule_componentscatalog':
	 $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
	 foreach ($data['item'] as $key => $val) {
	    $a_rules = $pmComponentscatalog_rule->find("`plugin_monitoring_componentscalalog_id`='".$key."'");
	    foreach ($a_rules as $data) {
	       $pmComponentscatalog_rule->getFromDB($data['id']);
	       PluginMonitoringComponentscatalog_rule::getItemsDynamicly($pmComponentscatalog_rule);
	    }
	 }
	 break;

      case 'plugin_monitoring_playrule_displayview':
	 $pmDisplayview_rule = new PluginMonitoringDisplayview_rule();
	 foreach ($data['item'] as $key => $val) {
	    $a_rules = $pmDisplayview_rule->find("`plugin_monitoring_displayviews_id`='".$key."'");
	    foreach ($a_rules as $data) {
	       $pmDisplayview_rule->getFromDB($data['id']);
	       PluginMonitoringDisplayview_rule::getItemsDynamicly($pmDisplayview_rule);
	    }
	 }
	 break;
   }
   return TRUE;
}


function plugin_monitoring_addSelect($type,$id,$num) {

   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$id]["table"];
   $field = $searchopt[$id]["field"];

//   echo $table.".".$field."<br>";
   if ($type == 'Computer') {

      if ($table.".".$field == "glpi_plugin_monitoring_computers_deviceprocessors.count") {
         return " COUNT(DISTINCT `processormonit`.`id`) AS ITEM_$num,";
      }
   } else if ($type == 'PluginMonitoringService') {
      if ($table.".".$field == 'glpi_computers.name') {
         return " CONCAT_WS('', `glpi_computers`.`name`, `glpi_printers`.`name`, `glpi_networkequipments`.`name`) AS ITEM_$num,";
      }
   } else if ($type == 'PluginMonitoringHost') {
      if ($table.".".$field == 'glpi_computers.name') {
         return " CONCAT_WS('', `glpi_computers`.`name`, `glpi_printers`.`name`, `glpi_networkequipments`.`name`) AS ITEM_$num,";
      }
   }

   return "";
}


function plugin_monitoring_forceGroupBy($type) {
    return false;
}


function plugin_monitoring_addLeftJoin($itemtype,$ref_table,$new_table,$linkfield,&$already_link_tables) {

   // Toolbox::logInFile("pm", "join $itemtype $ref_table $new_table $linkfield\n");
   switch ($itemtype) {

      case 'PluginMonitoringNetworkport':
         $already_link_tables_tmp = $already_link_tables;
         array_pop($already_link_tables_tmp);

         $leftjoin_networkequipments = 1;
         if (in_array('glpi_states', $already_link_tables_tmp)
            OR in_array('glpi_networkequipments', $already_link_tables_tmp)) {
            $leftjoin_networkequipments = 0;
         }
         switch ($new_table.".".$linkfield) {
            case "glpi_networkequipments.networkequipments_id" :
               if ($leftjoin_networkequipments == '0') {
                  return " ";
               }
               return " LEFT JOIN `glpi_networkequipments` ON (`glpi_plugin_monitoring_networkports`.`items_id` = `glpi_networkequipments`.`id` ) ";
               break;

            case "glpi_states.states_id":
               if ($leftjoin_networkequipments == '1') {
                  return " LEFT JOIN `glpi_networkequipments` ON (`glpi_plugin_monitoring_networkports`.`items_id` = `glpi_networkequipments`.`id` )
                     LEFT JOIN `glpi_states` ON (`glpi_networkequipments`.`states_id` = `glpi_states`.`id` ) ";
               } else {
                  return " LEFT JOIN `glpi_states` ON (`glpi_networkequipments`.`states_id` = `glpi_states`.`id` ) ";
               }
               break;
         }
         break;

      case 'Computer':
          if ($new_table.".".$linkfield == "glpi_plugin_monitoring_computers_deviceprocessors.plugin_monitoring_computers_deviceprocessors_id") {
             return " LEFT JOIN `glpi_items_deviceprocessors` AS `processormonit` "
             . " ON (`glpi_computers`.`id` = `processormonit`.`items_id`"
                . " AND `processormonit`.`itemtype` = 'Computer') ";
          }
          break;

      case 'PluginMonitoringServiceevent':
//         // Join between service events and services
//         if ($new_table.".".$linkfield == "glpi_plugin_monitoring_services.id") {
//            return "
//               LEFT JOIN `glpi_plugin_monitoring_services`
//             ON (`glpi_plugin_monitoring_serviceevents`.`plugin_monitoring_services_id`
//             = `glpi_plugin_monitoring_services`.`id`)
//            ";
//         }
//         // Join between service events and components catalogs
//         if ($new_table.".".$linkfield == "glpi_plugin_monitoring_components.plugin_monitoring_components_id") {
//            return "
//               LEFT JOIN `glpi_plugin_monitoring_services`
//             ON (`glpi_plugin_monitoring_serviceevents`.`plugin_monitoring_services_id`
//             = `glpi_plugin_monitoring_services`.`id`)
//               LEFT JOIN `glpi_plugin_monitoring_components`
//             ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_components_id`
//             = `glpi_plugin_monitoring_components`.`id`)
//            ";
//         }
//         // Join between service events and components catalogs hosts
//         if ($new_table.".".$linkfield == "glpi_plugin_monitoring_componentscatalogs_hosts.plugin_monitoring_componentscatalogs_hosts_id") {
//            return "
//               LEFT JOIN `glpi_plugin_monitoring_services` as servicess
//             ON (`glpi_plugin_monitoring_serviceevents`.`plugin_monitoring_services_id`
//             = servicess.`id`)
//            ";
//         }
//         // Join between service events and computers
//         if ($new_table.".".$linkfield == "glpi_computers.computers_id") {
//            return "
//               LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
//             ON (glpi_plugin_monitoring_services.`plugin_monitoring_componentscatalogs_hosts_id`
//             = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
//               LEFT JOIN `glpi_computers`
//             ON (`glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_computers`.`id`
//                AND
//                `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype` = 'Computer')
//            ";
//         }
       break;

      case 'PluginMonitoringHostdailycounter':
         // Join between daily counters and computers
         if ($new_table.".".$linkfield == "glpi_computers.computers_id") {
            return "
               INNER JOIN `glpi_computers`
            ON (`glpi_plugin_monitoring_hostdailycounters`.`hostname` = `glpi_computers`.`name`
               AND `glpi_computers`.`entities_id` IN (".$_SESSION['glpiactiveentities_string'].")
               )
            ";
         }
         // Join between daily counters and entities
         if ($new_table.".".$linkfield == "glpi_entities.entities_id") {
            return "
               INNER JOIN `glpi_entities`
               ON (`glpi_computers`.`entities_id` = `glpi_entities`.`id`
               )
            ";
         }
         break;

      case 'PluginMonitoringUnavailability':
          // Join between unavailabilities and services
          if ($new_table.".".$linkfield == "glpi_plugin_monitoring_services.id") {
             return "
                INNER JOIN `glpi_plugin_monitoring_services`
              ON (`glpi_plugin_monitoring_unavailabilities`.`plugin_monitoring_services_id`
              = `glpi_plugin_monitoring_services`.`id`)
             ";
          }
          // Join between unavailabilities and components catalogs
          if ($new_table.".".$linkfield == "glpi_plugin_monitoring_components.plugin_monitoring_components_id") {
             return "
                INNER JOIN `glpi_plugin_monitoring_components`
              ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_components_id`
              = `glpi_plugin_monitoring_components`.`id`)
             ";
          }
          // Join between unavailabilities and components catalogs hosts
          if ($new_table.".".$linkfield == "glpi_plugin_monitoring_componentscatalogs_hosts.plugin_monitoring_componentscatalogs_hosts_id") {
             return "
                INNER JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
              ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id`
              = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
             ";
          }
          // Join between unavailabilities and computers
          if ($new_table.".".$linkfield == "glpi_computers.computers_id") {
             $ret = '';
             if (!in_array('glpi_plugin_monitoring_services', $already_link_tables)) {
                $ret .= "
                   LEFT JOIN `glpi_plugin_monitoring_services`
                     ON (`glpi_plugin_monitoring_unavailabilities`.`plugin_monitoring_services_id`
                           = `glpi_plugin_monitoring_services`.`id`)
                ";
             }
             $ret .= "
                LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                  ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id`
                     = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
                LEFT JOIN `glpi_computers`
                  ON (`glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_computers`.`id`
                     AND
                  `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype` = 'Computer')
             ";
             return $ret;
          }
          if ($new_table.".".$linkfield == "glpi_networkequipments.networkequipments_id") {
             return "LEFT JOIN `glpi_networkequipments`
                  ON (`glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_networkequipments`.`id`
                     AND
                  `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype` = 'NetworkEquipment')";
          }
          break;

      case 'PluginMonitoringDowntime':
          // Join between downtimes and computers
          if ($new_table.".".$linkfield == "glpi_computers.computers_id") {
             return "
                INNER JOIN `glpi_plugin_monitoring_hosts`
             ON (`glpi_plugin_monitoring_downtimes`.`plugin_monitoring_hosts_id` = `glpi_plugin_monitoring_hosts`.`id`)
                INNER JOIN `glpi_computers`
                  ON (`glpi_plugin_monitoring_hosts`.`items_id` = `glpi_computers`.`id`)
             ";
          }
          break;

      case 'PluginMonitoringService':
          if ($new_table.".".$linkfield == "glpi_computers.computers_id") {
             return "
               LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                  ON `plugin_monitoring_componentscatalogs_hosts_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
               LEFT JOIN `glpi_computers`
                  ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_computers`.`id`
                        AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='Computer'
               LEFT JOIN `glpi_printers`
                  ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_printers`.`id`
                     AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='Printer'
               LEFT JOIN `glpi_networkequipments`
                  ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_networkequipments`.`id`
                     AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='NetworkEquipment' ";
          } else if ($new_table.".".$linkfield == 'glpi_plugin_monitoring_hosts.plugin_monitoring_hosts_id') {
             return "
               LEFT JOIN `glpi_plugin_monitoring_hosts`
                  ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_plugin_monitoring_hosts`.`items_id`
                        AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`=`glpi_plugin_monitoring_hosts`.`itemtype` ";
          } else if ($new_table.".".$linkfield == 'glpi_plugin_monitoring_componentscatalogs.plugin_monitoring_componentscatalogs_id') {
             return "
               LEFT JOIN `glpi_plugin_monitoring_componentscatalogs`
                  ON (`glpi_plugin_monitoring_componentscatalogs_hosts`.`plugin_monitoring_componentscalalog_id` = `glpi_plugin_monitoring_componentscatalogs`.`id`)";
          } else if ($new_table.".".$linkfield == 'glpi_plugin_monitoring_componentscatalogs_hosts.plugin_monitoring_componentscatalogs_hosts_id') {
             return " ";
          }
         break;


      case 'PluginMonitoringHost':
//         echo $new_table.".".$linkfield."<br>";
          if ($new_table.".".$linkfield == "glpi_computers.computers_id") {
             return "
               LEFT JOIN `glpi_computers`
                  ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_computers`.`id`
                        AND `glpi_plugin_monitoring_hosts`.`itemtype`='Computer'
               LEFT JOIN `glpi_printers`
                  ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_printers`.`id`
                     AND `glpi_plugin_monitoring_hosts`.`itemtype`='Printer'
               LEFT JOIN `glpi_networkequipments`
                  ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_networkequipments`.`id`
                     AND `glpi_plugin_monitoring_hosts`.`itemtype`='NetworkEquipment' ";
          }
          break;

   }
   return "";
}


function plugin_monitoring_addOrderBy($type,$id,$order,$key=0) {
   return "";
}


function plugin_monitoring_addDefaultWhere($type) {

   switch ($type) {
      case "PluginMonitoringDisplayview" :
	 $who=Session::getLoginUserID();
	 return " (`glpi_plugin_monitoring_displayviews`.`users_id` = '$who'
	    OR `glpi_plugin_monitoring_displayviews`.`users_id` = '0') ";
	 break;
   }
   return "";
}


function plugin_monitoring_addWhere($link,$nott,$type,$id,$val) {

   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$id]["table"];
   $field = $searchopt[$id]["field"];

   switch ($type) {
      // * Computer List (front/computer.php)
      case 'PluginMonitoringService':
         switch ($table.".".$field) {
            case "glpi_plugin_monitoring_services.Computer":
            case "glpi_plugin_monitoring_services.Printer":
            case "glpi_plugin_monitoring_services.NetworkEquipment":
               return $link." (`glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = '".$val."') ";
               break;
         }
         break;

      case 'PluginMonitoringHost':
         switch ($table.".".$field) {
            case "glpi_plugin_monitoring_hosts.name":
               return $link." (CONCAT_WS('', `glpi_computers`.`name`, `glpi_printers`.`name`, `glpi_networkequipments`.`name`) LIKE '%".$val."%') ";
               break;
         }
         break;
   }

   return "";
}



/*
 * Webservices
 */
function plugin_monitoring_registerMethods() {
   global $WEBSERVICES_METHOD;

   $WEBSERVICES_METHOD['monitoring.shinkenGetConffiles'] = array('PluginMonitoringWebservice',
						       'methodShinkenGetConffiles');
   # Get commands for arbiter
   $WEBSERVICES_METHOD['monitoring.shinkenCommands'] = array('PluginMonitoringWebservice',
						       'methodShinkenCommands');
   $WEBSERVICES_METHOD['monitoring.shinkenHosts'] = array('PluginMonitoringWebservice',
						       'methodShinkenHosts');
   $WEBSERVICES_METHOD['monitoring.shinkenHostgroups'] = array('PluginMonitoringWebservice',
						       'methodShinkenHostgroups');
   $WEBSERVICES_METHOD['monitoring.shinkenContacts'] = array('PluginMonitoringWebservice',
						       'methodShinkenContacts');
   $WEBSERVICES_METHOD['monitoring.shinkenTimeperiods'] = array('PluginMonitoringWebservice',
						       'methodShinkenTimeperiods');

   $WEBSERVICES_METHOD['monitoring.shinkenServices'] = array('PluginMonitoringWebservice',
						       'methodShinkenServices');
   $WEBSERVICES_METHOD['monitoring.shinkenTemplates'] = array('PluginMonitoringWebservice',
						       'methodShinkenTemplates');
   $WEBSERVICES_METHOD['monitoring.dashboard'] = array('PluginMonitoringWebservice',
						       'methodDashboard');
   $WEBSERVICES_METHOD['monitoring.getServicesList'] = array('PluginMonitoringWebservice',
							     'methodGetServicesList');
   $WEBSERVICES_METHOD['monitoring.getHostsStates'] = array('PluginMonitoringWebservice',
							     'methodGetHostsStates');
   $WEBSERVICES_METHOD['monitoring.getServicesStates'] = array('PluginMonitoringWebservice',
							     'methodGetServicesStates');
   $WEBSERVICES_METHOD['monitoring.getHostsLocations'] = array('PluginMonitoringWebservice',
							     'methodGetHostsLocations');
   $WEBSERVICES_METHOD['monitoring.getUnavailabilities'] = array('PluginMonitoringWebservice',
							     'methodGetUnavailabilities');
}

/**
 * Define Dropdown tables to be manage in GLPI :
**/
function plugin_monitoring_getDropdown(){

   return array(
      'PluginMonitoringServicescatalog'     => __('Services catalogs', 'monitoring'),
		'PluginMonitoringCheck'               => __('Check definitions', 'monitoring'),
		'PluginMonitoringCommand'             => __('Commands', 'monitoring'),
		'PluginMonitoringComponentscatalog'   => __('Components catalogs', 'monitoring'),
		'PluginMonitoringContacttemplate'     => __('Contact templates', 'monitoring'),
		'PluginMonitoringComponent'           => __('Components', 'monitoring'));
}

function plugin_monitoring_searchOptionsValues($item) {
   global $CFG_GLPI;

   // Fred : Add a log to check whether this function is still called ...
   PluginMonitoringToolbox::logIfExtradebug(
      'pm',
      "plugin_monitoring_searchOptionsValues is called ..\n"
   );
   // Search options for services
   if ($item['searchoption']['table'] == 'glpi_plugin_monitoring_services'
	   AND $item['searchoption']['field'] == 'state') {
      $input = array();
      $input['CRITICAL'] = 'CRITICAL';
      $input['DOWNTIME'] = 'DOWNTIME';
      $input['FLAPPING'] = 'FLAPPING';
      $input['OK'] = 'OK';
      $input['RECOVERY'] = 'RECOVERY';
      $input['UNKNOWN'] = 'UNKNOWN';
      $input['WARNING'] = 'WARNING';

      Dropdown::showFromArray($item['name'], $input, array('value'=>$item['value']));
      return true;
   } else if ($item['searchoption']['table'] == 'glpi_plugin_monitoring_services'
	   AND $item['searchoption']['field'] == 'state_type') {
      $input = array();
      $input['HARD'] = 'HARD';
      $input['SOFT'] = 'SOFT';

      Dropdown::showFromArray($item['name'], $input, array('value'=>$item['value']));
      return true;
   } else if ($item['searchoption']['table'] == 'glpi_plugin_monitoring_services'
	   AND ($item['searchoption']['field'] == 'Computer'
		   OR $item['searchoption']['field'] == 'Printer'
		   OR $item['searchoption']['field'] == 'NetworkEquipment')) {

      $itemtype = $item['searchoption']['field'];

      $use_ajax = false;

      if ($CFG_GLPI["use_ajax"]) {
	 $nb = countElementsInTable("glpi_plugin_monitoring_componentscatalogs_hosts", "`itemtype`='Computer'");
	 if ($nb > $CFG_GLPI["ajax_limit_count"]) {
	    $use_ajax = true;
	 }
      }

      $params = array();
      $params['itemtype'] = $itemtype;
      $params['searchText'] = '';
      $params['myname'] = $item['name'];
      $params['rand'] = '';
      $params['value'] = $item['value'];

      $default = "<select name='".$item['name']."' id='dropdown_".$item['name']."0'>";
      if (isset($item['value'])
	      AND !empty($item['value'])) {
	 $itemm = new $itemtype();
	 $itemm->getFromDB($item['value']);
	 $default .= "<option value='".$item['value']."'>".$itemm->getName()."</option></select>";
      }

      Ajax::dropdown($use_ajax, "/plugins/monitoring/ajax/dropdownDevices.php", $params,$default);

      return true;
   }

   // Search options for hosts
   if ($item['searchoption']['table'] == 'glpi_plugin_monitoring_hosts'
	   AND $item['searchoption']['field'] == 'state') {
      $input = array();
      $input['DOWN'] = 'DOWN';
      $input['DOWNTIME'] = 'DOWNTIME';
      $input['FLAPPING'] = 'FLAPPING';
      $input['RECOVERY'] = 'RECOVERY';
      $input['UNKNOWN'] = 'UNKNOWN';
      $input['UNREACHABLE'] = 'UNREACHABLE';
      $input['UP'] = 'UP';

      Dropdown::showFromArray($item['name'], $input, array('value'=>$item['value']));
      return true;
   } else if ($item['searchoption']['table'] == 'glpi_plugin_monitoring_hosts'
	   AND $item['searchoption']['field'] == 'state_type') {
      $input = array();
      $input['HARD'] = 'HARD';
      $input['SOFT'] = 'SOFT';

      Dropdown::showFromArray($item['name'], $input, array('value'=>$item['value']));
      return true;
   }
}

function plugin_monitoring_ReplayRulesForItem($args) {

   $itemtype = $args[0];
   $items_id = $args[1];
   $item = new $itemtype();
   $item->getFromDB($items_id);
   PluginMonitoringComponentscatalog_rule::isThisItemCheckRule($item);
}
?>
