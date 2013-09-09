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
 * Forumeintrag-Klasse
 * 
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Forum
 */
class Entry extends HObject {

   /**
    * Ersteller dieses Eintrags
    * @var User
    */
   private $user;

   /**
    * Inhalt dieses Eintrags
    * @var string
    */
   private $content;

   /**
    * Thread, in dem dieser Eintrag geschrieben wurde
    * @var Thread
    */
   private $thread;

   /**
    * Begründung der Veränderung des Eintrags, bei einer solchen Veränderung
    * durch den jeweiligen Ersteller bzw. Admin
    * @var string
    */
   private $edit_note;

   /**
    * Konstruktor der Entry-Klasse
    *
    * @param string $content Inhalt
    * @param integer $id ID
    * @param integer|User $user Ersteller(-ID)
    * @param integer|Thread $thread (ID des) Thread, in dem der Eintrag geschrieben wurde
    * @param integer $ctime Unix-Zeit der Erstellung
    * @param integer $mtime Unix-Zeit der letzen Veränderung
    * @param string $edit_note Begründung der Veränderung des Eintrags, bei
    * einer solchen Veränderung durch den jeweiligen Ersteller bzw. Admin
    */
   public function __construct($content, $id, &$user, &$thread, $ctime, $mtime, $edit_note) {
      parent::__construct($ctime, $mtime, $id);
      if (is_numeric($user)) {
         $this->user = FSystem::getUserById($user);
      } else {
         $this->user = $user;
      }
      if (is_numeric($thread)) {
         $this->thread = FSystem::getForumById($thread);
      } else {
         $this->thread = $thread;
      }
      $this->content = $content;
      $this->edit_note = $edit_note;
   }

   /**
    * Holt den Eintrag mit der korrespondierenden ID aus der Datenbank und gibt
    * ihn zurück
    * 
    * @param integer $id ID
    * @return Entry Entry-Objekt bei Erfolg, ansonsten null 
    */
   public static function createByID($id) {
      FDB::connect();
      $result = FDB::$db->query("SELECT * FROM threadentry WHERE id=" . FDB::$db->real_escape_string($id));
      return Entry::createFromFDBResult($result);
   }

   /**
    * Holt den Eintrag mit der korrespondierenden UID aus der Datenbank und gibt
    * ihn zurück
    * 
    * @param integer $id UID
    * @return Entry Entry-Objekt bei Erfolg, ansonsten null 
    */
   public static function createByUID($uid) {
      FDB::connect();
      $result = FDB::$db->query("SELECT * FROM threadentry WHERE uid=" . FDB::$db->real_escape_string($uid));
      return Entry::createFromFDBResult($result);
   }

   /**
    * Gibt den Inhalt dieses Eintrages zurück
    * 
    * @return string Inhalt
    */
   public function getContent() {
      return $this->content;
   }

   /**
    * Ersetzt den aktuellen Inhalt des Eintrages mit dem neuen und fügt die
    * Editiernotiz hinzu
    * 
    * @param string $new_content neuer Inhalt
    * @param string $edit_note Editiernotiz
    * @return boolean true, wenn erfolgreich
    */
   public function setContent($new_content, $edit_note) {
      if ($this->isEditable()) {
         FDB::connect();
         $this->content = FSystem::FDBFilter($new_content);
         $this->edit_note = FSystem::FDBFilter($edit_note);
         $this->setMTime(time());
         FDB::$db->query('UPDATE threadentry SET content="' . $this->content . '", editnote="' . $this->edit_note . '", mtime=' . $this->getMTime() . ' WHERE id=' . $this->getID());
         $this->thread->setMTime();
         return true;
      }
      return false;
   }

   /**
    * Löscht den Eintrag aus der Datenbank
    * 
    * @return boolean true, wenn erfolgreich
    */
   public function delete() {
      if ($this->isEditable()) {
         FDB::$db->query("DELETE FROM threadentry WHERE id=" . $this->getID());
         return true;
      }
      return false;
   }

