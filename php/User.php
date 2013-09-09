<?php

/*
 * Copyright (C) 2011 Julian Quast
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


require_once "FSystem.php";

/**
 * Description User
 * 
 * @author Julian Quast
 */
class User extends HObject implements Page {

   /**
    * Username
    * @var string 
    */
   private $name;

   /**
    * Ob der User im Chat ist.
    * @var boolean 
    */
   private $isInChat;

   /**
    * E-Mail-Adresse
    * @var string 
    */
   private $mailAdress;

   /**
    * Ob der User Admin ist.
    * @var boolean 
    */
   private $admin;

   /**
    *  Signatur
    * @var string 
    * */
   private $signatur;

   /**
    * Anzahl der verfassten Beiträge
    * @var int
    */
   private $entrycount;

   /**
    * Beschreibung
    * @var string 
    */
   private $description;

   /**
    * Ob der User aktiviert, oder deaktiviert ist.
    * @var boolean 
    */
   private $active;

   /**
    * HTML-Code für die User-Seite.
    * @var string 
    */
   private $userHTML;

   /**
    * Passwortstring
    * @var  string
    */
   private $pwdstring;

   /**
    * Ob der User ein Default-User ist.
    * @var boolean 
    */
   private $default_user = false;

   /**
    * Methode mit der der User instanziiert wird, also wofür das User-Objekt gebraucht wird.
    * @var string 
    */
   private $method = "default";

   /**
    * Konstruiert den User in Abhängigkeit von seiner ID.
    * Wenn diese nicht angegeben wird, wird ein Default-User erzeugt.
    * @param int $id 
    */
   public function __construct($id = -1, $method = "default") {
      if ($id != -1) {
         FDB::connect();
         $result = FDB::$db->query("SELECT * FROM user WHERE id=" . $id);
         if ($row = mysqli_fetch_array($result)) {
            parent::__construct($row['ctime'], $row['mtime'], $id);
            $this->mailAdress = $row['mailadress'];
            $this->name = $row['name'];
            $this->admin = ($row['isadmin'] == 1);
            $this->signatur = $row['signatur'];
            $this->pwdstring = $row['pwdstring'];
            $this->active = ($row['active'] == 1);
            return;
         }
      }
      parent::__construct(time(), time(), -1);
      $this->mailAdress = "";
      $this->name = "";
      $this->admin = false;
      $this->signatur = "";
      $this->entrycount = 0;
      $this->default_user = true;
      $this->method = $method;
   }

   /**
    * Erstellt den DefaultUser
    * @return User 
    */
   public static function createDefaultUser() {
      return new User();
   }

   /**
    * Gibt den Kurznamen des Templatestils dieses Benutzers zurück.
    * @return String
    */
   public function getStyle() {
      if (!$this->isDefaultUser()) {
         FDB::connect();
         $result = FDB::$db->query("SELECT * FROM usersettings WHERE userid=" . $this->ID);
         $row = mysqli_fetch_array($result);
         if ($row == null) {
            return "default";
         } else {
            return $row['style'];
         }
      } else {
         return "default";
      }
   }

   /**
    * Gibt den Namen des Users aus.
    * @return String 
    */
   public function getName() {
      return $this->name;
   }

   /**
    * Gibt die ID des Users aus.
    * @return Int
    */
   public function getID() {
      return $this->ID;
   }

   /**
    * Setzt, ob der User ein Default-User ist.
    * @param boolean $val 
    */
   public function setDefaultUser($val) {
      $this->default_user = $val;
   }

   /**
    * Gibt aus, ob der User ein Default-User ist.
    * @return boolean 
    */
   public function isDefaultUser() {
      return $this->default_user;
   }

   /**
    * Gibt die Anzahl der Beiträge eines Users aus.
    * @return int 
    */
   public function getEntryCount() {

      FDB::connect();
      $result = FDB::$db->query("SELECT COUNT(threadentries.userid), user.id  FROM user, threadentries WHERE (user.id=" . $this->ID . " ) AND (threadentries.userid = user.id)");
      $row = mysqli_fetch_array($result);
      return $row['0'];
   }

   /**
    * Gibt die Mailadresse vom User $this zurück.
    * @return string
    */
   public function getMailAdress() {
      return $this->mailAdress;
   }

   /**
    * Entfernt den User $this.
    */
   public function remove() {
      FSystem::removeUserByID($this->ID);
   }

