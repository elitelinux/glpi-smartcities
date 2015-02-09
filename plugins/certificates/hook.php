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

function plugin_certificates_install() {
   global $DB;
   
   include_once (GLPI_ROOT."/plugins/certificates/inc/profile.class.php");
   
   $install=false;
   $update78=false;
   $update80=false;
   
   if (!TableExists("glpi_plugin_certificates") && !TableExists("glpi_plugin_certificates_certificatetypes")) {
      
      $install=true;
      $DB->runFile(GLPI_ROOT ."/plugins/certificates/sql/empty-2.0.0.sql");

   } else if (TableExists("glpi_plugin_certificates_mailing") && !FieldExists("glpi_plugin_certificates","recursive")) {
      
      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/certificates/sql/update-1.4.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/certificates/sql/update-1.5.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/certificates/sql/update-1.5.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/certificates/sql/update-1.6.0.sql");

   } else if (TableExists("glpi_plugin_certificates_profiles") && FieldExists("glpi_plugin_certificates_profiles","interface")) {
      
      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/certificates/sql/update-1.5.0.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/certificates/sql/update-1.5.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/certificates/sql/update-1.6.0.sql");

   } else if (TableExists("glpi_plugin_certificates") && !FieldExists("glpi_plugin_certificates","date_mod")) {
      
      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/certificates/sql/update-1.5.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/certificates/sql/update-1.6.0.sql");

   } else if (!TableExists("glpi_plugin_certificates_certificatetypes")) {
      
      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/certificates/sql/update-1.6.0.sql");
      
   }
   //from 1.6 version
   if (TableExists("glpi_plugin_certificates_certificates") 
      && !FieldExists("glpi_plugin_certificates_certificates","users_id_tech")) {
      $DB->runFile(GLPI_ROOT ."/plugins/certificates/sql/update-1.8.0.sql");
   }
   
   if (TableExists("glpi_plugin_certificates_profiles")) {
   
      $notepad_tables = array('glpi_plugin_certificates_certificates');

      foreach ($notepad_tables as $t) {
         // Migrate data
         if (FieldExists($t, 'notepad')) {
            $query = "SELECT id, notepad
                      FROM `$t`
                      WHERE notepad IS NOT NULL
                            AND notepad <>'';";
            foreach ($DB->request($query) as $data) {
               $iq = "INSERT INTO `glpi_notepads`
                             (`itemtype`, `items_id`, `content`, `date`, `date_mod`)
                      VALUES ('".getItemTypeForTable($t)."', '".$data['id']."',
                              '".addslashes($data['notepad'])."', NOW(), NOW())";
               $DB->queryOrDie($iq, "0.85 migrate notepad data");
            }
            $query = "ALTER TABLE `glpi_plugin_certificates_certificates` DROP COLUMN `notepad`;";
            $DB->query($query);
         }
      }
   }
   
   if ($install || $update78) {

      //Do One time on 0.78
      $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginCertificatesCertificate' AND `name` = 'Alert Certificates'";
      $result = $DB->query($query_id) or die ($DB->error());
      $itemtype = $DB->result($result,0,'id');
      
      $query="INSERT INTO `glpi_notificationtemplatetranslations`
                                 VALUES(NULL, ".$itemtype.", '','##certificate.action## : ##certificate.entity##',
                        '##lang.certificate.entity## :##certificate.entity##
   ##FOREACHcertificates##
   ##lang.certificate.name## : ##certificate.name## - ##lang.certificate.dateexpiration## : ##certificate.dateexpiration##
   ##ENDFOREACHcertificates##',
                        '&lt;p&gt;##lang.certificate.entity## :##certificate.entity##&lt;br /&gt; &lt;br /&gt;
                        ##FOREACHcertificates##&lt;br /&gt;
                        ##lang.certificate.name##  : ##certificate.name## - ##lang.certificate.dateexpiration## :  ##certificate.dateexpiration##&lt;br /&gt; 
                        ##ENDFOREACHcertificates##&lt;/p&gt;');";
      $result=$DB->query($query);
      
      $query = "INSERT INTO `glpi_notifications`
                                   VALUES (NULL, 'Alert Expired Certificates', 0, 'PluginCertificatesCertificate', 'ExpiredCertificates',
                                          'mail',".$itemtype.",
                                          '', 1, 1, '2010-02-17 22:36:46');";
      
      $result=$DB->query($query);
      
      $query = "INSERT INTO `glpi_notifications`
                                   VALUES (NULL, 'Alert Certificates Which Expire', 0, 'PluginCertificatesCertificate', 'CertificatesWhichExpire',
                                          'mail',".$itemtype.",
                                          '', 1, 1, '2010-02-17 22:36:46');";
      
      $result=$DB->query($query);
   }
   
   if ($update78) {
      $query_="SELECT *
            FROM `glpi_plugin_certificates_profiles` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0) {

         while ($data=$DB->fetch_array($result_)) {
            $query="UPDATE `glpi_plugin_certificates_profiles`
                  SET `profiles_id` = '".$data["id"]."'
                  WHERE `id` = '".$data["id"]."';";
            $result=$DB->query($query);

         }
      }
      
      $query="ALTER TABLE `glpi_plugin_certificates_profiles`
               DROP `name` ;";
      $result=$DB->query($query);
   
      Plugin::migrateItemType(
         array(1700=>'PluginCertificatesCertificate'),
         array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"),
         array("glpi_plugin_certificates_certificates_items"));
      
      Plugin::migrateItemType(
         array(1200 => "PluginAppliancesAppliance",1300 => "PluginWebapplicationsWebapplication"),
         array("glpi_plugin_certificates_certificates_items"));
   }
   
   CronTask::Register('PluginCertificatesCertificate', 'CertificatesAlert', DAY_TIMESTAMP);

   PluginCertificatesProfile::initProfile();
   PluginCertificatesProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   $migration = new Migration("2.0.0");
   $migration->dropTable('glpi_plugin_certificates_profiles');
   
   return true;
}

