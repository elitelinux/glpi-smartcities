<?php
/**
 * @version $Id: autoreport.class.php 306 2015-05-31 16:03:56Z remi $
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Reports is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   reports
 @authors    Nelly Mahu-Lasson, Remi Collet, Alexandre Delaunay
 @copyright Copyright (c) 2009-2015 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

/**
* Class to create, execute and display a new record
* The class stores a collection of criterias and
* manage :
* 	- criterias selection form
*	- query executing using with criterias restriction
* 	- result display & export (HTML, PDF, CSV, SLK)
**/
class PluginReportsAutoReport {

   private $criterias       = array ();
   private $columns         = array ();
   private $group_by        = array ();
   private $columns_mapping = array ();
   private $sql             = "";
   private $name            = "";
   private $subname         = "";
   private $cpt             = 0;
   private $title           = '';


   function __construct($title='') {

      preg_match('@/plugins/(.*)/report/(.*)/@', $_SERVER['SCRIPT_NAME'], $regs);
      $this->plug = $regs[1];
      $this->name = $regs[2];
      includeLocales($this->name, $this->plug);
      $this->setTitle($title);
   }


   //-------------- Getters ------------------//
   function getCriterias() {
      return $this->criterias;
   }


   //-------------- Setters ------------------//
   /**
   * Set column mappings : when a column's value cannot be
   * displays as it is, but needs to be replaced by another one
   * DEPRECATED : should use PluginReportsColumnMap
   *
   * @param $columns_mappings array the columns new values
   **/
   function setColumnsMappings($columns_mappings) {
      $this->columns_mapping = $columns_mappings;
   }


   /**
    * Defined "GROUP BY" columns
    * for output improvment
    * first line displayed in bold
    * next lines not displayed
    *
    * @param $columns    column name or array of column names
   **/
   function setGroupBy($columns) {

      if (is_array($columns)) {
         $this->group_by = $columns;
      } else {
         $this->group_by = array($columns);
      }
   }


   /**
   * Set columns names (label to be displayed)
   *
   * @param $columns array which contains
   *        sql column name => PluginReportsColumn object
   **/
   function setColumns($columns) {

      $this->columns = array();
      foreach ($columns as $name => $column) {
         if ($column instanceof PluginReportsColumn) {
            $this->columns[$column->name] = $column;
         } else {
            // For compat with setColumnsNames - default text mode
            $this->columns[$name] = new PluginReportsColumn($name, $column);
         }
      }
   }


   /**
   * Set sql request to be executed
   * @param sql the sql request as a string
   **/
   function setSqlRequest($sql) {
      $this->sql = $sql;
   }


   /**
   * Set report's name
   * @param name the name of the report
   **/
   function setName($name) {
      list($this->plug,$this->name) = explode('.',$name,2);
   }


   /**
   * Set report's Title
   *
   * @param $title the title of the report
   **/
   function setTitle($title) {

      if ($title) {
         $this->title = $title;

      } else {
         $this->title = (isset($this->name)
                             ? sprintf(__('%s'), $this->name)
                             : __('Report', 'Reports', 1));
      }
   }


   /**
    * Get the report's title (main title + sub title from criteria)
   **/
   function getFullTitle() {

      if ($this->subname) {
         return $this->title ." - " . $this->subname;
      }
      return $this->title;
   }


   /**
    * Set the report's subname
    *
    * @param subname the report's subname to display
   **/
   function setSubName($subname) {
      $this->subname = $subname;
   }


   /**
    * Generate automatically the report's subname
   **/
   function setSubNameAuto() {

      $subname = "";
      $prefix = "";
      //Get all criteria's subnames and add it to the report's subname
      foreach ($this->criterias as $criteria) {
         if ($name = $criteria->getSubName()) {
            $subname .= $prefix.$name;
            $prefix = " - ";
         }
      }

      $this->subname = $subname;
   }


   //------------- Other -------------//
   /**
    * Indicates if the criteria's form is validated or not
    *
    * @return true if form is validated
   **/
   function criteriasValidated() {
      return isset ($_POST['find']);
   }