   /**
    * Gibt zurück, ob der User $this Admin ist.
    * @return boolean
    */
   public function isAdmin() {
      return $this->admin;
   }

   /**
    * Gibt aus, ob der User zur Zeit im Chat aktiv ist.
    * @return boolean 
    */
   public function getIsInChat() {
      FDB::connect();
      $result = FDB::$db->query("SELECT id FROM user WHERE last_chat_time > " . (time() - Chat::EXPIRE_TIME) . " AND id=" . $this->ID);
      return (mysqli_fetch_array($result) != null);
   }

   /**
    * Fragt, ob der User dem im System angemeldeten entspricht.
    * @return boolean
    */
   public function isCurrentUser() {
      return (new User($this->ID) == FSystem::getUser());
   }

   /**
    * Fragt, ob der User aktiviert oder deaktiviert ist.
    * @return boolean
    */
   public function isActive() {
      FDB::connect();
      $result = FDB::$db->query("SELECT active FROM user WHERE id=$this->ID") or die(mysqli_error());
      $row = mysqli_fetch_array($result);
      return ($row['active'] == 1);
   }

   /**
    * Gibt ein Array von Entry-Objekten aus, die der User verfasst hat.
    * @return arr Entry
    */
   public function getEntries() {
      FDB::connect();
      $result = FDB::$db->query("SELECT * FROM threadentry WHERE creator = " . $this->ID . "");
      $arr = array();
      while ($entry = Entry::createFromFDBResult($result)) {
         $arr[''] = $entry;
      }
      return $arr;
   }

   /**
    * Gibt ein Array mit Threads zurück, die dieser User erstellt hat.
    * @return arr Thread 
    */
   public function getThreads() {
      FDB::connect();
      $result = FDB::$db->query("SELECT * FROM thread WHERE creator = " . $this->ID . "");
      $arr = array();
      while ($entry = Thread::createFromFDBResult($result)) {
         $arr[''] = $entry;
      }
      return $arr;
   }

   /**
    * Gibt die letzten $anzahl Entries aus, die der User erstellt hat.
    * @param type $anzahl
    * @return arr Entry 
    */
   public function getLastEntries($anzahl = 10) {
      FDB::connect();
      $result = FDB::$db->query("SELECT * FROM threadentry WHERE (creator = " . $this->ID . ") ORDER BY ctime LIMIT 0," . mysqli_real_escape_string(FDB::$db, $anzahl) . "");
      $arr = array();
      while ($entry = Entry::createFromFDBResult($result)) {
         $arr[''] = $entry;
      }
      return $arr;
   }

   /**
    * Gibt die letzten $anzahl Threads aus, die der User erstellt hat.
    * @param type $anzahl
    * @return arr Thread
    */
   public function getLastThreads($anzahl = 1) {
      FDB::connect();
      $result = FDB::$db->query("SELECT * FROM thread WHERE creator = " . $this->ID . " ORDER BY ctime LIMIT 0, " . $anzahl . "");
      $arr = array();
      while ($entry = Thread::createFromFDBResult($result)) {
         $arr[''] = $entry;
      }
      return $arr;
   }

   /**
    * Gibt den HTML-Code für das Editieren eines Users zurück.
    * Wird, sowohl administrativ, als auch zum Profil editieren verwendet.
    * @param int $id
    * @return string 
    */
   public function getConfigPageHTML($id) {
      $edituser = new User($id);
      $html = "<h1>Admin: User bearbeiten</h1>";
      $html .= "<table border=1>";
      FDB::connect();
      $html .= "<tr>";
      $html .= "<td>" . $edituser->getID() . "</td>";
      $html .= "<td>" . $edituser->name . "</td>";
      $html .= "<td>" . $edituser->mailAdress . "</td>";
      $html .= "<td>" . $edituser->admin . "</td>";
      $html .= "<td>" . $edituser->signatur . "</td>";
      $html .= "<td>" . $edituser->description . "</td>";
      $html .= "<td>" . $edituser->active . "</td>";
      $html .= "</tr>";
      $html .= "</table>";
      return $html;
   }

   /**
    * Gibt Array mit den Settings des Users aus.
    * Settings sind eigene Tabelle in der FDB. Die Struktur ist gleich.
    * @return array 
    */
   public function getSettings() {

      FDB::connect();
      $result = FDB::$db->query("SELECT * FROM settings WHERE id=" . $this->ID);
      $row = mysqli_fetch_array($result);
      return $row;
   }

