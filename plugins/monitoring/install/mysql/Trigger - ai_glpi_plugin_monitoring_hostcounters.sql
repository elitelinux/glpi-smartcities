DELIMITER $$

USE `glpidb`$$

DROP TRIGGER /*!50032 IF EXISTS */ `ai_glpi_plugin_monitoring_hostcounters`$$

CREATE
    /*!50017 DEFINER = 'root'@'%' */
    TRIGGER `ai_glpi_plugin_monitoring_hostcounters` AFTER INSERT ON `glpi_plugin_monitoring_hostcounters` 
    FOR EACH ROW BEGIN
	DECLARE _dailyCountersExist TINYINT;
	DECLARE _yesterdayCountersExist TINYINT;
	DECLARE _day DATE;
	DECLARE _dayBefore DATE;
	DECLARE _changedToday TINYINT;
	DECLARE _previousCounter INT(11);
	
	SELECT DATE(new.date) INTO _day;
	SELECT DATE(DATE_SUB(new.date, INTERVAL 1 DAY)) INTO _dayBefore;
	-- INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('Day: ', _day, ', day before: ', _dayBefore));
	
	SELECT COUNT(cPagesTotal) INTO _dailyCountersExist FROM `glpi_plugin_monitoring_hostdailycounters` WHERE `hostname` = new.hostname AND `day` = _day LIMIT 1;
	-- INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('Day counters exist: ', _dailyCountersExist));
	SELECT COUNT(cPagesTotal) INTO _yesterdayCountersExist FROM `glpi_plugin_monitoring_hostdailycounters` WHERE `hostname` = new.hostname AND `day` = _dayBefore LIMIT 1;
	-- INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('Day before counters exist: ', _yesterdayCountersExist));
	
	-- 	Create daily counters row for concerned host/day ...
	IF _dailyCountersExist = 0 THEN
		-- INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('Creating new daily row ...'));
		
		INSERT INTO `glpi_plugin_monitoring_hostdailycounters` (`hostname`, `day`) VALUES (new.hostname, _day); 
		IF _yesterdayCountersExist = 1 THEN
			-- INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('Copying day before row ...'));
			UPDATE `glpi_plugin_monitoring_hostdailycounters` AS t
			JOIN `glpi_plugin_monitoring_hostdailycounters` AS tb
				ON tb.hostname=new.hostname AND tb.day=_dayBefore
			SET
				t.cPagesInitial = tb.cPagesInitial
				, t.cPagesTotal = tb.cPagesTotal
				, t.cPagesToday = 0
				, t.cPagesRemaining = tb.cPagesRemaining
				, t.cRetractedInitial = tb.cRetractedInitial
				, t.cRetractedTotal = tb.cRetractedTotal
				, t.cRetractedToday = 0
				, t.cPrinterChanged = tb.cPrinterChanged
				, t.cPaperChanged = tb.cPaperChanged
				, t.cBinEmptied = tb.cBinEmptied
				, t.cPaperLoad = tb.cPaperLoad
			WHERE
				t.hostname=new.hostname AND t.day=_day;
		ELSE
			-- INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('Setting new row ...'));
			UPDATE `glpi_plugin_monitoring_hostdailycounters` 
			SET
				cPagesInitial = 0
				, cPagesTotal = 0
				, cPagesToday = 0
				, cPagesRemaining = 2000
				, cRetractedInitial = 0
				, cRetractedTotal = 0
				, cRetractedToday = 0
				, cRetractedRemaining = 0
				, cPaperChanged = 0
				, cPrinterChanged = 0
				, cBinEmptied = 0
				, cPaperLoad = 2000
			WHERE
				`hostname`=new.hostname AND `day`=_day
			LIMIT 1;
		END IF;
	END IF;
	
	INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('Before update : ', 
		'Host : ', NEW.hostname, ', ', NEW.date, ' : ', NEW.counter, '=', NEW.value,
		' Printers: ', (SELECT cPrinterChanged FROM `glpi_plugin_monitoring_hostdailycounters` WHERE `hostname` = new.hostname AND `day` = _day),
		' Papers: ', (SELECT cPaperChanged FROM `glpi_plugin_monitoring_hostdailycounters` WHERE `hostname` = new.hostname AND `day` = _day),
		' Pages today: ', (SELECT cPagesToday FROM `glpi_plugin_monitoring_hostdailycounters` WHERE `hostname` = new.hostname AND `day` = _day),
		', retracted today: ', (SELECT cRetractedToday FROM `glpi_plugin_monitoring_hostdailycounters` WHERE `hostname` = new.hostname AND `day` = _day),
		', remaining: ', (SELECT cPagesRemaining FROM `glpi_plugin_monitoring_hostdailycounters` WHERE `hostname` = new.hostname AND `day` = _day),
		', bin: ', (SELECT cRetractedRemaining FROM `glpi_plugin_monitoring_hostdailycounters` WHERE `hostname` = new.hostname AND `day` = _day)
	));
		
	-- Update daily paper load counters row for concerned host/day ...
	IF NEW.counter = 'cPaperChanged' THEN
