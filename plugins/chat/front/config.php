<?php

include ('../../../inc/includes.php');

$plugin = new Plugin();
	if ($plugin->isActivated("chat")) {

      Html::header('Chat', "", "plugins", "chat");	


/*
echo '   
<!-- Place this tag where you want the Live Helper Plugin to render. -->
<div id="lhc_status_container_page" style="width: 350px; margin-left:37%;"></div>';

echo "
<!-- Place this tag after the Live Helper Plugin tag. -->
<script type=\"text/javascript\">
var LHCChatOptionsPage = {};
LHCChatOptionsPage.opt = {};
(function() {
var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
po.src = '//10.20.15.116/glpi/lhc/index.php/por/chat/getstatusembed';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
</script> ";   
   
echo "
<script type=\"text/javascript\">
var LHCChatOptions = {};
LHCChatOptions.opt = {widget_height:340,widget_width:300,popup_height:520,popup_width:500};
(function() {
var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
var refferer = (document.referrer) ? encodeURIComponent(document.referrer.substr(document.referrer.indexOf('://')+1)) : '';
var location  = (document.location) ? encodeURIComponent(window.location.href.substring(window.location.protocol.length)) : '';
po.src = '//10.20.15.116/glpi/lhc/index.php/por/chat/getstatus/(click)/internal/(position)/bottom_right/(ma)/br/(top)/350/(units)/pixels/(leaveamessage)/true?r='+refferer+'&l='+location;
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
</script> ";
*/
  
      
$file =  '../../../inc/html.class.php'; 

$string = file_get_contents( $file ); 
// poderia ser um string ao invés de file_get_contents().  /(.*).php  js/notify(.*)<

$acha = preg_match('/chat.php/', $string, $matches );
   
      echo "<div id='config' class='center here'>
      		<br><p>
            <span style='color:blue; font-weight:bold; font-size:13pt;'>".__('Chat Plugin')."</span> <br><br><p>";
            
      if($acha === 0) {
			echo "<span style='color:red; font-weight:bold; font-size:13pt;'>".__('Status').": ".__('Disabled')."</span>";
			echo "<br><p><span>" .__('Before you enable Clone plugin make sure your file <b>inc/html.class.php</b> is owned by apache user, usually www-data or wwwrun','chat')."</span>";
				}
		else { 
			echo "<span style='color:green; font-weight:bold; font-size:13pt;'>".__('Status').": "._x('plugin','Enabled')."</span>" ;}
            
                        
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
      echo "<b>".__('Please activate the plugin', 'chat')."</b></div>";
   }



if(isset($_REQUEST['opt'])) {

$action = $_REQUEST['opt'];

if($action == 'ativar') {

$search = "// Print foot for help page";
$replace = "// Print foot for help page" . PHP_EOL . "include('../plugins/chat/front/chat.php');";
file_put_contents('../../../inc/html.class.php', str_replace($search, $replace, file_get_contents('../../../inc/html.class.php')));

echo "<div id='config' class='center'><h2>";
echo "Plugin  "._x('plugin', 'Enabled')." </h2><br><br><p>
 		</div>";

}


if($action == 'desativar') {
	
$search = "include('../plugins/chat/front/chat.php');";	
$replace = " ";
file_put_contents('../../../inc/html.class.php', str_replace($search, $replace, file_get_contents('../../../inc/html.class.php')));

echo "<div id='config' class='center'><h2>";
echo "Plugin  ".__('Disabled')."  </h2><br><br><p>
		</div>";

}

}

echo "<div id='config' class='center'>
		<a class='vsubmit' type='submit' onclick=\"window.location.href = '". $CFG_GLPI['root_doc'] ."/front/plugin.php';\" >  ".__('Back')." </a> 
		</div>";

//Html::footer();
?>
