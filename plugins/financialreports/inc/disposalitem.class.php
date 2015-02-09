<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Financialreports plugin for GLPI
 Copyright (C) 2003-2011 by the Financialreports Development Team.

 https://forge.indepnet.net/projects/financialreports
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Financialreports.

 Financialreports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Financialreports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Financialreports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginFinancialreportsDisposalItem extends CommonDBTM {
   
   static $types = array(
         'Computer','Monitor','NetworkEquipment','Peripheral',
         'Phone','Printer'
         );
   
   static function getTypeName($nb = 0) {

      return _n('Disposal date', 'Disposal dates', $nb, 'financialreports');
   }

   
   /**
    * For other plugins, add a type to the linkable types
    *
    *
    * @param $type string class name
   **/
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }


   /**
    * Type than could be linked to a report
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
   **/
   static function getTypes($all=false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (in_array($item->getType(), self::getTypes(true))
               && Session::haveRight("plugin_financialreports", READ)) {

         return PluginFinancialreportsFinancialreport::getTypeName();
      }

      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;
      
      if (in_array($item->getType(), self::getTypes(true))) {
         $self = new self();
         $self->showForm(get_class($item),$item->getField('id'));
         
      }
      return true;
   }
  
   function getFromDBbyItem($items_id,$itemtype) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->getTable()."`
               WHERE `items_id` = '" . $items_id . "' 
               AND `itemtype` = '" . $itemtype . "'";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }
   
   function addDateDisposal($items_id,$itemtype,$date_disposal) {
      global $DB;
    
      if ($this->getFromDBbyItem($items_id,$itemtype)) {

         $this->update(array(
        'id'=>$this->fields['id'],
        'date_disposal'=>$date_disposal));
        
        return true;
        
      } else {

         $this->add(array(
        'items_id'=>$items_id,
        'itemtype'=>$itemtype,
        'date_disposal'=>$date_disposal));
        
         return true;
      }
      
      return false;
   }
  
   function showForm($type,$device) {
      global $DB,$CFG_GLPI;
      
      $PluginFinancialreportsFinancialreport= new PluginFinancialreportsFinancialreport();
      $canedit=$PluginFinancialreportsFinancialreport->canView();

      $query = "SELECT *
      FROM `".$this->getTable()."`
      WHERE `itemtype` = '".$type."'
      AND `items_id` = '".$device."'";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/financialreports/front/financialreport.php\">";
      echo "<div align='center'><table class=\"tab_cadre_fixe\"  cellspacing=\"2\" cellpadding=\"2\">";
      echo "<tr><th colspan='3'>".PluginFinancialreportsFinancialreport::getTypeName()."</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td class='left'>";
      echo __('Indicate the date of disposal', 'financialreports')."</td><td class='left'>";
      if ($number ==1) {
         while($line=$DB->fetch_array($result)) {
            $ID=$line["id"];
            Html::showDateFormItem("date_disposal",$line["date_disposal"],true,true);
            echo "</td>";
            if ($canedit) {
               echo "<td class='center' class='tab_bg_2'>";
               Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/financialreports/front/financialreport.php',
                                    'delete_date',
                                    _x('button', 'Delete permanently'),
                                    array('id' => $ID));
               echo "</td>";
            }
         }
      } else if ($canedit) {
         Html::showDateFormItem("date_disposal","",true,true);
         echo "</td>";
         echo "</tr>";
         echo "<tr><th colspan='3'>";
         echo "<input type='hidden' name='items_id' value='".$device."'>";
         echo "<input type='hidden' name='itemtype' value='".$type."'>";
         echo "<input type=\"submit\" name=\"add_date\" class=\"submit\" value='". _sx('button', 'Post')."'>";
         echo "</th></tr>";
      }
      echo "</table></div>";
      Html::closeForm();
   }
   
   /**
    * @since version 0.85
    *
    * @see CommonDBTM::getSpecificMassiveActions()
   **/
   function getSpecificMassiveActions($checkitem=NULL) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      return $actions;
   }


   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case 'plugin_financialreports_add_date_disposal':
            Html::showDateFormItem("date_disposal","",true,true);
            echo "&nbsp;".
                 Html::submit(_x('button','Post'), array('name' => 'massiveaction'));
            return true;
    }
      return parent::showMassiveActionsSubForm($ma);
   }


   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {
      global $DB;
      
       $disposal = new PluginFinancialreportsDisposalItem();
      
      switch ($ma->getAction()) {
         case "plugin_financialreports_add_date_disposal":
            $input = $ma->getInput();
            foreach ($ids as $id) {
               if ($disposal->addDateDisposal($id, $item->getType(), $input['date_disposal'])) {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
               }
            }

            return;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }
   
}
?>