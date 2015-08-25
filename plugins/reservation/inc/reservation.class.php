<?php



if (!defined('GLPI_ROOT')) {
  die("Sorry. You can't access directly to this file");
}



function getGLPIUrl()
   {return str_replace("plugins/reservation/front/reservation.php", "", $_SERVER['SCRIPT_NAME']);}    

class PluginReservationReservation extends CommonDBTM {

  static function getTypeName($nb=0) {
	return _n('Réservation', 'Réservation', $nb, 'Réservation');
  }

  function getAbsolutePath()
   {return str_replace("plugins/reservation/inc/reservation.class.php", "", $_SERVER['SCRIPT_FILENAME']);}
   
   
/*
   function isNewItem() {
    return false;
  }
*/
  
  static function getMenuName() {
     return PluginReservationReservation::getTypeName(2);
   } 

  static function canView() {
      global $CFG_GLPI;
      return true;
      return Session::haveRightsOr(self::$rightname, array(READ, self::RESERVEANITEM));
   }

  /**
   * Définition des onglets
   **/
  function defineTabs($options=array()) {
    $ong = array();
    $this->addStandardTab(__CLASS__, $ong, $options);
    return $ong;
  }

  /**
   * Définition du nom de l'onglet
   **/
  function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
    $ong = array();
    $ong[1] = 'Réservations en cours';
    $ong[2] = 'Matériel disponible';
    return $ong;
  }


  /**
   * Définition du contenu de l'onglet
   **/
  static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {



    $monplugin = new self();
    switch ($tabnum) {
      case 1 : // mon premier onglet
	$monplugin->showCurrentResa();
	break;

      case 2 : // mon second onglet
	$monplugin->showDispoAndFormResa();
	
	break;
    }
    return TRUE;
  }


  /**
   * Affiche le cadre avec la date de debut / date de fin
   **/
  function showFormDate() {
    GLOBAL $datesresa;


    /*if(isset($_GET['resareturn'])) {
      $_POST['reserve'] = $datesresa;
    } 
    else */
    if(!isset($datesresa)) {
  
      $jour = date("d",time());
      $mois = date("m",time());
      $annee = date("Y",time());
      $begin_time                 = time();

      $datesresa["begin"]  = date("Y-m-d H:i:s",$begin_time);

      if($begin_time > mktime(19,0,0,$mois,$jour,$annee))
	$datesresa["end"] = date("Y-m-d H:i:s",$begin_time + 3600);
      else
	$datesresa["end"] = date("Y-m-d H:i:s",mktime(19,0,0,$mois,$jour,$annee)); 
    }
    if(isset($_POST['nextday'])) {
      $tmpbegin = $datesresa["begin"];
      $tmpend = $datesresa["end"];

      $datesresa["begin"] = date("Y-m-d H:i:s", strtotime($datesresa["begin"]) + DAY_TIMESTAMP);
      $datesresa["end"] = date("Y-m-d H:i:s",strtotime($datesresa["end"]) + DAY_TIMESTAMP);
    }
    if(isset($_POST['previousday'])) {
      $tmpbegin = $datesresa["begin"];
      $tmpend = $datesresa["end"];

      $datesresa["begin"] = date("Y-m-d H:i:s", strtotime($datesresa["begin"]) - DAY_TIMESTAMP);
      $datesresa["end"] = date("Y-m-d H:i:s",strtotime($datesresa["end"]) - DAY_TIMESTAMP);
    }

    echo "<div id='viewresasearch'  class='center'>";
    echo "<form method='post' name='form' action='".Toolbox::getItemTypeSearchURL(__CLASS__)."'>";
    echo "<table class='tab_cadre'><tr class='tab_bg_2'>";
    echo "<th colspan='5'>Choisissez une date</th></tr>";

    

    echo "<tr class='tab_bg_2'>";

    echo "<td rowspan='3'>";
    echo "<input type='submit' class='submit' name='previousday' value=\"Jour precedent\">";
    echo "</td>";

    echo "<td>".__('Start date')."</td><td>";
    Html::showDateTimeField("reserve[begin]", array('value' =>  $datesresa["begin"], 
	  'maybeempty' => false));
    echo "</td><td rowspan='3'>";
    echo "<input type='submit' class='submit' name='submit' value=\""._sx('button', 'Search')."\">";
    echo "</td>";
    echo "<td rowspan='3'>";
    echo "<input type='submit' class='submit' name='nextday' value=\"Jour suivant\">";
    echo "</td></tr>";

    echo "<tr class='tab_bg_2'><td>".__('End date')."</td><td>";
    Html::showDateTimeField("reserve[end]", array('value' =>  $datesresa["end"], 
	  'maybeempty' => false));
    echo "</td></tr>";

    echo "</td></tr>";

    echo "</table>";
    Html::closeForm();
    echo "</div>";

  }



  /**
   * Fonction permettant d'afficher les materiels disponibles et de faire une nouvelle reservation
   * C'est juste une interface differente de celle de GLPI. Pour les nouvelles reservations, on utilise les fonctions du coeur de GLPI
   **/
  function showDispoAndFormResa(){
    global $DB, $CFG_GLPI, $datesresa;
    $showentity = Session::isMultiEntitiesMode();


    $begin = $datesresa["begin"];
    $end   = $datesresa["end"];
    $left = "";
    $where = "";

    echo "<div class='center'>";
    echo "<form name='form' method='GET' action='../../../front/reservation.form.php'>";
    echo "<table class='tab_cadre' style=\"border-spacing:20px;\">";
    echo "<tr>";

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
	$left = "LEFT JOIN `glpi_reservations`
	  ON (`glpi_reservationitems`.`id` = `glpi_reservations`.`reservationitems_id`
	      AND '". $begin."' < `glpi_reservations`.`end`
	      AND '". $end."' > `glpi_reservations`.`begin`)";
	$where = " AND `glpi_reservations`.`id` IS NULL ";
      }

      $query = "SELECT `glpi_reservationitems`.`id`,
	`glpi_reservationitems`.`comment`,
	`$itemtable`.`id` AS materielid,
	`$itemtable`.`name` AS name,
	`$itemtable`.`entities_id` AS entities_id,
	$otherserial,
	`glpi_locations`.`completename` AS location,
	`glpi_reservationitems`.`items_id` AS items_id
	  FROM `glpi_reservationitems`
	  $left
	  INNER JOIN `$itemtable`
	  ON (`glpi_reservationitems`.`itemtype` = '$itemtype'
	      AND `glpi_reservationitems`.`items_id` = `$itemtable`.`id`)
	  LEFT JOIN `glpi_locations`
	  ON (`$itemtable`.`locations_id` = `glpi_locations`.`id`)
	  WHERE `glpi_reservationitems`.`is_active` = '1'
	  AND `glpi_reservationitems`.`is_deleted` = '0'
	  AND `$itemtable`.`is_deleted` = '0'
	  $where ".
	  getEntitiesRestrictRequest(" AND", $itemtable, '',
	      $_SESSION['glpiactiveentities'],
	      $item->maybeRecursive())."
	  ORDER BY `$itemtable`.`entities_id`,
	`$itemtable`.`name`";


      if ($result = $DB->query($query)) {

	if($DB->numrows($result)) {
	  echo "<td>";
	  echo "<table class='tab_cadre'>";
	  echo "<tr><th colspan='".($showentity?"6":"5")."'>".$item->getTypeName()."</th></tr>\n"; 
	}
	while ($row = $DB->fetch_assoc($result)) {
	  echo "<tr class='tab_bg_2'><td>";
	  echo "<input type='checkbox' name='item[".$row["id"]."]' value='".$row["id"]."'>".
	    "</td>";
	  $typename = $item->getTypeName();
	  if ($itemtype == 'Peripheral') {
	    $item->getFromDB($row['items_id']);
	    if (isset($item->fields["peripheraltypes_id"]) && ($item->fields["peripheraltypes_id"] != 0)) {
	      $typename = Dropdown::getDropdownName("glpi_peripheraltypes",
		  $item->fields["peripheraltypes_id"]);
	    }
	  }
	  echo "<td white-space: nowrap ><a href='".getGLPIUrl()."front/".Toolbox::strtolower($itemtype).".form.php?id=".$row['materielid']."&forcetab=Reservation$1"."'>".sprintf(__('%1$s'), $row["name"])."</a></td>";
	  echo "<td>".nl2br($row["comment"])."</td>";
	  if ($showentity) {
	    echo "<td>".Dropdown::getDropdownName("glpi_entities", $row["entities_id"]).
	      "</td>";
	  }
	  echo "<td><a title=\"Voir le planning\" href='../../../front/reservation.php?reservationitems_id=".$row['id']."'>".
	    "<img title=\"\" alt=\"\" src=\"".getGLPIUrl()."pics/reservation-3.png\"></img></a></td>";
	  echo "</tr>\n";
	  
	}
      }
      if($DB->numrows($result)) {
	echo "</td>";
	echo "</table>\n"; 
      }
    }     

    echo "</tr>";
    echo "<tr class='tab_bg_1 center'><td colspan='".($showentity?"5":"4")."'>";
    echo "<input type='submit' value=\"Réserver\" class='submit'></td></tr>\n";
    

    echo "</table>\n";

    echo "<input type='hidden' name='id' value=''>";
    Html::closeForm(); 
    echo "</div>\n";
  }

  function mailUser($resaid)
  {
    global $DB, $CFG_GLPI;
    $reservation = new Reservation();
    $reservation->getFromDB($resaid);
    NotificationEvent::raiseEvent('plugin_reservation_expiration', $reservation);
    $config = new PluginReservationConfig();

    $query = "UPDATE `glpi_plugin_reservation_manageresa` SET `dernierMail`= '".date("Y-m-d H:i:s",time())."' WHERE `resaid` = ".$resaid;
    $DB->query($query) or die("error on 'update' dans mailUser: ". $DB->error());
   


  }

  /**
   * Fonction permettant de marquer une reservation comme rendue 
   * Si elle etait dans la table glpi_plugin_reservation_manageresa (c'etait donc une reservation prolongée), on insert la date de retour à l'heure actuelle ET on met à jour la date de fin de la vraie reservation.
   * Sinon, on insert une nouvelle entree dans la table pour avoir un historique du retour de la reservation ET on met à jour la date de fin de la vraie reservation
   **/
  function resaReturn($resaid)
  {
    global $DB, $CFG_GLPI;
    // on cherche dans la table de gestion des resa du plugin
    $query = "SELECT * FROM `glpi_plugin_reservation_manageresa` WHERE `resaid` = ".$resaid;
    $trouve = 0;
    $matId;
    if ($result = $DB->query($query)) {
	   # $matId = 
      if($DB->numrows($result))
	$trouve = 1;
    }

    $ok = 0;
    if($trouve) {
      // maj de la date de retour dans la table manageresa du plugin
      $query = "UPDATE `glpi_plugin_reservation_manageresa` SET `date_return` = '".date("Y-m-d H:i:s",time())."' WHERE `resaid` = '".$resaid."';";
      $DB->query($query) or die("error on 'update' into glpi_plugin_reservation_manageresa / hash: ". $DB->error());
      $ok = 1;
    }
    else {
      $temps = time();
      // insertion de la reservation dans la table manageresa
      $query = "INSERT INTO  `glpi_plugin_reservation_manageresa` (`resaid`, `matid`, `date_return`, `date_theorique`, `itemtype`) VALUES ('".$resaid."', '0',  '". date("Y-m-d H:i:s",$temps)."', '". date("Y-m-d H:i:s",$temps)."','null');";
      $DB->query($query) or die("error on 'insert' into glpi_plugin_reservation_manageresa / hash: ". $DB->error());
      $ok = 1;
    }


    //update de la vrai reservation
    if($ok) {
      $query = "UPDATE `glpi_reservations` SET `end`='". date("Y-m-d H:i:s",time())."' WHERE `id`='".$resaid."';";
      $DB->query($query) or die("error on 'update' into glpi_reservations / hash: ". $DB->error()); 
    }
  }


  /**
   * Fonction permettant d'afficher les reservations actuelles
   * 
   **/
  function showCurrentResa() {
    global $DB, $CFG_GLPI, $datesresa;
    $showentity = Session::isMultiEntitiesMode();
	    $config = new PluginReservationConfig();
      $methode = $config->getConfigurationMethode();
	
    $begin = $datesresa["begin"];
    $end   = $datesresa["end"];
    $left = "";
    $where = "";

    //tableau contenant un tableau des reservations par utilisateur
    // exemple : (salleman => ( 0=> (resaid => 1, debut => '12/12/2054', fin => '12/12/5464', comment => 'tralala', name => 'hobbit16'
    $ResaByUser = array();


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
	$left = "LEFT JOIN `glpi_reservations`
	  ON (`glpi_reservationitems`.`id` = `glpi_reservations`.`reservationitems_id`
	      AND '". $begin."' < `glpi_reservations`.`end`
	      AND '". $end."' > `glpi_reservations`.`begin`)";

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
	  $where ".
	  getEntitiesRestrictRequest(" AND", $itemtable, '',
	      $_SESSION['glpiactiveentities'],
	      $item->maybeRecursive())."
	  ORDER BY username,
	`$itemtable`.`entities_id`,
	`$itemtable`.`name`";

      if ($result = $DB->query($query)) {
	// on regroupe toutes les reservations d'un meme user dans un tableau.
	while ($row = $DB->fetch_assoc($result)) {
	  if(!array_key_exists($row["username"],$ResaByUser)) {
	    $ResaByUser[$row["username"]] = array();
	  }
	  $tmp = array ("resaid" => $row["resaid"],
	      "name" => $row['name'],
	      "debut" => $row["begin"],
	      "fin" => $row["end"],
	      "comment" => nl2br($row["comment"]));
	  $ResaByUser[$row["username"]][] = $tmp;
	  //on trie par date 
	  usort($ResaByUser[$row["username"]], 'compare_date_by_user');
	}
      }
    }
 

    echo "<div class='center'>";
    echo "<table class='tab_cadre'>";
    echo "<thead>";
    echo "<tr><th colspan='".($showentity?"11":"10")."'>"."Matériels empruntés"."</th></tr>\n";
    echo "<tr class='tab_bg_2'>";

    /*echo "<th><a href=\"#\" onclick=\"sortTable(this,0); return false;\">Utilisateur</a></th>";
    echo "<th><a href=\"#\" onclick=\"sortTable(this,1); return false;\">Materiel</a></th>";
    echo "<th><a href=\"#\" onclick=\"sortTable(this,2); return false;\">Debut</a></th>";
    echo "<th><a href=\"#\" onclick=\"sortTable(this,3); return false;\">Fin</a></th>";
    echo "<th><a href=\"#\" onclick=\"sortTable(this,4); return false;\">Commentaires</a></th>";
    echo "<th><a href=\"#\" onclick=\"sortTable(this,5); return false;\">Mouvement</a></th>";*/
    echo "<th>Utilisateur</a></th>";
    echo "<th>Materiel</a></th>";
    echo "<th>Debut</a></th>";
    echo "<th>Fin</a></th>";
    echo "<th>Commentaires</a></th>";
    echo "<th>Mouvement</a></th>";    
    echo "<th>Acquitter</th>";
    echo "<th colspan='".($methode == "manual" ? 3 : 2)."'>Actions</th>";

    echo "</tr></thead>";
    echo "<tbody>";

  
   
    //on parcourt le tableau pour construire la table à afficher
    foreach($ResaByUser as $User => $arrayResa) {
      $nbLigne = 1;
      $limiteLigneNumber = count($arrayResa);
      $flag = 0;
      echo "<tr class='tab_bg_2'>";
      echo "<td rowspan=".count($arrayResa).">".$User."</td>";
      foreach($arrayResa as $Num => $resa) {
        	$colorRed = "";
          $flagSurveille = 0;
        	// on regarde si la reservation actuelle a été prolongée par le plugin
        	$query = "SELECT `date_return`, `date_theorique`, `dernierMail` FROM `glpi_plugin_reservation_manageresa` WHERE `resaid` = ".$resa["resaid"];
        	if ($result = $DB->query($query)) {
        	  $dates = $DB->fetch_row($result);
        	}

         
          

        	if($DB->numrows($result)) {
        	  if($dates[1] < date("Y-m-d H:i:s",time()) && $dates[0] == NULL) {// on colore  en rouge seulement si la date de retour theorique est depassée et si le materiel n'est pas marqué comme rendu (avec une date de retour effectif)
			  $colorRed = "bgcolor=\"red\"";
            		  $flagSurveille = 1;
		  }
          }
      
        	
        	// le nom du materiel
        	echo "<td $colorRed>".$resa['name']."</td>";

          if(!$flag)
          {
            $i = $Num;
            
            while($i < count($arrayResa) - 1 )
          {
            if($arrayResa[$i+1]['debut'] == $resa['debut'] && $arrayResa[$Num+1]['fin'] == $resa['fin']) {
              $nbLigne++;              
            }
              
            else
              break;
            $i++;
          }
            $limiteLigneNumber = $Num + $nbLigne -1;

          }


        	//date de debut de la resa
          if(!$flag) {
        	  echo "<td rowspan=".$nbLigne." $colorRed>".date("d-m-Y \à H:i:s",strtotime($resa["debut"]))."</td>";

        	// si c'est une reservation prolongée, on affiche la date theorique plutot que la date reelle (qui est prolongée jusqu'au retour du materiel)
          if($DB->numrows($result) && $dates[0] == NULL) 
        	  echo "<td rowspan=".$nbLigne." $colorRed>".date("d-m-Y \à H:i:s",strtotime($dates[1]))."</td>";
        	else 
        	  echo "<td rowspan=".$nbLigne." $colorRed>".date("d-m-Y \à H:i:s",strtotime($resa["fin"]))."</td>";
        	
        	//le commentaire
        	echo "<td rowspan=".$nbLigne." $colorRed>".$resa["comment"]."</td>";

        	// les fleches de mouvements	
        	echo "<td rowspan=".$nbLigne." ><center>";
        	if(date("Y-m-d",strtotime($resa["debut"])) == date("Y-m-d",strtotime($begin)))
        	  echo "<img title=\"\" alt=\"\" src=\"../pics/up-icon.png\"></img>";
        	if(date("Y-m-d",strtotime($resa["fin"])) == date("Y-m-d",strtotime($end)))
        	  echo "<img title=\"\" alt=\"\" src=\"../pics/down-icon.png\"></img>";
        	echo "</center></td>";

        }
        if($nbLigne > 1)
          $flag = 1;

        if($Num == $limiteLigneNumber ) {
          $flag = 0;
          $nbLigne=1;
        }
          

        	// si la reservation est rendue, on affiche la date du retour, sinon le bouton pour acquitter le retour
        	if($dates[0] != NULL) 
        	  echo "<td>".date("d-m-Y \à H:i:s",strtotime($dates[0]))."</td>";
        	else
        	  echo "<td><center><a title=\"Marquer comme rendu\" href=\"reservation.php?resareturn=".$resa['resaid']."\"><img title=\"\" alt=\"\" src=\"../pics/greenbutton.png\"></img></a></center></td>";



// boutons action
          $matDispo = getMatDispo();
          echo "<td>";
          echo "<ul>";
          echo "<li><span class=\"bouton\" id=\"bouton_add".$resa['resaid']."\" onclick=\"javascript:afficher_cacher('add".$resa['resaid']."');\">Ajouter un materiel</span>
          <div id=\"add".$resa['resaid']."\" style=\"display:none;\">
          <form method='POST' name='form' action='".Toolbox::getItemTypeSearchURL(__CLASS__)."'>";
          echo '<select name="matDispoAdd">';          
          foreach($matDispo as $mat) {
             echo "\t",'<option value="', key($mat) ,'">', current($mat) ,'</option>';
          }
          echo "<input type='hidden' name='AjouterMatToResa' value='".$resa['resaid']."'>";
          echo "<input type='submit' class='submit' name='submit' value=Ajouter>";
          Html::closeForm();
          echo "</div></li>";
          
          echo "<li><span class=\"bouton\" id=\"bouton_replace".$resa['resaid']."\" onclick=\"javascript:afficher_cacher('replace".$resa['resaid']."');\">Remplacer le materiel</span>
          <div id=\"replace".$resa['resaid']."\" style=\"display:none;\">
          <form method='post' name='form' action='".Toolbox::getItemTypeSearchURL(__CLASS__)."'>";
          echo '<select name="matDispoReplace">';          
          foreach($matDispo as $mat) {
             echo "\t",'<option value="', key($mat) ,'">', current($mat) ,'</option>';
          }
          echo "<input type='hidden' name='ReplaceMatToResa' value='".$resa['resaid']."'>";
          echo "<input type='submit' class='submit' name='submit' value=Remplacer>";
          Html::closeForm();
          echo "</div></li>";
          echo "</ul>";
          echo "</td>";


          echo "<td>";   
          echo "<ul>";
          echo "<li><a class=\"bouton\" title=\"Editer la reservation\" href='../../../front/reservation.form.php?id=".$resa['resaid']."'>Editer la reservation</a></li>";
          echo "</ul>";
          echo "</td>";
        

          if($methode == "manual" ) {
            echo "<td>";   
          echo "<ul>";
          if($flagSurveille) {
          echo "<li><a class=\"bouton\" title=\"Envoyer un mail de rappel\" href=\"reservation.php?mailuser=".$resa['resaid']."\">Envoyer un mail de rappel</a></li>";
           
          if(isset($dates[2])) {
            echo "<li>Dernier mail envoyé le : </li>";
            echo "<li>".date("d-m-Y \à H:i:s",strtotime($dates[2]))."</li>";
          }
  }
          echo "</ul>";
          echo "</td>";
          }
            
            	
          echo "</tr>";
          echo "<tr class='tab_bg_2'>";
          
        	
          }
        echo "</tr>\n";
    }
    echo "</tbody>";
    echo "</table>\n";
    echo "</div>\n";

  }



