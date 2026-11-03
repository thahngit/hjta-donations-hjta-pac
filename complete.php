<?php 

if (session_status() === PHP_SESSION_NONE){
  session_start();
  if (!isset($_COOKIE['session_name()'])) setcookie(session_name(), session_id(), 0, '', '', 0);
}

require_once($_SERVER['DOCUMENT_ROOT']."/common/monologger.php");
//get_logger()->info('monologger verification', array('Called from' => __FILE__));

require '../vendor/autoload.php';
require_once 'ANetConstants.php';
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

$folder = dirname( __FILE__, 3 ) . '/Logs/ANet';
$logfile = $folder . '/' . date('Y-m') . '.anet.pp13.log' ;
define("AUTHORIZENET_LOG_FILE", $logfile);

$_root  =$_SERVER['DOCUMENT_ROOT'];

require_once($_root."/common/utils.php");
require_once($_root."/common/ezMysql_ntw.php");

spl_autoload_register(function ($class) { require_once"{$class}.class.php"; });

require_once($_root."/common/Z_HJTAPACglobals.php");
require_once($_root."/common/connect_REAL.php");

// LIVE SITE
$cust = '0024';
$event = '0103'; 
define( 'DONATE_LINK', isset($_SESSION["LIVE_TESTING"]) && $_SESSION["LIVE_TESTING"] == "Y" ? 'https://donations.hjta.org/hjta/donations_form.php?event=$cust-$event' : "https://donations.hjta.org/Zdon2HJTA/ZZ_0_HJTA_donations_form.php?event=$cust-$event" ) ;
define( 'RENEW_LINK' , isset($_SESSION["LIVE_TESTING"]) && $_SESSION["LIVE_TESTING"] == "Y" ? 'https://donations.hjta.org/hjta/renewals_form.php?event=$cust-$event' : "https://donations.hjta.org/Zdon2HJTA/ZZ_0_HJTA_renewals_form.php?event=$cust-$event" ) ;
$cust = '0024';
$event = '0103'; 
define( 'HOME_LINK', isset($_SESSION["LIVE_TESTING"]) && $_SESSION["LIVE_TESTING"] == "Y" ? 'https://donations.hjta.org/ProtectProp13/donations_form.php?event=$cust-$event' : "https://donations.hjta.org/Zdon2PP13/ZZ_0_PP13_donations_form.php?event=$cust-$event" ) ;

require_once($_root."/email_groups/email_groups.php");
require_once($_root."/common/email_funcs.php");

//
//////////////////////////////////////////////////////
//
//  set to the user defined error handler
//dbine.ntw:  Replaced with monologger.php $old_error_handler=set_error_handler("myErrorHandler");
date_default_timezone_set("America/Los_Angeles");

// incoming

// sid & SESSION 
//session_start();
$sid             = $_POST['BOTH_sid'] ; /// this is new for PP13 2020
$_SESSION["sid"] = $sid;  /// WTF - why did you have 2 ???


/// OLD ARGS
//  // Update Args
//  $visitor_Args = array() ;
//  $visitor_Args['sid']             = $_POST['sid'] ;
//  $visitor_Args['fname']           = ucfirst(strtolower($_POST['fname']));
//  $visitor_Args['lname']           = ucfirst(strtolower($_POST['lname']));
//  $visitor_Args['addr']            = $_POST['addr'] ;
//  $visitor_Args['addr2']           = $_POST['addr2'];
//  $visitor_Args['city']            = $_POST['city'] ;
//  $visitor_Args['state']           = $_POST['state'];
//  $visitor_Args['zip']             = $_POST['zip'] ;
//  $visitor_Args['email']           = $_POST['email']; 

//  $visitor_Args['contrib']         = $_POST['contrib'] ;       
//  $visitor_Args['cc_last5']        = $_POST['cc_last5'] ;  

//  THESE ARE NO LONGER USED ?  - REPLACED BY TWO NEW SETS
//  $visitor_Args['occupation']      = $_POST['occupation'];    
//  $visitor_Args['employer']        = $_POST['employer']   ;    
//  $visitor_Args['corp_card']       = $_POST['corp_card'] ;     
//  $visitor_Args['corp_name']       = $_POST['corp_name'] ;     
//  $visitor_Args['corp_address']    = $_POST['corp_address'] ;   
//  $visitor_Args['corp_city']       = $_POST['corp_city'] ;     
//  $visitor_Args['corp_state']      = $_POST['corp_state'];
//  $visitor_Args['corp_zip']        = $_POST['corp_zip']; 
//  $visitor_Args['cc_type']         = $_POST['cc_type'] ;    

//  /// charging only DONT SAVE
//  $charge_Args = array() ;
//  $charge_Args['contrib']         = $_POST['contrib'] ;   
//  $charge_Args['cc_name']         = $_POST['cc_name'];
//  $charge_Args['cc_number']       = $_POST['cc_number'];
//  $charge_Args['cc_type']         = $_POST['cc_type'] ;  // yes we do save
//  $charge_Args['cc_month']        = $_POST['cc_month'] ;
//  $charge_Args['cc_year']         = $_POST['cc_year'] ;
//  $charge_Args['cc_cvv']          = $_POST['cc_cvv'] ;
/// end OLD  ARGRS  


