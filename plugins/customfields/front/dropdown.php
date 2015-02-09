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
// Purpose of file: Page used to configure custom dropdown menus
// ----------------------------------------------------------------------

include ('../../../inc/includes.php');

define('DROPDOWN_EMPTY_VALUE', '');

// ACL-Check

Session::checkRight('config', READ);

// Header

Html::header(
   __('Manage Custom Dropdowns', 'customfields'),
   '',
   'config',
   'plugin',
   'customfields'
);

// Are we in read-only mode?

$haveright = Session::haveRight('config', UPDATE);

// ** ACTIONS ** //

if ($haveright) {

   /**
    * DELETE
    * ------
    */

   if (isset($_POST['delete'])) {

      foreach ($_POST['delete'] as $ID => $garbage) {

         // Get information about the object

         $sql = "SELECT *
                 FROM `glpi_plugin_customfields_dropdowns`
                 WHERE `id` = '" . intval($ID) . "'";

         $result = $DB->query($sql);
         $data = $DB->fetch_assoc($result);
         $system_name = $data['system_name'];
         $table = $data['dropdown_table'];

         // Delete the object
         
         $sql = "DELETE
                  FROM `glpi_plugin_customfields_dropdowns`
                  WHERE `id` = '" . intval($ID) . "'
                        AND `system_name` = '$system_name'";

         $DB->query($sql);

      }

      // Finished. Reload the page.

      Html::redirect($_SERVER['HTTP_REFERER']);
      
   } else if (isset($_POST['add'])) {

      /**
       * ADD
       * ---
       */

      // Sanity checks

      $has_entities = isset($_POST['has_entities']) ? 1 : 0;
      $is_tree      = isset($_POST['is_tree']) ? 1 : 0;

      // Generate a system name
      
      $name = ($_POST['name'] != '')
         ? $_POST['name']
         : __('Custom Dropdown', 'customfields');
      
      if ($_POST['system_name'] == '') {

         $system_name = plugin_customfields_make_system_name($name);

      } else {

         $system_name = plugin_customfields_make_system_name(
            $_POST['system_name']
         );

      }

      // Find a postfix for the system name

      $extra = 0;

      do {

         $sql    = "SELECT `system_name`
                  FROM `glpi_plugin_customfields_fields`
                  WHERE `system_name` = '$system_name$extra'
                  UNION
                  SELECT `system_name`
                  FROM `glpi_plugin_customfields_dropdowns`
                  WHERE `system_name` = '$system_name$extra';";

         $result = $DB->query($sql);

         $extra  = $extra + 1;

         // keep looping until a name for the field is found that
         // isn't already used

      } while ($DB->numrows($result) > 0);

      $system_name = $system_name . ($extra - 1);

      // Link to table from virtual class

      $table = "glpi_dropdown_plugin_customfields_$system_name";
         
      // Save the meta data

      $sql = "INSERT INTO `glpi_plugin_customfields_dropdowns`
              (`system_name`, `name`, `has_entities`, `is_tree`,
               `dropdown_table`)
              VALUES ('$system_name', '$name', '$has_entities', '$is_tree',
             '$table')";
      $DB->query($sql);

      // Done. Reload
         
      Html::redirect($_SERVER['HTTP_REFERER']);
      
   } else if (isset($_POST['update'])) {

      /**
       * UPDATE
       * ------
       */

      foreach ($_POST['name'] as $ID => $name) {

         $sql  = "UPDATE `glpi_plugin_customfields_dropdowns`
                 SET `name` = '$name'
                 WHERE `id` = '$ID'";
         $DB->query($sql);

      }

      // Done. Reload

      Html::redirect($_SERVER['HTTP_REFERER']);
   }
}

// ** OUTPUT ** //

// Header

echo '<div class="center">';

echo '<a href="./config.form.php">'
   . __('Back to Manage Custom Fields', 'customfields')
   . '</a><br><br>';

echo '<form action="#" method="post">';
echo '<table class="tab_cadre" cellpadding="5">';
echo '<tr><th colspan="6">'
   . __('Manage Custom Dropdowns', 'customfields')
   . '</th></tr>';
echo '<tr>';
echo '<th>' . __('Label', 'customfields') . '</th>';
echo '<th>' . __('System Name', 'customfields') . '</th>';
echo '<th></th>';
echo '<th></th>';
echo '</tr>';

