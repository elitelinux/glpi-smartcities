<?php
/*
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE
Inventaire
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

class PluginMobilePlanning extends Planning {
   
   public static function showSelectionForm($date, $type = 'week', $usertype, $uID = 0, $gID = 0) {
      global $LANG, $CFG_GLPI;
      
      saveCFG();
      $CFG_GLPI["use_ajax"] = false;
     
      echo "<form method='get' id='planning-form'>";
      echo "<div data-role='collapsible' data-collapsed='true'>";
      echo "<h3>".$LANG['plugin_mobile']['common'][5]."</h3>";
      echo "<div>";  
      echo "<div class='ui-body ui-body-c'>";
      echo "<h4>".$LANG['common'][34]."</h4>";
         
         echo "<div data-role='fieldcontain'>";
         echo "<fieldset data-role='controlgroup'>";
            echo "<input type='radio' id='radio_user' name='usertype' value='user' ".
               ($usertype=="user"?"checked":"").">";     
            echo "<label for='radio_user'>";
            $rand_user=User::dropdown(array( 'name'   => 'uID',
                                                'value'  => $uID,
                                                'comments'  => false,
                                                'right'  => 'interface',
                                                'all'    => 1,
                                                'entity' => $_SESSION["glpiactive_entity"]));      
            echo "</label>";
            echo "<input type='radio' id='radio_group' name='usertype' value='group' ".
               ($usertype=="group"?"checked":"").">";                                         
            echo "<label for='radio_group'>";
            $rand_group=Dropdown::show('Group',
                                          array('value'  =>$gID,
                                                'comments'  => false,
                                                'name'   =>'gID',
                                                'entity' =>$_SESSION["glpiactive_entity"]));         
            echo "</label>";
            echo "<input type='radio' id='radio_user_group' name='usertype' value='user_group' ".
               ($usertype=="user_group"?"checked":"").">";      
            echo "<label for='radio_user_group'>";
            echo $LANG['joblist'][3];
            echo "</label>";
         echo "</fieldset>";
         echo "</div>";

   
      echo "</div><br /><div class='ui-body ui-body-c'>";
      echo "<h4>".$LANG['common'][27]."</h4>"; 
         echo "<input type='date' name='date' id='date' value='".$date."' />";
         echo "<select name='type'>";
            echo "<option value='day' ".($type=="day"?" selected ":"").">".$LANG['planning'][5]."</option>";
            echo "<option value='week' ".($type=="week"?" selected ":"").">".$LANG['planning'][6]."</option>";
            echo "<option value='month' ".($type=="month"?" selected ":"").">".$LANG['planning'][14]."</option>";
         echo "</select>";
         echo "<hr />";
         echo "<input type='submit' class='button' name='submit' Value='". $LANG['buttons'][7] ."' data-theme='a' data-inline='true' />";
      echo "</div>";
      echo "</div>";
      echo "</div><!-- /collapsible -->";
      
      //echo "</form>";
      Html::closeForm();
      
      /*echo "<script type='text/javascript'>";
      echo "$('.datepickerinput').each(function(){";
      echo "$(this).after( $( '<div />' ).datepicker({ altField: '#' + $(this).attr( 'id' ), showOtherMonths: true, dateFormat: 'yy-mm-dd' }) );";
      echo "});";
      echo "</script>\n";*/
      
      restoreCFG();
   
   } 
   
   /**
    * Display an integer using 2 digits
    *
    *
    * @param $time value to display
    * @return string return the 2 digits item
    *
    **/
   static private function displayUsingTwoDigits($time) {

      $time=round($time);
      if ($time<10 && strlen($time)>0) {
         return "0".$time;
      } else {
         return $time;
      }
   }
      
   public function show($when,$type,$who,$who_group) {
      global $LANG,$CFG_GLPI,$DB;

     // if (!haveRight("show_planning",READ) && !haveRight("show_all_planning",UPDATE)) {
     	 if (!haveRight("planning",CREATE) || !haveRight("planning",Ticket::READALL)) {
         return false;
      }

      // Define some constants
      $date=explode("-",$when);
      $time=mktime(0,0,0,$date[1],$date[2],$date[0]);

      // Check bisextile years
      list($current_year,$current_month,$current_day)=explode("-",$when);
      if (($current_year%4)==0) {
         $feb=29;
      } else {
         $feb=28;
      }
      $nb_days= array(31,$feb,31,30,31,30,31,31,30,31,30,31);
      // Begin of the month
      $begin_month_day=strftime("%w",mktime(0,0,0,$current_month,1,$current_year));
      if ($begin_month_day==0) {
         $begin_month_day=7;
      }
      $end_month_day=strftime("%w",mktime(0,0,0,$current_month,$nb_days[$current_month-1],$current_year));
      // Day of the week
      $dayofweek=date("w",$time);
      // Cas du dimanche
      if ($dayofweek==0) {
         $dayofweek=7;
      }

      // Get begin and duration
      $begin=0;
      $end=0;
      switch ($type) {
         case "month" :
            $begin=strtotime($current_year."-".$current_month."-01 00:00:00");
            $end=$begin+DAY_TIMESTAMP*$nb_days[$current_month-1];
            
            $year_next=$date[0];
            $month_next=$date[1]+1;
            if ($month_next>12) {
               $year_next++;
               $month_next-=12;
            }
            $year_prev=$date[0];
            $month_prev=$date[1]-1;
            if ($month_prev==0) {
               $year_prev--;
               $month_prev+=12;
            }
            $next=$year_next."-".sprintf("%02u",$month_next)."-".$date[2];
            $prev=$year_prev."-".sprintf("%02u",$month_prev)."-".$date[2];
            
            break;

         case "week" :
            $tbegin=$begin=$time+mktime(0,0,0,0,1,0)-mktime(0,0,0,0,$dayofweek,0);
            $end=$begin+WEEK_TIMESTAMP;
            break;

         case "day" :
            $add="";
            $begin=$time;
            $end=$begin+DAY_TIMESTAMP;
            break;
      }
      $begin=date("Y-m-d H:i:s",$begin);
      $end=date("Y-m-d H:i:s",$end);
      
      
      //construct navigation intervals
      if (in_array($type, array('week', 'day'))) {
         $time=strtotime($when);
         $step=0;
         switch ($type) {
            case "week" :
               $step=WEEK_TIMESTAMP;
               break;

            case "day" :
               $step=DAY_TIMESTAMP;
               break;
         }
         $next=$time+$step+10;
         $prev=$time-$step;
         $next=strftime("%Y-%m-%d",$next);
         $prev=strftime("%Y-%m-%d",$prev);
      }
      
      $navBar = self::showNavBar($next, $prev, $type, $who, $who_group);
      
      // Print Headers
      echo "<div class='center'><table class='tab_cadre_fixe mobile_calendar'>";
            
      // Print Headers
      echo "<tr class='tab_bg_1'>";
      switch ($type) {
         case "month" :
            for ($i=1 ; $i<=7 ; $i++) {
               echo "<th width='12%'>".$LANG['calendarD'][$i%7]."</th>";
            }
            break;

         case "week" :
            echo "<th />";
            for ($i=1 ; $i<=7 ; $i++, $tbegin+=DAY_TIMESTAMP) {
               echo "<th width='12%'>".$LANG['calendarD'][$i%7]." ".date('d',$tbegin)."</th>";
            }
            break;

         case "day" :
            echo "<th />";
            echo "<th width='12%'>".$LANG['calendarDay'][$dayofweek%7]." ".date('d',$tbegin)."</th>";
            break;
      }
      echo "</tr>\n";


      // ---------------Tracking
      $interv = TicketPlanning::populatePlanning($who, $who_group, $begin, $end);

      // ---------------reminder
      $datareminders = Reminder::populatePlanning($who, $who_group, $begin, $end);

      $interv = array_merge($interv, $datareminders);

      // --------------- Plugins
      $data=doHookFunction("planning_populate",array("begin"=>$begin,
                                                     "end"=>$end,
                                                     "who"=>$who,
                                                     "who_group"=>$who_group));

      if (isset($data["items"])&&count($data["items"])) {
         $interv=array_merge($data["items"],$interv);
      }

      // Display Items
      $tmp=explode(":",$CFG_GLPI["planning_begin"]);
      $hour_begin=$tmp[0];
      $tmp=explode(":",$CFG_GLPI["planning_end"]);
      $hour_end=$tmp[0];
      if ($tmp[1]>0) {
         $hour_end++;
      }

      switch ($type) {
         case "week" :
            for ($hour=$hour_begin;$hour<=$hour_end;$hour++) {
               echo "<tr>";
               echo "<td class='td_hour'>".self::displayUsingTwoDigits($hour)."</td>";
               for ($i=1;$i<=7;$i++) {
                  echo "<td class='tab_bg_3 top' width='12%'>";
                  
                  // From midnight
                  if ($hour==$hour_begin) {
                     $begin_time=date("Y-m-d H:i:s",strtotime($when)+($i-$dayofweek)*DAY_TIMESTAMP);
                  } else {
                     $begin_time=date("Y-m-d H:i:s",
                                   strtotime($when)+($i-$dayofweek)*DAY_TIMESTAMP+$hour*HOUR_TIMESTAMP);
                  }
                  // To midnight
                  if($hour==$hour_end) {
                     $end_time=date("Y-m-d H:i:s",
                                    strtotime($when)+($i-$dayofweek)*DAY_TIMESTAMP+24*HOUR_TIMESTAMP);
                  } else {
                     $end_time=date("Y-m-d H:i:s",
                                 strtotime($when)+($i-$dayofweek)*DAY_TIMESTAMP+($hour+1)*HOUR_TIMESTAMP);
                  }

                  reset($interv);
                  while ($data=current($interv)) {
                     $type="";
                     if ($data["begin"]>=$begin_time && $data["end"]<=$end_time) {
                        $type="in";
                     } else if ($data["begin"]<$begin_time && $data["end"]>$end_time) {
                        $type="through";
                     } else if ($data["begin"]>=$begin_time && $data["begin"]<$end_time) {
                        $type="begin";
                     } else if ($data["end"]>$begin_time&&$data["end"]<=$end_time) {
                        $type="end";
                     }

                     if (empty($type)) {
                        next($interv);
                     } else {
                        self::displayPlanningItem($data,$who);
                        if ($type=="in") {
                           unset($interv[key($interv)]);
                        } else {
                           next($interv);
                        }
                     }
                  }
                  echo "</td>";
               }
               echo "</tr>\n";
            }
            break;

         case "day" :
            for ($hour=$hour_begin;$hour<=$hour_end;$hour++) {
               echo "<tr>";
               $begin_time=date("Y-m-d H:i:s",strtotime($when)+($hour)*HOUR_TIMESTAMP);
               $end_time=date("Y-m-d H:i:s",strtotime($when)+($hour+1)*HOUR_TIMESTAMP);
               echo "<td class='td_hour'>".self::displayUsingTwoDigits($hour).":00</td>";
               echo "<td class='tab_bg_3 top' width='12%'>";
               reset($interv);
               while ($data=current($interv)) {
                  $type="";
                  if ($data["begin"]>=$begin_time && $data["end"]<=$end_time) {
                     $type="in";
                  } else if ($data["begin"]<$begin_time && $data["end"]>$end_time) {
                     $type="through";
                  } else if ($data["begin"]>=$begin_time && $data["begin"]<$end_time) {
                     $type="begin";
                  } else if ($data["end"]>$begin_time && $data["end"]<=$end_time) {
                     $type="end";
                  }

                  if (empty($type)) {
                     next($interv);
                  } else {
                     Planning::displayPlanningItem($data,$who,$type,1);
                     if ($type=="in") {
                        unset($interv[key($interv)]);
                     } else {
                        next($interv);
                     }
                  }
               }
               echo "</td></tr>";
            }
            break;

         case "month" :
            echo "<tr class='tab_bg_3'>";
            // Display first day out of the month
            for ($i=1 ; $i<$begin_month_day ; $i++) {
               echo "<td style='background-color:#ffffff'>&nbsp;</td>";
            }
            // Print real days
            if ($current_month<10 && strlen($current_month)==1) {
               $current_month="0".$current_month;
            }
            $begin_time=strtotime($begin);
            $end_time=strtotime($end);
            for ($time=$begin_time ; $time<$end_time ; $time+=DAY_TIMESTAMP) {
               // Add 6 hours for midnight problem
               $day=date("d",$time+6*HOUR_TIMESTAMP);

               echo "<td height='100' class='tab_bg_3 top'>";
               echo "<table><tr><td style='text-align:left'>";
               echo "<span class='month_day'>".$day."</span></td></tr>";

               echo "<tr class='tab_bg_3 center'>";
               echo "<td class='tab_bg_3 top' width='12%'>";
               $begin_day=date("Y-m-d H:i:s",$time);
               $end_day=date("Y-m-d H:i:s",$time+DAY_TIMESTAMP);
               reset($interv);
               while ($data=current($interv)) {
                  $type="";
                  if ($data["begin"]>=$begin_day && $data["end"]<=$end_day) {
                     $type="in";
                  } else if ($data["begin"]<$begin_day && $data["end"]>$end_day) {
                     $type="through";
                  } else if ($data["begin"]>=$begin_day && $data["begin"]<$end_day) {
                     $type="begin";
                  } else if ($data["end"]>$begin_day && $data["end"]<=$end_day) {
                     $type="end";
                  }

                  if (empty($type)) {
                     next($interv);
                  } else {
                     self::displayPlanningItem($data,$who);
                     if ($type=="in") {
                        unset($interv[key($interv)]);
                     } else {
                        next($interv);
                     }
                  }
               }
               echo "</td></tr></table>";
               echo "</td>";

               // Add break line
               if (($day+$begin_month_day)%7==1) {
                  echo "</tr>\n";
                  if ($day!=$nb_days[$current_month-1]) {
                     echo "<tr>";
                  }
               }
            }
            if ($end_month_day!=0) {
               for ($i=0;$i<7-$end_month_day;$i++) {
                  echo "<td style='background-color:#ffffff'>&nbsp;</td>";
               }
            }
            echo "</tr>";
            break;
      }
      echo "</table></div>";
      
      echo $navBar;

   }
   
   
   static function displayPlanningItem($val,$who,$type="",$complete=0) {
      global $CFG_GLPI,$LANG,$PLUGIN_HOOKS;

      $color="#e4e4e4";
      if (isset($val["state"])) {
         switch ($val["state"]) {
            case 0 :
               $color="#efefe7"; // Information
               break;

            case 1 :
               $color="#fbfbfb"; // To be done
               break;

            case 2 :
               $color="#e7e7e2"; // Done
               break;
         }
      }
      echo "<div class='item' style='background-color: $color;'>";

      // Plugins case
      if (isset($val["plugin"]) && isset($PLUGIN_HOOKS['display_planning'][$val["plugin"]])) {
         $function=$PLUGIN_HOOKS['display_planning'][$val["plugin"]];
         if (is_callable($function)) {
            $val["type"]=$type;
            call_user_func($function,$val);
      
         }
      } else if (isset($val["tickets_id"])) {  // show tracking
         TicketPlanning::displayPlanningItem($val, $who, $type, $complete);     
      } else {  // show Reminder
         self::reminderDisplayPlanningItem($val, $who, $type, $complete);
      }
      echo "</div>";
   }
   
   
   static function reminderDisplayPlanningItem($val,$who,$type="",$complete=0) {
      global $CFG_GLPI, $LANG;

      $rand=mt_rand();
      $users_id="";  // show users_id reminder
      $img="rdv_private.png"; // default icon for reminder

      if (!$val["is_private"]) {
         $users_id="<br>".$LANG['planning'][9]."&nbsp;: ".getUserName($val["users_id"]);
         $img="rdv_public.png";
      }

      echo "<img src='".$CFG_GLPI["root_doc"]."/pics/".$img."' alt='' title='".$LANG['title'][37].
            "'>&nbsp;";
      echo "<a id='reminder_".$val["reminders_id"].$rand."' href='".$CFG_GLPI["root_doc"]."/front/reminder.form.php?id=".$val["reminders_id"]."'>";

      switch ($type) {
         case "in" :
            echo date("H:i",strtotime($val["begin"]))." -> ".date("H:i",strtotime($val["end"])).": ";
            break;

         case "through" :
            break;

         case "begin" :
            echo $LANG['buttons'][33]." ".date("H:i",strtotime($val["begin"])).": ";
            break;

         case "end" :
            echo $LANG['buttons'][32]." ".date("H:i",strtotime($val["end"])).": ";
            break;
      }
      echo $val["name"];
      echo $users_id;
      echo "</a>";
      if ($complete) {
         echo "<br><strong>".Planning::getState($val["state"])."</strong><br>";
         echo $val["text"];
      } else {
         showToolTip("<strong>".Planning::getState($val["state"])."</strong><br>".$val["text"],
                     array('applyto'=>"reminder_".$val["reminders_id"].$rand));
      }
      echo "";
   }

   public static function showNavBar($next, $prev, $type, $who, $who_group) {
      global $LANG, $CFG_GLPI;
      
      $out = "<div data-role='footer' data-position='fixed' data-theme='d'>";
      $out .= "<div data-role='navbar'>";
      $out .= "<ul>";
      
      $out .= "<li><a href='".$CFG_GLPI['root_doc']
         ."/plugins/mobile/front/planning.php?type=$type"
         ."&amp;date=$prev&amp;who=$who&amp;who_group=$who_group' "
         ."data-icon='arrow-l'>".$LANG['buttons'][12]."</a></li>";
       
      $out .= "<li><a href='".$CFG_GLPI['root_doc']
         ."/plugins/mobile/front/planning.php?type=$type"
         ."&amp;who=$who&amp;who_group=$who_group' "
         ."data-icon='home'>".'Aujourd\'hui'."</a></li>";

      $out .= "<li><a href='".$CFG_GLPI['root_doc']
         ."/plugins/mobile/front/planning.php?type=$type"
         ."&amp;date=$next&amp;who=$who&amp;who_group=$who_group' "
         ."data-icon='arrow-r'>".$LANG['buttons'][11]."</a></li>";
      
      $out .= "</ul>";
      $out .= "</div>";
      $out .= "</div>";
      
      return $out;
   }

}