//////////////////
////////             New Args for PP13 2020 projects
//////////////////


  /// charging only DONT SAVE
  $charge_Args = array() ;
  $charge_Args['contrib']         = $_POST['BOTH_contrib'] ;   
  $charge_Args['cc_name']         = $_POST['BOTH_cc_name'];
  $charge_Args['cc_number']       = $_POST['BOTH_cc_number'];
  $charge_Args['cc_type']         = $_POST['BOTH_cc_type'] ;  // yes we do save
  $charge_Args['cc_month']        = $_POST['BOTH_cc_month'] ;
  $charge_Args['cc_year']         = $_POST['BOTH_cc_year'] ;
  $charge_Args['cc_cvv']          = $_POST['BOTH_cc_cvv'] ;
  if (isset($_POST['BLZIP'])) :
    $charge_Args['cc_zip']		      = $_POST['BLZIP'] ;
  endif ;
 

  //  // Update Args
  $visitor_Args = array() ;  
  $visitor_Args['sid']             = $_POST['BOTH_sid'] ;
  $visitor_Args['contrib']         = $_POST['BOTH_contrib'] ;   // The $ money amount .... 
  $visitor_Args['cc_last5']        = $_POST['BOTH_cc_last5'] ; // <<<<< BOTH_cc_last5 does NOT exist in SQL schema
  $visitor_Args['cc_type']         = $_POST['BOTH_cc_type']  ; // <<<<< BOTH_cc_type does NOT exist in SQL schema

  // 
  $visitor_Args['BOTH_fromFormType']              = $_POST['BOTH_fromFormType'] ; 
  
  /*
  $visitor_Args['BOTH_IntermediaryType']          = $_POST['BOTH_IntermediaryType'] ; 
  $visitor_Args['BOTH_IntName']                   = $_POST['BOTH_IntName'] ; 
  $visitor_Args['BOTH_IntAddr']                   = $_POST['BOTH_IntAddr'] ; 
  $visitor_Args['BOTH_IntCity']                   = $_POST['BOTH_IntCity'] ; 
  $visitor_Args['BOTH_IntState']                  = $_POST['BOTH_IntState'] ; 
  $visitor_Args['BOTH_IntZip']                    = $_POST['BOTH_IntZip'] ; 
  $visitor_Args['BOTH_IntOCC']                    = $_POST['BOTH_IntOCC'] ; 
  $visitor_Args['BOTH_IntEMP']                    = $_POST['BOTH_IntEMP'] ; 
  */
  //
  $visitor_Args['IFRM_fname']                     = $_POST['IFRM_fname'] ; 
  $visitor_Args['IFRM_lname']                     = $_POST['IFRM_lname'] ; 
  $visitor_Args['IFRM_addr']                      = $_POST['IFRM_addr'] ; 
  $visitor_Args['IFRM_addr2']                     = $_POST['IFRM_addr2'] ; 
  $visitor_Args['IFRM_city']                      = $_POST['IFRM_city'] ; 
  $visitor_Args['IFRM_state']                     = $_POST['IFRM_state'] ; 
  $visitor_Args['IFRM_zip']                       = $_POST['IFRM_zip'] ; 
  $visitor_Args['IFRM_email']                     = $_POST['IFRM_email'] ; 
  $visitor_Args['IFRM_ocup']                      = $_POST['IFRM_ocup'] ;   
  $visitor_Args['IFRM_empl']                      = $_POST['IFRM_empl'] ;   
  
  //
  $visitor_Args['BFRM_name']                      = $_POST['BFRM_name'] ; 
  $visitor_Args['BFRM_addr']                      = $_POST['BFRM_addr'] ; 
  $visitor_Args['BFRM_addr2']                     = $_POST['BFRM_addr2'] ; 
  $visitor_Args['BFRM_city']                      = $_POST['BFRM_city'] ; 
  $visitor_Args['BFRM_state']                     = $_POST['BFRM_state'] ; 
  $visitor_Args['BFRM_zip']                       = $_POST['BFRM_zip'] ; 
  $visitor_Args['BFRM_email']                     = $_POST['BFRM_email'] ; 
  //  
  $visitor_Args['BFRM_DonorType']                 = $_POST['BFRM_DonorType'] ;  
  $visitor_Args['BFRM_BusinessType']              = $_POST['BFRM_BusinessType'] ; 
  $visitor_Args['BFRM_BizTypeOther']              = $_POST['BFRM_BizTypeOther'] ; 
  $visitor_Args['BFRM_SOSID']                     = $_POST['BFRM_SOSID'] ; 
  $visitor_Args['BFRM_ResponsibleIndividualName'] = $_POST['BFRM_ResponsibleIndividualName'] ; 
  

if ( $visitor_Args['BOTH_fromFormType'] == 'INDIVIDUAL' ) :
  $visitor_Args['fname']           = ucfirst(strtolower($_POST['IFRM_fname']));
  $visitor_Args['lname']           = ucfirst(strtolower($_POST['IFRM_lname']));
  $visitor_Args['addr']            = $_POST['IFRM_addr'] ;
  $visitor_Args['addr2']           = $_POST['IFRM_addr2'];
  $visitor_Args['city']            = $_POST['IFRM_city'] ;
  $visitor_Args['state']           = $_POST['IFRM_state'];
  $visitor_Args['zip']             = $_POST['IFRM_zip'] ;
  $visitor_Args['email']           = $_POST['IFRM_email']; 
else:  
  $visitor_Args['fname']           = ucfirst(strtolower($_POST['BFRM_name']));
  $visitor_Args['lname']           = ucfirst(strtolower($_POST['BFRM_name']));
  $visitor_Args['addr']            = $_POST['BFRM_addr'] ;
  $visitor_Args['addr2']           = $_POST['BFRM_addr2'];
  $visitor_Args['city']            = $_POST['BFRM_city'] ;
  $visitor_Args['state']           = $_POST['BFRM_state'];
  $visitor_Args['zip']             = $_POST['BFRM_zip'] ;
  $visitor_Args['email']           = $_POST['BFRM_email']; 

endif ;    
  

////////////////// End new ARGS


/// tracer
hjtaorgLog( 'Stage 1 ' , "Process $sid" , __FILE__ . ' Line: ' . __LINE__ , "PP13");

///////////////// DATABASE  begin
$ez    =new ezMysql_ntw();

//	Do Database stuff
$sid_condition = "sid='". $visitor_Args['sid']   . "'" ;

// Need to determine what VISITTYPE is for later use
$visittype = 'DONATE' ;
$result=$ez->ezSelect_i("*", VISIT_TBL , $sid_condition );
$numb_rows = mysqli_num_rows($result);
if ( $numb_rows == 1) : 
     $data       = mysqli_fetch_object($result);
     $visittype  = trim(strtoupper($data->visittype)) ;
endif ;

// trap to see if there is ALREADY a finalized trasaction with that SID
$already_condition = $sid_condition . " AND completed = 'Y' " ;
$result=$ez->ezSelect_i("*", VISIT_TBL , $already_condition );
$numb_rows = mysqli_num_rows($result);
  
if ( $numb_rows == 1) : // ALREADY DONE !!!
  // go to decline function
  DeclinedForm('Transaction has already completed. Do NOT press BACK ARROW.') ;
  // logit
  hjtaorgLog( 'HALT Stage 1 ' , "Duplicate charge attempt found. STOPPED. $sid"  , __FILE__ . ' Line: ' . __LINE__ , "PP13");
  // debug
  // DEBUG TRACER
  hjtaorgDBUG( 'STAGE 1 HALTED ' , "DUPLICATE COMPLETED TRANS FOUND $sid" , __FILE__ . ' Line: ' . __LINE__ , "PP13");
  // EXCEPTION PATH TODO :???
  exit() ;
else :
  // ok to proceed
endif ;

//UPDATE trivial/clerical fields
$visitor_updated = $ez->ezUpdate_i(VISITOR_TBL, $visitor_Args, $sid_condition ); //

