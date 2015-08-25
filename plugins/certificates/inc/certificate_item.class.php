<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Certificates plugin for GLPI
 Copyright (C) 2003-2011 by the certificates Development Team.

 https://forge.indepnet.net/projects/certificates
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of certificates.

 Certificates is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Certificates is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Certificates. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginCertificatesCertificate_Item extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1 = "PluginCertificatesCertificate";
   static public $items_id_1 = 'plugin_certificates_certificates_id';
   static public $take_entity_1 = false ;

   static public $itemtype_2 = 'itemtype';
   static public $items_id_2 = 'items_id';
   static public $take_entity_2 = true ;
   
   static $rightname = "plugin_certificates";

   
   static function cleanForItem(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
         array('itemtype' => $item->getType(),
               'items_id' => $item->getField('id'))
      );
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate) {
         if ($item->getType()=='PluginCertificatesCertificate'
             && count(PluginCertificatesCertificate::getTypes(false))) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(_n('Associated item','Associated items',2), self::countForCertificate($item));
            }
            return _n('Associated item','Associated items',2);

         } else if (in_array($item->getType(), PluginCertificatesCertificate::getTypes(true))
                    && Session::haveRight("plugin_certificates", READ)) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginCertificatesCertificate::getTypeName(2), self::countForItem($item));
            }
            return PluginCertificatesCertificate::getTypeName(2);
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='PluginCertificatesCertificate') {
         
         self::showForCertificate($item);

      } else if (in_array($item->getType(), PluginCertificatesCertificate::getTypes(true))) {

         self::showForItem($item);

      }
      return true;
   }
   
   static function countForCertificate(PluginCertificatesCertificate $item) {

      $types = implode("','", $item->getTypes());
      if (empty($types)) {
         return 0;
      }
      return countElementsInTable('glpi_plugin_certificates_certificates_items',
                                  "`itemtype` IN ('$types')
                                   AND `plugin_certificates_certificates_id` = '".$item->getID()."'");
   }


   static function countForItem(CommonDBTM $item) {

      return countElementsInTable('glpi_plugin_certificates_certificates_items',
                                  "`itemtype`='".$item->getType()."'
                                   AND `items_id` = '".$item->getID()."'");
   }
   
   function getFromDBbyCertificatesAndItem($plugin_certificates_certificates_id,$items_id,$itemtype) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->getTable()."` " .
         "WHERE `plugin_certificates_certificates_id` = '" . $plugin_certificates_certificates_id . "' 
         AND `itemtype` = '" . $itemtype . "'
         AND `items_id` = '" . $items_id . "'";
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
   
   function addItem($values) {

      $this->add(array('plugin_certificates_certificates_id'=>$values["plugin_certificates_certificates_id"],
                        'items_id'=>$values["items_id"],
                        'itemtype'=>$values["itemtype"]));
    
   }
  
   function deleteItemByCertificatesAndItem($plugin_certificates_certificates_id,$items_id,$itemtype) {
    
      if ($this->getFromDBbyCertificatesAndItem($plugin_certificates_certificates_id,$items_id,$itemtype)) {
         $this->delete(array('id'=>$this->fields["id"]));
      }
   }

   /**
    * Show items links to a certificate
    *
    * @since version 0.84
    *
    * @param $certificate PluginCertificatesCertificate object
    *
    * @return nothing (HTML display)
    **/
   public static function showForCertificate(PluginCertificatesCertificate $certificate) {
      global $DB,$CFG_GLPI;
      
//      $certif=new PluginCertificatesCertificate();

      $instID = $certificate->fields['id'];
      if (!$certificate->can($instID, READ)){
         return false;
      }
      $canedit=$certificate->can($instID, UPDATE);
      $rand=mt_rand();

      $query = "SELECT DISTINCT `itemtype`
            FROM `glpi_plugin_certificates_certificates_items`
            WHERE `plugin_certificates_certificates_id` = '".$instID."'
            ORDER BY `itemtype`
            LIMIT ".count(PluginCertificatesCertificate::getTypes(true));

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (Session::isMultiEntitiesMode()) {
         $colsup=1;
      } else {
         $colsup=0;
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form method='post' name='certificates_form$rand'
         id='certificates_form$rand'  action='".Toolbox::getItemTypeFormURL("PluginCertificatesCertificate")."'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='".($canedit?(5+$colsup):(4+$colsup))."'>".
            __('Add an item')."</th></tr>";

         echo "<tr class='tab_bg_1'><td colspan='".(3+$colsup)."' class='center'>";
         Dropdown::showAllItems("items_id",0,0,($certificate->fields['is_recursive']?-1:$certificate->fields['entities_id']),
            PluginCertificatesCertificate::getTypes());
         echo "</td><td colspan='2' class='center' class='tab_bg_1'>";
         echo "<input type='hidden' name='plugin_certificates_certificates_id' value='$instID'>";
         echo "<input type='submit' name='additem' value=\""._sx('button','Add')."\" class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>" ;
      }

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array();
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";

      if ($canedit && $number) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }

      echo "<th>".__('Type')."</th>";
      echo "<th>".__('Name')."</th>";
      if (Session::isMultiEntitiesMode())
         echo "<th>".__('Entity')."</th>";
      echo "<th>".__('Serial number')."</th>";
      echo "<th>".__('Inventory number')."</th>";
      echo "</tr>";

      for ($i=0 ; $i < $number ; $i++) {
         $itemtype=$DB->result($result, $i, "itemtype");

         if (!($item = getItemForItemtype($itemtype))) {
            continue;
         }

         if ($item->canView()) {
            $column="name";

            $itemTable = getTableForItemType($itemtype);
            $query = " SELECT `".$itemTable."`.*,
                              `glpi_plugin_certificates_certificates_items`.`id` AS items_id,
                              `glpi_entities`.id AS entity "
                     ." FROM `glpi_plugin_certificates_certificates_items`, `".$itemTable
                     ."` LEFT JOIN `glpi_entities`
                     ON (`glpi_entities`.`id` = `".$itemTable."`.`entities_id`) "
                     ." WHERE `".$itemTable."`.`id` = `glpi_plugin_certificates_certificates_items`.`items_id`
                     AND `glpi_plugin_certificates_certificates_items`.`itemtype` = '$itemtype'
                     AND `glpi_plugin_certificates_certificates_items`.`plugin_certificates_certificates_id` = '$instID' "
                     . getEntitiesRestrictRequest(" AND ",$itemTable,'','',$item->maybeRecursive());

            if ($item->maybeTemplate()) {
               $query.=" AND ".$itemTable.".is_template='0'";
            }

            $query.=" ORDER BY `glpi_entities`.`completename`, `".$itemTable."`.`$column` ";

            if ($result_linked=$DB->query($query)){
               if ($DB->numrows($result_linked)) {

                  Session::initNavigateListItems($itemtype,PluginCertificatesCertificate::getTypeName(2)." = ".$certificate->fields['name']);

                  while ($data=$DB->fetch_assoc($result_linked)) {

                     $item->getFromDB($data["id"]);

                     Session::addToNavigateListItems($itemtype,$data["id"]);

                     $ID="";

                     if ($_SESSION["glpiis_ids_visible"]||empty($data["name"]))
                        $ID= " (".$data["id"].")";

                     $link=Toolbox::getItemTypeFormURL($itemtype);
                     $name= "<a href=\"".$link."?id=".$data["id"]."\">"
                        .$data["name"]."$ID</a>";

                     echo "<tr class='tab_bg_1'>";

                     if ($canedit) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox(__CLASS__, $data["items_id"]);
                        echo "</td>";
                     }
                     echo "<td class='center'>".$item->getTypeName(1)."</td>";

                     echo "<td class='center' ".(isset($data['is_deleted'])&&$data['is_deleted']?"class='tab_bg_2_2'":"").
                        ">".$name."</td>";
                     if (Session::isMultiEntitiesMode())
                        echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",$data['entity'])."</td>";
                     echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-")."</td>";
                     echo "<td class='center'>".(isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";

                     echo "</tr>";
                  }
               }
            }
         }
      }
      echo "</table>";

      if ($canedit && $number) {
         $paramsma['ontop'] =false;
         Html::showMassiveActions($paramsma);
         Html::closeForm();
      }
      echo "</div>";

   }
   
   /**
    * @since version 0.84
   **/
   function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }
   /**
    * Show certificates associated to an item
    *
    * @since version 0.84
    *
    * @param $item            CommonDBTM object for which associated certificates must be displayed
    * @param $withtemplate    (default '')
   **/
   static function showForItem(CommonDBTM $item, $withtemplate='') {
      global $DB, $CFG_GLPI;

      $ID = $item->getField('id');

      if ($item->isNewID($ID)) {
         return false;
      }
      if (!Session::haveRight("plugin_certificates", READ)) {
         return false;
      }

      if (!$item->can($item->fields['id'], READ)) {
         return false;
      }

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }

      $canedit       =  $item->canadditem('PluginCertificatesCertificate');
      $rand          = mt_rand();
      $is_recursive  = $item->isRecursive();

      $query = "SELECT `glpi_plugin_certificates_certificates_items`.`id` AS assocID,
                       `glpi_entities`.`id` AS entity,
                       `glpi_plugin_certificates_certificates`.`name` AS assocName,
                       `glpi_plugin_certificates_certificates`.*
                FROM `glpi_plugin_certificates_certificates_items`
                LEFT JOIN `glpi_plugin_certificates_certificates`
                 ON (`glpi_plugin_certificates_certificates_items`.`plugin_certificates_certificates_id`=`glpi_plugin_certificates_certificates`.`id`)
                LEFT JOIN `glpi_entities` ON (`glpi_plugin_certificates_certificates`.`entities_id`=`glpi_entities`.`id`)
                WHERE `glpi_plugin_certificates_certificates_items`.`items_id` = '$ID'
                      AND `glpi_plugin_certificates_certificates_items`.`itemtype` = '".$item->getType()."' ";

      $query .= getEntitiesRestrictRequest(" AND","glpi_plugin_certificates_certificates",'','',true);

      $query .= " ORDER BY `assocName`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i      = 0;

      $certificates      = array();
      $certificate       = new PluginCertificatesCertificate();
      $used      = array();
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $certificates[$data['assocID']] = $data;
            $used[$data['id']] = $data['id'];
         }
      }

      if ($canedit && $withtemplate < 2) {
         // Restrict entity for knowbase
         $entities = "";
         $entity   = $_SESSION["glpiactive_entity"];

         if ($item->isEntityAssign()) {
            /// Case of personal items : entity = -1 : create on active entity (Reminder case))
            if ($item->getEntityID() >=0 ) {
               $entity = $item->getEntityID();
            }

            if ($item->isRecursive()) {
               $entities = getSonsOf('glpi_entities',$entity);
            } else {
               $entities = $entity;
            }
         }
         $limit = getEntitiesRestrictRequest(" AND ","glpi_plugin_certificates_certificates",'',$entities,true);
         $q = "SELECT COUNT(*)
               FROM `glpi_plugin_certificates_certificates`
               WHERE `is_deleted` = '0'
               $limit";

         $result = $DB->query($q);
         $nb     = $DB->result($result,0,0);

         echo "<div class='firstbloc'>";       
         
         
         if (Session::haveRight("plugin_certificates", READ)
             && ($nb > count($used))) {
            echo "<form name='certificate_form$rand' id='certificate_form$rand' method='post'
                   action='".Toolbox::getItemTypeFormURL('PluginCertificatesCertificate')."'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='4' class='center'>";
            echo "<input type='hidden' name='entities_id' value='$entity'>";
            echo "<input type='hidden' name='is_recursive' value='$is_recursive'>";
            echo "<input type='hidden' name='itemtype' value='".$item->getType()."'>";
            echo "<input type='hidden' name='items_id' value='$ID'>";
            if ($item->getType() == 'Ticket') {
               echo "<input type='hidden' name='tickets_id' value='$ID'>";
            }

            PluginCertificatesCertificate::dropdownCertificate(array('entity' => $entities ,
                                                                     'used'   => $used));
                                     
            echo "</td><td class='center' width='20%'>";
            echo "<input type='submit' name='additem' value=\"".
                     _sx('button', 'Associate a certificate', 'certificates')."\" class='submit'>";
            echo "</td>";
            echo "</tr>";
            echo "</table>";
            Html::closeForm();
         }

         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number && ($withtemplate < 2)) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array('num_displayed'  => $number);
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr>";
      if ($canedit && $number && ($withtemplate < 2)) {
         echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
      }
      echo "<th>".__('Name')."</th>";
      if (Session::isMultiEntitiesMode()) {
         echo "<th>".__('Entity')."</th>";
      }
      echo "<th>".__('Type')."</th>";
      echo "<th>".__('DNS name', 'certificates')."</th>";
      echo "<th>".__('DNS suffix', 'certificates')."</th>";
      echo "<th>".__('Creation date')."</th>";
      echo "<th>".__('Expiration date')."</th>";
      echo "<th>".__('Status')."</th>";
      echo "</tr>";
      $used = array();

      if ($number) {

         Session::initNavigateListItems('PluginCertificatesCertificate',
                           //TRANS : %1$s is the itemtype name,
                           //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                $item->getTypeName(1), $item->getName()));

         
         foreach  ($certificates as $data) {
            $certificateID        = $data["id"];
            $link         = NOT_AVAILABLE;

            if ($certificate->getFromDB($certificateID)) {
               $link         = $certificate->getLink();
            }

            Session::addToNavigateListItems('PluginCertificatesCertificate', $certificateID);
            
            $used[$certificateID] = $certificateID;
            $assocID      = $data["assocID"];

            echo "<tr class='tab_bg_1".($data["is_deleted"]?"_2":"")."'>";
            if ($canedit && ($withtemplate < 2)) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["assocID"]);
               echo "</td>";
            }
            echo "<td class='center'>$link</td>";
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $data['entities_id']).
                    "</td>";
            }
            echo "<td class='center'>";
            echo Dropdown::getDropdownName("glpi_plugin_certificates_certificatetypes",
                                          $data["plugin_certificates_certificatetypes_id"]);
            echo "</td>";
            echo "<td class='center'>".$data["dns_name"]."</td>";
            echo "<td class='center'>".$data["dns_suffix"]."</td>";
            echo "<td class='center'>".Html::convdate($data["date_query"])."</td>";
            if ($data["date_expiration"] <= date('Y-m-d') 
                  && !empty($data["date_expiration"])) {
               echo "<td class='center'>";
               echo "<div class='deleted'>".Html::convdate($data["date_expiration"])."</div>";
               echo "</td>";
            } else if (empty($data["date_expiration"])) {
               echo "<td class='center'>".__('Does not expire', 'certificates')."</td>";
            } else {
               echo "<td class='center'>".Html::convdate($data["date_expiration"])."</td>";
            }
            echo "<td class='center'>";
            echo Dropdown::getDropdownName("glpi_plugin_certificates_certificatestates",
                                                $data["plugin_certificates_certificatestates_id"]);
            echo "</td>";
            echo "</tr>";
            $i++;
         }
      }


      echo "</table>";
      if ($canedit && $number && ($withtemplate < 2)) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }
}

?>