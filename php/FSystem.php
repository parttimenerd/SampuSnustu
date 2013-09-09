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

require_once 'spl_autoload.php';

/**
 * Grundklasse, die statische Methoden bereitstellt, quasi die funktionale
 * Sammelklasse dieses Projekts
 *
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Utils
 */
class FSystem {

   /**
    * Chat dieser Seite
    * @var Chat
    */
   private static $chat;

   /**
    * Aktueller Benutzer dieser Seite
    * @var User 
    */
   private static $user;

   /**
    * Benutzer-Objekt-Array, in dem alle Benutzerobjekte gespeichert werden,
    * die per <u>FSystem->getUserById()</u> erzeugt wurden
    * @var User[]
    */
   private static $userarr = array();

   /**
    * Forum-Objekt-Array, in dem alle Forum-Objekte gespeichert werden,
    * die per <u>FSystem->getForumById()</u> erzeugt wurden
    * @var Forum[]
    */
   private static $forumarr = array();

   /**
    * Thread-Objekt-Array, in dem alle Thread-Objekte gespeichert werden,
    * die per <u>FSystem->getThreadById()</u> erzeugt wurden
    * @var Thread[]
    */
   private static $threadarr = array();

   /**
    * Eintrag-Objekt-Array, in dem alle Eintrag-Objekte gespeichert werden,
    * die per <u>FSystem->getEntryById()</u> erzeugt wurden
    * @var Entry[]
    */
   private static $entryarr = array();

   /**
    * Hash-Array mit der Benutzer-ID und der Session-ID, wenn der Benutzer sich
    * per URL-Parameter authentifiziert
    * @var array
    */
   private static $url_apparr = array("sid" => "", "sessionid" => "");

   /**
    * URL des Verzeichnisses, in dem das Projekt liegt
    * @var string
    */

   const URL = "/SampuSnustu/";

   /**
    * Anzahl der Runden, mit denen dies Passworthashs gehasht werden
    * @var integer
    */
   const HASH_ROUNDS = 100;

   /**
    * Anzahl der Einträge, die auf einer Forum- oder Thread-Seite angezeigt werden
    * @var integer
    */
   const ENTRIES_HTML_LIST_LENGTH = 30;

   /**
    * Titel Haupttitel der Seite
    * @var string 
    */
   const TITLE = 'Sampu Snustu';

   /**
    * Zitate auf der Seite anzeigen?
    * @var boolean
    */
   const QUOTES = true;

   /**
    * Zeitspanne die eine Session-Id gültig ist in Sekunden
    * @var integer
    */
   const SESSIONID_EXPIRE_TIME = 600;

   /**
    * Zeitspanne die ein Cookie gültig ist in Tagen
    * @var integer
    */
   const COOKIE_EXPIRE_TIME = 100;

   /**
    * Maximale Länge des Seitentitels
    * @var integer
    */
   const PAGE_TITLE_MAX_LENGTH = 30;

   /**
    * String, der im Seitentitel den Titel der einzelnen Seite und den
    * Hauptseitentitel trennt
    * @var string
    */
   const PAGE_TITLE_DELIMITER = ' | ';

   /**
    * Gibt an, ob das Programm gerade im DEBUG-Modus läuft
    * @var boolean
    */
   const DEBUG = false;

   /**
    * Gibt den Chat der Seite zurück
    * 
    * @return Chat Chat der Seite
    */
   public static function getChat() {
      if (FSystem::$chat == null) {
         FSystem::$chat = new Chat(1);
      }
      return FSystem::$chat;
   }

   /**
    * Gibt das aktuelle Forum zurück
    * 
    * @return Forum Forum-Objekt, wenn aktuelles Forum in URL spezifiert,
    * sonst Hauptforum
    */
   public static function getForum() {
      $forum = FSystem::getForumById(isset($_REQUEST["fid"]) ? $_REQUEST["fid"] : 0);
      return $forum ? $forum : FSystem::getMainForum();
   }

