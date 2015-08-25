<?php




class PluginReservationTask extends CommonDBTM
{
    
    static function addEvents(NotificationTargetReservation $target)
    {
        $target->events['plugin_reservation_conflit']    = "Conflit pour la prolongation d'une reservation";
        $target->events['plugin_reservation_expiration'] = "Expiration d'une reservation d'un utilisateur";
    }
    
    static function cronInfo($name)
    {
        global $LANG;
        
        switch ($name) {
            case "SurveilleResa":
                return array(
                    'description' => "Surveille les reservations"
                );
            case "MailUserDelayedResa":
                return array(
                    'description' => "Envoie un mail aux utilisateurs dont la reservation est depassée"
                );
        }
    }
    
    /**
     * Execute 1 task manage by the plugin
     *
     * @param $task Object of CronTask class for log / stat
     *
     * @return interger
     *    >0 : done
     *    <0 : to be run again (not finished)
     *     0 : nothing to do
     */
    static function cronSurveilleResa($task)
    {
        $res = self::surveilleResa($task);
        $task->setVolume($res);
        return $res;
    }
    
    
    static function cronMailUserDelayedResa($task)
    {
        $res = self::mailUserDelayedResa($task);
        $task->setVolume($res);
        return $res;
    }
    
    
    
    static function mailUserDelayedResa($task)
    {
      global $DB, $CFG_GLPI;
      $res = 0;

      $config = new PluginReservationConfig();
      $week = $config->getConfigurationWeek();
      setlocale (LC_TIME, 'fr_FR.utf8','fra'); 
      $jour = strftime("%A");
      if(isset($week[$jour]))
      {

        $query = "SELECT * FROM `glpi_plugin_reservation_manageresa` WHERE `date_return` is NULL";
        
        if ($result = $DB->query($query)) {
          while ($row = $DB->fetch_assoc($result)) {
            $task->log("envoie d'un mail pour la reservation du materiel depassée numero " . $row['resaid']);

            $reservation = new Reservation();
            $reservation->getFromDB($row['resaid']);
            NotificationEvent::raiseEvent('plugin_reservation_expiration', $reservation);
            $res++;


          }
        }
        return $res;
      }
      else
      {
        $task->log("jour sauté");
      }
    }
    
    
    static function surveilleResa($task)
    {
        global $DB, $CFG_GLPI;
        $valreturn = 0;
        
        $temps = time();
        $temps -= ($temps % MINUTE_TIMESTAMP);
        $begin = date("Y-m-d H:i:s", $temps);
        $end   = date("Y-m-d H:i:s", $temps + 5 * MINUTE_TIMESTAMP);
        $left  = "";
        $where = "";
        
        $listResaTraitee = array();
        
        foreach ($CFG_GLPI["reservation_types"] as $itemtype) {
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }
            
            $itemtable = getTableForItemType($itemtype);
            
            $otherserial = "'' AS otherserial";
            if ($item->isField('otherserial')) {
                $otherserial = "`$itemtable`.`otherserial`";
            }
            
            if (isset($begin) && isset($end)) {
                $left  = "LEFT JOIN `glpi_reservations`
      ON (`glpi_reservationitems`.`id` = `glpi_reservations`.`reservationitems_id`
       AND '" . $begin . "' <= `glpi_reservations`.`end`
       AND '" . $end . "' >= `glpi_reservations`.`end`)";
                $where = " AND `glpi_reservations`.`id` IS NOT NULL ";
            }
            
            $query = "SELECT `glpi_reservationitems`.`id`,
`glpi_reservationitems`.`comment`,
`$itemtable`.`name` AS name,
`$itemtable`.`entities_id` AS entities_id,
$otherserial,
`glpi_reservations`.`id` AS resaid,
`glpi_reservations`.`comment`,
`glpi_reservations`.`begin`,
`glpi_reservations`.`end`,
`glpi_users`.`name` AS username,
`glpi_reservationitems`.`items_id` AS items_id
FROM `glpi_reservationitems`
$left
INNER JOIN `$itemtable`
ON (`glpi_reservationitems`.`itemtype` = '$itemtype'
  AND `glpi_reservationitems`.`items_id` = `$itemtable`.`id`)
LEFT JOIN `glpi_users` 
ON (`glpi_reservations`.`users_id` = `glpi_users`.`id`)
WHERE `glpi_reservationitems`.`is_active` = '1'
AND `glpi_reservationitems`.`is_deleted` = '0'
AND `$itemtable`.`is_deleted` = '0'
$where " . "ORDER BY username,
`$itemtable`.`entities_id`,
`$itemtable`.`name`";
            
            
            
            if ($result = $DB->query($query)) {
                while ($row = $DB->fetch_assoc($result)) {                    
                    $query  = "SELECT * FROM `glpi_plugin_reservation_manageresa` WHERE `resaid` = " . $row["resaid"];                    
                    //on insere la reservation seulement si elle n'est pas deja presente dans la table
                    if ($res = $DB->query($query)) {
                        if (!$DB->numrows($res)) {
                            $query = "INSERT INTO  `glpi_plugin_reservation_manageresa` (`resaid`, `matid`, `date_theorique`, `itemtype`) VALUES ('" . $row["resaid"] . "','" . $row["items_id"] . "','" .$row['end'] . "','" .$itemtype. "');";
                            $DB->query($query) or die("error on 'insert' into glpi_plugin_reservation_manageresa  lors du cron/ hash: " . $DB->error());
                          }
                    }
                }
            }            
        }
        
