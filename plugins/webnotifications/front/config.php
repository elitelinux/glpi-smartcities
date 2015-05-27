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
            
                        
		echo" <table border='0' width='170px' style='margin-left: auto; margin-right: auto; margin-bottom: 25px; margin-top:30px;'>
					<tr>            
	      			<td><a class='vsubmit' type='submit' onclick=\"window.location.href = 'config.php?opt=ativar';\"> "._x('button','Enable')." </a></td>
	      			<td><a class='vsubmit' type='submit' onclick=\"window.location.href = 'config.php?opt=desativar';\"> ".__('Disable')." </a></td>
					</tr>
				</table>
				
				</div>      
      		";
      		
      echo "
      	<div id=sound class='center here' style='margin-bottom:35px;' >
      		<span style='font-size:16px; margin-bottom:20px;'> Sound Alert:&nbsp;&nbsp; </span> </br><p></p>      		
      		<div style='margin-top:10px;'>
	      		<a class='vsubmit' type='submit' onclick=\"window.location.href = 'config.php?sound=ativar';\"> "._x('button','Enable')." </a>
   	   		&nbsp;&nbsp;&nbsp;&nbsp;
	   	      <a class='vsubmit' type='submit' onclick=\"window.location.href = 'config.php?sound=desativar';\"> ".__('Disable')." </a>
	         </div>				      	      	
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


//enable plugin
if(isset($_REQUEST['opt'])) {

$action = $_REQUEST['opt'];

	if($action == 'ativar') {
	
		$search = "// Print foot for every page";
		$replace = "include('../plugins/webnotifications/front/notifica.php');";
		file_put_contents('../../../inc/html.class.php', str_replace($search, $replace, file_get_contents('../../../inc/html.class.php')));
		
		echo "<div id='config' class='center' style='font-size:18px;'>";
		echo "Plugin  "._x('plugin', 'Enabled')." <br><br><p> </div>";	
	}
	
	
	if($action == 'desativar') {
		
		$search = "include('../plugins/webnotifications/front/notifica.php');";	
		$replace = "// Print foot for every page";
		file_put_contents('../../../inc/html.class.php', str_replace($search, $replace, file_get_contents('../../../inc/html.class.php')));
		
		echo "<div id='config' class='center' style='font-size:18px;'>";
		echo "Plugin  ".__('Disabled')."  <br><br><p></div>";	
	}

}

//enable sound
if(isset($_REQUEST['sound'])) {

		if($_REQUEST['sound'] == 'ativar') {
			
			$query_act = "UPDATE glpi_plugin_webnotifications_config
			SET value = '1'
			WHERE name = 'sound' ";
		
		   $result_act = $DB->query($query_act);
		   
		//echo "<div id='config' class='center' style='font-size:18px;'>";
		//echo "Plugin  "._x('plugin', 'Enabled')." <br><br><p> </div>";						
			}
			
		if($_REQUEST['sound'] == 'desativar') {
			
			$query_act = "UPDATE glpi_plugin_webnotifications_config
			SET value = '0'
			WHERE name = 'sound' ";
		
		   $result_act = $DB->query($query_act);
		   
		//echo "<div id='config' class='center' style='font-size:18px;'>";
		//echo "Plugin  "._x('plugin', 'Enabled')." <br><br><p> </div>";						
			}	   
}	   

echo "<div id='config' class='center'>
		<a class='vsubmit' type='submit' onclick=\"window.location.href = '". $CFG_GLPI['root_doc'] ."/front/plugin.php';\" >  ".__('Back')." </a> 
		</div>";

//Html::footer();
?>