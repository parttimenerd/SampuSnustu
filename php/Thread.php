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
 * Thread-Klasse
 * 
 * Ein Thread entspricht einem Forenthema
 *
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Forum
 */
class Thread extends FObject {

   /**
    * Erstellt ein Thread-Objekt per übergebener ID aus der Datenbank und gibt
    * es zurück
    * 
    * @param integer $id ID
    * @return Thread Thread-Objekt wenn erfolgreich, ansonsten null
    */
   public static function createByID($id) {
      FDB::connect();
      return self::createFromFDBResult(FDB::$db->query('SELECT * FROM thread WHERE id=' . FDB::$db->real_escape_string($id)));
   }

   /**
    * Löscht das Thread-Objekt aus der Datenbank
    * 
    * @return boolean true wenn erfolgreich
    */
   public function delete() {
      if ($this->isEditable()) {
         FDB::connect();
         FDB::$db->query('DELETE FROM thread WHERE id=' . $this->ID);
         return true;
      }
      return false;
   }

   /**
    * Ersetzt die aktuelle Beschreibung durch die übergebene
    * 
    * @param string $new_description neue Beschreibung 
    * @return boolean true wenn erfolgreich
    */
   public function setDescription($new_description) {
      if ($this->isEditable() && $new_description != "") {
         FDB::connect();
         $this->description = FSystem::FDBFilter($new_description);
         FDB::$db->query('UPDATE thread SET description="' . $this->description . '" WHERE id=' . $this->ID);
         $this->setMTime();
         return true;
      }
      return false;
   }

   /**
    * Ersetzt das alte Elternforum durch das übergebene
    * 
    * @param Forum $new_parent neues Elternforum
    * @return boolean true wenn erfolgreich
    */
   public function setParent($new_parent) {
      if ($this->isEditable() && $new_parent != null) {
         if (is_numeric($new_parent)) {
            $this->parent = FSystem::getForumById($new_parent);
         } else {
            $this->parent = $new_parent;
         }
         FDB::$db->query('UPDATE thread SET parent="' . $this->parentgetID() . '" WHERE id=' . $this->ID);
         $this->setMTime();
         return true;
      }
      return false;
   }

   /**
    * Ersetzt den aktuellen Titel durch den übergebenen
    * 
    * @param string $new_title neuer Titel 
    * @return boolean true wenn erfolgreich
    */
   public function setTitle($new_title) {
      if ($this->isEditable() && $new_title != "") {
         FDB::connect();
         $this->title = FSystem::FDBFilter($new_title);
         FDB::$db->query('UPDATE thread SET title="' . $this->title . '" WHERE id=' . $this->ID);
         $this->setMTime();
         return true;
      }
      return false;
   }

   /**
    * Setzt den Zeitpunkt der letzten Veränderung
    * 
    * @param integer $time Zeitpunkt, default: aktueller Zeitpunkt
    */
   public function setMTime($time = -1) {
      if ($time == -1) {
         $time = time();
      }
      $this->mtime = $time;
      FDB::connect();
      FDB::$db->query('UPDATE thread SET mtime=' . $time . ' WHERE id=' . $this->ID);
   }

   /**
    * Holt eine Zeile aus dem Datenbankabfrage-Result und erzeugt daraus
    * einen Thread
    * 
    * @param mysqli_result $mysql_result Datenbankabfrage-Result
    * @return boolean|Thread Thread wenn erfolgreich, sonst null
    */
   public static function createFromFDBResult($mysql_result) {
      if (!$mysql_result) {
         return null;
      }
      $data = mysqli_fetch_array($mysql_result);
      if ($data == null) {
         return null;
      }
      return new Thread($data["id"], intval($data["creator"]), $data["parent"], $data["title"], $data["description"], $data["ctime"], $data["mtime"]);
   }

