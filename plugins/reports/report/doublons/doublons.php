<?php
/**
 * @version $Id: doublons.php 309 2015-05-31 17:44:06Z remi $
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Reports is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   reports
 @authors    Nelly Mahu-Lasson, Remi Collet
 @copyright Copyright (c) 2009-2015 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

include ("../../../../inc/includes.php");

includeLocales("doublons");

Session::checkRight("plugin_reports_doublons", READ);
$computer = new Computer();
$computer->checkGlobal(READ);

//TRANS: The name of the report = Duplicate computers
Html::header(__('doublons_report_title', 'report'), $_SERVER['PHP_SELF'], "utils", "report");

Report::title();

$crits = array(0 => Dropdown::EMPTY_VALUE,
               1 => __('Name'),
               2 => __('Model')." + ".__('Serial number'),
               3 => __('Name')." + ".__('Model')." + ".__('Serial number'),
               4 => __('MAC address'),
               5 => __('IP address'),
               6 => __('Inventory number'));

if (isset($_GET["crit"])) {
   $crit = $_GET["crit"];

} else if (isset($_POST["crit"])) {
   $crit = $_POST["crit"];

} else if (isset($_SESSION['plugin_reports_doublons_crit'])) {
   $crit = $_SESSION['plugin_reports_doublons_crit'];

} else {
   $crit = 0;
}
$rand  = mt_rand();

// check OCS install
$plugin = new Plugin;
$ocs_installed = $plugin->isInstalled('ocsinventoryng');

// ---------- Form ------------
echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
echo "<table class='tab_cadre' cellpadding='5'>\n";
echo "<tr class='tab_bg_1 center'>";
echo "<th colspan='3'>".__('Duplicate computers', 'reports')."</th></tr>\n";

if (Session::haveRight("config", READ)) { // Check only read as we probably use the replicate (no 'w' in this case)
   echo "<tr class='tab_bg_3 center'><td colspan='".(($crit > 0)?'3':'2')."'>";
   echo "<a href='./doublons.config.php'>".__('Report configuration', 'reports')."</a></td></tr>\n";
}

echo "<tr class='tab_bg_1'><td class='right'>"._n('Criterion', 'Criteria', 2). "</td><td>";
echo "<select name='crit'>";

foreach ($crits as $key => $val) {
   echo "<option value='$key'" . (($crit == $key) ? "selected" : "") . ">$val</option>";
}
echo "</select></td>";

if ($crit > 0) {
   echo "<td>";
   //Add parameters to uri to be saved as bookmarks
   $_SERVER["REQUEST_URI"] = buildBookmarkUrl($_SERVER["REQUEST_URI"],$crit);
   Bookmark::showSaveButton(Bookmark::SEARCH,'Computer');
   echo "</td>";
}
echo"</tr>\n";

echo "<tr class='tab_bg_1 center'><td colspan='".(($crit > 0)?'3':'2')."'>";
echo "<input type='submit' value='valider' class='submit'/>";
echo "</td></tr>\n";
echo "</table>\n";
Html::closeForm();

if ($crit == 5) { // Search Duplicate IP Address - From glpi_networking_ports
   $IPBlacklist = "A_ipa.`name` != ''
                   AND A_ipa.`name` != '0.0.0.0'";
   if (TableExists("glpi_plugin_reports_doublons_backlists")) {
      $res  =$DB->query("SELECT `addr`
                         FROM `glpi_plugin_reports_doublons_backlists`
                         WHERE `type` = '2'");

      while ($data = $DB->fetch_array($res)) {
         if (strpos($data["addr"], '%')) {
            $IPBlacklist .= " AND A_ipa.`name` NOT LIKE '".addslashes($data["addr"])."'";
         } else {
            $IPBlacklist .= " AND B_ipa.`name` != '".addslashes($data["addr"])."'";
         }
      }
   }

   $Sql = "SELECT A.`id` AS AID,
                  A.`name` AS Aname,
                  A_ipa.`name` AS Aaddr,
                  A.`entities_id` AS entity,

                  B.`id` AS BID,
                  B.`name` AS Bname,
                  B_ipa.`name` AS Baddr

            FROM `glpi_computers` A
            LEFT JOIN `glpi_networkports` A_np
               ON  A_np.`itemtype` = 'Computer'
               AND A_np.`items_id` = A.`id`
            LEFT JOIN `glpi_networknames` A_nn
               ON  A_nn.`itemtype` = 'NetworkPort'
               AND A_nn.`items_id` = A_np.`id`
            LEFT JOIN `glpi_ipaddresses`  A_ipa
               ON  A_ipa.`itemtype` = 'NetworkName'
               AND A_ipa.`items_id` = A_nn.`id`


            LEFT JOIN `glpi_computers` B
               ON B.`id` > A.`id`
               AND A.`entities_id` = B.`entities_id`
            LEFT JOIN `glpi_networkports` B_np
               ON  B_np.`itemtype` = 'Computer'
               AND B_np.`items_id` = B.`id`
            LEFT JOIN `glpi_networknames` B_nn
               ON  B_nn.`itemtype` = 'NetworkPort'
               AND B_nn.`items_id` = B_np.`id`
            LEFT JOIN `glpi_ipaddresses`  B_ipa
               ON  B_ipa.`itemtype` = 'NetworkName'
               AND B_ipa.`items_id` = B_nn.`id`

            ".getEntitiesRestrictRequest(" WHERE ", "A", "entities_id") ."
                 AND ($IPBlacklist)
                 AND A.`is_template` = '0'
                 AND B.`is_template` = '0'
                 AND A.`is_deleted` = '0'
                 AND B.`is_deleted` = '0'
                 AND A_ipa.`name` = B_ipa.`name`";

   $col = __('IP');

} else if ($crit == 4) { // Search Duplicate Mac Address - From glpi_computer_device
   $MacBlacklist = "''";
   if (TableExists("glpi_plugin_reports_doublons_backlists")) {
      $res = $DB->query("SELECT `addr`
                         FROM `glpi_plugin_reports_doublons_backlists`
                         WHERE `type` = '1'");
      while ($data = $DB->fetch_array($res)) {
         $MacBlacklist .= ",'".addslashes($data["addr"])."'";
      }
   } else {
      $MacBlacklist .= ",'44:45:53:54:42:00', 'BA:D0:BE:EF:FA:CE', '00:53:45:00:00:00',
                         '80:00:60:0F:E8:00'";
   }
   $Sql = "SELECT A.`id` AS AID,
                  A.`name` AS Aname,
                  A_np.`mac` AS Aaddr,
                  A.`entities_id` AS entity,
                  B.`id` AS BID,
                  B.`name` AS Bname,
                  B_np.`mac` AS Baddr

           FROM `glpi_computers` A
           LEFT JOIN `glpi_networkports` A_np
              ON  A_np.`itemtype` = 'Computer'
              AND A_np.`items_id` = A.`id`

           LEFT JOIN `glpi_computers` B
              ON B.`id` > A.`id`
              AND A.`entities_id` = B.`entities_id`
            LEFT JOIN `glpi_networkports` B_np
               ON  B_np.`itemtype` = 'Computer'
               AND B_np.`items_id` = B.`id`

            ".getEntitiesRestrictRequest(" WHERE ", "A", "entities_id") ."
                 AND A_np.`mac` = B_np.`mac`
                 AND A_np.`mac` NOT IN ($MacBlacklist)
                 AND A.`is_template` = '0'
                 AND B.`is_template` = '0'
                 AND A.`is_deleted` = '0'
                 AND B.`is_deleted` = '0'";

   $col = __('MAC');

} else if ($crit > 0) { // Search Duplicate Name and/ord Serial or Otherserial - From glpi_computers
   $SerialBlacklist = "''";
   if (TableExists("glpi_plugin_reports_doublons_backlists")) {
      $res = $DB->query("SELECT `addr`
                         FROM `glpi_plugin_reports_doublons_backlists`
                         WHERE `type` = '3'");
      while ($data = $DB->fetch_array($res)) {
         $SerialBlacklist .= ",'".addslashes($data["addr"])."'";
      }
   }
   $Sql = "SELECT A.`id` AS AID, A.`name` AS Aname,
                  A.`entities_id` AS entity,
                  B.`id` AS BID, B.`name` AS Bname
           FROM `glpi_computers` A,
                `glpi_computers` B " .
           getEntitiesRestrictRequest(" WHERE ", "A", "entities_id") ."
                 AND B.`id` > A.`id`
                 AND A.`entities_id` = B.`entities_id`
                 AND A.`is_template` = '0'
                 AND B.`is_template` = '0'
                 AND A.`is_deleted` = '0'
                 AND B.`is_deleted` = '0'";

   if ($crit == 6) {
      $Sql .= " AND A.`otherserial` != ''
                AND A.`otherserial` = B.`otherserial`";
   } else {
      if ($crit & 1) {
         $Sql .= " AND A.`name` != ''
                   AND A.`name` = B.`name`";
      }
      if ($crit & 2) {
         $Sql .= " AND A.`serial` NOT IN ($SerialBlacklist)
                   AND A.`serial` = B.`serial`
                   AND A.`computermodels_id` = B.`computermodels_id`";
      }
   }
   $col = "";
}


if ($crit > 0) { // Display result
   $canedit = $computer->canUpdate();
   $colspan = ($col ? 8 : 7) + ($canedit ? 1 : 0);

   // save crit for massive action
   $_SESSION['plugin_reports_doublons_crit'] = $crit;

   $rand = mt_rand();
   if ($canedit) {
      Html::openMassiveActionsForm('massformComputer');
   }
   echo "<table class='tab_cadrehov' cellpadding='5'>" .
      "<tr><th colspan='$colspan'>" . __('First computer', 'reports') . "</th>" .
      "<th class='blue' colspan='$colspan'>" . __('Second computer', 'reports')."</th></tr>\n" .
      "<tr>";
   $colspan *= 2;

   if ($canedit) {
      echo "<th>&nbsp;</th>";
   }
   echo "<th>" . __('ID') . "</th>" .
      "<th>" . __('Name') . "</th>" .
      "<th>" . __('Manufacturer') . "</th>" .
      "<th>" . __('Model') . "</th>" .
      "<th>" . __('Serial number') . "</th>" .
      "<th>" . __('Inventory number') . "</th>";
   if ($col) {
      echo "<th>$col</th>";
   }
   echo "<th>".__('Last inventory date', 'reports')."</th>";

   if ($canedit) {
      echo "<th>&nbsp;</th>";
   }

   echo "<th class='blue'>" . __('ID') . "</th>" .
        "<th class='blue'>" . __('Name') . "</th>" .
        "<th class='blue'>" . __('Manufacturer') . "</th>" .
        "<th class='blue'>" . __('Inventory number') . "</th>" .
        "<th class='blue'>" . __('Serial number') . "</th>".
        "<th class='blue'>".__('Inventory number')."</th>";
   if ($col) {
      echo "<th class='blue'>$col</th>";
   }
   echo "<th class='blue'>".__('Last inventory date', 'reports')."</th>";

   echo "</tr>\n";


   if (method_exists('DBConnection', 'getReadConnection')) { // In 0.80
      $DBread = DBConnection::getReadConnection();
   } else {
      $DBread = $DB;
   }

   $comp = new Computer();
   $result = $DBread->query($Sql);
   for ($prev=-1, $i=0 ; $data = $DBread->fetch_array($result) ; $i++) {
      if ($prev != $data["entity"]) {
         $prev = $data["entity"];
         echo "<tr class='tab_bg_4'><td class='center' colspan='$colspan'>".
            Dropdown::getDropdownName("glpi_entities", $prev) . "</td></tr>\n";
      }
      echo "<tr class='tab_bg_2'>";
      if ($canedit) {
         echo "<td>" . Html::getMassiveActionCheckBox('Computer', $data["AID"]) . "</td>";
      }
      echo "<td class='b'>".$data["AID"]."</td>";
      if ($comp->getFromDB($data["AID"])) {
         echo "<td>";
         echo $comp->getLink(true);
         echo "</td><td>";
         echo Dropdown::getDropdownName("glpi_manufacturers", $comp->getField('manufacturers_id'));
         echo "</td><td>";
         echo Dropdown::getDropdownName("glpi_computermodels", $comp->getField('computermodels_id'));
         echo "</td><td>".$comp->getField('serial');
         echo "</td><td>".$comp->getField('otherserial')."</td>";
      } else {
         echo "<td colspan='5'>".$data["Aname"]."</td>";
      }
      if ($col) {
         echo "<td>" .$data["Aaddr"]. "</td>";
      }
      echo "<td>";
      if ($ocs_installed) {
         echo getLastOcsUpdate($data['AID']);
      }
      echo "</td>";
      if ($canedit) {
         echo "<td>" . Html::getMassiveActionCheckBox('Computer', $data["BID"]) . "</td>";
      }
      echo "<td class='b blue'>".$data["BID"]."</td>";
      if ($comp->getFromDB($data["BID"])) {
         echo "<td class='blue'>";
         echo $comp->getLink(true);
         echo "</td><td class='blue'>";
         echo Dropdown::getDropdownName("glpi_manufacturers", $comp->getField('manufacturers_id'));
         echo "</td><td class='blue'>";
         echo Dropdown::getDropdownName("glpi_computermodels", $comp->getField('computermodels_id'));
         echo "</td><td class='blue'>".$comp->getField('serial');
         echo "</td><td class='blue'>".$comp->getField('otherserial')."</td>";
      } else {
         echo "<td colspan='5' class='blue'>".$data["Aname"]."</td>";
      }
      if ($col) {
         echo "<td class='blue'>" .$data["Baddr"]. "</td>";
      }
      echo "<td class='blue'>";
      if ($ocs_installed) {
         echo getLastOcsUpdate($data['BID']);
      }
      echo "</td>";

   echo "</tr>\n";
   }
   echo "<tr class='tab_bg_4'><td class='center' colspan='$colspan'>";
   if ($i) {
      printf(__('%1$s: %2$s'), __('Duplicate computers', 'reports'), $i);
   } else {
      _e('No item found');
   }
   echo "</td></tr>\n";
   echo "</table>";
   if ($canedit) {
      if ($i) {
         $massiveactionparams = array('num_displayed'    => $i,
                                      'container'        => 'massformComputer',
                                      'ontop'            => false,
                                      'forcecreate'      => true,);
         Html::showMassiveActions($massiveactionparams);
      }
      Html::closeForm();
   }
}
Html::footer();


function buildBookmarkUrl($url,$crit) {
   return $url."?crit=".$crit;
}


function getLastOcsUpdate($computers_id) {
   global $DB;

   $query = "SELECT `last_ocs_update`
             FROM `glpi_plugin_ocsinventoryng_ocslinks`
             WHERE `computers_id` = '$computers_id'";
   $results = $DB->query($query);

   if ($DB->numrows($results) > 0) {
      return $DB->result($results,0,'last_ocs_update');
   }
   return '';
}
?>