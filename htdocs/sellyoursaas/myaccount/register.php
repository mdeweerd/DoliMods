<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');			// Do not check anti CSRF attack test
//if (! defined('NOSTYLECHECK'))   define('NOSTYLECHECK','1');			// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');		// Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');			// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');			// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
if (! defined("NOLOGIN"))        define("NOLOGIN",'1');				    // If this page is public (can be called outside logged session)


include ('./mainmyaccount.inc.php');

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

// Re set variables specific to new environment
$conf->global->SYSLOG_FILE_ONEPERSESSION=1;
$langs=new Translate('', $conf);
$langs->setDefaultLang('auto');
$langs->loadLangs(array("main","companies","sellyoursaas@sellyoursaas","errors"));


$partner=GETPOST('partner','alpha');
$plan=GETPOST('plan','alpha');

$productref='DOLICLOUD-PACK-Dolibarr';
if ($plan)	// Plan is a product/service
{
	$productref=$plan;
}
$tmpproduct = new Product($db);
$result = $tmpproduct->fetch(0, $productref);
if (empty($tmpproduct->id))
{
	print 'Service/Plan (Product ref) '.$productref.' was not found.';
	exit;
}
if (! preg_match('/^DOLICLOUD-PACK-(.+)$/', $tmpproduct->ref, $reg))
{
	print 'Service/Plan name (Product ref) is invalid. Name must be DOLICLOUD-PACK-...';
	exit;
}
$packageref = $reg[1];

dol_include_once('/sellyoursaas/class/packages.class.php');
$tmppackage = new Packages($db);
$tmppackage->fetch(0, $packageref);
if (empty($tmppackage->id))
{
	print 'Package name '.$packageref.' was not found.';
	exit;
}


/*
 * Action
 */

// Nothing



/*
 * View
 */

$form = new Form($db);

$conf->dol_hide_topmenu = 1;
$conf->dol_hide_leftmenu = 1;


$head='<link rel="icon" href="img/favicon.ico">
<!-- Bootstrap core CSS -->
<!--<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.css" rel="stylesheet">-->
<link href="dist/css/bootstrap.css" rel="stylesheet">
<link href="dist/css/myaccount.css" rel="stylesheet">';

llxHeader($head, $langs->trans("ERPCRMOnlineSubscription"), '', '', 0, 0, array(), array('../dist/css/myaccount.css'));

?>

<div id="waitMask" style="display:none;">
    <font size="3em" style="color:#888; font-weight: bold;"><?php echo $langs->trans("InstallingInstance") ?><br><?php echo $langs->trans("PleaseWait") ?><br></font>
    <img id="waitMaskImg" width="100px" src="<?php echo 'ajax-loader.gif'; ?>" alt="Loading" />
</div>

