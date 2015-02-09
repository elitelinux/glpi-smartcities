-- 0 - create new tables for counters
DROP TABLE IF EXISTS `glpi_plugin_monitoring_hostdailycounters`;
CREATE TABLE `glpi_plugin_monitoring_hostdailycounters` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`hostname` varchar(255) NOT NULL DEFAULT '',
	`day` date NOT NULL DEFAULT '2013-01-01',
	`cPaperChanged` int(11) NOT NULL DEFAULT '0',
	`cPrinterChanged` int(11) NOT NULL DEFAULT '0',
	`cBinEmptied` int(11) NOT NULL DEFAULT '0',
	`cPagesInitial` int(11) NOT NULL DEFAULT '0',
	`cPagesTotal` int(11) NOT NULL DEFAULT '0',
	`cPagesToday` int(11) NOT NULL DEFAULT '0',
	`cPagesRemaining` int(11) NOT NULL DEFAULT '0',
	`cRetractedInitial` int(11) NOT NULL DEFAULT '0',
	`cRetractedTotal` int(11) NOT NULL DEFAULT '0',
	`cRetractedToday` int(11) NOT NULL DEFAULT '0',
	`cRetractedRemaining` int(11) NOT NULL DEFAULT '0',
	`cPaperLoad` int(11) NOT NULL DEFAULT '0',
	`cCardsInsertedOk` INT(11) NOT NULL DEFAULT '0',
	`cCardsInsertedKo` INT(11) NOT NULL DEFAULT '0',
	`cCardsRemoved` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY (`hostname`,`day`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_monitoring_hostcounters`;
CREATE TABLE `glpi_plugin_monitoring_hostcounters` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`hostname` varchar(255) DEFAULT NULL,
	`date` datetime DEFAULT NULL,
	`counter` varchar(255) DEFAULT NULL,
	`value` int(11) NOT NULL DEFAULT '0',
	`updated` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `hostname` (`hostname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- In the first database : 
-- - Execute 1 2 3 4a to build 'glpi_plugin_monitoring_hostcounters' table
-- - Copy glpi_plugin_monitoring_hostcounters table in the second database
--
-- In the second database :
-- - Execute 1 2 3 4a to build 'glpi_plugin_monitoring_hostcounters' table
-- - Copy glpi_plugin_monitoring_hostcounters table in the second database
--   Counters in glpi_plugin_monitoring_hostcounters from 19/11/2013 to 8/1/2014
--
-- In the last database :
-- - Execute 1 2 3 to build 'glpi_plugin_monitoring_hostcounters_tmp' table
--   Counters in glpi_plugin_monitoring_hostcounters_tmp from 26/12/2013 to now ... overlapping previous data !
-- - Execute 4b to clean 'glpi_plugin_monitoring_hostcounters_tmp' table from overlapping data

-- - Execute 5 to save the old counters table
-- - Copy glpi_plugin_monitoring_hostcounters table in the server database

-- 1 - create temporary table from serviceevents (execution time : < 30 sec / 141427 rows)
DROP TABLE IF EXISTS `glpi_plugin_monitoring_hostcounters_tmp`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_hostcounters_tmp` (
   `id` INT(11) NOT NULL AUTO_INCREMENT,
   `serviceId` INT(11),
   `hostname` VARCHAR(255) DEFAULT NULL,
   `date` DATETIME DEFAULT NULL,
   `cutPages` INT(11) NOT NULL DEFAULT '0',
   `retractedPages` INT(11) NOT NULL DEFAULT '0',
   `printerChanged` INT(11) NOT NULL DEFAULT '0',
   `paperChanged` INT(11) NOT NULL DEFAULT '0',
   `binEmptied` INT(11) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`)
)
SELECT
  `plugin_monitoring_services_id` AS serviceId
  , 'unknown' AS hostname
  , `date` AS DATE
  , IF(LOCATE('\'Cut Pages\'=', `perf_data`)>0, SUBSTRING_INDEX(SUBSTRING_INDEX(`perf_data`, '\'Cut Pages\'=', -1), 'c', 1) , 0) AS cutPages
  , IF(LOCATE('\'Retracted Pages\'=', `perf_data`)>0, SUBSTRING_INDEX(SUBSTRING_INDEX(`perf_data`, '\'Retracted Pages\'=', -1), 'c', 1) , 0) AS retractedPages
  , IF(LOCATE('\'Printer Replace\'=', `perf_data`)>0, SUBSTRING_INDEX(SUBSTRING_INDEX(`perf_data`, '\'Printer Replace\'=', -1), 'c', 1) , 0) AS printerChanged
  , IF(LOCATE('\'Paper Reams\'=', `perf_data`)>0, SUBSTRING_INDEX(SUBSTRING_INDEX(`perf_data`, '\'Paper Reams\'=', -1), 'c', 1) , 0) AS paperChanged
  , IF(LOCATE('\'Trash Empty\'=', `perf_data`)>0, SUBSTRING_INDEX(SUBSTRING_INDEX(`perf_data`, '\'Trash Empty\'=', -1), 'c', 1) , 0) AS binEmptied
