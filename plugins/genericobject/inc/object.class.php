<?php
/*
 This file is part of the genericobject plugin.

 Genericobject plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Genericobject plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Genericobject. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   genericobject
 @author    the genericobject plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/genericobject
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

class PluginGenericobjectObject extends CommonDBTM {

   protected $objecttype;

   //Internal field counter
   private $cpt = 0;

   //Get itemtype name
   static function getTypeName($nb=0) {
      global $LANG;
      $class    = get_called_class();
      //Datainjection : Don't understand why I need this trick : need to be investigated !
      if(preg_match("/Injection$/i",$class)) {
         $class = str_replace("Injection", "", $class);
      }
      $item     = new $class();
      //Itemtype name can be contained in a specific locale field : try to load it
      PluginGenericobjectType::includeLocales($item->objecttype->fields['name']);
      if(isset($LANG['genericobject'][$class][0])) {
         $type_name = $LANG['genericobject'][$class][0];
      } else {
         $type_name = $item->objecttype->fields['name'];
      }
      return ucwords($type_name);
   }


   public function __construct() {
      $class       = get_called_class();
      $this->table = getTableForItemType($class);
      if (class_exists($class)) {
         $this->objecttype = PluginGenericobjectType::getInstance($class);
      }
      $this->dohistory = $this->canUseHistory();
   }

   static function install() {
   }

   static function uninstall() {
   }

   static function registerType() {
      global $DB, $PLUGIN_HOOKS, $UNINSTALL_TYPES, $ORDER_TYPES, $CFG_GLPI,
              $GO_LINKED_TYPES;

      $class  = get_called_class();
      $item   = new $class();
      $fields = PluginGenericobjectSingletonObjectField::getInstance($class);

      PluginGenericobjectType::includeLocales($item->getObjectTypeName());
      PluginGenericobjectType::includeConstants($item->getObjectTypeName());

      $options = array("document_types"         => $item->canUseDocuments(),
                       "helpdesk_visible_types" => $item->canUseTickets(),
                       "linkgroup_types"        => $item->canUseTickets()
                                                    && isset ($fields["groups_id"]),
                       "linkuser_types"         => $item->canUseTickets()
                                                   && isset ($fields["users_id"]),
                       "ticket_types"           => $item->canUseTickets(),
                       "infocom_types"          => $item->canUseInfocoms(),
                       "networkport_types"      => $item->canUseNetworkPorts(),
                       "reservation_types"      => $item->canBeReserved(),
                       "contract_types"         => $item->canUseContracts(),
                       "unicity_types"          => $item->canUseUnicity());
      Plugin::registerClass($class, $options);

      if (plugin_genericobject_haveRight($class, READ)) {
         //Change url for adding a new object, depending on template management activation
         if ($item->canUseTemplate()) {
            //Template management is active
            $add_url = "/front/setup.templates.php?itemtype=$class&amp;add=1";
            $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['template']
                                                        = "/front/setup.templates.php?itemtype=$class&amp;add=0";
         } else {
            //Template management is not active
            $add_url = Toolbox::getItemTypeFormURL($class, false);
         }
         //Menu management
         $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['title']
                                                   = $class::getTypeName();
         $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['page']
                                                   = Toolbox::getItemTypeSearchURL($class, false);
         $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['search']
                                                    = Toolbox::getItemTypeSearchURL($class, false);

         if (plugin_genericobject_haveRight($class, "w")) {
            $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['add']
                                                      = $add_url;

         }

         //Add configuration icon, if user has right
         if (Session::haveRight('config', UPDATE)) {
            $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['config']
               = Toolbox::getItemTypeSearchURL('PluginGenericobjectType',false)."?itemtype=$class";
         }

         if ($item->canUsePluginUninstall()) {
            if (!in_array($class, $UNINSTALL_TYPES)) {
               array_push($UNINSTALL_TYPES, $class);
            }
         }
         if ($item->canUsePluginOrder()) {
            if (!in_array($class, $ORDER_TYPES)) {
               array_push($ORDER_TYPES, $class);
            }
         }
         if ($item->canUseGlobalSearch()) {
            if (!in_array($class, $CFG_GLPI['asset_types'])) {
               array_push($CFG_GLPI['asset_types'], $class);
            }
            if (!in_array($class, $CFG_GLPI['globalsearch_types'])) {
               array_push($CFG_GLPI['globalsearch_types'], $class);
            }
         }

         if ($item->canUseDirectConnections()) {
            if (!in_array($class, $GO_LINKED_TYPES)) {
               array_push($GO_LINKED_TYPES, $class);
            }
            $items_class = $class."_Item";
            //if (class_exists($items_class)) {
               $items_class::registerType();
            //}
         }
      }

      foreach(PluginGenericobjectType::getDropdownForItemtype($class) as $table) {
         $itemtype = getItemTypeForTable($table);
         if (class_exists($itemtype) ) {
            $item     = new $itemtype();
            //If entity dropdown, check rights to view & create
            if ($itemtype::canView()) {
               $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$itemtype]['links']['search']
                  = Toolbox::getItemTypeSearchURL($itemtype, false);
               if ($itemtype::canCreate()) {
                  $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['add']
                     = Toolbox::getItemTypeFormURL($class, false);
               }
            }
         }
      }
   }

   static function getMenuIcon($itemtype) {
      global $CFG_GLPI;
      $default_icon = "/plugins/genericobject/pics/default-icon.png";
      _log("get called class", get_called_class());
      _log("itemtype", $itemtype);
      $itemtype_table = getTableForItemType($itemtype);
      $itemtype_shortname = preg_replace("/^glpi_plugin_genericobject_/", "", $itemtype_table);
      _log("itemtype short name", $itemtype_shortname);
      _log("itemtype short name (singular)", getSingular($itemtype_shortname));
      $itemtype_icons = glob(
         GENERICOBJECT_PICS_PATH . '/' . getSingular($itemtype_shortname) . ".*"
      );
      _log("itemtype_icons\n", $itemtype_icons);
      $finfo = new finfo(FILEINFO_MIME);
      $icon_found = null;
      foreach($itemtype_icons as $icon) {
         if ( preg_match("|^image/|", $finfo->file($icon)) ) {
            $icon_found = preg_replace("|^".GLPI_ROOT."|", "", $icon);
         }
      }
      _log("itemtype icon found", $icon_found);
      if ( !is_null($icon_found)) {
         $icon_path = $CFG_GLPI['root_doc'] . "/" . $icon_found;
      } else {
         $icon_path = $CFG_GLPI['root_doc'] . "/" . $default_icon;
      }
      return "".
         "<img ".
         "  class='genericobject_menu_icon' ".
         "src='".$icon_path."'".
         "/>";
   }

   /**
    * Generate items to display in GLPI menus
    *
    */
   static function getMenuContent() {
      $menu = array();
      $types = PluginGenericobjectType::getTypes();
      unset($_SESSION['glpimenu']);
      foreach($types as $type) {
         $itemtype = $type['itemtype'];
         $item = new $itemtype();
         $itemtype_rightname = PluginGenericobjectProfile::getProfileNameForItemtype($itemtype);
         if (
            class_exists($itemtype)
            and Session::haveRight($itemtype_rightname, READ)
         ) {

            $links = array();
            if ($item->canUseTemplate()) {
               $links['template'] = "/front/setup.templates.php?itemtype=$itemtype&amp;add=0";
               $links['add'] = "/front/setup.templates.php?itemtype=$itemtype&amp;add=1";
            } else {
               $links['add'] = self::getFormUrl(false).'?itemtype='.$itemtype;
            }
            $menu[strtolower($itemtype)]= array(
               'title' => (
                  "<span class='genericobject_menu_wrapper'>"
                  . self::getMenuIcon($type['itemtype'])
                  . "<span class='genericobject_menu_text'>"
                  .     $type['itemtype']::getMenuName()
                  . "</span>"
                  .
                  "</span>"
               ),
               'page' => self::getSearchUrl(false).'?itemtype='.$itemtype,
               'links' => $links
            );
            _log("Menu Content for ", $itemtype, "\n", $menu[strtolower($itemtype)]);
         }
      }
      $menu['is_multi_entries']= true;
      return $menu;
   }

   static function checkItemtypeRight($class = null, $right) {
      if (!is_null($class) and class_exists($class) ) {
         $right_name = PluginGenericobjectProfile::getProfileNameForItemtype(
            $class
         );

         return Session::haveRight($right_name,$right);
      }
   }

   static function canCreate() {
      $class    = get_called_class();
      //Datainjection : Don't understand why I need this trick : need to be investigated !
      if(preg_match("/Injection$/i",$class)) {
         $class = str_replace("Injection", "", $class);
      }
      return static::checkItemtypeRight($class, CREATE);
   }

   static function canView() {
      $class = get_called_class();
      return static::checkItemtypeRight($class, READ);
   }

   static function canUpdate() {
      $class = get_called_class();
      return static::checkItemtypeRight($class, UPDATE);
   }

   static function canDelete() {
      $class = get_called_class();
      return static::checkItemtypeRight($class, DELETE);
   }

   static function canPurge() {
      $class = get_called_class();
      return static::checkItemtypeRight($class, PURGE);
   }

   function defineTabs($options=array()) {
      $ong = array ();

      $this->addDefaultFormTab($ong);

      if (!$this->isNewItem()) {

         if ($this->canUseNetworkPorts()) {
            $this->addStandardTab('NetworkPort', $ong, $options);
         }

         if ($this->canUseInfocoms()) {
            $this->addStandardTab('Infocom', $ong, $options);
         }

         if ($this->canUseContracts()) {
            $this->addStandardTab('Contract_Item', $ong, $options);
         }

         if ($this->canUseDocuments()) {
            $this->addStandardTab('Document_Item', $ong, $options);
         }

         if ($this->canUseTickets()) {
            $this->addStandardTab('Ticket', $ong, $options);
            $this->addStandardTab('Item_Problem', $ong, $options);
         }

         if ($this->canUseNotepad() && Session::haveRight("notes", "r")) {
            $this->addStandardTab('Note',$ong, $options);
         }

         if ($this->canBeReserved()) {
            $this->addStandardTab('Reservation', $ong, $options);
         }

         if ($this->canUseHistory()) {
            $this->addStandardTab('Log',$ong, $options);
         }
      }
      return $ong;
   }


   //------------------------ CAN methods -------------------------------------//

   function getObjectTypeName() {
      return $this->objecttype->getName();
   }

   function canUseInfocoms() {
      return ($this->objecttype->canUseInfocoms() && Session::haveRight("infocom", READ));
   }

   function canUseContracts() {
      return ($this->objecttype->canUseContracts() && Session::haveRight("contract", READ));
   }


   function canUseTemplate() {
      return $this->objecttype->canUseTemplate();
   }


   function canUseNotepad() {
      return $this->objecttype->canUseNotepad();
   }

   function canUseUnicity() {
      return ($this->objecttype->canUseUnicity() && Session::haveRight("config", READ));
   }


   function canUseDocuments() {
      return ($this->objecttype->canUseDocuments() && Session::haveRight("document", READ));
   }


   function canUseTickets() {
      return ($this->objecttype->canUseTickets());
   }


   function canUseGlobalSearch() {
      return ($this->objecttype->canUseGlobalSearch());
   }


   function canBeReserved() {
      return (
         $this->objecttype->canBeReserved()
         and Session::haveRight("reservation", READ)
      );
   }


   function canUseHistory() {
      return ($this->objecttype->canUseHistory());
   }


   function canUsePluginDataInjection() {
      return ($this->objecttype->canUsePluginDataInjection());
   }


   function canUsePluginPDF() {
      return ($this->objecttype->canUsePluginPDF());
   }


   function canUsePluginOrder() {
      return ($this->objecttype->canUsePluginOrder());
   }


   function canUseNetworkPorts() {
      return ($this->objecttype->canUseNetworkPorts());
   }


   function canUseDirectConnections() {
      return ($this->objecttype->canUseDirectConnections());
   }


   function canUsePluginUninstall() {
      return ($this->objecttype->canUsePluginUninstall());
   }

   function getLinkedItemTypesAsArray() {
      return $this->objecttype->getLinkedItemTypesAsArray();
   }

   function title() {
   }


   function showForm($id, $options=array(), $previsualisation = false) {
      global $DB;

      if ($previsualisation) {
         $canedit = true;
         $this->getEmpty();
      } else {
         if ($id > 0) {
            $this->check($id, READ);
         } else {
            // Create item
            $this->check(-1, CREATE);
            $this->getEmpty();
         }

         $canedit = $this->can($id, UPDATE);
      }

      if (isset($options['withtemplate']) && $options['withtemplate'] == 2) {
         $template   = "newcomp";
         $date = sprintf(__('Created on %s'), Html::convDateTime($_SESSION["glpi_currenttime"]));
      } else if (isset($options['withtemplate']) && $options['withtemplate'] == 1) {
         $template   = "newtemplate";
         $date = sprintf(__('Created on %s'), Html::convDateTime($_SESSION["glpi_currenttime"]));
      } else {
         $date = sprintf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
         $template   = false;
      }

      $this->fields['id'] = $id;
      $this->initForm($id,$options);
      $this->showFormHeader($options);

      if ($previsualisation) {
         echo "<tr><th colspan='4'>".__("Object preview", "genericobject").":&nbsp;";
         $itemtype = $this->objecttype->fields['itemtype'];
         echo $itemtype::getTypeName();
         echo "</th></tr>";
      }


      //Reset fields definition only to keep the itemtype ones
      $GO_FIELDS = array();
      plugin_genericobject_includeCommonFields(true);
      PluginGenericobjectType::includeConstants($this->getObjectTypeName(), true);

      foreach (PluginGenericobjectSingletonObjectField::getInstance($this->objecttype->fields['itemtype'])
               as $field => $description) {
         $this->displayField($canedit, $field, $this->fields[$field], $template, $description);
      }
      $this->closeColumn();

      if (!$this->isNewID($id)) {
         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2' class='center'>".$date;
         if (!$template && !empty($this->fields['template_name'])) {
            echo "<span class='small_space'>(".__("Template name")."&nbsp;: ".
                  $this->fields['template_name'].")</span>";
         }
         echo "</td></tr>";
      }

      if (!$previsualisation) {
         $this->showFormButtons($options);
         echo "<div id='tabcontent'></div>";
         echo "<script type='text/javascript'>loadDefaultTab();</script>";
      } else {
         echo "</table></div>";
         Html::closeForm();
      }
   }


   static function getFieldsToHide() {
      return array('id', 'is_recursive', 'is_template', 'template_name', 'is_deleted',
                   'entities_id', 'notepad', 'date_mod');
   }


   function displayField($canedit, $name, $value, $template, $description = array()) {
      global $GO_BLACKLIST_FIELDS;

      $searchoption  = PluginGenericobjectField::getFieldOptions($name, get_called_class());

      if (!empty($searchoption)
         && !in_array($name, self::getFieldsToHide())) {

         $this->startColumn();
         echo $searchoption['name'];
         if (isset($searchoption['autoname']) && $searchoption['autoname'] && $template) {
            echo "*&nbsp;";
         }
         $this->endColumn();
         $this->startColumn();
         switch ($description['Type']) {
            case "int(11)":
               $fk_table = getTableNameForForeignKeyField($name);
               if ($fk_table != '') {
                  $itemtype   = getItemTypeForTable($fk_table);
                  $dropdown   = new $itemtype();
                  $parameters = array('name' => $name, 'value' => $value, 'comments' => true);
                  if ($dropdown->isEntityAssign()) {
                     $parameters["entity"] = $this->fields['entities_id'];
                  }
                  if ($dropdown->maybeRecursive()) {
                     $parameters['entity_sons'] = true;
                  }
                  if(isset($searchoption['condition'])) {
                     $parameters['condition'] = $searchoption['condition'];
                  }
                  Dropdown::show($itemtype, $parameters);
               } else {
                  $min = $max = $step = 0;
                  if (isset($searchoption['min'])) {
                     $min = $searchoption['min'];
                  } else {
                     $min = 0;
                  }
                  if (isset($searchoption['max'])) {
                     $max = $searchoption['max'];
                  } else {
                     $max = 100;
                  }
                  if (isset($searchoption['step'])) {
                     $step = $searchoption['step'];
                  } else {
                     $step = 1;
                  }
                  Dropdown::showInteger($name, $value, $min, $max, $step);
               }
               break;

            case "tinyint(1)":
               Dropdown::showYesNo($name, $value);
               break;

            case "varchar(255)":
               if (isset($searchoption['autoname']) && $searchoption['autoname']) {
                  $objectName = autoName($this->fields[$name], $name, ($template === "newcomp"),
                                         $this->getType(), $this->fields["entities_id"]);
               } else {
                   $objectName = $this->fields[$name];
               }
               Html::autocompletionTextField($this, $name, array('value' => $objectName));
               break;

            case "longtext":
            case "text":
               echo "<textarea cols='40' rows='4' name='" . $name . "'>" . $value .
                     "</textarea>";
               break;

            case "date":
                  Html::showDateFormItem($name, $value, false, true);
                  break;

            case "datetime":
                  Html::showDateTimeFormItem($name, $value, false, true);
                  break;

            default:
            case "float":
                  echo "<input type='text' name='$name' value='$value'>";
                  break;

            case 'decimal':
                  echo "<input type='text' name='$name' value='".Html::formatNumber($value)."'>";
                  break;
         }
         $this->endColumn();
      }
   }



   /**
   * Add a new column
   **/
   function startColumn() {
      if ($this->cpt == 0) {
         echo "<tr class='tab_bg_1'>";
      }

      echo "<td>";
      $this->cpt++;
   }



   /**
   * End a column
   **/
   function endColumn() {
      echo "</td>";

      if ($this->cpt == 4) {
         echo "</tr>";
         $this->cpt = 0;
      }

   }



   /**
   * Close a column
   **/
   function closeColumn() {
      if ($this->cpt > 0) {
         while ($this->cpt < 4) {
            echo "<td></td>";
            $this->cpt++;
         }
         echo "</tr>";
      }
   }


   function prepareInputForAdd($input) {

      //Template management
      if (isset ($input["id"]) && $input["id"] > 0) {
         $input["_oldID"] = $input["id"];
      }
      unset ($input['id']);
      unset ($input['withtemplate']);

      return $input;
   }


   function post_addItem() {
      global $DB;
      // Manage add from template
      if (isset ($this->input["_oldID"])) {
         // ADD Infocoms
         $ic = new Infocom();
         if ($this->canUseInfocoms()
            && $ic->getFromDBforDevice($this->type, $this->input["_oldID"])) {
            $ic->fields["items_id"] = $this->fields['id'];
            unset ($ic->fields["id"]);
            if (isset ($ic->fields["immo_number"])) {
               $ic->fields["immo_number"] = autoName($ic->fields["immo_number"], "immo_number", 1,
                                                     'Infocom', $this->input['entities_id']);
            }
            if (empty ($ic->fields['use_date'])) {
               unset ($ic->fields['use_date']);
            }
            if (empty ($ic->fields['buy_date'])) {
               unset ($ic->fields['buy_date']);
            }
            $ic->addToDB();
         }

         foreach (array('Document_Item' => 'documents_id',
                        'Contract_Item' => 'contracts_id') as $type => $fk) {
            $item = new $type();
            foreach ($item->find("items_id='" . $this->input["_oldID"] . "'
                                 AND itemtype='" . $this->type . "'") as $tmpid => $data) {
               $tmp             = array();
               $tmp['items_id'] = $this->input["_oldID"];
               $tmp['itemtype'] = $type;
               $tmp[$fk]        = $data[$fk];
               $item->add($tmp);
            }
         }

         if ($this->canUseNetworkPorts()) {
            // ADD Ports
            $query  = "SELECT `id`
                       FROM `glpi_networkports`
                       WHERE `items_id` = '".$this->input["_oldID"]."'
                          AND `itemtype` = '".get_called_class()."';";
            $result = $DB->query($query);
            if ($DB->numrows($result) > 0) {
               while ($data = $DB->fetch_array($result)) {
                  $np  = new NetworkPort();
                  $npv = new NetworkPort_Vlan();
                  $np->getFromDB($data["id"]);
                  unset($np->fields["id"]);
                  unset($np->fields["ip"]);
                  unset($np->fields["mac"]);
                  unset($np->fields["netpoints_id"]);
                  $np->fields["items_id"] = $this->fields['id'];
                  $portid = $np->addToDB();
                  foreach ($DB->request('glpi_networkports_vlans',
                                        array('networkports_id' => $data["id"])
                          ) as $vlan) {
                     $npv->assignVlan($portid, $vlan['vlans_id']);
                  }
               }
            }
         }

      }
   }


   function cleanDBonPurge() {
      $parameters = array('items_id' => $this->getID(), 'itemtype' => get_called_class());
      $types      = array('Ticket', 'NetworkPort', 'Computer_Item',
                          'ReservationItem', 'Document_Item', 'Infocom', 'Contract_Item');
      foreach ($types as $type) {
         $item = new $type();
         $item->deleteByCriteria($parameters);
      }

      $ip = new Item_Problem();
      $ip->cleanDBonItemDelete(get_called_class(), $this->getID());

   }



   /**
    * Display object preview form
    * @param type the object type
    */
   static function showPrevisualisationForm(PluginGenericobjectType $type) {
      //Toolbox::logDebug(print_r($type->fields,true));
      $itemtype = $type->fields['itemtype'];
      $item     = new $itemtype();

      $right_name = PluginGenericobjectProfile::getProfileNameForItemtype(
         $itemtype
      );
      if (Session::haveRight($right_name, READ)) {
         $item->showForm(-1, array(), true);
      } else {
         echo "<br><strong>" . __("You must configure rights to enable the preview",
                                  "genericobject") . "</strong><br>";
      }
   }


   function getSearchOptions() {
      return $this->getObjectSearchOptions(true);
   }


   function getObjectSearchOptions($with_linkfield = false) {
      global $DB, $GO_FIELDS, $GO_BLACKLIST_FIELDS;

      $datainjection_blacklisted = array('id', 'date_mod', 'entities_id');
      $index_exceptions = array('name' => 1, 'id' => 2, 'comment' => 16, 'date_mod' => 19,
                                 'entities_id' => 80, 'is_recursive' => 86, 'notepad' => 90);
      $index   = 3;
      $options = array();
      $table   = getTableForItemType(get_called_class());

      foreach (
         PluginGenericobjectSingletonObjectField::getInstance(get_called_class())
         as $field => $values
      ) {
         $searchoption = PluginGenericobjectField::getFieldOptions(
            $field,
            $this->objecttype->fields['itemtype']
         );

         //Some fields have fixed index values...
         $currentindex = $index;
         if (isset($index_exceptions[$field])) {
            $currentindex = $index_exceptions[$field];
         } elseif (in_array($currentindex, $index_exceptions)) {
            //If this index is reserved, jump to next
            $currentindex++;
         }

         if (in_array($field,array('is_deleted'))) {
            continue;
         }

         $item = new $this->objecttype->fields['itemtype'];

         //Table definition
         $tmp  = getTableNameForForeignKeyField($field);

         if ($with_linkfield) {
            $options[$currentindex]['linkfield'] = $field;
         }

         if ($tmp != '') {
            $itemtype   = getItemTypeForTable($tmp);
            $tmpobj     = new $itemtype();

            //Set table
            $options[$currentindex]['table'] = $tmp;

            //Set field
            if ($tmpobj instanceof CommonTreeDropdown) {
               $options[$currentindex]['field'] = 'completename';
            } else {
               $options[$currentindex]['field'] = 'name';
            }

         } else {
            $options[$currentindex]['table'] = $table;
            $options[$currentindex]['field'] = $field;

         }

         $options[$currentindex]['name']  = $searchoption['name'];

         //Massive action or not
         if (isset($searchoption['massiveaction'])) {
            $options[$currentindex]['massiveaction']
               = $searchoption['massiveaction'];
         }


         //Datainjection option
         if (!in_array($field, $datainjection_blacklisted)) {
            $options[$currentindex]['injectable'] = 1;
         } else {
            $options[$currentindex]['injectable'] = 0;
         }

         //Field type
         switch ($values['Type']) {
            default:
            case "varchar(255)":
               if ($field == 'name') {
                  $options[$currentindex]['datatype']      = 'itemlink';
                  $options[$currentindex]['itemlink_type'] = get_called_class();
                  $options[$currentindex]['massiveaction'] = false;
               } else {
                  if (isset($searchoption['datatype']) && $searchoption['datatype'] == 'weblink') {
                     $options[$currentindex]['datatype'] = 'weblink';
                  } else {
                     $options[$currentindex]['datatype'] = 'string';
                  }
               }
               if ($item->canUsePluginDataInjection()) {
                  //Datainjection specific
                  $options[$currentindex]['checktype']   = 'text';
                  $options[$currentindex]['displaytype'] = 'text';
               }
               break;
            case "tinyint(1)":
               $options[$currentindex]['datatype'] = 'bool';
               if ($item->canUsePluginDataInjection()) {
                  //Datainjection specific
                  $options[$currentindex]['displaytype'] = 'bool';
               }
               break;
            case "text":
            case "longtext":
               $options[$currentindex]['datatype'] = 'text';
               if ($item->canUsePluginDataInjection()) {
                  //Datainjection specific
                  $options[$currentindex]['displaytype'] = 'multiline_text';
               }
               break;
            case "int(11)":
               if ($tmp != '') {
                  $options[$currentindex]['datatype'] = 'dropdown';
               } else {
                  $options[$currentindex]['datatype'] = 'integer';
               }

               if ($item->canUsePluginDataInjection()) {
                  if ($tmp != '') {
                     $options[$currentindex]['displaytype'] = 'dropdown';
                     $options[$currentindex]['checktype']   = 'text';
                  } else {
                     //Datainjection specific
                     $options[$currentindex]['displaytype'] = 'dropdown_integer';
                     $options[$currentindex]['checktype']   = 'integer';
                  }
               }
               break;
            case "float":
            case "decimal":
               $options[$currentindex]['datatype'] = $values['Type'];
               if ($item->canUsePluginDataInjection()) {
                  //Datainjection specific
                  $options[$currentindex]['display']   = 'text';
                  $options[$currentindex]['checktype'] = $values['Type'];
               }
               break;
            case "date":
               $options[$currentindex]['datatype'] = 'date';
               if ($item->canUsePluginDataInjection()) {
                  //Datainjection specific
                  $options[$currentindex]['displaytype'] = 'date';
                  $options[$currentindex]['checktype']   = 'date';
               }
               break;
            case "datetime":
               $options[$currentindex]['datatype'] = 'datetime';
               if ($item->canUsePluginDataInjection()) {
                  //Datainjection specific
                  $options[$currentindex]['displaytype'] = 'date';
                  $options[$currentindex]['checktype']   = 'date';
               }
               break;
         }
         $index = $currentindex + 1;
      }
      asort($options);
      return $options;
   }



   //Datainjection specific methods
   function isPrimaryType() {
      return true;
   }


   function connectedTo() {
      return array();
   }



   /**
    * Standard method to add an object into glpi
    *
    * @param values fields to add into glpi
    * @param options options used during creation
    * @return an array of IDs of newly created objects : for example array(Computer=>1, Networkport=>10)
    *
   **/
   function addOrUpdateObject($values=array(), $options=array()) {

      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }


   function getOptions($primary_type = '') {
      return Search::getOptions($primary_type);
   }


   function transfer($new_entity) {
      global $DB;
      if ($this->fields['id'] > 0 && $this->fields['entities_id'] != $new_entity) {
         //Update entity for this object
         $tmp['id']          = $this->fields['id'];
         $tmp['entities_id'] = $new_entity;
         $this->update($tmp);

         $toupdate = array('id' => $this->fields['id']);
         foreach (PluginGenericobjectSingletonObjectField::getInstance(get_called_class()) as $field => $data) {
            $table = getTableNameForForeignKeyField($field);

            //It is a dropdown table !
            if ($field != 'entities_id'
               && $table != ''
                  && isset($this->fields[$field]) && $this->fields[$field] > 0) {
               //Instanciate a new dropdown object
               $dropdown_itemtype = getItemTypeForTable($table);
               $dropdown          = new $dropdown_itemtype();
               $dropdown->getFromDB($this->fields[$field]);

               //If dropdown is only accessible in the other entity
               //do not go further
               if (!$dropdown->isEntityAssign()
                  || in_array($new_entity, getAncestorsOf('glpi_entities',
                                                          $dropdown->getEntityID()))) {
                  continue;
               } else {
                  $tmp   = array();
                  $where = "";
                  if ($dropdown instanceof CommonTreeDropdown) {
                     $tmp['completename'] = $dropdown->fields['completename'];
                     $where               = "`completename`='".
                                             addslashes_deep($tmp['completename'])."'";
                  } else {
                     $tmp['name'] = $dropdown->fields['name'];
                     $where       = "`name`='".addslashes_deep($tmp['name'])."'";
                  }
                  $tmp['entities_id'] = $new_entity;
                  $where             .= " AND `entities_id`='".$tmp['entities_id']."'";
                  //There's a dropdown value in the target entity
                  if ($found = $this->find($where)) {
                     $myfound = array_pop($found);
                     if ($myfound['id'] != $this->fields[$field]) {
                        $toupdate[$field] = $myfound['id'];
                     }
                  } else {
                     $clone = $dropdown->fields;
                     if ($dropdown instanceof CommonTreeDropdown) {
                        unset($clone['completename']);
                     }
                     unset($clone['id']);
                     $clone['entities_id'] = $new_entity;
                     $new_id               = $dropdown->import($clone);
                     $toupdate[$field]     = $new_id;
                  }
               }
            }
         }
         $this->update($toupdate);
      }
      return true;
   }

}
