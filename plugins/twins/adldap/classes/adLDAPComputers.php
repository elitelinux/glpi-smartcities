<?php
/**
 * PHP LDAP CLASS FOR MANIPULATING ACTIVE DIRECTORY 
 * Version 4.0.4
 * 
 * PHP Version 5 with SSL and LDAP support
 * 
 * Written by Scott Barnett, Richard Hyland
 *   email: scott@wiggumworld.com, adldap@richardhyland.com
 *   http://adldap.sourceforge.net/
 * 
 * Copyright (c) 2006-2012 Scott Barnett, Richard Hyland
 * 
 * We'd appreciate any improvements or additions to be submitted back
 * to benefit the entire community :)
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * @category ToolsAndUtilities
 * @package adLDAP
 * @subpackage Computers
 * @author Scott Barnett, Richard Hyland
 * @copyright (c) 2006-2012 Scott Barnett, Richard Hyland
 * @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html LGPLv2.1
 * @revision $Revision: 97 $
 * @version 4.0.4
 * @link http://adldap.sourceforge.net/
 */
require_once(dirname(__FILE__) . '/../adLDAP.php');
require_once(dirname(__FILE__) . '/../collections/adLDAPComputerCollection.php');  

/**
* COMPUTER MANAGEMENT FUNCTIONS
*/
class adLDAPComputers {
    
    /**
    * The current adLDAP connection via dependency injection
    * 
    * @var adLDAP
    */
    protected $adldap;
    
    public function __construct(adLDAP $adldap) {
        $this->adldap = $adldap;
    }
    
    /**
    * Get information about a specific computer. Returned in a raw array format from AD
    * 
    * @param string $computerName The name of the computer
    * @param array $fields Attributes to return
    * @return array
    */
    public function info($computerName, $fields = NULL)
    {
        if ($computerName === NULL) { return false; }
        if (!$this->adldap->getLdapBind()) { return false; }

        $filter = "(&(objectClass=computer)(cn=" . $computerName . "))";
        if ($fields === NULL) { 
            $fields = array("memberof","cn","displayname","dnshostname","distinguishedname","objectcategory","operatingsystem","operatingsystemservicepack","operatingsystemversion"); 
        }
        $sr = ldap_search($this->adldap->getLdapConnection(), $this->adldap->getBaseDn(), $filter, $fields);
        $entries = ldap_get_entries($this->adldap->getLdapConnection(), $sr);
        
        return $entries;
    }
    
    /**
    * Find information about the computers. Returned in a raw array format from AD
    * 
    * @param string $computerName The name of the computer
    * @param array $fields Array of parameters to query
    * @return mixed
    */
    public function infoCollection($computerName, $fields = NULL)
    {
        if ($computerName === NULL) { return false; }
        if (!$this->adldap->getLdapBind()) { return false; }
        
        $info = $this->info($computerName, $fields);
        
        if ($info !== false) {
            $collection = new adLDAPComputerCollection($info, $this->adldap);
            return $collection;
        }
        return false;
    }
    
    /**
    * Check if a computer is in a group
    * 
    * @param string $computerName The name of the computer
    * @param string $group The group to check
    * @param bool $recursive Whether to check recursively
    * @return array
    */
    public function inGroup($computerName, $group, $recursive = NULL)
    {
        if ($computerName === NULL) { return false; }
        if ($group === NULL) { return false; }
        if (!$this->adldap->getLdapBind()) { return false; }
        if ($recursive === NULL) { $recursive = $this->adldap->getRecursiveGroups(); } // use the default option if they haven't set it

        //get a list of the groups
        $groups = $this->groups($computerName, array("memberof"), $recursive);

        //return true if the specified group is in the group list
        if (in_array($group, $groups)){ 
            return true; 
        }

        return false;
    }
    
    /**
    * Get the groups a computer is in
    * 
    * @param string $computerName The name of the computer
    * @param bool $recursive Whether to check recursively
    * @return array
    */
    public function groups($computerName, $recursive = NULL)
    {
        if ($computerName === NULL) { return false; }
        if ($recursive === NULL) { $recursive = $this->adldap->getRecursiveGroups(); } //use the default option if they haven't set it
        if (!$this->adldap->getLdapBind()){ return false; }

        //search the directory for their information
        $info = @$this->info($computerName, array("memberof", "primarygroupid"));
        $groups = $this->adldap->utilities()->niceNames($info[0]["memberof"]); //presuming the entry returned is our guy (unique usernames)

        if ($recursive === true) {
            foreach ($groups as $id => $groupName){
              $extraGroups = $this->adldap->group()->recursiveGroups($groupName);
              $groups = array_merge($groups, $extraGroups);
            }
        }

        return $groups;
    }

//============================================================================//
//== Fonctions rajoutées =====================================================//
//============================================================================//
    