FROM
  `glpi_plugin_monitoring_serviceevents`
WHERE 
  LOCATE('\'Cut Pages\'=', `perf_data`)>0;

-- 2- update hostname (execution time : < 7 sec / 141402 rows)
UPDATE `glpi_plugin_monitoring_hostcounters_tmp`
    INNER JOIN `glpi_plugin_monitoring_services` 
        ON (`glpi_plugin_monitoring_hostcounters_tmp`.`serviceId` = `glpi_plugin_monitoring_services`.`id`)
    INNER JOIN `glpi_plugin_monitoring_componentscatalogs_hosts` 
        ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
    INNER JOIN `glpi_computers` 
        ON (`glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_computers`.`id`)
SET
	hostname = `glpi_computers`.`name`;

-- 3 - clean table from rotten data (invalid counters and unknown hosts)
DELETE FROM `glpi_plugin_monitoring_hostcounters_tmp`
WHERE cutPages = '-1' OR cutPages = '0' OR hostname='unknown';

-- 4a - empty and then update tables (execution time : < 7 sec / 27915 rows)
INSERT INTO glpi_plugin_monitoring_hostcounters ( `hostname`, `date`, `counter`, `value` ) 
SELECT `hostname`, `date`, 'cutPages' AS counter, cutPages AS VALUE
FROM `glpi_plugin_monitoring_hostcounters_tmp`
UNION SELECT `hostname`, `date`, 'retractedPages' AS counter, retractedPages AS VALUE
FROM `glpi_plugin_monitoring_hostcounters_tmp`
UNION SELECT `hostname`, `date`, 'printerChanged' AS counter, printerChanged AS VALUE
FROM `glpi_plugin_monitoring_hostcounters_tmp`
UNION SELECT `hostname`, `date`, 'paperChanged' AS counter, paperChanged AS VALUE
FROM `glpi_plugin_monitoring_hostcounters_tmp`
UNION SELECT `hostname`, `date`, 'binEmptied' AS counter, binEmptied AS VALUE
FROM `glpi_plugin_monitoring_hostcounters_tmp`
ORDER BY DATE ASC;

-- 4b - delete overlapping data
DELETE FROM `glpi_plugin_monitoring_hostcounters_tmp` WHERE DATE < '2014-01-08 00:45:29';

-- 5 - backup old table
RENAME TABLE `glpi_plugin_monitoring_hostcounters` TO `glpi_plugin_monitoring_hostcounters_save`;

-- 6 - update table
-- remove old counters
DELETE FROM `glpi_plugin_monitoring_hostcounters_save` WHERE DATE < (SELECT MAX(DATE) FROM `glpi_plugin_monitoring_hostcounters`);
-- insert recent counters
-- Note : before this operation the Shinken module that updates the counters may be stopped !
LOCK TABLE `glpi_plugin_monitoring_hostcounters` WRITE, `glpi_plugin_monitoring_hostcounters_save` READ;
INSERT INTO `glpi_plugin_monitoring_hostcounters` ( `hostname`, `date`, `counter`, `value` ) 
SELECT `hostname`, `date`, `counter`, `value`
FROM `glpi_plugin_monitoring_hostcounters_save`
ORDER BY DATE ASC;
UNLOCK TABLES;
