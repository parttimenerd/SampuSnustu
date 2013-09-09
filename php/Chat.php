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

error_reporting(0);

/**
 * Chat-Klasse, die den Chat managed und formatiert.  
 * 
 * Andwendungsbeispiel:
 * <code>
 * <?php
 * $chat = new Chat();
 * $chat->createHTML(); //Erzeugt die Chat-HTML-Seite
 * //Auch möglich
 * $chat = FSystem::getChat();
 * ?>
 * </code>
 * 
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @uses Message, FSystem, FDB
 * @package SampuSnustu
 * @subpackage Chat
 */
class Chat extends HObject implements Page {

   /**
    * ID der letzten Nachricht
    * @var integer 
    */
   private $last = 0;

   /**
    * Aktuelle Anzahl der Benutzer im Chat
    * @var integer
    */
   private $user_count;

   /**
    * Kommandos, welche der Chat interpretieren kann, und deren Beschreibung
    * @var array Kommando-Array-Array
    */
   private $cmds = array(
       array("help", "Gibt die Hilfe aus"),
       array("cmd_pi", "Gibt die wundersch&ouml;ne Zahl &pi; mit ein paar Nachkommastellen aus"),
       array("cmd_e", "Gibt die eulersche Zahl &pi; mit ein paar Nachkommastellen aus"),
       array("cmd_quote", "Gibt ein zufülliges Zitat aus"),
       array("cmd_unixtime", "Gibt die aktuelle Unixzeit zurück"),
       array("cmd_dectohex [x]", "Gibt die hexadezimale Darstellung der Dezimalzahl x aus"),
       array("cmd_hextodec [x]", "Gibt die dezimale Darstellung der Hexadezimalzahl x aus"),
       array("cmd_dectobin [x]", "Gibt die bin&auml;re Darstellung der Dezimalzahl x aus"),
       array("cmd_bintodec [x]", "Gibt die dezimale Darstellung der Bin&auml;rzahl x aus"),
       array("cmd_sqrt [x]", "Gibt die Quadratwurzel der Zahl x aus"),
       array("cmd_for [x] [command]", 'F&uuml;hrt das angegebene Kommando x mal aus, per $i kann auf die Laufvariable zugegriffen werden'),
       array("cmd_echo [text]", "Gibt den Text aus"),
       array("cmd_dellast [x]", "L&ouml;scht die letzten x [default: 1] eigenen Nachrichten")
   );

   /**
    * Maximale Anzahl von Nachrichten, die auf einmal im Chat angezeigt werden
    * @var integer 
    */

   const MAX_MSGS = 10;

   /**
    * Zeitspanne in Sekunden, die ein Benutzer im Chat als angemeldet gilt,
    * wenn er die Seite einmal neugeladen hat bzw. aufgerufen hat
    * @var integer
    */
   const EXPIRE_TIME = 30;

   /**
    * Konstuktor der Chat-Klasse
    *
    * @param integer $id ID des Chatobjekts, per default auf 0 gesetzt, da sie
    * im Normalfall keine Bedeutung hat
    */
   public function __construct($id = 0) {
      parent::__construct(time(), time(), $id);
      FDB::connect();
      $result = FDB::$db->query("SELECT MAX(id) AS maxid FROM chatmsg");
      if ($result) {
         $line = mysqli_fetch_array($result);
         if ($line) {
            $this->last = intval($line["maxid"]) + 1;
         } else {
            $this->last = 0;
         }
      } else {
         $this->last = 0;
      }
   }

