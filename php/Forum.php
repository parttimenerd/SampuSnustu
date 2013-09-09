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
 * Forumklasse, die ein (Unter-)Forum darstellt
 *
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Forum
 */
class Forum extends FObject {

   /**
    * Erstellt ein Forum-Objekt per übergebener ID aus der Datenbank und gibt
    * es zurück
    * 
    * @param integer $id ID
    * @return Forum Forum-Objekt wenn erfolgreich, ansonsten null
    */
   public static function createByID($id) {
      FDB::connect();
      $val = FDB::$db->query('SELECT * FROM forum WHERE id=' . mysqli_real_escape_string(FDB::$db, $id));
      return self::createFromFDBResult($val);
   }

   /**
    * Löscht das Forum aus der Datenbank
    * 
    * @return boolean true wenn erfolgreich
    */
   public function delete() {
      if (FSystem::isAdmin()) {
         FDB::connect();
         FDB::$db->query('DELETE FROM forum WHERE id=' . $this->ID);
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
      if (FSystem::isAdmin() && $new_description != "") {
         FDB::connect();
         $this->description = FSystem::FDBFilter($new_description);
         FDB::$db->query('UPDATE forum SET description="' . $this->description . '" WHERE id=' . $this->ID);
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
      if (FSystem::isAdmin() && $new_parent != NULL) {
         if (is_numeric($new_parent)) {
            $this->parent = FSystem::getForumById($new_parent);
         } else {
            $this->parent = $new_parent;
         }
         FDB::$db->query('UPDATE forum SET parent="' . $this->parentgetID() . '" WHERE id=' . $this->ID);
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
      if (FSystem::isAdmin() && $new_title != "") {
         FDB::connect();
         $this->title = FSystem::FDBFilter($new_title);
         FDB::$db->query('UPDATE forum SET title="' . $this->title . '" WHERE id=' . $this->ID);
         return true;
      }
      return false;
   }

   /**
    * Holt eine Zeile aus dem Datenbankabfrage-Result und erzeugt daraus
    * ein Forum
    * 
    * @param mysqli_result $mysql_result Datenbankabfrage-Result
    * @return boolean|Forum Forum wenn erfolgreich, sonst null
    */
   public static function createFromFDBResult($mysql_result) {
      $data = mysqli_fetch_array($mysql_result);
      if ($data == null) {
         return null;
      }
      return new Forum($data["id"], intval($data["creator"]), $data["parent"], $data["title"], $data["description"], $data["ctime"], $data["mtime"]);
   }

   /**
    * Gibt ein <u>$length</u> Foren und Thread fassendes Array mit den 
    * Forenn, zurück, deren Position in diesem Forum mindestens
    * <u>$begin</u> ist (Forum-Objekte, dann Thread-Objekte)
    * 
    * @param $begin Postion des ersten Forums
    * @param $length Anzahl der Foren
    * @return Forum|Thread[] Foren und Thread fassendes Array
    */
   public function getEntries($begin, $length) {
      $entryarr = array();
      FDB::connect();
      $result = FDB::$db->query('SELECT * FROM forum WHERE parent=' . $this->ID . ' ORDER BY title LIMIT ' . $begin . ', ' . $length);
      $anz = 0;
      while ($entryarr[] = Forum::createFromFDBResult($result)) {
         $anz++;
      }
      $length -= $anz;
      $begin -= $anz;
      if ($begin < 0) {
         $begin = 0;
      }
      if ($length > 0) {
         $result = FDB::$db->query('SELECT * FROM thread WHERE parent=' . $this->ID . ' ORDER BY mtime DESC LIMIT ' . $begin . ', ' . $length);
         while ($entryarr[] = Thread::createFromFDBResult($result));
      }
      return $entryarr;
   }

   /**
    * Gibt den Seitentitel zurück
    * 
    * @return string Seitentitel
    */
   public function getPageTitle() {
      if (isset($_REQUEST["tid"]) && FSystem::getThreadById($_REQUEST["tid"])) {
         return FSystem::getThreadById($_REQUEST["tid"])->getPageTitle();
      }
      return FSystem::buildPageTitle($this->title);
   }

   /**
    * Erzeugt den HTML-Code des Forum-Objektes abhängig von dem übergebenen
    * Methodenstring und gibt ihn zurück
    * 
    * @param string $method Methodenstring
    * @return string HTML-Code
    */
   public function createHTML($method = "default") {
      if (isset($_REQUEST["login"]) && isset($_REQUEST["passwort"]) && isset($_REQUEST["username"])) {
         if (!FSystem::verifyUser($_REQUEST["username"], $_REQUEST["passwort"])) {
            $login = new Login();
            return $login->createPageContentHTML();
         } else if (isset($_REQUEST["cookie"])) {
            FSystem::updatePwdCookie(FSystem::getUserIdByName($_REQUEST["username"]), $_REQUEST["passwort"]);
         } else {
            FSystem::updateURLAppendix(FSystem::getUserIdByName($_REQUEST["username"]));
            $arr = FSystem::getURLAppendixArray();
            $_REQUEST["sid"] = $arr["sid"];
            $_REQUEST["sessionid"] = $arr["sessionid"];
         }
         FSystem::getUser(true);
      }
      if (isset($_REQUEST["tid"]) && (FSystem::getMethod() == 'default' || FSystem::getMethod() == 'show') && FSystem::getThreadById($_REQUEST["tid"])) {
         return FSystem::getThreadById($_REQUEST["tid"])->createHTML($method = "default");
      }
      $html = '';
      $spechtml = $this->createSpecificHTML($method);
      if ($method == 'default') {
         if ($method == "default") {
            $html = $this->createLocationHTML() . "<br/>\n";
         }
         $html .= '<table class = "entry_table">
            ' . $this->createDescriptionHTML() . "\n" . $spechtml;
         return $html . '</table>' . ($this->getPage() >= $this->getPageCount() && !FSystem::isDefaultUser() ? "\n" . $this->getCreateDiv() : '');
      } else {
         return $spechtml;
      }
   }

   /**
    * Erzeugt den spezifischen HTML-Code des Forums abhängig von
    * dem übergebenen Methodenstring und gibt ihn zurück
    * 
    * @param string $method Methodenstring
    * @return string HTML-Code
    */
   public function createSpecificHTML($method = "default") {
      $home = isset($_REQUEST['tid']) ? FSystem::getThreadById($_REQUEST['tid']) : FSystem::getForumById(isset($_REQUEST['fid']) ? $_REQUEST['fid'] : 1);
      switch ($method) {
         case "default":
            if (isset($_REQUEST["tid"]) && FSystem::getThreadById($_REQUEST["tid"])) {
               return FSystem::getThreadById($_REQUEST["tid"])->createSpecificHTML();
            }
            $html = '';
            $entryarr = $this->getEntries($this->getPage() * FSystem::ENTRIES_HTML_LIST_LENGTH, FSystem::ENTRIES_HTML_LIST_LENGTH);
            foreach ($entryarr as $val) {
               if ($val) {
                  $html .= $val->createSpecificHTML("info") . "\n";
               }
            }
            return $html;
         case "info": // ' . ($this->isEditable() ? $this->getEditDiv(isset($_REQUEST['fid']) ? $_REQUEST['fid'] : '1') : '') . '
            return '
                        <tr class="entry_header header">
                            <td colspan="2"><a href="' . FSystem::URL . 'forum.php?fid=' . $this->ID . '&' . FSystem::getURLAppendix() . '">' . ($this == FSystem::getMainForum() ? '' : 'Unterforum: ') . $this->title . '</a></td>
                        </tr>
                        <tr class="entry description entry_content">
                            <td class="description">
                                ' . $this->description . '
                            </td>
                            <td class="info create_time">Erstellt: ' . date("Y.m.d H:i:s", $this->ctime) . '</td>
                         </tr>';
         case "entryedit":
            if (isset($_REQUEST["eid"]) && isset($_REQUEST["content"]) && isset($_REQUEST["enote"])) {
               $entry = FSystem::getEntryById($_REQUEST["eid"]);
               if ($entry && $entry->isEditable()) {
                  $entry->setContent($_REQUEST["content"], $_REQUEST["enote"]);
               }
            }
            return $home->createHTML();
         case "show":
            if (isset($_REQUEST["tid"]) && isset($_REQUEST["eid"])) {
               $obj = FSystem::getThreadById($_REQUEST["tid"]);
               if ($obj) {
                  $obj->setPage($obj->getPageByEntryId($_REQUEST["eid"]));
                  return $obj->createHTML();
               }
               return FSystem::getForum()->createHTML();
            } else if (isset($_REQUEST["fid"])) {
               return FSystem::getForumById($_REQUEST["fid"])->createHTML();
            } else {
               return FSystem::getMainForum()->createHTML();
            }
         case "forumedit":
         case "threadedit":
            if (isset($_REQUEST["fid"]) || isset($_REQUEST["tid"])) {
               if (isset($_REQUEST["descr"]))
                  $home->setDescription($_REQUEST["descr"]);
               if (isset($_REQUEST["parent"]))
                  $home->setParent($_REQUEST["parent"]);
               if (isset($_REQUEST["title"]))
                  $home->setTitle($_REQUEST["title"]);
            }
            return $home->createHTML('default');
         case "create":
            $obj = null;
            if (isset($_REQUEST["class"]) && isset($_REQUEST["title"]) && isset($_REQUEST["descr"]) && isset($_REQUEST["fid"]) && !FSystem::isDefaultUser()) {
               if ($_REQUEST["class"] == "forum" && FSystem::isAdmin()) {
                  $obj = Forum::store(FSystem::getUser(), $_REQUEST["fid"], $_REQUEST["title"], $_REQUEST["descr"]);
               } else if ($_REQUEST["class"] == "thread") {
                  $obj = Thread::store(FSystem::getUser(), $_REQUEST["fid"], $_REQUEST["title"], $_REQUEST["descr"]);
               }
            }
            return $obj ? $obj->createHTML() : $home->createHTML();
         case "create_entry":
            if (isset($_REQUEST["tid"]) && isset($_REQUEST["content"]) && !FSystem::isDefaultUser()) {
               $entry = Entry::store($_REQUEST["content"], FSystem::getID(), $_REQUEST["tid"]);
               if ($home) {
                  $home->setPage($home->getPageByEntryId($entry->getID()));
                  return $home->createHTML();
               }
            }
            return $home->createHTML();
         case "entrydel":
            if (isset($_REQUEST["eid"])) {
               $entry = FSystem::getEntryById($_REQUEST["eid"]); #
               if ($entry && $entry->isEditable()) {
                  $entry->delete();
               }
            }
            return $home->createHTML();
         case "forumdel":
         case "threaddel":
            if (isset($_REQUEST["fid"]) || isset($_REQUEST["tid"])) {
               $obj = !isset($_REQUEST["tid"]) ? FSystem::getForumById($_REQUEST["fid"]) : FSystem::getThreadById($_REQUEST["tid"]);
               if ($obj && $obj != FSystem::getMainForum()) {
                  $parent = $obj->getParent();
                  $obj->delete();
                  return $parent->createHTML('default');
               }
            }
            return $home->createHTML('default');
      }
   }

   /**
    * Gibt die aktuelle URL zurück 
    * 
    * @return string aktuelle URL
    */
   public function getURL() {
      return FSystem::URL . 'forum.php?fid=' . $this->ID . '&' . FSystem::getURLAppendix();
   }

   /**
    * Gibt den spezifischen Headertext des Beschreibung-HTML-Code-Div zurück
    * 
    * @return string Beschreibung-HTML-Code-Div-Headertext
    */
   public function getSpecificDescriptionHeader() {
      return "Beschreibung des Forums";
   }

   /**
    * Gibt die spezifische Seitenanzahl zurück
    * 
    * @return integer spezifische Seitenanzahl 
    */
   public function getSpecificPageCount() {
      FDB::connect();
      $fanzres = FDB::$db->query('SELECT count(*) AS anz FROM forum WHERE parent="' . $this->ID);
      $tanzres = FDB::$db->query('SELECT count(*) AS anz FROM thread WHERE parent="' . $this->ID);
      $anz = $fanzres ? mysqli_fetch_object($fanzres)->anz : 0;
      $anz += $fanzres ? mysqli_fetch_object($tanzres)->anz : 0;
      return ceil($anz / FSystem::ENTRIES_HTML_LIST_LENGTH);
   }

   /**
    * Gibt den Untergeordnetes-Forum-und-Thread-Erzeugen-HTML-Code dieses 
    * Forums zurück, abhängig vom aktuelle Benutzer
    * 
    * @return string Untergeordnetes-Forum-und-Thread-Erzeugen-HTML-Code-Div
    */
   public function getCreateDiv() {
      return (FSystem::isAdmin() ? '<div class="textbox create_forum">
            <span class="text">Neues Forum hinzuf&uuml;gen</span><br/>
            <form class="edit_div" action="forum.php" method="POST">
                <table>
                    <tr><td class="create_header">Neues Forum hinzuf&uuml;gen</td></tr>
                    <tr><td><input type="text" name="title"/></td></tr>
                    <tr><td><textarea name="descr"></textarea></td></tr>
                    <tr>
                        <td><input type="reset" value="Reset"/>
                            <input type="submit" value="Abschicken"/></td>
                    </tr>
                </table>
                <input type="hidden" name="method" value="create"/>
                <input type="hidden" name="fid" value="' . $this->getID() . '"/>
                <input type="hidden" name="class" value="forum"/>
                ' . FSystem::getFormAppendix() . FSystem::getFormAppendix() . '
            </form>
        </div>' : '') . (!FSystem::isDefaultUser() ? '<div class="textbox create_thread">
            <span class="text">Neuen Thread hinzuf&uuml;gen</span><br/>
            <form class="edit_div" action="forum.php" method="POST">
                <table>
                    <tr><td class="create_header">Neuen Thread hinzuf&uuml;gen</td></tr>
                    <tr><td><input type="text" name="title"/></td></tr>
                    <tr><td><textarea name="descr"></textarea></td></tr>
                    <tr>
                        <td><input type="reset" value="Reset"/>
                            <input type="submit" value="Abschicken"/></td>
                    </tr>
                </table>
                <input type="hidden" name="method" value="create"/>
                <input type="hidden" name="class" value="thread"/>
                <input type="hidden" name="fid" value="' . $this->getID() . '"/>
                ' . FSystem::getFormAppendix() . FSystem::getFormAppendix() . '
            </form>
        </div>' : '');
   }

   /* public function getLastEntries($num = FSystem::ENTRIES_HTML_LIST_LENGTH) {
     $entryarr = array();
     $result = FDB::$db->query('SELECT * FROM threadentry WHERE threadid="' . $this->ID . 'ORDER BY title ORDER BY id DESC LIMIT ' . $num);
     while ($entryarr[] = Entry::createFromFDBResult($result));
     return $entryarr;
     } */

   /**
    * Speichert ein neues Forum in der Datenbank und gibt es zurück
    * 
    * @param integer|User $creator Ersteller(-ID)
    * @param integer|Forum $parent Elternforum-Objekt(-ID)
    * @param string $title Titel
    * @param string $description Beschreibung
    * @return null|Forum Forum-Objekt wenn erfolgreich, ansonsten null
    */
   public static function store($creator, $parent, $title, $description) {
      if (FSystem::isAdmin()) {
         if (is_numeric($creator)) {
            $creator = FSystem::getUserById($creator);
         }
         if (is_numeric($parent)) {
            $parent = FSystem::getForumById($parent);
         }
         $uid = FSystem::createUID();
         FDB::connect();
         FDB::$db->query('INSERT INTO forum(id, title, description, parent, ctime, mtime, uid, creator) VALUES(NULL, "' . FSystem::FDBFilter($title) . '", "' . FSystem::FDBFilter($description) . '", ' . ($parent ? $parent->getID() : '1') . ', ' . time() . ', ' . time() . ', "' . $uid . '", ' . $creator->getID() . ')');
         return Forum::createFromFDBResult(FDB::$db->query('SELECT * FROM forum WHERE uid="' . $uid . '"'));
      }
      return null;
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
      return '<input type="hidden" name="fid" value="' . $this->getID() . '"/>
            ' . ($fid ? '<input type="hidden" name="jumpfid" value="' . $fid . '"/>' : '') . '
            <input type="hidden" name="page" value="' . FSystem::getPage() . '"/>';
   }

   /**
    * Gibt den HTML-Code zurück, der im Forum-Löschen-Formular
    * hinzugefügt werden muss
    * 
    * @param string $fid Forum-ID, wenn in ein bestimmtes Forum, nachfolgend
    * gegangen werden soll
    * @return string HTML-Code
    */
   public function getDeleteFormAppendix($fid = '') {
      return '<input type="hidden" name="method" value="forumdel"/>
            <input type="hidden" name="fid" value="' . $this->getID() . '"/>
            ' . ($fid ? '<input type="hidden" name="jumpfid" value="' . $fid . '"/>' : '') . '
            <input type="hidden" name="page" value="' . FSystem::getPage() . '"/>';
   }

}

?>