///  low ball testing
if ( trim($charge_Args['cc_name']) == 'UMA GUMA JR'  ):
  $charge_Args['contrib']   =  '1.01' ;
  $totalContrib             =  '1.01' ;
endif ;


///// ***************************************************
/////  BEGIN AUTHORIZE.NET CHARGE 06/20/2012
///// ***************************************************


/* Latest net\authorize\api replaces AuthorizeNetAIM */

//$sale = new AuthorizeNetAIM;
//$sale->amount     = $charge_Args["contrib"];
//$sale->card_num   = $charge_Args['cc_number'];
//$sale->exp_date   = $charge_Args["cc_month"] . '/' . $charge_Args["cc_year"] ;
//$sale->card_code  = $charge_Args['cc_cvv'] ; 

// take which FORM into account

$donation_fname = "";
$donation_lname = "";
$donation_address = "";
$donation_city = "";
$donation_state = "";
$donation_zip = "";
$donation_email = "";
$donation_company = "";

if ( $visitor_Args['BOTH_fromFormType'] == 'INDIVIDUAL' ) :

    $donation_fname = $visitor_Args['IFRM_fname'] ;
    $donation_lname = $visitor_Args['IFRM_lname'] ;
    $donation_address = $visitor_Args['IFRM_addr'] ;
    $donation_city = $visitor_Args['IFRM_city'];
    $donation_state = $visitor_Args['IFRM_state'] ;
    $donation_zip = $visitor_Args['IFRM_zip'] ;
    $donation_email = $visitor_Args['IFRM_email'] ;
    $donation_company = $visitor_Args['cc_name'] ;
    
    $donor_occupation = $visitor_Args['IFRM_ocup'] ; 
    $donor_employer = $visitor_Args['IFRM_empl'] ;

else : 

    $donor_name = explode(" ", $visitor_Args['BFRM_ResponsibleIndividualName']);
    $donation_fname = $donor_name[0] ;
    $donation_lname = $donor_name[1] ;
    $donation_address = $visitor_Args['BFRM_addr'] ;
    $donation_city = $visitor_Args['BFRM_city'];
    $donation_state = $visitor_Args['BFRM_state'] ;
    $donation_zip = $visitor_Args['BFRM_zip'] ;
    $donation_email = $visitor_Args['BFRM_email'] ;
    $donation_company = $visitor_Args['BFRM_name'] ;
    $donor_business_type = $visitor_Args['BFRM_BusinessType'] ;

endif ; 

if (isset($_POST['BLZIP'])) :
	$donation_zip = $charge_Args['cc_zip'] ;
endif ;


/* Create a merchantAuthenticationType object with authentication details retrieved from the constants file */
$merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
$merchantAuthentication->setName(\ANetConstants::MERCHANT_LOGIN_ID);
$merchantAuthentication->setTransactionKey(\ANetConstants::MERCHANT_TRANSACTION_KEY);

// Set the transaction's refId
$refId = 'ref' . time();

// Create the payment data for a credit card
$creditCard = new AnetAPI\CreditCardType();
$creditCard->setCardNumber($charge_Args['cc_number']);
$creditCard->setExpirationDate($charge_Args["cc_month"] . '/' . $charge_Args["cc_year"]);
$creditCard->setCardCode($charge_Args['cc_cvv']);

// Add the payment data to a paymentType object
$paymentOne = new AnetAPI\PaymentType();
$paymentOne->setCreditCard($creditCard);

// Create order information
$order = new AnetAPI\OrderType();
$order->setInvoiceNumber(substr($visitor_Args['sid'], 0, 9) . "-" . substr($visitor_Args['sid'], strpos($visitor_Args['sid'], "----")+4, 10)); //string too long exception if try to log entire session id + time
$order->setDescription("HJTA-PAC:" . $visittype);

// Set the customer's Bill To address
$customerAddress = new AnetAPI\CustomerAddressType();
$customerAddress->setFirstName($donation_fname );
$customerAddress->setLastName($donation_lname);
$customerAddress->setCompany($donation_company);
$customerAddress->setAddress($donation_address);
$customerAddress->setCity($donation_city);
$customerAddress->setState($donation_state);
$customerAddress->setZip( $donation_zip);
$customerAddress->setCountry("USA");

// Set the customer's identifying information
$customerData = new AnetAPI\CustomerDataType();
$customerData->setType("individual"); //dbine.ntw: I think it's okay to set type as "individual" even if it's a business donation, from the Authorize.Net credit card processing side there's no distinction
$customerData->setId(substr($visitor_Args['sid'], 0, 9) . "-" . substr($visitor_Args['sid'], strpos($visitor_Args['sid'], "----")+4, 10)); //string too long exception if try to log entire session id + time
$customerData->setEmail($donation_email);

// Add values for transaction settings
$duplicateWindowSetting = new AnetAPI\SettingType();
$duplicateWindowSetting->setSettingName("duplicateWindow");
$duplicateWindowSetting->setSettingValue("60");

// Create a TransactionRequestType object and add the previous objects to it
$transactionRequestType = new AnetAPI\TransactionRequestType();
$transactionRequestType->setTransactionType("authCaptureTransaction"); 
$transactionRequestType->setAmount($charge_Args["contrib"]);
$transactionRequestType->setOrder($order);
$transactionRequestType->setPayment($paymentOne);
$transactionRequestType->setBillTo($customerAddress);
$transactionRequestType->setCustomer($customerData);
$transactionRequestType->addToTransactionSettings($duplicateWindowSetting);
$transactionRequestType->setCustomerIP($_SERVER['REMOTE_ADDR']);

// Assemble the complete transaction request
$request = new AnetAPI\CreateTransactionRequest();
$request->setMerchantAuthentication($merchantAuthentication);
$request->setRefId($refId);
$request->setTransactionRequest($transactionRequestType);

// Create the controller and get the response
$controller = new AnetController\CreateTransactionController($request);
$response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION); //PRODUCTION

$r_approved = FALSE;
$r_declined = FALSE;
$r_error = FALSE;
$r_error_message = "";

$r_response_reason_text  = "";
$r_authorization_code    = "";
$r_amount                = "";
$transaction_id          = "";