   /**
    * Execute the report
    *
    * @param $options   array
   **/
   function execute($options=array()) {
      global $DB, $CFG_GLPI, $HEADER_LOADED;

      // Require (for pager) when not called by displayCriteriasForm
      $this->manageCriteriasValues();

      if (isset ($_POST['list_limit'])) {
         $_SESSION['glpilist_limit'] = $_POST['list_limit'];
         unset ($_POST['list_limit']);
      }

      $limit = $_SESSION['glpilist_limit'];

      if (isset ($_POST["display_type"])) {
         $output_type = $_POST["display_type"];
         if ($output_type < 0) {
            $output_type = - $output_type;
            $limit       = 0;
         }
      } else {
         $output_type = Search::HTML_OUTPUT;
      }

      $title = $this->title;
      if ($this->subname) {
         $title = sprintf(__('%1$s - %2$s'), $title, $this->subname);
      }

      $res   = $DB->query($this->sql);
      $nbtot = ($res ? $DB->numrows($res) : 0);
      if ($limit) {
         $start = (isset ($_GET["start"]) ? $_GET["start"] : 0);
         if ($start >= $nbtot) {
            $start = 0;
         }
         if (($start > 0) || (($start + $limit) < $nbtot)) {
            $res = $DB->query($this->sql . " LIMIT $start,$limit");
         }
      } else {
         $start = 0;
      }

      if ($nbtot == 0) {
         if (!$HEADER_LOADED) {
            Html::header($title, $_SERVER['PHP_SELF'], "utils", "report");
            Report::title();
         }
         echo "<div class='center'><font class='red b'>".__('No item found')."</font></div>";
         Html::footer();
      } else if ($output_type == Search::HTML_OUTPUT) {
         if (!$HEADER_LOADED) {
            Html::header($title, $_SERVER['PHP_SELF'], "utils", "report");
            Report::title();
         }
         echo "<div class='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th>".$title."</th></tr>\n";
         echo "<tr class='tab_bg_2 center'><td class='center'>";
         echo "<form method='POST' action='" .$_SERVER["PHP_SELF"] . "?start=$start'>\n";

         $param = "";
         foreach ($_POST as $key => $val) {
            if (is_array($val)) {
               foreach ($val as $k => $v) {
                  echo "<input type='hidden' name='".$key."[$k]' value='$v' >";
                  if (!empty ($param)) {
                     $param .= "&";
                  }
                  $param .= $key."[".$k."]=".urlencode($v);
               }
            } else {
               echo "<input type='hidden' name='".$key."' value='$val' >";
               if (!empty ($param)) {
                  $param .= "&";
               }
               $param .= "$key=" . urlencode($val);
            }
         }
         Dropdown::showOutputFormat();
         Html::closeForm();
         echo "</td></tr>";
         echo "</table></div>";

         Html::printPager($start, $nbtot, $_SERVER['PHP_SELF'], $param);
      }

      $field = 'plugin_reports_'.$this->name;
      if ($this->plug != 'reports') {
         $field = 'plugin_reports_'.$this->plug."_".$this->name;
      }

      Session::checkRight($field, READ);

      if (!isset ($_POST["display_type"]) || ($_POST["display_type"] == Search::HTML_OUTPUT)) {
         if (isset($options['withmassiveaction']) && class_exists($options['withmassiveaction'])) {
            $massformid = 'massform'.$options['withmassiveaction'];
            Html::openMassiveActionsForm($massformid);
            Html::showMassiveActions(array('container' => $massformid));
         }
      }

      if ($res && ($nbtot > 0)) {
         $nbcols = $DB->num_fields($res);
         $nbrows = $DB->numrows($res);

         echo Search::showHeader($output_type, $nbrows, $nbcols, true);
         echo Search::showNewLine($output_type);
         $num = 1;

         // fill $sqlcols with default sql query fields so we can validate $columns
         $sqlcols = array();
         for ($i = 0 ; $i < $nbcols ; $i++) {
            $colname   = $DB->field_name($res, $i);
            $sqlcols[] = $colname;
         }
         $colsname = array();
         // if $columns is not empty, display $columns
         if (count($this->columns) > 0) {
            foreach ($this->columns as $colname => $column) {
               // display only $columns that are valid
               if (in_array($colname, $sqlcols)) {
                  $column->showTitle($output_type, $num);
                  $colsname[$colname] = $column;
               }
            }
         } else { // else display default columns from SQL query
            foreach ($sqlcols as $colname) {
               $column = new PluginReportsColumn($colname, $colname);
               $column->showTitle($output_type, $num);
               $colsname[$colname] = $column;
            }
         }

         echo Search::showEndLine($output_type);

         $prev = "";
         for ($row_num = 2 ; $row = $DB->fetch_assoc($res) ; $row_num++) {
            $crt = "";
            foreach ($this->group_by as $colname) {
               if (isset ($row[$colname])) {
                  $crt .= $row[$colname] . "####";
               }
            }

            echo Search::showNewLine($output_type);
            $num = 1;

            foreach ($colsname as $colname => $column) {

               //If value needs to be modified on the fly
               if (isset ($this->columns_mapping[$colname])
                   && isset ($this->columns_mapping[$colname][$row[$colname]])) {

                  $new_value = $this->columns_mapping[$colname][$row[$colname]];
                  $row[$colname] = $new_value;
               }

               if (!in_array($colname, $this->group_by)) {
                  $column->showValue($output_type, $row, $num, $row_num);
               } else if ($crt == $prev) {
                  $column->showValue($output_type,
                                     (($output_type == Search::CSV_OUTPUT) ? $row : array()),
                                     $num, $row_num);
               } else {
                  $column->showValue($output_type, $row, $num, $row_num, true);
               }
            } // Each column
            echo Search::showEndLine($output_type);
            $prev = $crt;
         } // Each row

         if (isset($options['withtotal']) && $options['withtotal']) {
               echo Search::showNewLine($output_type);
               $num = 1;

               foreach ($colsname as $colname => $column) {
                  $column->showTotal($output_type, $num, $row_num);
               }

               echo Search::showEndLine($output_type);
         }
      }
      echo Search::showFooter($output_type, $title);

      if (!isset ($_POST["display_type"]) || ($_POST["display_type"] == Search::HTML_OUTPUT)) {
         if (isset($options['withmassiveaction']) && class_exists($options['withmassiveaction'])) {
            Html::showMassiveActions(array('container' => $massformid,
                                           'ontop'     => false));
            Html::closeForm();
         }
         Html::footer();
      }
   }


