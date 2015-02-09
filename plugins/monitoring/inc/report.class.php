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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringReport {

   function __construct(CommonGLPI $obj=NULL) {

      $this->obj = ($obj ? $obj : new Computer());
   }


   static function beginCapture() {
      ob_start();
   }



   static function endCapture() {
      $content = ob_get_contents();
      ob_end_clean();
      return $content;
   }



   static function generatePDF($content, $orientation='P') {

      include(GLPI_ROOT . "/plugins/monitoring/lib/mpdf/mpdf.php");
      $format = 'A4';
      if ($orientation == 'L') {
         $format .= '-L';
      }
      $pdf=new mPDF('c', $format, '', '', 5, 5, 10, 10, 2, 2, $orientation);
      $pdf->mirrorMargins = true;
      $pdf->SetDisplayMode('fullpage');

      $pdf->showImageErrors = true;

      $css = file_get_contents(GLPI_ROOT.'/plugins/monitoring/css/pdf.css');
      $pdf->WriteHTML($css,1);

      $content = "<body>".$content."</body>";
      $pdf->WriteHTML($content, 2);
      $pdf->output();
//      $out = $pdf->output(GLPI_PLUGIN_DOC_DIR.'/monitoring/example_001.pdf', 'F');
      exit;
   }

}

?>
