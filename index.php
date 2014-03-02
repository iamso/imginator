<?php
include('imginator.php');

if(isset($_GET['data'])){
  new imginator($_GET['data'], isset($_GET['text']) && $_GET['text'] != '' ? $_GET['text'] : false);
	exit;
}
else {
  // put your landing page here
}
?>
