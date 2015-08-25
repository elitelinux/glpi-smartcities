<?php
/**
 * @version $Id: examplexmlrpc.php 396 2014-11-23 18:46:25Z yllen $
 -------------------------------------------------------------------------
 webservices - WebServices plugin for GLPI
 Copyright (C) 2003-2013 by the webservices Development Team.

 https://forge.indepnet.net/projects/webservices
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

// ----------------------------------------------------------------------
// Purpose of file: XML-RPC Client testing for GLPI
// ----------------------------------------------------------------------

if (!extension_loaded("xmlrpc")) {
   die("Extension xmlrpc not loaded\n");
}

/*
* SETTINGS
*/
chdir(dirname($_SERVER["SCRIPT_FILENAME"]));
chdir("../../..");
$url = "/" . basename(getcwd()) . "/plugins/webservices/xmlrpc.php";

$args = array ();
if ($_SERVER['argc'] > 1) {
   for ($i = 1 ; $i < count($_SERVER['argv']) ; $i++) {
      $it           = explode("=", $argv[$i], 2);
      $it[0]        = preg_replace('/^--/', '', $it[0]);
      $args[$it[0]] = (isset($it[1]) ? $it[1] : true);
   }
}

if (isset($args['help'])) {
   echo "\nusage : " . $_SERVER["SCRIPT_FILENAME"] . " [options] \n\n";

   echo "\thelp     : display this screen\n";
   echo "\thost     : server name or IP (default = localhost)\n";
   echo "\turl      : XML-RPC plugin URL (default = $url)\n\n";
   echo "\tws_user : login name of webservices user (optional)\n";
   echo "\tws_pass : login password of webservices user (optional)\n\n";
   echo "\tglpi_user : login name of GLPI user (default = glpi)\n";
   echo "\tglpi_pass : login password of GLPI user (default = glpi)\n\n";
   echo "\tglpi_test_user : login name for created user (default = WSXML-RPC_User_01_TESTING)\n";
   echo "\tglpi_test_pass : password for test user (default = WSXML-RPC_User_01_TESTING)\n\n";
   echo "\tdeflate  : allow server to compress response (if supported)\n";

   die("\n\n");
}

if (isset($args['url'])) {
   $url = $args['url'];
   unset($args['url']);
}

if (isset($args['host'])) {
   $host = $args['host'];
   unset ($args['host']);
} else {
   $host = 'localhost';
}

if (isset($args['glpi_user'])) {
   $glpi_user = $args['glpi_user'];
   unset($args['glpi_user']);
} else {
   $glpi_user = "glpi";
}

if (isset($args['glpi_pass'])) {
   $glpi_pass = $args['glpi_pass'];
   unset($args['glpi_pass']);
} else {
   $glpi_pass = "glpi";
}

if (isset($args['glpi_test_user'])) {
   $glpi_test_user = $args['glpi_test_user'];
   unset($args['glpi_test_user']);
} else {
   $glpi_test_user = "WSXML-RPC_User_01_TESTING";
}

if (isset($args['glpi_test_pass'])) {
   $glpi_test_pass = $args['glpi_test_pass'];
   unset($args['glpi_test_pass']);
} else {
   $glpi_test_pass = "WSXML-RPC_User_01_TESTING";
}

if (isset($args['deflate'])) {
   unset($args['deflate']);
   $deflate = true;
}

if (isset($args['base64'])) {
   $content = @file_get_contents($args['base64']);
   if (!$content) {
      die ("File not found or empty (".$args['base64'].")\n");
   }
   $base64 = base64_encode($content);
   unset($args['base64']);
}

if (isset($args['ws_user'])) {
   $ws_user = $args['ws_user'];
   unset($args['ws_user']);
}

if (isset($args['ws_pass'])) {
   $ws_pass = $args['ws_pass'];
   unset($args['ws_pass']);
}


/*
* LOGIN
*/
function login() {
   global $glpi_user, $glpi_pass, $ws_user, $ws_pass;

    $args['method']          = "glpi.doLogin";
    $args['login_name']      = $glpi_user;
    $args['login_password']  = $glpi_pass;

    if (isset($ws_user)) {
       $args['username'] = $ws_user;
    }

    if (isset($ws_pass)) {
       $args['password'] = $ws_pass;
    }

    if ($result = call_glpi($args)) {
       return $result['session'];
    }
}


/*
* LOGOUT
*/
function logout() {
    $args['method'] = "glpi.doLogout";

    if ($result = call_glpi($args)) {
       return true;
    }
}


