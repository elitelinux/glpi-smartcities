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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringMessage extends CommonDBTM {


   static function getMessages() {
      global $CFG_GLPI;

      $pmMessage = new self();

      $sess_id = session_id();
//      PluginMonitoringSecurity::updateSession();

      // Display if shinken is in restart or if restarted less than 5 minutes
      echo "<div id='shikenrestart'></div>";
//      echo "<script type=\"text/javascript\">
//      var elshikenrestart = Ext.get(\"shikenrestart\");
//      var mgrshikenrestart = elshikenrestart.getUpdateManager();
//      mgrshikenrestart.loadScripts=true;
//      mgrshikenrestart.showLoadIndicator=false;
//      mgrshikenrestart.startAutoRefresh(30, \"".$CFG_GLPI["root_doc"].
//                 "/plugins/monitoring/ajax/updateshinkenrestartmessage.php\","
//              . " \"sess_id=".$sess_id.
//              "&glpiID=".$_SESSION['glpiID'].
//              "&plugin_monitoring_securekey=".$_SESSION['plugin_monitoring_securekey'].
//              "\", \"\", true);";
//      echo "</script>";

echo "<script type=\"text/javascript\">
(function worker() {
  $.get('".$CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/updateshinkenrestartmessage.php"
        ."?sess_id=".$sess_id."&glpiID=".$_SESSION['glpiID']."', function(data) {
    $('#shikenrestart').html(data);
    setTimeout(worker, 30000);
  });
})();
</script>";

      $servicecatalog = '';
      $confchanges = '';

      if (session::haveRight("plugin_monitoring_servicescatalog", READ)) {
         $servicecatalog = $pmMessage->servicescatalogMessage();
      }
      $confchanges = $pmMessage->configurationchangesMessage();
      $runningshinken = $pmMessage->ShinkennotrunMessage();
      $i = 0;
      if ($servicecatalog != ''
              OR $confchanges != '') {
         echo "<div class='msgboxmonit msgboxmonit-orange'>";
         if ($confchanges != '') {
            echo $confchanges;
            $i++;
         }
         if ($servicecatalog != '') {
            if($i > 0) {
               echo "</div>";
               echo "<div class='msgboxmonit msgboxmonit-orange'>";
            }
            echo $servicecatalog;
            $i++;
         }
         if ($runningshinken != '') {
            if($i > 0) {
               echo "</div>";
               echo "<div class='msgboxmonit msgboxmonit-red'>";
            }
            echo $runningshinken."!";
            $i++;
         }
         echo "</div>";
      }
   }



   /**
    * This fonction search if a services catalog has a resource deleted
    *
    */
   function servicescatalogMessage() {
      global $DB;

      $pmServicescatalog = new PluginMonitoringServicescatalog();
      $input = '';
      $a_catalogs = array();

      $query = "SELECT `plugin_monitoring_servicescatalogs_id` FROM `glpi_plugin_monitoring_businessrulegroups`

         LEFT JOIN `glpi_plugin_monitoring_businessrules` ON `glpi_plugin_monitoring_businessrulegroups`.`id` = `plugin_monitoring_businessrulegroups_id`

         LEFT JOIN `glpi_plugin_monitoring_services` ON `plugin_monitoring_services_id` = `glpi_plugin_monitoring_services`.`id`

         WHERE `glpi_plugin_monitoring_services`.`id` IS NULL";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $pmServicescatalog->getFromDB($data['plugin_monitoring_servicescatalogs_id']);
         $a_catalogs[$data['plugin_monitoring_servicescatalogs_id']] = $pmServicescatalog->getLink();
      }
      if (count($a_catalogs) > 0) {
         $input = __('Services catalog with resources not available', 'monitoring')." : <br/>";
         $input .= implode(" - ", $a_catalogs);
      }
      return $input;
   }



   /**
    * Get modifications of resources (if have modifications);
    */
   function configurationchangesMessage() {
      global $DB;

      $input = '';
      $pmLog = new PluginMonitoringLog();
      // Get id of last Shinken restart
      $id_restart = 0;
      $a_restarts = $pmLog->find("`action` LIKE 'restart%'", "`id` DESC", 1);
      if (count($a_restarts) > 0) {
         $a_restart = current($a_restarts);
         $id_restart = $a_restart['id'];
      }
      // get number of modifications
      $nb_delete  = 0;
      $nb_add     = 0;
      $nb_update  = 0;
      $nb_delete = countElementsInTable(getTableForItemType('PluginMonitoringLog'), "`id` > '".$id_restart."'
         AND `action`='delete'");
      $nb_add = countElementsInTable(getTableForItemType('PluginMonitoringLog'), "`id` > '".$id_restart."'
         AND `action`='add'");
      $nb_update = countElementsInTable(getTableForItemType('PluginMonitoringLog'), "`id` > '".$id_restart."'
         AND `action`='update'");
      if ($nb_delete > 0 OR $nb_add > 0 OR $nb_update > 0) {
         $input .= __('The configuration has changed', 'monitoring')."<br/>";
         if ($nb_add > 0) {
            $input .= "<a onClick='Ext.get(\"added_elements\").toggle();'>".$nb_add."</a> ".__('resources added', 'monitoring');
            echo "<div style='position:absolute;z-index:10;left: 50%;
               margin-left: -350px;margin-top:40px;display:none'
               class='msgboxmonit msgboxmonit-grey' id='added_elements'>";
            $query = "SELECT * FROM `".getTableForItemType('PluginMonitoringLog')."`
               WHERE `id` > '".$id_restart."' AND `action`='add'
               ORDER BY `id` DESC";
            $result = $DB->query($query);
            while ($data=$DB->fetch_array($result)) {
               echo "[".Html::convDateTime($data['date_mod'])."] Add ".$data['value']."<br/>";
            }
            echo "</div>";
         }
         if ($nb_delete > 0) {
            if ($nb_add > 0) {
               $input .= " / ";
            }
            $input .= "<a onClick='Ext.get(\"deleted_elements\").toggle();'>".$nb_delete."</a> ".__('resources deleted', 'monitoring');
            echo "<div style='position:absolute;z-index:10;left: 50%;
               margin-left: -350px;margin-top:40px;display:none'
               class='msgboxmonit msgboxmonit-grey' id='deleted_elements'>";
            $query = "SELECT * FROM `".getTableForItemType('PluginMonitoringLog')."`
               WHERE `id` > '".$id_restart."' AND `action`='delete'
               ORDER BY `id` DESC";
            $result = $DB->query($query);
            while ($data=$DB->fetch_array($result)) {
               echo "[".Html::convDateTime($data['date_mod'])."] Delete ".$data['value']."<br/>";
            }
            echo "</div>";
         }
         if ($nb_update > 0) {
            if ($nb_add > 0 OR $nb_delete > 0) {
               $input .= " / ";
            }
            $input .= "<a onClick='Ext.get(\"updated_elements\").toggle();'>".$nb_update."</a> ".__('resources updated', 'monitoring');
            echo "<div style='position:absolute;z-index:10;left: 50%;
               margin-left: -350px;margin-top:40px;display:none'
               class='msgboxmonit msgboxmonit-grey' id='updated_elements'>";
            $query = "SELECT * FROM `".getTableForItemType('PluginMonitoringLog')."`
               WHERE `id` > '".$id_restart."' AND `action`='update'
               ORDER BY `id` DESC";
            $result = $DB->query($query);
            while ($data=$DB->fetch_array($result)) {
               echo "[".Html::convDateTime($data['date_mod'])."] Update ".$data['value']."<br/>";
            }
            echo "</div>";
         }
         $input .= "<br/>";

         // Try to restart Shinken via webservice
         $pmShinkenwebservice = new PluginMonitoringShinkenwebservice();
         $pmShinkenwebservice->sendRestartArbiter();
         $input .= __('Shinken is restarted automatically', 'monitoring');
         //$input .= __('Restart Shinken to reload this new configuration', 'monitoring');
      }
      return $input;
   }


   /**
    * Get maximum time between 2 checks and see if have one event in this period
    *
    */
   function ShinkennotrunMessage() {
      global $DB;

      $input = '';
      $query = "SELECT * FROM `glpi_plugin_monitoring_checks` ORDER BY `check_interval` DESC LIMIT 1";

      $result = $DB->query($query);
      $data = $DB->fetch_assoc($result);
      $time_s = $data['check_interval'] * 60;

      $query = "SELECT count(id) as cnt FROM `glpi_plugin_monitoring_services`";
      $result = $DB->query($query);
      $data = $DB->fetch_assoc($result);
      if ($data['cnt'] > 0) {
         $query = "SELECT * FROM `glpi_plugin_monitoring_services`
            WHERE UNIX_TIMESTAMP(last_check) > UNIX_TIMESTAMP()-".$time_s."
               ORDER BY `last_check`
               LIMIT 1";
         $result = $DB->query($query);
         if ($DB->numrows($result) == '0') {
            $input = __('No events found in last minutes, so Shinken seems stopped', 'monitoring');
         }
      }
      return $input;
   }



   function displayShinkenRestart() {
      global $CFG_GLPI;

      $pmLog = new PluginMonitoringLog();

      $a_reload_planned = $pmLog->find("`action` LIKE 'reload%' AND "
              ."`date_mod` > date_add(now(), interval - 10 MINUTE)", "`id` DESC", 1);
      if (count($a_reload_planned) == 1) {
         $a_reload = current($a_reload_planned);
         if ($a_reload['action'] == 'reload_planned') {
            echo "<div class='msgboxmonit msgboxmonit-red'>";
            echo __('Shinken reload order has been sent at', 'monitoring')." ".Html::convDateTime($a_reload['date_mod']);
            echo "</div>";
         } else {
            echo "<div class='msgboxmonit msgboxmonit-orange'>";
            echo __('Shinken reloaded at '.Html::convDateTime($a_reload['date_mod']));
            echo "</div>";
         }
      }

      $a_restart_planned = $pmLog->find("`action` LIKE 'restart%' AND "
              ."`date_mod` > date_add(now(), interval - 10 MINUTE)", "`id` DESC", 1);
      if (count($a_restart_planned) == 1) {
         $a_restart = current($a_restart_planned);
         if ($a_restart['action'] == 'restart_planned') {
            echo "<div class='msgboxmonit msgboxmonit-red'>";
            echo __('Shinken restart order has been sent at', 'monitoring')." ".Html::convDateTime($a_restart['date_mod']);
            echo "</div>";
         } else {
            echo "<div class='msgboxmonit msgboxmonit-orange'>";
            echo __('Shinken restarted at '.Html::convDateTime($a_restart['date_mod']));
            echo "</div>";
         }
      }
   }
}

?>