function plugin_certificates_uninstall() {
   global $DB;
   
   include_once (GLPI_ROOT."/plugins/certificates/inc/profile.class.php");
   include_once (GLPI_ROOT."/plugins/certificates/inc/menu.class.php");
   
   $tables = array("glpi_plugin_certificates_certificates",
               "glpi_plugin_certificates_certificates_items",
               "glpi_plugin_certificates_certificatetypes",
               "glpi_plugin_certificates_certificatestates",
               "glpi_plugin_certificates_configs",
               "glpi_plugin_certificates_notificationstates");

   foreach($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   
   //old versions	
   $tables = array("glpi_plugin_certificates",
               "glpi_plugin_certificates_profiles",
               "glpi_plugin_certificates_device",
               "glpi_dropdown_plugin_certificates_type",
               "glpi_dropdown_plugin_certificates_status",
               "glpi_plugin_certificates_config",
               "glpi_plugin_certificates_mailing",
               "glpi_plugin_certificates_default");

   foreach($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   
   $notif = new Notification();
   $options = array('itemtype' => 'PluginCertificatesCertificate',
                    'event'    => 'ExpiredCertificates',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   $options = array('itemtype' => 'PluginCertificatesCertificate',
                    'event'    => 'CertificatesWhichExpire',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   
   //templates
   $template = new NotificationTemplate();
   $translation = new NotificationTemplateTranslation();
   $options = array('itemtype' => 'PluginCertificatesCertificate',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
      $options_template = array('notificationtemplates_id' => $data['id'],
                    'FIELDS'   => 'id');
   
         foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
            $translation->delete($data_template);
         }
      $template->delete($data);
   }
   
   $tables_glpi = array("glpi_displaypreferences",
               "glpi_documents_items",
               "glpi_bookmarks",
               "glpi_logs",
               "glpi_tickets",
               "glpi_contracts_items",
               "glpi_notepads");

   foreach($tables_glpi as $table_glpi)
      $DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` = 'PluginCertificatesCertificate';");
      
   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginCertificatesProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(array('name' => $right['field']));
   }
   PluginCertificatesMenu::removeRightsFromSession();
   
   PluginCertificatesProfile::removeRightsFromSession();

   return true;
}

function plugin_certificates_postinit() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['certificates'] = array();

   foreach (PluginCertificatesCertificate::getTypes(true) as $type) {

      $PLUGIN_HOOKS['item_purge']['certificates'][$type]
         = array('PluginCertificatesCertificate_Item','cleanForItem');

      CommonGLPI::registerStandardTab($type, 'PluginCertificatesCertificate_Item');
   }
}

function plugin_certificates_AssignToTicket($types) {

   if (Session::haveRight("plugin_certificates_open_ticket", "1")) {
      $types['PluginCertificatesCertificate']=PluginCertificatesCertificate::getTypeName(2);
   }
   return $types;
}

// Define dropdown relations
function plugin_certificates_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("certificates"))

      return array("glpi_plugin_certificates_certificatetypes"=>array("glpi_plugin_certificates_certificates"=>"plugin_certificates_certificatetypes_id"),
               "glpi_plugin_certificates_certificatestates"=>array("glpi_plugin_certificates_certificates"=>"plugin_certificates_certificatestates_id",
                                                                     "glpi_plugin_certificates_mailingstates"=>"plugin_certificates_certificatestates_id"),
               "glpi_entities"=>array("glpi_plugin_certificates_certificates"=>"entities_id",
                              "glpi_plugin_certificates_certificatetypes"=>"entities_id",
                              "glpi_plugin_certificates_certificatestates"=>"entities_id"),
               "glpi_users"=>array("glpi_plugin_certificates_certificates"=>"users_id_tech"),
               "glpi_groups"=>array("glpi_plugin_certificates_certificates"=>"groups_id_tech"),
               "glpi_locations"=>array("glpi_plugin_certificates_certificates"=>"locations_id"),
               "glpi_manufacturers"=>array("glpi_plugin_certificates_certificates"=>"manufacturers_id"),
               "glpi_plugin_certificates_certificates"=>array("glpi_plugin_certificates_certificates_items"=>"plugin_certificates_certificates_id"));
   else
      return array();
}

// Define Dropdown tables to be manage in GLPI :
function plugin_certificates_getDropdown() {

   $plugin = new Plugin();
   if ($plugin->isActivated("certificates"))
      return array('PluginCertificatesCertificateType'=>PluginCertificatesCertificateType::getTypeName(2),
               'PluginCertificatesCertificateState'=>PluginCertificatesCertificateState::getTypeName(2));
   else
      return array();
}


////// SEARCH FUNCTIONS ///////() {

function plugin_certificates_getAddSearchOptions($itemtype) {

   $sopt=array();

   if (in_array($itemtype,PluginCertificatesCertificate::getTypes(true))) {
      if (Session::haveRight("plugin_certificates", READ)) {
         $sopt[1710]['table']='glpi_plugin_certificates_certificates';
         $sopt[1710]['field']='name';
         $sopt[1710]['name']= PluginCertificatesCertificate::getTypeName(2) ." - ".__('Name');
         $sopt[1710]['forcegroupby']='1';
         $sopt[1710]['datatype']='itemlink';
         $sopt[1710]['massiveaction']  = false;
         $sopt[1710]['itemlink_type']='PluginCertificatesCertificate';
         $sopt[1710]['joinparams']     = array('beforejoin'
                                                => array('table'      => 'glpi_plugin_certificates_certificates_items',
                                                         'joinparams' => array('jointype' => 'itemtype_item')));
                                                         
         $sopt[1711]['table']='glpi_plugin_certificates_certificatetypes';
         $sopt[1711]['field']='name';
         $sopt[1711]['name']= PluginCertificatesCertificate::getTypeName(2)." - ".__('Type');
         $sopt[1711]['forcegroupby']=true;
         $sopt[1711]['joinparams']     = array('beforejoin' => array(
                                                   array('table'      => 'glpi_plugin_certificates_certificates',
                                                         'joinparams' => $sopt[1710]['joinparams'])));
         $sopt[1711]['datatype']       = 'dropdown';
         $sopt[1711]['massiveaction']  = false;
      }
   }
   return $sopt;
}

function plugin_certificates_displayConfigItem($type,$ID,$data,$num) {

   $searchopt=&Search::getOptions($type);
   $table=$searchopt[$ID]["table"];
   $field=$searchopt[$ID]["field"];
   
   switch ($table.'.'.$field) {
      case "glpi_plugin_certificates_certificates.date_expiration" :
         if ($data[$num][0]['name'] <= date('Y-m-d') && !empty($data[$num][0]['name']))
            return " class=\"deleted\" ";
         break;
   }
   return "";
}

function plugin_certificates_giveItem($type,$ID,$data,$num) {
   global $DB;

   $searchopt=&Search::getOptions($type);
   $table=$searchopt[$ID]["table"];
   $field=$searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case "glpi_plugin_certificates_certificates.date_expiration" :
         if (empty($data[$num][0]['name']))
            $out=__('Does not expire', 'certificates');
         else
            $out= Html::convdate($data[$num][0]['name']);
         return $out;
         break;
      case "glpi_plugin_certificates_certificates_items.items_id" :
      //$type : item type
         $query_device = "SELECT DISTINCT `itemtype`
                     FROM `glpi_plugin_certificates_certificates_items`
                     WHERE `plugin_certificates_certificates_id` = '".$data['id']."'
                     ORDER BY `itemtype` ";
         $result_device = $DB->query($query_device);
         $number_device = $DB->numrows($result_device);

         $out='';
         $certificate=$data['id'];
         if ($number_device>0) {
            for ($i=0 ; $i < $number_device ; $i++) {
               $column="name";
               $itemtype=$DB->result($result_device, $i, "itemtype");
               
               if (!class_exists($itemtype)) {
                  continue;
               }
               
               $item = new $itemtype();
               if ($item->canView()) {
                  $table_item = getTableForItemType($itemtype);
                  
                  $query = "SELECT `".$table_item."`.*, `glpi_plugin_certificates_certificates_items`.`id` AS items_id, `glpi_entities`.`id` AS entity "
                  ." FROM `glpi_plugin_certificates_certificates_items`, `".$table_item
                  ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table_item."`.`entities_id`) "
                  ." WHERE `".$table_item."`.`id` = `glpi_plugin_certificates_certificates_items`.`items_id`
                     AND `glpi_plugin_certificates_certificates_items`.`itemtype` = '$itemtype'
                     AND `glpi_plugin_certificates_certificates_items`.`plugin_certificates_certificates_id` = '".$certificate."' "
                  . getEntitiesRestrictRequest(" AND ",$table_item,'','',$item->maybeRecursive());

                  if ($item->maybeTemplate()) {
                     $query.=" AND ".$table_item.".is_template='0'";
                  }
                  $query.=" ORDER BY `glpi_entities`.`completename`, `".$table_item."`.`$column` ";

                  if ($result_linked=$DB->query($query))
                     if ($DB->numrows($result_linked)) {
                        $item = new $itemtype();
                        while ($data_linked = $DB->fetch_assoc($result_linked)) {
                           if ($item->getFromDB($data_linked['id'])) {
                              $out .= $item::getTypeName(1)." - ".$item->getLink()."<br>";
                           }
                        }
                     } else
                        $out.=' ';
               } else
                  $out.=' ';
            }
         }
         return $out;
         break;
   }
   return "";
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_certificates_MassiveActions($type) {

   if (in_array($type,PluginCertificatesCertificate::getTypes(true))) {
      return array('PluginCertificatesCertificate'.MassiveAction::CLASS_ACTION_SEPARATOR.'plugin_certificates_add_item' =>
                                                              __('Associate to certificate', 'certificates'));
   }
   return array();
}

?>