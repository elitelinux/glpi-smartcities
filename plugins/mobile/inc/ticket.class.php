<?php
/*
 * @version $Id: ticket.class.php 14537 2011-05-26 08:18:11Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

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

/// Tracking class

class PluginMobileTicket extends Ticket {


   /** Get users which have intervention assigned to  between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct users which have any intervention assigned to.
   **/
    function getUsedTechBetween($date1='',$date2='') {
      global $DB;

      $query = "SELECT DISTINCT `glpi_users`.`id` AS users_id,
                                `glpi_users`.`name` AS name,
                                `glpi_users`.`realname` AS realname,
                                `glpi_users`.`firstname` AS firstname
                FROM `glpi_tickets`
                LEFT JOIN `glpi_tickets_users`
                           ON (`glpi_tickets_users`.`tickets_id` = `glpi_tickets`.`id`
                               AND `glpi_tickets_users`.`type` = '".CommonITILActor::ASSIGN."')
                LEFT JOIN `glpi_users` ON (`glpi_users`.`id` = `glpi_tickets_users`.`users_id`) ".
                getEntitiesRestrictRequest("WHERE", "glpi_tickets");

      if (!empty($date1)||!empty($date2)) {
         $query .= " AND (".getDateRequest("`glpi_tickets`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`glpi_tickets`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY realname, firstname, name";

      $result = $DB->query($query);
      $tab    = array();

      if ($DB->numrows($result) >=1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["users_id"];
            $tmp['link'] = formatUserName($line["users_id"], $line["name"], $line["realname"],
                                          $line["firstname"], 1);
            $tab[] = $tmp;
         }
      }
      return $tab;
   }


   /** Get users which have followup assigned to  between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct users which have any followup assigned to.
   **/
    function getUsedTechTaskBetween($date1='',$date2='') {
      global $DB;

      $query = "SELECT DISTINCT `glpi_users`.`id` AS users_id,
                                `glpi_users`.`name` AS name,
                                `glpi_users`.`realname` AS realname,
                                `glpi_users`.`firstname` AS firstname
                FROM `glpi_tickets`
                LEFT JOIN `glpi_tickettasks`
                     ON (`glpi_tickets`.`id` = `glpi_tickettasks`.`tickets_id`)
                LEFT JOIN `glpi_users` ON (`glpi_users`.`id` = `glpi_tickettasks`.`users_id`)
                LEFT JOIN `glpi_profiles_users`
                     ON (`glpi_users`.`id` = `glpi_profiles_users`.`users_id`)
                LEFT JOIN `glpi_profiles`
                     ON (`glpi_profiles`.`id` = `glpi_profiles_users`.`profiles_id`) ".
                getEntitiesRestrictRequest("WHERE","glpi_tickets");

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`glpi_tickets`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`glpi_tickets`.`closedate`", $date1, $date2).") ";
      }
      $query .="     AND `glpi_profiles`.`own_ticket` = 1
                     AND `glpi_tickettasks`.`users_id` <> '0'
                     AND `glpi_tickettasks`.`users_id` IS NOT NULL
               ORDER BY realname, firstname, name";

      $result = $DB->query($query);
      $tab    = array();

      if ($DB->numrows($result) >= 1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["users_id"];
            $tmp['link'] = formatUserName($line["users_id"], $line["name"], $line["realname"],
                                          $line["firstname"], 1);
            $tab[] = $tmp;
         }
      }
      return $tab;
   }


   /** Get enterprises which have followup assigned to between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct enterprises which have any tickets assigned to.
   **/
    function getUsedSupplierBetween($date1='', $date2='') {
      global $DB,$CFG_GLPI;

      $query = "SELECT DISTINCT `glpi_suppliers`.`id` AS suppliers_id_assign,
                                `glpi_suppliers`.`name` AS name
                FROM `glpi_tickets`
                LEFT JOIN `glpi_suppliers`
                     ON (`glpi_suppliers`.`id` = `glpi_tickets`.`suppliers_id_assign`) ".
                getEntitiesRestrictRequest("WHERE", "glpi_tickets");

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`glpi_tickets`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`glpi_tickets`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY name";

      $tab    = array();
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp["id"]   = $line["suppliers_id_assign"];
            $tmp["link"] = "<a href='".$CFG_GLPI["root_doc"]."/front/supplier.form.php?id=".
                           $line["suppliers_id_assign"]."'>".$line["name"]."</a>";
            $tab[] = $tmp;
         }
      }
      return $tab;
   }


   /** Get users_ids of tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct users_ids which have tickets
   **/
    function getUsedAuthorBetween($date1='', $date2='') {
      global $DB;

      $query = "SELECT DISTINCT `glpi_users`.`id` AS users_id, `glpi_users`.`name` AS name,
                                `glpi_users`.`realname` AS realname,
                                `glpi_users`.`firstname` AS firstname
                FROM `glpi_tickets`
                LEFT JOIN `glpi_tickets_users`
                     ON (`glpi_tickets_users`.`tickets_id` = `glpi_tickets`.`id`
                         AND `glpi_tickets_users`.`type` = '".CommonITILActor::REQUESTER."')
                INNER JOIN `glpi_users` ON (`glpi_users`.`id` = `glpi_tickets_users`.`users_id`) ".
                getEntitiesRestrictRequest("WHERE", "glpi_tickets");

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`glpi_tickets`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`glpi_tickets`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY realname, firstname, name";

      $result = $DB->query($query);
      $tab    = array();
      if ($DB->numrows($result) >= 1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["users_id"];
            $tmp['link'] = formatUserName($line["users_id"], $line["name"], $line["realname"],
                                          $line["firstname"], 1);
            $tab[] = $tmp;
         }
      }
      return $tab;
   }


   /** Get recipient of tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct recipents which have tickets
   **/
    function getUsedRecipientBetween($date1='', $date2='') {
      global $DB;

      $query = "SELECT DISTINCT `glpi_users`.`id` AS user_id,
                                `glpi_users`.`name` AS name,
                                `glpi_users`.`realname` AS realname,
                                `glpi_users`.`firstname` AS firstname
                FROM `glpi_tickets`
                LEFT JOIN `glpi_users`
                     ON (`glpi_users`.`id` = `glpi_tickets`.`users_id_recipient`) ".
                getEntitiesRestrictRequest("WHERE", "glpi_tickets");

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`glpi_tickets`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`glpi_tickets`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY realname, firstname, name";

      $result = $DB->query($query);
      $tab    = array();

      if ($DB->numrows($result) >= 1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["user_id"];
            $tmp['link'] = formatUserName($line["user_id"], $line["name"], $line["realname"],
                                          $line["firstname"], 1);
            $tab[] = $tmp;
         }
      }
      return $tab;
   }


   /** Get groups which have tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct groups of tickets
   **/
    function getUsedGroupBetween($date1='', $date2='') {
      global $DB;

      $query = "SELECT DISTINCT `glpi_groups`.`id`, `glpi_groups`.`name`
                FROM `glpi_tickets`
                LEFT JOIN `glpi_groups_tickets`
                     ON (`glpi_groups_tickets`.`tickets_id` = `glpi_tickets`.`id`
                         AND `glpi_groups_tickets`.`type` = '".CommonITILActor::REQUESTER."')
                LEFT JOIN `glpi_groups`
                     ON (`glpi_groups_tickets`.`groups_id` = `glpi_groups`.`id`)".
                getEntitiesRestrictRequest(" WHERE", "glpi_tickets");

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`glpi_tickets`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`glpi_tickets`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY `glpi_groups`.`name`";

      $result = $DB->query($query);
      $tab    = array();

      if ($DB->numrows($result) >=1 ) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["id"];
            $tmp['link'] = $line["name"];
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }


   /** Get groups assigned to tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct groups assigned to a tickets
   **/
    function getUsedAssignGroupBetween($date1='', $date2='') {
      global $DB;

      $query = "SELECT DISTINCT `glpi_groups`.`id`, `glpi_groups`.`name`
                FROM `glpi_tickets`
                LEFT JOIN `glpi_groups_tickets`
                     ON (`glpi_groups_tickets`.`tickets_id` = `glpi_tickets`.`id`
                         AND `glpi_groups_tickets`.`type` = '".CommonITILActor::ASSIGN."')
                LEFT JOIN `glpi_groups`
                     ON (`glpi_groups_tickets`.`groups_id` = `glpi_groups`.`id`)".
                getEntitiesRestrictRequest(" WHERE", "glpi_tickets");

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`glpi_tickets`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`glpi_tickets`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY `glpi_groups`.`name`";

      $result = $DB->query($query);
      $tab    = array();
      if ($DB->numrows($result) >=1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["id"];
            $tmp['link'] = $line["name"];
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }


   /**
    * Get priorities of tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct priorities of tickets
   **/
    function getUsedPriorityBetween($date1='', $date2='') {
      global $DB;

      $query = "SELECT DISTINCT `priority`
                FROM `glpi_tickets` ".
                getEntitiesRestrictRequest("WHERE", "glpi_tickets");

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`glpi_tickets`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`glpi_tickets`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY `priority`";

      $result = $DB->query($query);
      $tab    = array();
      if ($DB->numrows($result) >= 1) {
         $i = 0;
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["priority"];
            $tmp['link'] = self::getPriorityName($line["priority"]);
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }


   /**
    * Get urgencies of tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct priorities of tickets
   **/
    function getUsedUrgencyBetween($date1='', $date2='') {
      global $DB;

      $query = "SELECT DISTINCT `urgency`
                FROM `glpi_tickets` ".
                getEntitiesRestrictRequest("WHERE","glpi_tickets");

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`glpi_tickets`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`glpi_tickets`.`closedate`",$date1,$date2).") ";
      }
      $query .= " ORDER BY `urgency`";

      $result = $DB->query($query);
      $tab    = array();

      if ($DB->numrows($result) >= 1) {
         $i = 0;
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["urgency"];
            $tmp['link'] = self::getUrgencyName($line["urgency"]);
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }


   /**
    * Get impacts of tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct priorities of tickets
   **/
    function getUsedImpactBetween($date1='', $date2='') {
      global $DB;

      $query = "SELECT DISTINCT `impact`
                FROM `glpi_tickets` ".
                getEntitiesRestrictRequest("WHERE","glpi_tickets");

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`glpi_tickets`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`glpi_tickets`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY `impact`";
      $result = $DB->query($query);
      $tab    = array();

      if ($DB->numrows($result) >= 1) {
         $i = 0;
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["impact"];
            $tmp['link'] = self::getImpactName($line["impact"]);
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }


   /**
    * Get request types of tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct request types of tickets
   **/
    function getUsedRequestTypeBetween($date1='', $date2='') {
      global $DB;

      $query = "SELECT DISTINCT `requesttypes_id`
                FROM `glpi_tickets` ".
                getEntitiesRestrictRequest("WHERE","glpi_tickets");

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`glpi_tickets`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`glpi_tickets`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY `requesttypes_id`";

      $result = $DB->query($query);
      $tab    = array();
      if ($DB->numrows($result) >= 1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["requesttypes_id"];
            $tmp['link'] = Dropdown::getDropdownName('glpi_requesttypes', $line["requesttypes_id"]);
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }


   /**
    * Get solution types of tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    *
    * @return array contains the distinct request types of tickets
   **/
    function getUsedSolutionTypeBetween($date1='', $date2='') {
      global $DB;

      $query = "SELECT DISTINCT `ticketsolutiontypes_id`
                FROM `glpi_tickets` ".
                getEntitiesRestrictRequest("WHERE","glpi_tickets");

      if (!empty($date1) || !empty($date2)) {
         $query .= " AND (".getDateRequest("`glpi_tickets`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`glpi_tickets`.`closedate`", $date1, $date2).") ";
      }
      $query .= " ORDER BY `ticketsolutiontypes_id`";

      $result = $DB->query($query);
      $tab    = array();
      if ($DB->numrows($result) >=1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line["ticketsolutiontypes_id"];
            $tmp['link'] = Dropdown::getDropdownName('glpi_ticketsolutiontypes',
                                                     $line["ticketsolutiontypes_id"]);
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }


   /** Get recipient of tickets between 2 dates
    *
    * @param $date1 date : begin date
    * @param $date2 date : end date
    * @param title : indicates if stat if by title (true) or type (false)
    *
    * @return array contains the distinct recipents which have tickets
   **/
    function getUsedUserTitleOrTypeBetween($date1='', $date2='', $title=true) {
      global $DB;

      if ($title) {
         $table = "glpi_usertitles";
         $field = "usertitles_id";
      } else {
         $table = "glpi_usercategories";
         $field = "usercategories_id";
      }

      $query = "SELECT DISTINCT `glpi_users`.`$field`
                FROM `glpi_tickets`
                INNER JOIN `glpi_tickets_users`
                     ON (`glpi_tickets`.`id` = `glpi_tickets_users`.`tickets_id`
                         AND `glpi_tickets_users`.`type` = '".CommonITILActor::REQUESTER."')
                INNER JOIN `glpi_users` ON (`glpi_users`.`id` = `glpi_tickets_users`.`users_id`)
                LEFT JOIN `$table` ON (`$table`.`id` = `glpi_users`.`$field`) ".
                getEntitiesRestrictRequest("WHERE","glpi_tickets");

      if (!empty($date1)||!empty($date2)) {
         $query .= " AND (".getDateRequest("`glpi_tickets`.`date`", $date1, $date2)."
                          OR ".getDateRequest("`glpi_tickets`.`closedate`", $date1, $date2).") ";
      }
      $query .=" ORDER BY `glpi_users`.`$field`";

      $result = $DB->query($query);
      $tab    = array();
      if ($DB->numrows($result) >=1) {
         while ($line = $DB->fetch_assoc($result)) {
            $tmp['id']   = $line[$field];
            $tmp['link'] = Dropdown::getDropdownName($table, $line[$field]);
            $tab[]       = $tmp;
         }
      }
      return $tab;
   }




}
?>
