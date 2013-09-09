<?php

/*
 * Copyright (C) 2011 Julian
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
 * Description of Register
 *
 * @author Julian
 */
class Register implements Page {

   /**
    * erzeugt den kompletten HTML-Code fï¿½r die Registrierungsseite.
    * @param type $method
    * @return string 
    */
   public function createPageContentHTML($method = "default") {
      if (isset($_REQUEST["method"]) && isset($_REQUEST["new_captcha"]) && isset($_REQUEST["captcha_id"])) {
         Captcha::delete($_REQUEST["captcha_id"]);
         $captcha = new Captcha();
         return "<form action='' method='post'>
                    <h1>Registrieren</h1>
                    <table border=1>
                        <tr><td>Username: </td><td><input type='text' name='username' value='" . (isset($_REQUEST["username"]) ? $_REQUEST["username"] : "") . "'/></td></tr>
                        <tr><td>E-Mail: </td><td><input type='text' name='mailadress' value='" . (isset($_REQUEST["mailadress"]) ? $_REQUEST["mailadress"] : "") . "'/></td></tr>
                        <tr><td>Passwort: </td><td><input type='password' name='passwort' value='" . (isset($_REQUEST["passwort"]) ? $_REQUEST["passwort"] : "") . "'/></td></tr>
                        <tr><td>Passwort best&auml;tigen: </td><td><input type='password' name='passwortbest' value='" . (isset($_REQUEST["passwortbest"]) ? $_REQUEST["passwortbest"] : "") . "'/></td></tr>
                        <tr><td colspan='2'>" . $captcha->createHTML() . "</td></tr>
                        <tr><td>Captcha: </td>
                            <td><input type='text' name='captcha_text' placeholder='Buchstaben'/><br/>
                            <input type='submit' name='new_captcha' value='Neues Captcha'/>
                            <input type='hidden' name='captcha_id' value='" . $captcha->getID() . "'/>
                        </td></tr>
                    </table>
                    <input type='submit' value='Registrieren' />
                    <input type='hidden' name='method' value='register' />
                </form>";
      }
      if (isset($_REQUEST[method]) &&
              $_REQUEST[method] == "register" &&
              isset($_REQUEST[username]) &&
              isset($_REQUEST[mailadress]) &&
              isset($_REQUEST[passwort]) &&
              isset($_REQUEST[passwortbest]) &&
              isset($_REQUEST[username]) &&
              ($_REQUEST[passwort] == $_REQUEST[passwortbest]) &&
              ($_REQUEST[passwort]) &&
              $_REQUEST["captcha_text"] &&
              $_REQUEST["captcha_id"] &&
              Captcha::checkAndDelete($_REQUEST["captcha_id"], $_REQUEST["captcha_text"])
      ) {

         $geklappt = FSystem::addUser($_REQUEST['username'], $_REQUEST['mailadress'], false, $_REQUEST['passwort'], "", "");
         if ($geklappt) {
            $html = "<span class='success'>Sie wurden erfolgreich registriert.</span>";
         } else {
            $html = "<span class='error'>Fehler.</span>" . $seite;
         }
      } else {
         $captcha = new Captcha();
         $html = "
                <form  action='' method='post'>
                    <h1>Registrieren</h1>
                    <table border=1>
                        <tr><td>Username: </td><td><input type='text' name='username' /></td></tr>
                        <tr><td>E-Mail: </td><td><input type='text' name='mailadress'/></td></tr>
                        <tr><td>Passwort: </td><td><input type='password' name='passwort' value=''/></td></tr>
                        <tr><td>Passwort best&auml;tigen: </td><td><input type='password' name='passwortbest' value=''/></td></tr>
                        <tr><td colspan='2'>" . $captcha->createHTML() . "</td></tr>
                        <tr><td>Captcha: </td>
                            <td><input type='text' name='captcha_text' placeholder='Buchstaben'/><br/>
                            <input type='submit' name='new_captcha' value='Neues Captcha'/>
                            <input type='hidden' name='captcha_id' value='" . $captcha->getID() . "'/>
                        </td></tr>
                    </table>
                    <input type='submit' value='Registrieen' />
                    <input type='hidden' name='method' value='register' />
                </form>";
      }
      return $html;
   }

   public function getPageTitle() {
      return FSystem::buildPageTitle("Registrieren");
   }

}

?>