   /**
    * Erzeugt den spezifischen HTML-Code des Threads abhängig von
    * dem übergebenen Methodenstring und gibt ihn zurück
    * 
    * @param string $method Methodenstring
    * @return string HTML-Code
    */
   public function createSpecificHTML($method = "default") {
      switch ($method) {
         case "default":
//                $html = $this->createDescriptionHTML() . "\n";
            $html = '';
            $num = $this->getPage() * FSystem::ENTRIES_HTML_LIST_LENGTH;
            foreach ($this->getEntries($num, FSystem::ENTRIES_HTML_LIST_LENGTH) as $val) {
               if ($val) {
                  $html .= $val->createHTML('default', $num);
                  $num++;
               }
            }
            return $html;
            break;
         case "info":
            $entry = $this->getLastEntry(); //' . ($this->isEditable() ? $this->getEditDiv(!isset($_REQUEST['tid']) ? (isset($_REQUEST['fid']) ? $_REQUEST['fid'] : 1) : '') : '') . '
            $beitraghtml = 'Beitr&auml;ge: ' . ($this->getEntryCount() == 0 ? '-' : ($this->getEntryCount() == 1 ? 'ein Beitrag' : $this->getEntryCount() . ' Beitr&auml;ge'));
            return '<tr class="header">
                            <td colspan="2"><a href="' . FSystem::URL . 'forum.php?tid=' . $this->ID . '&' . FSystem::getURLAppendix() . '">' . $this->title . '</a></td>
                        </tr>
                        <tr class="forum_info">
                            <td class="description">
                                ' . $this->description . '
                            </td>
                            <td class="info">
                                <span class="create_time"> Erstellt: ' . date("Y.m.d H:i:s", $this->ctime) . '</span><br/>
                                <span class="count"> ' . $beitraghtml . '</span><br/>
                                <span class="last_entry"> Letzter Beitrag: ' . ($entry ? date("Y.m.d H:i:s", $entry->getCTime()) : '-') . '</span><br/>
                             </td>
                         </tr>';
      }
   }

   /**
    * Gibt die aktuelle URL zurück 
    * 
    * @return string aktuelle URL
    */
   public function getURL() {
      return FSystem::URL . 'forum.php?tid=' . $this->ID . '&' . FSystem::getURLAppendix();
   }

   /**
    * Gibt den zu letzt in diesem thread geschriebenen Eintrag zurück
    * 
    * @return Entry Eintrag-Objekt
    */
   public function getLastEntry() {
      FDB::connect();
      $result = FDB::$db->query('SELECT * FROM threadentry WHERE threadid=' . $this->ID . ' ORDER BY id DESC LIMIT 0, 1');
      return Entry::createFromFDBResult($result);
   }

   /**
    * Gibt ein <u>$length</u> Einträge fassendes Array mit den Einträgen, zurück,
    * deren Position im Thread mindestens <u>$begin</u> ist
    * 
    * @param $begin Postion des ersten Eintrages im Thread
    * @param $length Anzahl der Einträge
    * @return Entry[] Einträge-Array
    */
   public function getEntries($begin, $length) {
      $entryarr = array();
      FDB::connect();
      $result = FDB::$db->query('SELECT * FROM threadentry WHERE threadid=' . $this->ID . ' LIMIT ' . $begin . ', ' . $length . '');
      while ($entryarr[] = Entry::createFromFDBResult($result));
      return $entryarr;
   }

   /**
    * Gibt zurück, ob der Thread durch den aktuellen Benutzer editiert werden kann
    * 
    * @return boolean true wenn editierbar durch den aktuellen Benutzer
    */
   public function isEditable() {
      return FSystem::isAdmin() || (intval(FSystem::getUser()->getID()) == intval($this->user->getID()));
   }

   /**
    * Gibt den spezifischen Headertext des Beschreibung-HTML-Code-Divs zurück
    * 
    * @return string Beschreibung-HTML-Code-Div-Headertext
    */
   public function getSpecificDescriptionHeader() {
      return "Beschreibung des Threads";
   }

   /**
    * Gibt die spezifische Seitenanzahl zurück
    * 
    * @return integer spezifische Seitenanzahl 
    */
   public function getSpecificPageCount() {
      FDB::connect();
      $tanzres = FDB::$db->query('SELECT count(*) AS anz FROM threadentry WHERE threadid=' . $this->ID);
      return $tanzres ? ceil(mysqli_fetch_object($tanzres)->anz / FSystem::ENTRIES_HTML_LIST_LENGTH) : 0;
   }

   /**
    * Gibt den Antwort-Schreiben-HTML-Code dieses 
    * Threads zurück
    * 
    * @return string Antwort-Schreiben-HTML-Code-Div
    */
   public function getCreateDiv() {
      return '<div class="textbox create_entry">
            <span class="text">Beitrag schreiben</span><br/>
            <form class="edit_div" action="forum.php" method="POST">
                <table>
                    <tr><td class="create_header">Beitrag schreiben</td></tr>
                    <tr><td><textarea name="content" class="new_entry_area"></textarea></td></tr>
                    <tr>
                        <td><input type="reset" value="Reset"/>
                            <input type="submit" value="Abschicken"/></td>
                    </tr>
                </table>
                <input type="hidden" name="method" value="create_entry"/>
                <input type="hidden" name="tid" value="' . $this->getID() . '"/>
                <input type="hidden" name="page" value="' . FSystem::getPage() . '"/>                
                ' . FSystem::getFormAppendix() . '
            </form>
        </div>';
   }

