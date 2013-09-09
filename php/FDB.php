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
 * Kapselt die Erzeugung eines Datenbankverbindungsobjekts, das in ihr als 
 * statische Eigenschaft vorhanden ist.
 *
 * Anwendungbeispiel:
 * <code>
 * <?php
 * FDB::connect();
 * $result = FDB::$db->query('SELECT * FROM forum');
 * ?>
 * </code>
 * 
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @see mysqli
 * @package SampuSnustu
 * @subpackage Utils
 */
class FDB {

   /**
    * Datenbankverbindungsobjekt
    * @var mysqli
    */
   public static $db = null;

   /**
    * Erstellt ein Datenbankobjekt und damit eine Datenbankverbindung, sofern 
    * nicht schon eine solche existiert, und macht sonst noch sinnvolle Dinge
    */
   public static function connect() {
      if (self::$db == null) {
         $server = "localhost";
         $username = "root";
         $database = "sampusnustu";
         $password = "";
         self::$db = @new mysqli($server, $username, $password, $database);
      }
   }

   /**
    * Schließt die Datenbankverbindung
    */
   public static function close() {
      if (self::$db != null) {
         self::$db->close();
         self::$db = null;
      }
   }

}

?>