   /**
    * Erzeugt den HTML-Code des Chats, abhängig vom übergebenen Methodenstring
    * und gibt ihn als String zurück. Wenn der aktuelle Benutzer der Seite nicht
    * angemeldet ist, gibt sie den HTML-Code der entsprechenden Login-Seite zurück. 
    * 
    * @param string $method Methodenstring, folgende Werte sind möglich:
    * Wert | URL-Übergabeparameter | Aktion<br/>
    *  update | [last] | Gibt alle Nachrichten, die nach der durch die mit <code>last</code> 
    *          angegebenen ID geschrieben wurden, und die aktuelle Benutzerliste
    *          als HTML zurück<br/>
     addmgs</td>
    *      <td>msg</td>
    *      <td>Fügt eine Nachricht mit <code>msg</code> als Inhalt dem Chat hinzu,
    *          keine Rückgabe</td>
    *  </tr>
    *  <tr>
    *      <td>addmgs_noajax</td>
    *      <td>msg</td>
    *      <td>Fügt eine Nachricht mit <code>msg</code> als Inhalt dem Chat hinzu
    *          und gibt die aktuelle HTML-Seite des Chats zurück</td>
    *  </tr>
    *  <tr>
    *      <td>default</td>
    *      <td>-</td>
    *      <td>Gibt die die aktuelle HTML-Seite des Chats zurück</td>
    *  </tr>
    * </table>
    * </html>
    * @return string HTML-Code
    */
   public function createHTML($method = 'default') {
      $html = "";
      $isuser = false;
      if (isset($_REQUEST["uid"]) && isset($_REQUEST["sid"])) {
         $result = FDB::$db->query('SELECT id FROM user WHERE uid="' . FDB::$db->real_escape_string($_REQUEST["uid"]) . '" AND id=' . FDB::$db->real_escape_string($_REQUEST["sid"]));
         $isuser = $result != null;
         if ($isuser) {
            FSystem::setUser(FSystem::getUserById($_REQUEST["sid"]));
         }
      }
      if (FSystem::getUser()->isDefaultUser()) {
         $login = new Login();
         $html = $login->createPageContentHTML('default', 'chat.php');
      } else {
         switch ($method) {
            case "update":
               $msgarr = $this->getMessages(isset($_REQUEST["last"]) ? $_REQUEST["last"] : -1);
               $html = count($msgarr) . "|";
               foreach ($msgarr as $msg) {
                  $html .= str_replace("|", " ", $msg->createHTML());
               }
               $html .= "|" . self::getUserListHTML();
               FDB::$db->query("UPDATE user SET last_chat_time = " . time() . " WHERE id = " . FSystem::getUser()->getID());
               break;
            case "addmsg":
               $this->addMessage($_REQUEST["msg"]);
               break;
//                case "del":
//                    if (isset($_REQUEST["id"]) && FSystem::isAdmin()) {
//                        FDB::$FDB->query("DELETE FROM chatmsg WHERE id=" . FDB::$FDB->real_escape_string($_REQUEST["id"]));
//                        $html = "true";
//                    } else {
//                        $html = "false";
//                    }
//                    break;
            case "addmsg_noajax":
               if ($_REQUEST["msg"]) {
                  $this->addMessage($_REQUEST["msg"]);
               }
            case 'default':
               FDB::$db->query("UPDATE user SET last_chat_time = " . time() . " WHERE id = " . FSystem::getUser()->getID());
               $html .= '<table id="chat_table_container">
                        <tr>
                        <td>
                        <div id="chat_container">
                        <div id="chat_div">
                        <table id="chat_table" class="chat">';
               $msgarr = $this->getMessages($this->last - self::MAX_MSGS);
               foreach ($msgarr as $msg) {
                  $html .= $msg->createHTML();
               }
               $result = FDB::$db->query('SELECT uid FROM user WHERE id=' . FSystem::getID());
               $uid = mysqli_fetch_object($result)->uid;
               $html .= '</table>
                        </div>
                        <span id="chat_submit">
                            <form action="chat.php" method="POST">
                                <input type="search" class="textinput" placeholder="Chatnachricht schreiben..." name="msg" id="textinput"/>
                                <input type="submit" value="Abschicken"/>
                                <input type="hidden" name="method" value="addmsg_noajax"/>
                                ' . FSystem::getFormAppendix() . '
                            </form>
                        </span>
                        </div>
                        </td>
                        <td id="chatuserlist_container">
                        ' . $this->getUserListHTML() . '
                        </td>
                        </tr>
                        </table>';
//                        <script>
//                            chatUpdateInitialize(' . $this->last . ', ' . FSystem::getID() . ', "' . $uid . '", "ws://localhost:8000/chatserver", "' . FSystem::getURLAppendix() . '");
//                            $("#chat_submit").html("<input type=\'text\' id=\'textinput\'/><span class=\'button\' id=\'textsubmit\' onclick=\'chatSendMessage()\'>Abschicken</span>");
//                         </script>';
               break;
         }
      }
      return $html;
   }

