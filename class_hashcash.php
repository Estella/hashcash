<?php
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
//
// Based on: https://github.com/007/hashcash-js
// @Contributor Estella Mystagic
// twitter.com/Mystagic
class hashcash {
  private $hc_salt;
  private $hc_contract; // number of bits to collide
  private $hc_maxcoll; // maximum length of data to hash
  private $hc_tolerance; // tolerance, in minutes between stamp generation and expiration
  private $hc_stampsize; // hashsize
  private $hc_debug; // debug mode
  protected $hc_error_log; // error log
  protected $hc_debug_log; // debug log
  public function __construct($hc_salt = 'hashcash',$hc_contract = 12,$hc_maxcoll = 32,$hc_tolerance = 2,$hc_debug = 0,$hc_stampsize = 128) {
    $this->hc_salt = $hc_salt;
    $this->hc_contract = $hc_contract;
    $this->hc_maxcoll = $hc_maxcoll;
    $this->hc_tolerance = $hc_tolerance;
    $this->hc_stampsize = $hc_stampsize;
    $this->hc_debug = $hc_debug;
    $this->hc_error_log = "";
    $this->hc_debug_log = "";
  }
  public function hc_dump_debuglog() { return $this->hc_debug_log; }
  public function hc_dump_errorlog() { return $this->hc_error_log; }
  protected function hc_HashFunc($x) { return strtoupper(hash('sha512',$x)); }
  protected function hc_HexInBin($x) {
    switch($x) {
      case '0': $ret = '0000'; break;
      case '1': $ret = '0001'; break;
      case '2': $ret = '0010'; break;
      case '3': $ret = '0011'; break;
      case '4': $ret = '0100'; break;
      case '5': $ret = '0101'; break;
      case '6': $ret = '0110'; break;
      case '7': $ret = '0111'; break;
      case '8': $ret = '1000'; break;
      case '9': $ret = '1001'; break;
      case 'A': $ret = '1010'; break;
      case 'B': $ret = '1011'; break;
      case 'C': $ret = '1100'; break;
      case 'D': $ret = '1101'; break;
      case 'E': $ret = '1110'; break;
      case 'F': $ret = '1111'; break;
      default: $ret = '0000';
    }
    if ($this->hc_debug) { $this->hc_debug_log .= "ret = $ret\n"; }
    return $ret;
  }
  protected function hc_ExtractBits($hex_string, $num_bits, $hc_offset) {
    $bit_string = "";
    $num_chars = ceil($num_bits / 4);
    $bytehash = "";
    for($i = 0; $i < $num_chars; $i++) {
      $bytehash .= substr($hex_string, ($hc_offset+$i), 1);
      $bit_string .= $this->hc_HexInBin(substr($hex_string, ($hc_offset+$i), 1));
    }
    if ($this->hc_debug) {
      $this->hc_debug_log .= "EXTRACT: Offset: $hc_offset : $bytehash\n";
      $hex_string = str_replace($bytehash, "<span class='marker'>$bytehash</span>",$hex_string);
      $this->hc_debug_log .= "EXTRACT: Requested $num_bits from $hex_string, returned $bit_string as " . substr($bit_string, 0, $num_bits) . "\n";
    }
    return substr($bit_string, 0, $num_bits);
  }
  public function hc_CreateStamp($metadata = '') {
    $now = intval(time() / 60);
    $stamp = $this->hc_HashFunc( hash_hmac('sha256', hash('sha256', ($now . $this->hc_salt), true), hash('sha256', ($metadata . $this->hc_salt), true) ));
    $hc_offset = rand(0,42);
    $output = "";
    $output .= "<input type=\"hidden\" name=\"hc_stamp\" id=\"hc_stamp\" value=\"" . $stamp . "\" />\n";
    $output .= "<input type=\"hidden\" name=\"hc_contract\" id=\"hc_contract\" value=\"" . $this->hc_contract . "\" />\n";
    $output .= "<input type=\"hidden\" name=\"hc_offset\" id=\"hc_offset\" value=\"" . $hc_offset . "\" />\n";
    $output .= "<input type=\"hidden\" name=\"hc_collision\" id=\"hc_collision\" value=\"" . $this->hc_maxcoll . "\" />\n";
    return $output;
  }
  protected function hc_CheckExpiration($a_stamp,$metadata = '') {
    $expired = 1;
    $tempnow = intval(time() / 60);
    for($i = 0; $i < $this->hc_tolerance; $i++) {
      $tmphash = $this->hc_HashFunc( hash_hmac('sha256', hash('sha256', (($tempnow - $i). $this->hc_salt), true), hash('sha256', ($metadata . $this->hc_salt), true) ));
      if ($this->hc_debug) { $this->hc_debug_log .= "EXPIRE: checking hashes:\n\nA: $a_stamp\nB: $tmphash\n"; }
      if($a_stamp === $tmphash) {
        if ($this->hc_debug) { $this->hc_debug_log .= "EXPIRE: stamp matched at T-Minus-" . $i . "\n"; }
        $expired = 0;
        break;
      }
    }
    return !($expired);
  }
  protected function hc_CheckContract($stamp, $collision, $stamp_contract, $hc_offset) {
    if($stamp_contract >= 32) { return false; }
    $maybe_sum = $this->hc_HashFunc($collision);
    if ($this->hc_debug) { $this->hc_debug_log .= "CONTRACT: checking hashes:\n\nA: $stamp\nB: $maybe_sum\n"; }
    $partone = $this->hc_ExtractBits($stamp, $stamp_contract,$hc_offset);
    $parttwo = $this->hc_ExtractBits($maybe_sum, $stamp_contract,$hc_offset);
    if ($this->hc_debug) { $this->hc_debug_log .= "CONTRACT: checking bits:\n\nA: $partone\nB: $parttwo\n"; }
    return (strcmp($partone, $parttwo) == 0);
  }
  public function hc_CheckStamp($metadata = '') {
    $hc_contract = $this->hc_contract;
    $hc_maxcoll = $this->hc_maxcoll;
    $hc_stampsize = $this->hc_stampsize;
    $validstamp = 1;
    $stamp = $_POST['hc_stamp'];
    $client_con = $_POST['hc_contract'];
    $hc_offset = $_POST['hc_offset'];
    $collision = $_POST['hc_collision'];
    if ($this->hc_debug) {
      $this->hc_debug_log .= "INPUT: stamp: $stamp\n";
      $this->hc_debug_log .= "INPUT: hc_contract: $client_con\n";
      $this->hc_debug_log .= "INPUT: hc_offset: $hc_offset\n";
      $this->hc_debug_log .= "INPUT: collision text: $collision\n";
    }
    if ($client_con != $hc_contract) {
      $validstamp = 0;
      $this->hc_error_log .= "INVALID: contract comparison: $client_con and $hc_contract : $validstamp\n";
    }
    if ($validstamp) {
      if(strlen($stamp) != $hc_stampsize) {
        $validstamp = 0;
        $this->hc_error_log .= "INVALID: stamp size: " . strlen($stamp) . " and $hc_stampsize : $validstamp\n";
      }
    }
    if ($validstamp) {
      if(strlen($collision) > $hc_maxcoll) {
        $validstamp = 0;
        $this->hc_error_log .= "INVALID: collision size " . strlen($collision) . " <= $hc_maxcoll : $validstamp";
      }
    }
    if ($validstamp) {
      $validstamp = $this->hc_CheckExpiration($stamp,$metadata);
      if ($this->hc_debug) { $this->hc_debug_log .= "STAGE1: checked expiration: $validstamp\n"; }
    }
    if ($validstamp) {
      $validstamp = $this->hc_CheckContract($stamp, $collision, $client_con, $hc_offset);
    }
    if ($validstamp) {
      if ($this->hc_debug) { $this->hc_debug_log .= "STAGE2: HASHCASH: PASSED\n"; }
    } else {
      if ($this->hc_debug) { $this->hc_debug_log .= "STAGE2: HASHCASH: FAILED\n"; }
    }
    return $validstamp;
  }
}
