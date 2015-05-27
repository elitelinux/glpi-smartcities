<?php
/*
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMobileStat extends Stat {
   
   static function getItems($date1,$date2,$type) {
      global $CFG_GLPI,$DB;

      $val=array();

      switch ($type) {
         case "technicien" :
            $val = PluginMobileTicket::getUsedTechBetween($date1,$date2);
            break;

         case "technicien_followup" :
            $val = PluginMobileTicket::getUsedTechFollowupBetween($date1,$date2);
            break;

         case "enterprise" :
            $val = PluginMobileTicket::getUsedSupplierBetween($date1,$date2);
            break;

         case "user" :
            $val = PluginMobileTicket::getUsedAuthorBetween($date1,$date2);
            break;

         case "users_id_recipient" :
            $val = PluginMobileTicket::getUsedRecipientBetween($date1,$date2);
            break;

         case "ticketcategories_id" :
           
            // Get all ticket categories for tree merge management
            $query = "SELECT DISTINCT `glpi_itilcategories`.`id`,
                           `glpi_itilcategories`.`completename` AS category
                     FROM `glpi_itilcategories`
                     ".getEntitiesRestrictRequest(" WHERE", "glpi_itilcategories", '', '', true)."
                     ORDER BY category";

            $result = $DB->query($query);
            $val=array();
            if ($DB->numrows($result) >=1) {
               while ($line = $DB->fetch_assoc($result)) {
                  $tmp['id']= $line["id"];
                  $tmp['link']=$line["category"];
                  $val[]=$tmp;
               }
            }
            break;

         case "group" :
            $val = Ticket::getUsedGroupBetween($date1,$date2);
            break;

         case "groups_id_assign" :
            $val = Ticket::getUsedAssignGroupBetween($date1,$date2);
            break;

         case "priority" :
            $val = Ticket::getUsedPriorityBetween($date1,$date2);
            break;

         case "urgency" :
            $val = Ticket::getUsedUrgencyBetween($date1,$date2);
            break;

         case "impact" :
            $val = Ticket::getUsedImpactBetween($date1,$date2);
            break;

         case "requesttypes_id" :
            $val = Ticket::getUsedRequestTypeBetween($date1,$date2);
            break;

         case "ticketsolutiontypes_id" :
            $val = Ticket::getUsedSolutionTypeBetween($date1,$date2);
            break;

         case "usertitles_id" :
            $val = Ticket::getUsedUserTitleOrTypeBetween($date1,$date2,true);
            break;

         case "usercategories_id" :
            $val = Ticket::getUsedUserTitleOrTypeBetween($date1,$date2,false);
            break;

         // DEVICE CASE
         default :
            $item = new $type();
            if ($item instanceof CommonDevice) {
               $device_table = $item->getTable();

               //select devices IDs (table row)
               $query = "SELECT `id`, `designation`
                        FROM `".$device_table."`
                        ORDER BY `designation`";
               $result = $DB->query($query);

               if ($DB->numrows($result) >=1) {
                  $i = 0;
                  while ($line = $DB->fetch_assoc($result)) {
                     $val[$i]['id'] = $line['id'];
                     $val[$i]['link'] = $line['designation'];
                     $i++;
                  }
               }
            } else {
               //echo $type;
               // Dropdown case for computers
               $field = "name";
               $table=getTableFOrItemType($type);
               $item = new $type();
               if ($item instanceof CommonTreeDropdown) {
                  $field="completename";
               }
               $where = '';
               $order = " ORDER BY `$field`";
               if ($item->isEntityAssign()) {
                  $where = getEntitiesRestrictRequest(" WHERE",$table);
                  $order = " ORDER BY `entities_id`, `$field`";
               }

               $query = "SELECT *
                        FROM `$table`
                        $where
                        $order";

               $val=array();
               $result = $DB->query($query);
               if ($DB->numrows($result) >0) {
                  while ($line = $DB->fetch_assoc($result)) {
                     $tmp['id']= $line["id"];
                     $tmp['link']=$line[$field];
                     $val[]=$tmp;
                  }
               }
            }
      }
      return $val;
   }

   //static function show($itemtype,$type,$date1,$date2,$start,$value,$value2="") {
   	function show($itemtype, $type, $date1, $date2, $start, array $value, $value2="") {
      global $LANG,$CFG_GLPI;
      
      // Set display type for export if define
      
      $output_type=Search::HTML_OUTPUT;
      if (isset($_GET["display_type"])) {
         $output_type=$_GET["display_type"];
      }
      
      //printCleanArray($value);

      if (is_array($value)) {
         $end_display=$start+$_SESSION['plugin_mobile']['rows_limit'];
         $numrows=count($value);
         if (isset($_GET['export_all'])) {
            $start=0;
            $end_display=$numrows;
         }
         $nbcols=8;
         if ($output_type!=Search::HTML_OUTPUT) { // not HTML display
         $nbcols--;
         
         }
         echo PluginMobileSearch::showHeader($output_type,$end_display-$start+1,$nbcols);
         
         Search::showNewLine($output_type);
         
         $header_num=1;
         if ($output_type==Search::HTML_OUTPUT) { // HTML display - massive modif
            $search_config="";
            echo PluginMobileSearch::showHeaderItem($output_type,$search_config,$header_num,"",0,array());
         }
         
         echo "<div data-type='horizontal' data-role='controlgroup' class='mobile_list_header'>";
               
         $header_num=1;
         echo PluginMobileSearch::showHeaderItem($output_type,"&nbsp;",$header_num, '#', 0, '', 3);
         
         /*if ($output_type==Search::HTML_OUTPUT) { // HTML display
            echo PluginMobileSearch::showHeaderItem($output_type,"&nbsp;",$header_num, '#', 0, '', 7);
         }*/
 
 //stevenes         
         echo PluginMobileSearch::showHeaderItem($output_type,$LANG['stats'][13],$header_num, '#', 0, '', 3);
         echo PluginMobileSearch::showHeaderItem($output_type,$LANG['stats'][11],$header_num, '#', 0, '', 3);
       //  echo PluginMobileSearch::showHeaderItem($output_type,$LANG['stats'][11],$header_num, '#', 0, '', 7);
       /*  echo PluginMobileSearch::showHeaderItem($output_type,$LANG['stats'][15],$header_num, '#', 0, '', 7);
         echo PluginMobileSearch::showHeaderItem($output_type,$LANG['stats'][25],$header_num, '#', 0, '', 7);
         echo PluginMobileSearch::showHeaderItem($output_type,$LANG['stats'][27],$header_num, '#', 0, '', 7);
         echo PluginMobileSearch::showHeaderItem($output_type,$LANG['stats'][30],$header_num, '#', 0, '', 7);
		*/         
         echo "</div>";
         
         // End Line for column headers
         echo PluginMobileSearch::showEndLine($output_type);

