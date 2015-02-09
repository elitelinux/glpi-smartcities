<?php
//============================================================================//
//==    Plugin pour GLPI - Dévelloppeur: Viduc (Fleury Tristan) - ©2013     ==//
//==            http://viduc.sugarbox.fr - viduc@sugarbox.fr                ==//
//============================================================================//

/**
 * Class de gestion pour la partie profil
 */
class PluginGroupeadProfile extends Profile{
    
    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
        if ($item->getID() > 0 && $item->fields['interface'] == 'central') {
            return self::createTabEntry(__('GroupeAD', 'groupead'));
        }
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
        $pfProfile = new self();
        $pfProfile->showForm($item->getID());
        return TRUE;
    }

    /**
    * Show profile form
    *
    * @param $items_id integer id of the profile
    * @param $target value url of target
    *
    * @return nothing
    **/
    function showForm($profiles_id=0, $openform=TRUE, $closeform=TRUE) 
    {    
        $profile = new Profile();
        echo "<div class='firstbloc'>";
        if (($canedit = Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE)))
        && $openform) {
            echo "<form method='post' action='".$profile->getFormURL()."'>";
        }
        $profile->getFromDB($profiles_id);

        $rights = $this->getAllRights();
        $profile->displayRightsChoiceMatrix($rights, array('canedit'       => $canedit,
                                                      'default_class' => 'tab_bg_2',
                                                      'title'         => __('Reforme', 'reforme')));
        if ($canedit && $closeform) {
            echo "<div class='center'>";
            echo Html::hidden('id', array('value' => $profiles_id));
            echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
            echo "</div>\n";
            echo Html::closeForm(false);
        }
        echo "</div>";
    }

    static function uninstallProfile() {
        $pfProfile = new self();
        $a_rights = $pfProfile->getAllRights();
        foreach ($a_rights as $data) {
            ProfileRight::deleteProfileRights(array($data['field']));
        }
    }

    function getAllRights() {
        $a_rights = array(
            /*array('rights'    => array(UPDATE  => __('Update')),
                'label'     => __('Gestion configuration', 'reforme'),
                'field'     => 'plugin_reforme_config'
            ),*/
            array('rights'    => array(CREATE  => __('Create')),
                'label'     => __('Utilisation groupeAD', 'groupead'),
                'field'     => 'plugin_groupead_groupead'
            )
        );
        return $a_rights;
    }
   
    static function addDefaultProfileInfos($profiles_id, $rights) {
        $profileRight = new ProfileRight();
        foreach ($rights as $right => $value) {
            if (!countElementsInTable('glpi_profilerights',
                "`profiles_id`='$profiles_id' AND `name`='$right'")) {
                $myright['profiles_id'] = $profiles_id;
                $myright['name']        = $right;
                $myright['rights']      = $value;
                $profileRight->add($myright);

                //Add right to the current session
                $_SESSION['glpiactiveprofile'][$right] = $value;
            }
        }
    }

    /**
     * @param $ID  integer
     */
    static function createFirstAccess($profiles_id) {
        include_once(GLPI_ROOT."/plugins/groupead/inc/profile.class.php");
        $profile = new self();
        foreach ($profile->getAllRights() as $right) {
            self::addDefaultProfileInfos($profiles_id,
                array($right['field'] => ALLSTANDARDRIGHT));
        }
    }

    static function removeRights() {
        $profile = new self();
        foreach ($profile->getAllRights() as $right) {
            if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
                unset($_SESSION['glpiactiveprofile'][$right['field']]);
            }
            ProfileRight::deleteProfileRights(array($right['field']));
        }
    }

    static function migrateProfiles() {
        global $DB;
        //Get all rights from the old table
        $profiles = getAllDatasFromTable(getTableForItemType(__CLASS__));

        //Load mapping of old rights to their new equivalent
        $oldrights = self::getOldRightsMappings();

        //For each old profile : translate old right the new one
        foreach ($profiles as $id => $profile) {
            switch ($profile['right']) {
                case 'r' :
                   $value = READ;
                   break;
                case 'w':
                   $value = ALLSTANDARDRIGHT;
                   break;
                case 0:
                default:
                   $value = 0;
                   break;
            }
        //Write in glpi_profilerights the new fusioninventory right
            if (isset($oldrights[$profile['type']])) {
                //There's one new right corresponding to the old one
                if (!is_array($oldrights[$profile['type']])) {
                    self::addDefaultProfileInfos($profile['profiles_id'],
                        array($oldrights[$profile['type']] => $value));
                } 
                else {
                    //One old right has been splitted into serveral new ones
                    foreach ($oldrights[$profile['type']] as $newtype) {
                        self::addDefaultProfileInfos($profile['profiles_id'],
                            array($newtype => $value));
                        }
                }
            }
        }
    }

    /**
    * Init profiles during installation :
    * - add rights in profile table for the current user's profile
    * - current profile has all rights on the plugin
    */
    static function initProfile() {
        $pfProfile = new self();
        $profile   = new Profile();
        $a_rights  = $pfProfile->getAllRights();

        foreach ($a_rights as $data) {
            if (countElementsInTable("glpi_profilerights", "`name` = '".$data['field']."'") == 0) {
                ProfileRight::addProfileRights(array($data['field']));
                $_SESSION['glpiactiveprofile'][$data['field']] = 0;
            }
        }

        // Add all rights to current profile of the user
        if (isset($_SESSION['glpiactiveprofile'])) {
            $dataprofile       = array();
            $dataprofile['id'] = $_SESSION['glpiactiveprofile']['id'];
            $profile->getFromDB($_SESSION['glpiactiveprofile']['id']);
            foreach ($a_rights as $info) {
                if (is_array($info)
                    && ((!empty($info['itemtype'])) || (!empty($info['rights'])))
                    && (!empty($info['label'])) && (!empty($info['field']))) {

                    if (isset($info['rights'])) {
                        $rights = $info['rights'];
                    } else {
                        $rights = $profile->getRightsFor($info['itemtype']);
                    }
                    foreach ($rights as $right => $label) {
                        $dataprofile['_'.$info['field']][$right] = 1;
                        $_SESSION['glpiactiveprofile'][$data['field']] = $right;
                    }
                }
            }
            $profile->update($dataprofile);
        }
    }
    
}

?>