   /**
    * Gibt die Signatur des Users zurück
    * @return string 
    */
   public function getSignatur() {
      return $this->signatur;
   }

   /**
    * Gibt die Informationsseite eines Users aus, die z.B. neben dem Beitrag steht.
    * @return string
    */
   public function getUserInfoHTML() {
      if ($this->userHTML == null) {
         $this->userHTML = "<div class='userinfohead'><a href='user.php?id=" . $this->ID . '&method=data&' . FSystem::getURLAppendix() . "'>" . $this->name . "</a></div><span class='count'>" . $this->count . "</span><br />"
                 . ($this->admin ? "<span class='admin'>Admin</span><br />" : "") . "
            <span class='entrycount'>" . $this->entrycount . "</span><br />" . ($this->active ? "<span class='gesperrt'>Gesperrt</span><br />" : "") . "";
      }
      return $this->userHTML;
   }

   /**
    * Deaktiviert einen User.
    */
   public function deactivate() {
      FDB::connect();
      FDB::$db->query("UPDATE user SET active=0 WHERE id=$this->ID") or die(mysqli_error());
   }

   /**
    * Aktiviert einen User.
    */
   public function activate() {
      FDB::connect();
      FDB::$db->query("UPDATE user SET active=1 WHERE id=$this->ID") or die(mysqli_error());
   }

   /**
    * Gibt den HTML-Code des Userteils der Toolbar zurück.
    * 
    * @return string HTML-Code, wenn der Benutzer nicht angemeldet ist, Loginleiste,
    * ansonsten, Benutzerleiste
    * @author Johannes Bechberger
    */
   public function createUserHeader() {
      if ($this->default_user) {
         return '<span class="login_user_bar">
            <form action="' . FSystem::URL . '"forum.php" method="POST" class="login_form">
                <span class="button head_button">Anmelden</span><br/>
                <div class="menu">
                    <input type="search" placeholder="Benutzername" name="username" title="Benutzername"/><br/> 
                    <input type="password" name="passwort" title="Passwort"/><br/>
                    <!--<input type="radio" value="yes" name="cookies">Cookies verwenden?</span><br/>-->
                    <input type="submit" value="Anmelden"/><br/>
                    <input type="hidden" name="login"/>
                    <a class="register" href="' . FSystem::URL . 'register.php">Registrieren</a> 
                </div>
            </form>
          </span>';
      } else {
         return '<span class="login_user_bar">
            <div class="login_form">
                <a href="' . FSystem::URL . 'user.php?' . FSystem::getURLAppendix() . '" class="button head_button">' . $this->name . '</a><br/>
                <div class="menu">
                    <a href="' . FSystem::URL . 'user.php?id=' . FSystem::getID() . '&method=data&' . FSystem::getURLAppendix() . '">Profil ansehen</a><br/>
                    <a href="' . FSystem::URL . 'useredit.php?id=' . FSystem::getID() . '&' . FSystem::getURLAppendix() . '">Profil bearbeiten</a><br/>
                </div>
            </div>
          </span>';
      }
   }

   /**
    * Gibt den Titel von der Userseite zurück
    * @return string 
    */
   public function getPageTitle() {
      return FSystem::buildPageTitle("User: " . $this->name);
   }

   /**
    * Ruft die Methode zur HTML-Code-Erzeugung mit angegebendem Wert oder "default" auf
    * @param string
    */
   public function createPageContentHTML($method = "default") {
      switch ($this->method) {
         case "default":
            return $this->getUserDataHTML($this->method);
            break;
         case "adminedit":
            break;
         case "admin":
            break;
         case "data":
            return $this->getUserDataHTML($this->method);
            break;
      }
   }