if ($response != null) {
  // Check to see if the API request was successfully received and acted upon
  if ($response->getMessages()->getResultCode() == "Ok") {
      // Since the API request was successful, look for a transaction response
      // and parse it to display the results of authorizing the card
      $tresponse = $response->getTransactionResponse();
  
      if ($tresponse != null && $tresponse->getMessages() != null) {

          $r_approved = TRUE;
          $r_response_reason_text  = $tresponse->getMessages()[0]->getDescription();
          $r_authorization_code    = trim($tresponse->getAuthCode());
          $r_amount                = $charge_Args["contrib"]; //(used to be pulled from response in AuthorizeNetAIM)
          $transaction_id          = $tresponse->getTransId();           

          //echo " Successfully created transaction with Transaction ID: " . $tresponse->getTransId() . "\n";
          // echo " Transaction Response Code: " . $tresponse->getResponseCode() . "\n";
          // echo " Message Code: " . $tresponse->getMessages()[0]->getCode() . "\n";
          //echo " Auth Code: " . $tresponse->getAuthCode() . "\n";
          //echo " Description: " . $tresponse->getMessages()[0]->getDescription() . "\n";
      } else {
          //echo "Transaction Failed \n";
          $r_response_reason_text = "Transaction Failed";
          if ($tresponse->getErrors() != null) {
              //echo " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
              //echo " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
              $r_error_message = $tresponse->getErrors()[0]->getErrorText() . "[" . $tresponse->getErrors()[0]->getErrorCode() . "]";
          }
      }
      // Or, print errors if the API request wasn't successful
  } else {
      //echo "Transaction Failed \n";
      $r_response_reason_text = "API Request Failed";
      $tresponse = $response->getTransactionResponse();
  
      if ($tresponse != null && $tresponse->getErrors() != null) {
          //echo " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
          //echo " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
          $r_error_message = $tresponse->getErrors()[0]->getErrorText() . "[" . $tresponse->getErrors()[0]->getErrorCode() . "]";
      } else {
          //echo " Error Code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
          //echo " Error Message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
          $r_error_message = $response->getMessages()->getMessage()[0]->getText() . "[" . $response->getMessages()->getMessage()[0]->getCode() . "]";
      }
  }
} else {
  //echo  "No response returned \n";
  $r_error_message = "No response returned from payment processor" . "[" . $response->getMessages()->getResultCode() . "]";
}


//'NTW: see where customer id maps in the migration documentation'

if ($r_approved) :

  // convert new vars into old DRCC vars
  $serverRet    = 'SUCCESS' ;
  $respText     = 'APPROVAL ' . $r_authorization_code . ' TRANS ID: ' . $transaction_id ;
  $respErrNum   = '' ;
  $respErrText  = '' ;
  $auth         = 'APPROVAL ' . $r_authorization_code ;
  
  // build reply values for form
  $a_ReplyVals = array() ;
  $a_ReplyVals["trans_id"]    = $transaction_id;
  $a_ReplyVals["amt_charged"] = $charge_Args["contrib"] ;
  $a_ReplyVals["sku"]         = 'HJTA-PAC'  ;
  $a_ReplyVals["visittype"]   = 'HJTA-PAC' ;
  

  /// APPROVED CHARGE
  /// tracer
  hjtaorgLog( 'END Stage 1 ' , "Successful charge. $sid" , __FILE__ . ' Line: ' . __LINE__ , "PP13");

  // CHARGED SUCCEED !!!
  $rv = authStatusUpdate("Y");

  
  // EMAIL STUFF
  $ebody = PP13_Donation_Thanks($visitor_Args['fname']) ;
  zEmail( trim($visitor_Args['email'])  , "Thank you for your donation to HJTA-PAC", $ebody ) ;
  
  
  
  
  // donation email notification
     $amt_charged = $a_ReplyVals["amt_charged"] ;
     $sid         = $visitor_Args['sid'] ;
     $donor       = $donation_fname . ' ' . $donation_lname  ;
     $donor_address = $donation_address . ' ' . $donation_city . ', ' . $donation_state . ' ' . $donation_zip;
     $donor_email = $donation_email;
     $donor_type = $visitor_Args['BOTH_fromFormType'];
     
     $body  = "<!DOCTYPE html><html><head><title>DONATION - HJTA-PAC</title></head><body><div>";
     $body .= "<h3>HJTA-PAC Donation</h3>"; 
     $body .= "Type: $donor_type";
     if ($donor_type == 'BUSINESS') {
         $body .= "<p>Company: $donation_company </p>";
         $body .= "<p>Business Type: $donor_business_type </p>";
         $body .= "<p>Responsible Individual: $donor_name[0] $donor_name[1] </p>";
     }
     if ($donor_type == 'INDIVIDUAL') {
         $body .= "<p>Donor: $donor </p>";
         $body .= "<p>Occupation: $donor_occupation </p>";
         $body .= "<p>Employer: $donor_employer </p>";
     }
     $body .= "<p>Amount: $amt_charged </p>";
     $body .= "<p>Address: $donor_address </p>";
     $body .= "<p>Email: $donor_email  </p>" ;
     $body .= "<p>Transaction: $respText</p>";
     $body .= "<p>Code: 00240103 <br/>See: $sid </p>";
     $body .= "</div></body></html>";
     
     //zEmail( 'tysons_music@yahoo.com'  , "DONATION - HJTA-PAC", $body ) ;
     zEmail( 'emails@datareply.net'  , "DONATION - HJTA-PAC", $body ) ;
     zEmail( 'tricia@bmhlaw.com'  , "DONATION - HJTA-PAC", $body ) ;
     
  
  // NEW PATH 2014 PROJECT Z
  $rv = ApprovedForm( $a_ReplyVals) ;
  
  
else :  // FAILED - DECLINED

   // convert new vars into old DRCC vars
   $serverRet    = '' ;
   //$respText     = $r_response_reason_text   ;
   $respText     = $r_response_reason_text . ' '  . $r_error_message   ; // VERBOSE TO interface GIU & email  
   $respErrNum   = '' ;
   $respErrText  = '' ;
   $auth         = '' ;


  /// tracer
  hjtaorgLog( 'END Stage 1' , "DECLINED charge. $sid" . " $respText . ' '  . $r_error_message " , __FILE__ . ' Line: ' . __LINE__ , "PP13");



  // DECLINED
  $rv   = authStatusUpdate("N");
  
  // new path 2014 Project Zip 
  $rv = DeclinedForm($respText) ;
  
  // EMAIL stuff  
  $ebody = PP13_Donation_Sorry($visitor_Args['fname'], $respText ) ;
  zEmail( trim($visitor_Args['email'])  , "Your HJTA-PAC Transaction Attempt Failed", $ebody ) ;


endif ; // $r_approved


///// ***************************************************
/////  END EVERYTHING 
///// ***************************************************

$PAGE_TITLE = "HJTA Donations | Howard Jarvis Taxpayers Association";
$HEADER_TITLE = "Protect Prop. 13 Secure Online Donation Form";


