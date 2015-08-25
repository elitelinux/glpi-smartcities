<?php
/**
 * @version $Id: methodsession.class.php 398 2014-12-05 16:15:48Z yllen $
 -------------------------------------------------------------------------
 LICENSE

 This file is part of Webservices plugin for GLPI.

 Webservices is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Webservices is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Webservices. If not, see <http://www.gnu.org/licenses/>.

 @package   Webservices
 @author    Nelly Mahu-Lasson
 @copyright Copyright (c) 2009-2014 Webservices plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/webservices
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginWebservicesMethodSession extends PluginWebservicesMethodCommon {

   /**
    * This method try to identicate a user
    *
    * @param $params array of options
    * => login_name : mandatory user name
    * => login_password : mandatory user password
    * => other : optionnal values for post action
    *@param $protocol the communication protocol used
    *
    * @return an response ready to be encode
    * => id of the user
    * => name of the user
    * => realname of the user
    * => firstname of the user
    * => session : ID of the session for future call
   **/
   static function methodLogin($params, $protocol) {

      if (isset($params['help'])) {
         return array( 'login_name'     => 'string,mandatory',
                       'login_password' => 'string,mandatory',
                       'help'           => 'bool,optional');
      }

      if (!isset($params['login_name']) || empty($params['login_name'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'login_name');
      }
      if (!isset($params['login_password']) || empty($params['login_password'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'login_password');
      }

      foreach ($params as $name => $value) {
         switch ($name) {
            case 'login_name' :
            case 'login_password' :
               break;

            default :
               // Store to Session, for post login action (retrieve_more_data_from_ldap, p.e.)
               $_SESSION[$name] = $value;
         }
      }

      $identificat = new Auth();

      if ($identificat->Login($params['login_name'], $params['login_password'], true)) {
         session_write_close();
         return (array('id'        => Session::getLoginUserID(),
                       'name'      => $_SESSION['glpiname'],
                       'realname'  => $_SESSION['glpirealname'],
                       'firstname' => $_SESSION['glpifirstname'],
                       'session'   => $_SESSION['valid_id']));
      }
      return self::Error($protocol, WEBSERVICES_ERROR_LOGINFAILED, '',
                         Html::clean($identificat->getErr()));
   }


   /**
    * This method try to identicate a user
    *
    * @param $params array of options ignored
    * @param $protocol the communication protocol used
    *
    * @return an response ready to be encode
    * => fields of glpi_users
   **/
   static function methodGetMyInfo($params, $protocol) {

      if (isset($params['help'])) {
         return array ('help'    => 'bool,optional',
                       'id2name' => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      $user = new User();
      if ($user->getFromDB($uid=Session::getLoginUserID())) {
         $resp = $user->fields;

         $resp['email'] = UserEmail::getDefaultForUser($uid);
         $resp['emails'] = UserEmail::getAllForUser($uid);

         if (isset($params['id2name'])) {
            $resp['locations_name']
                  = Html::clean(Dropdown::getDropdownName('glpi_locations',
                                                          $resp['locations_id']));
            $resp['usertitles_name']
                  = Html::clean(Dropdown::getDropdownName('glpi_usertitles',
                                                          $resp['usertitles_id']));
            $resp['usercategories_name']
                  = Html::clean(Dropdown::getDropdownName('glpi_usercategories',
                                                          $resp['usercategories_id']));
            $resp['default_requesttypes_name']
                  = Html::clean(Dropdown::getDropdownName('glpi_requesttypes',
                                                          $resp['default_requesttypes_id']));
         }
         return ($resp);
      }
      return self::Error($protocol, WEBSERVICES_ERROR_NOTFOUND);
   }


   /**
    * This method try to identicate an user
    *
    * @param $params array of options ignored
    * @param $protocol the communication protocol used
    *
    * @return a response ready to be encode
    * => Nothing
   **/
   static function methodLogout($params, $protocol) {

      if (isset($params['help'])) {
         return array ('help' => 'bool,optional');
      }

      $msg = "Bye ";
      if (Session::getLoginUserID()) {
         $msg .= (empty ($_SESSION['glpifirstname']) ? $_SESSION['glpiname']
                                                     : $_SESSION['glpifirstname']);
      }

      Session::destroy();

      return array ('message' => $msg);
   }


   /**
    * This method try to identicate an user
    *
    * @param $params array of options ignored
    * @param $protocol the communication protocol used
    *
    * @return a response ready to be encode
    * => fields of glpi_users
   **/
   static function methodListMyProfiles($params, $protocol) {

      if (isset($params['help'])) {
         return array ('help' => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      $resp = array ();
      foreach ($_SESSION['glpiprofiles'] as $id => $prof) {
         $resp[] = array ('id'      => $id,
                          'name'    => $prof['name'],
                          'current' => ($id == $_SESSION['glpiactiveprofile']['id'] ? 1 : 0));
      }
      return $resp;
   }


   /**
    * This method return the entities list allowed
    * for an authenticated users
    *
    * @param $params array of option : ignored
    * @param $protocol the communication protocol used
    *
    * @return a response ready to be encode (ID + completename)
   **/
   static function methodListMyEntities($params, $protocol) {
      global $DB;

      if (isset($params['help'])) {
         return array ('help' => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      $resp = array ();

      foreach ($_SESSION['glpiactiveprofile']['entities'] as $ent) {
         if ($ent['is_recursive']) {
            $search = getSonsOf("glpi_entities",$ent['id']);
         } else {
            $search = $ent['id'];
         }
         if ($ent['id'] == 0) {
            $resp[0] = array ('id'           => 0,
                              'name'         => __('Root entity'),
                              'entities_id'  => 0,
                              'completename' => __('Root entity'),
                              'comment'      => '',
                              'level'        => 0,
                              'is_recursive' => $ent['is_recursive'],
                              'current'      => (in_array(0,
                                                          $_SESSION['glpiactiveentities']) ? 1 : 0));
         }
         foreach ($DB->request('glpi_entities', array ('id' => $search)) as $data) {
            $resp[$data['id']] = array('id'           => $data['id'],
                                       'name'         => $data['name'],
                                       'entities_id'  => $data['entities_id'],
                                       'completename' => $data['completename'],
                                       'comment'      => $data['comment'],
                                       'level'        => $data['level'],
                                       'is_recursive' => $ent['is_recursive'],
                                       'current'      => (in_array($data['id'],
                                                                   $_SESSION['glpiactiveentities'])
                                                                        ? 1 : 0));
         }
      }
      return $resp;
   }


   /**
    * Change the current profile of an authenticated user
    *
    * @param $params array of options
    *  - profile : ID of the new profile
    * @param $protocol the communication protocol used
    *
    * @return a response ready to be encode
    *  - ID
    *  - name of the new profile
   **/
   static function methodSetMyProfile($params, $protocol) {

      if (isset($params['help'])) {
         return array ('profile' => 'integer,mandatory',
                       'help'    => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol,WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }
      if (!isset($params['profile'])) {
         return self::Error($protocol,WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'profile');
      }

      // TODO search for profile name if not an ID.
      $id = $params['profile'];

      if (isset($_SESSION['glpiprofiles'][$id])
          && count($_SESSION['glpiprofiles'][$id]['entities'])) {

         Session::changeProfile($id);
         $resp = array ('id'   => $_SESSION['glpiactiveprofile']['id'],
                        'name' => $_SESSION['glpiactiveprofile']['name']);
      } else {
         return self::Error($protocol,WEBSERVICES_ERROR_BADPARAMETER, '', "profile=$id");
      }
      return $resp;
   }


   /**
    * Change the current entity(ies) of a authenticated user
    *
    * @param $params array of options
    *  - entity : ID of the new entity or "all"
    *  - recursive : 1 to see children
    * @return like plugin_webservices_method_listEntities
    */
   static function methodSetMyEntity($params, $protocol) {

      if (isset($params['help'])) {
         return array ('entity'    => 'integer,mandatory',
                       'recursive' => 'bool,optional',
                       'help'      => 'bool,optional');
      }

      if (!Session::getLoginUserID()) {
         return self::Error($protocol, WEBSERVICES_ERROR_NOTAUTHENTICATED);
      }

      if (!isset($params['entity'])) {
         return self::Error($protocol, WEBSERVICES_ERROR_MISSINGPARAMETER, '', 'entity');
      }

      if (Session::changeActiveEntities($params['entity'],
                                        (isset($params['recursive']) && $params['recursive']))) {
         return self::methodListEntities($_SESSION['glpiactiveentities'], $params);
      }

      return self::Error($protocol, WEBSERVICES_ERROR_BADPARAMETER, '',
                         "entity=" . $params['entity']);
   }


   /**
    * Recovery session
    *
    * @param session the session ID
    *
    * TODO : use it for xmlrpc.php
   **/
   static function setSession($session) {

      $current = session_id();
      $session = trim($session);

      if (file_exists(GLPI_ROOT . "/config/config_path.php")) {
         include_once (GLPI_ROOT . "/config/config_path.php");
      }
      if (!defined("GLPI_SESSION_DIR")) {
         define("GLPI_SESSION_DIR", GLPI_ROOT . "/files/_sessions");
      }

      if ($session!=$current && !empty($current)) {
         session_destroy();
      }
      if ($session!=$current && !empty($session)) {
         if (ini_get("session.save_handler")=="files") {
            session_save_path(GLPI_SESSION_DIR);
         }
         session_id($session);
         session_start();

         // Define current time for sync of action timing
         $_SESSION["glpi_currenttime"] = date("Y-m-d H:i:s");
      }
   }


   /**
    * Standard method execution : checks if client can execute method + manage session
    *
    * @param $method string method name
    * @param $params array the method parameters
    * @param $protocol the communication protocol used
    *
    * @return array the method response
   **/
   function execute($method, $params, $protocol) {
      global $DB, $WEBSERVICES_METHOD, $TIMER_DEBUG;

      // Don't display error in result
      set_error_handler(array('Toolbox', 'userErrorHandlerNormal'));
      ini_set('display_errors', 'Off');

      $iptxt = (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"]
                                                        : $_SERVER["REMOTE_ADDR"]);
      $ipnum = (strstr($iptxt, ':')===false ? ip2long($iptxt) : '');


      if (isset($_SESSION["MESSAGE_AFTER_REDIRECT"])) {
         // Avoid to keep "info" message between call
         $_SESSION["MESSAGE_AFTER_REDIRECT"]='';
      }

      $plug = new Plugin();
      if ($plug->isActivated('webservices')) {
         if (isset($params['session'])) {
            self::setSession($params['session']);
         }

         // Build query for security check
         $sql = "SELECT *
                 FROM `glpi_plugin_webservices_clients`
                 WHERE '" . addslashes($method) . "' REGEXP pattern
                       AND `is_active` = '1' ";
         if ($ipnum) {
            $sql .= " AND (`ip_start` IS NULL
                            OR (`ip_start` <= '$ipnum' AND `ip_end` >= '$ipnum'))";
         } else {
            $sql .= " AND `ipv6` = '".addslashes($iptxt)."'";
         }

         if (isset($params["username"])) {
            $username = addslashes($params["username"]);
            $password = md5(isset($params["password"]) ? $params["password"] : '');

            $sql     .= " AND (`username` IS NULL
                               OR (`username` = '$username' AND `password` = '$password'))";

            unset ($params["username"]);
            unset ($params["password"]);

         } else {
            $username = 'anonymous';
            $sql     .= " AND `username` IS NULL ";
         }

         $deflate  = $debug = $log = false;
         $entities = array ();
         if (Session::getLoginUserID() && isset($_SESSION['glpiactiveentities'])) {
            $username = $_SESSION['glpiname']; // for log (no t for SQL request)
         }

         foreach ($DB->request($sql) as $data) {
            // Check matching rules

            // Store entities for not authenticated user
            if (!Session::getLoginUserID()) {
               if ($data['is_recursive']) {
                  foreach (getSonsOf("glpi_entities",$data['entities_id']) as $entity) {
                     $entities[$entity] = $entity;
                  }
               } else {
                  $entities[$data['entities_id']] = $data['entities_id'];
               }
            }

            // Where to log
            if ($data["do_log"] == 2) {
               // Log to file
               $log = LOGFILENAME;
            } else if ($data["do_log"] == 1) {
               // Log to History
               $log = $data["id"];
            }
            $debug = $data['debug'];
            $deflate = $data['deflate'];
         }
         $callname='';
         // Always log when connection denied
         if (!Session::getLoginUserID() && !count($entities)) {
            $resp = self::Error($protocol,1, __('Access denied'));

            // log to file (not macthing config to use history)
            Toolbox::logInFile(LOGFILENAME,
                               __('Access denied')." ($username, $iptxt, $method, $protocol)\n");
         } else { // Allowed
            if (!Session::getLoginUserID()) {
               // TODO : probably more data should be initialized here
               $_SESSION['glpiactiveentities'] = $entities;
            }
            // Log if configured
            if (is_numeric($log)) {
               $changes[0] = 0;
               $changes[1] = "";
               $changes[2] = __('Connection') . " ($username, $iptxt, $method, $protocol)";
               Log::history($log, 'PluginWebservicesClient', $changes, 0,
                            Log::HISTORY_LOG_SIMPLE_MESSAGE);
            } else if ($log && !$debug) {
               Toolbox::logInFile($log, __('Connection') . " ($username, $iptxt, $method)\n");
            }

            $defserver = ini_get('zlib.output_compression');

            if ($deflate && !$defserver) {
               // Globally off, try to enable for this client
               // This only work on PHP > 5.3.0
               ini_set('zlib.output_compression', 'On');
            }
            if (!$deflate && $defserver) {
               // Globally on, disable for this client
               ini_set('zlib.output_compression', 'Off');
            }

            if (!isset($WEBSERVICES_METHOD[$method])) {
               $resp = self::Error($protocol,2, "Unknown method ($method)");
               Toolbox::logInFile(LOGFILENAME, "Unknown method ($method)\n");
            } else if (is_callable($call=$WEBSERVICES_METHOD[$method], false, $callname)) {
               $resp = call_user_func($WEBSERVICES_METHOD[$method], $params, $protocol);
               Toolbox::logInFile(LOGFILENAME,
                                  "Execute method:$method ($protocol), function:$callname, ".
                                  "duration:".$TIMER_DEBUG->getTime().", size:".
                                  strlen(serialize($resp))."\n");
            } else {
               $resp = self::Error($protocol, 3, "Unknown internal function for $method",
                                                $protocol);
               Toolbox::logInFile(LOGFILENAME, "Unknown internal function for $method\n");
            }
         } // Allowed
         if ($debug) {
            Toolbox::logInFile(LOGFILENAME, __('Connection') . ": $username, $iptxt\n".
                               "Protocol: $protocol, Method: $method, Function: $callname\n".
                               "Params: ".(count($params) ? print_r($params, true) : "none\n") .
                               "Compression: Server:$defserver/" . ini_get('zlib.output_compression') .
                               ", Config:$deflate, Agent:" .
                               (isset($_SERVER['HTTP_ACCEPT_ENCODING'])
                                             ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '?') .
                               "\nDuration: " .$TIMER_DEBUG->getTime().
                               "\nResponse size: ".strlen(serialize($resp)).
                               "\nResponse content: " .print_r($resp, true));
         }
      } else {
         $resp = self::Error($protocol,4, "Server not ready",$protocol);
      } // Activated

      return $resp;
   }
}
?>