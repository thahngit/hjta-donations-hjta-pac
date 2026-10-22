<?php 

$_root  =$_SERVER['DOCUMENT_ROOT'];
require_once($_root."/common/ntw-session.php");
//Remove the LIVE_TESTING indicator if exists in this session because this is a live page; not a test page:
if (isset($_SESSION['LIVE_TESTING'])) { unset($_SESSION['LIVE_TESTING']); }

require_once($_SERVER['DOCUMENT_ROOT']."/common/monologger.php");
//get_logger()->info('monologger verification', array('Called from' => __FILE__));

// used to resolve IE https IFRAME issue
header('P3P: CP="CAO PSA OUR"'); // test 4/12/2011

// INDIV
//https://donations.hjta.org/ZXdon2PP13/XX_0_PP13_individual_donations_form.php?event=0026-0502
// BIZ
//https://donations.hjta.org/ZXdon2PP13/XX_0_PP13_business_donations_form.php?event=0026-0502


$_root  =$_SERVER['DOCUMENT_ROOT'];

require_once($_root."/common/utils.php");
require_once($_root."/common/ezMysql_ntw.php");

//////////////////////////////////
//
//	Get passed parameters - establish event & customer code 
//
if (isset($_REQUEST['event'])) :
 list($cust, $event)=explode("-", $_REQUEST['event']);
else :
  // redirect to HJTA.org 
  header("Location: https://www.hjta.org/take-action/");
  exit(502);  
endif;  

// REQUIRED email files
require_once($_root."/email_groups/email_groups.php");
require_once($_root."/common/email_funcs.php");

require_once($_root."/common/Z_HJTAPACglobals.php");
require_once($_root."/common/connect_REAL.php");


///////////////////////////////
//
//  Manage error reporting
//   added php 5 support for  E_STRICT
ini_set("error_reporting", E_ALL | E_STRICT & ~E_NOTICE & ~E_DEPRECATED  );

// init stuff
//dbine.ntw:  Replaced with monologger.php $error_handler=set_error_handler("myErrorHandler");
date_default_timezone_set("America/Los_Angeles");

$mytime  = time();
$now     = date("Y-m-d H:i:s", $mytime);
$ip      = $_SERVER["REMOTE_ADDR"];

////////////////// session id

session_regenerate_id();
session_destroy();
if(isset($_SESSION['sid'])) :
  $_SESSION = array();
  unset($_SESSION);
endif ;
if (session_status() === PHP_SESSION_NONE){
	session_start();
}
$sid   = session_id();
$sid = $sid . '----' . $mytime ;
$_SESSION['sid'] = $sid ;


//////////////////////////////////////////////////////////////////////////////////////
////// NEW Event & cust by database lookup
////// JUN 2017
$ska         = 1 ;
//$event      = ;
$visittype   = 'donate' ;      // because this is DONATE page
//////////////////////////////////////////////////////////////////////////////////////


/// tracer
hjtaorgLog( 'BEGIN Stage 0' , "Init write & form $sid" , __FILE__ . ' Line: ' . __LINE__ , "PP13");

//	Do Database stuff
/// SET DATA CONNECTION
$ez    =new ezMysql_ntw();

// ARE THEY APPROVED EVENT & CUSTOMER CODES?
$search = "event='" . $event . "'" ;
$result=$ez->ezSelect_i("*", 'don2hjta_approved_codes' , $search );
$numb_rows = mysqli_num_rows($result);

// IF NOT APPROVED 
// REDIRECT 
if ( $numb_rows == 0): // search does not exist in database
   /// tracer
   hjtaorgLog( 'BEGIN Stage 00' , "Redirect. Failed event code of: ( $event ) tried" , __FILE__ . ' Line: ' . __LINE__ , "PP13");
  // redirect to HJTA.org 
  header("Location: https://www.hjta.org/take-action/");
  //exit(502);
  exit(0) ;
endif ;

/// SET session for future links
$_SESSION['cust']   = $cust   ;
$_SESSION['event']  = $event ;

