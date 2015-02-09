<?php

include ('../../../inc/includes.php');

$plugin = new Plugin();
	if ($plugin->isActivated("webnotifications")) {

      Html::header('Web Notification', "", "plugins", "webnotifications");	
      

$file =  '../../../inc/html.class.php'; 

$string = file_get_contents( $file ); 
// poderia ser um string ao invés de file_get_contents().  /(.*).php  js/notify(.*)<

$acha = preg_match('/notifica.php/', $string, $matches );
  
      echo "<div id='config' class='center here'>
      		<br><p>
           <span style='color:blue; font-weight:bold; font-size:13pt;'>".__('Web Notifications Plugin')."</span> <br><br><p>";
                       
      if($acha === 0) {
			echo "<span style='color:red; font-weight:bold; font-size:13pt;'>".__('Status').": ".__('Disabled')."</span>";
				}
		else { 
			echo "<span style='color:green; font-weight:bold; font-size:13pt;'>".__('Status').": "._x('plugin','Enabled')."</span>" ;
				}
            
                        
		echo" <table border='0' width='200px' style='margin-left: auto; margin-right: auto; margin-bottom: 25px; margin-top:30px;'>
				<tr>            
      		<td><a class='vsubmit' type='submit' onclick=\"window.location.href = 'config.php?opt=ativar';\"> "._x('button','Enable')." </a></td>
      		<td><a class='vsubmit' type='submit' onclick=\"window.location.href = 'config.php?opt=desativar';\"> ".__('Disable')." </a></td>
				</tr>
				</table>
				
				</div>      
      		";

      // choose config server or config synchro
      //PluginOcsinventoryngConfig::showMenu();

   } else {
      Html::header(__('Setup'),'',"config","plugins");
      echo "<div class='center'><br><br>";
      echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt='".__s('Warning')."'><br><br>";
      echo "<b>".__('Please activate the plugin', 'webnotifications')."</b></div>";
   }



if(isset($_REQUEST['opt'])) {

$action = $_REQUEST['opt'];

if($action == 'ativar') {

$search = "// Print foot for every page";
$replace = "include('../plugins/webnotifications/front/notifica.php');";
file_put_contents('../../../inc/html.class.php', str_replace($search, $replace, file_get_contents('../../../inc/html.class.php')));

echo "<div id='config' class='center'>";
echo "Plugin  "._x('plugin', 'Enabled')." <br><br><p>
 		</div>";

}


if($action == 'desativar') {
	
$search = "include('../plugins/webnotifications/front/notifica.php');";	
$replace = "// Print foot for every page";
file_put_contents('../../../inc/html.class.php', str_replace($search, $replace, file_get_contents('../../../inc/html.class.php')));

echo "<div id='config' class='center'>";
echo "Plugin  ".__('Disabled')."  <br><br><p>
		</div>";

}

}

echo "<div id='config' class='center'>
		<a class='vsubmit' type='submit' onclick=\"window.location.href = '". $CFG_GLPI['root_doc'] ."/front/plugin.php';\" >  ".__('Back')." </a> 
		</div>";

//Html::footer();
?>