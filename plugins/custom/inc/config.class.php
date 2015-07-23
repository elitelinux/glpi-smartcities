<?php
class PluginCustomConfig extends CommonGLPI {
   public static function getTypeName($nb = 0) {
      return __("Custom", 'Custom');
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu['page'] = "/plugins/custom/front/config.php";
      $menu['title'] = self::getTypeName();

      $menu['options']['tab']['page']                      = "/plugins/custom/front/tab.php";
      $menu['options']['tab']['title']                     = __("Color or delete tabs", 'custom');
      $menu['options']['tab']['links']['add']              = PluginCustomTab::getFormURL(false);
      if (PluginCustomTab::canCreate()) {
         $menu['options']['tab']['links']['search']        = PluginCustomTab::getSearchURL(false);
      }

      $menu['options']['defaulttab']['page']               = "/plugins/custom/front/defaulttab.php";
      $menu['options']['defaulttab']['title']              = __("default Tab", 'custom');
      $menu['options']['defaulttab']['links']['add']       = PluginCustomDefaulttab::getFormURL(false);
      if (PluginCustomDefaulttab::canCreate()) {
         $menu['options']['defaulttab']['links']['search'] = PluginCustomDefaulttab::getSearchURL(false);
      }

      $menu['options']['style']['page']                    = "/plugins/custom/front/style.form.php";
      $menu['options']['style']['title']                   = __("Customise GLPI style", 'custom');

      return $menu;
   }

   static function showConfigPage() {
      echo "<div class='custom_center'><ul class='custom_config'>";
      echo "<li onclick='location.href=\"tab.php\"'>
         <img src='../pics/tab_edit.png' />
         <p><a>".__('Color or delete tabs', 'custom')."</a></p></li>";
      echo "<li onclick='location.href=\"defaulttab.php\"'>
         <img src='../pics/tab_default.png' />
         <p><a>".__('Default Tabs', 'custom')."</a></p></li>";
      echo "<li onclick='location.href=\"style.form.php\"'>
         <img src='../pics/palette.png' />
         <p><a>".__('Customise GLPI style', 'custom')."</a></p></li>";
      echo "</ul><div class='custom_clear'></div></div>";
   }

}