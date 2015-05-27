<?php
/*
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE
Inventaire
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
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

class PluginMobileMenu extends CommonDBTM {
   
   
   public function showFirstLevel($message) {
      global $LANG, $CFG_GLPI;
      
      $menu = $this->getMenu();
      
      if ($message != '') {
         echo "<div class='ui-loader ui-body-a ui-corner-all' id='messagebox' style='top: 75px;display:block'>";
         echo "<h1>$message</h1>";
         echo "</div>";
         echo "<script>
               $('#messagebox').delay(800).fadeOut(2000);
         </script>";
      }
      
      echo "<div data-role='content'>";
      echo "<ul data-role='listview' data-inset='true' data-theme='c' data-dividertheme='a'>";
      echo "<li data-role='list-divider'>".$LANG['plugin_mobile']["title"]."</li>";
      
      $i=1;
      foreach ($menu as $part => $data) {
         if (isset($data['content']) && count($data['content'])) {
            echo "<li id='menu$i'>";
            $link=$CFG_GLPI["root_doc"]."/plugins/mobile/front/ss_menu.php?menu=".$part;
            
            if (Toolbox::strlen($data['title'])>14) {
               $data['title']=utf8_substr($data['title'],0,14)."...";
            }
            
            if (isset($data['icon'])) echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/mobile/pics/".$data['icon']."' class='ui-li-icon round-icon' />";
            echo "<a href=\"$link\" data-back='false'>".$data['title']."</a>";
            echo "</li>";
            $i++;
         }
      }
      echo "</ul>";
      echo "</div>";
   } 
   
   public function showSpecificMenu($item) {
      global $LANG, $CFG_GLPI;
      
      $menu = $this->getMenu();
      
      $class = "";
      if (largeScreen()) $class = "class='ui-grid-a'";
      
      echo "<div data-role='content'>";
      echo "<ul data-role='listview' data-inset='true' data-theme='c' data-dividertheme='a' $class>";
      echo "<li data-role='list-divider'>";
      echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/mobile/pics/".$menu[$item]['icon']."' class='title_icon' />";
      echo $menu[$item]['title']."</li>";
      
      $cpt = 0;
      foreach ($menu[$item]['content'] as $key => $val) {
         if (isset($val['page'])&&isset($val['title'])) {
            $link = $CFG_GLPI["root_doc"].$val['page'];
            $link = $CFG_GLPI["root_doc"]."/plugins/mobile".$val['page'];
            $external  = "";
            if (isset($val['external']) && $val['external']) $external  = "rel='external'";
            
            if (largeScreen()) $class = "class='ui-block-".chr(($cpt%2)+97)."'";
            
            echo "<li $class>";
            if (isset($val['icon'])) echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/mobile/pics/".$val['icon']."' class='ui-li-icon round-icon' />";
            echo "<a href='$link' data-back='false' $external>".$val['title']."</a>";
            echo "</li>\n";
            $cpt++;
         }
      }
      
      echo "</ul>";
      echo "</div>";
   } 
   
   public function getMenu($sector="none",$item="none",$option="") {
      global $LANG, $CFG_GLPI;
    
       
     // INVENTORY
      $showstate=false;
      $menu['inventory']['title']=$LANG['Menu'][38];
      $menu['inventory']['default']='/front/computer.php';
      $menu['inventory']['icon']='icons/inventory.png';

      if (Session::haveRight("computer",CREATE)) {
         
         $menu['inventory']['content']['computer']['title']=$LANG['Menu'][0];
         $menu['inventory']['content']['computer']['shortcut']='c';
         $menu['inventory']['content']['computer']['icon']='icons/computer.png';
         $menu['inventory']['content']['computer']['page']='/front/search.php?itemtype=computer&menu=inventory&ssmenu=computer';
         $menu['inventory']['content']['computer']['links']['search']='/front/computer.php';
         if (Session::haveRight("computer",UPDATE)) {
            $menu['inventory']['content']['computer']['links']['add']='/front/setup.templates.php?' .
                   'itemtype=Computer&amp;add=1';
            $menu['inventory']['content']['computer']['links']['template']='/front/setup.templates.php?' .
                   'itemtype=Computer&amp;add=0';
         }
         $showstate=true;
      }
      if (Session::haveRight("monitor",CREATE)) {
         $menu['inventory']['content']['monitor']['title']=$LANG['Menu'][3];
         $menu['inventory']['content']['monitor']['shortcut']='m';
         $menu['inventory']['content']['monitor']['icon']='icons/monitor.png';
         $menu['inventory']['content']['monitor']['page']='/front/search.php?itemtype=monitor&menu=inventory&ssmenu=monitor';
         $menu['inventory']['content']['monitor']['links']['search']='/front/monitor.php';
         if (Session::haveRight("monitor",UPDATE)) {
            $menu['inventory']['content']['monitor']['links']['add']='/front/setup.templates.php?' .
                   'itemtype=Monitor&amp;add=1';
            $menu['inventory']['content']['monitor']['links']['template']='/front/setup.templates.php?' .
                   'itemtype=Monitor&amp;add=0';
         }
         $showstate=true;
      }
      if (Session::haveRight("software",CREATE)) {
         $menu['inventory']['content']['software']['title']=$LANG['Menu'][4];
         $menu['inventory']['content']['software']['shortcut']='s';
         $menu['inventory']['content']['software']['icon']='icons/software.png';
         $menu['inventory']['content']['software']['page']='/front/search.php?itemtype=software&menu=inventory&ssmenu=software';
         $menu['inventory']['content']['software']['links']['search']='/front/software.php';
         if (Session::haveRight("software",UPDATE)){
            $menu['inventory']['content']['software']['links']['add']='/front/setup.templates.php?' .
                   'itemtype=Software&amp;add=1';
            $menu['inventory']['content']['software']['links']['template']='/front/setup.templates.php?' .
                   'itemtype=Software&amp;add=0';
         }
         $showstate=true;
      }
      if (Session::haveRight("networking",CREATE)) {
         $menu['inventory']['content']['networking']['title']=$LANG['Menu'][1];
         $menu['inventory']['content']['networking']['shortcut']='n';
         $menu['inventory']['content']['networking']['icon']='icons/network.png';
         $menu['inventory']['content']['networking']['page']='/front/search.php?itemtype=networkequipment&menu=inventory&ssmenu=networking';
         $menu['inventory']['content']['networking']['links']['search']='/front/networkequipment.php';
         if (Session::haveRight("networking",UPDATE)) {
            $menu['inventory']['content']['networking']['links']['add']='/front/setup.templates.php?' .
                   'itemtype=NetworkEquipment&amp;add=1';
            $menu['inventory']['content']['networking']['links']['template']='/front/setup.templates.php?' .
                   'itemtype=NetworkEquipment&amp;add=0';
         }
         $showstate=true;
      }
      if (Session::haveRight("peripheral",CREATE)) {
         $menu['inventory']['content']['peripheral']['title']=$LANG['Menu'][16];
         $menu['inventory']['content']['peripheral']['shortcut']='n';
         $menu['inventory']['content']['peripheral']['icon']='icons/connect.png';
         $menu['inventory']['content']['peripheral']['page']='/front/search.php?itemtype=peripheral&menu=inventory&ssmenu=peripheral';
         $menu['inventory']['content']['peripheral']['links']['search']='/front/peripheral.php';
         if (Session::haveRight("peripheral",UPDATE)) {
            $menu['inventory']['content']['peripheral']['links']['add']='/front/setup.templates.php?' .
                   'itemtype=Peripheral&amp;add=1';
            $menu['inventory']['content']['peripheral']['links']['template']='/front/setup.templates.php?' .
                   'itemtype=Peripheral&amp;add=0';
         }
         $showstate=true;
      }
      if (Session::haveRight("printer",CREATE)) {
         $menu['inventory']['content']['printer']['title']=$LANG['Menu'][2];
         $menu['inventory']['content']['printer']['shortcut']='p';
         $menu['inventory']['content']['printer']['icon']='icons/print.png';
         $menu['inventory']['content']['printer']['page']='/front/search.php?itemtype=printer&menu=inventory&ssmenu=printer';
         $menu['inventory']['content']['printer']['links']['search']='/front/printer.php';
         if (Session::haveRight("printer",UPDATE)) {
            $menu['inventory']['content']['printer']['links']['add']='/front/setup.templates.php?' .
                   'itemtype=Printer&amp;add=1';
            $menu['inventory']['content']['printer']['links']['template']='/front/setup.templates.php?' .
                   'itemtype=Printer&amp;add=0';
         }
         $showstate=true;
      }
      if (Session::haveRight("cartridge",CREATE)) {
         $menu['inventory']['content']['cartridge']['title']=$LANG['Menu'][21];
         $menu['inventory']['content']['cartridge']['shortcut']='c';
         $menu['inventory']['content']['cartridge']['icon']='icons/cartridge.png';
         $menu['inventory']['content']['cartridge']['page']='/front/search.php?itemtype=cartridgeitem&menu=inventory&ssmenu=cartridge';
         $menu['inventory']['content']['cartridge']['links']['search']='/front/cartridgeitem.php';
         if (Session::haveRight("cartridge",UPDATE)) {
            $menu['inventory']['content']['cartridge']['links']['add']='/front/cartridgeitem.form.php';
         }
      }
      if (Session::haveRight("consumable",CREATE)) {
         $menu['inventory']['content']['consumable']['title']=$LANG['Menu'][32];
         $menu['inventory']['content']['consumable']['shortcut']='g';
         $menu['inventory']['content']['consumable']['icon']='icons/coffe_cup.png';
         $menu['inventory']['content']['consumable']['page']='/front/search.php?itemtype=consumableitem&menu=inventory&ssmenu=consumable';
         $menu['inventory']['content']['consumable']['links']['search']='/front/consumableitem.php';
         if (Session::haveRight("consumable",UPDATE)) {
            $menu['inventory']['content']['consumable']['links']['add']='/front/consumableitem.form.php';
         }
         $menu['inventory']['content']['consumable']['links']['summary']='/front/consumableitem.php?' .
               'synthese=yes';
      }
      if (Session::haveRight("phone",CREATE)) {
         $menu['inventory']['content']['phone']['title']=$LANG['Menu'][34];
         $menu['inventory']['content']['phone']['shortcut']='t';
         $menu['inventory']['content']['phone']['icon']='icons/phone.png';
         $menu['inventory']['content']['phone']['page']='/front/search.php?itemtype=phone&menu=inventory&ssmenu=phone';
         $menu['inventory']['content']['phone']['links']['search']='/front/phone.php';
         if (Session::haveRight("phone",UPDATE)){
            $menu['inventory']['content']['phone']['links']['add']='/front/setup.templates.php?' .
                   'itemtype=Phone&amp;add=1';
            $menu['inventory']['content']['phone']['links']['template']='/front/setup.templates.php?' .
                   'itemtype=Phone&amp;add=0';
         }
         $showstate=true;
      }
      if ($showstate){
         $menu['inventory']['content']['state']['title']=$LANG['Menu'][28];
         $menu['inventory']['content']['state']['shortcut']='n';
         $menu['inventory']['content']['state']['icon']='icons/list.png';
         $menu['inventory']['content']['state']['page']='/front/search.php?itemtype=state&menu=inventory&ssmenu=state';
         $menu['inventory']['content']['state']['links']['search']='/front/states.php';
         $menu['inventory']['content']['state']['links']['summary']='/front/states.php?synthese=yes';
      }      
      
      

      // ASSISTANCE
      $menu['maintain']['title']=$LANG['title'][24];
      $menu['maintain']['default']='/front/ticket.php';
      $menu['maintain']['icon']='icons/assistance.png';

   //   if (Session::haveRight("observe_ticket","1") || Session::haveRight("show_all_ticket","1") || Session::haveRight("create_ticket","1")) {

      if (Session::haveRight("ticket",CREATE) || Session::haveRight("ticket", UPDATE)  ) {
      	//if (Session::haveRight("ticket",CREATE) ) {

         $menu['maintain']['content']['ticket']['title']=$LANG['Menu'][5];
         $menu['maintain']['content']['ticket']['icon']='icons/ticket.png';
         $menu['maintain']['content']['ticket']['shortcut']='t';
         $menu['maintain']['content']['ticket']['page']='/front/search.php?itemtype=ticket&menu=maintain&ssmenu=ticket';
         $menu['maintain']['content']['ticket']['links']['search']='/front/ticket.php';
         $menu['maintain']['content']['ticket']['links']['search']='/front/ticket.php';

         if (Session::haveRight('ticket',Ticket::READALL)) {
            $opt=array();
            $opt['reset']  = 'reset';
            $opt['field'][0]      = 55; // validation status
            $opt['searchtype'][0] = 'equals';
            $opt['contains'][0]   = 'waiting';
            $opt['link'][0]        = 'AND';

            $opt['field'][1]      = 59; // validation aprobator
            $opt['searchtype'][1] = 'equals';
            $opt['contains'][1]   = Session::getLoginUserID();
            $opt['link'][1]        = 'AND';


            $pic_validate="<img title=\"".$LANG['validation'][15]."\" alt=\"".$LANG['validation'][15]."\" src='".
                                    $CFG_GLPI["root_doc"]."/pics/menu_showall.png'>";
            $menu['maintain']['content']['ticket']['links'][$pic_validate]='/front/ticket.php?'.Toolbox::append_params($opt,'&amp;');
         }
      }
      
      if (Session::haveRight("ticket",CREATE) || Session::haveRight("ticket",Ticket::READALL)) {
         $menu['maintain']['content']['helpdesk']['title']=$LANG['job'][13];
         $menu['maintain']['content']['helpdesk']['icon']='icons/ticket.png';
         $menu['maintain']['content']['helpdesk']['shortcut']='c';
         $menu['maintain']['content']['helpdesk']['page']='/front/helpdesk.php';

         $menu['maintain']['content']['ticket']['links']['add']='/front/ticket.form.php';
      }
      if (Session::haveRight("planning",CREATE) || Session::haveRight("planning",Ticket::READALL)) {
         $menu['maintain']['content']['planning']['title']= __('Planning');//$LANG['Menu'][29];
         $menu['maintain']['content']['planning']['shortcut']='l';
         $menu['maintain']['content']['planning']['icon']='icons/planning.png';
         $menu['maintain']['content']['planning']['page']='/front/planning.php';
         $menu['maintain']['content']['planning']['links']['search']='/front/planning.php';
         $menu['maintain']['content']['planning']['external'] = true;
      }
      if (Session::haveRight("statistic","1")) {
         $menu['maintain']['content']['stat']['title']=$LANG['Menu'][13];
         $menu['maintain']['content']['stat']['shortcut']='1';
         $menu['maintain']['content']['stat']['icon']='icons/charts.png';
         $menu['maintain']['content']['stat']['page']='/front/stat.php';
      }
      
    
     
      // FINANCIAL
      
      if (Session::haveRight("budget",READ)) {
         $menu['financial']['content']['budget']['title']=$LANG['financial'][110];
         $menu['financial']['content']['budget']['shortcut']='n';
         $menu['financial']['content']['budget']['icon']='icons/euro.png';
         $menu['financial']['content']['budget']['page']='/front/search.php?itemtype=budget&menu=financial&ssmenu=budget';
         $menu['financial']['content']['budget']['links']['search']='/front/budget.php';
         if (Session::haveRight("contract",UPDATE)) {
            $menu['financial']['content']['budget']['links']['add']='/front/setup.templates.php?'.
               'itemtype=Budget&amp;add=1';
            $menu['financial']['content']['budget']['links']['template']='/front/setup.templates.php?'.
               'itemtype=Budget&amp;add=0';
         }
      }

      $menu['financial']['title']=$LANG['Menu'][26];
      $menu['financial']['icon']='icons/wallet.png';

      if (Session::haveRight("contact_enterprise",READ)) {
         $menu['financial']['content']['supplier']['title']=$LANG['Menu'][23];
         $menu['financial']['content']['supplier']['shortcut']='e';
         $menu['financial']['content']['supplier']['icon']='icons/track.png';
         $menu['financial']['content']['supplier']['page']='/front/search.php?itemtype=supplier&menu=financial&ssmenu=supplier';
         $menu['financial']['content']['supplier']['links']['search']='/front/supplier.php';

         $menu['financial']['default']='/front/contact.php';
         $menu['financial']['content']['contact']['title']=$LANG['Menu'][22];
         $menu['financial']['content']['contact']['icon']='icons/contact.png';
         $menu['financial']['content']['contact']['shortcut']='t';
         $menu['financial']['content']['contact']['page']='/front/search.php?itemtype=contact&menu=financial&ssmenu=contact';
         $menu['financial']['content']['contact']['links']['search']='/front/contact.php';

         if (Session::haveRight("contact_enterprise",UPDATE)) {
            $menu['financial']['content']['contact']['links']['add']='/front/contact.form.php';
            $menu['financial']['content']['supplier']['links']['add']='/front/supplier.form.php';
         }
      }

      if (Session::haveRight("contract",READ)) {
         $menu['financial']['content']['contract']['title']=$LANG['Menu'][25];
         $menu['financial']['content']['contract']['shortcut']='n';
         $menu['financial']['content']['contract']['icon']='icons/case.png';
         $menu['financial']['content']['contract']['page']='/front/search.php?itemtype=contract&menu=financial&ssmenu=contract';
         $menu['financial']['content']['contract']['links']['search']='/front/contract.php';
         if (Session::haveRight("contract",UPDATE)) {
            $menu['financial']['content']['contract']['links']['add']='/front/contract.form.php';
         }
      }

      if (Session::haveRight("document",READ)) {
         $menu['financial']['content']['document']['title']=$LANG['Menu'][27];
         $menu['financial']['content']['document']['shortcut']='d';
         $menu['financial']['content']['document']['icon']='icons/doc.png';
         $menu['financial']['content']['document']['page']='/front/search.php?itemtype=document&menu=financial&ssmenu=document';
         $menu['financial']['content']['document']['links']['search']='/front/document.php';
         if (Session::haveRight("document",UPDATE)) {
            $menu['financial']['content']['document']['links']['add']='/front/document.form.php';
         }
      }

      // UTILS
      /*$menu['utils']['title']=$LANG['Menu'][18];
      $menu['utils']['default']='/front/reminder.php';

      $menu['utils']['content']['reminder']['title']=$LANG['title'][37];
      $menu['utils']['content']['reminder']['page']='/front/search.php?itemtype=document&menu=utils&ssmenu=reminder';
      $menu['utils']['content']['reminder']['links']['search']='/front/reminder.php';
      $menu['utils']['content']['reminder']['links']['add']='/front/reminder.form.php';

      if (Session::haveRight("knowbase",READ) || Session::haveRight("faq",READ)) {
         $menu['utils']['content']['knowbase']['title']=$LANG['Menu'][19];
         $menu['utils']['content']['knowbase']['page']='/front/search.php?itemtype=knowbaseitem&menu=utils&ssmenu=knowbase';
         $menu['utils']['content']['knowbase']['links']['search']='/front/knowbaseitem.php';
         if (Session::haveRight("knowbase",UPDATE) || Session::haveRight("faq",UPDATE)) {
            $menu['utils']['content']['knowbase']['links']['add']='/front/knowbaseitem.form.php?id=new';
         }
      }

      if (Session::haveRight("reservation_helpdesk","1") || Session::haveRight("reservation_central",READ)) {
         $menu['utils']['content']['reservation']['title']=$LANG['Menu'][17];
         $menu['utils']['content']['reservation']['page']='/front/search.php?itemtype=reservationitem&menu=utils&ssmenu=reservation';
         $menu['utils']['content']['reservation']['links']['search']='/front/reservationitem.php';
         $menu['utils']['content']['reservation']['links']['showall']='/front/reservation.php';
      }

      if (Session::haveRight("reports",READ)) {
         $menu['utils']['content']['report']['title']=$LANG['Menu'][6];
         $menu['utils']['content']['report']['page']='/front/report.php';
      }

      if ($CFG_GLPI["use_ocs_mode"] && Session::haveRight("ocsng",UPDATE)) {
         $menu['utils']['content']['ocsng']['title']=$LANG['Menu'][33];
         $menu['utils']['content']['ocsng']['page']='/front/ocsng.php';
      }

      // PLUGINS
      if (isset($PLUGIN_HOOKS["menu_entry"]) && count($PLUGIN_HOOKS["menu_entry"])) {
         $menu['plugins']['title']=$LANG['common'][29];
         $plugins=array();
         foreach  ($PLUGIN_HOOKS["menu_entry"] as $plugin => $active) {
            if ($active) { // true or a string
               $function="plugin_version_$plugin";
               if (function_exists($function)) {
                  $plugins[$plugin]=$function();
               }
            }
         }
         if (count($plugins)) {
            $list=array();
            foreach ($plugins as $key => $val) {
               $list[$key]=$val["name"];
            }
            asort($list);
            foreach ($list as $key => $val) {
               $menu['plugins']['content'][$key]['title']=$val;
               $menu['plugins']['content'][$key]['page']='/plugins/'.$key.'/';
               if (is_string($PLUGIN_HOOKS["menu_entry"][$key])) {
                  $menu['plugins']['content'][$key]['page'] .= $PLUGIN_HOOKS["menu_entry"][$key];
               }

               // Set default link for plugins
               if (!isset($menu['plugins']['default'])) {
                  $menu['plugins']['default']=$menu['plugins']['content'][$key]['page'];
               }

               if ($sector=="plugins"&&$item==$key) {
                  if (isset($PLUGIN_HOOKS["submenu_entry"][$key])
                      && is_array($PLUGIN_HOOKS["submenu_entry"][$key])) {

                     foreach ($PLUGIN_HOOKS["submenu_entry"][$key] as $name => $link) {

                        // New complete option management
                        if ($name=="options") {
                           $menu['plugins']['content'][$key]['options']=$link;
                        } else { // Keep it for compatibility
                           if (is_array($link)) {
                              // Simple link option
                              if (isset($link[$option])) {
                                 $menu['plugins']['content'][$key]['links'][$name]='/plugins/'.$key.'/'.
                                                                                 $link[$option];
                              }
                           } else {
                              $menu['plugins']['content'][$key]['links'][$name]='/plugins/'.$key.'/'.$link;
                           }
                        }
                     }
                  }
               }
            }
         }
      }*/

      /// ADMINISTRATION
      $menu['admin']['title']=$LANG['Menu'][15];
      $menu['admin']['default']='/front/user.php';
      $menu['admin']['icon']='icons/cogs.png';
      
      if (Session::haveRight("user",READ)) {
         $menu['admin']['content']['user']['title']=$LANG['Menu'][14];
         $menu['admin']['content']['user']['shortcut']='u';
         $menu['admin']['content']['user']['icon']='icons/user.png';
         $menu['admin']['content']['user']['page']='/front/search.php?itemtype=user&menu=admin&ssmenu=user';
         $menu['admin']['content']['user']['links']['search']='/front/user.php';
         if (Session::haveRight("user",UPDATE)) {
            $menu['admin']['content']['user']['links']['add']="/front/user.form.php";
         }

        $menu['admin']['content']['user']['options']['ldap']['title']=$LANG['login'][2];
        $menu['admin']['content']['user']['options']['ldap']['page']="/front/ldap.php";
      }
      if (Session::haveRight("group",READ)) {
         $menu['admin']['content']['group']['title']=$LANG['Menu'][36];
         $menu['admin']['content']['group']['shortcut']='g';
         $menu['admin']['content']['group']['icon']='icons/users.png';
         $menu['admin']['content']['group']['page']='/front/search.php?itemtype=group&menu=admin&ssmenu=group';
         $menu['admin']['content']['group']['links']['search']='/front/group.php';
         if (Session::haveRight("group",UPDATE)) {
            $menu['admin']['content']['group']['links']['add']="/front/group.form.php";
            $menu['admin']['content']['group']['options']['ldap']['title']=$LANG['login'][2];
            $menu['admin']['content']['group']['options']['ldap']['page']="/front/ldap.group.php";
         }
      }

      if (Session::haveRight("entity",READ)) {
         $menu['admin']['content']['entity']['title']=$LANG['Menu'][37];
         $menu['admin']['content']['entity']['shortcut']='z';
         $menu['admin']['content']['entity']['icon']='icons/layers_2.png';
         $menu['admin']['content']['entity']['page']='/front/search.php?itemtype=entity&menu=admin&ssmenu=entity';
         $menu['admin']['content']['entity']['links']['search']='/front/entity.php';
         $menu['admin']['content']['entity']['links']['add']="/front/entity.form.php";
      }

      /*if (Session::haveRight("rule_ldap",READ)
            || Session::haveRight("rule_ocs",READ)
               || Session::haveRight("entity_rule_ticket",READ)
                  || Session::haveRight("rule_softwarecategories",READ)
                     || Session::haveRight("rule_mailcollector",READ)) {

         $menu['admin']['content']['rule']['title']=$LANG['rulesengine'][17];
         $menu['admin']['content']['rule']['shortcut']='r';
         $menu['admin']['content']['rule']['page']='/front/search.php?itemtype=rule&menu=admin&ssmenu=rule';
         
         if ($sector=='admin' && $item == 'rule') {

            $menu['admin']['content']['rule']['options']['ocs']['title']=$LANG['Menu'][33];
            $menu['admin']['content']['rule']['options']['ocs']['page']='/front/ruleocs.php';
            $menu['admin']['content']['rule']['options']['ocs']['links']['search']='/front/ruleocs.php';
            if (Session::haveRight("rule_ocs",UPDATE)) {
               $menu['admin']['content']['rule']['options']['ocs']['links']['add']='/front/ruleocs.form.php';
            }

            $menu['admin']['content']['rule']['options']['right']['title']=$LANG['Menu'][37]." / ".$LANG['Menu'][41];
            $menu['admin']['content']['rule']['options']['right']['page']='/front/ruleright.php';
            $menu['admin']['content']['rule']['options']['right']['links']['search']='/front/ruleright.php';
            if (Session::haveRight("rule_ldap",UPDATE)) {
               $menu['admin']['content']['rule']['options']['right']['links']['add']='/front/ruleright.form.php';
            }

            $menu['admin']['content']['rule']['options']['mailcollector']['title']=$LANG['rulesengine'][70];
            $menu['admin']['content']['rule']['options']['mailcollector']['page']='/front/rulemailcollector.php';
            $menu['admin']['content']['rule']['options']['mailcollector']['links']['search']='/front/rulemailcollector.php';
            if (Session::haveRight("rule_mailcollector",UPDATE)) {
               $menu['admin']['content']['rule']['options']['mailcollector']['links']['add']='/front/rulemailcollector.form.php';
            }

            $menu['admin']['content']['rule']['options']['ticket']['title']=$LANG['Menu'][5];
            $menu['admin']['content']['rule']['options']['ticket']['page']='/front/ruleticket.php';
            $menu['admin']['content']['rule']['options']['ticket']['links']['search']='/front/ruleticket.php';
            if (Session::haveRight("entity_rule_ticket",UPDATE)) {
               $menu['admin']['content']['rule']['options']['ticket']['links']['add']='/front/ruleticket.form.php';
            }

            $menu['admin']['content']['rule']['options']['softwarecategories']['title']=$LANG['softwarecategories'][5];
            $menu['admin']['content']['rule']['options']['softwarecategories']['page']='/front/rulesoftwarecategory.php';
            $menu['admin']['content']['rule']['options']['softwarecategories']['links']['search']='/front/rulesoftwarecategory.php';
            if (Session::haveRight("rule_softwarecategories",UPDATE)) {
               $menu['admin']['content']['rule']['options']['softwarecategories']['links']['add']='/front/rulesoftwarecategory.form.php';
            }
         }
      }

      if (Session::haveRight("rule_dictionnary_dropdown",READ) || Session::haveRight("rule_dictionnary_software",READ)) {
         $menu['admin']['content']['dictionnary']['title']=$LANG['rulesengine'][77];
         $menu['admin']['content']['dictionnary']['shortcut']='r';
         $menu['admin']['content']['dictionnary']['page']='/front/dictionnary.php';

         if ($sector=='admin' && $item == 'dictionnary') {

            $menu['admin']['content']['dictionnary']['options']['manufacturers']['title']=$LANG['common'][5];
            $menu['admin']['content']['dictionnary']['options']['manufacturers']['page']='/front/ruledictionnarymanufacturer.php';
            $menu['admin']['content']['dictionnary']['options']['manufacturers']['links']['search']='/front/ruledictionnarymanufacturer.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['manufacturers']['links']['add']='/front/ruledictionnarymanufacturer.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['software']['title']=$LANG['Menu'][4];
            $menu['admin']['content']['dictionnary']['options']['software']['page']='/front/ruledictionnarysoftware.php';
            $menu['admin']['content']['dictionnary']['options']['software']['links']['search']='/front/ruledictionnarysoftware.php';
            if (Session::haveRight("rule_dictionnary_software",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['software']['links']['add']='/front/ruledictionnarysoftware.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['model.computer']['title']=$LANG['setup'][91];
            $menu['admin']['content']['dictionnary']['options']['model.computer']['page']='/front/ruledictionnarycomputermodel.php';
            $menu['admin']['content']['dictionnary']['options']['model.computer']['links']['search']='/front/ruledictionnarycomputermodel.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['model.computer']['links']['add']='/front/ruledictionnarycomputermodel.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['model.monitor']['title']=$LANG['setup'][94];
            $menu['admin']['content']['dictionnary']['options']['model.monitor']['page']='/front/ruledictionnarymodelmonitor.php';
            $menu['admin']['content']['dictionnary']['options']['model.monitor']['links']['search']='/front/ruledictionnarymonitormodel.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['model.monitor']['links']['add']='/front/ruledictionnarymonitormodel.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['model.printer']['title']=$LANG['setup'][96];
            $menu['admin']['content']['dictionnary']['options']['model.printer']['page']='/front/ruledictionnaryprintermodel.php';
            $menu['admin']['content']['dictionnary']['options']['model.printer']['links']['search']='/front/ruledictionnaryprintermodel.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['model.printer']['links']['add']='/front/ruledictionnaryprintermodel.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['model.peripheral']['title']=$LANG['setup'][97];
            $menu['admin']['content']['dictionnary']['options']['model.peripheral']['page']='/front/ruledictionnaryperipheralmodel.php';
            $menu['admin']['content']['dictionnary']['options']['model.peripheral']['links']['search']='/front/ruledictionnaryperipheralmodel.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['model.peripheral']['links']['add']='/front/ruledictionnaryperipheralmodel.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['model.networking']['title']=$LANG['setup'][95];
            $menu['admin']['content']['dictionnary']['options']['model.networking']['page']='/front/ruledictionnarynetworkequipmentmodel.php';
            $menu['admin']['content']['dictionnary']['options']['model.networking']['links']['search']='/front/ruledictionnarynetworkequipmentmodel.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['model.networking']['links']['add']='/front/ruledictionnarynetworkequipmentmodel.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['model.phone']['title']=$LANG['setup'][503];
            $menu['admin']['content']['dictionnary']['options']['model.phone']['page']='/front/ruledictionnaryphonemodel.php';
            $menu['admin']['content']['dictionnary']['options']['model.phone']['links']['search']='/front/ruledictionnaryphonemodel.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['model.phone']['links']['add']='/front/ruledictionnaryphonemodel.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['type.computer']['title']=$LANG['setup'][4];
            $menu['admin']['content']['dictionnary']['options']['type.computer']['page']='/front/ruledictionnarycomputertype.php';
            $menu['admin']['content']['dictionnary']['options']['type.computer']['links']['search']='/front/ruledictionnarycomputertype.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['type.computer']['links']['add']='/front/ruledictionnarycomputertype.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['type.monitor']['title']=$LANG['setup'][44];
            $menu['admin']['content']['dictionnary']['options']['type.monitor']['page']='/front/ruledictionnarymonitortype.php';
            $menu['admin']['content']['dictionnary']['options']['type.monitor']['links']['search']='/front/ruledictionnarymonitortype.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['type.monitor']['links']['add']='/front/ruledictionnarymonitortype.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['type.printer']['title']=$LANG['setup'][43];
            $menu['admin']['content']['dictionnary']['options']['type.printer']['page']='/front/ruledictionnaryprintertype.php';
            $menu['admin']['content']['dictionnary']['options']['type.printer']['links']['search']='/front/ruledictionnaryprintertype.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['type.printer']['links']['add']='/front/ruledictionnaryprintertype.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['type.peripheral']['title']=$LANG['setup'][69];
            $menu['admin']['content']['dictionnary']['options']['type.peripheral']['page']='/front/ruledictionnaryperipheraltype.php';
            $menu['admin']['content']['dictionnary']['options']['type.peripheral']['links']['search']='/front/ruledictionnaryperipheraltype.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['type.peripheral']['links']['add']='/front/ruledictionnaryperipheraltype.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['type.networking']['title']=$LANG['setup'][42];
            $menu['admin']['content']['dictionnary']['options']['type.networking']['page']='/front/ruledictionnarynetworkequipmenttype.php';
            $menu['admin']['content']['dictionnary']['options']['type.networking']['links']['search']='/front/ruledictionnarynetworkequipmenttype.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['type.networking']['links']['add']='/front/ruledictionnarynetworkequipmenttype.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['type.phone']['title']=$LANG['setup'][504];
            $menu['admin']['content']['dictionnary']['options']['type.phone']['page']='/front/ruledictionnaryphonetype.php';
            $menu['admin']['content']['dictionnary']['options']['type.phone']['links']['search']='/front/ruledictionnaryphonetype.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['type.phone']['links']['add']='/front/ruledictionnaryphonetype.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['os']['title']=$LANG['computers'][9];
            $menu['admin']['content']['dictionnary']['options']['os']['page']='/front/ruledictionnaryoperatingsystem.php';
            $menu['admin']['content']['dictionnary']['options']['os']['links']['search']='/front/ruledictionnaryoperatingsystem.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['os']['links']['add']='/front/ruledictionnaryoperatingsystem.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['os_sp']['title']=$LANG['computers'][53];
            $menu['admin']['content']['dictionnary']['options']['os_sp']['page']='/front/ruledictionnaryoperatingsystemservicepack.php';
            $menu['admin']['content']['dictionnary']['options']['os_sp']['links']['search']='/front/ruledictionnaryoperatingsystemservicepack.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['os_sp']['links']['add']='/front/ruledictionnaryoperatingsystemservicepack.form.php';
            }

            $menu['admin']['content']['dictionnary']['options']['os_version']['title']=$LANG['computers'][52];
            $menu['admin']['content']['dictionnary']['options']['os_version']['page']='/front/ruledictionnaryoperatingsystemversion.php';
            $menu['admin']['content']['dictionnary']['options']['os_version']['links']['search']='/front/rruledictionnaryoperatingsystemversion.php';
            if (Session::haveRight("rule_dictionnary_dropdown",UPDATE)) {
               $menu['admin']['content']['dictionnary']['options']['os_version']['links']['add']='/front/ruledictionnaryoperatingsystemversion.form.php';
            }
         }
      }*/


      if (Session::haveRight("profile",READ)) {
         $menu['admin']['content']['profile']['title']=$LANG['Menu'][35];
         $menu['admin']['content']['profile']['shortcut']='p';
         $menu['admin']['content']['profile']['icon']='icons/key.png';
         $menu['admin']['content']['profile']['page']='/front/search.php?itemtype=profile&menu=admin&ssmenu=profile';
         $menu['admin']['content']['profile']['links']['search']="/front/profile.php";
         if (Session::haveRight("profile",UPDATE)) {
            $menu['admin']['content']['profile']['links']['add']="/front/profile.form.php";
         }
      }

      if (Session::haveRight("transfer",READ )&& Session::isMultiEntitiesMode()) {
         $menu['admin']['content']['transfer']['title']=$LANG['transfer'][1];
         $menu['admin']['content']['transfer']['shortcut']='t';
         $menu['admin']['content']['transfer']['icon']='icons/folder_arrow.png';
         $menu['admin']['content']['transfer']['page']='/front/search.php?itemtype=transfer&menu=admin&ssmenu=transfer';
         $menu['admin']['content']['transfer']['links']['search']="/front/transfer.php";
         if (Session::haveRight("transfer",UPDATE)) {
            $menu['admin']['content']['transfer']['links']['summary']="/front/transfer.action.php";
            $menu['admin']['content']['transfer']['links']['add']="/front/transfer.form.php";
         }
      }

      /*if (Session::haveRight("backup",UPDATE)) {
         $menu['admin']['content']['backup']['title']=$LANG['Menu'][12];
         $menu['admin']['content']['backup']['shortcut']='b';
         $menu['admin']['content']['backup']['page']='/front/search.php?itemtype=backup&menu=admin&ssmenu=backup';
      }*/

      /*if (Session::haveRight("logs",READ)) {
         $menu['admin']['content']['log']['title']=$LANG['Menu'][30];
         $menu['admin']['content']['log']['shortcut']='l';
         $menu['admin']['content']['log']['page']='/front/event.php';
         $menu['admin']['content']['log']['page']='/front/search.php?itemtype=log&menu=admin&ssmenu=log';
      }*/

      /*/// CONFIG
      $config=array();
      $addconfig=array();
      $menu['config']['title']=$LANG['common'][12];

      if (Session::haveRight("dropdown",READ) || Session::haveRight("entity_dropdown",READ)) {
         $menu['config']['content']['dropdowns']['title']=$LANG['setup'][0];
         $menu['config']['content']['dropdowns']['page']='/front/dropdown.php';
         $menu['config']['default']='/front/dropdown.php';

         if ($item=="dropdowns") {
            $dps = Dropdown::getStandardDropdownItemTypes();

            foreach ($dps as $tab) {
               foreach ($tab as $key => $val) {
                  if ($key == $option) {
                     $tmp = new $key();
                     $menu['config']['content']['dropdowns']['options'][$option]['title']=$val;
                     $menu['config']['content']['dropdowns']['options'][$option]['page']=
                                                   $tmp->getSearchURL(false);
                     $menu['config']['content']['dropdowns']['options'][$option]['links']['search']=
                                                   $tmp->getSearchURL(false);
                     if ($tmp->canCreate()) {
                        $menu['config']['content']['dropdowns']['options'][$option]['links']['add']=
                                                   $tmp->getFormURL(false);
                     }
                  }
               }
            }
         }
      }

      if (Session::haveRight("device",UPDATE)) {
         $menu['config']['content']['device']['title']=$LANG['title'][30];
         $menu['config']['content']['device']['page']='/front/device.php';

         if ($item=="device") {
            $dps = Dropdown::getDeviceItemTypes();

            foreach ($dps as $tab) {
               foreach ($tab as $key => $val) {
                  if ($key == $option) {
                     $tmp = new $key();
                     $menu['config']['content']['device']['options'][$option]['title']=$val;
                     $menu['config']['content']['device']['options'][$option]['page']=
                                                   $tmp->getSearchURL(false);
                     $menu['config']['content']['device']['options'][$option]['links']['search']=
                                                   $tmp->getSearchURL(false);
                     if ($tmp->canCreate()) {
                        $menu['config']['content']['device']['options'][$option]['links']['add']=
                                                   $tmp->getFormURL(false);
                     }
                  }
               }
            }
         }
      }


      if (Session::haveRight("config",UPDATE) || Session::haveRight("notification",READ)) {

         $menu['config']['content']['mailing']['title']=$LANG['setup'][704];
         $menu['config']['content']['mailing']['page']='/front/setup.notification.php';
         $menu['config']['content']['mailing']['options']['notification']['title']=$LANG['setup'][704];
         $menu['config']['content']['mailing']['options']['notification']['page']='/front/notification.php';
         $menu['config']['content']['mailing']['options']['notification']['links']['add']='/front/notification.form.php';
         $menu['config']['content']['mailing']['options']['notification']['links']['search']='/front/notification.php';

      }

      if (Session::haveRight("config",UPDATE)) {

         $menu['config']['content']['config']['title']=$LANG['setup'][703];
         $menu['config']['content']['config']['page']='/front/config.form.php';

         $menu['config']['content']['crontask']['title']=$LANG['crontask'][0];
         $menu['config']['content']['crontask']['page']='/front/crontask.php';
         $menu['config']['content']['crontask']['links']['search']="/front/crontask.php";

        $menu['config']['content']['mailing']['options']['config']['title']=$LANG['mailing'][118];
        $menu['config']['content']['mailing']['options']['config']['page']='/front/notificationmailsetting.form.php';

         $menu['config']['content']['mailing']['options']['notificationtemplate']['title']=$LANG['mailing'][113];
         $menu['config']['content']['mailing']['options']['notificationtemplate']['page']='/front/notificationtemplate.php';
         $menu['config']['content']['mailing']['options']['notificationtemplate']['links']['add']='/front/notificationtemplate.form.php';
         $menu['config']['content']['mailing']['options']['notificationtemplate']['links']['search']='/front/notificationtemplate.php';

         $menu['config']['content']['extauth']['title']=$LANG['login'][10];
         $menu['config']['content']['extauth']['page']='/front/setup.auth.php';

         $menu['config']['content']['extauth']['options']['ldap']['title']=$LANG['login'][2];
         $menu['config']['content']['extauth']['options']['ldap']['page']='/front/authldap.php';

         $menu['config']['content']['extauth']['options']['imap']['title']=$LANG['login'][3];
         $menu['config']['content']['extauth']['options']['imap']['page']='/front/authmail.php';

         $menu['config']['content']['extauth']['options']['others']['title']=$LANG['common'][67];
         $menu['config']['content']['extauth']['options']['others']['page']='/front/auth.others.php';

         $menu['config']['content']['extauth']['options']['settings']['title']=$LANG['common'][12];
         $menu['config']['content']['extauth']['options']['settings']['page']='/front/auth.settings.php';

         switch ($option) {
            case "ldap" : // LDAP
               $menu['config']['content']['extauth']['options']['ldap']['links']['search']='/front/authldap.php';
               $menu['config']['content']['extauth']['options']['ldap']['links']['add']='' .
                       '/front/authldap.form.php';
               break;

            case "imap" : // IMAP
               $menu['config']['content']['extauth']['links']['search']='/front/authmail.php';
               $menu['config']['content']['extauth']['links']['add']='' .
                       '/front/authmail.form.php';
               break;
         }

         $menu['config']['content']['mailcollector']['title']=$LANG['Menu'][39];
         $menu['config']['content']['mailcollector']['page']='/front/mailcollector.php';
         if (canUseImapPop()) {
            $menu['config']['content']['mailcollector']['links']['search']='/front/mailcollector.php';
            $menu['config']['content']['mailcollector']['links']['add']='/front/mailcollector.form.php';
            $menu['config']['content']['mailcollector']['options']['rejectedemails']['links']['search']='/front/rejectedemail.php';
         }
      }

      if ($CFG_GLPI["use_ocs_mode"] && Session::haveRight("config",UPDATE)) {
         $menu['config']['content']['ocsng']['title']=$LANG['setup'][134];
         $menu['config']['content']['ocsng']['page']='/front/ocsserver.php';
         $menu['config']['content']['ocsng']['links']['search']='/front/ocsserver.php';
         $menu['config']['content']['ocsng']['links']['add']='/front/ocsserver.form.php';
      }

      if (Session::haveRight("link",READ)) {
         $menu['config']['content']['link']['title']=$LANG['title'][33];
         $menu['config']['content']['link']['page']='/front/link.php';
         $menu['config']['content']['link']['hide']=true;
         $menu['config']['content']['link']['links']['search']='/front/link.php';
         if (Session::haveRight("link",UPDATE)) {
            $menu['config']['content']['link']['links']['add']="/front/link.form.php";
         }
      }

      if (Session::haveRight("config",UPDATE)) {
         $menu['config']['content']['plugins']['title']=$LANG['common'][29];
         $menu['config']['content']['plugins']['page']='/front/plugin.php';
      }

*/
      // Special items
      $menu['preference']['title'] = $LANG['Menu'][11];
      $menu['preference']['default'] = '/front/preference.php';
      
      
      return $menu;
   }
}
