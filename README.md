# PHP & JS - Hashcash / Proof of work Concept
![Hashcash](/hashcash.png)

This project allows for you to add a proof of work system called hashcash, which forces a resource hit on the client to process a configurable workload process before proceeding. Originally based on the concepts outlined in http://geekportfolio.com/casestudy/hashcash.xhtml and https://github.com/007/hashcash-js. The underlying hash cipher was changed from crc32,md5 to sha512 to leave room for future change, this provides enough bits for any foreseeable future to work with. 

The process works by setting up an amount of characters to generation into a string, which is stored as $hc_maxcoll which by default is 32 characters in length. This string is randomly generated initially server side to produce an original hash, then an agreed upon bit contract is setup which is $hc_contract by default is 12 bits of difficulty, where the client side has to iterate through a loop of generating $hc_maxcoll sized strings till it finds a matching collision at the dynamic offset that is $hc_offset during the original hidden form fields. Also a tolerance can be setup default is two minutes, which requires the calculation to be completed within that time frame as a means to prevent reuse and replay attacks. Another security feature is the use of adding metadata from sources within your webapp such as session values like useragent, ip address, login time, and so forth. 

Setting up the class construct:
```
include "class_hashcash.php";
$HC = new hashcash('DEMOSALT',12,32,2,1);
$metadata = $_SERVER['REMOTE_ADDR'];
```
$hc_salt = 'hashcash',
$hc_contract = 12,
$hc_maxcoll = 32,
$hc_tolerance = 2,
$hc_debug = 1,

The class construct defaults include the webapp/product salt, initial bit contract length, collision string size, time tolerance value in minutes, and debug mode or not.

```
<?php echo $HC->hc_CreateStamp($metadata); ?>
```
Next function is to add the initial stamp and hidden fields to your current form. You will also need to add onsubmit="hc_SpendHash()" to trigger the JavaScript spend function, which does the client side calculations and populates the hidden field values during form submission. The $metadata input is optional, in the demo the metadata is the client's IP address.

```
if ($HC->hc_CheckStamp($metadata)) {
  echo "PASSED\n";
} else {
  echo "FAILED\n\n";
  echo $HC->hc_dump_errorlog();
}
```
After the form is submitted to check the user's calculations you use $HC->hc_CheckStamp($metadata) which must include the same metadata used to create the initial form fields. This returns a true or false based on if the calculations are correct or not, if their not you can pull the error log to provide feedback to why the check failed. Such as the time tolerance was exceeded.