   /**
    * Gibt die Editiernotiz zurück
    * 
    * @return string Editiernotiz 
    */
   public function getEditNote() {
      return $this->edit_note;
   }

   /**
    * Holt eine Zeile aus dem Datenbankabfrage-Result und erzeugt daraus
    * ein Eintragobjekt
    * 
    * @param mysqli_result $mysql_result Datenbankabfrage-Result
    * @return boolean|Entry Eintragobjekt wenn erfolgreich, sonst false
    */
   public static function createFromFDBResult($mysql_result) {
      if (!$mysql_result) {
         return false;
      }
      $line = mysqli_fetch_array($mysql_result);
      if (!$line) {
         return false;
      }
      return new Entry($line["content"], $line["id"], $line["creator"], Thread::createByID($line["threadid"]), $line["ctime"], $line["mtime"], $line["editnote"]);
   }

   /**
    * Erzeugt ein Eintragobjekt mit den übergebenen Parametern und gibt es zurück
    * 
    * @param string $content Inhalt
    * @param integer|User $user ID des Benutzers bzw. Benutzerobjekt
    * @param integer|Thread $thread ID des Threads bzw. Threadobjekt
    * @return boolean|Entry Eintrag-Objekt wenn erfolgreich, sonst null
    */
   public static function store($content, $user, $thread) {
      FDB::connect();
      $time = time();
      $uid = FSystem::createUID();
      $userobj = is_numeric($user) ? FSystem::getUserById($user) : $user;
      $threadobj = is_numeric($thread) ? FSystem::getThreadById($thread) : $thread;
      FDB::$db->query("INSERT INTO threadentry(id, mtime, ctime, creator, content, threadid, forumid, editnote, uid) VALUES(NULL, " . $time . ", " . $time . ", " . $userobj->getID() . ", '" . FSystem::FDBFilter($content) . "', " . $threadobj->getID() . ", '" . $threadobj->getParent()->getID() . "', '', '" . $uid . "')");
      if (mysqli_error(FDB::$db)) {
         echo mysqli_error(FDB::$db);
      }
      $result = FDB::$db->query("SELECT * FROM threadentry WHERE uid='" . $uid . "'");
      return Entry::createFromFDBResult($result);
   }

   /**
    * Gibt den Thread zurück, in dem dieser Eintrag geschrieben wurde
    * 
    * @return Thread
    */
   public function getThread() {
      return $this->thread;
   }

   /**
    * Gibt zurück, ob der Eintrag vom aktuellen Benutzer editiert werden kann
    * 
    * @return boolean true, wenn der Eintrag vom aktuellen Benutzer editiert werden kann
    */
   public function isEditable() {
      return FSystem::isAdmin() || (FSystem::getUser()->getID() == $this->user->getID());
   }

