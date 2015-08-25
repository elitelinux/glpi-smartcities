<?php

/**
 * PluginTimezonesUser short summary.
 *
 * PluginTimezonesUser description.
 *
 * @version 1.0
 * @author MoronO
 */
class PluginTimezonesUser extends CommonDBTM
{
   
    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
        global $LANG;
        
        return array( 'timezonestimezones' => $LANG['timezones']['item']['tab'] );
        
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

        if ( in_array( $item->getType(), array( 'Preference', 'User' ))) {
            $pref = new self();
            $user_id = ($item->getType()=='Preference'?Session::getLoginUserID():$item->getID());
            $pref->showForm($user_id);
        }
        return true;
    }

    function showForm($user_id, $options=array()) {
        global $LANG;

        $target = $this->getFormURL();
        if (isset($options['target'])) {
            $target = $options['target'];
        }

        $tzID = $this->getIDFromUserID( $user_id ) ;
        if ($user_id) {
            if( !$tzID ) {
                $tz=ini_get('date.timezone');
                if (empty($tz)) {
                     $tz = @date_default_timezone_get();
                }
                $this->add( array( 'users_id' => $user_id, 'timezone' => $tz) );
                $tzID = $this->getID();
            } else {
                $this->getFromDB( $tzID );
            }
        } else 
            return ;

        echo "<form action='".$target."' method='post'>";
        echo "<input type=hidden name=users_id value='$user_id'/>";
        echo "<input type=hidden name=id value='$tzID'/>";
        echo "<table class='tab_cadre_fixe'>";

        echo "<tr><th colspan='2'>".$LANG['timezones']['item']['header']."</th></tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<td>".$LANG['timezones']['item']['dropdown']." :</td><td>";

        $timezones = self::getTimezones( ) ;
        Dropdown::showFromArray('timezone', $timezones, array('value' => $this->fields["timezone"]));

        echo "</td></tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<td colspan='4' class='center'>";
        echo "<input type='submit' name='update' class='submit' value=\"".$LANG['timezones']['item']['submit']."\">";
        echo "</td></tr>";

        echo "</table>";
        Html::closeForm();
    }

    /**
     * Summary of getTimezones
     * @return array: an array of string (timezones name). This list is checked with MySQL time_zone list
     */
    static function getTimezones( ) {
        global $DB ;

        $tz = array() ; //default $tz is empty
        $phpTimezones = DateTimeZone::listIdentifiers();
        $now = new DateTime ;
        $query = "SELECT Name FROM mysql.time_zone_name" ;
        foreach( $DB->request( $query ) as $mySQLTimezone ) {
            if( in_array( $mySQLTimezone['Name'], $phpTimezones ) ){
                $now->setTimezone( new DateTimeZone( $mySQLTimezone['Name'] ) );
                $tz[ $mySQLTimezone['Name'] ] = $mySQLTimezone['Name'] . $now->format( " (T P)" );
            }
        }

        return $tz ;
    }

    /**
     * Summary of getIDFromUserID
     * @param mixed $user_id 
     * @return mixed returns id of record if found, false otherwise
     */
    function getIDFromUserID( $user_id ) {
        $found = $this->find("users_id = ".$user_id);
        if( $found ) {
            $first_found = array_pop($found);
            return $first_found['id'];
        }
        return false ;
    }

    /**
     * Summary of preItemUpdate
     * will add or update record in DB
     * @param CommonDBTM $parm 
     */
    static function preItemUpdate( CommonDBTM $parm ){
       global $DB;

       if($parm->getType() == 'User' && isset( $parm->input['plugin_timezones_users_timezone']) ) {
           //$query = "REPLACE INTO `glpi_plugin_timezones_users` (`users_id`, `timezone`) VALUES (".$parm->getID().", '".$parm->input['plugin_timezones_users_timezone']."');";
           //$DB->query( $query ) ;
           $tzUser = new self;
           $data = array( 'users_id' => $parm->getID(), 'timezone' => $parm->input['plugin_timezones_users_timezone'] ) ;
           //check if datas already inserted
           $found = $tzUser->getIDFromUserID($parm->getID());
            if (!$found) {
                $tzUser->add($data);
            } else {
                $data['id'] = $found;
                $tzUser->update($data);
            }

       }

    }
}