/*
* GENERIC CALL
*/
function call_glpi($args) {
   global $host,$url,$deflate,$base64;

   // To avoid IDE warning
   $http_response_header = '';

   echo "+ Calling {$args['method']} on http://$host/$url\n";

   if (isset($args['session'])) {
      $url_session = $url.'?session='.$args['session'];
   } else {
      $url_session = $url;
   }

   $header = "Content-Type: text/xml";

   if (isset($deflate)) {
      $header .= "\nAccept-Encoding: deflate";
   }

   if (isset($base64)) {
      $args['base64'] = $base64;
   }

   $request = xmlrpc_encode_request($args['method'], $args);
   $context = stream_context_create(array('http' => array('method'  => "POST",
                                                          'header'  => $header,
                                                          'content' => $request)));

   $file = file_get_contents("http://$host/$url_session", false, $context);
   if (!$file) {
      die("+ No response\n");
   }

   if (in_array('Content-Encoding: deflate', $http_response_header)) {
      $lenc = strlen($file);
      echo "+ Compressed response : $lenc\n";
      $file = gzuncompress($file);
      $lend = strlen($file);
      echo "+ Uncompressed response : $lend (".round(100.0*$lenc/$lend)."%)\n";
   }

   $response = xmlrpc_decode($file);
   if (!is_array($response)) {
      echo $file;
      die ("+ Bad response\n");
   }

   if (xmlrpc_is_fault($response)) {
       echo("xmlrpc error(".$response['faultCode']."): ".$response['faultString']."\n");
   } else {
      return $response;
   }
}


/*
* ACTIONS
*/


// Init sessions
$session = login();


/*
* Create 1 ENTITY
*/
$args['session'] = $session;
$args['method']  = "glpi.createObjects";
$args['fields']  = array('Entity' => array(array('name'          => 'WSXML-RPC_Entity_01_TESTING',
                                                 'entities_id'   => 0,
                                                 'completename'  => 'Entity WEBSERVICES TEST',
                                                 'comment'       => 'TEST Entity for webservices.',
                                                 'level'         => 1)));

$entity          = call_glpi($args);
$entity          = $entity['Entity'][0]['id'];

// Reset login after create entity
logout();
$session = login();


/*
* Create 1 USER, 1 GROUP
*/
$args['session'] = $session;
$args['method']  = "glpi.createObjects";
$args['fields']  = array('User'  => array(array('name'         => $glpi_test_user,
                                                'password'     => md5($glpi_test_pass),
                                                'realname'     => 'Xml-Rpc TEST',
                                                'firstname'    => 'Xml-Rpc USER',
                                                'use_mode'     => 0,
                                                'entities_id'  => $entity,
                                                'profiles_id'  => $profile)),

                         'Group' => array(array('name'         => 'WSXML-RPC_Group_01_TESTING',
                                                'comment'      => 'TEST Group for Webservices.',
                                                'entities_id'  => $entity,
                                                'is_recursive' => 1)));

$result          = call_glpi($args);
$user            = $result['User'][0]['id'];
$group           = $result['Group'][0]['id'];


/*
* CREATE 1 COMPUTER and 1 MONITOR
*/
$args['session'] = $session;
$args['method']  = "glpi.createObjects";
$args['fields']  = array('Computer' => array(array('name'        => 'WSXML-RPC_Computer_01_TESTING',
                                                   'serial'      => 'I98GFD-FF98-F0ZFDF8-980',
                                                   'otherserial' => '0000134',
                                                   'entities_id' => $entity,
                                                   'users_id'    => $user,
                                                   'groups_id'   => $group)),

                         'Monitor'  => array(array('name'        => 'WSXML-RPC_Monitor_01_TESTING',
                                                   'serial'      => 'I98GFD-8973987-DE98',
                                                   'otherserial' => '0000190',
                                                   'entities_id' => $entity,
                                                   'users_id'    => $user,
                                                   'groups_id'   => $group)));

$items = call_glpi($args);


/*
* CONNECT Monitor to Computer
*/

$computer         = $items['Computer'][0]['id'];
$monitor          = $items['Monitor'][0]['id'];
$args['session']  = $session;
$args['method']   = "glpi.createObjects";
$args['fields']   = array('Computer_Item' => array(array('items_id'     => $monitor,
                                                         'computers_id' => $computer,
                                                         'itemtype'     => 'Monitor')));

call_glpi($args);


/*
* GET Computer with Monitor (with new session)
*/

logout();
$args['session']        = login();
$args['method']         = "glpi.getObject";
$args['itemtype']       = "Computer";
$args['id']             = $computer;
$args['with_monitor']   = 1;

print_r(call_glpi($args));

logout();
?>