function addToResa($idmat,$idresa) {

  global $DB, $CFG_GLPI;     
    
    $query = "SELECT * FROM `glpi_reservations` WHERE `id`='".$idresa."';";
      $result = $DB->query($query) or die("error on 'select' dans addToResa / 1: ". $DB->error());


      $matToAdd = $DB->fetch_assoc($result);

      $query = "INSERT INTO  `glpi_reservations` (`begin`, `end`, `reservationitems_id`,`users_id`) VALUES ('".$matToAdd['begin']."', '".$matToAdd['end']."', '".$idmat ."', '".$matToAdd['users_id'] ."');";
      $DB->query($query) or die("error on 'insert' dans addToResa / hash: ". $DB->error());

      // pour avoir l'id et l'itemtypede la nouvelle reservation créée
      $query = "SELECT `glpi_reservations`.`id`, `glpi_reservationitems`.`itemtype` FROM `glpi_reservations`, `glpi_reservationitems`  WHERE `begin` = '".$matToAdd['begin']."' AND `end` = '".$matToAdd['end']."' AND `reservationitems_id` = '".$idmat ."' AND `users_id` ='".$matToAdd['users_id'] ."' AND `glpi_reservationitems`.`id` = `glpi_reservations`.`reservationitems_id`";
      $result = $DB->query($query) or die("error on 'select' dans addToResa / 2: ". $DB->error());
      $res = $DB->fetch_row($result);
      $idnewreservation = $res[0];
      $itemtypenewresa = $res[1];


      //on regarde si la reservation à laquelle on ajoute le materiel est deja "surveillée", pour  alors surveiller le nouveau mat
      $query = "SELECT * FROM `glpi_plugin_reservation_manageresa` WHERE `resaid` = '".$idresa."';";
      $result = $DB->query($query) or die("error on 'select' dans addToResa / manageresa: ". $DB->error());
      
      if($DB->numrows($result)>0) {
        $row = $DB->fetch_assoc($result);
        if($row['date_return'] == NULL)
         $query = "INSERT INTO  `glpi_plugin_reservation_manageresa` (`resaid`, `matid`, `itemtype`,  `date_theorique`) VALUES ('".$idnewreservation."', '".$idmat."', '".$itemtypenewresa."', '". $row['date_theorique']."');";
        else
          $query = "INSERT INTO  `glpi_plugin_reservation_manageresa` (`resaid`, `matid`, `itemtype`, `date_return`, `date_theorique`) VALUES ('".$idnewreservation."', '".$idmat."', '".$itemtypenewresa."', ".$row['date_return']."', '". $row['date_theorique']."');";




      $DB->query($query) or die("error on 'insert' dans addToResa / hash: ". $DB->error());

      }

     
    
       
}