    /**
     * Créé un ordinateur dans l'ad
     * @param array $attributes
     * @return string|boolean
     */
    public function create($attributes)
        {
        // Check for compulsory fields
        if (!array_key_exists("cn", $attributes)){ return "Missing compulsory field [username]"; }
        if (!array_key_exists("container", $attributes)){ return "Missing compulsory field [container]"; }
        if (!is_array($attributes["container"])){ return "Container attribute must be an array."; }

        // Translate the schema
        //  $add = $this->adldap->adldap_schema($attributes);

        // Additional stuff only used for adding accounts
        $add["cn"][0] = $attributes["cn"];
        $add["sAMAccountName"][0] = $attributes["cn"]."$";
        $add["objectClass"][0] = "top";
        $add["objectClass"][1] = "person";
        $add["objectClass"][2] = "organizationalPerson";
        $add["objectClass"][3] = "user";//person?
        $add["objectClass"][4] = "computer";
        //$add["name"][0]=$attributes["firstname"]." ".$attributes["surname"];

        // Set the account control attribute
        $control_options = array("WORKSTATION_TRUST_ACCOUNT");

        $add["userAccountControl"][0] = $this->accountControl($control_options);

        // Determine the container
        $attributes["container"] = array_reverse($attributes["container"]);
        //$container = "OU=" . implode(",OU= ",$attributes["container"]);
        $container = "CN=" . implode(",CN= ",$attributes["container"]);
        
        // Add the entry
        $result = @ldap_add($this->adldap->getLdapConnection(), "CN=" . $add["cn"][0] . "," . $container . "," . $this->adldap->getBaseDn(), $add);
        
        if ($result != true) {return false;}
        return true;
        }
        
    /**
    * Account control options
    *
    * @param array $options The options to convert to int 
    * @return int
    */
    protected function accountControl($options)
    {
        $val=0;

        if (is_array($options)) {
            if (in_array("SCRIPT",$options)){ $val=$val+1; }
            if (in_array("ACCOUNTDISABLE",$options)){ $val=$val+2; }
            if (in_array("HOMEDIR_REQUIRED",$options)){ $val=$val+8; }
            if (in_array("LOCKOUT",$options)){ $val=$val+16; }
            if (in_array("PASSWD_NOTREQD",$options)){ $val=$val+32; }
            //PASSWD_CANT_CHANGE Note You cannot assign this permission by directly modifying the UserAccountControl attribute.
            //For information about how to set the permission programmatically, see the "Property flag descriptions" section.
            if (in_array("ENCRYPTED_TEXT_PWD_ALLOWED",$options)){ $val=$val+128; }
            if (in_array("TEMP_DUPLICATE_ACCOUNT",$options)){ $val=$val+256; }
            if (in_array("NORMAL_ACCOUNT",$options)){ $val=$val+512; }
            if (in_array("INTERDOMAIN_TRUST_ACCOUNT",$options)){ $val=$val+2048; }
            if (in_array("WORKSTATION_TRUST_ACCOUNT",$options)){ $val=$val+4096; }
            if (in_array("SERVER_TRUST_ACCOUNT",$options)){ $val=$val+8192; }
            if (in_array("DONT_EXPIRE_PASSWORD",$options)){ $val=$val+65536; }
            if (in_array("MNS_LOGON_ACCOUNT",$options)){ $val=$val+131072; }
            if (in_array("SMARTCARD_REQUIRED",$options)){ $val=$val+262144; }
            if (in_array("TRUSTED_FOR_DELEGATION",$options)){ $val=$val+524288; }
            if (in_array("NOT_DELEGATED",$options)){ $val=$val+1048576; }
            if (in_array("USE_DES_KEY_ONLY",$options)){ $val=$val+2097152; }
            if (in_array("DONT_REQ_PREAUTH",$options)){ $val=$val+4194304; } 
            if (in_array("PASSWORD_EXPIRED",$options)){ $val=$val+8388608; }
            if (in_array("TRUSTED_TO_AUTH_FOR_DELEGATION",$options)){ $val=$val+16777216; }
        }
        return $val;
    }
    
    /**
     * Supprime un ordinateur de l'ad
     * @param type $computername
     * @param type $isGUID
     * @return boolean
     */
    public function delete($computername, $isGUID = false) 
        {      
        $userinfo = $this->info($computername, array("*"), $isGUID);
        $dn = $userinfo[0]['distinguishedname'][0];
        $result = $this->adldap->folder()->delete($dn);
        if ($result != true) {return false;}        
        return true;
        }
}
?>