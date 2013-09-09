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
 * HTML-Objekt, dient als Elternklasse vieler anderer Klassen dieses Projektes
 * und stellt rudimentäre Funktionen bereit
 *
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Utils
 */
abstract class HObject {

   /**
    * Zeitpunkt der Erstellung
    * @var integer 
    */
   protected $ctime;

   /**
    * Zeitpunkt der letzten Bearbeitung
    * @var integer 
    */
   protected $mtime;

   /**
    * Objekt-ID
    * @var integer
    */
   protected $ID;

   /**
    * Konstruktor dieser Klasse
    * 
    * @param integer $ctime Zeitpunkt der Erstellung
    * @param integer $mtime Zeitpunkt der letzten Bearbeitung
    * @param integer $id ID
    */
   public function __construct($ctime, $mtime, $id) {
      $this->ctime = $ctime;
      $this->mtime = $mtime;
      $this->ID = $id;
   }

   /**
    * Erzeugt den HTML-Code des HObject-Objektes, abhängig vom Methodenstring
    * 
    * @param string $method Methodenstring
    * @return string HTML-Code 
    */
   public abstract function createHTML($method = 'default');

   /**
    * Gibt die ID zurück
    * 
    * @return integer ID 
    */
   public function getID() {
      return $this->ID;
   }

   /**
    * Gibt den Erstellzeitpunkt zurück
    * 
    * @return integer Erstellzeitpunkt
    */
   public function getCTime() {
      return $this->ctime;
   }

   /**
    * Gibt den Zeitpunkt der letzten Bearbeitung zurück
    * 
    * @return integer Zeitpunkt der letzten Bearbeitung
    */
   public function getMTime() {
      return $this->mtime;
   }

   /**
    * Setzt den Zeitpunkt der letzten Bearbeitung auf den übergebenen
    * 
    * @param integer $time Zeitpunkt der letzten Bearbeitung, default: aktueller
    * Zeitpunkt
    */
   public function setMTime($time = -1) {
      if ($time == -1) {
         $this->mtime = time();
      } else {
         $this->mtime = $time;
      }
   }

   /**
    * Gibt zurück, ob das HObject-Objekt schon einmal editiert wurde
    * 
    * @return boolean true, wenn schon einmal editiert
    */
   public function isModified() {
      return abs($this->mtime - $this->ctime) > 2;
   }

   /**
    * Erzeugt den Default-HTML-Code und gibt ihn zurück, Alias für die
    * <u>HObject->createHTML()</u> Methode
    * 
    * @return string Default-HTML-Code
    */
   public function __toString() {
      return $this->createHTML();
   }

}

?>
