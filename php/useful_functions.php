<?php

/*
 * Copyright (C) 2011-2012 Johannes Bechberger
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
 * Sinnvolle Funktionen, die nicht in dem Projekt an sich verwendet werden, jedoch
 * sinnvoll für die Integration in bestehende Projekte sein können, da sie nur
 * die FDB.php benötigen und unabhängig von allen anderen Dateien des Projekts sind.
 * 
 * @package SampuSnustu
 * @subpackage Utils
 */
require_once 'FDB.php';

/**
 * Gibt ein Array mit den Namen der im Forum sich aktuell befindenden Benutzer zurück.
 * 
 * @return string[] String-Array
 */
function getForumUserArray() {
   $userarr = array();
   FDB::connect();
   $result = FDB::$db->query("SELECT name FROM user WHERE last_forum_time > " . (time() - 60));
   while ($line = mysqli_fetch_array($result)) {
      $userarr[] = $line["name"];
   }
   return $userarr;
}

/**
 * Gibt ein Array mit den Namen der im Chat sich aktuell befindenden Benutzer zurück.
 * 
 * @return user[] Chat-User-Array
 */
function getChatUserArray() {
   $userarr = array();
   FDB::connect();
   $result = FDB::$db->query("SELECT name FROM user WHERE last_chat_time > " . (time() - 30));
   while ($line = mysqli_fetch_array($result)) {
      $userarr[] = $line["name"];
   }
   return $userarr;
}

?>
