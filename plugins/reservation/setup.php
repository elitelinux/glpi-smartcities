<?php

function plugin_version_reservation() {

  return array(
      'name'           => _n('Réservation', 'Réservation', 2, 'Réservation'),
      'version'        => '1.3.1',
      'author'         => 'Sylvain Allemand',
      'license'        => 'GPLv2+',
      'homepage'       => 'https://forge.indepnet.net/projects/reservation',
      'minGlpiVersion' => '0.85');// For compatibility / no install in version < 0.80

}


//controle des prerequis
function plugin_reservation_check_prerequisites() {

  if (version_compare(GLPI_VERSION,'0.85','lt') || version_compare(GLPI_VERSION,'0.86','gt')) {
    echo "This plugin requires GLPI >= 0.85 and GLPI < 0.86";
    return false;
  }
  return true;
}



//controle de la config
function plugin_reservation_check_config($verbose=false) {
  if (true) { // Your configuration check
    return true;
  }

  if ($verbose) {
    echo 'Installed / not configured';
  }
  return false;
}

//installation du plugin
function plugin_init_reservation() {
  global $PLUGIN_HOOKS;

  $PLUGIN_HOOKS['csrf_compliant']['reservation'] = true;
  $PLUGIN_HOOKS['add_css']['reservation'][]="css/views.css";
  $PLUGIN_HOOKS['add_javascript']['reservation']= array('scripts/tri.js');
  $PLUGIN_HOOKS['config_page']['reservation'] = 'front/config.form.php';
  $PLUGIN_HOOKS['item_update']['reservation'] = array('Reservation' => 'plugin_item_update_reservation');
  $PLUGIN_HOOKS['menu_toadd']['reservation'] = array('plugins' => 'PluginReservationReservation');


  Plugin::registerClass('PluginReservationConfig');
  Plugin::registerClass('PluginReservationReservation');
  Plugin::registerClass('PluginReservationTask');



   // Notifications
   $PLUGIN_HOOKS['item_get_events']['reservation'] =
         array('NotificationTargetReservation' => array('PluginReservationTask', 'addEvents'));


  if (Session::getLoginUserID()) {
    $PLUGIN_HOOKS['menu_entry']['reservation']              = 'front/reservation.php';
  }



}
