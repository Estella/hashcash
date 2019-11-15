/*******************************************************************************************************/
// hashcash.js
/*******************************************************************************************************/
var g_ProcessFlag = 0;
var g_PageContract = 0;
var g_GenSize = 0;
var g_Col_Hash = '';
var g_PSBits = '';
var g_Offset = 0;
/*******************************************************************************************************/
function hc_SetFormData(x, y) {
  var z = document.getElementById(x);
  if(z) { z.value = y; }
}
/*******************************************************************************************************/
function hc_GetFormData(x) {
  var z = document.getElementById(x);
  if(z) {
    return z.value;
  } else {
    return '';
  }
}
/*******************************************************************************************************/
function hc_HexInBin(x) {
  var ret = '';
  switch(x.toUpperCase()) {
    case '0': ret = '0000'; break; case '1': ret = '0001'; break;
    case '2': ret = '0010'; break; case '3': ret = '0011'; break;
    case '4': ret = '0100'; break; case '5': ret = '0101'; break;
    case '6': ret = '0110'; break; case '7': ret = '0111'; break;
    case '8': ret = '1000'; break; case '9': ret = '1001'; break;
    case 'A': ret = '1010'; break; case 'B': ret = '1011'; break;
    case 'C': ret = '1100'; break; case 'D': ret = '1101'; break;
    case 'E': ret = '1110'; break; case 'F': ret = '1111'; break;
    default : ret = '0000';
  }
  return ret;
}
/*******************************************************************************************************/
function hc_ExtractBits(hex_string, num_bits, offset) {
  var bit_string = "";
  var num_chars = Math.ceil(num_bits / 4);
  for(var i = 0; i < num_chars; i++) {
    bit_string = bit_string + "" + hc_HexInBin(hex_string.charAt(offset+i));
  }
  bit_string = bit_string.substr(0, num_bits);
  return bit_string;
}
/*******************************************************************************************************/
function hc_CheckContract(pg_contract, pg_sbits, col_string, offset) {
  var col_hash = hc_HashFunc(col_string);
  var check_bits = hc_ExtractBits(col_hash, pg_contract, offset);
  return (check_bits == pg_sbits);
}
/*******************************************************************************************************/
function hc_GenChars(x) {
  var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
  var randomstring = '';
  for (var i = 0; i < x; i++) {
    randomstring += chars.substr(Math.floor(Math.random() * chars.length), 1);
  }
  return randomstring;
}
/*******************************************************************************************************/
function hc_SpendHash() {
  var PageStamp = hc_GetFormData('hc_stamp');
  g_PageContract = parseInt(hc_GetFormData('hc_contract'));
  g_GenSize = parseInt(hc_GetFormData('hc_collision'));
  g_Offset = parseInt(hc_GetFormData('hc_offset'));
  g_PSBits = hc_ExtractBits(PageStamp, g_PageContract, g_Offset);
  if(!(g_GenSize > 1)) g_GenSize = 32;
  if(g_ProcessFlag == 0) {
    var Collision = hc_GenChars(g_GenSize);
    var looper = 1;
    while(!hc_CheckContract(g_PageContract, g_PSBits, Collision, g_Offset)) {
      Collision = hc_GenChars(g_GenSize);
      looper++;
    }
    hc_SetFormData('hc_collision', Collision);
  }
  return true;
}
/*******************************************************************************************************/
// EOF
