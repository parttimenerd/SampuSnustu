<?php

/*
 * Copyright (C) 2012 Julian
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of UserList
 *
 * @author Julian
 */
require_once "FSystem.php";

class UserList implements Page {

   /**
    * Erzeugt den kompletten HTML-Code für die UserEdit-Seite.
    * @param type $method
    * @return string 
    */
   public function createPageContentHTML($method = "default") {
      if ($method == "default") {
         return $this->createListHTML();
      } else if ($method == "delete") {
         return $this->deleteHTML() . $this->createListHTML();
      }
   }

   public function getPageTitle() {
      return FSystem::buildPageTitle("User Liste");
   }

   /**
    * Erzeugt den HTML-Code für die Liste
    * @return string 
    */
   public function createListHTML() {
      $destination = FSystem::URL . 'useredit.php';
      $user = new User(1);
      if ($user->isAdmin()) {
         $html = "<h1>Admin: Userverwaltung</h1>";
         $html .= "<form action='' method='get'>";
         $html .= "<table class='UserTableHTML' >";
         FDB::connect();
         $result = FDB::$db->query("SELECT * FROM user") or die(mysqli_error());

         $html .= "<tr>";
         $html .= "<th>ID</th>";
         $html .= "<th></th>";
         $html .= "<th>Name</th>";
         $html .= "<th>E-Mail</th>";
         $html .= "<th>Admin</th>";
         $html .= "<th>Aktiv</th>";
         $html .= "<th>View</th>";
         $html .= "<th>Edit</th>";
         $html .= "</tr>";
         while ($row = mysqli_fetch_array($result)) {
            $html .= "<tr>";
            $html .= "<td>" . $row['id'] . "</td>";
            $html .= "<td>" . "<input type='radio' name='delete' value='" . $row['id'] . "' />" . "</td>";
            $html .= "<td>" . $row['name'] . "</td>";
            $html .= "<td>" . $row['mailadress'] . "</td>";
            $html .= "<td>" . $row['isadmin'] . "</td>";
            $html .= "<td>" . $row['active'] . "</td>";
            $html .= "<td><a href='user.php?id=" . $row['id'] . "&method=data" . FSystem::getURLAppendix() . "'>" . "View" . "</a></td>";
            $html .= "<td><a href='" . $destination . "?id=" . $row['id'] . "&method=adminedit" . FSystem::getURLAppendix() . "'>" . "Edit" . "</a></td>";
            $html .= "</tr>";
         }
         $html .= "</table>";
         $html .= "<p><a href='register.php' >Neuer User</a></p>";
         $html .= "<input type='submit' name='method' value='delete'>";
         $html .= "</form>";
      } else {
         $html = "<span class='error'> Sie haben keine Berechtigung die Userliste einzusehen.</span>";
      }
      return $html;
   }

   /**
    * Löscht einen User und gibt den HTML-Code dazu aus.
    * @return string 
    */
   public function deleteHTML() {
      if (isset($_REQUEST['delete'])) {
         if ($_REQUEST['delete'] != 1) {
            FDB::connect();
            FDB::$db->query("DELETE FROM user WHERE id = " . FSystem::filter($_REQUEST['delete']));
            return "<span class='success'>L&ouml;schvorgang erfolgreich.</span>";
         } else {
            return "<span class='error'>Der root ist gegen L&ouml;schen gesch&uuml;tzt</span>";
         }
      } else {
         return "<span class='error'>Keine User-ID angegeben.</span> ";
      }
   }

}

?>
