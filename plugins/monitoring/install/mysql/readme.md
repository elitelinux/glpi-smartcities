# Production database update procedure

This procedure describes how to update production database with : 
1/ information which are stored in former server database.
2/ information which are missing in current database

When this procedure will have been applied the database will contain : 
1/ a new table `glpi_plugin_monitoring_hostcounters` which stores every counter update made by the hosts through Shinken Glpi notifier module
2/ a new table `glpi_plugin_monitoring_hostdailycounters` which stores a daily sum-up for those counters

Those tables will then be updated as soon as interesting counters are updated by the hosts :
 - the table `glpi_plugin_monitoring_hostcounters` is updated by Shinken each time an interesting counter is sent in perfdata. Interesting counters are defined in shinken configuration file in the glpi_counters module configuration. Only few counters are concerned among all perfdata counters to avoir heavy updating ...
 - the table `glpi_plugin_monitoring_hostdailycounters` is updated by a MySql trigger each time as a counter is updated by Shinken.
 
It aims to make available daily counters for monitored hosts. Those counters will be available in the Glpi user interface to be filtered, searched, and exported by Glpi users.


## 1 - Create trigger `ai_glpi_plugin_monitoring_hostcounters' in database : 
 This trigger is used to update host daily counters table when data are inserted in the host counters table. Some computation is made with provided data ...

 The SQL file to use is in the plugin install directory (Trigger - ai_glpi_plugin_monitoring_hostcounters.sql)
 Note : take care to replace USE `glpidb` with the concerned database name !

## 2 - Create and update `glpi_plugin_monitoring_hostdailycounters` table in database :
 When the sytem is running, this table is updated by Shinken glpicounters module as soon as some interesting perfdata are detected. To retro update database, it is necessary to insert data in this table with SQL requests against serviceevents table to simulate what Shinken should have done ...
 
 The SQL file to use is in the plugin install directory (Script - createHostDailyCounters.sql) 
 Note : currently, this source code is not considered as enough generic to be included completely in Monitoring plugin ... this is why all database updates are made through SQL scripts and are not included in update.php.

 All the requests included in the script file must be executed to update data in `glpi_plugin_monitoring_hostcounters` and `glpi_plugin_monitoring_hostdailycounters`. 

 Once the requests have been executed, the host daily counters are available here : /plugins/monitoring/front/hostdailycounter.php