   /**
    * Display a common search criterias form
    * @param target the form's target
    * @param params the search criterias
    */
   function displayCriteriasForm() {
      global $HEADER_LOADED;

      //Get criteria's values
      $this->manageCriteriasValues();

      //Display Html::header is output is HTML
      if (isset ($_POST["display_type"]) && $_POST["display_type"] != Search::HTML_OUTPUT) {
         return;
      }
      if (!$HEADER_LOADED) {

         $title = $this->title;
         if ($this->subname) {
            $title = sprintf(__('%1$s - %2$s'), $title, $this->subname);
         }

         if (isStat($this->name)) {
            Html::header($title, $_SERVER['PHP_SELF'], "helpdesk", "stat");
            Stat::title();
         } else {
            Html::header($title, $_SERVER['PHP_SELF'],
                        "tools", "report");
            Report::title();
         }
      }

      $field = 'plugin_reports_'.$this->name;
      if ($this->plug != 'reports') {
         $field = 'plugin_reports_'.$this->plug."_".$this->name;
      }
      Session::checkRight($field, READ);

      //Display form only if there're criterias
      if (!empty($this->criterias)) {
         echo "<div class='center'>";
         echo "<form method='post' name='form' action='".$_SERVER['PHP_SELF']."'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='6'>" . __('Search criteria', 'reports');

         //If form is validated, then display the bookmark button
         if ($this->criteriasValidated()) {
            //Add parameters to uri to be saved as bookmarks
            $_SERVER["REQUEST_URI"] = $this->buildBookmarkUrl();
            Bookmark::showSaveButton(Bookmark::URI,
                                     (isStat($this->name)?'PluginReportsStat':'PluginReportsReport'));
         }
         echo "</th></tr>\n";

         //Display each criteria's html selection item
         foreach ($this->criterias as $criteria) {
            $criteria->displayCriteria();
         }

         $this->closeColumn();

         echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
         echo "<input type='submit' name='find' value='"._sx('button', 'Search')."' class='submit'>";
         echo "</td></tr>";
         echo "</table></div>";
         Html::closeForm();
      }
   }