   /**
    * Gibt die zu letzt geschriebenen Einträge als Array zurück
    * 
    * @param integer $num Länge des Arrays
    * @return Entry[] Eintrag-Objekt-Array
    */
   public function getLastEntries($num = FSystem::ENTRIES_HTML_LIST_LENGTH) {
      $entryarr = array();
      $result = FDB::$db->query('SELECT * FROM threadentry WHERE threadid=' . $this->ID . 'ORDER BY title ORDER BY id DESC LIMIT ' . $num);
      while ($entryarr[] = Entry::createFromFDBResult($result));
      return $entryarr;
   }

   /**
    * Speichert ein neues Thread-Objekt in der Datenbank und gibt es zurück
    * 
    * @param User $creator Ersteller
    * @param integer|Forum $parent Elternforum-Objekt(-ID)
    * @param string $title Titel
    * @param string $description Beschreibung
    * @return null|Thread Thread-Objekt wenn erfolgreich, ansonsten null
    */
   public static function store($creator, $parent, $title, $description) {
      if (is_numeric($creator)) {
         $creator = FSystem::getUserById($creator);
      }
      if (is_numeric($parent)) {
         $parent = FSystem::getForumById($parent);
      }
      $uid = FSystem::createUID();
      FDB::connect();
      FDB::$db->query('INSERT INTO thread(id, title, description, parent, ctime, mtime, uid, creator) VALUES(NULL, "' . FSystem::FDBFilter($title) . '", "' . FSystem::FDBFilter($description) . '", ' . ($parent ? $parent->getID() : '-1') . ', ' . time() . ', ' . time() . ', "' . $uid . '", ' . $creator->getID() . ')');
      return Thread::createFromFDBResult(FDB::$db->query('SELECT * FROM thread WHERE uid="' . $uid . '"'));
   }

   /**
    * Gibt die Anzahl der Einträge zurück, die in diesem Thread enthalten sind
    * 
    * @return integer Anzahl der Einträge
    */
   public function getEntryCount() {
      if (!$this->entry_count) {
         $this->entry_count = mysqli_fetch_object(FDB::$db->query('SELECT count(*) AS anz FROM threadentry WHERE threadid=' . $this->ID))->anz;
      }
      return $this->entry_count;
   }

   /**
    * Gibt die Seitennummer zurück, auf der sich der Eintrag mit der übergebenen
    * ID befindet
    * 
    * @param $id ID des Eintrages
    * @return integer Seitennummer (die erste Seite hat die Seitennummer 0) wenn
    * erfolgreich, ansonsten 0
    */
   public function getPageByEntryId($id) {
      if ($id) {
         $count = mysqli_fetch_object(FDB::$db->query('SELECT count(*) AS anz FROM threadentry WHERE threadid=' . $this->ID . ' AND id <= ' . FDB::$db->real_escape_string($id)))->anz;
         return $count % FSystem::ENTRIES_HTML_LIST_LENGTH;
      }
      return 0;
   }

   /**
    * Gibt den HTML-Code zurück, der im Editier-HTML-Code-Div-Formular
    * hinzugefügt werden muss
    * 
    * @param string $fid Forum-ID, wenn in ein bestimmtes Forum, nachfolgend
    * gegangen werden soll
    * @return string HTML-Code
    */
   public function getEditFormAppendix($fid = '') {
      return '<input type="hidden" name="method" value="threadedit"/>
            <input type="hidden" name="tid" value="' . $this->getID() . '"/>
            ' . ($fid ? '<input type="hidden" name="jumpfid" value="' . $fid . '"/>' : '') . '
            <input type="hidden" name="page" value="' . FSystem::getPage() . '"/>';
   }

   /**
    * Gibt den HTML-Code zurück, der im Thread-Löschen-Formular
    * hinzugefügt werden sollte
    * 
    * @param string $fid Forum-ID, wenn in ein bestimmtes Forum, nachfolgend
    * gegangen werden soll
    * @return string HTML-Code
    */
   public function getDeleteFormAppendix($fid = '') {
      return '<input type="hidden" name="method" value="threaddel"/>
            <input type="hidden" name="tid" value="' . $this->getID() . '"/>
            ' . ($fid ? '<input type="hidden" name="jumpfid" value="' . $fid . '"/>' : '') . '
            <input type="hidden" name="page" value="' . FSystem::getPage() . '"/>';
   }

}

?>