        //on va prolonger toutes les resa managées qui n'ont pas de date de retour
        $query = "SELECT * FROM `glpi_plugin_reservation_manageresa` WHERE date_return is NULL;";
        if ($result = $DB->query($query)) {
            while ($row = $DB->fetch_assoc($result)) {
              $newEnd = $temps + 5 * MINUTE_TIMESTAMP;
              $task->log("prolongation de la reservation numero " . $row['resaid']);

              // prolongation de la vrai resa
              self::verifDisponibiliteAndMailIGS($task, $row['itemtype'], $row['matid'], $row['resaid'], $begin, date("Y-m-d H:i:s", $newEnd));
              $query = "UPDATE `glpi_reservations` SET `end`='" . date("Y-m-d H:i:s", $newEnd) . "' WHERE `id`='" . $row["resaid"] . "';";
              $DB->query($query) or die("error on 'update' into glpi_reservations lors du cron : " . $DB->error());
              
              $valreturn++;                   
              
            }
        }      
        
        return $valreturn;
    }


    
    
    static function verifDisponibiliteAndMailIGS($task, $itemtype, $idMat, $currentResa, $datedebut, $datefin)
    {
        global $DB, $CFG_GLPI;
        
        $begin = $datedebut;
        $end   = $datefin;
        
        $left      = "";
        $where     = "";
        $itemtable = getTableForItemType($itemtype);
        
        if (isset($begin) && isset($end)) {
            $left  = "LEFT JOIN `glpi_reservations`
                ON (`glpi_reservationitems`.`id` = `glpi_reservations`.`reservationitems_id`
                  AND '" . $begin . "' < `glpi_reservations`.`end`
                  AND '" . $end . "' > `glpi_reservations`.`begin`)";
                        $where = " AND `glpi_reservations`.`id` IS NOT NULL 
            AND `glpi_reservations`.`id` != '" . $currentResa . "'
            AND `glpi_reservationitems`.`items_id` = '" . $idMat . "'";
        }
        
        $query = "SELECT `glpi_reservationitems`.`id`,
                  `glpi_reservationitems`.`comment`,
                  `$itemtable`.`name` AS name,
                  `$itemtable`.`entities_id` AS entities_id,
                  `glpi_reservations`.`id` AS resaid,
                  `glpi_reservations`.`comment`,
                  `glpi_reservations`.`begin`,
                  `glpi_reservations`.`end`,
                  `glpi_users`.`name` AS username,
                  `glpi_reservationitems`.`items_id` AS items_id
                  FROM `glpi_reservationitems`
                  $left
                  INNER JOIN `$itemtable`
                  ON (`glpi_reservationitems`.`itemtype` = '$itemtype'
                    AND `glpi_reservationitems`.`items_id` = `$itemtable`.`id`)
                  LEFT JOIN `glpi_users` 
                  ON (`glpi_reservations`.`users_id` = `glpi_users`.`id`)
                  WHERE `glpi_reservationitems`.`is_active` = '1'
                  AND `glpi_reservationitems`.`is_deleted` = '0'
                  AND `$itemtable`.`is_deleted` = '0'
                  $where";
        
        
        if ($result = $DB->query($query)) {
            while ($row = $DB->fetch_assoc($result)) {
                
                
                $task->log("CONFLIT avec la reservation du materiel " . $row['name'] . " par " . $row['username'] . " (du " . date("\L\e d-m-Y \à H:i:s", strtotime($row['begin'])) . " au " . date("\L\e d-m-Y \à H:i:s", strtotime($row['end'])));
                $task->log("on supprime la resa numero : " . $row['resaid']);
                
                $reservation = new Reservation();
                $reservation->getFromDB($row['resaid']);
                NotificationEvent::raiseEvent('plugin_reservation_conflit', $reservation);
                
                $query = "DELETE FROM `glpi_reservations` WHERE `id`='" . $row["resaid"] . "';";
                $DB->query($query) or die("error on 'delete' into glpi_reservations lors du cron : " . $DB->error());
                
            }
        }
    }
    
    
    
}





?>