$itemtype = "Ticket";   
//echo $start;      
//echo $value[0]['id'];
//echo $value[0]['link'];
         $row_num=1;
         for ($i=$start ; $i< $numrows && $i<($end_display) ; $i++) {            
            $value[$i]['link'] = preg_replace('#<a.*>(.*)</a>#isU', '$1', $value[$i]['link']);   
            $value[$i]['link'] = "<a href='stat.graph.php?id=".$value[$i+1]['id']."&amp;date1=$date1&amp;date2=".
                        "$date2&amp;type=$type".(!empty($value2)?"&amp;champ=$value2":"")."'>".$value[$i]['link']."</a>";
         
            $row_num++;
            $item_num=1;
            echo PluginMobileSearch::showNewLine($output_type,$i%2);
            
            echo PluginMobileSearch::showItem($output_type,$value[$i]['link'],$item_num,$row_num, '', 3);
     
            //echo PluginMobileSearch::showItem($output_type,$link,$item_num,$row_num, '', 7);
            /*if ($output_type==Search::HTML_OUTPUT) { // HTML display
               $link="";
               if ($value[$i]['id']>0) {
                  $link="<a href='stat.graph.php?id=".$value[$i]['id']."&amp;date1=$date1&amp;date2=".
                        "$date2&amp;type=$type".(!empty($value2)?"&amp;champ=$value2":"")."'>".
                        "<img src=\"".$CFG_GLPI["root_doc"]."/pics/stats_item.png\" alt='' title=''>".
                        "</a>";
               }
               echo PluginMobileSearch::showItem($output_type,$link,$item_num,$row_num, '', 7);
            }*/
            //le nombre d'intervention - the number of intervention
                        
            $opened=Stat::constructEntryValues($itemtype,"inter_total",$date1,$date2,$type,$value[$i]["id"],$value2);
            $nb_opened=array_sum($opened);
            echo PluginMobileSearch::showItem($output_type,$nb_opened,$item_num,$row_num, '', 3);
            $export_data['opened'][$value[$i]['link']]=$nb_opened;

            //le nombre d'intervention resolues - the number of resolved intervention
                        
            $solved=Stat::constructEntryValues($itemtype,"inter_solved",$date1,$date2,$type,$value[$i]["id"],$value2);
            $nb_solved=array_sum($solved);
            echo PluginMobileSearch::showItem($output_type,$nb_solved,$item_num,$row_num, '', 3);
            $export_data['solved'][$value[$i]['link']]=$nb_solved;

//dados chamados
            //Le temps moyen de resolution - The average time to resolv
  /*          
            $data=Stat::constructEntryValues($itemtype,"inter_avgsolvedtime",$date1,$date2,$type,$value[$i]["id"],$value2);
            foreach ($data as $key2 => $val2) {
               $data[$key2]*=$solved[$key2];
            }
            if ($nb_solved>0) {
               $nb=array_sum($data)/$nb_solved;
            } else {
               $nb=0;
            }
            $timedisplay = $nb*HOUR_TIMESTAMP;
            if ($output_type==Search::HTML_OUTPUT
               || $output_type==Search::PDF_OUTPUT_LANDSCAPE
               || $output_type==Search::PDF_OUTPUT_PORTRAIT) {
               $timedisplay=mobileTimestampToString($timedisplay,0);
            }
            echo PluginMobileSearch::showItem($output_type,$timedisplay,$item_num,$row_num, '', 7);

            //Le temps moyen de l'intervention reelle - The average realtime to resolv
            
            $data=Stat::constructEntryValues($itemtype,"inter_avgrealtime",$date1,$date2,$type,$value[$i]["id"],$value2);
            foreach ($data as $key2 => $val2) {
               if (isset($solved[$key2])) {
                  $data[$key2]*=$solved[$key2];
               } else {
                  $data[$key2]*=0;
               }
            }
            $total_realtime=array_sum($data);
            if ($nb_solved>0) {
               $nb=$total_realtime/$nb_solved;
            } else {
               $nb=0;
            }
            $timedisplay=$nb*MINUTE_TIMESTAMP;
            if ($output_type==Search::HTML_OUTPUT || $output_type==Search::PDF_OUTPUT_LANDSCAPE
               || $output_type==Search::PDF_OUTPUT_PORTRAIT) {
               $timedisplay=mobileTimestampToString($timedisplay,0);
            }
            echo PluginMobileSearch::showItem($output_type,$timedisplay,$item_num,$row_num, '', 7);
            //Le temps total de l'intervention reelle - The total realtime to resolv
            $timedisplay=$total_realtime*MINUTE_TIMESTAMP;
            if ($output_type==Search::HTML_OUTPUT
               || $output_type==Search::PDF_OUTPUT_LANDSCAPE
               || $output_type==Search::PDF_OUTPUT_PORTRAIT) {
               $timedisplay=mobileTimestampToString($timedisplay,0);
            }
            echo PluginMobileSearch::showItem($output_type,$timedisplay,$item_num,$row_num, '', 7);
            //Le temps moyen de prise en compte du ticket - The average time to take a ticket into account
            $data=Stat::constructEntryValues($itemtype,"inter_avgtakeaccount",$date1,$date2,$type,$value[$i]["id"],
                                       $value2);
            foreach ($data as $key2 => $val2) {
               $data[$key2]*=$solved[$key2];
            }
            if ($nb_solved>0) {
               $nb=array_sum($data)/$nb_solved;
            } else {
               $nb=0;
            }
            $timedisplay=$nb*HOUR_TIMESTAMP;
            if ($output_type==Search::HTML_OUTPUT
               || $output_type==Search::PDF_OUTPUT_LANDSCAPE
               || $output_type==Search::PDF_OUTPUT_PORTRAIT) {
               $timedisplay=mobileTimestampToString($timedisplay,0);
            }
            echo PluginMobileSearch::showItem($output_type,$timedisplay,$item_num,$row_num, '', 7);
            echo PluginMobileSearch::showEndLine($output_type);
         } */ 
         echo PluginMobileSearch::showEndLine($output_type);
         }
         // Display footer
         echo PluginMobileSearch::showFooter($output_type);
      } else {
         echo $LANG['stats'][23];
      }
   }


   /** Get groups assigned to tickets between 2 dates
   * BASED ON SPIP DISPLAY GRAPH : www.spip.net
   * @param $type string : "month" or "year"
   * @param $entrees array : array containing data to displayed
   * @param $titre string : title
   * @param $unit string : unit
   * @param $showtotal boolean : also show total values ?
   * @return array contains the distinct groups assigned to a tickets
   */
   static function graphBy($entrees,$titre="",$unit="",$showtotal=1,$type="month") {
      global $DB,$CFG_GLPI,$LANG;

      $total="";
      if ($showtotal==1) {
         $total=array_sum($entrees);
      }

      echo "<p class='center'>";
      echo "<font face='verdana,arial,helvetica,sans-serif' size='2'>";
      echo "<strong>$titre - $total $unit</strong></font>";
      echo "<div class='center'>";

      if (count($entrees)>0) {
         $max = max($entrees);
         $maxgraph = substr(ceil(substr($max,0,2) / 10)."000000000000", 0, strlen($max));

         if ($maxgraph < 10) {
            $maxgraph = 10;
         }
         if (1.1 * $maxgraph < $max) {
            $maxgraph.="0";
         }
         if (0.8*$maxgraph > $max) {
            $maxgraph = 0.8 * $maxgraph;
         }
         $rapport = 200 / $maxgraph;

         $largeur = floor(420 / (count($entrees)));
         if ($largeur < 1) {
            $largeur = 1;
         }
         if ($largeur > 50) {
            $largeur = 50;
         }
      }

      echo "<table class='tab_glpi'><tr>";
      echo "<td style='background-image:url(".$CFG_GLPI["root_doc"]."/pics/fond-stats.gif)' >";
      echo "<table><tr><td bgcolor='black'>";
      echo "<img src='".$CFG_GLPI["root_doc"]."/pics/noir.png' width='1' height='200' alt=''></td>";

      // Presentation graphique
      $n = 0;
      $decal = 0;
      $tab_moyenne = "";
      $total_loc = 0;
      while (list($key, $value) = each($entrees)) {
         $n++;
         if ($decal == 30) {
            $decal = 0;
         }
         $decal ++;
         $tab_moyenne[$decal] = $value;

         $total_loc = $total_loc + $value;
         reset($tab_moyenne);

         $moyenne = 0;
         while (list(,$val_tab) = each($tab_moyenne)) {
            $moyenne += $val_tab;
         }
         $moyenne = $moyenne / count($tab_moyenne);

         $hauteur_moyenne = round($moyenne * $rapport) ;
         $hauteur = round($value * $rapport)	;
         echo "<td class='bottom' width=".$largeur.">";

         if ($hauteur >= 0) {
            if ($hauteur_moyenne > $hauteur) {
               $difference = ($hauteur_moyenne - $hauteur) -1;
               echo "<img alt=\"$key: $value\" title=\"$key: $value\" src='".$CFG_GLPI["root_doc"].
                     "/pics/moyenne.png' width=".$largeur." height='1' >";
               echo "<img alt=\"$key: $value\" title=\"$key: $value\" src='".$CFG_GLPI["root_doc"].
                     "/pics/rien.gif' width=".$largeur." height=".$difference." >";
               echo "<img alt=\"$key: $value\" title=\"$key: $value\" src='".$CFG_GLPI["root_doc"].
                     "/pics/noir.png' width=".$largeur." height='1' >";
               if (strstr($key,"-01")) { // janvier en couleur foncee
                  echo "<img alt=\"$key: $value\" title=\"$key: $value\" src='".$CFG_GLPI["root_doc"].
                        "/pics/fondgraph1.png' width=".$largeur." height=".$hauteur." >";
               } else {
                  echo "<img alt=\"$key: $value\" title=\"$key: $value\" src='".$CFG_GLPI["root_doc"].
                        "/pics/fondgraph2.png' width=".$largeur." height=".$hauteur." >";
               }
            } else if ($hauteur_moyenne < $hauteur) {
               $difference = ($hauteur - $hauteur_moyenne) -1;
               echo "<img alt=\"$key: $value\" title=\"$key: $value\" src='".$CFG_GLPI["root_doc"].
                     "/pics/noir.png' width=".$largeur." height='1'>";
               if (strstr($key,"-01")) { // janvier en couleur foncee
                  $couleur = "1";
                  $couleur2 = "2";
               } else {
                  $couleur = "2";
                  $couleur2 = "1";
               }
               echo "<img alt=\"$key: $value\" title=\"$key: $value\" src='".$CFG_GLPI["root_doc"].
                     "/pics/fondgraph$couleur.png' width=".$largeur." height=".$difference.">";
               echo "<img alt=\"$key: $value\" title=\"$key: $value\" src='".$CFG_GLPI["root_doc"].
                     "/pics/moyenne.png' width=".$largeur." height='1'>";
               echo "<img alt=\"$key: $value\" title=\"$key: $value\" src='".$CFG_GLPI["root_doc"].
                     "/pics/fondgraph$couleur.png' width=".$largeur." height=".$hauteur_moyenne.">";
            } else {
               echo "<img alt=\"$key: $value\" title=\"$key: $value\" src='".$CFG_GLPI["root_doc"].
                     "/pics/noir.png' width=".$largeur." height='1'>";
               if (strstr($key,"-01")) { // janvier en couleur foncee
                  echo "<img alt=\"$key: $val_tab\" title=\"$key: $value\" src='".$CFG_GLPI["root_doc"].
                        "/pics/fondgraph1.png' width=".$largeur." height=".$hauteur.">";
               } else {
                  echo "<img alt=\"$key: $value\" title=\"$key: $value\" src='".$CFG_GLPI["root_doc"].
                        "/pics/fondgraph2.png' width=".$largeur." height=".$hauteur.">";
               }
            }
         }
         echo "<img alt=\"$value\" title=\"$value\" src='".$CFG_GLPI["root_doc"].
               "/pics/rien.gif' width=".$largeur." height='1'>";
         echo "</td>\n";
      }
      echo "<td bgcolor='black'>";
      echo "<img src='".$CFG_GLPI["root_doc"]."/pics/noir.png' width='1' height='1' alt=''></td></tr>";
      if ($largeur>10) {
         echo "<tr><td></td>";
         foreach ($entrees as $key => $val) {
            if ($type=="month") {
               $splitter=explode("-",$key);
               echo "<td class='center'>".utf8_substr($LANG['calendarM'][$splitter[1]-1],0,3)."</td>";
            } else if ($type=="year") {
               echo "<td class='center'>".substr($key,2,2)."</td>";
            }
         }
         echo "</tr>";
      }

      if ($maxgraph<=10) {
         $r=2;
      } else if ($maxgraph<=100) {
         $r=1;
      } else {
         $r=0;
      }
      echo "</table>";
      echo "</td>";
      echo "<td style='background-image:url(".$CFG_GLPI["root_doc"]."/pics/fond-stats.gif)' class='bottom'>";
      echo "<img src='".$CFG_GLPI["root_doc"]."/pics/rien.gif' style='background-color:black;' ".
            "width='3' height='1' alt=''></td>";
      echo "<td><img src='".$CFG_GLPI["root_doc"]."/pics/rien.gif' width='5' height='1' alt=''></td>";
      echo "<td class='top'>";
      echo "<table>";
      echo "<tr><td height='15' class='top'>";
      echo "<font face='arial,helvetica,sans-serif' size='1' class ='b'>".
            formatNumber($maxgraph,false,$r)."</font></td></tr>";
      echo "<tr><td height='25' class='middle'>";
      echo "<font face='arial,helvetica,sans-serif' size='1' color='#999999'>".
            formatNumber(7*($maxgraph/8),false,$r)."</font></td></tr>";
      echo "<tr><td height='25' class='middle'>";
      echo "<font face='arial,helvetica,sans-serif' size='1'>".formatNumber(3*($maxgraph/4),false,$r);
      echo "</font></td></tr>";
      echo "<tr><td height='25' class='middle'>";
      echo "<font face='arial,helvetica,sans-serif' size='1' color='#999999'>".
            formatNumber(5*($maxgraph/8),false,$r)."</font></td></tr>";
      echo "<tr><td height='25' class='middle'>";
      echo "<font face='arial,helvetica,sans-serif' size='1' class ='b'>".
            formatNumber($maxgraph/2,false,$r)."</font></td></tr>";
      echo "<tr><td height='25' class='middle'>";
      echo "<font face='arial,helvetica,sans-serif' size='1' color='#999999'>".
            formatNumber(3*($maxgraph/8),false,$r)."</font></td></tr>";
      echo "<tr><td height='25' class='middle'>";
      echo "<font face='arial,helvetica,sans-serif' size='1'>".formatNumber($maxgraph/4,false,$r);
      echo "</font></td></tr>";
      echo "<tr><td height='25' class='middle'>";
      echo "<font face='arial,helvetica,sans-serif' size='1' color='#999999'>".
            formatNumber(1*($maxgraph/8),false,$r)."</font></td></tr>";
      echo "<tr><td height='10' class='bottom'>";
      echo "<font face='arial,helvetica,sans-serif' size='1' class='b'>0</font></td></tr>";

      echo "</table>";
      echo "</td></tr></table>";
      echo "</div>";
   }


   /** Get groups assigned to tickets between 2 dates
   * @param $entrees array : array containing data to displayed
   * @param $options array : options
   *     - title string title displayed (default empty)
   *     - showtotal boolean show total in title (default false)
   *     - width integer width of the graph (default 700)
   *     - height integer height of the graph (default 300)
   *     - unit integer height of the graph (default empty)
   *     - type integer height of the graph (default line) : line bar pie
   *     - csv boolean export to CSV (default true)
   * @return array contains the distinct groups assigned to a tickets
   */
    
   
   static function showGraph(array $entrees,$options=array()) {
      global $CFG_GLPI,$LANG;           
    
    //stevenes donato  
    
$ScreenWidth = "undefined";
if(!isset($_GET['screen_check']) && !isset($_GET['date1'])){
	echo '
		<script>
		document.location="'.$_SERVER["REQUEST_URI"].'?screen_check=done&Width="+screen.width+"&Height="+screen.height;
		</script>';
	exit;
}

if(!isset($_GET['screen_check']) && isset($_GET['date1'])){
	echo '
		<script>
		document.location="'.$_SERVER["REQUEST_URI"].'&screen_check=done&Width="+screen.width+"&Height="+screen.height;
		</script>';
	exit;
}



if(isset($_GET['Width'])){
	$ScreenWidth = $_GET['Width'];
} else {
	$ScreenWidth = 400;
}
//echo "Screen width = $ScreenWidth";  $CFG_GLPI["root_doc"]

// stevenes

      if ($uid=Session::getLoginUserID(false)) {
         if (!isset($_SESSION['glpigraphtype'])) {
            $_SESSION['glpigraphtype']=$CFG_GLPI['default_graphtype'];
         }

         $param['showtotal']  = false;
         $param['title']      = '';
    //     $param['width']      = $_SESSION['plugin_mobile']['screen_width'] - 50;  
    //     $param['width']      = $ScreenWidth - 50;
         $param['width']      = $ScreenWidth - 50;
         $param['height']     = 200;
         $param['unit']       = '';
         $param['type']       = 'line';
         $param['csv']        = true;

         if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
               $param[$key]=$val;
            }
         }

         // Clean data
         if (is_array($entrees) && count($entrees)) {
            foreach ($entrees as $key => $val) {
               if (!is_array($val) || count($val)==0) {
                  unset($entrees[$key]);
               }
            }
         }

         if (!is_array($entrees) || count($entrees) == 0) {
            if (!empty($param['title'])) {
               echo "<h3>".$param['title']." : </h3>".$LANG['stats'][2]."<br />";
            }
            return false;
         }


         switch ($param['type']) {
            case 'pie':
               // Check datas : sum must be > 0
               reset($entrees);
               $sum=array_sum(current($entrees));
               while ($sum==0 && $data=next($entrees)) {
                  $sum+=array_sum($data);
               }
               if ($sum==0) {
                  return false;
               }
               $graph = new ezcGraphPieChart();
               $graph->palette = new GraphPalette();
               $graph->options->font->maxFontSize = 15;
               $graph->title->background = '#EEEEEC';
               $graph->renderer = new ezcGraphRenderer3d();
               $graph->renderer->options->pieChartHeight = 20;
               $graph->renderer->options->moveOut = .2;
               $graph->renderer->options->pieChartOffset = 63;
               $graph->renderer->options->pieChartGleam = .3;
               $graph->renderer->options->pieChartGleamColor = '#FFFFFF';
               $graph->renderer->options->pieChartGleamBorder = 2;
               $graph->renderer->options->pieChartShadowSize = 5;
               $graph->renderer->options->pieChartShadowColor = '#BABDB6';

               break;
            case 'bar':
               $graph = new ezcGraphBarChart();
               $graph->options->fillLines = 210;
               $graph->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedBoxedLabelRenderer();
               $graph->xAxis->axisLabelRenderer->angle = 45;
               $graph->xAxis->axisSpace = .2;
               $graph->yAxis->min = 0;
               $graph->palette = new GraphPalette();
               $graph->options->font->maxFontSize = 15;
               $graph->title->background = '#EEEEEC';
               $graph->renderer = new ezcGraphRenderer3d();
               $graph->renderer->options->legendSymbolGleam = .5;
               $graph->renderer->options->barChartGleam = .5;

               $max = 0;
               foreach ($entrees as $key => $val) {
                  if (count($val) > $max) {
                     $max = count($val);
                  }
               }
               $graph->xAxis->labelCount = $max;

               break;
            case 'line':
               // No break default case
            default :
               $graph = new ezcGraphLineChart();
               $graph->options->fillLines = 210;
               $graph->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedLabelRenderer();
               $graph->xAxis->axisLabelRenderer->angle = 45;
               $graph->xAxis->axisSpace = .2;
               $graph->yAxis->min = 0;
               $graph->palette = new GraphPalette();
               $graph->options->font->maxFontSize = 15;
               $graph->title->background = '#EEEEEC';
               $graph->renderer = new ezcGraphRenderer3d();
               $graph->renderer->options->legendSymbolGleam = .5;
               $graph->renderer->options->barChartGleam = .5;
               $graph->renderer->options->depth = 0.07;
               break;
         }


         if (!empty($param['title'])) {
            $pretoadd="";
            $posttoadd="";
            if (!empty($param['unit'])) {
               $posttoadd = " ".$param['unit'];
               $pretoadd = " - ";
            }
            // Add to title
            if (count($entrees)==1) {
               $param['title'] .= $pretoadd;
               if ($param['showtotal']==1) {
                  reset($entrees);
                  $param['title'] .= array_sum(current($entrees));
               }
               $param['title'] .= $posttoadd;
            } else { // add sum to legend and unit to title
               $param['title'] .=$pretoadd.$posttoadd;
               if ($param['showtotal']==1) {
                  $entree_tmp=$entrees;
                  $entrees=array();
                  foreach ($entree_tmp as $key => $data) {
                     $entrees[$key." (".array_sum($data).")"]=$data;
                  }
               }
            }

            //$graph->title = $param['title'];
            echo "<h3>".$param['title']."</h3>";
         }

         if (count($entrees)==1) {
            $graph->legend = false;
         }
         
         $graphtype = $_SESSION['glpigraphtype'];
         if (in_array(navigatorDetect(), array('Android'))) $graphtype = 'png';
         
         switch ($graphtype) {
            case "png" :
               $extension="png";
               $graph->driver = new ezcGraphGdDriver();
               $graph->options->font = GLPI_FONT_FREESANS;
               break;

            default:
               $extension="svg";
               break;
         }

         $filename=$uid.'_'.mt_rand();
         $csvfilename=$filename.'.csv';
         $filename.='.'.$extension;
         foreach ($entrees as $label => $data) {
            $graph->data[$label] = new ezcGraphArrayDataSet( $data );
            $graph->data[$label]->symbol = ezcGraph::NO_SYMBOL;
         }

         switch ($graphtype) {
            case "png" :
               $graph->render( $param['width'], $param['height'], GLPI_GRAPH_DIR.'/'.$filename );
               echo "<img src='".$CFG_GLPI['root_doc']."/front/graph.send.php?file=$filename'>";
               break;
            default:
               $graph->render( $param['width'], $param['height'], GLPI_GRAPH_DIR.'/'.$filename );
               echo "<object data='".$CFG_GLPI['root_doc']."/front/graph.send.php?file=$filename'
                     type='image/svg+xml' width='".$param['width']."' height='".$param['height']."'>
                     <param name='src' value='".$CFG_GLPI['root_doc']."/front/graph.send.php?file=$filename'>
                     You need a browser capeable of SVG to display this image.
                     </object> ";

            break;
         }
         // Render CSV
         
         if ($param['csv']) {
            if ($fp = fopen(GLPI_GRAPH_DIR.'/'.$csvfilename, 'w')) {
  
               // reformat datas
               
               $values=array();
               $labels=array();
               $row_num=0;
               foreach ($entrees as $label => $data) {
                  $labels[$row_num]=$label;
                  if (is_array($data) && count($data)) {
                     foreach ($data as $key => $val) {
                        if (!isset($values[$key])) {
                           $values[$key]=array();
                        }
                        $values[$key][$row_num]=$val;
                     }
                  }
                  $row_num++;
               }
               ksort($values);
               
               // Print labels
               
               fwrite($fp,$CFG_GLPI["csv_export_delimiter"]);
               foreach ($labels as $val) {
                  fwrite($fp,$val.$CFG_GLPI["csv_export_delimiter"]);
               }
               fwrite($fp,"\n");
               foreach ($values as $key => $data) {
                  fwrite($fp,$key.$CFG_GLPI["csv_export_delimiter"]);
                  foreach ($data as $value) {
                     fwrite($fp,$value.$CFG_GLPI["csv_export_delimiter"]);
                  }
                  fwrite($fp,"\n");
               }

               fclose($fp);
            }
         } 
      }
   }

   static function showItems($target,$date1,$date2,$start) {
      global $DB,$CFG_GLPI,$LANG;

      $view_entities=Session::isMultiEntitiesMode();

      if ($view_entities) {
         $entities=getAllDatasFromTable('glpi_entities');
      }

      $output_type=Search::HTML_OUTPUT;
      if (isset($_GET["display_type"])) {
         $output_type=$_GET["display_type"];
      }
      if (empty($date2)) {
         $date2=date("Y-m-d");
      }
      $date2.=" 23:59:59";

      // 1 an par defaut
      if (empty($date1)) {
         $date1 = date("Y-m-d",mktime(0,0,0,date("m"),date("d"),date("Y")-1));
      }
      $date1.=" 00:00:00";

      $query = "SELECT `itemtype`, `items_id`, COUNT(*) AS NB
               FROM `glpi_tickets`
               WHERE `date` <= '$date2'
                     AND `date` >= '$date1' ".
                     getEntitiesRestrictRequest("AND","glpi_tickets")."
                     AND `itemtype` <> ''
                     AND `items_id` > 0
               GROUP BY `itemtype`, `items_id`
               ORDER BY NB DESC";

      $result=$DB->query($query);
      $numrows=$DB->numrows($result);

      if ($numrows>0) {

         $end_display=$start+$_SESSION['plugin_mobile']['rows_limit'];
         if (isset($_GET['export_all'])) {
            $end_display=$numrows;
         }
         
         echo PluginMobileSearch::showHeader($output_type,$end_display-$start+1,2,1);
         $header_num=1;
         echo "<div data-type='horizontal' data-role='controlgroup' class='mobile_list_header'>";
         echo PluginMobileSearch::showHeaderItem($output_type,$LANG['common'][1],$header_num, '#', 0, '', 3);
         if ($view_entities) {
            echo PluginMobileSearch::showHeaderItem($output_type,$LANG['entity'][0],$header_num, '#', 0, '', 3);
         }
         echo PluginMobileSearch::showHeaderItem($output_type,$LANG['stats'][13],$header_num, '#', 0, '', 3);
         echo "</div>";
         echo PluginMobileSearch::showEndLine($output_type);

         $DB->data_seek($result,$start);

         $i=$start;
         if (isset($_GET['export_all'])) {
            $start=0;
         }
         for ($i = $start ;$i < $numrows && $i<$end_display ;$i++) {
            $item_num=1;
            
            // Get data and increment loop variables
            
            $data=$DB->fetch_assoc($result);
            if (!class_exists($data["itemtype"])) {
               continue;
            }
            $item = new $data["itemtype"]();
            if ($item->getFromDB($data["items_id"])) {

               echo PluginMobileSearch::showNewLine($output_type,$i%2);
               echo PluginMobileSearch::showItem($output_type,$item->getTypeName()." - ".$item->getNameID(),$item_num,
                                    $i-$start+1,"class='center'"." ".
                                    ($item->isDeleted()?" class='deleted' ":""), 3);
               if ($view_entities) {
                  $ent=$item->getEntityID();
                  if ($ent==0) {
                     $ent=$LANG['entity'][2];
                  } else {
                     $ent=$entities[$ent]['completename'];
                  }
                  echo PluginMobileSearch::showItem($output_type,$ent,$item_num,$i-$start+1,"class='center'"." ".
                                       ($item->isDeleted()?" class='deleted' ":""), 3);
               }
               echo PluginMobileSearch::showItem($output_type,$data["NB"],$item_num,$i-$start+1,
                                    "class='center'"." ".
                                    ($item->isDeleted()?" class='deleted' ":""), 3);
            }
         }

         echo PluginMobileSearch::showFooter($output_type);
         if ($output_type==Search::HTML_OUTPUT) {
            self::displayFooterNavBar($target, $numrows);
         }
      }
   }
   
   public static function displayFooterNavBar($url = '',$numrows) {
      global $LANG, $CFG_GLPI;  

      if ($url != '') $url = $CFG_GLPI["root_doc"]."/plugins/mobile/front/".$url;

      $step = $_SESSION['plugin_mobile']['rows_limit'];

      if (!isset($_GET['start'])) $start = 0;
      else $start = $_GET['start'];

      $get_str = $_SERVER['QUERY_STRING'];
      $get_str = substr($get_str, 0, strpos($get_str, '&start='));

      $first = 0;
      $prev = $start - $step;
      if ($prev < 0) $prev = 0;
      $next = $start + $step;
      $last = floor($numrows / $step) * $step;

      $disable_first = false;
      $disable_prev = false;
      $disable_next = false;
      $disable_end = false;

      $start_str = "start=";
      if (strlen(trim($get_str)) > 0) $start_str = "&".$start_str;

      //disable unnecessary navigation element
      
      if ($start == 0) {
      $disable_first = true;
      $disable_prev = true;
      }

      if (($numrows - $start) <= $step) {
      $disable_next = true;
      $disable_end = true;
      }    

      //display footer navigation bar
      
      echo "<div data-role='footer' data-position='fixed' data-theme='a'>";
      // display navigation position
      
      echo "<span id='nav_position'>"
        . $LANG['plugin_mobile']['common'][0] ." "
        . ($start+1) ." "
        . $LANG['plugin_mobile']['common'][1] ." "
        . ($start+$step) ." "
        . $LANG['plugin_mobile']['common'][2] ." "
        . $numrows 
        . "</span>";
   
      echo "<div data-role='navbar'>";
      echo "<ul>";
        echo "<li><a ";
        if (!$disable_first) { echo "href='".$url."?".$get_str.$start_str.$first."' rel='external'"; }
        else  { echo "class='ui-disabled'"; }
        echo " data-icon='back'>".$LANG['buttons'][33]."</a></li>";
        
        echo "<li><a ";
        if (!$disable_prev) echo "href='".$url."?".$get_str.$start_str.$prev."' rel='external'";
        else echo "class='ui-disabled'";
        echo " data-icon='arrow-l'>".$LANG['buttons'][12]."</a></li>";
        
        echo "<li><a ";
        if (!$disable_next) echo "href='".$url."?".$get_str.$start_str.$next."' rel='external'";
        else echo "class='ui-disabled'";
        echo " data-icon='arrow-r'>".$LANG['buttons'][11]."</a></li>";
        
        echo "<li><a ";
        if (!$disable_end) echo "href='".$url."?".$get_str.$start_str.$last."' rel='external'";
        else echo "class='ui-disabled'";
        echo " data-icon='forward'>".$LANG['buttons'][32]."</a></li>";
        
      echo "</ul>";
      echo "</div>";
      echo "</div>";
   }
   
   public static function displayFooterNavBar2($val1, $val2, $next, $prev, $title) {
      global $LANG;
      
      $cleantarget = preg_replace("/[&]date[12]=[0-9-]*/","",$_SERVER['QUERY_STRING']);
      $cleantarget = preg_replace("/[&]*id=([0-9]+[&]{0,1})/","",$cleantarget);
      $cleantarget = preg_replace("/&/","&amp;",$cleantarget);
      
      echo "<div data-role='footer' data-position='fixed' id='footernavbar2' data-theme='a'>";
      echo "<div data-role='navbar'>";
      echo "<ul>";
      if ($prev > 0) {
         echo "<li><a href='".$_SERVER['PHP_SELF']."?$cleantarget&amp;date1=".$_POST["date1"]."&amp;date2=".
               $_POST["date2"]."&amp;id=$prev' data-icon='arrow-l' data-direction='reverse'>".$LANG['buttons'][12]."</a></li>";
      }

      echo "<li><a data-theme='c' href='#'>".$title."</a></li>";
      if ($next > 0) {
         echo "<li><a href='".$_SERVER['PHP_SELF']."?$cleantarget&amp;date1=".$_POST["date1"]."&amp;date2=".
         $_POST["date2"]."&amp;id=$next' data-icon='arrow-r'>".$LANG['buttons'][11]."</a></li>";
      }
      echo "</ul>";
      echo "</div>";
      echo "</div>";
   }
   
   public static function getVal(&$val1, &$val2, &$next, &$prev, &$title) {
      global $LANG, $DB;
      
      $next = 0;
      $prev = 0;
      $title = "";      
       
      $job = new Ticket();
      
      switch($_GET["type"]) {
         case "technicien" :
            $val1 = $_GET["id"];
            $val2 = "";

            $next = getNextItem("glpi_users",$_GET["id"]);
            $prev = getPreviousItem("glpi_users",$_GET["id"]);
            $title = $LANG['stats'][16]."&nbsp;: ".Ticket::getAssignName($_GET["id"],'User',1);
            break;

         case "technicien_followup" :
            $val1 = $_GET["id"];
            $val2 = "";

            $next = getNextItem("glpi_users",$_GET["id"]);
            $prev = getPreviousItem("glpi_users",$_GET["id"]);
            $title = $LANG['stats'][16]."&nbsp;: ".Ticket::getAssignName($_GET["id"],'User',1);
            break;

         case "enterprise" :
            $val1 = $_GET["id"];
            $val2 = "";

            $next = getNextItem("glpi_suppliers",$_GET["id"]);
            $prev = getPreviousItem("glpi_suppliers",$_GET["id"]);
            $title = $LANG['stats'][44]."&nbsp;: ".Ticket::getAssignName($_GET["id"],'Supplier',1);
            break;

         case "user" :
            $val1 = $_GET["id"];
            $val2 = "";
            $job->fields["users_id"] = $_GET["id"];

            $next = getNextItem("glpi_users",$_GET["id"]);
            $prev = getPreviousItem("glpi_users",$_GET["id"]);
            //$title = $LANG['stats'][20]."&nbsp;: ".$job->getAuthorName();
//user footer            
            $title = $LANG['stats'][20]."&nbsp;: ".getUserName($_GET["id"]);			
								
            break;

         case "users_id_recipient" :
            $val1 = $_GET["id"];
            $val2 = "";
            $job->fields["users_id"]=$_GET["id"];

            $next = getNextItem("glpi_users",$_GET["id"]);
            $prev = getPreviousItem("glpi_users",$_GET["id"]);       
            //$title = $LANG['stats'][20]."&nbsp;: ".$job->getAuthorName();            
            $title = $LANG['stats'][20]."&nbsp;: ";            
            break;

         case "ticketcategories_id" :
            $val1 = $_GET["id"];
            $val2 = "";

            $next = getNextItem("glpi_itilcategories", $_GET["id"], '', 'completename');
            $prev = getPreviousItem("glpi_itilcategories", $_GET["id"], '', 'completename');
            $title = $LANG['common'][36]."&nbsp;: ".Dropdown::getDropdownName("glpi_itilcategories",$_GET["id"]);
            break;

         case "group" :
            $val1 = $_GET["id"];
            $val2 = "";

            $next = getNextItem("glpi_groups",$_GET["id"]);
            $prev = getPreviousItem("glpi_groups",$_GET["id"]);
            $title = $LANG['common'][35]."&nbsp;: ".Dropdown::getDropdownName("glpi_groups",$_GET["id"]);
            break;

         case "groups_id_assign" :
            $val1 = $_GET["id"];
            $val2 = "";

            $next = getNextItem("glpi_groups",$_GET["id"]);
            $prev = getPreviousItem("glpi_groups",$_GET["id"]);
            $title = $LANG['common'][35]."&nbsp;: ".Dropdown::getDropdownName("glpi_groups",$_GET["id"]);
            break;

         case "priority" :
            $val1 = $_GET["id"];
            $val2 = "";
            $next = $prev = 0;
            if ($val1 < 6) {
               $next = $val1+1;
            }
            if ($val1 > 1) {
               $prev = $val1-1;
            }
            $title = $LANG['joblist'][2]."&nbsp;: ".Ticket::getPriorityName($_GET["id"]);
            break;

         case "urgency" :
            $val1 = $_GET["id"];
            $val2 = "";
            $next = $prev = 0;
            if ($val1 < 5) {
               $next = $val1+1;
            }
            if ($val1 > 1) {
               $prev = $val1-1;
            }
            $title = $LANG['joblist'][29]."&nbsp;: ".Ticket::getUrgencyName($_GET["id"]);
            break;

         case "impact" :
            $val1 = $_GET["id"];
            $val2 = "";
            $next = $prev = 0;
            if ($val1 < 5) {
               $next = $val1+1;
            }
            if ($val1 > 1) {
               $prev = $val1-1;
            }
            $title = $LANG['joblist'][30]."&nbsp;: ".Ticket::getImpactName($_GET["id"]);
            break;

         case "usertitles_id" :
            $val1 = $_GET["id"];
            $val2 = "";
            $next = $prev = 0;
            $next = getNextItem("glpi_usertitles",$_GET["id"]);
            $prev = getPreviousItem("glpi_usertitles",$_GET["id"]);
            $title = $LANG['users'][1]."&nbsp;: ".Dropdown::getDropdownName("glpi_usertitles",$_GET["id"]);
            break;

         case "ticketsolutiontypes_id" :
            $val1 = $_GET["id"];
            $val2 = "";
            $next = $prev = 0;
            $next = getNextItem("glpi_ticketsolutiontypes",$_GET["id"]);
            $prev = getPreviousItem("glpi_ticketsolutiontypes",$_GET["id"]);
            $title = $LANG['users'][1]."&nbsp;: ".Dropdown::getDropdownName("glpi_ticketsolutiontypes",$_GET["id"]);
            break;

         case "usercategories_id" :
            $val1 = $_GET["id"];
            $val2 = "";
            $next = $prev=0;
            $next = getNextItem("glpi_usercategories",$_GET["id"]);
            $prev = getPreviousItem("glpi_usercategories",$_GET["id"]);
            $title = $LANG['users'][2]."&nbsp;: ".Dropdown::getDropdownName("glpi_usercategories",$_GET["id"]);
            break;

         case "requesttypes_id" :
            $val1 = $_GET["id"];
            $val2 = "";
            $next = $prev = 0;
            if ($val1 < 6) {
               $next = $val1+1;
            }
            if ($val1 > 0) {
               $prev = $val1-1;
            }
            $title = $LANG['job'][44]."&nbsp;: ".Dropdown::getDropdownName('glpi_requesttypes', $_GET["id"]);
            break;

         case "device" :
            $val1 = $_GET["id"];
            $val2 = $_GET["champ"];

            $item = new $_GET["champ"]();
            $device_table = $item->getTable();
            $next = getNextItem($device_table,$_GET["id"],'','designation');
            $prev = getPreviousItem($device_table,$_GET["id"],'','designation');

            $query = "SELECT `designation`
                      FROM `".$device_table."`
                      WHERE `id` = '".$_GET['id']."'";
            $result = $DB->query($query);

            $title = $item->getTypeName()."&nbsp;: ".$DB->result($result,0,"designation");
            break;

         case "comp_champ" :
            $val1 = $_GET["id"];
            $val2 = $_GET["champ"];

            $item = new $_GET["champ"]();
            $table = $item->getTable();
            $next = getNextItem($table,$_GET["id"]);
            $prev = getPreviousItem($table,$_GET["id"]);
            $title = $item->getTypeName()."&nbsp;: ".Dropdown::getDropdownName($table,$_GET["id"]);
            break;
      }
      
   }
   
   public static function showDateSelector($target = '') {
      global $LANG;
      
      echo "<div data-role='collapsible' data-collapsed='true'>";
      echo "<h2>".$LANG['common'][27]."</h2>";
      echo "<form method='post' name='form'>";
      
      echo "<label for='stat_date2'><b>".$LANG['search'][8]."&nbsp;:</b></label>";
      echo "<input type='date' name='date1' id='stat_date1' value='"
         .$_POST["date1"]."' /><br /><br />";
      
      echo "<label for='stat_date2'><b>".$LANG['search'][9]."&nbsp;:</b></label>";
      echo "<input type='date' name='date2' id='stat_date2' value='"
         .$_POST["date2"]."' /><br /><br />";
      
      echo "<input type='submit' class='button' name='submit' value='"
         .$LANG['buttons'][7]."' data-inline='true' data-theme='a'>";
         
      //echo "</form>";
      Html::closeForm();
      echo "</div>";
   }
   
}

?>