function authStatusUpdate($auth = "N"){
    //  Add final information to the visit table, showing completion
    //  and authorization status
    //
    global $sid;
    global $serverRet;
    global $respText;
    global $respErrNum;
    global $respErrText;

    // LAST BITS OF COMPLETION
    $mytime    = time();
    $now       = date("Y-m-d H:i:s", $mytime);
    $myArgs['cc_server_stat']    =  $serverRet;
    $myArgs['cc_authorization']  =  $auth;
    $myArgs['cc_respText']       =  $respText;
    $myArgs['cc_respErrNum']     =  $respErrNum;
    $myArgs['cc_respErrText']    =  $respErrText;
    $myArgs['completed']         =  "Y";
    $myArgs['completedate']      =  $now;
    
    $ez    =new ezMysql_ntw();   
    $ez->ezUpdate_i( VISIT_TBL , $myArgs, "sid='".$sid."'"); 
    
    return($auth) ;
} // eof: authStatusUpdate()


function ApprovedForm( $a_ReplyVals) {
	
$vPP13_SDS_ROOT = PP13_SDS_ROOT ;
$vPP13_SD_ROOT  = PP13_SD_ROOT ;

$trans_id    =   $a_ReplyVals["trans_id"]    ;
$amt_charged =   $a_ReplyVals["amt_charged"] ;
$sku         =   $a_ReplyVals["sku"]         ;
$visittype   =   $a_ReplyVals["visittype"]   ;

$vRENEW_LINK  = RENEW_LINK ;
$vDONATE_LINK = DONATE_LINK ;

$PAGE_TITLE = "HJTA Donations | Howard Jarvis Taxpayers Association";
$HEADER_TITLE = "HJTA-PAC SECURE ONLINE DONATION FORM";

include('../global/ui-components/header.php');

/// NEW GTM 09/29/2014
echo "<!-- \n";
echo "Google Tag Manager Data Layer for Google Analytics Ecommerce tracking\n";
echo "-->\n";
echo "<script>\n";
echo "dataLayer = [{\n";
echo "    \"transactionId\": \"$trans_id\",\n";
echo "    \"transactionTotal\": \"$amt_charged\",\n";
echo "    \"transactionProducts\": [{\n";
echo "        \"sku\": \"$sku\",\n";
echo "        \"name\": \"$visittype\",\n";
echo "        \"price\": \"$amt_charged\",\n";
echo "        \"quantity\": 1\n";
echo "    }]\n";
echo "}];\n";
echo "</script>\n";

/// END GTM 
echo "<!-- Google Tag Manager -->\n";
echo "<noscript>\n";
echo "<iframe src=\"//www.googletagmanager.com/ns.html?id=GTM-NQP3DX\" height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\">\n";
echo "</iframe>\n";
echo "</noscript>\n";
echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\"gtm.start\":new Date().getTime(),event:\"gtm.js\"});\n";
echo "var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!=\"dataLayer\"?\"&l=\"+l:\"\";\n";
echo "j.async=true;j.src=\"//www.googletagmanager.com/gtm.js?id=\"+i+dl;\n";
echo "f.parentNode.insertBefore(j,f);\n";
echo "})(window,document,\"script\",\"dataLayer\",\"GTM-NQP3DX\");\n";
echo "</script>\n";
echo "<!-- End Google Tag Manager -->\n";
//
echo "		<div class=\"siteorigin-widget-tinymce textwidget\">\n";
echo "		<!-- Main Content -->\n";
echo "		<div id=\"page\">\n";
echo "			<h1><span>Thank You for Your Donation</span></h1>\n";
echo "			<p>Your card was charged the following:</p>\n";
echo "			<p><strong><span class=\"Label\">Donation</span> \$ $amt_charged </strong></p>\n";
echo "			<a class=\"btn gradient\" href=\"http://www.hjta.org\">Return to Home Page</a>\n";
echo "		</div>\n";
echo "		</div>\n";
//

include('../global/ui-components/footer.php');


return(TRUE) ;

} // eof: ApprovedForm()


function DeclinedForm($ccFailReason) {

$vPP13_SDS_ROOT = PP13_SDS_ROOT ;
$vPP13_SD_ROOT  = PP13_SD_ROOT ;

$vRENEW_LINK = RENEW_LINK ;
$vDONATE_LINK = DONATE_LINK ;
$vHOME_LINK = HOME_LINK ;

$PAGE_TITLE = "HJTA Donations | Howard Jarvis Taxpayers Association";
$HEADER_TITLE = "Protect Prop. 13 Secure Online Donation Form";

include('../global/ui-components/header.php');

// failed reason
//
echo "<!-- Google Tag Manager -->\n";
echo "<noscript>\n";
echo "<iframe src=\"//www.googletagmanager.com/ns.html?id=GTM-NQP3DX\" height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\">\n";
echo "</iframe>\n";
echo "</noscript>\n";
echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\"gtm.start\":new Date().getTime(),event:\"gtm.js\"});\n";
echo "var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!=\"dataLayer\"?\"&l=\"+l:\"\";\n";
echo "j.async=true;j.src=\"//www.googletagmanager.com/gtm.js?id=\"+i+dl;\n";
echo "f.parentNode.insertBefore(j,f);\n";
echo "})(window,document,\"script\",\"dataLayer\",\"GTM-NQP3DX\");\n";
echo "</script>\n";
echo "<!-- End Google Tag Manager -->\n";
//
echo "		<div class=\"siteorigin-widget-tinymce textwidget\">\n";
echo "			<h1><span>Your donation attempt failed.</span></h1>\n";
echo "                <p>Unfortunately, there was a problem with your donation attempt. Nothing has been charged.</p>\n";
echo "                <p><strong><span class=\"Label\">Reason: </span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
echo "                $ccFailReason \n";
echo "			<a class=\"btn gradient\" href=\"individual_donations_form.php?event=0026-0502\">TRY AGAIN</a>\n";
echo "		</div>\n";
//

include('../global/ui-components/footer.php');

//ob_flush();

return(TRUE) ;
	
} // eof DeclinedForm()