   /**
    * Gibt das aktuell anzuzeigende Forum zurück
    * 
    * @return Forum Forum-Objekt, wenn aktuelles Forum in URL spezifiert,
    * sonst Hauptforum
    */
   public static function getJumpForum() {
      $forum = FSystem::getForumById(isset($_REQUEST["jumpfid"]) ? $_REQUEST["jumpfid"] : (isset($_REQUEST["fid"]) ? $_REQUEST["fid"] : 0));
      return $forum ? $forum : FSystem::getMainForum();
   }

   /**
    * Erzeugt das mit der ID spezifizierte Forum-Objekt und gibt es zurück
    * 
    * @param integer $id ID des Forum-Objektes
    * @return Forum erzeugtes Forum-Objekt, wenn erfolgreich, sonst Hauptforum-Objekt
    */
   public static function getForumById($id) {
      if (!array_key_exists($id, FSystem::$forumarr)) {
         FSystem::$forumarr[$id] = Forum::createByID($id);
      }
      return FSystem::$forumarr[$id];
   }

   /**
    * Gibt den aktuellen Thread zurück
    * 
    * @return null|Thread Thread-Objekt, wenn aktuelles Thread in URL spezifiert,
    * sonst null
    */
   public static function getThread() {
      return isset($_REQUEST["tid"]) ? FSystem::getThreadById($_REQUEST["tid"]) : null;
   }

   /**
    * Erzeugt das mit der ID spezifizierte Thread-Objekt und gibt es zurück
    * 
    * @param integer $id ID des Thread-Objektes
    * @return null|Thread erzeugtes Thread-Objekt, wenn erfolreich, sonst null
    */
   public static function getThreadById($id) {
      if (!array_key_exists($id, FSystem::$threadarr)) {
         FSystem::$threadarr[$id] = Thread::createByID($id);
      }
      return FSystem::$threadarr[$id] ? FSystem::$threadarr[$id] : null;
   }

   /**
    * Erzeugt das mit der ID spezifizierte Eintrag-Objekt und gibt es zurück
    * 
    * @param integer $id ID des Eintrag-Objektes
    * @return null|Thread spezifiziertes Eintrag-Objekt, wenn erfolreich, sonst null
    */
   public static function getEntryById($id) {
      if (!array_key_exists($id, FSystem::$entryarr)) {
         FSystem::$entryarr[$id] = Entry::createByID($id);
      }
      return FSystem::$entryarr[$id] ? FSystem::$entryarr[$id] : null;
   }

   /**
    * Erzeugt den aktellen Benutzer, nach Verifikation, und gibt ihn zurück
    * 
    * @param boolean $force wenn true, wird der Benutzer sicher neu erzeugt, wenn
    * false, wird er nur dann erstellt, wenn er nicht schon im Cache liegt
    * @return User aktueller Benutzer, wenn erfolgreich, ansonsten Default-Benutzer-Objekt
    */
   public static function getUser($force = false) {
      FDB::connect();
      if (FSystem::$user == null || $force) {
         if (isset($_COOKIE["password"]) && isset($_COOKIE["id"])) {
            FSystem::$user = FSystem::verifyUser($_COOKIE["id"], $_COOKIE["password"]) ? new User($_COOKIE["id"]) : User::createDefaultUser();
         } else if (FSystem::verifyUserByURL()) {
            FSystem::$user = new User($_REQUEST["sid"]);
            FSystem::updateURLAppendix($_REQUEST["sid"]);
         } else {
            FSystem::$user = User::createDefaultUser();
         }
         if (!FSystem::isDefaultUser()) {
            FDB::$db->query("UPDATE user SET last_forum_time = " . time() . " WHERE id = " . FSystem::$user->getID());
         }
      }
      return FSystem::$user;
   }

