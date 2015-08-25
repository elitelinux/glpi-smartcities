<?php
/**
 * @version $Id: iteminstall.php 297 2015-05-30 21:34:55Z yllen $
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

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 1;

// Initialization of the variables
include ("../../../../inc/includes.php");

//TRANS: The name of the report = Time before equipment start-up
$report = new PluginReportsAutoReport(__('iteminstall_report_title', 'reports'));

//Report's search criterias
$date = new PluginReportsDateIntervalCriteria($report, 'buy_date');
$type = new PluginReportsItemTypeCriteria($report, 'itemtype', '', 'infocom_types');
$budg = new PluginReportsDropdownCriteria($report, 'budgets_id', 'glpi_budgets', __('Budget'));

//Display criterias form is needed
$report->displayCriteriasForm();

$display_type = Search::HTML_OUTPUT;

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();
   $title    = $report->getFullTitle();
   $itemtype = $type->getParameterValue();

   if ($itemtype && $itemtype != "all") {
      $types = array($itemtype);
   } else {
      $types = array();
      $sql   = "SELECT DISTINCT `itemtype`
                FROM `glpi_infocoms` ".
                getEntitiesRestrictRequest('WHERE', 'glpi_infocoms').
                    $date->getSqlCriteriasRestriction('AND').
                    $budg->getSqlCriteriasRestriction('AND');
      foreach ($DB->request($sql) as $data) {
         $types[] = $data['itemtype'];
      }
   }

   $result = array();
   foreach ($types as $type) {
      if (!class_exists($type)) {
         continue;
      }
      $item  = new $type();
      $table = $item->getTable();
      $sql = "SELECT COUNT(*) AS cpt
              FROM `$table`
              INNER JOIN `glpi_infocoms` ON (`glpi_infocoms`.`itemtype`='$type'
                                             AND `glpi_infocoms`.`items_id`=`$table`.`id`)".
              getEntitiesRestrictRequest('WHERE', $table);
      if ($item->maybeDeleted()) {
         $sql .= " AND NOT `$table`.`is_deleted` ";
      }
      if ($item->maybeTemplate()) {
         $sql .= " AND NOT `$table`.`is_template` ";
      }
      $result[$type] = array();

      // Total of buy equipment
      $crit = $budg->getSqlCriteriasRestriction('AND').
              $date->getSqlCriteriasRestriction('AND');

      foreach ($DB->request($sql.$crit) as $data) {
         $result[$type]['buy'] = $data['cpt'];
      }

      for ($deb=0 ; $deb<12 ; $deb=$fin) {
         $fin = $deb+2;
         $crit2 = $crit;
         if ($deb) {
            $crit2 .= " AND `use_date` >= DATE_ADD(`buy_date`, INTERVAL $deb MONTH) ";
         }
         if ($fin) {
            $crit2 .= " AND `use_date` < DATE_ADD(`buy_date`, INTERVAL $fin MONTH) ";
         }
         foreach ($DB->request($sql.$crit2) as $data) {
            $result[$type]["$deb-$fin"] = $data['cpt'];
         }
      }
      $crit2  = $crit;
      $crit2 .= " AND (`use_date` IS NULL
                       OR `use_date` >= DATE_ADD(`buy_date`, INTERVAL 12 MONTH))";
      foreach ($DB->request($sql.$crit2) as $data) {
         $result[$type]['12+'] = $data['cpt'];
      }
   }

   if ($display_type == Search::HTML_OUTPUT) {
         echo "<div class='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th>$title</th></tr>\n";
         echo "</table></div>\n";
   }

   $nbres = count($result);
   if ($nbres > 0) {
      if ($nbres > 1) {
         $nbrows = $nbres*2+2;
         $result['total'] = array();
         reset($result);
         foreach (next($result) as $key => $val) {
            $result['total'][$key] = 0;
         }
      } else {
         $nbrows = 2;
      }
      $nbcols = 9;
      echo Search::showHeader($display_type, $nbrows, $nbcols, true);
      echo Search::showNewLine($display_type);
      $numcol=1;
      echo Search::showHeaderItem($display_type, __('Item type'), $numcol);
      echo Search::showHeaderItem($display_type, __('Total'), $numcol);
      echo Search::showHeaderItem($display_type, '0-1', $numcol);
      echo Search::showHeaderItem($display_type, '2-3', $numcol);
      echo Search::showHeaderItem($display_type, '4-5', $numcol);
      echo Search::showHeaderItem($display_type, '6-7', $numcol);
      echo Search::showHeaderItem($display_type, '8-9', $numcol);
      echo Search::showHeaderItem($display_type, '10-11', $numcol);
      echo Search::showHeaderItem($display_type, '12+', $numcol);
      echo Search::showEndLine($display_type);

      $row_num = 1;
      foreach ($result as $itemtype => $row) {
         if ($itemtype == 'total') {
            $name = __('Total');

         } else if ($item = getItemForItemtype($itemtype)) {
            $name = $item->getTypeName();

         } else {
            continue;
         }

         $numcol=1;
         echo Search::showNewLine($display_type);
         echo Search::showItem($display_type, $name, $numcol, $row_num, "class='b'");
         foreach ($row as $ref => $val) {
            $val = $result[$itemtype][$ref];
            echo Search::showItem($display_type, ($val ? $val : ''), $numcol, $row_num,
                                  "class='right'");
            if ($itemtype != 'total' && isset($result['total'])) {
               $result['total'][$ref] += $val;
            }
         }
         echo Search::showEndLine($display_type);
         $row_num++;

         $numcol = 1;
         echo Search::showNewLine($display_type);
         echo Search::showItem($display_type, '', $numcol, $row_num);
         foreach ($row as $ref => $val) {
            $val = $result[$itemtype][$ref];
            $buy = $result[$itemtype]['buy'];
            if (($ref == 'buy') || ($buy == 0) || ($val == 0)) {
               $tmp = '';
            } else {
               $tmp = round($val*100/$buy,0)."%";
            }
            echo Search::showItem($display_type, $tmp, $numcol, $row_num, "class='right'");
         }
         echo Search::showEndLine($display_type);
         $row_num++;
      }

      if ($display_type == Search::HTML_OUTPUT) {
         $row = array_pop($result); // Last line : total or single type
         unset($row['buy']);
         Stat::showGraph(array($title => $row), array('type' => 'pie'));
      }
   } else {
      $nbrows = 1; $nbcols = 1;
      echo Search::showHeader($display_type, $nbrows, $nbcols, true);
      echo Search::showNewLine($display_type);
      $num=1;
      echo Search::showHeaderItem($display_type, __('No item found'), $num);
      echo Search::showEndLine($display_type);
   }
   echo Search::showFooter($display_type, $title);
}
if ($display_type == Search::HTML_OUTPUT) {
   Html::footer();
}
?>
