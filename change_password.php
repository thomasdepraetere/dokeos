<?php
/* For licensing terms, see /license.txt */

// only this script should have this constant defined. This is used to activate the javascript that
// gives the login name automatic focus in header.inc.html.
/** @todo Couldn't this be done using the $HtmlHeadXtra array? */
define('DOKEOS_HOMEPAGE', true);

// the language file
$language_file = array ('courses', 'index', 'admin', 'registration', 'messages', 'userInfo');

/* Flag forcing the 'current course' reset, as we're not inside a course anymore */
// maybe we should change this into an api function? an example: Coursemanager::unset();
$cidReset = true;

/*
-----------------------------------------------------------
	Included libraries
-----------------------------------------------------------
*/

/** @todo make all the library files consistent, use filename.lib.php and not filename.lib.inc.php */
require_once 'main/inc/global.inc.php';
include_once api_get_path(LIBRARY_PATH).'course.lib.php';
include_once api_get_path(LIBRARY_PATH).'debug.lib.inc.php';
include_once api_get_path(LIBRARY_PATH).'events.lib.inc.php';
include_once api_get_path(LIBRARY_PATH).'system_announcements.lib.php';
include_once api_get_path(LIBRARY_PATH).'groupmanager.lib.php';
include_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once (api_get_path(LIBRARY_PATH).'language.lib.php');
require_once api_get_path(LIBRARY_PATH).'sublanguagemanager.lib.php';

// for shopping cart & catalog
require_once api_get_path(SYS_PATH). 'main/core/model/ecommerce/EcommerceCatalog.php';
require_once api_get_path(SYS_PATH) . 'main/core/controller/shopping_cart/shopping_cart_controller.php';

$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.5.1.min.js" language="javascript"></script>';
//Code changed like this for testing.
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/slides.min.jquery.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/general-functions.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
$htmlHeadXtra[] = '<link type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/chosen/chosen.css"/>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/chosen/chosen.jquery.min.js" language="javascript"></script>';
$htmlHeadXtra[] = '
<script type="text/javascript">
$(document).ready(function() {
		function password_switch_radio_button() {
			var input_elements = document.getElementsByTagName("input");
			for (var i = 0; i < input_elements.length; i++) {
				if (input_elements.item(i).name == "pass1[password_auto]" && input_elements.item(i).value == "0") {
					input_elements.item(i).checked = true;
				}
			}
		}
});
</script>';

if (api_get_setting('password_length') <> 0) {
    $password_length_rule = 'minLength: ' . api_get_setting('password_length') . ',';
}
if (api_get_setting('show_force_password_change') == 'true') {
    $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_CODE_PATH) . 'inc/lib/javascript/jquery.strengthy-0.0.1.js" language="javascript"></script>';
    $htmlHeadXtra[] = '<script type="text/javascript">
							var messages = [
									  "' . get_lang('PasswordTooShort') . '",
									  "' . get_lang('PasswordMustContainNumber') . '",
									  "' . get_lang('PasswordMustContainLowerUpper') . '",
									  "' . get_lang('PasswordMustContainSymbol') . '",
									  "' . get_lang('PasswordIsValid') . '",
									  "' . get_lang('PasswordShowPassword') . '"
									]
						
							$(document).ready(function() {
								$(".pass1").strengthy({
									' . $password_length_rule . '
									require: {
										numbers: ' . api_get_setting('password_rule', 'numbers') . ',
										upperAndLower: ' . api_get_setting('password_rule', 'camelcase') . ',
										symbols: ' . api_get_setting('password_rule', 'symbols') . '
									},
									errorClass: "form_error",
									validClass: "good-message",
									showToggle: true,
									msgs: messages
								});
							});
					</script>';
}

$loginFailed = isset($_GET['loginFailed']) ? true : isset($loginFailed);
$setting_show_also_closed_courses = api_get_setting('show_closed_courses') == 'true';

// the section (for the tabs)
$this_section = SECTION_CAMPUS;


$my_user_id = api_get_user_id();


// the header
Display :: display_header('', 'dokeos');

echo '<div id="content" style=" min-height: 300px!important;">';
global $_user;

// creating the form
                $_SESSION['force_password_change'] = true;                  
		$form = new FormValidator('forcepasswordchange');
		$form->addElement('header', '', get_lang('YouMustChangeYourPassword'));
		//$form->addElement('static', 'firstname', get_lang('FirstName'), $user['firstname']);
		//$form->addElement('static', 'firstname', get_lang('LastName'), $user['lastname']);
		$form->addElement('html','</br>');
		$form->addElement('html','<div style="padding-left:110px;">'.get_lang('FirstName').' : '.$_user['firstname'].'</div></br>');
		$form->addElement('html','<div style="padding-left:110px;">'.get_lang('LastName').' : '.$_user['lastname'].'</div></br>');
		$form->addElement('html','<div style="padding-left:110px;">'.get_lang('UserName').' : '.$_user['username'].'</div></br>');
		$form->addElement('password', 'pass1', get_lang('NewPass'),  array('onkeydown' => 'javascript: password_switch_radio_button();', 'class' => 'pass1', 'size' => 20));
		$form->addElement('password', 'pass2', get_lang('langConfirmation'), array('size' => 20));		
		$form->addRule('pass2', get_lang('ThisFieldIsRequired'), 'required');
		if (api_get_setting('show_force_password_change') == 'true') {
			$form->registerRule('passwordlength', 'function', 'passwordlength');
			$form->registerRule('passwordnumbers', 'function', 'passwordnumbers');
			$form->registerRule('passwordcamelcase', 'function', 'passwordcamelcase');
			$form->registerRule('passwordsymbols', 'function', 'passwordsymbols');
			$form->addRule('pass1', get_lang('PasswordTooShort'), 'passwordlength');
			$form->addRule('pass1', get_lang('PasswordMustContainSymbol'), 'passwordsymbols');
			$form->addRule('pass1', get_lang('PasswordMustContainLowerUpper'), 'passwordcamelcase');
			$form->addRule('pass1', get_lang('PasswordMustContainNumber'), 'passwordnumbers');
		}
		$form->addRule(array('pass1', 'pass2'), get_lang('PassTwo'), 'compare');
		$form->addElement('style_submit_button','submitAuth', get_lang('Save'));
		$form->registerRule('newpassworddifferentthanoldpassword', 'function', 'newpassworddifferentthanoldpassword');
		$form->addRule('pass1',get_lang('NewPasswordShouldBeDifferentThanOldPassword'), 'newpassworddifferentthanoldpassword');
		if ($form->validate ()) {
			$values = $form->exportValues ();

                        // Before changing the current password, it should be added to the log
                        update_user_password_log($_user['user_id']);
                        
			// hashing the password
			$password = api_get_encrypted_password($values['pass1']);

			// setting the new password in the user profile
			$sql = "UPDATE ".Database :: get_main_table(TABLE_MAIN_USER)." set password='".$password."', login_counter = 0 WHERE user_id='".Database::escape_string($_user['user_id'])."'";
			$result = Database::query($sql,__FILE__,__LINE__);
			Display::display_confirmation_message(get_lang('NewPasswordSet'));
			$_SESSION['force_password_change'] = false;
                        update_login_counter($_user['user_id']);
                        $urlHome = api_get_path(WEB_PATH);                       
                        ?>
                        <script type="text/javascript">
                            window.location.href = "<?php echo $urlHome; ?>";
                        </script>
                        <?php                                              
		} else {
			$form->display();
		}

echo '</div>';

// display the footer
Display :: display_footer();

