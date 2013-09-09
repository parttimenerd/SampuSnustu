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

require_once 'FSystem.php';

/**
 * Klasse, die eine Chat-Nachricht darstellt
 *
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Chat
 */
class Message extends HObject {

   /**
    * Inhalt der Nachricht
    * @var string
    */
   private $content;

   /**
    * Absender der Nachricht
    * @var User
    */
   private $sender;

   /**
    * Konstruktor dieser Klasse
    * 
    * @param string $content Inhalt
    * @param integer|User $sender Absender
    * @param integer $ctime Zeitpunkt der Erstellung
    * @param integer $id ID
    */
   public function __construct($content, &$sender, $ctime, $id) {
      parent::__construct($ctime, $ctime, $id);
      $this->content = $content;
      if (is_numeric($sender)) {
         $this->sender = FSystem::getUserById($sender);
      } else {
         $this->sender = $sender;
      }
   }

   /**
    * Holt eine Zeile aus dem Datenbankabfrage-Result und erzeugt daraus
    * ein Nachrichtenobjekt
    * 
    * @param mysqli_result $mysql_result Datenbankabfrage-Result
    * @return boolean|Message Nachrichtenobjekt wenn erfolgreich, ansonsten false
    */
   public static function createFromFDBResult($mysqli_result) {
      $line = mysqli_fetch_array($mysqli_result);
      if (!$line) {
         return false;
      }
      return new Message($line["content"], $line["userid"], $line["time"], $line["id"]);
   }

   /**
    * Erzeugt ein Nachrichtenobjekt mit den übergebenen Parametern und gibt es
    * zurück
    * 
    * @param string $content Inhalt
    * @param integer|User $sender Absender(-ID)
    * @param integer $ctime Zeitpunkt der Erstellung
    * @return boolean|Message Nachrichtenobjekt wenn erfolgreich, ansonsten null
    */
   public static function store($content, $sender, $ctime) {
      FDB::connect();
      if (is_numeric($sender)) {
         $sender = FSystem::getUserById($sender);
      }
      $content = FSystem::FDBFilter($content);
      //       Logger::getLogger("ChatLog").trace(substr(str_pad($sender->getName(), 11), 0, 10) . ' | ' . $content);
      $random = time() . FSystem::createSalt();
      FDB::$db->query("INSERT INTO chatmsg(id, time, user_id, content, uid) VALUES(NULL, " . $ctime . ", " . $sender->getID() . ", '" . $content . "', '" . $random . "')");
      return Message::createFromFDBResult(FDB::$db->query("SELECT * FROM chatmsg WHERE uid='" . $random . "'"));
   }

   /**
    * Erzeugt den HTML-Code und gibt ihn zurück
    * 
    * @param string $method Methodenstring, hat keine Auswirkung
    * @return string erzeugter HTML-Code 
    */
   public function createHTML($method = 'default') {
      return '<tr class="chatmsg chat" id="' . $this->getID() . '"><td class="chattime">' . date('d.m.Y H:i:s', $this->ctime) . '</td><td class="chatuser">' . $this->sender->getName() . '</td><td class="chatcontent">' . $this->content . '</td></tr>';
   }

   /**
    * Gibt den Inhalt zurück
    * 
    * @return string Inhalt
    */
   public function getContent() {
      return $this->content;
   }

   /**
    * Gibt den Absender zurück
    * 
    * @return User Absender 
    */
   public function getSender() {
      return $this->sender;
   }

}

?>