function replaceResa($idmat,$idresa) {
  global $DB, $CFG_GLPI;     
    
      $query = "UPDATE `glpi_reservations` SET `reservationitems_id`='". $idmat ."' WHERE `id`='".$idresa."';";
      $DB->query($query) or die("error on 'update' dans replaceResa / hash: ". $DB->error());  

}


}


function getMatDispo() {
  
  global $DB, $CFG_GLPI, $datesresa;
  
    $showentity = Session::isMultiEntitiesMode();


    $begin = $datesresa["begin"];
    $end   = $datesresa["end"];
    $left = "";
    $where = "";
    $myArray = array();
    
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
  $left = "LEFT JOIN `glpi_reservations`
    ON (`glpi_reservationitems`.`id` = `glpi_reservations`.`reservationitems_id`
        AND '". $begin."' < `glpi_reservations`.`end`
        AND '". $end."' > `glpi_reservations`.`begin`)";
  $where = " AND `glpi_reservations`.`id` IS NULL ";
      }

      $query = "SELECT `glpi_reservationitems`.`id`,
  `glpi_reservationitems`.`comment`,
  `$itemtable`.`id` AS materielid,
  `$itemtable`.`name` AS name,
  `$itemtable`.`entities_id` AS entities_id,
  $otherserial,
  `glpi_locations`.`completename` AS location,
  `glpi_reservationitems`.`items_id` AS items_id
    FROM `glpi_reservationitems`
    $left
    INNER JOIN `$itemtable`
    ON (`glpi_reservationitems`.`itemtype` = '$itemtype'
        AND `glpi_reservationitems`.`items_id` = `$itemtable`.`id`)
    LEFT JOIN `glpi_locations`
    ON (`$itemtable`.`locations_id` = `glpi_locations`.`id`)
    WHERE `glpi_reservationitems`.`is_active` = '1'
    AND `glpi_reservationitems`.`is_deleted` = '0'
    AND `$itemtable`.`is_deleted` = '0'
    $where ".
    getEntitiesRestrictRequest(" AND", $itemtable, '',
        $_SESSION['glpiactiveentities'],
        $item->maybeRecursive())."
    ORDER BY `$itemtable`.`entities_id`,
  `$itemtable`.`name`";


      if ($result = $DB->query($query)) {



    while ($row = $DB->fetch_assoc($result)) {
      array_push($myArray, array($row["id"] => $row["name"]));  
  }
 }
}
return $myArray;
}



function compare_date_by_user($a, $b) { return strnatcmp($a['debut'], $b['debut']); }
function compare_date_by_alluser($a, $b) { return strnatcmp($a[0]['debut'], $b[0]['debut']); }




?>