   /**
    * Gibt alle Nachrichten als Array zurück, deren ID mindestens die mit <u>$begin</u>
    * spezifierte ist
    * 
    * @param integer|string $begin ID der Anfangsnachricht
    * @return Message[] Nachrichtenarray 
    */
   public function getMessages($begin) {
      $msgarr = array();
      FDB::connect();
      $result = FDB::$db->query("SELECT * FROM chatmsg WHERE id >= " . FDB::$db->real_escape_string($begin));
      while ($line = mysqli_fetch_array($result)) {
         $msgarr[] = new Message($line["content"], $line["user_id"], $line["time"], $line["id"]);
      }
      return $msgarr;
   }

   /**
    * Gibt alle Nachrichten als Array zurück, deren ID mindestens die mit <u>$begin</u>
    * spezifierte ist und höchstens die mit <u>$last</u> spezifierte ist
    * 
    * @param integer|string $begin ID der Anfangsnachricht
    * @param integer|string $last ID der letzten Nachricht
    * @return Message[] Nachrichtenarray 
    */
   public function getMessageRange($begin, $last) {
      $msgarr = array();
      FDB::connect();
      $result = FDB::$db->query("SELECT * FROM chatmsg WHERE id >= " . FDB::$db->real_escape_string($begin) . " AND id <= " . FDB::$db->real_escape_string($last));
      while ($line = mysqli_fetch_array($result)) {
         $msgarr[] = Message::createFromFDBResult(result);
//            $msgarr[] = new Message($line["content"], $line["user_id"], $line["time"], $line["id"]);
      }
      return $msgarr;
   }

   /**
    * Fügt dem Chat eine neue Nachricht hinzu und gibt diese zurück
    * 
    * @param string $msg Inhalt der Nachricht
    * @return Message Nachricht
    * @see Message::store()
    */
   public function addMessage($msg) {
      $filtered_text = FSystem::filter($msg);
      if (stripos($filtered_text, "help") === 0 || stripos($filtered_text, "cmd_") === 0) {
         $filtered_text = $this->processCommand($filtered_text);
      }
      return Message::store($filtered_text, FSystem::getUser(), time());
   }

   /**
    * Gibt die aktuellen Benutzer des Chats als Array zurück
    * 
    * @return User[] Benutzerarray
    */
   public static function getUsers() {
      $userarr = array();
      FDB::connect();
      $result = FDB::$db->query("SELECT id FROM user WHERE last_chat_time > " . (time() - self::EXPIRE_TIME));
      while ($line = mysqli_fetch_array($result)) {
         $userarr[] = FSystem::getUserById($line["id"]);
      }
      return $userarr;
   }

   /**
    * Gibt die Liste der aktuellen Benutzer als einfaches HTML-Array zurück.
    * 
    * @return string HTML-Code (<div id="chatuserlist" class="chat">
    * [<span class="chatuserlistitem"></span>]*
    * </div>)
    * 
    */
   public static function getUserListHTML() {
      $html = '<div id="chatuserlist" class="chat">
            <span id="chatuserlist_header">Benutzer</span><br/>';
      $result = FDB::$db->query("SELECT name FROM user WHERE last_chat_time > " . (time() - self::EXPIRE_TIME));
      while ($line = mysqli_fetch_array($result)) {
         $html .= '<span class="chatuserlistitem">' . $line["name"] . "</span><br/>";
      }
      return $html . '</div>';
   }

