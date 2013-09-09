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
 * @package SampuSnustu
 * @subpackage Utils 
 */
/**
 * 
 * Datenbankverbindungsobjekt
 * @var mysqli
 */
$db = null;
$server = "localhost";
$username = "root";
$database = "sampusnustu";
$password = "";

/*
 * Datenbank falls nicht vorhanden erzeugen
 */
$db = new mysqli($server, $username, $password);
$db->query('CREATE DATABASE IF NOT EXISTS `' . $database . '`') or die("Datenbankerzeugung: " . mysqli_error($db));
$db->close();

echo 'Datenbank erfolgreich erzeugt.<br/>';

$db = new mysqli($server, $username, $password, $database);

/**
 * Notwendige Tabellen erzeugen, wenn sie nicht vorhanden sind
 */
$db->query("CREATE TABLE IF NOT EXISTS chatmsg(id INT NOT NULL AUTO_INCREMENT, time BIGINT, user_id INT, content TEXT, uid VARCHAR(100), PRIMARY KEY(id))") or die("Chatmsg-Tabelle: " . mysqli_error($db));
$db->query("CREATE TABLE IF NOT EXISTS user(id INT NOT NULL AUTO_INCREMENT, name VARCHAR(50), mailadress VARCHAR(50), isadmin INT, pwdstring VARCHAR(200), ctime BIGINT, mtime BIGINT, signatur TEXT, description TEXT, active INT, last_chat_time BIGINT, last_forum_time BIGINT, sessionid VARCHAR(200), uid VARCHAR(120), PRIMARY KEY(id))") or die("User-Tabelle: " . mysqli_error($db));
$db->query("CREATE TABLE IF NOT EXISTS thread(id INT NOT NULL AUTO_INCREMENT, title TEXT, description TEXT, parent INT, count INT, ctime BIGINT, mtime BIGINT, uid VARCHAR(120), creator INT, PRIMARY KEY(id))") or die("Thread-Tabelle: " . mysqli_error($db));
$db->query("CREATE TABLE IF NOT EXISTS forum(id INT NOT NULL AUTO_INCREMENT, title TEXT, description TEXT, parent INT, ctime BIGINT, mtime BIGINT, uid VARCHAR(120), creator INT, PRIMARY KEY(id))") or die("Forum-Tabelle: " . mysqli_error($db));
$db->query("CREATE TABLE IF NOT EXISTS threadentry(id INT NOT NULL AUTO_INCREMENT, content TEXT, threadid INT, forumid INT, creator INT, ctime BIGINT, mtime BIGINT, editnote TEXT, uid VARCHAR(120), PRIMARY KEY(id))") or die("Threadentries-Tabelle: " . mysqli_error($db));
$db->query("CREATE TABLE IF NOT EXISTS captcha(id CHAR(20), text VARCHAR(10), time BIGINT, PRIMARY KEY(id))") or die("Captcha-Tabelle: " . mysqli_error($db));

echo 'Tabellen erfolgreich erzeugt<br/>';

/* Mit notwendigen Werten füllen */
/* Erzeugen des Hauptforum */
//Forum::store(1, -1, 'Hauptforum', 'Hauptforum dieses Forums') or die("Hauptforumerzeugung: " . mysqli_error($db));
$db->query("INSERT INTO forum(id, title, description, parent, ctime, mtime, uid, creator) VALUES(1, 'Hauptforum', 'Hauptforum dieses Forums', -1, " . time() . ", " . time() . ", '" . FSystem::createUID() . "', '0')");
echo 'Hauptforum erfolgreich erzeugt<br/>';
/**
 * Name des Root Benutzers
 * Root Benutzer = Erster Admin der Seite
 */
$rootname = 'root';
/**
 * Passwort des Root Benutzers
 */
$rootpassword = 'HalloWelt';
/**
 * Email-Adresse des Root Benutzers
 */
$rootmailadress = 'johannes.bechberger@online.de';
FSystem::addUser($rootname, $rootmailadress, true, $rootpassword, 'Erster Administrator dieses Forums', '') or die("Rootbenutzererzeugung: " . mysqli_error($db));
echo 'Rootbenutzer erfolgreich erzeugt.';