   /**
    * Verifiziert den Benutzer, mit der übergebenen ID bzw. des übergebenen Namens,
    * mit dem Passwordstring
    * 
    * @param integer|string $idname ID bzw. Name des Benutzers
    * @param string $password Passwordstring, 'Hash-Runden@Salt@Passwordhash'
    * @return boolean wenn true, ist Benutzer verifiziert
    */
   public static function verifyUser($idname, $password) {
      FDB::connect();
      $result = null;
      if (is_numeric($idname)) {
         $result = FDB::$db->query("SELECT pwdstring FROM user WHERE id=" . FDB::$db->real_escape_string($idname));
      } else if ($idname) {
         $result = FDB::$db->query("SELECT pwdstring FROM user WHERE name='" . FDB::$db->real_escape_string($idname) . "'");
      }
      if (!$result) {
         return false;
      }
      $line = mysqli_fetch_array($result);
      if ($line == null) {
         return false;
      }
      $arr = explode("@", $line["pwdstring"]);
      return FSystem::secHash($password . md5($arr[1]), $arr[0]) == $arr[2];
   }

   /**
    * Verifiziert den Benutzer mit Hilfe der Session-Id und der Benutzer-ID aus
    * den URL-Übergabeparametern und erzeugt eine neue Session-ID, in
    * <u>FSystem::url_apparr</u> gespeichert
    * 
    * @return boolean wenn true, ist Benutzer verifiziert
    */
   public static function verifyUserByURL() {
      if (isset($_REQUEST["sid"]) && isset($_REQUEST["sessionid"])) {
         FDB::connect();
         $result = FDB::$db->query('SELECT sessionid FROM user WHERE id=' . FDB::$db->real_escape_string($_REQUEST["sid"]) . ' AND sessionid="' . FDB::$db->real_escape_string($_REQUEST["sessionid"]) . '"');
         if ($result) {
            $arr = mysqli_fetch_array($result);
            $arr = explode('|', $arr["sessionid"]);
            if (count($arr) == 2 && is_numeric($arr[0]) && $arr[0] > time() - self::SESSIONID_EXPIRE_TIME) {
               self::$url_apparr = self::updateURLAppendix($_REQUEST["sid"]);
               return true;
            }
         }
      } else if (isset($_REQUEST["chat"]) && isset($_REQUEST["uid"]) && isset($_REQUEST["sid"])) {
         FDB::connect();
         $result = FDB::$db->query('SELECT id FROM user WHERE id=' . FDB::$db->real_escape_string($_REQUEST["sid"]) . ' uid="' . FDB::$db->real_escape_string($_REQUEST["uid"]) . '"');
         if ($result) {
            return true;
         }
      }
      return false;
   }

   /**
    * Erzeugt ein sicheren SHA512-Hash des Datenstrings, mit der übergebenen Anzahl
    * von Hashrunden
    * 
    * @param string $data Datenstring
    * @param integer $hash_rounds Hashrunden, default: <u>FSystem::$HASH_ROUNDS</u>
    * @return string Base64-Codierter String des erzeugten Hashs
    */
   public function secHash($data, $hash_rounds = -1) {
      $rounds = $hash_rounds == -1 ? FSystem::HASH_ROUNDS : $hash_rounds;
      $bin = $data;
      for ($i = 0; $i < $rounds; $i++) {
         $bin = hash("sha512", $bin, true);
      }
      return base64_encode($bin);
   }

   /**
    * Erzeugt einen einfachen SHA512-Hash des übergebenen Datenstrings
    *
    * @param string $data Datenstring
    * @return string Base64-Codirerter String des erzeugten Hashs
    */
   public static function hash($data) {
      return base64_encode(hash("sha512", $data, true));
   }

   /**
    * Gibt das Hauptforum zurück
    * 
    * @return Forum Hauptforum
    */
   public static function getMainForum() {
      return FSystem::getForumById(1);
   }

