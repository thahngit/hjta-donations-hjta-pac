<?php 

if (session_status() === PHP_SESSION_NONE){
    session_start();
    if (!isset($_COOKIE['session_name()'])) setcookie(session_name(), session_id(), 0, NULL, NULL, 0);
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

require_once($_root."/common/Z_HJTAPACglobals.php");
require_once($_root."/common/connect_REAL.php");

require_once($_root."/email_groups/email_groups.php");
require_once($_root."/common/email_funcs.php");

//  set to the user defined error handler
//dbine.ntw:  Replaced with monologger.php $old_error_handler=set_error_handler("myErrorHandler");
date_default_timezone_set("America/Los_Angeles");

$sid =trim($_POST['sid']) ;

// get incoming via POST
// $visitorArgs
$visitor_Args=array();

// Common to both forms
///$visitor_Args['visitor_details']="Y";   //// DEPRECATED ? - Does this even deo anything anymore ? 

///
/// Charge stuff common to both forms 
///
$visitor_Args['BOTH_sid']       =trim($_POST['sid']);
$visitor_Args['BOTH_cc_name']   = $_POST['CNAME'];
$visitor_Args['BOTH_cc_number'] = $_POST['CN'];
$visitor_Args['BOTH_cc_type']   = strtoupper($_POST['CT']);
$visitor_Args['BOTH_cc_last5']  = substr($_POST['CN'], -4); // changed to last 4
$visitor_Args['BOTH_cc_month']  = $_POST['CD1'];
$visitor_Args['BOTH_cc_year']   = $_POST['CD2'];
$visitor_Args['BOTH_cc_cvv']    = trim($_POST['CVV']);


// charge stuff
/// modification to accomodate Radio Buttons for Extra Donation  SAVE!!!
if (isset($_POST["donateRadioGroup"])):
    if ($_POST["donateRadioGroup"] == 'other'):
        $visitor_Args['BOTH_contrib'] = $_POST['CONAMT'];
    else:
        $visitor_Args['BOTH_contrib'] = $_POST["donateRadioGroup"];
    endif;
else:
    $visitor_Args['BOTH_contrib'] = 0;
endif;



// other stuff
setlocale(LC_MONETARY, 'en_US');
$to_charge = '$ ' . sprintf("%01.2f", $visitor_Args['BOTH_contrib'] ) ;


///////////////////////////
/////////////////////////// New for 2020 - Dual forms with new fields
///////////////////////////

/// from both forms
$visitor_Args['BOTH_fromFormType']        = $_POST["fromFormType"]; 
$visitor_Args['BOTH_IntermediaryType']    =trim($_POST['intermediaryTypeRadioGroup']);
$visitor_Args['BOTH_IntName']             =trim($_POST['INTNAME']);  
$visitor_Args['BOTH_IntAddr']             =trim($_POST['INTADDR']);
$visitor_Args['BOTH_IntCity']             =trim($_POST['INTCTY']);
$visitor_Args['BOTH_IntState']            =trim($_POST['INTST']);
$visitor_Args['BOTH_IntZip']              =trim($_POST['INTZIP']);
$visitor_Args['BOTH_IntOCC']              =trim($_POST['INTOCUP']);
$visitor_Args['BOTH_IntEMP']              =trim($_POST['INTEMPL']); 

// unique stuff fron each form 
if ( $visitor_Args['BOTH_fromFormType']  == "INDIVIDUAL"  ) :  
  /// Only from INDIVIDUAL Form
  $visitor_Args['IFRM_fname']   =ucfirst(strtolower(trim($_POST['FN']))) ;
  $visitor_Args['IFRM_lname']   =ucfirst(strtolower(trim($_POST['LN']))) ;
  $visitor_Args['IFRM_addr']    =trim($_POST['ADDR']);
  $visitor_Args['IFRM_addr2']   =trim($_POST['addr2']);
  $visitor_Args['IFRM_city']    =trim($_POST['CTY']);
  $visitor_Args['IFRM_state']   =trim($_POST['ST']);
  $visitor_Args['IFRM_zip']     =trim($_POST['ZIP']);
  $visitor_Args['IFRM_email']   =trim($_POST['EM']);  
  $visitor_Args['IFRM_ocup']    =trim($_POST['OCUP']);
  $visitor_Args['IFRM_empl']    =trim($_POST['EMPL']);

  /// Only from BUSINESS Form
  $visitor_Args['BFRM_name']    =NULL;
  $visitor_Args['BFRM_addr']    =NULL;
  $visitor_Args['BFRM_addr2']   =NULL;
  $visitor_Args['BFRM_city']    =NULL;
  $visitor_Args['BFRM_state']   =NULL;
  $visitor_Args['BFRM_zip']     =NULL;
  $visitor_Args['BFRM_email']   =NULL;
  //    
  $visitor_Args['BFRM_DonorType']                  =NULL; 
  $visitor_Args['BFRM_BusinessType']               =NULL; 
  $visitor_Args['BFRM_BizTypeOther']               =NULL;
  $visitor_Args['BFRM_SOSID']                      =NULL;
  $visitor_Args['BFRM_ResponsibleIndividualName']  =NULL;

else:
  /// only from INDIVIDUAL Form 
  $visitor_Args['IFRM_fname']   =NULL; 
  $visitor_Args['IFRM_lname']   =NULL; 
  $visitor_Args['IFRM_addr']    =NULL;
  $visitor_Args['IFRM_addr2']   =NULL;
  $visitor_Args['IFRM_city']    =NULL;
  $visitor_Args['IFRM_state']   =NULL;
  $visitor_Args['IFRM_zip']     =NULL;
  $visitor_Args['IFRM_email']   =NULL;
  $visitor_Args['IFRM_ocup']    =NULL;
  $visitor_Args['IFRM_empl']    =NULL;  
  /// Only from BUSINESS Form
  $visitor_Args['BFRM_name']    =trim($_POST['BNAME']);
  $visitor_Args['BFRM_addr']    =trim($_POST['ADDR']);
  $visitor_Args['BFRM_addr2']   =trim($_POST['addr2']);
  $visitor_Args['BFRM_city']    =trim($_POST['CTY']);
  $visitor_Args['BFRM_state']   =trim($_POST['ST']);
  $visitor_Args['BFRM_zip']     =trim($_POST['ZIP']);
  $visitor_Args['BFRM_email']   =trim($_POST['EM']);
  //      
  $visitor_Args['BFRM_DonorType']                  =trim($_POST['donorTypeRadioGroup']); 
  if (isset($_POST['businessTypeRadioGroup'])) :
    $charge_Args['BFRM_BusinessType']	=	$visitor_Args['BFRM_BusinessType']      = $_POST['businessTypeRadioGroup'] ;
  endif ;
  $visitor_Args['BFRM_BizTypeOther']               =trim($_POST['BUSTYPEOTHER']);
  $visitor_Args['BFRM_SOSID']                      =trim($_POST['SOSID']);
  $visitor_Args['BFRM_ResponsibleIndividualName']  =trim($_POST['RESPONSIBLEINDIVIDUAL']);

endif ; 



///////
/////// END NEW DUAL INDIV / BIZ FORM GATHER
///////



/// tracer
hjtaorgLog( 'Stage 01 ' , "Confirm form $sid" , __FILE__ . ' Line: ' . __LINE__ , "PP13");

////////////////// END 


$PAGE_TITLE = "HJTA Donations | Howard Jarvis Taxpayers Association";
$HEADER_TITLE = "HJTA-PAC SECURE ONLINE DONATION FORM";

ob_start();

include('../global/ui-components/header.php');
?>


<div class="siteorigin-widget-tinymce textwidget">

        <!-- WORK AREA -->
		<form name = "confirm_charge_form" action="complete.php" method="post" onsubmit="return process(this)">
		<fieldset id="totals" name="totals">
					<div id="totals-wrap">
						<h2 class="pageTitle confirm">Please Confirm</h2>
						<h3 style="padding-bottom:6px;">Your card will be charged the following:</h3>
						<strong id="totals-label"><span><?php echo( $to_charge ) ; ?></span> <span class="tLabel">Donation</span></strong>
						<p class="warning"><span class="important">IMPORTANT:</span><br/>Authorizing might take 30 seconds.<br/>Please press "Confirm" <u>ONLY</u> once.</p>
					</div>
				</fieldset>

        <fieldset id="buttons" name="buttons">


<!-- NEW Dual Form PP12 2020 method -->
<input type="hidden" id="BOTH_sid"                       name="BOTH_sid"  value="<?php echo $visitor_Args['BOTH_sid'] ?>">
<input type="hidden" id="BOTH_cc_name"                   name="BOTH_cc_name"  value="<?php echo $visitor_Args['BOTH_cc_name'] ?>">
<input type="hidden" id="BOTH_cc_number"                 name="BOTH_cc_number"  value="<?php echo $visitor_Args['BOTH_cc_number'] ?>">
<input type="hidden" id="BOTH_cc_type"                   name="BOTH_cc_type"  value="<?php echo $visitor_Args['BOTH_cc_type'] ?>">
<input type="hidden" id="BOTH_cc_last5"                  name="BOTH_cc_last5"  value="<?php echo $visitor_Args['BOTH_cc_last5'] ?>">
<input type="hidden" id="BOTH_cc_month"                  name="BOTH_cc_month"  value="<?php echo $visitor_Args['BOTH_cc_month'] ?>">
<input type="hidden" id="BOTH_cc_year"                   name="BOTH_cc_year"  value="<?php echo $visitor_Args['BOTH_cc_year'] ?>">
<input type="hidden" id="BOTH_cc_cvv"                    name="BOTH_cc_cvv"  value="<?php echo $visitor_Args['BOTH_cc_cvv'] ?>">
<input type="hidden" id="BOTH_contrib"                   name="BOTH_contrib"  value="<?php echo $visitor_Args['BOTH_contrib'] ?>">
<input type="hidden" id="BOTH_fromFormType"              name="BOTH_fromFormType"  value="<?php echo $visitor_Args['BOTH_fromFormType'] ?>">
<input type="hidden" id="BOTH_IntermediaryType"          name="BOTH_IntermediaryType"  value="<?php echo $visitor_Args['BOTH_IntermediaryType'] ?>">
<input type="hidden" id="BOTH_IntName"                   name="BOTH_IntName"  value="<?php echo $visitor_Args['BOTH_IntName'] ?>">
<input type="hidden" id="BOTH_IntAddr"                   name="BOTH_IntAddr"  value="<?php echo $visitor_Args['BOTH_IntAddr'] ?>">
<input type="hidden" id="BOTH_IntCity"                   name="BOTH_IntCity"  value="<?php echo $visitor_Args['BOTH_IntCity'] ?>">
<input type="hidden" id="BOTH_IntState"                  name="BOTH_IntState"  value="<?php echo $visitor_Args['BOTH_IntState'] ?>">
<input type="hidden" id="BOTH_IntZip"                    name="BOTH_IntZip"  value="<?php echo $visitor_Args['BOTH_IntZip'] ?>">
<input type="hidden" id="BOTH_IntOCC"                    name="BOTH_IntOCC"  value="<?php echo $visitor_Args['BOTH_IntOCC'] ?>">
<input type="hidden" id="BOTH_IntEMP"                    name="BOTH_IntEMP"  value="<?php echo $visitor_Args['BOTH_IntEMP'] ?>">
<input type="hidden" id="IFRM_fname"                     name="IFRM_fname"  value="<?php echo $visitor_Args['IFRM_fname'] ?>">
<input type="hidden" id="IFRM_lname"                     name="IFRM_lname"  value="<?php echo $visitor_Args['IFRM_lname'] ?>">
<input type="hidden" id="IFRM_addr"                      name="IFRM_addr"  value="<?php echo $visitor_Args['IFRM_addr'] ?>">
<input type="hidden" id="IFRM_addr2"                     name="IFRM_addr2"  value="<?php echo $visitor_Args['IFRM_addr2'] ?>">
<input type="hidden" id="IFRM_city"                      name="IFRM_city"  value="<?php echo $visitor_Args['IFRM_city'] ?>">
<input type="hidden" id="IFRM_state"                     name="IFRM_state"  value="<?php echo $visitor_Args['IFRM_state'] ?>">
<input type="hidden" id="IFRM_zip"                       name="IFRM_zip"  value="<?php echo $visitor_Args['IFRM_zip'] ?>">
<input type="hidden" id="IFRM_email"                     name="IFRM_email"  value="<?php echo $visitor_Args['IFRM_email'] ?>">
<input type="hidden" id="IFRM_ocup"                      name="IFRM_ocup"  value="<?php echo $visitor_Args['IFRM_ocup'] ?>">
<input type="hidden" id="IFRM_empl"                      name="IFRM_empl"  value="<?php echo $visitor_Args['IFRM_empl'] ?>">
<input type="hidden" id="BFRM_name"                      name="BFRM_name"  value="<?php echo $visitor_Args['BFRM_name'] ?>">
<input type="hidden" id="BFRM_addr"                      name="BFRM_addr"  value="<?php echo $visitor_Args['BFRM_addr'] ?>">
<input type="hidden" id="BFRM_addr2"                     name="BFRM_addr2"  value="<?php echo $visitor_Args['BFRM_addr2'] ?>">
<input type="hidden" id="BFRM_city"                      name="BFRM_city"  value="<?php echo $visitor_Args['BFRM_city'] ?>">
<input type="hidden" id="BFRM_state"                     name="BFRM_state"  value="<?php echo $visitor_Args['BFRM_state'] ?>">
<input type="hidden" id="BFRM_zip"                       name="BFRM_zip"  value="<?php echo $visitor_Args['BFRM_zip'] ?>">
<input type="hidden" id="BFRM_email"                     name="BFRM_email"  value="<?php echo $visitor_Args['BFRM_email'] ?>">
<input type="hidden" id="BFRM_DonorType"                 name="BFRM_DonorType"  value="<?php echo $visitor_Args['BFRM_DonorType'] ?>">
<input type="hidden" id="BFRM_BusinessType"              name="BFRM_BusinessType"  value="<?php echo $visitor_Args['BFRM_BusinessType'] ?>">
<input type="hidden" id="BFRM_BizTypeOther"              name="BFRM_BizTypeOther"  value="<?php echo $visitor_Args['BFRM_BizTypeOther'] ?>">
<input type="hidden" id="BFRM_SOSID"                     name="BFRM_SOSID"  value="<?php echo $visitor_Args['BFRM_SOSID'] ?>">
<input type="hidden" id="BFRM_ResponsibleIndividualName" name="BFRM_ResponsibleIndividualName"  value="<?php echo $visitor_Args['BFRM_ResponsibleIndividualName'] ?>">
<!-- END new PP13 Dual form -->


         <!-- buttons -->
		<input id="donation-submit" name="submit" value="Confirm" type="submit" />
		<!-- ver 3 removed <input class="gradient" name="cancel" value= "Cancel" type="reset" /> -->
		
		</fieldset>
        </form>
        <!-- END WORK AREA -->
 
			

		</div>
<!-- end form -->
<?php include('../global/ui-components/footer.php'); ?>

<?php ob_flush(); ?>
