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

class PluginMonitoringCommand extends CommonDBTM {

   static $rightname = 'plugin_monitoring_command';


/*
   Shinken 2.0 defines:
      # Nagios legacy macros
      $USER1$=$NAGIOSPLUGINSDIR$
      $NAGIOSPLUGINSDIR$=/usr/lib/nagios/plugins

      #-- Location of the plugins for Shinken
      $PLUGINSDIR$=/var/lib/shinken/libexec
 */

   // Prefix to use when commands are built for Shinken configuration
   public static $command_prefix = 'pm-';

   function initCommands() {
      global $DB;

      // Shinken 1.4
      // - restart/reload Shinken
      $input = array();
      $input['name'] = "Shinken (1.4) reload";
      // Default is not active ...
      $input['is_active'] = "0";
      $input['command_name'] = "restart_shinken";
      $input['command_line'] = $DB->escape("nohup sh -c '/usr/local/shinken/bin/stop_arbiter.sh && sleep 3 && /usr/local/shinken/bin/launch_arbiter.sh'  > /dev/null 2>&1 &");
      $this->add($input);

      // Shinken 2.0
      // - restart/reload Shinken
      // - same command_name as default Shinken's
      $input = array();
      $input['name'] = "Shinken (2.0) restart";
      $input['command_name'] = "restart-shinken";
      $input['command_line'] = $DB->escape("nohup sh -c '/etc/init.d/shinken restart'  > /dev/null 2>&1 &");
      $this->add($input);

      $input = array();
      $input['name'] = "Shinken (2.0) reload";
      $input['command_name'] = "reload-shinken";
      $input['command_line'] = $DB->escape("nohup sh -c '/etc/init.d/shinken reload'  > /dev/null 2>&1 &");
      $this->add($input);

      // Shinken 2.0
      // - default installed checks
      $input = array();
      $input['name'] = 'Check host alive (ICMP)';
      $input['command_name'] = 'check_host_alive';
      $input['command_line'] = "\$NAGIOSPLUGINSDIR$/check_icmp -H \$HOSTADDRESS$ -w 1000,100% -c 3000,100% -p 1";
      $this->add($input);

      $input = array();
      $input['name'] = 'Check host alive (ping)';
      $input['command_name'] = 'check_ping';
      $input['command_line'] = "\$NAGIOSPLUGINSDIR\$/check_ping -H \$HOSTADDRESS\$ -w 3000,100% -c 5000,100% -p 1";
      $this->add($input);

      $input = array();
      $input['name'] = 'Ask a nrpe agent';
      $input['command_name'] = 'check_nrpe';
      $input['command_line'] = "\$NAGIOSPLUGINSDIR\$/check_nrpe -H \$HOSTADDRESS\$ -t \$ARG1\$ -u \$ARG2\$ -c \$ARG3\$";
      $input['module_type'] = 'nrpe_poller';
      $arg = array();
      $arg['ARG1'] = 'NRPE timeout (seconds)';
      $arg['ARG2'] = 'NRPE SSL enable/disable (-n to disable)';
      $arg['ARG3'] = 'NRPE check command';
      $input['arguments'] = exportArrayToDB($arg);
      $this->add($input);

      $input = array();
      $input['name'] = 'Ask a nrpe agent with arguments';
      $input['command_name'] = 'check_nrpe_args';
      $input['command_line'] = "\$NAGIOSPLUGINSDIR\$/check_nrpe -H \$HOSTADDRESS\$ -t \$ARG1\$ -u \$ARG2\$ -c \$ARG3\$ -a  \$ARG4\$ \$ARG5\$ \$ARG6\$ \$ARG7\$ \$ARG8\$ \$ARG9\$";
      $input['module_type'] = 'nrpe_poller';
      $arg = array();
      $arg['ARG1'] = 'NRPE timeout (seconds)';
      $arg['ARG2'] = 'NRPE SSL enable/disable (-n to disable)';
      $arg['ARG3'] = 'NRPE check command';
      $arg['ARG4'] = 'NRPE check argument';
      $arg['ARG5'] = 'NRPE check argument';
      $arg['ARG6'] = 'NRPE check argument';
      $arg['ARG7'] = 'NRPE check argument';
      $arg['ARG8'] = 'NRPE check argument';
      $arg['ARG9'] = 'NRPE check argument';
      $input['arguments'] = exportArrayToDB($arg);
      $this->add($input);


      // Plugin Monitoring - Host action command
      $input = array();
      $input['name'] = "Host action";
      $input['command_name'] = "host_action";
      $input['command_line'] = $DB->escape("host_action");
      $this->add($input);


      // Nagios plugins
      $input = array();
      $input['name'] = "Dummy check";
      $input['command_name'] = "check_dummy";
      $input['command_line'] = $DB->escape("\$PLUGINSDIR\$/check_dummy \$ARG1\$ \"\$ARG2$\"");
      $arg = array();
      $arg['ARG1'] = 'INTEGER: dummy status code';
      $arg['ARG2'] = 'TEXT: dummy status output text';
      $input['arguments'] = exportArrayToDB($arg);
      $this->add($input);

      $input = array();
      $input['name'] = 'Simple tcp port check';
      $input['command_name'] = 'check_tcp';
      $input['command_line'] = "\$PLUGINSDIR\$/check_tcp  -H \$HOSTADDRESS\$ -p \$ARG1\$";
      $this->add($input);

      $input = array();
      $input['name'] = 'Simple web check';
      $input['command_name'] = 'check_http';
      $input['command_line'] = "\$PLUGINSDIR\$/check_http -H \$HOSTADDRESS\$";
      $this->add($input);

      $input = array();
      $input['name'] = 'Simple web check with SSL';
      $input['command_name'] = 'check_https';
      $input['command_line'] = "\$PLUGINSDIR\$/check_http -H \$HOSTADDRESS\$ -S";
      $this->add($input);

      $input = array();
      $input['name'] = 'Check a DNS entry';
      $input['command_name'] = 'check_dig';
      $input['command_line'] = "\$PLUGINSDIR\$/check_dig -H \$HOSTADDRESS\$ -l \$ARG1\$";
      $arg = array();
      $arg['ARG1'] = 'Machine name to lookup';
      $input['arguments'] = exportArrayToDB($arg);
      $this->add($input);

      $input = array();
      $input['name'] = 'Check a FTP service';
      $input['command_name'] = 'check_ftp';
      $input['command_line'] = "\$PLUGINSDIR\$/check_ftp -H \$HOSTADDRESS\$";
      $this->add($input);

      $input = array();
      $input['name'] = 'Look at good ssh launch';
      $input['command_name'] = 'check_ssh';
      $input['command_line'] = "\$PLUGINSDIR\$/check_ssh -H \$HOSTADDRESS\$";
      $this->add($input);

      $input = array();
      $input['name'] = 'Look for good SMTP connexion';
      $input['command_name'] = 'check_smtp';
      $input['command_line'] = "\$PLUGINSDIR\$/check_smtp -H \$HOSTADDRESS\$";
      $this->add($input);

      $input = array();
      $input['name'] = 'Look for good SMTPS connexion';
      $input['command_name'] = 'check_smtps';
      $input['command_line'] = "\$PLUGINSDIR\$/check_smtp -H \$HOSTADDRESS\$ -S";
      $this->add($input);

      $input = array();
      $input['name'] = 'Look at a SSL certificate';
      $input['command_name'] = 'check_https_certificate';
      $input['command_line'] = "\$PLUGINSDIR\$/check_http -H \$HOSTADDRESS\$ -C 30";
      $this->add($input);

      $input = array();
      $input['name'] = 'Look at an HP printer state';
      $input['command_name'] = 'check_hpjd';
      $input['command_line'] = "\$PLUGINSDIR\$/check_hpjd -H \$HOSTADDRESS\$ -C \$SNMPCOMMUNITYREAD\$";
      $this->add($input);

      $input = array();
      $input['name'] = 'Look at Oracle connexion';
      $input['command_name'] = 'check_oracle_listener';
      $input['command_line'] = "\$PLUGINSDIR\$/check_oracle --tns \$HOSTADDRESS\$";
      $this->add($input);

      $input = array();
      $input['name'] = 'Look at MSSQL connexion';
      $input['command_name'] = 'check_mssql_connexion';
      $input['command_line'] = "\$PLUGINSDIR\$/check_mssql_health --hostname \$HOSTADDRESS\$ --username \"\$MSSQLUSER\$\" --password \"\$MSSQLPASSWORD\$\" --mode connection-time";
      $this->add($input);

      $input = array();
      $input['name'] = 'Ldap query';
      $input['command_name'] = 'check_ldap';
      $input['command_line'] = "\$PLUGINSDIR\$/check_ldap -H \$HOSTADDRESS\$ -b \"\$LDAPBASE\$\" -D \$DOMAINUSER\$ -P \"\$DOMAINPASSWORD\$\"";
      $this->add($input);

      $input = array();
      $input['name'] = 'Ldaps query';
      $input['command_name'] = 'check_ldaps';
      $input['command_line'] = "\$PLUGINSDIR\$/check_ldaps -H \$HOSTADDRESS\$ -b \"\$LDAPBASE\$\" -D \$DOMAINUSER\$ -P \"\$DOMAINPASSWORD\$\"";
      $this->add($input);

      $input = array();
      $input['name'] = 'Distant mysql check';
      $input['command_name'] = 'check_mysql_connexion';
      $input['command_line'] = "\$PLUGINSDIR\$/check_mysql -H \$HOSTADDRESS\$ -u \$MYSQLUSER\$ -p \$MYSQLPASSWORD\$";
      $this->add($input);

      $input = array();
      $input['name'] = 'ESX hosts checks';
      $input['command_name'] = 'check_esx_host';
      $input['command_line'] = "\$PLUGINSDIR\$/check_esx3.pl -D \$VCENTER\$ -H \$HOSTADDRESS\$ -u \$VCENTERLOGIN\$ -p \$VCENTERPASSWORD\$ l \$ARG1\$";
      $this->add($input);

      $input = array();
      $input['name'] = 'ESX VM checks';
      $input['command_name'] = 'check_esx_vm';
      $input['command_line'] = "\$PLUGINSDIR\$/check_esx3.pl -D \$VCENTER\$ -N \$HOSTALIAS\$ -u \$VCENTERLOGIN\$ -p \$VCENTERLOGIN\$ -l \$ARG1\$";
      $this->add($input);

      $input = array();
      $input['name'] = 'Check Linux host alive';
      $input['command_name'] = 'check_linux_host_alive';
      $input['command_line'] = "\$PLUGINSDIR\$/check_tcp -H \$HOSTADDRESS\$ -p 22 -t 3";
      $this->add($input);

      $input = array();
      $input['name'] = 'Check Windows host alive';
      $input['command_name'] = 'check_windows_host_alive';
      $input['command_line'] = "\$PLUGINSDIR\$/check_tcp -H \$HOSTADDRESS\$ -p 139 -t 3";
      $this->add($input);

      $input = array();
      $input['name'] = 'Check disk';
      $input['command_name'] = 'check_disk';
      $input['command_line'] = "\$PLUGINSDIR\$/check_disk -w \$ARG1\$ -c \$ARG2\$ -p \$ARG3\$";
      $arg = array();
      $arg['ARG1'] = 'INTEGER: WARNING status if less than INTEGER units of disk are free\n
         PERCENT%: WARNING status if less than PERCENT of disk space is free';
      $arg['ARG2'] = 'INTEGER: CRITICAL status if less than INTEGER units of disk are free\n
         PERCENT%: CRITICAL status if less than PERCENT of disk space is free';
      $arg['ARG3'] = 'Path or partition';
      $input['arguments'] = exportArrayToDB($arg);
      $this->add($input);

      $input = array();
      $input['name'] = 'Check local disk';
      $input['command_name'] = 'check-host-alive';
      $input['command_line'] = "\$PLUGINSDIR\$/check.sh \$HOSTADDRESS\$ -c \$ARG1\$ SERVICE \$USER1\$";
      $this->add($input);

      $input = array();
      $input['name'] = 'Business rules';
      $input['command_name'] = 'bp_rule';
      $input['command_line'] = "";
      $this->add($input);

      $input = array();
      $input['name'] = 'Check local cpu';
      $input['command_name'] = 'check_cpu_usage';
      $input['command_line'] = "\$PLUGINSDIR\$/check_cpu_usage -w \$ARG1\$ -c \$ARG2\$";
      $arg = array();
      $arg['ARG1'] = 'Percentage of CPU for warning';
      $arg['ARG2'] = 'Percentage of CPU for critical';
      $input['arguments'] = exportArrayToDB($arg);
      $this->add($input);

      $input = array();
      $input['name'] = 'Check load';
      $input['command_name'] = 'check_load';
      $input['command_line'] = "\$PLUGINSDIR\$/check_load -r -w \$ARG1\$ -c \$ARG2\$";
      $arg = array();
      $arg['ARG1'] = 'WARNING status if load average exceeds WLOADn (WLOAD1,WLOAD5,WLOAD15)';
      $arg['ARG2'] = 'CRITICAL status if load average exceed CLOADn (CLOAD1,CLOAD5,CLOAD15)';
      $input['arguments'] = exportArrayToDB($arg);
      $this->add($input);

      $input = array();
      $input['name'] = 'Check snmp';
      $input['command_name'] = 'check_snmp';
      $input['command_line'] = "\$PLUGINSDIR\$/check_snmp -H \$HOSTADDRESS\$ -P \$ARG1\$ -C \$ARG2\$ -o \$ARG3\$,\$ARG4\$,\$ARG5\$,\$ARG6\$,\$ARG7\$,\$ARG8\$,\$ARG9\$,\$ARG10\$";
      $arg = array();
      $arg['ARG1'] = 'SNMP protocol version (1|2c|3) [SNMP:version]';
      $arg['ARG2'] = 'Community string for SNMP communication [SNMP:authentication]';
      $arg['ARG3'] = 'oid [OID:ifinoctets]';
      $arg['ARG4'] = 'oid [OID:ifoutoctets]';
      $arg['ARG5'] = 'oid [OID:ifinerrors]';
      $arg['ARG6'] = 'oid [OID:ifouterrors]';
      $arg['ARG7'] = 'oid';
      $arg['ARG8'] = 'oid';
      $arg['ARG9'] = 'oid';
      $arg['ARG10'] = 'oid';
      $input['arguments'] = exportArrayToDB($arg);
      $this->add($input);

      $input = array();
      $input['name'] = 'Check users connected';
      $input['command_name'] = 'check_users';
      $input['command_line'] = "\$PLUGINSDIR\$/check_users -w \$ARG1\$ -c \$ARG2\$";
      $arg = array();
      $arg['ARG1'] = 'Set WARNING status if more than INTEGER users are logged in';
      $arg['ARG2'] = 'Set CRITICAL status if more than INTEGER users are logged in';
      $input['arguments'] = exportArrayToDB($arg);
      $this->add($input);
   }