//	Do Database stuff
//
$sid_condition = "sid='".$sid."'" ;
$result=$ez->ezSelect_i("*", VISIT_TBL , $sid_condition );
$numb_rows = mysqli_num_rows($result);
  
if ( $numb_rows == 0): // SID does not exist in database
    $mytime             =time();
    $now                =date("Y-m-d H:i:s", $mytime);
    $myArgs=array();
    $myArgs['sid']      =$sid;
    $myArgs['customer'] =$cust;
    $myArgs['event']    =$event;
    $myArgs['visittype']=$visittype;
    $myArgs['visitdate']=$now;
    $myArgs['ska']      =$ska ;
    $myArgs['myIP']     = $_SERVER['REMOTE_ADDR'];
    $this_query         =$ez->ezInsert_i( VISIT_TBL , $myArgs, "SET");
    if ($this_query) :
      // OK
    else :
       // no INSERT
       $errmsg =  "Failed INSERT into " . VISIT_TBL ;
       // Send all people individually until looping / array method
       $toList = $DR_ADM1 ;
       $lName  = $toList[0][name];
       $lEmail = $toList[0][email];
       $sendto =  $lEmail  ;
       // The SEND
       $rvOK = TRUE ;
       if ($rvOK) :
          //$rvOK =  vEmail ($sendto, 'DR' , "NOTE - Failed INSERT into " . VISIT_TBL , $errmsg ) ;  
          $rvOK =  zEmail ($sendto,  "NOTE - Failed INSERT into " . VISIT_TBL , $errmsg ) ;  
       endif ;
       // DEBUG TRACER
       hjtaorgDBUG( 'STAGE 0 SQL err ' , "Failed INSERT"  . ' ' . $errmsg , __FILE__ . ' Line: ' . __LINE__ , "PP13");
       // EXCEPTION PATH TODO :???
       // BETTER GO ELSEWHERE
       header("Location: https://www.hjta.org/take-action/");
       exit();       
    endif ;

else : //  SID already exists in the database
    $mess=" Duplicate SID $sid found";
    $src = __FILE__ . ' Line: ' . __LINE__ ;
    //Logit( $mess, '', $src ) ;
    // err_email( $mess , $src ) ; // todo convert to extended zEmail()
    //
    session_regenerate_id();
    session_destroy();
    if (session_status() === PHP_SESSION_NONE){
        session_start();
    }
    $sid   = session_id();
    $sid   = $mytime.'----'.$sid."----".$mytime ;
    $_SESSION['sid'] = $sid ;

    $myArgs=array();
    $myArgs['sid']       = $sid ;
    $myArgs['sid_stat']  = "duplicate";
    $myArgs['customer']  = $cust;
    $myArgs['event']     = $event;
    $myArgs['visittype'] = $visittype;
    $myArgs['visitdate'] = $now;
    $myArgs['ska']       = $ska;
    $myArgs['myIP']      = $ip;

    $this_query = $ez->ezInsert_i( VISIT_TBL , $myArgs, "SET");
    if ($this_query) :
      // OK
    else :
       // no INSERT
       $errmsg =  "Failed INSERT into " . VISIT_TBL  ;
       // Send all people individually until looping / array method
       $toList = $DR_ADM1 ;
       $lName  = $toList[0][name];
       $lEmail = $toList[0][email];
       $sendto =  $lEmail  ;
       // The SEND
       $rvOK = TRUE ;
       if ($rvOK) :
          //$rvOK =  vEmail ($sendto, 'DR' , "NOTE - Failed INSERT into " . VISIT_TBL , $errmsg ) ;
          $rvOK =  zEmail ($sendto,  "NOTE - Failed INSERT into " . VISIT_TBL , $errmsg ) ;          
       endif ;
       // DEBUG TRACER
       hjtaorgDBUG( 'STAGE 0 SQL err ' , "Failed INSERT"  . ' ' . $errmsg , __FILE__ . ' Line: ' . __LINE__ , "PP13");
       // EXCEPTION PATH TODO :???

       // do NOT proceed
       // redirect to HJTA.org 
       header("Location: https://www.hjta.org/take-action/");
       exit(502) ;
    endif ;