// Table content

$query  = "SELECT dd.*, COUNT(linked.`id`) AS num_links
          FROM `glpi_plugin_customfields_dropdowns` AS dd
          LEFT JOIN `glpi_plugin_customfields_fields` AS linked
               ON (linked.`dropdown_table` = dd.`dropdown_table`)
          GROUP BY dd.`id`
          ORDER BY `name`";
$result = $DB->query($query);

while ($data = $DB->fetch_assoc($result)) {

   $ID = $data['id'];

   echo '<tr class="tab_bg_1">';

   // Label of dropdown

   echo '<td><input name="name['
      . $ID
      . ']" value="'
      . htmlspecialchars($data['name'])
      . '" size="20"></td>';

   // Internal name

   echo '<td>' . $data['system_name'] . '</td>';
   echo '<td class="center">';

   if ($data['num_links'] == 0 && $haveright) {

      // Show "delete" link, when ACL is right and no usages

      echo '<input name="delete['
         . $ID
         . ']" class="submit" type="submit" value=\''
         . _sx('button', 'Delete permanently')
         . '\'>';

   } else {

      // Show usages

      echo sprintf(_n('Used by %1$s device', 'Used by %1$s devices', $data['num_links'], 'customfields'), $data['num_links']);

   }

   echo '</td>';
   
   // show Search/Add-Link to manage dropdown fields

   echo '<td>';
   
   $item    = new PluginCustomfieldsDropdown();
   $table   = $item->getTable();
   $rand    = mt_rand();
   $name    = Dropdown::EMPTY_VALUE;
   $comment = "";
   $tmpname = Dropdown::getDropdownName($table, '', 1);

   if ($tmpname["name"] != "&nbsp;") {
      $name    = $tmpname["name"];
      $comment = $tmpname["comment"];
   }

   // Manage items
   
   $options_tooltip['link'] = $CFG_GLPI["root_doc"]
      . '/plugins/customfields/front/dropdownsitem.php';
   $options_tooltip['linktarget'] = '_blank';
   Html::showToolTip($comment, $options_tooltip);

   // Add item

   $itemFormUrl = $CFG_GLPI["root_doc"]
      . '/plugins/customfields/front/dropdownsitem.form.php'
      . "?popup=1&amp;rand="
      . $rand
      . "&amp;plugin_customfields_dropdowns_id="
      . $ID;
   
   echo "<img alt='' title=\""
      . _sx('button', 'Add')
      . "\" src='"
      . $CFG_GLPI["root_doc"] .
      "/pics/add_dropdown.png' style='cursor:pointer; margin-left:2px;'"
      . "onClick=\"var w = window.open('"
      . $itemFormUrl
      . "' ,'glpipopup', 'height=400, "
      . "width=1000, top=100, left=100, scrollbars=yes' );w.focus();\">";
   echo '</td></tr>';

}

if ($haveright) {

   // Update link

   echo '<tr><td class="center top tab_bg_2" colspan="6">';

   if ($DB->numrows($result) > 0) {

      echo '<input type="submit" name="update" value=\''
         . _sx('button', 'Update')
         . '\' class="submit"/>';

   } else {

      echo __('No custom dropdowns defined yet', 'customfields');

   }

   echo '</td></tr>';
}

echo '</table>';
Html::closeForm();

if ($haveright) {

   // Add Dropdown

   echo '<br><form action="#" method="post">';
   echo '<table class="tab_cadre" cellpadding="4">';
   echo '<tr><th colspan="3">'
      . __('Add New Dropdown', 'customfields')
      . '</th></tr>';
   echo '<tr>';
   echo '<th>' . __('Label', 'customfields') . '</th>';
   echo '<th>' . __('System Name', 'customfields') . '</th>';
   echo '<th/>';
   echo '</tr>';
   
   echo '<tr class="tab_bg_1">';

   // Label

   echo '<td><input name="name" size="20"></td>';

   // System name (internal name)

   echo '<td><input name="system_name"></td>';

   echo '<td><input name="add" class="submit" type="submit" value=\''
      . _sx('button', 'Add')
      . '\'></td>';
   echo '</tr>';
   echo '</table>';

   Html::closeForm();
}

echo '</div>';

Html::footer();