   /**
    * Erstellt einen Benutzer mit den übergebenen Parametern und speichert ihn in
    * der Datenbank
    * 
    * @param string $name Name
    * @param string $mailadress E-Mail-Adresse
    * @param string $isadmin hat der Benutzer Administratorrechte?
    * @param string $passwort Passwort
    * @param string $signatur Signatur
    * @param string $description Beschreibung
    * @param boolean cookie_allowed Anmeldung per Cookie?
    * @return boolean wenn true, Erstellung erfolgreich, ansonsten Fehler,
    * z.B. Benutzer mit dem Namen existiert schon in der Datenbank
    */
   public static function addUser($name, $mailadress, $isadmin, $passwort, $signatur, $description, $cookie_allowed = false) {
      FDB::connect();
      $secname = FDB::$db->real_escape_string($name);
      $salt = self::createSalt();
      $uid = self::createURLSuitedSalt(100);
      $pwdhash = self::HASH_ROUNDS . "@" . $salt . "@" . FSystem::secHash($passwort . md5($salt));
      $result = FDB::$db->query('SELECT name FROM user WHERE name="' . $secname . '"');
      if (!mysqli_num_rows($result)) {
         FDB::$db->query("INSERT INTO user (id, name, mailadress, isadmin, pwdstring, ctime, mtime, signatur, description, active, sessionid, uid) VALUES(NULL , '" . $secname . "', '" . FDB::$db->real_escape_string($mailadress) . "', " . ($isadmin ? 1 : 0) . ", '" . $pwdhash . "', " . time() . ", " . time() . ", '" . self::filter($signatur) . "', '" . self::filter($description) . "', 1, '', '" . $uid . "')") or die(mysqli_error(FDB::$db));
         $result = FDB::$db->query('SELECT id FROM user WHERE name="' . $secname . '"');
         if ($result) {
            $arr = mysqli_fetch_array($result);
            if ($cookie_allowed) {
               self::updatePWDCookie($arr['id'], $passwort);
            } else {
               self::updateURLAppendix($arr['id']);
            }
            return true;
         }
      }
      return false;
   }

   /**
    * Produziert einen zufalligen String, der sich als Salt eignet.
    * 
    * @return string Base64-Codierter-String 
    */
   public static function createSalt() {
      return FSystem::hash(time() . microtime() . mt_getrandmax() . mt_getrandmax() . mt_getrandmax());
   }

   /**
    * Erzeugt einen Saltstring mit der angebenen Länge, welcher aus einer
    * zufälligen Abfolge von Kleinbustaben und Ziffern besteht, somit besonders
    * für die Verwendung in einer URL verwendet werden kann.
    * 
    * @param integer $length Länge
    * @return string Saltstring
    */
   public static function createURLSuitedSalt($length = 10) {
      $letterstr = 'abcdefghijklmnopqrstuvwxyz1234567890';
      $str = '';
      for ($i = 0; $i < $length; $i++) {
         $letterstr = str_shuffle($letterstr);
         $str .= substr($letterstr, 0, 1);
      }
//        if (count($str) < $length) {
//            $str .= substr(str_shuffle($letterstr), 0, $length - count($str));
//        }
      return $str;
   }

   /**
    * Erzeugt eine UID (Unique ID)
    * 
    * @return string UID-String
    */
   public static function createUID() {
      return base64_encode(str_replace('.', '', microtime())) . FSystem::createSalt();
   }

   /**
    * Löscht den Benutzer mit der übergebenen ID aus der Datenbank
    *
    * @param integer $user_id Benutzer-ID
    * @return boolean true, wenn erfolgreich
    */
   public static function removeUserByID($user_id) {
      FDB::connect();
      if (FSystem::isAdmin() || FSystem::getID() == $user_id) {
         FDB::$db->query("DELETE FROM user WHERE id=" . FDB::$db->real_escape_string($user_id));
         return true;
      }
      return false;
   }

   /**
    * Löscht den übergebenen Benutzer aus der Datenbank
    * 
    * @param User $user Benutzer
    * @return boolean true, wenn erfolgreich
    */
   public static function removeUser(User $user) {
      return $this::removeUserById($user->getID());
   }