endif ;

$PAGE_TITLE = "HJTA Donations | Howard Jarvis Taxpayers Association";
$HEADER_TITLE = "HJTA POLITICAL ACTION COMMITTEE (HJTA-PAC) ONLINE DONATION FORM";

ob_start();

include('../global/ui-components/header.php');

$donation_script_type = "pp13_individual";
?>


<style>
	#contactInfo h2 {
		font-size: 2rem;
	}
	#tagline p {
		width: 81%;
		border: solid 1px #000000;
		margin-top: 20px;
		margin-left: auto;
		margin-right: auto;
		padding: 5px 50px 7px 20px;
		font-size: 16px;
	}
	.formitem label {
		width: 30%;
		text-align: right;
		display: block;
		float: left;
		padding-right: 0.5em;
	}
@media screen and (max-width: 600px) {
  .formitem label {
		width: 40%;
	}
	#donateAmount .option {
	    margin-right: 25px;
	}
}
</style>
<script lang="JavaScript">
	function ntwSet() {
		if (jQuery("#buttons").length && jQuery("#donation-submit").length) {
			jQuery("#buttons #donation-submit").prop('disabled',true);
			jQuery("#buttons #donation-submit").prop('value','Processing');
			jQuery("#buttons #donation-submit").css('cursor','default');
			jQuery("#buttons #donation-submit").css('opacity','0.5');
		}
		return true;
	}
	function ntwReset() {
		if (jQuery("#buttons").length && jQuery("#donation-submit").length) {
			jQuery("#buttons #donation-submit").prop('disabled',false);
			jQuery("#buttons #donation-submit").prop('value','SUBMIT');
			jQuery("#buttons #donation-submit").css('cursor','default');
			jQuery("#buttons #donation-submit").css('opacity','1.0');
		}
		return false;
	}
</script>
<div class="siteorigin-widget-tinymce textwidget">		
			<!--<h3>Donations are currently unavailable</h3>-->
			<p>Election laws won’t let HJTA itself spend one dollar of our dues or donations on candidate elections! That is why Howard Jarvis originally founded the HJTA Political Action Committee, our HJTA-PAC. The only way we can win election battles is through our HJTA-PAC so we sincerely appreciate your donation in helping to reshape today’s tax-and-spend legislature.</p>