   /**
    * Gibt die Userdaten für den Admin aus, wenn der aufrufende User Admin ist.
    * @param string
    * @return string
    */
   public function getUserDataHTML() {
      $html = "";
      if (!$this->isDefaultUser()) {
         $html .= "<h1>User: $this->name</h1>
                    <table>
                    <tr><td>Im Chat</td><td>" . ($this->isInChat ? "Ja" : "Nein") . "</td></tr>
                    <tr><td>E-Mail</td><td>$this->mailAdress</td></tr>
                    <tr><td>Admin</td><td>" . ($this->admin ? "Ja" : "Nein") . "</td></tr>
                    <tr><td>Signatur</td><td>" . ($this->signatur == "" ? "<p class='grayed'>(keine)</p>" : $this->signatur) . "</td></tr>
                    <tr><td>Beiträge</td><td>$this->entrycount</td></tr>
                    <tr><td>Beschreibung</td><td>" . ($this->description == "" ? "<p class='grayed'>(keine)</p>" : $this->description) . "</td></tr>
                    <tr><td>Aktiv?</td><td>" . ($this->active ? "Ja" : "Nein") . "</td></tr>
                </table>
                ";
         if (FSystem::isAdmin()) {
            $html .= "<a href='useredit.php?id=" . FSystem::getID() . "&" . FSystem::getURLAppendix() . "'>Editieren</a>";
         }
      } else {
         $html .= "<p class='error'>Dieser User existiert nicht.</p>";
      }
      return $html;
   }

   /**
    * Gibt HTML-Code für den Adminedit aus.
    * @return string
    */
   public function getUserEditHTML($edituser = "normal") {
      if ($edituser == "admin") {
         return "Hier fehlt der Admin-Edit Code";
      } else {
         return "<table class='bordered'> 
                    <tr><td>Username</td><td><input type='text' value='$this->name' name='name'/></td></tr>
                    <tr><td>Im Chat ?</td><td>" . ($this->isInChat ? 1 : 0) . "</td></tr>
                    <tr><td>E-Mail</td><td><input type='text' value='$this->mailAdress' name='mailadress'/></td></tr>
                    <tr><td>admin</td><td><input type='text' value='$this->admin' name='admin'/></td></tr>
                    <tr><td>signatur</td><td><input type='text' value='$this->signatur' name='signatur'/></td></tr>
                    <tr><td>Beschreibung</td><td><input type='text' value='$this->description' name='description'/></td></tr>
                    <tr><td>Aktiv?</td><td><input type='text' value='" . ( $this->active ? 1 : 0) . "' name='active'/></td></tr>
                    <tr><td>userHTML</td><td><input type='text' value='$this->userHTML' name='userHTML'/></td></tr>
                    <tr><td>DefaultUser ?</td><td><input type='text' value='" . ($this->default_user ? 1 : 0 ) . "' name='default_user'/></td></tr>
                    <input type='hidden' value='$this->ID' name='id'/>    
                </table>
                ";
      }
   }

   /**
    * Gibt HTML-Code für den Self-Edit aus.
    * @return string
    */
   public function getUserSelfEditHTML($edituser = "normal") {
      if ($edituser == "admin") {
         return "Hier fehlt der Admin-Edit Code";
      } else {
         return "<table class='bordered'> 
                    <tr><td>Username</td><td><input type='text' value='$this->name' name='name'/></td></tr>
                    <tr><td>Im Chat ?</td><td>" . ($this->isInChat ? 1 : 0) . "</td></tr>
                    <tr><td>E-Mail</td><td><input type='text' value='$this->mailAdress' name='mailadress'/></td></tr>
                    <tr><td>admin</td><td>$this->admin</td></tr>
                    <tr><td>signatur</td><td><input type='text' value='$this->signatur' name='signatur'/></td></tr>
                    <tr><td>Beschreibung</td><td><input type='text' value='$this->description' name='description'/></td></tr>
                    <input type='hidden' value='$this->ID' name='id'/>    
                </table>
                ";
      }
   }

   /**
    * Setzt den Admin-Zustand des Users
    * @param integer $id
    * @param boolean $value
    * @return boolean 
    */
   public function setIsAdmin($id, $value) {
      if ($this->isAdmin()) {
         $query = "UPDATE user SET isadmin=" . ($value ? 1 : 0) . " WHERE id=" . $id;
         FDB::$db->query($query) or die(mysqli_error());
         return true;
      }
      return false;
   }

   public function setName($name) {
      $this->name = FSystem::filter($name);
   }

   public function setMailAdress($mailadress) {
      $this->mailAdress = FSystem::filter($mailadress);
   }

   public function getPwdstring() {
      return $this->pwdstring;
   }

   /**
    * Standard-Methode für die HTML-Code-Erzeugung
    * Umleitung auf createPageContentHTML()
    * @param string $method
    * @return string 
    */
   public function createHTML($method = "default") {
      return $this->createPageContentHTML($method);
   }

}

?>
