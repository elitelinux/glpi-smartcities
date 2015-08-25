<?php
include ('../../../inc/includes.php');

//change mimetype
header("Content-type: application/javascript");

if (!$plugin->isInstalled("talk") 
   || !$plugin->isActivated("talk")
   || !isset($_SESSION['plugin_talk_lasttickets_id'])) {
   exit;
}

$ticket     = new Ticket;
$ticket->getFromDB(intval($_SESSION['plugin_talk_lasttickets_id']));

$talkticket = new PluginTalkTicket;
$tab_title  = $talkticket->getTabNameForItem($ticket);
$tab_url    = $CFG_GLPI['root_doc']."/ajax/common.tabs.php?".
              "_target=/glpi/0.85-git/front/ticket.form.php&_itemtype=Ticket".
              "&_glpi_tab=PluginTalkTicket$1&id=".$ticket->getID();

$JS = <<<JAVASCRIPT

$(document).ready(function() {
   //need a timeout for execute code after tabpanel initialization
   window.setTimeout(function() {

      function getUrlVar(key) {
         var result = new RegExp(key + "=([^&]*)", "i").exec(window.location.search);
         return result && unescape(result[1]) || "";
      }

      //function for insert tab
      this.inserTab = function() {
         var tabpanel          = $('#tabspanel + div.ui-tabs'),
             newtab_html_title = "<li title='$tab_title'><a href='$tab_url'>$tab_title</a></li>";
         
         //insert in second position
         tabpanel.find('ul li').first().after(newtab_html_title);
         tabpanel.tabs("refresh");

         // active talk tab when followup/task/solution tabs was selected
         var activeTabHref = tabpanel.find('.ui-tabs-active').children().attr('href');
         if (activeTabHref.indexOf('TicketFollowup') > 0
             || activeTabHref.indexOf('TicketTask') > 0
             || activeTabHref.indexOf('Ticket\\$2') > 0
             || activeTabHref.indexOf('Document_Item') > 0
             || getUrlVar('load_kb_sol') != '') {
            tabpanel.tabs("option", "active", 1); 
         }
      }

      this.inserTab();

   }, 250)
});

JAVASCRIPT;
echo $JS;