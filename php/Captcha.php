<?php

/*
 * Copyright (C) 2012 Johannes Bechberger
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
 * Captcha-Klasse
 *
 * @author Johannes Bechberger
 * @copyright Johannes Bechberger
 * @license http://opensource.org/licenses/gpl-license.php
 * @package SampuSnustu
 * @subpackage Utils
 */
class Captcha {

   /**
    * ID dieses Captchas
    * @var integer
    */
   private $id = '';

   /**
    * Text dieses Captchas
    * @var string
    */
   private $text = '';

   /**
    * Breite dieses Captchas
    * @var integer
    */
   private $width = 200;

   /**
    * Höhe dieses Captchas
    * @var integer
    */
   private $height = 100;

   /**
    * Pfad des Ordners, in dem die Dateien dieses Captchas liegen
    * @var string
    */
   private $path = '';

   /**
    * Audio-Datei erzeugen?
    * @var boolean
    */
   private $audio = false;

   /**
    * Maximale Anzahl an Zeichen die ein Captcha haben kann
    * @var integer 
    */

   const MAX_LENGTH = 5;

   /**
    * Minimale Anzahl an Zeichen die ein Captcha haben kann
    * @var integer 
    */
   const MIN_LENGTH = 4;

   /**
    * Ordner in dem die Audioteildateien liegen
    * @var string
    */
   const AUDIO_PART_FILE_DIR = '/resources/captcha_audio/';

   /**
    * Ordner in dem die Captchas gespeichert werden
    * @var string 
    */
   const STORE_DIR = '../captchas/';

   /**
    * Dateiname der Audiodatei des Captchas
    * @var string
    */
   const AUDIO_FILENAME = 'audio.wav';

   /**
    * Dateiname der Bilddatei des Captchas
    * @var string
    */
   const IMAGE_FILENAME = 'img.png';

   /**
    * Datei des verwendeten Fonts
    * @var string
    */
   const FONTFILE = '../css/fonts/acttfpc/ATARCE__.TTF';

   /**
    * Zeitdauer in Sekunden, nach der ein Captcha nicht mehr gültig ist und
    * gelöscht wird
    * @var integer
    */
   const EXPRIRE_TIME = 200;

   /**
    * Konstruktor der Captcha-Klasse, erzeugt ein Captcha und säubert den
    * Captchaordner wie auch die Datenbank
    * 
    * @param integer $width Breite des Captcha-Bildes
    * @param integer $height Höhe des Captcha-Bildes
    * @param boolean $audio Audiodatei erzeugen?
    */
   public function __construct($width = 200, $height = 100, $audio = true) {
      $this->width = $width;
      $this->height = $height;
      $this->audio = $audio;
      $this->text = $this->createRandomText();
      $this->id = FSystem::createURLSuitedSalt(15);
      $this->path = __DIR__ . '/' . self::STORE_DIR . $this->id;
      mkdir($this->path);
      if ($audio) {
         $this->createAudioFile();
      }
      $this->createImageFile();
      $this->writeIntoDB();
      self::cleanCaptchaDir();
   }

   /**
    * Erzeugt die Audiodatei des Captchas
    */
   private function createAudioFile() {
      $wavs = array();
      $chars = str_split($this->text, 1);
      foreach ($chars as $char) {
         $wavs[] = __DIR__ . '/' . self::AUDIO_PART_FILE_DIR . $char . '.wav';
      }
      $data = $this->joinwavs($wavs);
      $file = fopen($this->path . '/' . self::AUDIO_FILENAME, 'w');
      fwrite($file, $data);
   }

   /**
    * Verbindet mehrere Wav-Dateien zu einem Datenarray, via
    * http://www.splitbrain.org/blog/2006-11/15-joining_wavs_with_php
    * @param String[] $wavs Wav-Dateinamen
    * @return array() Datenarray der zusammengefügten Dateien
    * @copyright www.splitbrain.org/
    * @author www.splitbrain.org/
    */
   private function joinwavs($wavs) {
      $fields = join('/', array('H8ChunkID', 'VChunkSize', 'H8Format',
          'H8Subchunk1ID', 'VSubchunk1Size',
          'vAudioFormat', 'vNumChannels', 'VSampleRate',
          'VByteRate', 'vBlockAlign', 'vBitsPerSample'));
      $data = '';
      $header = '';
      foreach ($wavs as $wav) {
         $fp = fopen($wav, 'rb');
         $header = fread($fp, 36);
         $info = unpack($fields, $header);
         // read optional extra stuff
         if ($info['Subchunk1Size'] > 16) {
            $header .= fread($fp, ($info['Subchunk1Size'] - 16));
         }
         // read SubChunk2ID
         $header .= fread($fp, 4);
         // read Subchunk2Size
         $size = unpack('vsize', fread($fp, 4));
         $size = $size['size'];
         // read data
         $data .= fread($fp, $size * 10);
      }
      return $header . pack('V', strlen($data)) . $data;
   }

