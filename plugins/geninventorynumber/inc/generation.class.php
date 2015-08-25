<?php
/*
 * @version $Id: generation.class.php 79 2013-01-21 08:56:14Z walid $
 LICENSE

 This file is part of the geninventorynumber plugin.

 geninventorynumber plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 geninventorynumber plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with geninventorynumber. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   geninventorynumber
 @author    the geninventorynumber plugin team
 @copyright Copyright (c) 2008-2013 geninventorynumber plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/geninventorynumber
 @link      http://www.glpi-project.org/
 @since     2008
 ---------------------------------------------------------------------- */


class PluginGeninventorynumberGeneration {

   static function autoName($config, CommonDBTM $item) {
   
      $template = $config['template'];
      $len      = strlen($template);
      
      $suffix = strpos($template,'&lt;');

      if ($len > 8
         && $suffix !== FALSE
            && substr($template, $len - 4, 4) === '&gt;') {
   
         $autoNum = substr($template, $suffix+4, $len-(4+$suffix+4));
         $mask    = '';

         if (preg_match("/\\#{1,10}/", $autoNum, $mask)) {
            $serial = (isset ($item->fields['serial']) ? $item->fields['serial'] : '');
            $name   = (isset ($item->fields['name']) ? $item->fields['name'] : '');
            
            $global  = strpos($autoNum, '\\g') !== false && $type != INFOCOM_TYPE ? 1 : 0;
            $autoNum = str_replace(array ('\\y', '\\Y', '\\m', '\\d', '_', '%', '\\g', '\\s', '\\n'),
                                   array (date('y'), date('Y'), date('m'), date('d'), '\\_',
                                           '\\%', '', $serial, $name), $autoNum);
            $mask    = $mask[0];
            $pos     = strpos($autoNum, $mask) + 1;
            $len     = strlen($mask);
            $like    = str_replace('#', '_', $autoNum);

            if ($config['use_index']) {
               $index = PluginGeninventorynumberConfig::getNextIndex();
            } else {
               $index = PluginGeninventorynumberConfigField::getNextIndex($config['itemtype']);
            }
   
            $next_number = str_pad($index, $len, '0', STR_PAD_LEFT);
	    $prefix   = substr($template, 0, $suffix);
            $template    = $prefix . str_replace(array ($mask, '\\_', '\\%'),
                                       array ($next_number,  '_',  '%'),
                                       $autoNum);
         }
      }
      return $template;
   }

   static function preItemAdd(CommonDBTM $item, $massiveaction = false) {

      $config = PluginGeninventorynumberConfigField::getConfigFieldByItemType(get_class($item));

      if (in_array(get_class($item), PluginGeninventorynumberConfigField::getEnabledItemTypes())) {

	 if ((!$massiveaction) && (!Session::haveRight("plugin_geninventorynumber", CREATE))) { 
	    if (!isCommandLine()) {
	       Session::addMessageAfterRedirect(__('GenerateInventoryNumberDenied', 
                                                'geninventorynumber'), true, ERROR);
	    }
	    return array('noright');
         }

	 $tmp    = clone $item;
         $values = array();

	 if (PluginGeninventorynumberConfig::isGenerationActive()
	    && PluginGeninventorynumberConfigField::isActiveForItemType(get_class($item))) {

	    if (!$massiveaction) {
	       $values['otherserial'] = self::autoName($config, $item);
	       if (!isCommandLine()) {
		  Session::addMessageAfterRedirect(__('InventoryNumberGenerated', 'geninventorynumber'), true);
	       }
	    } else {
	       $values['otherserial']   = self::autoName($config, $item);
	       $values['massiveaction'] = true;
               $values['id']            = $item->getID();
	       $tmp->update($values);
	    }

	    if ($config['use_index']) {
	       PluginGeninventorynumberConfig::updateIndex();
	    } else {
	       PluginGeninventorynumberConfigField::updateIndex(get_class($item));
	    }
	    return array('ok');
	 } else {
            $values['otherserial'] = '';
            $values['id']          = $item->getID();
	    $tmp->update($values);
	 }	
      }
   }
      
   static function preItemUpdate(CommonDBTM $item) {

      if (!Session::haveRight("plugin_geninventorynumber", UPDATE)) {
          return array('noright');
      }

      if (PluginGeninventorynumberConfig::isGenerationActive()
	 && PluginGeninventorynumberConfigField::isActiveForItemType(get_class($item))
	 && !isset($item->input['massiveaction'])) {

	 if (isset($item->fields['otherserial'])
	    && isset($item->input['otherserial'])
	    && $item->fields['otherserial'] != $item->input['otherserial']) {

	    $item->input['otherserial'] = $item->fields['otherserial'];
	    if (!isCommandLine()) {
	       Session::addMessageAfterRedirect(__('GenerateInventoryNumberDenied', 'geninventorynumber'),
		  true, ERROR);
	       return array('ko');
	    }
	 }

	 return array('ok');
      }
      return '';
   }

  /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      global $GENINVENTORYNUMBER_TYPES;

      // KK TODO: check if MassiveAction itemtypes are concerned 
      //if (in_array ($options['itemtype'], $GENINVENTORYNUMBER_TYPES)) {
      switch ($ma->action) {
         case "plugin_geninventorynumber_generate" :
         case "plugin_geninventorynumber_overwrite" :
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" .
               _sx('button','Add') . "\" >";
            break;
         default :
            break;
      }
 //  }
      return true; 
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {
      $results = array( 'ok'      => 0,
			'ko'      => 0,
			'noright' => 0,
			'messages' => array());

      switch ($ma->action) {
	 case "plugin_geninventorynumber_generate" :
	 case "plugin_geninventorynumber_overwrite" :
	    //KK Not sure we could have multiple itemtimes
	    foreach ($ma->items as $itemtype => $val) {
	       foreach ($val as $key => $item_id) {
		  $item = new $itemtype;
		  $item->getFromDB($item_id);

		  if ($ma->action == "plugin_geninventorynumber_generate") {

		     //Only generates inventory number for object without it !
		     if (isset ($item->fields["otherserial"])
			&& ($item->fields["otherserial"] == "")) {

			if (!Session::haveRight("plugin_geninventorynumber", CREATE)) {
			   $results['noright']++;
			} else {
			   $myresult = PluginGeninventorynumberGeneration::preItemAdd($item, true);
			   $results[$myresult[0]]++;
			}
		     } else {
			$results['ko']++;
		     }
		  }

		  if (//Or is overwrite action is selected
		     ($ma->action == "plugin_geninventorynumber_overwrite")) {

		     if (!Session::haveRight("plugin_geninventorynumber", UPDATE)) {
			$results['noright']++;
		     } else {
			$myresult = PluginGeninventorynumberGeneration::preItemAdd($item, true);
			$results[$myresult[0]]++;
		     }
		  }
	       }
	    }
	    break;

	 default :
	    break;
      }
      $ma->results=$results;
   }

}
