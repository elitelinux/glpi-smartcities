<?php
/*
 * @version $Id$
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.
 
 http://indepnet.net/   http://glpi-project.org
 ----------------------------------------------------------------------
 
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
 ------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Sponsor: Oregon Dept. of Administrative Services, State Data Center
// Original Author of file: Ryan Foster
// Contact: Matt Hoover <dev@opensourcegov.net>
// Project Website: http://www.opensourcegov.net
// Purpose of file: Page to add and manage custom fields.
// ----------------------------------------------------------------------

include ('../../../inc/includes.php');

// Do ACL checks

Session::checkRight('config', READ);

Html::header(
   __('Manage Custom Fields', 'customfields'),
   '',
   'config',
   'plugin',
   'customfields'
);

if (isset($_GET['itemtype'])) {
   $itemtype = $_GET['itemtype'];

   // ** ACTIONS ** //

   if (isset($_POST['enable'])) {

      // Enable custom fields for this device type

      // Get current custom fields

      $sql    = "SELECT COUNT(`id`) AS num_cf
              FROM `glpi_plugin_customfields_fields`
              WHERE `itemtype` = '$itemtype'
              AND `data_type` <> 'sectionhead'";

      $result = $DB->query($sql);
      $data   = $DB->fetch_assoc($result);

      if ($data['num_cf'] > 0) {

         // Need at least one custom field (not including section headings)
         // before enabling

         global $ACTIVE_CUSTOMFIELDS_TYPES;

         $ACTIVE_CUSTOMFIELDS_TYPES[] = $itemtype;

         // Enable custom fields for this item type

         $query = "UPDATE `glpi_plugin_customfields_itemtypes`
                   SET `enabled` = 1
                   WHERE `itemtype` = '$itemtype'";

         $DB->query($query);

         // Handle automatic activation
         
         if (CUSTOMFIELDS_AUTOACTIVATE) {
            plugin_customfields_activate_all($itemtype);
         }

         Session::addMessageAfterRedirect(
            __('Custom fields have been enabled for this device type', 'customfields')
         );
      }
      
      Html::back();

   }

   if (isset($_POST['disable'])) {

      // Disable custom fields for this device type

      plugin_customfields_disable_device($itemtype);
      
      Html::back();

   } else if (isset($_POST['delete'])) {

      // Delete a field

      foreach ($_POST['delete'] as $id => $garbage) {

         // Get info about the field to delete

         $sql         = "SELECT *
                         FROM `glpi_plugin_customfields_fields`
                         WHERE `itemtype` = '$itemtype'
                               AND `id` = '" . intval($id) . "'";
         $result      = $DB->query($sql);
         $data        = $DB->fetch_assoc($result);
         $system_name = $data['system_name'];

         // 5200 is the beginning of the range reserved for customfields

         $sopt_pos    = $data['sopt_pos'] + 5200;

         // Check if the field is in the history log

         $sql    = "SELECT COUNT(`id`) AS history_found
                    FROM `glpi_logs`
                    WHERE `itemtype` = '$itemtype'
                          AND `id_search_option` = '$sopt_pos'";
         $result = $DB->query($sql);
         $data   = $DB->fetch_assoc($result);
         
         if ($data['history_found']) {

            // Keep a record of the deleted field for the log

            $sql = "UPDATE `glpi_plugin_customfields_fields`
                    SET `deleted` = 1,
                        `system_name` = 'DELETED',
                        `sort_order` = 0,
                        `dropdown_table` = ''
                    WHERE `itemtype` = '$itemtype'
                          AND `id` = '" . intval($id) . "'
                          AND `system_name` = '$system_name'";

         } else {

            // Nothing in the history log, so delete the field completely

            $sql = "DELETE
                    FROM `glpi_plugin_customfields_fields`
                    WHERE `itemtype` = '$itemtype'
                          AND `id` = '" . intval($id) . "'
                          AND `system_name` = '$system_name'";

         }

         // Remove the custom field from the data table

         $result = $DB->query($sql);
         $table  = plugin_customfields_table($itemtype);
         
         $sql    = "SELECT COUNT(`id`) AS num_left
                 FROM `glpi_plugin_customfields_fields`
                 WHERE `itemtype` = '$itemtype'
                       AND `data_type` <> 'sectionhead'
                       AND `deleted` = 0";
         $result = $DB->query($sql);
         $data   = $DB->fetch_assoc($result);

         if ($data['num_left'] == 0) {

            // If no more fields, drop the data table

            $sql = "DROP TABLE IF EXISTS `$table`";

            // ...and disable the device

            plugin_customfields_disable_device($itemtype);

         } else {

            // Remove the column from the data table

            $sql = "ALTER TABLE `$table`
                    DROP `$system_name`";

         }

         $result = $DB->query($sql);

      }

      // Done. Reload.

      Html::redirect($_SERVER['HTTP_REFERER']);
      
   } else if (isset($_POST['add'])) {

      // Add a field

      $data_ok = false;
      $sort    = intval($_POST['sort']);
      
      if (isset($_POST['dropdown_id'])) {

         // Add a drop down menu

         // Find more information about the dropdown

         $sql = "SELECT *
                 FROM `glpi_plugin_customfields_dropdowns`
                 WHERE `id` = '" . intval($_POST['dropdown_id']) . "'";
         
         if ($result = $DB->query($sql)) {
            $data        = $DB->fetch_assoc($result);
            $system_name = $data['system_name'];
            $label       = Toolbox::addslashes_deep($data['name']);
            $dd_table    = $data['dropdown_table'];
            $data_type   = 'dropdown';
            $data_ok     = true;
         }
         
      } else {

         // Add a normal field

         if (isset($_POST['clonedata'])) {

            // Clone field

            list($system_name, $data_type, $label) = explode(
               ',',
               $_POST['clonedata'],
               3
            );

            $system_name = plugin_customfields_make_system_name($system_name);

            // clean up in case of tampering

         } else {

            $label = ($_POST['label'] != '')
               ? $_POST['label']
               : __('Custom Field', 'customfields');

            if ($_POST['system_name'] == '') {

               // If the system name was left blank, use the label

               $system_name = plugin_customfields_make_system_name($label);

            } else {

               $system_name = plugin_customfields_make_system_name(
                  $_POST['system_name']
               );

            }

            $data_type = $_POST['data_type'];

         }

         $dd_table = '';
         $extra    = '';
         
         $maintable = getTableForItemType($itemtype);
         
         do {

            // Make sure the field name is not already used

            $sql    = "SELECT `system_name`
                    FROM `glpi_plugin_customfields_fields`
                    WHERE `itemtype` = '$itemtype'
                          AND `deleted` = 0
                          AND `system_name` = '$system_name$extra'
                    UNION
                    SELECT `system_name`
                    FROM `glpi_plugin_customfields_dropdowns`
                    WHERE `system_name` = '$system_name$extra'";
            $result = $DB->query($sql);
            
            if ($DB->numrows($result) == 0) {
               $sql    = "SHOW COLUMNS
                          FROM `$maintable`
                          WHERE Field='$system_name$extra'";
               $result = $DB->query($sql);
            }
            $extra = $extra + 1;

         } while ($DB->numrows($result) > 0);
         
         if ($extra > 1) { // We need to append a number to make it unique
            $system_name = $system_name . ($extra - 1);
         }

         $data_ok = true;

      }
      
      if ($data_ok) {

         // Get next search option position

         $sql = "SELECT MAX(`sopt_pos`)+1 AS next_sopt_pos
                 FROM `glpi_plugin_customfields_fields`
                 WHERE `itemtype` = '$itemtype'";
         
         $result   = $DB->query($sql);
         $data     = $DB->fetch_assoc($result);
         $sopt_pos = $data['next_sopt_pos'];

         if (!$sopt_pos) {
            $sopt_pos = 1;
         }

         // Insert field
         
         $sql    = "INSERT INTO `glpi_plugin_customfields_fields`
                        (`itemtype`, `system_name`, `label`, `data_type`,
                         `sort_order`, `dropdown_table`, `deleted`,
                         `sopt_pos`, `restricted`)
                    VALUES ('$itemtype', '$system_name', '$label',
                            '$data_type', '$sort', '$dd_table', 0,
                            '$sopt_pos', 0)";

         $result = $DB->query($sql);
         
         if ($data_type != 'sectionhead') {

            // add the field to the data table if it isn't a section header

            $table = plugin_customfields_table($itemtype);
            
            if (CUSTOMFIELDS_AUTOACTIVATE) {

               // creates table and activates IF necessary

               plugin_customfields_activate_all($itemtype);

            } else {

               // creates table if it doesn't alreay exist

               plugin_customfields_create_data_table($itemtype);

            }
            
            switch ($data_type) {
               case 'general':
                  $db_data_type =
                     'VARCHAR(255) collate utf8_unicode_ci default NULL';
                  break;
               
               case 'yesno':
                  $db_data_type = 'SMALLINT(6) NOT NULL default \'0\'';
                  break;
               
               case 'text':
                  $db_data_type = 'TEXT collate utf8_unicode_ci';
                  break;
               
               case 'notes':
                  $db_data_type = 'LONGTEXT collate utf8_unicode_ci';
                  break;
               
               case 'date':
                  $db_data_type = 'DATE default NULL';
                  break;
               
               case 'money':
                  $db_data_type = 'DECIMAL(20,4) NOT NULL default \'0.0000\'';
                  break;
               
               case 'dropdown':
               case 'number':
               default:
                  $db_data_type = 'INT(11) NOT NULL default \'0\'';
                  break;
            }

            // Add column to the data table
            
            $sql    = "ALTER TABLE `$table`
                    ADD `$system_name` $db_data_type;";
            $result = $DB->query($sql);
         }
      }

      // Done. Reload.

      Html::redirect($_SERVER['HTTP_REFERER']);
      
   } else if (isset($_POST['update'])) {

      // Update labels, sort order, etc.

      $query  = "SELECT *
                FROM `glpi_plugin_customfields_fields`
                WHERE `itemtype` = '$itemtype'
                      AND `deleted` = 0
                ORDER BY `sort_order`";
      $result = $DB->query($query);
      
      while ($data = $DB->fetch_assoc($result)) {
         $ID         = $data['id'];
         $label      = $_POST['label'][$ID];
         $sort       = intval($_POST['sort'][$ID]);
         $required   = isset($_POST['required'][$ID]) ? 1 : 0;
         $entities   = trim($_POST['entities'][$ID]);
         $restricted = isset($_POST['restricted'][$ID]) ? 1 : 0;

         // Update field informations

         $sql = "UPDATE `glpi_plugin_customfields_fields`
                 SET `label` = '$label',
                     `sort_order` = '$sort',
                     `required` = '$required',
                     `entities` = '$entities',
                     `restricted` = '$restricted'
                 WHERE `itemtype` = '$itemtype'
                       AND `id` = '$ID'";
         $DB->query($sql);

         // Alter table column if needed

         if ($restricted == 1 && $data['restricted'] == 0) {
            $sql = "ALTER TABLE `glpi_plugin_customfields_profiles`
                    ADD `{$itemtype}_{$data['system_name']}`
                    char(1) default NULL";
            $DB->query($sql);
         } else if ($restricted == 0 && $data['restricted'] == 1) {
            $sql = "ALTER TABLE `glpi_plugin_customfields_profiles`
                    DROP `{$itemtype}_{$data['system_name']}`";
            $DB->query($sql);
         }
      }

      // Done. Reload.

      Html::back();
   }

   // ** OUTPUT ** //

   // Header
   
   echo '<div class="center">';
   
   echo '<a href="./config.form.php">'
   		. __('Back to Manage Custom Fields', 'customfields')
   		. '</a><br><br>';
      
   echo '<form action="?itemtype=' . $itemtype . '" method="post">';
   echo '<table class="tab_cadre" cellpadding="5">';
   echo '<tr><th colspan="8">' . __('Title', 'customfields') . ' (' . call_user_func(array(
      $itemtype,
      'getTypeName'
   )) . ')</th></tr>';
   echo '<tr>';
   echo '<th>' . __('Label', 'customfields') . '</th>';
   echo '<th>' . __('System Name', 'customfields') . '</th>';
   echo '<th>' . __('Type', 'customfields') . '</th>';
   echo '<th>' . __('Sort', 'customfields') . '</th>';
   echo '<th>' . __('Required', 'customfields') . '</th>';
   echo '<th>' . __('Entities', 'customfields') . '</th>';
   echo '<th></th>';
   echo '</tr>';

   // Get custom fields
   
   $query  = "SELECT *
              FROM `glpi_plugin_customfields_fields`
              WHERE `itemtype` = '$itemtype'
                    AND `deleted` = 0
              ORDER BY `sort_order`";
   $result = $DB->query($query);
   
   $numdatafields = 0;

   while ($data = $DB->fetch_assoc($result)) {

      $ID = $data['id'];
      echo '<tr class="tab_bg_1">';

      // Label

      echo '<td><input name="label['
         . $ID
         . ']" value="'
         . htmlspecialchars($data['label'])
         . '" size="20"></td>';

      // System name

      echo '<td>' . $data['system_name'] . '</td>';

      // Type

      echo '<td>' . __($data['data_type'], 'customfields') . '</td>';

      // Sort order

      echo '<td><input name="sort['
         . $ID
         . ']" value="'
         . $data['sort_order']
         . '" size="2"></td>';

      // Required

      if ($data['data_type'] != 'sectionhead') {
         echo '<td class="center"><input name="required['
            . $ID
            . ']" type="checkbox"';
         if ($data['required']) {
            echo ' checked="checked"';
         }
         echo '></td>';
      } else {
         echo '<td></td>';
      }

      // Entities

      echo '<td><input name="entities['
         . $ID
         . ']" value="'
         . $data['entities']
         . '" size="7"></td>';

      // Delete-Link

      echo '<td><input name="delete['
         . $ID
         . ']" class="submit" type="submit" value=\''
         . _sx('button', 'Delete permanently')
         . '\'></td>';

      echo '</tr>';

      if ($data['data_type'] != 'sectionhead') {
         $numdatafields++;
      }

   }

   // Update-link

   echo '<tr><td class="center top tab_bg_2" colspan="8">';
   if ($DB->numrows($result) > 0) {
      echo '<input type="submit" name="update" value=\'' . _sx('button', 'Save') . '\' class="submit"/>';
   } else {
      echo __('No custom field yet', 'customfields');
   }
   echo '</td></tr>';
   echo '</table>';
   Html::closeForm();

   // Form to add fields

   // Header

   echo '<br><form action="?itemtype=' . $itemtype . '" method="post">';
   echo '<table class="tab_cadre" cellpadding="5">';
   echo '<tr><th colspan="5">'
      . __('Add New Field', 'customfields')
      . '</th></tr>';
   echo '<tr>';
   echo '<th>' . __('Label', 'customfields') . '</th>';
   echo '<th>' . __('System Name', 'customfields') . '</th>';
   echo '<th>' . __('Type', 'customfields') . '</th>';
   echo '<th>' . __('Sort', 'customfields') . '</th>';
   echo '<th></th>';
   echo '</tr>';
   echo '<tr class="tab_bg_1">';

   // Label

   echo '<td><input name="label" size="20"></td>';

   // System name

   echo '<td><input name="system_name"></td>';

   // Type

   echo '<td><select name="data_type">';
   echo '<option value="general">'
      . __('general', 'customfields')
      . '</option>';
   echo '<option value="text">'
      . __('text_explained', 'customfields')
      . '</option>';
   echo '<option value="notes">'
      . __('notes_explained', 'customfields')
      . '</option>';
   echo '<option value="date">'
      . __('date', 'customfields')
      . '</option>';
   echo '<option value="number">'
      . __('number', 'customfields')
      . '</option>';
   echo '<option value="money">'
      . __('money', 'customfields')
      . '</option>';
   echo '<option value="yesno">'
      . __('yesno', 'customfields')
      . '</option>';
   echo '<option value="sectionhead">'
      . __('sectionhead', 'customfields')
      . '</option>';
   echo '</select></td>';

   // Sort

   echo '<td><input name="sort" size="2"></td>';

   // Submit

   echo '<td><input name="add" class="submit" type="submit" value=\''
      . _sx('button', 'Add')
      . '\'></td>';
   echo '</tr>';
   echo '</table>';

   Html::closeForm();
   
   // Show clone field form if there are any fields that can be cloned

   $query  = "SELECT DISTINCT `system_name`, `data_type`, `label`
              FROM `glpi_plugin_customfields_fields`
              WHERE `data_type` <> 'dropdown'
                    AND `itemtype` <> '$itemtype'
                    AND `deleted` = 0
                    AND `system_name` NOT IN (
                       SELECT `system_name`
                       FROM `glpi_plugin_customfields_fields`
                       WHERE `itemtype` = '$itemtype'
                             AND `deleted` = 0)
              ORDER BY `label`";
   $result = $DB->query($query);
   
   if ($DB->numrows($result) > 0) {

      // Header

      echo '<br><form action="?itemtype=' . $itemtype . '" method="post">';
      echo '<table class="tab_cadre" cellpadding="5">';
      echo '<tr><th colspan="4">' . __('Clone Field', 'customfields') . '</th></tr>';
      echo '<tr>';
      echo '<th>' . __('Field', 'customfields') . '</th>';
      echo '<th>' . __('Sort', 'customfields') . '</th>';
      echo '<th></th>';
      echo '</tr>';
      echo '<tr class="tab_bg_1">';
      echo '<td><select name="clonedata">';

      // Selection of field to clone

      while ($data = $DB->fetch_assoc($result)) {
         echo '<option value="'
            . $data['system_name']
            . ',' . $data['data_type']
            . ',' . htmlspecialchars($data['label'])
            . '">'
            . $data['label']
            . ' ('
            . $data['system_name']
            . ') - '
            . __($data['data_type'], 'customfields')
            . '</option>';
      }
      echo '</select></td>';

      // Sort

      echo '<td><input name="sort" size="2"></td>';

      // Add-Link

      echo '<td><input name="add" class="submit" type="submit" value=\''
         . _sx('button', 'Add')
         . '\'></td>';
      echo '</tr>';
      echo '</table>';

      Html::closeForm();

   }
   
   // Form to add drop down menus

   $query  = "SELECT dd.*
              FROM `glpi_plugin_customfields_dropdowns` AS dd
              LEFT JOIN `glpi_plugin_customfields_fields` AS more
              ON (more.`dropdown_table` = dd.`dropdown_table`
                   AND more.`itemtype` = '$itemtype'
                   AND more.`deleted` = 0)
              WHERE more.`id` IS NULL
              ORDER BY dd.`name`";

   $result = $DB->query($query);
   
   if ($DB->numrows($result) > 0) {

      // Header

      echo '<br><form action="?itemtype=' . $itemtype . '" method="post">';
      echo '<table class="tab_cadre" cellpadding="5">';
      echo '<tr><th colspan="3"><a href="./dropdown.php">'
         . __('Add Custom Dropdown','customfields')
         . '</a></th></tr>';
      echo '<tr>';
      echo '<th>' . __('Dropdown Name','customfields') . '</th>';
      echo '<th>' . __('Sort','customfields') . '</th>';
      echo '<th></th>';
      echo '</tr>';

      echo '<tr class="tab_bg_1">';

      // Dropdown selection

      echo '<td><select name="dropdown_id">';
      while ($data = $DB->fetch_assoc($result)) {
         echo '<option value="'
            . $data['id']
            . '">'
            . $data['name']
            . '</option>';
      }
      echo '</select></td>';

      // Sort

      echo '<td><input name="sort" value="'
         . $data['sort_order']
         . '" size="2"></td>';

      // Add

      echo '<td><input name="add" class="submit" type="submit" value=\''
         . _sx('button', 'Add')
         . '\'></td>';
      echo '</tr>';
      echo '</table>';

      Html::closeForm();
      
   } else {

      // Link to Management of custom dropdowns

      echo '<br><a href="./dropdown.php">'
         . __('Add Custom Dropdown','customfields')
         . '</a><br>';

   }
   
   // Form to enable or disable custom fields for this device type

   $query  = "SELECT *
              FROM `glpi_plugin_customfields_itemtypes`
              WHERE `itemtype` = '$itemtype'";

   $result = $DB->query($query);
   $data   = $DB->fetch_assoc($result);

   // Header

   echo '<br><form action="?itemtype=' . $itemtype . '" method="post">';
   echo '<table class="tab_cadre" cellpadding="5">';
   echo '<tr class="tab_bg_1"><th>'
      . __('Status of Custom Fields','customfields')
      . ': </th><td>';

   if ($data['enabled'] == 1) {

      // It's enabled, display the "Disable"-Action

      echo __('Enable','customfields')
         . '</td>'
         . '<td><input class="submit" type="submit" name="disable" value=\''
         . __('Disable','customfields')
         . '\'>';

   } else {

      // Disabled. Display the "Enable"-Action...

      echo '<span style="color:#f00;font-weight:bold;">'
         . __('Disabled','customfields')
         . '</span></td>';

      // ...if there are some fields

      if ($numdatafields > 0) {

         echo '<td><input class="submit" type="submit" name="enable" value=\''
            . __('Enable','customfields')
            . '\'>';

      } else {

         echo '</tr><tr><td class="tab_bg_2" colspan="2">'
            . __('Custom Dropdown','customfields');

      }
   }

   echo '</td></tr>';
   echo '</table>';

   Html::closeForm();
   echo '</div>';
}

Html::footer();