   /**
    * Erzeugt den HTML-Code des Eintrages, abhängig von der übergebenen Methode
    * und gibt ihn zurück
    * 
    * @param string $method Methodenstring ("default" oder "info")
    * @param integer $num Nummer des Eintrags in der Liste, die ihn enthällt
    * @return boolean|string wenn der Methodenstring "default" oder "info" (oder
    * nicht angegeben wurde) den entsprechenden HTML-Code, ansonsten false
    */
   public function createHTML($method = "default", $num = -1) {
      switch ($method) {
         case 'default':
            $mod = '';
            if ($this->edit_note) {
               $mod = '<div class="entry_edit_note" id="enote' . $this->ID . '">Letzte Ver&auml;nderung ' . date('Y.m.d H:m:s', $this->getMTime()) . ': ' . ($this->edit_note ? $this->edit_note : ' - ') . '</div>';
            } else {
               $mod = '<div class="entry_edit_note" id="enote' . $this->ID . '">Erstellt: ' . date('Y.m.d H:m:s', $this->getCTime()) . '</div>';
            }
            $signaturhtml = '';
            if ($this->user->getSignatur() != '') {
               $signaturhtml = '<div class="signatur">
                                ' . $this->user->getSignatur() . '
                            </div>';
            }
            return '<tr>
                    <td class="entry_title header" id="entry' . $this->ID . '"><a href="forum.php?method=show&tid=' . $this->thread->getID() . '&eid=' . $this->ID . '&' . FSystem::getURLAppendix() . '">' . ($num != -1 ? '<span class="entry_number">#' . $num . '</span>' : '') . ' ' . $this->thread->getTitle() . '</a></td>
                </tr>
                <tr>
                    <td class="entry_user_area">
                        ' . $this->user->getUserInfoHTML() . '
                    </td>
                    <td>
                        <div class="entry_content content" id="content' . $this->ID . '">
                            ' . ($this->isEditable() ? $this->createEditDiv() : '') . '
                            ' . $this->content . '
                            ' . $signaturhtml . '
                        </div><br/>
                        ' . $mod . '
                    </td>
                </tr>';
            break;
         case 'info':
            return '<div class="entry_info">
                    <span class="user">' . $this->user->getName() . '</span><br/>
                    <span class="time">' . date("Y.M.d H:i:s", $this->ctime) . ($this->isModified() ? ' <span class="mtime">(' . date("Y.M.d H:i:s", $this->mtime) . ')</span>' : '') . '</span>
                    </div>';
      }
      return false;
   }

   /**
    * Erzeugt das Editier-HTML-Code-Div zum editieren des Eintrages und gibt es
    * zurück
    * 
    * @return string Editier-HTML-Code-Div
    */
   private function createEditDiv() {
      return '<div class="textbox">
            <span class="text">Editieren</span><br/>
            <div class="edit_div">
                <form action="forum.php" method="POST">
                    <table>
                        <tr><td colspan="3" class="create_header">Editieren</td></tr>
                        <tr><td colspan="3"><textarea name="content">' . $this->content . '</textarea></td></tr>
                        <tr>
                            <td><input type="input" placeholder="&Auml;nderungnachricht" name="enote" value="' . $this->edit_note . '"/></td>
                            <td><input type="reset" value="Reset"/></td>
                            <td><input type="submit" value="Abschicken"/></td>
                        </tr>
                    </table>
                    <input type="hidden" name="method" value="entryedit"/>
                    <input type="hidden" name="eid" value="' . $this->getID() . '"/>
                    <input type="hidden" name="tid" value="' . $this->thread->getID() . '"/>
                    <input type="hidden" name="page" value="' . FSystem::getPage() . '"/>
                    ' . FSystem::getFormAppendix() . '
                </form>
                <form action="forum.php" method="POST" class="delete_form">
                    <input type="submit" value="Löschen"/>
                    <input type="hidden" name="method" value="entrydel"/>
                    <input type="hidden" name="eid" value="' . $this->getID() . '"/>
                    <input type="hidden" name="tid" value="' . $this->thread->getID() . '"/>
                    <input type="hidden" name="page" value="' . FSystem::getPage() . '"/>
                    ' . FSystem::getFormAppendix() . '
                </form>
            </div>
        </div>';
   }

   /**
    * Gibt den aktuellen Seitentitel zurück
    * 
    * @return string Seitentitel
    */
   public function getPageTitle() {
      return FSystem::buildPageTitle('Eintrag');
   }

   /**
    * Gibt den Ersteller dieses Eintrages zurück
    * 
    * @return User Ersteller 
    */
   public function getUser() {
      return $this->user;
   }

   /**
    * Alias für die <u>Entry->createHTML()</u>-Methode
    * 
    * @param string $method Methodenstring
    * @return string HTML-Code 
    */
   public function createPageContentHTML($method = "default") {
      return $this->createHTML($method);
   }

}

?>