   function manageCriteriasValues() {

      foreach ($this->criterias as $criteria) {
         $criteria->manageCriteriaValues();
      }

      //If selectio form is validated, then stores it
      if (isset($_GET['find']) || isset($_POST['find'])) {
         $_POST['find'] = true;
      }
      // Order by
      if (isset($_GET['sort'])) {
         $_POST['sort'] = $_GET['sort'];
      }
      if (isset($_GET['order'])) {
         $_POST['order'] = $_GET['order'];
      }
   }


   /**
    * Append date and time restriction in an sql request
    * @param fields the fields to be restricted
    * @param params the values to be used
    * @param link with previous condition
    */
   function addSqlCriteriasRestriction($link = 'AND') {

      $sql = "";
      //Get all criterias sql restriction criterias
      foreach ($this->criterias as $criteria) {
         $add = $criteria->getSqlCriteriasRestriction($link);
         if ($add) {
            $sql .= $add;
            $link = 'AND';
         }
      }
      return $sql;
   }


   /**
   * Build the bookmark URL, which contains all the criteria's values
   * @return a string to be stored by the bookmarking system
   **/
   function buildBookmarkUrl() {

      $bookmark_criterias='?find=1';
      foreach ($this->criterias as $criteria) {
         $bookmark_criterias.= $criteria->getBookmarkUrl();
      }
      return $_SERVER["REQUEST_URI"].$bookmark_criterias;
   }


   /**
   * Add a new criteria to the report
   **/
   function addCriteria($criteria) {
      $this->criterias[] = $criteria;
   }


   /**
    * Delete a criteria
    */
   function delCriteria($name) {

      foreach ($this->criterias as $key => $crit) {
         if ($crit->getName() == $name) {
            unset($this->criterias[$key]);
         }
      }
   }


   /**
   * Add a new column in the criterias selection form
   **/
   function startColumn() {

      if ($this->cpt==0) {
         echo "<tr class='tab_bg_1'>";
      }
      echo "<td>";
      $this->cpt++;
   }


   /**
   * End a column in the criterias selection form
   **/
   function endColumn() {

      echo "</td>";
      if ($this->cpt==4) {
         echo "</tr>";
         $this->cpt=0;
      }
   }


   /**
   * Close a column in the criterias selection form
   **/
   function closeColumn() {

      if ($this->cpt>0) {
         while ($this->cpt<4) {
            echo "<td></td>";
            $this->cpt++;
         }
         $this->cpt=0;
         echo "</tr>";
      }
   }

   /**
    * Get the fields used for order
    *
    * @param $default string, name of the column used by default
    *
    * @return array of column names
    */
   function getOrderByFields($default) {

      if (!isset($_REQUEST['sort'])) {
         $_REQUEST['sort'] = $default;
      }
      $colsort = $_REQUEST['sort'];

      foreach ($this->columns as $colname => $column) {
         if ($colname==$colsort) {
            return explode(',',$column->sorton);
         }
      }
      return array();
   }

   /**
    * Build the ORDER BY clause
    *
    * @param $default string, name of the column used by default
    * @apram $setgroupby if true, setGroupBy on same column
    *
    * @return string with SQL clause
    */
   function getOrderBy($default, $setgroupby=false) {

      if (!isset($_REQUEST['order']) || $_REQUEST['order']!='DESC') {
         $_REQUEST['order'] = 'ASC';
      }
      $order   = $_REQUEST['order'];

      $tab = $this->getOrderByFields($default);
      if (count($tab)>0) {
         if ($setgroupby) {
            $this->setGroupBy($tab);
         }
         return " ORDER BY ".implode(" $order, ", $tab)." $order";
      }
      return '';
   }


   /**
    * Set the GroupBy columns using the Orderby Fields
    * **** name of the columns must be the same than the fields ***
    *
    * @param $default string, name of the column used by default
    */
   function setGroupByAuto($default) {
      $this->setGroupBy($this->getOrderByFields($default));
   }
}
?>