<?php
//
include_once (GLPI_ROOT . "/plugins/custom/inc/install.function.php");
define("CUSTOM_FILES_DIR", GLPI_ROOT."/files/_plugins/custom/");
define("CUSTOM_CSS_PATH", CUSTOM_FILES_DIR."glpi_style.css");

// Init the hooks of the plugins -Needed
function plugin_init_custom() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['config_page']['custom']  = "front/config.php";

   $PLUGIN_HOOKS['submenu_entry']['custom']['options']['tab'] = array(
      'title' => __('Colored Tabs', 'custom'),
      'page'  =>'/plugins/custom/front/tab.php',
      'links' => array(
         'search' => '/plugins/custom/front/tab.php',
         'add'    =>'/plugins/custom/front/tab.form.php'
   ));
   $PLUGIN_HOOKS['submenu_entry']['custom']['options']['defaulttab'] = array(
      'title' => __('Default Tabs', 'custom'),
      'page'  =>'/plugins/custom/front/defaulttab.php',
      'links' => array(
         'search' => '/plugins/custom/front/defaulttab.php',
         'add'    =>'/plugins/custom/front/defaulttab.form.php'
   ));
   $PLUGIN_HOOKS['submenu_entry']['custom']['options']['style'] = array(
      'title' => __('GLPI Style', 'custom'),
      'page'  =>'/plugins/custom/front/style.form.php',
      'links' => array(
         'search' => '/plugins/custom/front/style.form.php',
         'add'    =>'/plugins/custom/front/style.form.php'
   ));

   $PLUGIN_HOOKS['add_javascript']['custom'][]    = 'selector.js.php';

   if (file_exists(CUSTOM_FILES_DIR."glpi_style.css")
      || file_exists(GLPI_DOC_DIR."/_plugins/custom/glpi_style.css")) {
      $PLUGIN_HOOKS['add_css']['custom'][]        = 'custom_style.css.php';
   }
   $PLUGIN_HOOKS['add_css']['custom'][]           = 'style.css';

   $PLUGIN_HOOKS['csrf_compliant']['custom']      = true;

   $PLUGIN_HOOKS['menu_toadd']['custom'] = array('config' => 'PluginCustomConfig');
}


// Get the name and the version of the plugin - Needed
function plugin_version_custom() {
   return array('name'           => "Custom",
                'version'        => "0.85-1.0",
                'author'         => "<a href='mailto:adelaunay@teclib.com'>Alexandre DELAUNAY</a> ".
                  "- <a href='http://www.teclib.com'>Teclib'</a>",
                'homepage'       => "http://www.teclib.com/glpi/plugins/color",
                'minGlpiVersion' => "0.85");
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_custom_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.85','lt') || version_compare(GLPI_VERSION,'0.86','ge')) {
      echo "This plugin requires GLPI 0.85";
      return false;
   } elseif (!extension_loaded("gd")) {
      echo "php-gd is required";
   }
   if (version_compare(PHP_VERSION, '5.3.0', 'lt')) {
      echo "PHP 5.3.0 or higher is required";
      return false;
   }

   return true;
}


// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_custom_check_config($verbose=false) {
   if (true) { // Your configuration check
      return true;
   }
   if ($verbose) {
      echo __('Installed / not configured');
   }
   return false;
}


function plugin_custom_haveRight($module,$right) {
   $matches=array(""  => array("","r","w"), // ne doit pas arriver normalement
                  "r" => array("r","w"),
                  "w" => array("w"),
                  "1" => array("1"),
                  "0" => array("0","1")); // ne doit pas arriver non plus

   if (isset($_SESSION["glpi_plugin_custom_profile"][$module])
       && in_array($_SESSION["glpi_plugin_custom_profile"][$module],$matches[$right])) {
      return true;
   }
   return false;
}