   /**
    * Gibt den Titel der Seite zurück
    * 
    * @return string Titel
    * @uses FSystem::buildPageTitle()
    */
   public function getPageTitle() {
      return FSystem::buildPageTitle('Chat');
   }

   /**
    * Alias für die {@link Chat->createHTML()} Methode
    * 
    * @param string Methodenstring
    * @return string HTML-Code
    */
   public function createPageContentHTML($method = "default") {
      return $this->createHTML($method);
   }

   /**
    * Gibt die aktuelle Anzahl der Benutzer des Chats zurück
    * 
    * @return integer Benutzeranzahl
    */
   public function getUserCount() {
      if ($this->user_count) {
         $this->user_count = mysqli_fetch_object(FDB::$db->query("SELECT count(*) AS anz FROM user WHERE last_chat_time > " . (time() - self::EXPIRE_TIME)))->anz;
      }
      return $this->user_count;
   }

   /**
    * Löscht die letzten Nachrichten des aktuellen Benutzers
    * 
    * @param integer $count Anzahl der zu löschenden Nachrichten
    */
   public function deleteLast($count) {
      FDB::connect();
      FDB::$db->query('DELETE FROM chatmsg WHERE user_id=' . FSystem::getUser()->getID() . ' ORDER BY time DESC LIMIT ' . $count);
   }

   /**
    * Interpretiert das Kommando
    * 
    * @param string $command Kommando
    * @param boolean $span Ausgabe mit div-Container umfassen?
    * @return string Ausgabe
    */
   public function processCommand($command, $div = true) {
      $cmd_arr = explode(" ", $command);
      $cmd = $cmd_arr[0];
      $val = isset($cmd_arr[1]) ? $cmd_arr[1] : '';
      $retstr = '';
      switch ($cmd) {
         case 'cmd_pi':
            $retstr .= '&pi;: ' . pi();
            break;
         case 'cmd_e':
            $retstr .= 'e: 2.718281828';
            break;
         case 'cmd_quote':
            $retstr .= Quote::getRandomQuote();
            break;
         case 'cmd_unixtime':
            $retstr .= 'Unixtime: ' . time() . ' 0x' . dechex(time());
            break;
         case 'cmd_dectohex':
            $retstr .= dechex($val);
            break;
         case 'cmd_hextodec':
            $retstr .= hexdec($val);
            break;
         case 'cmd_dectobin':
            $retstr .= decbin($val);
            break;
         case 'cmd_bintodec':
            $retstr .= bindec($val);
            break;
         case 'cmd_sqrt':
            $retstr .= sqrt($val);
            break;
         case 'cmd_for':
            $cmd_str = join(' ', array_slice($cmd_arr, 2));
            $count = 1;
            for ($i = 0; $i < intval($cmd_arr[1]); $i++) {
               if (isset($cmd_arr[3]) && stripos($cmd_arr[3], '$i') >= 0) {
                  $retstr .= $this->processCommand(str_replace('$i', $i, $cmd_str, $count), false) . '<br/>';
               } else {
                  $retstr .= $this->processCommand($cmd_str, false) . '<br/>';
               }
            }
            break;
         case 'cmd_echo':
            $retstr = $val;
            break;
         case 'cmd_dellast':
            if ($val != '') {
               $this->deleteLast(FDB::$db->real_escape_string($val));
            } else {
               $this->deleteLast(1);
            }
            break;
         case "help":
            $retstr .= '<b>Hilfe</b><br/>
               Kommando - Beschreibung<br/>';
            foreach ($this->cmds as $value) {
               $retstr .= '<small><b>' . $value[0] . '</b> - ' . $value[1] . '</small><br/>';
            }
            break;
         default:
            $retstr = 'Syntaxerror: Unknown command "' . $cmd . '"';
            break;
      }
      if ($div) {
         $retstr = '<div class="cmd_result">' . $retstr . '</div>';
      }
      return $retstr;
   }

}

?>