   /**
    * Erzeugt das mit der ID spezifizierte Benutzer-Objekt und gibt es zurück
    * 
    * @param integer $id ID des Benutzer-Objektes
    * @return Forum erzeugtes Benutzer-Objekt, wenn erfolgreich, sonst Default-User-Objekt
    */
   public static function getUserById($id) {
      if (!array_key_exists($id, FSystem::$userarr)) {
         FSystem::$userarr[$id] = new User($id);
      }
      return FSystem::$userarr[$id];
   }

   /**
    * Gibt zurück, ob der aktuelle Benutzer ein Administrator ist
    *
    * @return boolean true, wenn Administrator
    */
   public static function isAdmin() {
      return ($u = FSystem::getUser()) ? $u->isAdmin() : false;
   }

   /**
    * Gibt die ID des aktuellen Benutzers zurück
    *
    * @return integer ID
    */
   public static function getID() {
      return ($u = FSystem::getUser()) ? $u->getID() : -1;
   }

   /**
    * Verwendet HTML Purifier 4.3.0 um den Text, vornehmlich HTML-Code, zu säubern
    * 
    * @param string $dirty_text unsauberer (d.h. von Benutzer übergebener) HTML-Code
    * @return string gesäuberter HTML-Code 
    */
   public static function filter($dirty_text) {
      require_once 'foreign_code/htmlpurifier-4.3.0_standalone/HTMLPurifier.standalone.php';
      $purifier = new HTMLPurifier();
      $text = $purifier->purify($dirty_text);
      return $text;
   }

   /**
    * Verwendet HTML Purifier 4.3.0 um den Text, vornehmlich HTML-Code, zu säubern,
    * welcher anschließend noch für die Datenbank vorbereitet wird.
    * Kurzschreibweise für <u>FDB::$FDB->real_escape_string(FSystem::filter($dirty_text))</u>
    * 
    * @param string $dirty_text unsauberer (d.h. von Benutzer übergebener) HTML-Code
    * @return string gesäuberter HTML-Code
    */
   public static function FDBFilter($dirty_text) {
      return FDB::$db->real_escape_string(FSystem::filter($dirty_text));
   }

   /**
    * Gibt das Templatestil-Hash-Array mit dem Kurznamen des Templatestils als
    * Schlüssel und dem jeweiligen Eigenschaften-Hash-Array als Wert zurück
    * 
    * Aufbau:
    * <code>
    * $styles = array(
    *   "default" => array(
    *      "name" => "Default Template",
    *      "further_css_files" => array(),
    *      "further_js_files" => array(),
    *      "css" => "",
    *      "js" => "",
    *   )
    * );
    * </code>
    * 
    * @return array Templatestil-Hash-Array
    */
   public static function getStyles() {
      return Template::getStyles();
   }

   /**
    * Gibt den Methodenstring aus den URL-Parametern zurück, wenn keiner angegeben
    * 'default'
    * 
    * @return string Methodenstring 
    */
   public static function getMethod() {
      return isset($_REQUEST['method']) ? $_REQUEST['method'] : 'default';
   }

   /**
    * Gibt den Kurznamen des Templatestils des aktuellen Benutzers zurück
    * 
    * @return string Templatestilkurzname
    */
   public static function getStyle() {
      if (self::getUser()->isDefaultUser()) {
         return "default";
      } else {
         return self::getUser()->getStyle();
      }
   }

   /**
    * Erzeugt eine neue Session-ID und schreibt sie ins <u>FSystem::$url_apparr</u>
    * Array und in die Datenbank
    * 
    * @param integer $id ID des Benutzers, dessen Session-ID neu erzeugt werden soll
    */
   public static function updateURLAppendix($id) {
      FSystem::$url_apparr["sid"] = $id;
      FSystem::$url_apparr["sessionid"] = time() . "|" . self::createURLSuitedSalt();
      FDB::$db->query("UPDATE user SET sessionid = '" . FSystem::$url_apparr["sessionid"] . "' WHERE id = " . $id) or die(mysqli_error(FDB::$db));
   }