   /**
   * Get name of this type
   *
   *@return text name of this type by language of the user connected
   *
   **/
   static function getTypeName($nb=0) {
      return __('Commands', 'monitoring');
   }



   function getSearchOptions() {

      $tab = array();

      $tab['common'] = __('Commands', 'monitoring');

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
      $tab[$i]['field']     = 'arguments';
      $tab[$i]['name']      = __('Arguments', 'monitoring');

      // Issue #112, what should be done with these fields ?
      $i++;
      $tab[$i]['table']     = $this->getTable();
      $tab[$i]['field']     = 'poller_tag';
      $tab[$i]['name']      = __('Shinken poller tag', 'monitoring');

      $i++;
      $tab[$i]['table']     = $this->getTable();
      $tab[$i]['field']     = 'module_type';
      $tab[$i]['name']      = __('Shinken module type', 'monitoring');

      return $tab;
   }



   function defineTabs($options=array()){
      $ong = array();
      $this->addDefaultFormTab($ong);
      return $ong;
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
   function showForm($items_id, $options=array(), $copy=array()) {
      global $DB,$CFG_GLPI;


      if (count($copy) > 0) {
         foreach ($copy as $key=>$value) {
            $this->fields[$key] = stripslashes($value);
         }
      }

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
      echo "<td>".__('Poller tag', 'monitoring')."&nbsp;:</td>";
      echo "<td>";
      echo "<input type='text' name='poller_tag' value='".$this->fields["poller_tag"]."' size='30'/>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Command line', 'monitoring')."&nbsp;:</td>";
      echo "<td colspan='3'>";
      echo '<input type="text" name="command_line" value="'.htmlspecialchars($this->fields["command_line"]).'" size="97"/>';
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Arguments description', 'monitoring')."&nbsp;:</td>";
      echo "<td colspan='3'>";
         $arguments = array();
         preg_match_all("/\\$(ARG\d+)\\$/", $this->fields['command_line'], $arguments);
         $arrayargument = importArrayFromDB($this->fields["arguments"]);
         echo "<table>";
         foreach ($arguments[0] as $adata) {
            $adata = str_replace('$', '', $adata);
            echo "<tr>";
            echo "<td>";
            echo " ".$adata. " : ";
            echo "</td>";
            echo "<td>";
            if (!isset($arrayargument[$adata])) {
               $arrayargument[$adata] = '';
            }
            echo "<textarea cols='90' rows='2' name='argument_".$adata."' >".$arrayargument[$adata]."</textarea>";
            echo "</td>";
            echo "</tr>";
         }
         echo "</table>";

      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      // Add form for copy item
      if ($items_id!='' && Session::haveRight("config", UPDATE)) {
         $this->fields['id'] = 0;
         $this->showFormHeader($options);

         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='4' class='center'>";
         foreach ($this->fields as $key=>$value) {
            if ($key != 'id') {
               echo "<input type='hidden' name='".$key."' value='".$value."'/>";
            }
         }
         echo "<input type='submit' name='copy' value=\"".__('copy', 'monitoring')."\" class='submit'>";
         echo "</td>";
         echo "</tr>";

         echo "</table>";
         Html::closeForm();
      }

      return true;
   }


   function convertPostdata($data) {

      // Convert arguments descriptions
      $a_arguments = array();
      foreach ($data as $name=>$value) {
         if (strstr($name, "argument_")) {
            $name = str_replace("argument_", "", $name);
            $a_arguments[$name] = $value;
         }
      }
      $data['arguments'] = exportArrayToDB($a_arguments);


      $where = "`command_name`='".$data['command_name']."'";
      if (isset($data['id'])) {
         $where .= " AND `id` != '".$data['id']."'";
      }
      $num_com = countElementsInTable("glpi_plugin_monitoring_commands", $where);
      if ($num_com > 0) {
         $data['command_name'] = $data['command_name'].mt_rand();
      }

      return $data;
   }

}

?>