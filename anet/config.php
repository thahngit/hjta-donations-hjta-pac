<?php

  // will be config.php for HJTA account


   // includes - required files
   $_root  =$_SERVER['DOCUMENT_ROOT'];

   // 10/20/2014
   require dirname(__FILE__) . '/AuthorizeNet.php' ;




$METHOD_TO_USE = "AIM";

//TRUE goes to real account.
// put live account into TEST MODE to test it there
//define("TEST_REQUEST", "TRUE");           // You may want to set to true if testing against production

// PP13 FAKE TEST ACCOUNT
//define("AUTHORIZENET_API_LOGIN_ID","7Xw6vhR3P");    
//define("AUTHORIZENET_TRANSACTION_KEY","5AndJM6KpQ5464qK"); 
//define("AUTHORIZENET_SANDBOX",true);       // Set to false to test against production
//define("TEST_REQUEST", "TRUE");           // You may want to set to true if testing against production

// PP13 REAL ACCOUNT
define("AUTHORIZENET_API_LOGIN_ID","4B235BRm9Bb6");    
define("AUTHORIZENET_TRANSACTION_KEY","9Mv8677WS5Vfzz4J"); 
define("AUTHORIZENET_SANDBOX",false);       // TRUE goes to FAKE MERCHANT ACCOUNT ONLY
//define("AUTHORIZENET_SANDBOX",true);       // Set to false to test against production
//define("TEST_REQUEST", "TRUE");           // You may want to set to true if testing against production



?>