   /**
    * Schreibt die übergebene ID eines Benutzers und das korrespondierende
    * Passwort in die Cookies
    * 
    * @param integer $id ID des Benutzers, dessen Passwort in die Cookies
    * geschrieben werden soll
    * @param string $pwd Password
    */
   public static function updatePWDCookie($id, $pwd) {
      $time = time() + (86400 * self::COOKIE_EXPIRE_TIME);
      setcookie('id', $id, $time);
      setcookie('pwd', $pwd, $time);
   }

   /**
    * Gibt den, aus dem <u>FSystem::$url_apparr</u> Array erstellten, URL-Appendix zurück
    * 
    * @return string URL-Appendix, wenn keine Session-ID existiert '' 
    */
   public static function getURLAppendix() {
      if (self::$url_apparr["sid"]) {
         return "sid=" . self::$url_apparr["sid"] . '&sessionid=' . self::$url_apparr["sessionid"];
      }
      return '';
   }

   /**
    * Gibt den, aus dem <u>FSystem::$url_apparr</u> Array erstellten, URL-Form-Appendix
    * zurück, der jedem Formular hinzugefügt werden sollte
    * 
    * @return string URL-Form-Appendix, wenn keine Session-ID existiert '' 
    */
   public static function getFormAppendix() {
      if (self::$url_apparr["sid"]) {
         return '<input type="hidden" name="sid" value="' . self::$url_apparr["sid"] . '"/>
                <input type="hidden" name="sessionid" value="' . self::$url_apparr["sessionid"] . '"/>';
      }
      return '';
   }

   /**
    * Ersetzt den aktuellen Benutzer mit dem übergebenen
    * 
    * @param User $user neues Benutzer-Objekt
    * @return boolean true, wenn erfolgreich
    */
   public static function setUser($user) {
      if ($user && is_a($user, "User")) {
         self::$user = $user;
         return true;
      }
      return false;
   }

   /**
    * Erzeugt aus beiden Arrays in ein neues, wobei die Werte des ersten Arrays
    * bevorzugt werden. Wird verwendet, um Optionen Arrays mit den Defaultoptionen
    * zu vervollständigen.
    * 
    * @param array $options Array mit den neuen Werten
    * @param array $defoptions Default-Array
    * @return array erzeugtes Array
    */
   public static function extend($options, $defoptions) {
      if ($options && $defoptions) {
         foreach ($options as $key => $value) {
            if ($value && array_key_exists($key, $defoptions)) {
               $defoptions[$key] = $value;
            }
         }
      }
      return $defoptions;
   }

   /**
    * Filtert das die Werte des übergebenen Arrays per <u>FDB::$FDB->real_escape_string()</u>
    * Methode und gibt es zurück
    * 
    * @param array $arr zu filterdes Array
    * @return array gefiltertes Array
    */
   public static function filterFDBArr($arr) {
      FDB::connect();
      if ($arr) {
         foreach ($arr as $key => $value) {
            $arr[$key] = $value ? FDB::$db->real_escape_string($value) : $value;
         }
      }
      return $arr;
   }

   /**
    * Gibt zurück, ob der aktuelle Benutzer ein Default-Benutzer ist, Alias für
    * <u>FSystem::getUser()->isDefaultUser()</u>
    * 
    * @return boolean wenn true, ist der Benutzer ein Default-Benutzer
    */
   public static function isDefaultUser() {
      return self::getUser()->isDefaultUser();
   }