<p>Donations to this committee help HJTA-PAC publicize the names of candidates who deserve the support of taxpayers. These candidates are screened through a questionnaire and interview with the goal of finding candidates who are taxpayer-friendly, fiscally responsible and dedicated to the principles of Proposition 13.</p>
			
			<form action="confirm.php" method="post" class="joinform" id="joinform" onsubmit="return (ntwSet() && FrontPage_Form2_Validator(this) ? true : ntwReset())" name="FrontPage_Form2">
			    <input type="hidden" name="sid" id="sid" value="<?php echo($sid); ?>" />
				<input type="hidden" id="fromFormType" name="fromFormType" value="INDIVIDUAL" />			    
				<div class="formgroup">
					<fieldset id="donateAmount" name="donateAmount">
						<h3>Choose Donation Amount:</h3>
						<div>
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup10" value="10" />$10</label>
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup15" value="15" checked="checked" />$15</label>
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup25" value="25" />$25</label>
						</div>
						<div>
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup50" value="50" />$50</label>
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup100" value="100" />$100</label>
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup250" value="250" />$250</label>
						</div>
						<div class="last">
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup500" value="500" />$500</label>
							<label class="option"><input type="radio" name="donateRadioGroup" id="donateRadioGroup1000" value="1000" />$1,000</label>
							<span class="other">
								<label class="option other"><input type="radio" name="donateRadioGroup" id="otherRadio" value="other"  />Other Amount: $&nbsp;</label>
								<input type="text" id="CONAMT" name="CONAMT" size="12" class="formfield" value="" data-min="5" />
								<span class="disclaim">*Minimum $5 donation</span>
							</span>
						</div>
					</fieldset>
				</div>
				<div class="formgroup">
					<fieldset id="contactInfo" name="contactInfo">
						<h2><strong>If contributing as an INDIVIDUAL, please complete the following fields.<br/>If contributing as a BUSINESS or other entity, please <a href="<?php echo ($_SESSION['LIVE_TESTING'] == "Y" ? "business_donations_form.php" : "business_donations_form.php"); ?>?event=<?php echo $_REQUEST['event']; ?><?php echo ($_SESSION['LIVE_TESTING'] == "Y" ? "&auth=cd6e56d7-7721-4dd0-80ba-0b79040ededb&LT=Y" : ""); ?>">click here</a>.</strong></h2><br/>
						<h3>Donor Information (INDIVIDUAL): <span class="disclaim">*required fields</span></h3>
						<div class="formitem">
							<label for="FN">First Name:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="FN" name="FN" size="35" maxlength="64" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="LN">Last Name:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="LN" name="LN" size="35" maxlength="64" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="ADDR">Address 1:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="ADDR" name="ADDR" size="35" class="formfield" data-nopo="true" value="" />
					
							<span class="description">Unfortunately, we cannot accept a P.O. Box address. Please provide<br />a street address. You may also enter an alternative mailing address<br />in Address 2.</span>
						</div>
						<div class="formitem">
							<label for="addr2">Address 2:</label>
							<input type="text" id="addr2" name="addr2" size="35" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="CTY">City:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="CTY" name="CTY" size="35" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="ST">State:<span class="form-required" title="This field is required.">*</span></label>
							<select id="ST" name="ST" size="1" class="formfield" value="">
								<option value="AL">Alabama</option>
								<option value="AK">Alaska</option>
								<option value="AS">American Samoa</option>
								<option value="AZ">Arizona</option>
								<option value="AR">Arkansas</option>
								<option value="CA" selected="selected">California</option>
								<option value="CO">Colorado</option>
								<option value="CT">Connecticut</option>
								<option value="DE">Delaware</option>
								<option value="DC">District of Comumbia</option>
								<option value="FL">Florida</option>
								<option value="GA">Georgia</option>
								<option value="GU">Guam</option>
								<option value="HI">Hawaii</option>
								<option value="ID">Idaho</option>
								<option value="IL">Illinois</option>
								<option value="IN">Indiana</option>
								<option value="IA">Iowa</option>
								<option value="KS">Kansas</option>
								<option value="KY">Kentucky</option>
								<option value="LA">Louisiana</option>
								<option value="ME">Maine</option>
								<option value="MD">Maryland</option>
								<option value="MA">Massachusetts</option>
								<option value="MI">Michigan</option>
								<option value="MN">Minnesota</option>
								<option value="MO">Missouri</option>
								<option value="MS">Mississippi</option>
								<option value="MT">Montana</option>
								<option value="NE">Nebraska</option>
								<option value="NV">Nevada</option>
								<option value="NH">New Hampshire</option>
								<option value="NJ">New Jersey</option>
								<option value="NM">New Mexico</option>
								<option value="NY">New York</option>
								<option value="NC">North Carolina</option>
								<option value="ND">North Dakota</option>
								<option value="OH">Ohio</option>
								<option value="OK">Oklahoma</option>
								<option value="OR">Oregon</option>
								<option value="PA">Pennsylvania</option>
								<option value="PR">Puerto Rico</option>
								<option value="RI">Rhode Island</option>
								<option value="SC">South Carolina</option>
								<option value="SD">South Dakota</option>
								<option value="TN">Tennessee</option>
								<option value="TX">Texas</option>
								<option value="UT">Utah</option>
								<option value="VT">Vermont</option>
								<option value="VA">Virginia</option>
								<option value="VI">Virgin islands</option>
								<option value="WA">Washington</option>
								<option value="WV">West Virginia</option>
								<option value="WI">Wisconsin</option>
								<option value="WY">Wyoming</option>
							</select>
						</div>
						<div class="formitem">
							<label for="ZIP">ZIP Code:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="ZIP" name="ZIP" size="12" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="EM">Email Address:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="EM" name="EM" size="35" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="DP">Phone Number:</label>
							<input type="text" id="DP" name="DP" size="35" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="OUPC">Occupation:*</label>
							<input type="text" id="OCUP" name="OCUP" size="35" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="EMPL">Employer:*</label>
							<input type="text" id="EMPL" name="EMPL" size="35" class="formfield" value="" />
						
							<span class="description">If self-employed, please put name of business. If retired, please put "none".<br/>Occupation and employer information is required by state law for donations to campaign committees. Thank you.</span>
						</div>						
					</fieldset>	
					<fieldset id="billingInfo" name="billingInfo">
						<h3>My Billing Information: <span class="disclaim">*required fields</span></h3>
						<div class="formitem">
							<label for="CT">Payment Type:<span class="form-required" title="This field is required.">*</span></label>
							<select size="1" id="CT" name="CT" class="formfield">
								<option selected="selected">Select one:</option>
								<option>Mastercard</option>
								<option>Visa</option>
								<option>Discover</option>
								<option>American Express</option>
							</select>
						</div>
						<div class="formitem">
							<label for="CNAME">Name on Card:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="CNAME" name="CNAME" size="35" value="" maxlength="64" class="formfield" />
						</div>
						<h3>Billing Address (if different from "Donor Information" Address):  <span class="disclaim">*required fields</span></h3>
						<div class="formitem">
							<label for="BLaddr">Street Address or P.O. Box:</label>
							<input type="text" id="BLaddr" name="BLaddr" size="35" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="BLCTY">City:</label>
							<input type="text" id="BLCTY" name="BLCTY" size="35" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="BLST">State:</label>
							<select id="BLST" name="BLST" size="1" class="formfield" value="">
								<option value="AL">Alabama</option>
								<option value="AK">Alaska</option>
								<option value="AS">American Samoa</option>
								<option value="AZ">Arizona</option>
								<option value="AR">Arkansas</option>
								<option value="CA" selected="selected">California</option>
								<option value="CO">Colorado</option>
								<option value="CT">Connecticut</option>
								<option value="DE">Delaware</option>
								<option value="DC">District of Comumbia</option>
								<option value="FL">Florida</option>
								<option value="GA">Georgia</option>
								<option value="GU">Guam</option>
								<option value="HI">Hawaii</option>
								<option value="ID">Idaho</option>
								<option value="IL">Illinois</option>
								<option value="IN">Indiana</option>
								<option value="IA">Iowa</option>
								<option value="KS">Kansas</option>
								<option value="KY">Kentucky</option>
								<option value="LA">Louisiana</option>
								<option value="ME">Maine</option>
								<option value="MD">Maryland</option>
								<option value="MA">Massachusetts</option>
								<option value="MI">Michigan</option>
								<option value="MN">Minnesota</option>
								<option value="MO">Missouri</option>
								<option value="MS">Mississippi</option>
								<option value="MT">Montana</option>
								<option value="NE">Nebraska</option>
								<option value="NV">Nevada</option>
								<option value="NH">New Hampshire</option>
								<option value="NJ">New Jersey</option>
								<option value="NM">New Mexico</option>
								<option value="NY">New York</option>
								<option value="NC">North Carolina</option>
								<option value="ND">North Dakota</option>
								<option value="OH">Ohio</option>
								<option value="OK">Oklahoma</option>
								<option value="OR">Oregon</option>
								<option value="PA">Pennsylvania</option>
								<option value="PR">Puerto Rico</option>
								<option value="RI">Rhode Island</option>
								<option value="SC">South Carolina</option>
								<option value="SD">South Dakota</option>
								<option value="TN">Tennessee</option>
								<option value="TX">Texas</option>
								<option value="UT">Utah</option>
								<option value="VT">Vermont</option>
								<option value="VA">Virginia</option>
								<option value="VI">Virgin islands</option>
								<option value="WA">Washington</option>
								<option value="WV">West Virginia</option>
								<option value="WI">Wisconsin</option>
								<option value="WY">Wyoming</option>
							</select>
						</div>
						<div class="formitem">
							<label for="BLZIP">ZIP Code:</label>
							<input type="text" id="BLZIP" name="BLZIP" size="12" class="formfield" value="" />
						</div>
						<div class="formitem">
							<label for="CN">Card Number:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="CN" name="CN" size="35" maxlength="32" class="formfield" />
							<span class="description">Do not use dashes or spaces between numbers.</span>
						</div>
						<div class="formitem">
							<label for="CD1">Exp. Date:<span class="form-required" title="This field is required.">*</span></label>
							<select size="1" id="CD1" name="CD1" class="formfield">
								<option selected="selected">Month</option>
								<option>01</option>
								<option>02</option>
								<option>03</option>
								<option>04</option>
								<option>05</option>
								<option>06</option>
								<option>07</option>
								<option>08</option>
								<option>09</option>
								<option>10</option>
								<option>11</option>
								<option>12</option>
							</select> &nbsp;
							<select size="1" id="CD2" name="CD2" class="formfield">
								<option selected="selected">Year</option>																
								<?php include('../uilib/ccExpirationYear.php'); ?>
							</select>
						</div>
						<div class="formitem">
							<label for="CVV">CVV Code:<span class="form-required" title="This field is required.">*</span></label>
							<input type="text" id="CVV" name="CVV" size="3" value="" maxlength="4" class="formfield" />
							<img src="https://donations.hjta.org/global/images/cvv_generic.jpg" width="120" height="65" alt="cvv" />
							<img class="amex" src="https://donations.hjta.org/global/images/cvv_amex.jpg" width="120" height="65" alt="cvv" />
						</div>
						
					</fieldset>
				</div>
				
				<div class="formgroup">
					<fieldset id="confirm" name="confirm">
						<h3>I confirm that the following statements are true and accurate: <span class="disclaim">*required fields</span></h3>
						<div class="formitem">
							<label class="option">1.<span class="form-required" title="This field is required.">*</span> <input type="checkbox" id="RES" name="RES" value="ON" class="form-checkbox" /> I am a United States citizen or a permanent resident alien.</label>
						</div>
						<div class="formitem">
							<label class="option">2.<span class="form-required" title="This field is required.">*</span> <input type="checkbox" id="FUNDS" name="FUNDS" value="ON" class="form-checkbox" /> The funds I am contributing are not being provided to me by another person or entity for the purpose of making this contribution.</label>
						</div>
					</fieldset>
				</div>											
				<fieldset id="totals" name="totals">
					<div id="totals-wrap">
						<p>Your contribution is not tax-deductible as a charitable contribution for Federal income tax purposes.</p>
						<h3 style="padding-bottom:6px;">Your card will be charged the following:</h3>
						<strong id="totals-label">$<span id="donationValue">0</span> <span class="tLabel">Donation</span></strong>
						<p class="warning"><span class="important">IMPORTANT:</span><br/>Authorizing might take 30 seconds.<br/>Please press "Submit" <u>ONLY</u> once.</p>
					</div>
				</fieldset>
				<fieldset id="buttons" name="buttons">
					<input id="donation-submit" name="submit" value="Submit" type="submit" />
					<input id="donation-cancel" name="cancel" value= "Cancel" type="reset" />	
				</fieldset>
			</form>
			<div id="tagline"><p style="text-align: center;">Paid for by the Howard Jarvis Taxpayers Association State PAC</strong></p></div>
		</div>
<!-- end form -->
<?php include('../global/ui-components/footer.php'); ?>

<?php ob_flush(); ?>


<!-- 2020 REVISED SCHED A Project PP13 -->