-- 		INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('New paper changed counter : ', new.value));
		IF _yesterdayCountersExist = 1 THEN
			INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('Updating paper changed with previous day ...'));
			
			UPDATE `glpi_plugin_monitoring_hostdailycounters` AS t
			JOIN `glpi_plugin_monitoring_hostdailycounters` AS tb
				ON t.hostname=new.hostname AND t.day=_dayBefore
			SET
				t.cPaperChanged = new.value
				, t.cPaperLoad = (new.value + 1) * 2000
				, t.cRetractedRemaining = IF (t.cBinEmptied > tb.cBinEmptied, 0, t.cRetractedRemaining)
				, t.cPagesRemaining = tb.cPagesRemaining - t.cPagesToday
			WHERE
				t.hostname=new.hostname AND t.day=_day;
		ELSE
			UPDATE `glpi_plugin_monitoring_hostdailycounters` 
			SET
				cPaperChanged = new.value
				, cPaperLoad = (new.value + 1) * 2000
				, cPagesRemaining = (new.value + 1) * 2000 - cPagesToday
			WHERE
				`hostname`=new.hostname AND `day`=_day
			LIMIT 1;
		END IF;
	END IF;
	
	-- Update daily bin emptied counters row for concerned host/day ...
	IF NEW.counter = 'cBinEmptied' THEN
-- 		INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('New bin emptied counter : ', new.value));
		IF _yesterdayCountersExist = 1 THEN
			INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('Updating bin emptied with previous day ...'));
			
			UPDATE `glpi_plugin_monitoring_hostdailycounters` AS t
			JOIN `glpi_plugin_monitoring_hostdailycounters` AS tb
				ON t.hostname=new.hostname AND t.day=_dayBefore
			SET
				t.cRetractedRemaining = IF (t.cBinEmptied > tb.cBinEmptied, 0, t.cRetractedRemaining)
			WHERE
				t.hostname=new.hostname AND t.day=_day;
		END IF;
		
		UPDATE `glpi_plugin_monitoring_hostdailycounters` 
		SET
			cBinEmptied = new.value
		WHERE
			`hostname`=new.hostname AND `day`=_day
		LIMIT 1;
	END IF;
	
	-- Update daily printer changed counters row for concerned host/day ...
	IF NEW.counter = 'cPrinterChanged' THEN
		IF _yesterdayCountersExist = 1 THEN
			INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('Updating cPrinterChanged with previous day ...'));
			
			UPDATE `glpi_plugin_monitoring_hostdailycounters` AS t
			JOIN `glpi_plugin_monitoring_hostdailycounters` AS tb
				ON t.hostname=new.hostname AND t.day=_dayBefore
			SET
				t.cPrinterChanged = new.value
				, t.cPagesToday = 0
				, t.cRetractedToday = 0
				, t.cPagesInitial = t.cPagesTotal
				, t.cRetractedInitial = t.cRetractedTotal
				, t.cPagesRemaining = tb.cPagesRemaining
				, t.cRetractedRemaining = IF (t.cBinEmptied > tb.cBinEmptied, 0, t.cRetractedRemaining)
			WHERE
				t.hostname=new.hostname AND t.day=_day;
		ELSE
			UPDATE `glpi_plugin_monitoring_hostdailycounters` 
			SET
				cPrinterChanged = new.value
				, cPagesInitial = cPagesTotal
				, cRetractedInitial = cRetractedTotal
			WHERE
				`hostname`=new.hostname AND `day`=_day
			LIMIT 1;
		END IF;
	END IF;
	
	-- Update daily printed pages counters row for concerned host/day ...
	IF NEW.counter = 'cPagesTotal' THEN
		IF _yesterdayCountersExist = 1 THEN