<div class="signup">

      <div style="text-align: center;">
        <?php
        $linklogo = DOL_URL_ROOT.'/viewimage.php?modulepart=mycompany&file='.urlencode('/thumbs/'.$conf->global->SELLYOURSAAS_LOGO_SMALL);

        if (GETPOST('partner','alpha'))
        {
            $tmpthirdparty = new Societe($db);
            $result = $tmpthirdparty->fetch(0, GETPOST('partner','alpha'));
            $logo = $tmpthirdparty->logo;
        }
        print '<img style="center" class="logoheader"  src="'.$linklogo.'" id="logo" />';
        ?>
      </div>
      <div class="block medium">

        <header class="inverse">
          <h1><?php echo $langs->trans("Registration") ?> <small><?php echo ($tmpproduct->label?'('.$tmpproduct->label.')':''); ?></small></h1>
        </header>


      <form action="register_instance" method="post" id="formregister">
        <div class="form-content">
    	  <input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />
          <input type="hidden" name="service" value="<?php echo dol_escape_htmltag($tmpproduct->ref); ?>" />
          <input type="hidden" name="package" value="<?php echo dol_escape_htmltag($tmppackage->ref); ?>" />
          <input type="hidden" name="partner" value="<?php echo dol_escape_htmltag($partner); ?>" />

          <section id="enterUserAccountDetails">


			<?php
			if (isset($_SESSION['dol_events']['errors'])) {
				print '<div class="alert alert-error">';
				if (is_array($_SESSION['dol_events']['errors']))
				{
					foreach($_SESSION['dol_events']['errors'] as $key => $val)
					{
						print '<ul><li>'.$val.'</li></ul>';
					}
				}
				else
				{
					print '<ul><li>'.$_SESSION['dol_events']['errors'].'</li></ul>';
				}
				print '</div>'."\n";
			}
            ?>

            <div class="control-group  required">
            	<label class="control-label" for="username" trans="1"><?php echo $langs->trans("Email") ?></label>
            	<div class="controls">
            		<input type="text" name="username" value="<?php echo GETPOST('username','alpha'); ?>" required="" id="username" />

            	</div>
            </div>

            <div class="control-group  required">
            	<label class="control-label" for="orgName" trans="1"><?php echo $langs->trans("NameOfCompany") ?></label>
            	<div class="controls">
            		<input type="text" name="orgName" value="<?php echo GETPOST('orgName','alpha'); ?>" required="" maxlength="250" id="orgName" />
            	</div>
            </div>

            <div class="group">
                <div class="horizontal-fld">

                <div class="control-group  required">
                	<label class="control-label" for="password" trans="1"><?php echo $langs->trans("Password") ?></label>
                	<div class="controls">

                        <input name="password" type="password" required />

                	</div>
                </div>

                </div>
                <div class="horizontal-fld">
                  <div class="control-group required">
                    <label class="control-label" for="password2" trans="1"><?php echo $langs->trans("ConfirmPassword") ?></label>
                    <div class="controls">
                      <input name="password2" type="password" required />
                    </div>
                  </div>
                </div>
            </div>

            <hr />




			<div class="control-group  ">
				<label class="control-label" for="address_country"><?php echo $langs->trans("Country") ?></label>
				<div class="controls">
			<?php
			$countryselected=dolGetCountryCodeFromIp($_SERVER["REMOTE_ADDR"]);
			print '<!-- Autodetected IP/Country: '.$_SERVER["REMOTE_ADDR"].'/'.$countryselected.' -->'."\n";
			if (empty($countryselected)) $countryselected='US';
			if (GETPOST('address_country','alpha')) $countryselected=GETPOST('address_country','alpha');
			print $form->select_country($countryselected, 'address_country', 'optionsValue="name"', 0, 'minwidth300', 'code2');
			?>
				</div>
			</div>


          </section>

          <hr/>

          <section id="selectDomain">
            <div class="fld select-domain required">
              <label trans="1"><?php echo $langs->trans("ChooseANameForYourApplication") ?></label>
              <div class="linked-flds">
                <input class="sldAndSubdomain" type="text" name="sldAndSubdomain" value="<?php echo GETPOST('sldAndSubdomain','alpha') ?>" maxlength="29" />
                <select name="tldid" id="tldid" >
                    <option value=".with.<?php echo $conf->global->SELLYOURSAAS_MAIN_DOMAIN_NAME; ?>" >.with.<?php echo $conf->global->SELLYOURSAAS_MAIN_DOMAIN_NAME; ?></option>
                </select>
                <br class="unfloat" />
              </div>
            </div>
          </section>


			<br>

          <section id="formActions">
          <p style="color:#444;margin:10px 0;" trans="1"><?php echo $langs->trans("WhenRegisteringYouAccept", 'https://www.'.$conf->global->SELLYOURSAAS_MAIN_DOMAIN_NAME.'/en/terms-and-conditions') ?></p>
          <div class="form-actions center">
              <input type="submit" name="submit" value="<?php echo $langs->trans("SignMeUp") ?>" class="btn btn-primary" id="submit" />
          </div>
         </section>
       </div> <!-- end form-content -->
     </form>


  </div>
</div>




<script type="text/javascript" language="javascript">
    function applyDomainConstraints( domain )
    {
        domain = domain.replace(/ /g,"");
        domain = domain.replace(/\W/g,"");
        domain = domain.replace(/\_/g,"");
        domain = domain.toLowerCase();
        if (!isNaN(domain)) {
          return ""
        }
        while ( domain.length >1 && !isNaN( domain.charAt(0))  ){
          domain=domain.substr(1)
        }
        return domain
    }

    jQuery(document).ready(function() {

        /* Autofill the domain */
        jQuery("[name=orgName]").change(function(){
        	dn = applyDomainConstraints( $(this).val() )
    	    	$("[name=sldAndSubdomain]").val( applyDomainConstraints( $(this).val() ) );
        });


        /* Sow hourglass */
        $('#formregister').submit(function() {
                console.log("We clicked on submit")
                jQuery(document.body).css({ 'cursor': 'wait' });
                jQuery("div#waitMask").show();
                jQuery("#waitMask").css("opacity"); // must read it first
                jQuery("#waitMask").css("opacity", "0.5");
                return true;
        });
	});
</script>


<?php

llxFooter('', 'public', 1);		// We disabled output of messages. Already done into page
$db->close();

