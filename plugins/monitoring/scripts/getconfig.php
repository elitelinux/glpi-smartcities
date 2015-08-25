<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2014 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Monitoring. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author
   @comment
   @copyright Copyright (c) 2011-2014 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2011

   ------------------------------------------------------------------------
 */

if (!extension_loaded("xmlrpc")) {
   die("Extension xmlrpc not loaded\n");
}

/*
* SETTINGS
*/
chdir(dirname($_SERVER["SCRIPT_FILENAME"]));
chdir("../../..");
$url = "/" . basename(getcwd()) . "/plugins/webservices/xmlrpc.php";

$url = "/glpi085/plugins/webservices/xmlrpc.php";
$host = 'localhost';
$glpi_user  = "glpi";
$glpi_pass  = "glpi";

$method = "monitoring.shinkenGetConffiles";
$file = "all";

/*
* PARAMETERS
*/

// $argv[0] is full script name
$options = getopt("v::t:");
// var_dump($options);

$verbose = isset($options['v']) ? true : false;
echo '+ Use command line parameter -v to set verbose mode: '. $verbose ."\n";

$tags = '';
if (isset($options['t'])) {
   $tags = $options['t'];
}
echo '+ Use command line parameter -t "tags" to set Shinken tags: '. $tags ."\n";



/*
* LOGIN
*/
function login() {
   global $glpi_user, $glpi_pass, $ws_user, $ws_pass;

    $args['method']          = "glpi.doLogin";
    $args['login_name']      = $glpi_user;
    $args['login_password']  = $glpi_pass;

    if (isset($ws_user)){
       $args['username'] = $ws_user;
    }

    if (isset($ws_pass)){
       $args['password'] = $ws_pass;
    }

    if($result = call_glpi($args)) {
       return $result['session'];
    }
}

/*
* LOGOUT
*/
function logout() {
    $args['method'] = "glpi.doLogout";

    if($result = call_glpi($args)) {
       return true;
    }
}

/*
* GENERIC CALL
*/
function call_glpi($args) {
   global $host,$url,$deflate,$base64;

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


   $request = xmlrpc_encode_request($args['method'], $args);
   $context = stream_context_create(array('http' => array('method'  => "POST",
                                                          'header'  => $header,
                                                          'content' => $request,
                                                          'timeout' => 500)));

   $file = file_get_contents("http://$host/$url_session", false, $context);
   if (!$file) {
      die("+ No response\n");
   }

   if (in_array('Content-Encoding: deflate', $http_response_header)) {
      $lenc=strlen($file);
      echo "+ Compressed response : $lenc\n";
      $file = gzuncompress($file);
      $lend=strlen($file);
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

$args['session'] = $session;
$args['method'] = $method;
$args['file'] = $file;
$args['tags'] = $tags;

$tags = explode(',', $args['tags']);
if (count($tags) > 1) {
   foreach ($tags as $tag) {
      $tag = trim($tag);
      $args['tag'] = $tag;
      $configfiles = call_glpi($args);
      foreach ($configfiles as $filename=>$filecontent) {
         $filename = "plugins/monitoring/scripts/".$tag."-".$filename;
         $handle = fopen($filename,"w+");
         if (is_writable($filename)) {
             if (fwrite($handle, $filecontent) === FALSE) {
               echo "Impossible to write file ".$filename."\n";
             }
             echo "File ".$filename." writen successful\n";
             fclose($handle);
         }
      }
   }
} else {
   $args['tag'] = $args['tags'];
   $configfiles = call_glpi($args);
   foreach ($configfiles as $filename=>$filecontent) {
      $filename = "plugins/monitoring/scripts/".$filename;
      $handle = fopen($filename,"w+");
      if (is_writable($filename)) {
          if (fwrite($handle, $filecontent) === FALSE) {
            echo "Impossible to write file ".$filename."\n";
          }
          echo "File ".$filename." writen successful\n";
          fclose($handle);
      }
   }
}

logout();
?>
