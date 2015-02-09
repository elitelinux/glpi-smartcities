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

class PluginMonitoringNotificationcommand extends CommonDBTM {


   static $rightname = 'plugin_monitoring_command';


/*
   Shinken 2.0 defines:
      # Nagios legacy macros
      $USER1$=$NAGIOSPLUGINSDIR$
      $NAGIOSPLUGINSDIR$=/usr/lib/nagios/plugins

      #-- Location of the plugins for Shinken
      $PLUGINSDIR$=/var/lib/shinken/libexec
 */

   function initCommands() {
      global $DB;

      // Shinken 2.0 default commands
      // Host notifications
      $input = array();
      $input['name'] = 'Host : mail notification';
      $input['command_name'] = 'notify-host-by-email';
      $input['command_line'] = $DB->escape('/usr/bin/printf "%b" "Shinken Notification\n\nType:$NOTIFICATIONTYPE$\nHost: $HOSTNAME$\nState: $HOSTSTATE$\nAddress: $HOSTADDRESS$\nInfo: $HOSTOUTPUT$\nDate/Time: $DATE$ $TIME$\n" | /usr/bin/mail -s "Host $HOSTSTATE$ alert for $HOSTNAME$" $CONTACTEMAIL$');
      $this->add($input);

      $input = array();
      $input['name'] = 'Host : mail detailed notification';
      $input['command_name'] = 'detailled-host-by-email';
      $input['command_line'] = $DB->escape('/usr/bin/printf "%b" "Shinken Notification\n\nType:$NOTIFICATIONTYPE$\nHost: $HOSTNAME$\nState: $HOSTSTATE$\nAddress: $HOSTADDRESS$\nDate/Time: $DATE$/$TIME$\n Host Output : $HOSTOUTPUT$\n\nHost description: $_HOSTDESC$\nHost Impact: $_HOSTIMPACT$" | /usr/bin/mail -s "Host $HOSTSTATE$ alert for $HOSTNAME$" $CONTACTEMAIL$');
      $this->add($input);

      $input = array();
      $input['name'] = 'Host : XMPP notification';
      $input['command_name'] = 'notify-host-by-xmpp';
      $input['command_line'] = $DB->escape('$PLUGINSDIR$/notify_by_xmpp.py -a $PLUGINSDIR$/notify_by_xmpp.ini "Host $HOSTNAME$ is $HOSTSTATE$ - Info : $HOSTOUTPUT$" $CONTACTEMAIL$');
      $this->add($input);

      // Service notifications
      $input = array();
      $input['name'] = 'Service : mail notification';
      $input['command_name'] = 'notify-service-by-email';
      $input['command_line'] = $DB->escape('/usr/bin/printf "%b" "Shinken Notification\n\nNotification Type: $NOTIFICATIONTYPE$\n\nService: $SERVICEDESC$\nHost: $HOSTNAME$\nAddress: $HOSTADDRESS$\nState: $SERVICESTATE$\n\nDate/Time: $DATE$ $TIME$\nAdditional Info : $SERVICEOUTPUT$\n" | /usr/bin/mail -s "** $NOTIFICATIONTYPE$ alert - $HOSTNAME$/$SERVICEDESC$ is $SERVICESTATE$ **" $CONTACTEMAIL$');
      $this->add($input);

      $input = array();
      $input['name'] = 'Service : mail detailed notification';
      $input['command_name'] = 'detailled-service-by-email';
      $input['command_line'] = $DB->escape('/usr/bin/printf "%b" "Shinken Notification\n\nNotification Type: $NOTIFICATIONTYPE$\n\nService: $SERVICEDESC$\nHost: $HOSTALIAS$\nAddress: $HOSTADDRESS$\nState: $SERVICESTATE$\n\nDate/Time: $DATE$ at $TIME$\nService Output : $SERVICEOUTPUT$\n\nService Description: $_SERVICEDETAILLEDESC$\nService Impact: $_SERVICEIMPACT$\nFix actions: $_SERVICEFIXACTIONS$" | /usr/bin/mail -s "$SERVICESTATE$ on Host : $HOSTALIAS$/Service : $SERVICEDESC$" $CONTACTEMAIL$');
      $this->add($input);

      $input = array();
      $input['name'] = 'Service : XMPP notification';
      $input['command_name'] = 'notify-service-by-xmpp';
      $input['command_line'] = $DB->escape('$PLUGINSDIR$/notify_by_xmpp.py -a $PLUGINSDIR$/notify_by_xmpp.ini "$NOTIFICATIONTYPE$ $HOSTNAME$ $SERVICEDESC$ $SERVICESTATE$ $SERVICEOUTPUT$ $LONGDATETIME$" $CONTACTEMAIL$');
      $this->add($input);


/*
   TODO : migration script should remove (or rename ...) those commands from existing table
      $input = array();
      $input['name'] = 'Host : notify by mail';
      $input['command_name'] = 'notify-host-by-email';
      $input['command_line'] = "\$PLUGINSDIR\$/sendmailhost.pl \"\$NOTIFICATIONTYPE\$\" \"\$HOSTNAME\$\" \"\$HOSTSTATE\$\" \"\$HOSTADDRESS\$\" \"\$HOSTOUTPUT\$\" \"\$SHORTDATETIME\$\" \"\$CONTACTEMAIL\$\"";
      $this->add($input);

      $input = array();
      $input['name'] = 'Service : notify by mail (perl)';
      $input['command_name'] = 'notify-service-by-email-perl';
      $input['command_line'] = "\$PLUGINSDIR\$/sendmailservices.pl \"\$NOTIFICATIONTYPE\$\" \"\$SERVICEDESC\$\" \"\$HOSTALIAS\$\" \"\$HOSTADDRESS\$\" \"\$SERVICESTATE\$\" \"\$SHORTDATETIME\$\" \"\$SERVICEOUTPUT\$\" \"\$CONTACTEMAIL\$\" \"\$SERVICENOTESURL\$\"";
      $this->add($input);

      $input = array();
      $input['name'] = 'Service : notify by mail (python)';
      $input['command_name'] = 'notify-service-by-email-py';
      $input['command_line'] = "\$PLUGINSDIR\$/sendmailservice.py -s \"\$SERVICEDESC\$\" -n \"\$SERVICESTATE\$\" -H \"\$HOSTALIAS\$\" -a \"\$HOSTADDRESS\$\" -i \"\$SHORTDATETIME\$\" -o \"\$SERVICEOUTPUT\$\" -t \"\$CONTACTEMAIL\$\" -r \"\$SERVICESTATE\$\"";
      $this->add($input);
*/

   }



   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Notification commands', 'monitoring');
   }



   function getSearchOptions() {
      $tab = array();

      $tab['common'] = __('Notification commands', 'monitoring');

      $i=1;
		$tab[$i]['table'] = $this->getTable();
		$tab[$i]['field'] = 'name';
		$tab[$i]['linkfield'] = 'name';
		$tab[$i]['name'] = __('Name');
		$tab[$i]['datatype'] = 'itemlink';

      $i++;
      $tab[$i]['table']     = $this->getTable();
      $tab[$i]['field']     = 'is_active';
      $tab[$i]['linkfield'] = 'is_active';
      $tab[$i]['name']      = __('Active', 'monitoring');
      $tab[$i]['datatype']  = 'bool';

      $i++;
      $tab[$i]['table']     = $this->getTable();
      $tab[$i]['field']     = 'command_name';
      $tab[$i]['name']      = __('Command name', 'monitoring');

      $i++;
      $tab[$i]['table']     = $this->getTable();
      $tab[$i]['field']     = 'command_line';
      $tab[$i]['name']      = __('Command line', 'monitoring');

      $i++;
      $tab[$i]['table']     = $this->getTable();
      $tab[$i]['field']     = 'reactionner_tag';
      $tab[$i]['name']      = __('Shinken reactionner tag', 'monitoring');

      $i++;
      $tab[$i]['table']     = $this->getTable();
      $tab[$i]['field']     = 'module_type';
      $tab[$i]['name']      = __('Shinken module type', 'monitoring');

      return $tab;
   }



   /**
   * Display form for agent configuration
   *
   * @param $items_id integer ID
   * @param $options array
   *
   *@return bool true if form is ok
   *
   **/
   function showForm($items_id, $options=array()) {
      global $DB,$CFG_GLPI;

      $this->initForm($items_id, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')." :</td>";
      echo "<td>";
      echo "<input type='text' name='name' value='".$this->fields["name"]."' size='30'/>";
      echo "</td>";
      echo "<td>".__('Command name', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      echo "<input type='text' name='command_name' value='".$this->fields["command_name"]."' size='30'/>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Active ?', 'monitoring')."</td>";
      echo "<td>";
      if (self::canCreate()) {
         Dropdown::showYesNo('is_active', $this->fields['is_active']);
      } else {
         echo Dropdown::getYesNo($this->fields['is_active']);
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Module type', 'monitoring')." :</td>";
      echo "<td>";
      echo "<input type='text' name='module_type' value='".$this->fields["module_type"]."' size='30'/>";
      echo "</td>";
      echo "<td>".__('Reactionner tag', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      echo "<input type='text' name='reactionner_tag' value='".$this->fields["reactionner_tag"]."' size='30'/>";
      echo "</td>";
      echo "</tr>";


      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Command line', 'monitoring')."&nbsp;:</td>";
      echo "<td colspan='3'>";
      echo "<input type='text' name='command_line' value='".$this->fields["command_line"]."' size='130'/>";
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }
}

?>