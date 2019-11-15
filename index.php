<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
include "class_hashcash.php";
$HC = new hashcash('DEMOSALT',12,32,2,1);
$metadata = $_SERVER['REMOTE_ADDR'];
?>
<html>
<head>
<title>Hashcash Demo</title>
<script type="text/javascript" src="sha512.js"></script>
<script type="text/javascript" src="hashcash.js"></script>
<style type="text/css">
span.marker { color: red; font-weight: bold; }
</style>
</head>
<body>
<?php if ($_SERVER['REQUEST_METHOD'] == "GET") { ?>
<form id="stampform" action="index.php" method="post" onsubmit="hc_SpendHash()">
<?php echo $HC->hc_CreateStamp($metadata); ?>
<input type="submit" value="TEST!">
<?php } else if ($_SERVER['REQUEST_METHOD'] == "POST") { ?>
<br />
<pre>
<?php
if ($HC->hc_CheckStamp($metadata)) {
  echo "PASSED\n";
} else {
  echo "FAILED\n\n";
  echo $HC->hc_dump_errorlog();
}
echo "\n\n\n";
echo $HC->hc_dump_debuglog();
?>
</pre>
<br /><br />
<form id="stampform" action="index.php" method="post" onsubmit="hc_SpendHash()">
<?php echo $HC->hc_CreateStamp($metadata); ?>
<input type="submit" value="TEST!">
<?php } ?>
</form>
</body>
</html>
