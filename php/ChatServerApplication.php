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
require_once 'foreign_code/php-websocket/server/server.php';

/**
 * Websocket-Application, die in der Gesammtheit einen Chatserver darstellt
 * 
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Chat
 */
class ChatServerApplication extends WebSocket\Application\Application {

   /**
    * Array mit allen Verbindungen zu Clients
    * @var WebSocket\Connection[] 
    */
   private $clients_all = array();

   /**
    * Array mit allen Verbindungen zu Clients, die sich authentifiziert haben
    * @var WebSocket\Connection[] 
    */
   private $clients = array();

   /**
    * {id => "Name"}
    * @var mixed[]
    */
   private $client_ids = array();

   /**
    * Logkanal, dieser Klasse
    * @var Logger
    */
   private $log;

   /**
    * Verbindungsaufbau-Handler-Funktion, registriert den Client beim Server
    * 
    * @param WebSocket\Connection $client Client-Connection
    */
   public function onConnect($client) {
      if ($this->log == null) {
         $this->log = Logger::getLogger("ChatServer");
      }
      $id = $client->getClientId();
      $this->clients_pre[$id] = $client;
      $this->log->info("Verbingung mit " . $id . " hergestellt");
   }

   /**
    * Daten-vom-Client-Handler-Funktion, die die Daten, die vom Client kommen,
    * verarbeitet
    * 
    * @param string $data Datum, JSON-String, {"method" => "", "data" => {"id" => "", "pwd" => ""}}
    * @param WebSocket\Connection $client Client-Verbindung
    * @return null 
    */
   public function onData($data, $client) {
      $decodedData = $this->_decodeData($data);
      if (!$decodedData) {
         return;
      }
      if ($decodedData['action'] == "verification") {
         if (!isset($this->clients[$client->getClientId()]) && $this->_verify($decodedData["data"])) {
            $this->clients[$client->getClientId()] = $client;
            $this->client_ids[$client->getClientId()] = $decodedData["id"];
            FDB::$db->query("UPDATE user SET last_chat_time = " . (time() + 100000000) . " WHERE id = " . $this->client_ids[$id]);
            $this->log->info('Verbingung mit ' . $client->getClientId() . ' als Benutzer(' . $decodedData["data"]["id"] . ') verifiziert');
            $this->_sendUserList();
         }
         return;
      }
      $actionName = '_action' . ucfirst($decodedData['action']);
      if (method_exists($this, $actionName) && isset($decodedData["data"]) && $this->_verify($decodedData["data"])) {
         call_user_func(array($this, $actionName), $decodedData["data"]);
      }
   }

   /**
    * Überprüft, ob der Client, von dem das Daten-Array kommt, angemeldet ist
    * 
    * @param array $decodedData Daten-Array
    * @return boolean true => Client ist angemeldet
    */
   private function _verify($decodedData) {
      if (!isset($decodedData["id"]) || !isset($decodedData["pwdstring"])) {
         return false;
      }
      $user = FSystem::getUserById($decodedData["id"]);
      return $user && $user->getPwdstring() == $decodedData["pwdstring"];
   }

   /**
    * Fügt eine Nachricht dem Chat hinzu
    * 
    * @param array $data Daten-Array, mit Nachrichteninhalt und Benutzer-ID, {"content" => "", "uid" => ""}
    */
   public function _actionAddMsg($data) {
      if (isset($data["content"])) {
         $msg = Message::store($data["content"], $data["uid"], time());
         $this->_sendToAll("newmessage", $msg->createHTML());
      }
   }

   /**
    * Client-Verbindungs-Schließen-Handler, löscht die Client-Verbindung aus den
    * jeweiligen Objekteeigentschaften
    * 
    * @param WebSocket\Connection $connection Client-Verbindung
    */
   public function onDisconnect(WebSocket\Connection $connection) {
      $id = $connection->getClientId();
      $this->log->info("Verbingung mit " . $id . " geschlossen");
      unset($this->clients_all[$id]);
      if (isset($this->clients[$id])) {
         FDB::$db->query("UPDATE user SET last_chat_time = " . (time() - 10) . " WHERE id = " . $this->client_ids[$id]);
         unset($this->clients[$id]);
         unset($this->client_ids[$id]);
         $this->_sendUserList();
      }
   }

   /**
    * Schickt das Daten-Array mit dem Methodenstring zu allen verbunden,
    * verifizierten Clients
    *
    * @param string $action Methodenstring
    * @param string $data Daten-Array
    */
   private function _sendToAll($action, $data) {
      $val = $this->_encodeData($action, $data);
      if ($val) {
         foreach ($this->clients as $client) {
            $client->send($val);
         }
      }
   }

   /**
    * Versendet die Benutzerliste als HTML zu allen verfizierten Clients 
    */
   private function sendUserList() {
      $this->_sendToAll("userlisthtml", Chat::getUserListHTML());
   }

}

?>