function PP13_Donation_Thanks($fname) {
$vPP13_SDS_ROOT = PP13_SDS_ROOT ;
$vPP13_SD_ROOT  = PP13_SD_ROOT ;

$ebody  = '' ;
$ebody .= "\n";
$ebody .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
$ebody .= "<html xmlns:v=\"urn:schemas-microsoft-com:vml\">\n";
$ebody .= "<head>\n";
$ebody .= "<meta name=\"HandheldFriendly\" content=\"false\" />\n";
$ebody .= "<meta name=\"viewport\" content=\"width=device-width; initial-scale=1; maximum-scale=1; minimum-scale=1; user-scalable=no;\" />\n";
$ebody .= "<title>Thank you for your donation!</title>\n";
$ebody .= "<style type=\"text/css\">\n";
$ebody .= ".ReadMsgBody {width:100%;}\n";
$ebody .= ".ExternalClass {width:100%;}\n";
$ebody .= "table td {border-collapse:collapse;}\n";
$ebody .= ".appleLinksGrey a {color:#5B5B5B; text-decoration: none;}\n";
$ebody .= "@media all and (max-width:540px) {\n";
$ebody .= "	table {width:100%;}\n";
$ebody .= "	table[class=\"wrapper\"] {width:100% !important;}\n";
$ebody .= "	table[class=\"hide\"], tr[class=\"hide\"], td[class=\"hide\"], img[class=\"hide\"] {display:none!important;}\n";
$ebody .= "	td[class=\"header\"] {padding:15px;font-size:12px !important;}\n";
$ebody .= "	img[class=\"fit\"] {width:100%; height:auto;}\n";
$ebody .= "	/*Spacing*/\n";
$ebody .= "	td[class=\"gutter\"] {width:10px !important;}\n";
$ebody .= "	td[class=\"padding15\"] {padding:15px;}\n";
$ebody .= "	/* CTA */\n";
$ebody .= "	table[class=\"CTA_1\"] {width:250px;}\n";
$ebody .= "}\n";
$ebody .= "</style>\n";
$ebody .= "</head>\n";
$ebody .= "<body bgcolor=\"#F6F6F6\" text=\"#000001\" link=\"#0000EE\" alink=\"#0000EE\" vlink=\"#551A8B\" style=\"margin-left:0px;margin-right:0px;margin-top:0px;padding:0px;\">\n";
$ebody .= "<div style=\"text-decoration:none;\">\n";
$ebody .= "	<div style=\"-webkit-text-size-adjust:none;\">\n";
$ebody .= "		<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bgcolor=\"#F6F6F6\" class=\"wrapper\">\n";
$ebody .= "			<tr valign=\"top\">\n";
$ebody .= "				<td width=\"100%\" align=\"center\" valign=\"top\">\n";
$ebody .= "\n";
$ebody .= "					<table width=\"620\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bgcolor=\"#F6F6F6\">\n";
$ebody .= "						<tr valign=\"top\">\n";
$ebody .= "							<td class=\"gutter\" width=\"10\"></td>\n";
$ebody .= "							<td align=\"center\" valign=\"top\">\n";
$ebody .= "								\n";
$ebody .= "								<!--HEADER-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td height=\"45\" width=\"10\"></td>\n";
$ebody .= "										<td align=\"center\" valign=\"middle\" style=\"font:normal 11px/15px Arial, Helvetica, sans-serif;color:#5B5B5B;\" class=\"header\">\n";
$ebody .= "											Thank you for your donation to HJTA-PAC\n";
$ebody .= "										</td>\n";
$ebody .= "										<td height=\"45\" width=\"10\"></td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "								\n";
$ebody .= "								<!--TOP-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#FFFFFF\" style=\"border-bottom:6px solid #a82512;\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td align=\"center\" style=\"padding:10px 10px 0;\">\n";
$ebody .= "											\n";
$ebody .= "											<!--Logo-->\n";
$ebody .= "											<table width=\"836\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:bold 14px/20px Arial, Helvetica, sans-serif;color:#FF0000;\" align=\"left\">\n";
$ebody .= "														<img src=\"https://donations.hjta.org/global/images/hjta-pac/HJTA-PAC-email-header-drk-navy.png\" width=\"836\" alt=\"Howard Jarvis Taxpayers Association | PAC\" border=\"0\" style=\"display:block;\" class=\"fit\" />\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "											<table width=\"1\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\" class=\"hide\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td width=\"1\"> </td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "											<!--CTA-->\n";
$ebody .= "											<table width=\"220\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"right\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td class=\"hide\"><img src=\"https://donations.hjta.org/global/images/shim.gif\" width=\"1\" height=\"25\" alt=\"\" border=\"0\" style=\"display:block;\" /></td>\n";
$ebody .= "												</tr>\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:normal 12px/20px Verdana, Geneva, sans-serif;color:#FFFFFF;padding:15px 0;\" align=\"center\">\n";
// removed 9/2019
//$ebody .= "														<a href=\"http://www.hjta.org/e-mail-updates/\" target=\"_blank\" style=\"outline:none;text-decoration:none;\"><span style=\"color:#0062A6;text-decoration:underline;\">Sign up for FREE e-mail updates</span></a>\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "								\n";
$ebody .= "								<!--Main Body: start-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#FFFFFF\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td style=\"padding:20px 20px 0;\">\n";
$ebody .= "										\n";
$ebody .= "											<!--IMAGE-->\n";
$ebody .= "											<table width=\"90\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:bold 12px/20px Arial, Helvetica, sans-serif;color:#FFFFFF;\" align=\"left\">\n";
$ebody .= "														<img src=\"https://donations.hjta.org/global/images/joncoupal.jpg\" width=\"79\" height=\"79\" alt=\"Jon Coupal\" border=\"0\" style=\"display:block;\" />\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "											<table width=\"20\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td width=\"20\"><img src=\"https://donations.hjta.org/global/images/shim.gif\" width=\"20\" height=\"20\" alt=\"\" border=\"0\" style=\"display:block;\" /></td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "											<!--TEXT-->\n";
$ebody .= "											<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:normal 14px/20px Verdana, Geneva, sans-serif;color:#5B5B5B;\" align=\"left\">\n";
$ebody .= "														Dear $fname,\n";
$ebody .= "														<br /><br />\n";
$ebody .= "														On behalf of everyone at the Howard Jarvis Taxpayers Association, I would like to express my heartfelt thanks for your financial support to the Howard Jarvis Taxpayers Association Political Action Committee, (HJTA-PAC).\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "										\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td style=\"padding:20px;\">\n";
$ebody .= "										\n";
$ebody .= "											<!--Copy-->\n";
$ebody .= "											<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:normal 14px/20px Verdana, Geneva, sans-serif;color:#5B5B5B;padding:0 0 25px;\" align=\"left\">\n";
$ebody .= "														We will use your donation wisely as we continue to support and publicize candidates who are taxpayer-friendly, fiscally responsible and dedicated to the principles of Proposition 13. The only way we can win election battles is through our HJTA-PAC so we sincerely appreciate your donation in helping to reshape today’s tax-and-spend legislature.\n";
$ebody .= "														<br /><br />\n";
$ebody .= "														I invite you to keep tabs on our progress, and follow important news affecting California taxpayers, at our <a href=\"https://hjta.org\" target=\"_blank\">official website</a>. You can also <a href=\"https://www.hjta.org/take-action/updates-by-email/\" target=\"_blank\">click here</a> to sign up for our free weekly email updates.\n";
$ebody .= "														<br /><br />\n";
$ebody .= "														Once again, thank you for your support. \n";
$ebody .= "														<br /><br />\n";
$ebody .= "														Sincerely,<br />\n";
$ebody .= "														Jon Coupal on behalf of HJTA-PAC\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
//$ebody .= "											<!--CTA-->\n";
//$ebody .= "											<table width=\"250\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"CTA_1\">\n";
//$ebody .= "												<tr valign=\"top\" align=\"center\">\n";
//$ebody .= "													<td align=\"center\" valign=\"middle\" bgcolor=\"#0062A6\" style=\"font:bold 16px/20px Arial, Helvetica, sans-serif;color:#FFFFFF;padding:6px;border-radius:6px;-webkit-border-radius:6px;-moz-border-radius:6px;text-shadow:0 1px 2px #000000;\">\n";
//$ebody .= "														<a href=\"http://www.hjta.org/e-mail-updates/\" target=\"_blank\" style=\"outline:none;text-decoration:none;\"><span style=\"color:#FFFFFF;text-decoration:none;\">FREE Weekly e-mail updates</span></a>\n";
//$ebody .= "													</td>\n";
//$ebody .= "												</tr>\n";
//$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "								<!--Main Body: end-->\n";
$ebody .= "								\n";
$ebody .= "								<!--Legal: start-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td style=\"font:normal 11px/15px Arial, Helvetica, sans-serif;color:#5B5B5B;padding:20px;\" align=\"left\">\n";
$ebody .= "											Copyright &copy; ".date("Y")." Howard Jarvis Taxpayers Association.\n";
$ebody .= "											All rights reserved. Howard Jarvis Taxpayers Association, <span class=\"appleLinksGrey\">621 South Westmoreland Avenue, Suite 200, Los Angeles, CA 90005</span> (ID #931447)\n";
$ebody .= "											<br /><br />\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "											\n";
$ebody .= "								\n";
$ebody .= "							</td>\n";
$ebody .= "							<td class=\"gutter\" width=\"10\"></td>\n";
$ebody .= "						</tr>\n";
$ebody .= "					</table>\n";
$ebody .= "					\n";
$ebody .= "				</td>\n";
$ebody .= "			</tr>\n";
$ebody .= "		</table>\n";
$ebody .= "	</div>\n";
$ebody .= "</div>\n";
$ebody .= "</body>\n";
$ebody .= "</html>\n";
$ebody .= "\n";	

return($ebody)	 ;
} // PP13_Donation_Thanks() 


