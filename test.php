
<html>
<?php
// hello world
if ($c=OCILogon("ora_u4e7", "a71174098", "ug")) {
  echo "Successfully connected to Oracle.\n";
  OCILogoff($c);
} else {
  $err = OCIError();
  echo "Oracle Connect Error " . $err['message'];
}

?>
</html>