   /**
    * Erzeugt den HTML-Code des Seiten-Wähl-Span, des übergebenen Objektes
    * 
    * @param Forum|Thread $pageable Thread- oder Forum-Objekt
    * @return string Seiten-Wähl-Span-HTML-Code wenn erfolgreich, ansonsten ''
    */
   public static function getPageChooseBar($pageable) {
      if ($pageable && array_key_exists('getPageCount', get_class_methods(get_class($pageable))) && $pageable->getPageCount() > 1) {
         try {
            $html = '<span id="page_choose_bar">Seite: ';
            for ($i = 1; $i <= $pageable->getPageCount(); $i++) {
               $html .= '<a href="' . $pageable->getPageLink() . '&page=' . $i . '&' . FSystem::getURLAppendix() . '"' . ($i - 1 == $pageable->getPage() ? ' class="selected_page"' : '') . '>' . $i . '</a>' . ($pageable->getPageCount() != $i ? ' | ' : '');
            }
            return $html . '</span>';
         } catch (Exception $e) {
            return '';
         }
      }
      return '';
   }

   /**
    * Erzeugt aus dem übergebenen Objekt den Seiten-HTML-Code, abhängig vom
    * Methodenstring, und gibt ihn zurück
    *
    * @param Page $page
    * @param string $method Methodenstring
    * @return string erzeugter HTML-Code
    */
   public static function processPage($page, $method = null) {
      return Template::process($page, FSystem::getStyle(), $method ? $method : FSystem::getMethod());
   }

   /**
    * Gibt die Nummer der aktuellen Seite, ausgehend vom URL-Übergabeparameter
    * <u>page</u>, wenn dieser nicht vorhanden ist 0, zurück
    * 
    * @return integer aktuelle Seitennummer (die erste Seite hat die Seitennummer 0)
    */
   public static function getPage() {
      return isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 0;
   }

   /**
    * Baut aus dem übergebenen Titel und dem Haupttitel den Seitentitel zusammen
    * und gibt ihn zurück
    * 
    * @param string $item_title Titel
    * @return string Seitentitel
    */
   public static function buildPageTitle($item_title) {
      if (count($item_title) > self::PAGE_TITLE_MAX_LENGTH - count(self::PAGE_TITLE_DELIMITER . self::TITLE)) {
         return substr($item_title, 0, self::PAGE_TITLE_MAX_LENGTH - count('...' . self::PAGE_TITLE_DELIMITER . self::TITLE)) . '...' . self::PAGE_TITLE_DELIMITER . self::TITLE;
      }
      return $item_title . self::PAGE_TITLE_DELIMITER . self::TITLE;
   }

   /**
    * Ermittelt die User-ID aufgrund des Usernamens
    *
    * @author Julian Quast
    * @param string $name  Username
    * @return integer      User-ID, wenn nicht kein Benutzer mit dem Namen existiert -1
    */
   public static function getUserIdByName($name) {
      FDB::connect();
      $result = FDB::$db->query("SELECT id FROM user WHERE name='" . FSystem::FDBFilter($name) . "'");
      if ($result) {
         $row = mysqli_fetch_array($result);
         return $row['id'];
      } else {
         return -1;
      }
   }

   /**
    * Liest die Namen aller Benutzer des Forum aus der Datenbank aus und gibt
    * sie als Array zurück
    * 
    * @return string[] Benutzernamenarray
    */
   public static function getUserNames() {
      $arr = array();
      FDB::connect();
      $result = FDB::$db->query('SELECT name FROM user');
      if ($result) {
         while ($row = mysqli_fetch_array($result)) {
            $arr[] = $row['name'];
         }
      }
      return $arr;
   }

   /**
    * Ruft die Methode var_dump mit dem übergebenen Parameter auf, falls das
    * Programm sich im Debug-Modus befindet
    */
   public static function var_dump($val) {
      if (self::DEBUG) {
         var_dump($val);
      }
   }

   /**
    * Gibt das URL-Appendix-Array zurück
    * 
    * @return array URL-Appendix-Array 
    */
   public static function getURLAppendixArray() {
      return FSystem::$url_apparr;
   }

}

?>