-- 			INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('Updating cPagesTotal with previous day ...'));
			
			UPDATE `glpi_plugin_monitoring_hostdailycounters` AS t
			JOIN `glpi_plugin_monitoring_hostdailycounters` AS tb
				ON tb.hostname=new.hostname AND tb.day=_dayBefore
			SET
				t.cPagesTotal = new.value
				, t.cPagesInitial = IF (t.cPrinterChanged > tb.cPrinterChanged, new.value, tb.cPagesInitial)
				, t.cPagesToday = IF (t.cPrinterChanged > tb.cPrinterChanged, 0, GREATEST(new.value - tb.cPagesTotal, 0))
-- 				, t.cPagesRemaining = t.cPaperLoad - (t.cPagesTotal - t.cPagesInitial)
				, t.cPagesRemaining = tb.cPagesRemaining - IF (t.cPrinterChanged > tb.cPrinterChanged, 0, GREATEST(new.value - tb.cPagesTotal, 0))
			WHERE
				t.hostname=new.hostname AND t.day=_day;
		ELSE
			-- INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('Updating cPagesTotal with current day ...'));
			
			UPDATE `glpi_plugin_monitoring_hostdailycounters` 
			SET
				cPagesTotal = new.value
				, cPagesInitial = new.value
				, cPagesToday = 0
-- 				, cPagesRemaining = cPaperLoad - (cPagesTotal - cPagesInitial)
				, cPagesRemaining = cPaperLoad - cPagesToday
			WHERE
				`hostname`=new.hostname AND `day`=_day
			LIMIT 1;
		END IF;
	END IF;
	
	-- Update daily retracted pages counters row for concerned host/day ...
	IF NEW.counter = 'cRetractedTotal' THEN
		IF _yesterdayCountersExist = 1 THEN
			-- INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('Updating cRetractedTotal with previous day ...'));
			
			UPDATE `glpi_plugin_monitoring_hostdailycounters` AS t
			JOIN `glpi_plugin_monitoring_hostdailycounters` AS tb
				ON tb.hostname=new.hostname AND tb.day=_dayBefore
			SET
				t.cRetractedTotal = new.value
				, t.cRetractedInitial = IF (t.cPrinterChanged > tb.cPrinterChanged, new.value, tb.cRetractedInitial)
				, t.cRetractedToday = IF (t.cPrinterChanged > tb.cPrinterChanged, 0, GREATEST(new.value - tb.cRetractedTotal, 0))
				, t.cRetractedRemaining = IF (t.cPrinterChanged > tb.cPrinterChanged, tb.cRetractedRemaining, new.value)
			WHERE
				t.hostname=new.hostname AND t.day=_day;
		ELSE
			-- INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('Updating cRetractedTotal with current day ...'));
			
			UPDATE `glpi_plugin_monitoring_hostdailycounters` 
			SET
				cRetractedTotal = new.value
				, cRetractedInitial = new.value
				, cRetractedToday = new.value
				, cRetractedRemaining = new.value
			WHERE
				`hostname`=new.hostname AND `day`=_day
			LIMIT 1;
		END IF;
	END IF;
	
	INSERT INTO `glpi_plugin_monitoring_import_logs` (`log`) VALUES (CONCAT('After update  : ', 
		'Host : ', NEW.hostname, ', ', NEW.date, ' : ', NEW.counter, '=', NEW.value,
		' Printers: ', (SELECT cPrinterChanged FROM `glpi_plugin_monitoring_hostdailycounters` WHERE `hostname` = new.hostname AND `day` = _day),
		' Papers: ', (SELECT cPaperChanged FROM `glpi_plugin_monitoring_hostdailycounters` WHERE `hostname` = new.hostname AND `day` = _day),
		' Pages today: ', (SELECT cPagesToday FROM `glpi_plugin_monitoring_hostdailycounters` WHERE `hostname` = new.hostname AND `day` = _day),
		', retracted today: ', (SELECT cRetractedToday FROM `glpi_plugin_monitoring_hostdailycounters` WHERE `hostname` = new.hostname AND `day` = _day),
		', remaining: ', (SELECT cPagesRemaining FROM `glpi_plugin_monitoring_hostdailycounters` WHERE `hostname` = new.hostname AND `day` = _day),
		', bin: ', (SELECT cRetractedRemaining FROM `glpi_plugin_monitoring_hostdailycounters` WHERE `hostname` = new.hostname AND `day` = _day)
	));
    END;
$$

DELIMITER ;