function PP13_Donation_Sorry($fname, $ccFailReason) {

$vPP13_SDS_ROOT = PP13_SDS_ROOT ;
$vPP13_SD_ROOT  = PP13_SD_ROOT ;
$vHOME_LINK     = HOME_LINK ;

$ebody = '' ;	
$ebody .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
$ebody .= "<html xmlns:v=\"urn:schemas-microsoft-com:vml\">\n";
$ebody .= "<head>\n";
$ebody .= "<meta name=\"HandheldFriendly\" content=\"false\" />\n";
$ebody .= "<meta name=\"viewport\" content=\"width=device-width; initial-scale=1; maximum-scale=1; minimum-scale=1; user-scalable=no;\" />\n";
$ebody .= "<title>Sorry, there has been an error with your donation attempt.</title>\n";
$ebody .= "<style type=\"text/css\">\n";
$ebody .= ".ReadMsgBody {width:100%;}\n";
$ebody .= ".ExternalClass {width:100%;}\n";
$ebody .= "table td {border-collapse:collapse;}\n";
$ebody .= ".appleLinksGrey a {color:#5B5B5B; text-decoration: none;}\n";
$ebody .= "@media all and (max-width:540px) {\n";
$ebody .= "	table {width:100%;}\n";
$ebody .= "	table[class=\"wrapper\"] {width:100% !important;}\n";
$ebody .= "	table[class=\"hide\"], tr[class=\"hide\"], td[class=\"hide\"], img[class=\"hide\"] {display:none!important;}\n";
$ebody .= "	td[class=\"header\"] {padding:15px;font-size:12px !important;}\n";
$ebody .= "	img[class=\"fit\"] {width:100%; height:auto;}\n";
$ebody .= "	/*Spacing*/\n";
$ebody .= "	td[class=\"gutter\"] {width:10px !important;}\n";
$ebody .= "	td[class=\"padding15\"] {padding:15px;}\n";
$ebody .= "	/* CTA */\n";
$ebody .= "	table[class=\"CTA_1\"] {width:250px;}\n";
$ebody .= "}\n";
$ebody .= "</style>\n";
$ebody .= "</head>\n";
$ebody .= "<body bgcolor=\"#F6F6F6\" text=\"#000001\" link=\"#0000EE\" alink=\"#0000EE\" vlink=\"#551A8B\" style=\"margin-left:0px;margin-right:0px;margin-top:0px;padding:0px;\">\n";
$ebody .= "<div style=\"text-decoration:none;\">\n";
$ebody .= "	<div style=\"-webkit-text-size-adjust:none;\">\n";
$ebody .= "		<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bgcolor=\"#F6F6F6\" class=\"wrapper\">\n";
$ebody .= "			<tr valign=\"top\">\n";
$ebody .= "				<td width=\"100%\" align=\"center\" valign=\"top\">\n";
$ebody .= "\n";
$ebody .= "					<table width=\"620\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bgcolor=\"#F6F6F6\">\n";
$ebody .= "						<tr valign=\"top\">\n";
$ebody .= "							<td class=\"gutter\" width=\"10\"></td>\n";
$ebody .= "							<td align=\"center\" valign=\"top\">\n";
$ebody .= "								\n";
$ebody .= "								<!--HEADER-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td height=\"45\" width=\"10\"></td>\n";
$ebody .= "										<td align=\"center\" valign=\"middle\" style=\"font:normal 11px/15px Arial, Helvetica, sans-serif;color:#5B5B5B;\" class=\"header\">\n";
$ebody .= "											Your HJTA-PAC Transaction Attempt Failed\n";
$ebody .= "										</td>\n";
$ebody .= "										<td height=\"45\" width=\"10\"></td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "								\n";
$ebody .= "								<!--TOP-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#FFFFFF\" style=\"border-bottom:6px solid #a82512;\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td align=\"center\" style=\"padding:10px 10px 0;\">\n";
$ebody .= "											\n";
$ebody .= "											<!--Logo-->\n";
$ebody .= "											<table width=\"836\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:bold 14px/20px Arial, Helvetica, sans-serif;color:#FF0000;\" align=\"left\">\n";
$ebody .= "														<img src=\"https://donations.hjta.org/global/images/hjta-pac/HJTA-PAC-email-header-drk-navy.png\" width=\"836\" alt=\"Howard Jarvis Taxpayers Association | PAC\" border=\"0\" style=\"display:block;\" class=\"fit\" />\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "											<table width=\"1\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\" class=\"hide\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td width=\"1\"> </td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "											<!--CTA-->\n";
$ebody .= "											<table width=\"220\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"right\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td class=\"hide\"><img src=\"https://donations.hjta.org/global/images/shim.gif\" width=\"1\" height=\"25\" alt=\"\" border=\"0\" style=\"display:block;\" /></td>\n";
$ebody .= "												</tr>\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:normal 12px/20px Verdana, Geneva, sans-serif;color:#FFFFFF;padding:15px 0;\" align=\"center\">\n";
//$ebody .= "														<a href=\"http://www.hjta.org/e-mail-updates/\" target=\"_blank\" style=\"outline:none;text-decoration:none;\"><span style=\"color:#0062A6;text-decoration:underline;\">Sign up for FREE e-mail updates</span></a>\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "								\n";
$ebody .= "								<!--Main Body: start-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#FFFFFF\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td style=\"padding:20px 20px 0;\">\n";
$ebody .= "										\n";
$ebody .= "											<!--IMAGE-->\n";
$ebody .= "											<table width=\"90\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:bold 12px/20px Arial, Helvetica, sans-serif;color:#FFFFFF;\" align=\"left\">\n";
$ebody .= "														<img src=\"https://donations.hjta.org/global/images/joncoupal.jpg\" width=\"79\" height=\"79\" alt=\"Jon Coupal\" border=\"0\" style=\"display:block;\" />\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "											<table width=\"20\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td width=\"20\"><img src=\"https://donations.hjta.org/global/images/shim.gif\" width=\"20\" height=\"20\" alt=\"\" border=\"0\" style=\"display:block;\" /></td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "											<!--TEXT-->\n";
$ebody .= "											<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0;mso-table-rspace:0;\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:normal 14px/20px Verdana, Geneva, sans-serif;color:#5B5B5B;\" align=\"left\">\n";
$ebody .= "														Dear $fname,\n";
$ebody .= "														<br /><br />\n";
$ebody .= "														We're sorry, but we were unable to complete your donation to the Howard Jarvis Taxpayers Association Political Action Committee (HJTA-PAC) due to a problem with your credit card information.	\n";
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "										\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td style=\"padding:20px;\">\n";
$ebody .= "										\n";
$ebody .= "											<!--Copy-->\n";
$ebody .= "											<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
$ebody .= "												<tr valign=\"top\">\n";
$ebody .= "													<td style=\"font:normal 14px/20px Verdana, Geneva, sans-serif;color:#5B5B5B;padding:0 0 25px;\" align=\"left\">\n";
$ebody .= "														There are several reasons why this can happen, perhaps something as simple as mistyping a number. <br/><br/>Decline Reason : $ccFailReason \n";
$ebody .= "														<br /><br />\n";
$ebody .= "														Please take a moment now to enter your donation and credit card information again at <a href=\"individual_donations_form.php\">our website</a>. As you know, HJTA-PAC was founded by Howard Jarvis to back candidates for office who support Proposition 13. Howard knew that once Proposition 13 was in place it would be attacked by those who support unlimited taxation and spending, and he wanted to make sure taxpayers were represented by strong advocates in the Legislature.\n";
$ebody .= "														<br /><br />\n";
$ebody .= "													Thank you for helping to protect Californians from higher taxes!<br/>
Howard Jarvis Taxpayers Association Political Action Committee\n";
/*$ebody .= "														<br /><br />\n";
$ebody .= "														Sincerely,<br />\n";
$ebody .= "														Jon Coupal<br />\n";
$ebody .= "														President, Howard Jarvis Taxpayers Association\n";*/
$ebody .= "													</td>\n";
$ebody .= "												</tr>\n";
$ebody .= "											</table>\n";
$ebody .= "											\n";
//$ebody .= "											<!--CTA-->\n";
//$ebody .= "											<table width=\"250\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"CTA_1\">\n";
//$ebody .= "												<tr valign=\"top\" align=\"center\">\n";
//$ebody .= "													<td align=\"center\" valign=\"middle\" bgcolor=\"#0062A6\" style=\"font:bold 16px/20px Arial, Helvetica, sans-serif;color:#FFFFFF;padding:6px;border-radius:6px;-webkit-border-radius:6px;-moz-border-radius:6px;text-shadow:0 1px 2px #000000;\">\n";
//$ebody .= "														<a href=\"http://www.hjta.org/e-mail-updates/\" target=\"_blank\" style=\"outline:none;text-decoration:none;\"><span style=\"color:#FFFFFF;text-decoration:none;\">FREE Weekly e-mail updates</span></a>\n";
//$ebody .= "													</td>\n";
//$ebody .= "												</tr>\n";
//$ebody .= "											</table>\n";
$ebody .= "											\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "								<!--Main Body: end-->\n";
$ebody .= "								\n";
$ebody .= "								<!--Legal: start-->\n";
$ebody .= "								<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
$ebody .= "									<tr valign=\"top\">\n";
$ebody .= "										<td style=\"font:normal 11px/15px Arial, Helvetica, sans-serif;color:#5B5B5B;padding:20px;\" align=\"left\">\n";
$ebody .= "											Copyright &copy; ".date("Y")." Howard Jarvis Taxpayers Association.\n";
$ebody .= "											All rights reserved. Howard Jarvis Taxpayers Association, <span class=\"appleLinksGrey\">621 South Westmoreland Avenue, Suite 200, Los Angeles, CA 90005</span> (ID #931447)\n";
$ebody .= "											<br /><br />\n";
$ebody .= "										</td>\n";
$ebody .= "									</tr>\n";
$ebody .= "								</table>\n";
$ebody .= "											\n";
$ebody .= "								\n";
$ebody .= "							</td>\n";
$ebody .= "							<td class=\"gutter\" width=\"10\"></td>\n";
$ebody .= "						</tr>\n";
$ebody .= "					</table>\n";
$ebody .= "					\n";
$ebody .= "				</td>\n";
$ebody .= "			</tr>\n";
$ebody .= "		</table>\n";
$ebody .= "	</div>\n";
$ebody .= "</div>\n";
$ebody .= "</body>\n";
$ebody .= "</html>\n";
	
return( $ebody)	 ;
} // eof: PP13_Donation_Sorry()


?>
