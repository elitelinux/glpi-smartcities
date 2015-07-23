<?php

include ("../../inc/includes.php");

//change mimetype
header("Content-type: application/javascript");

if ($plugin->isInstalled("custom") && $plugin->isActivated("custom")) {
   echo "$(function() {
      if ($('#tabspanel').length >= 1 ) {
      ";

      $itemtype = PluginCustomTab::getItemtype();

      /*** Color Tabs ***/
      $query = "SELECT * FROM glpi_plugin_custom_tabs WHERE itemtype = '$itemtype'";
      $res = $DB->query($query);
      while($data = $DB->fetch_array($res)) {

         $color = $data['color'];
         $tab = PluginCustomTab::escapeTabName($data['tab']);

         if ($color != "deleted") {
            echo "$('li[role=tab]:has(a[href*=$tab])').addClass('$color');";
         } else {
            echo "$('li[role=tab]:has(a[href*=$tab])').remove();";
         }
      }


      /*** Default Tabs ***/
      $query = "SELECT * FROM glpi_plugin_custom_defaulttabs WHERE itemtype = '$itemtype'";
      $res = $DB->query($query);
      $data = $DB->fetch_array($res);
      $default_tab = PluginCustomTab::escapeTabName($data['tab']);
      echo "}";


      $path = dirname(dirname(dirname($_SERVER['REQUEST_URI'])));
      $login_locale = __("Login");

      $JS = <<<JAVASCRIPT

      starVIP = function() {
         $('a[id*=tooltiplink] img').each(function (index) {
            var tip = $(this).qtip();
            if (tip && "options" in tip) {
               var ttip_content = tip.options.content.text[0].innerHTML;
               if (ttip_content
                  && ttip_content.indexOf('VIP') > 0
                  && ttip_content.indexOf('$login_locale') > 0
                  && $(this).parent()[0].innerHTML.indexOf('vip.png') == -1) {
                  $(this).after("<img src='$path/plugins/custom/pics/vip.png' alt='VIP' title='VIP' />");
               }
            }
         });
      }

      var is_defaulttab_activated = false;

      // on tab panel load event
      $('#tabspanel + div.ui-tabs').on("tabsload", function( event, ui ) {
         starVIP();

         var default_tab = "{$default_tab}";
         if (default_tab && !is_defaulttab_activated) {
            is_defaulttab_activated = true;
            default_tab = $('li[role=tab]:has(a[href*=$default_tab])');
            var defaul_tab_index = default_tab.attr('aria-controls').replace('ui-tabs-', '') - 1;
            console.log("{$default_tab}",  default_tab.attr('aria-controls'));
            $('#tabspanel + div.ui-tabs').tabs( "option", "active", defaul_tab_index );
         }
      });
   });
JAVASCRIPT;
echo $JS;
}