   /**
    * Erzeugt die Bilddatei des Captchas
    */
   private function createImageFile() {
      $image = imagecreate($this->width, $this->height);
      $chars = str_split($this->text, 1);
      $w = $this->width - ($this->width / 4);
      $x = ($this->height / 8);
      $minfontsize = $this->height / 4;
      $maxfontsize = $this->height / 1.7;
      $blue = imagecolorallocate($image, 0, 0, 255);
      imagefilledrectangle($image, 0, 0, $this->width, $this->height, imagecolorallocatealpha($image, 255, 255, 255, 127));
      for ($i = 0; $i < count($chars); $i++) {
         //imagechar($image, 10, $x, $y, $chars[$i], $blue);
         $fontsize = rand($minfontsize, $maxfontsize);
         imagettftext($image, $fontsize, rand(0, 30), $x, $this->height - ($fontsize / 2), $blue, __DIR__ . '/' . self::FONTFILE, $chars[$i]);
         $x += $w / count($chars) - rand(-$minfontsize / 3, $minfontsize / 3); //$i + rand($this->width / count($chars) / 3, $this->width / count($chars) / 2);
      }
      for ($i = 0; $i < $this->height * $this->width / 1.5; $i++) {
         imagesetpixel($image, rand(0, $this->width), rand(0, $this->height), $blue);
      }
      imagepng($image, $this->path . '/' . self::IMAGE_FILENAME);
   }

   /**
    * Erzeugt einen zufälligen Captchatext
    * 
    * @return string zufälliger Captchatext bestehend aus Kleinbuchstaben und
    * den Zahlen 0 bis 9
    */
   private function createRandomText() {
      return FSystem::createURLSuitedSalt(rand(self::MIN_LENGTH, self::MAX_LENGTH));
   }

   /**
    * Schreibt das Captcha in die Datenbank 
    */
   private function writeIntoDB() {
      FDB::connect();
      FDB::$db->query('INSERT INTO captcha(id, text, time) VALUES("' . FDB::$db->real_escape_string($this->id) . '", "' . FDB::$db->real_escape_string($this->text) . '", ' . time() . ')');
   }

   /**
    * Erzeugt den HTML-Code zur Anzeige des Captchas
    * 
    * @return string HTML-Code 
    */
   public function createHTML() {
      return '<div class="captcha">
    <img src="' . FSystem::URL . 'captchas/' . $this->id . '/' . self::IMAGE_FILENAME . '"/>
    ' . ($this->audio ? '<audio controls="controls">
        <source src="' . FSystem::URL . 'captchas/' . $this->id . '/' . self::AUDIO_FILENAME . '" type="audio/wav" />
        Ihr Browser unterstützt die Audiowiedergabe nicht.
    </audio>' : '') . '
</div>';
   }

   /**
    * Säubert den Captchaordner und die Datenbank von zu alten Captchas 
    */
   public static function cleanCaptchaDir() {
      $dir = opendir(__DIR__ . '/' . self::STORE_DIR);
      while ($cdir = readdir($dir)) {
         $file = __DIR__ . '/' . self::STORE_DIR . $cdir;
         if ($cdir != '.' && $cdir != '..' && filectime($file) < time() - self::EXPRIRE_TIME) {
            unlink($file . '/' . self::AUDIO_FILENAME);
            unlink($file . '/' . self::IMAGE_FILENAME);
            rmdir($file);
         }
      }
      FDB::$db->query('DELETE FROM captcha WHERE time < ' . (time() - self::EXPRIRE_TIME));
   }

   /**
    * Überprüft ob das Captcha mit der übergebenen ID den übergebenen Text hat
    * 
    * @param string $idstr ID
    * @param string $assumed_text Text
    * @return boolean
    */
   public static function check($idstr, $assumed_text) {
      FDB::connect();
      return FDB::$db->query('SELECT text FROM captcha WHERE id="' . FDB::$db->real_escape_string($idstr) . '" AND text="' . FDB::$db->real_escape_string($assumed_text) . '"');
   }

   /**
    * Löscht das Captcha mit der übergebenen ID
    * 
    * @param string $idstr ID
    */
   public static function delete($idstr) {
      FDB::connect();
      FDB::$db->query('DELETE FROM captcha WHERE id="' . FDB::$db->real_escape_string($idstr) . '"');
      rmdir(__DIR__ . '/' . self::STORE_DIR . $idstr);
   }

   /**
    * Überprüft ob das Captcha mit der übergebenen ID den übergebenen Text hat
    * und löscht es dann
    * 
    * @param string $idstr ID
    * @param string $assumed_text Text
    * @return boolean
    */
   public static function checkAndDelete($idstr, $assumed_text) {
      $bool = self::check($idstr, $assumed_text);
      self::delete($idstr);
      return $bool;
   }

   /**
    * Gibt die ID des Captchas zurück
    * 
    * @return string ID
    */
   public function getID() {
      return $this->id;
   }

}

?>
