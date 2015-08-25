<?php


if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


class PluginReservationConfig extends CommonDBTM {

    function getConfigurationMethode()
        {
            global $DB;
            $query = "SELECT * FROM glpi_plugin_reservation_config WHERE name='methode'";
        if ($result = $DB->query($query))
            {
            if ($DB->numrows($result) > 0)
                {
                while ($row = $DB->fetch_assoc($result)) 
                    {
                        $methode = $row['value'];           
                    }
                }  
            }
        return $methode;

        }

function setConfigurationMethode($methode="manual")
{
    global $DB;       

    
        $query = "UPDATE `glpi_crontasks` SET state=".($methode == "manual" ? 0 : 1)." WHERE name = 'MailUserDelayedResa'";
        $DB->query($query) or die($DB->error());
    


    $query = "UPDATE glpi_plugin_reservation_config SET value='".$methode."' where name = 'methode'";
        $DB->query($query) or die($DB->error());
}        
    

    function getConfigurationWeek()
        {
        global $DB;
        
        $query = "SELECT * FROM glpi_plugin_reservation_configdayforauto WHERE actif=1";
        if ($result = $DB->query($query))
            {
            if ($DB->numrows($result) > 0)
                {
                while ($row = $DB->fetch_assoc($result)) 
                    {
                        $config[$row['jour']] = $row['actif'];           
                    }
                }  
            }
        return $config;
        }


function setConfigurationWeek($week=null)
{
    global $DB;       

    $query = "UPDATE glpi_plugin_reservation_configdayforauto SET actif=0";
        $DB->query($query) or die($DB->error());
    foreach($week as $day)
    {
        $query = "UPDATE glpi_plugin_reservation_configdayforauto SET actif=1 WHERE jour='$day'";
        $DB->query($query) or die($DB->error());
    }
}


function showForm() 
{
$methode = $this->getConfigurationMethode();
$config = $this->getConfigurationWeek();

echo "<form method='post' action='".$this->getFormURL()."'>";

echo "<div class='center'>";


if($methode == "auto")
{
echo "<table class='tab_cadre_fixe'  cellpadding='2'>";

echo "<th>Mail aux utilisateurs avec reservation depassée</th>";
echo "<tr>";
echo "<td> lundi : </td><td> <INPUT type=\"checkbox\" name=\"week[]\" value=\"lundi\" ".(isset($config['lundi'])?'checked':'')." > </td>";
echo "</tr>";
echo "<tr>";
echo "<td> mardi : </td><td> <INPUT type=\"checkbox\" name=\"week[]\" value=\"mardi\"".(isset($config['mardi'])?'checked':'')."> </td>";
echo "</tr>";
echo "<tr>";
echo "<td> mercredi : </td><td> <INPUT type=\"checkbox\" name=\"week[]\" value=\"mercredi\" ".(isset($config['mercredi'])?'checked':'')."> </td>";
echo "</tr>";
echo "<tr>";
echo "<td> jeudi : </td><td> <INPUT type=\"checkbox\" name=\"week[]\" value=\"jeudi\" ".(isset($config['jeudi'])?'checked':'')."> </td>";
echo "</tr>";
echo "<tr>";
echo "<td> vendredi : </td><td> <INPUT type=\"checkbox\" name=\"week[]\" value=\"vendredi\" ".(isset($config['vendredi'])?'checked':'')." ></td>";
echo "</tr>";
echo "<tr>";
echo "<td> samedi : </td><td> <INPUT type=\"checkbox\" name=\"week[]\" value=\"samedi\" ".(isset($config['samedi'])?'checked':'')." ></td>";
echo "</tr>";
echo "<tr>";
echo "<td> dimanche : </td><td> <INPUT type=\"checkbox\" name=\"week[]\" value=\"dimanche\" ".(isset($config['dimanche'])?'checked':'')."> </td>";

echo "</tr>";
echo "</table>";
}



echo "<table class='tab_cadre_fixe'  cellpadding='2'>";

echo "<th>Methode pour gerer l'envoi de mail aux utilisateurs dont la reservation est depassée </th>";
echo "<tr>";
echo "<td> <input type=\"radio\" name=\"methode\" value=\"auto\" ".($methode == "auto" ? 'checked':'')."> Automatiquement (avec l'action automatique à configurer) </td>";
echo "</tr>";
echo "<tr>";
echo "<td> <input type=\"radio\" name=\"methode\" value=\"manual\" ".($methode == "manual" ? 'checked':'')."> Manuellement (à l'aide du bouton sur la vue des reservations en cours)</td>" ;
echo "</tr>";

echo "</table>";



echo "<input type=\"submit\" value=\"Valider\">";
echo "</div>";



Html::closeForm();





}



}
?>
