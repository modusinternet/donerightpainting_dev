<?php
header("Content-Type:text/html; charset=UTF-8");
header("Expires: on, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if($_SERVER["SCRIPT_NAME"] != "/ccmsusr/index.php") {
	echo "This script can NOT be called directly.";
	exit;
}

$message = NULL;
$ccms_pass_reset_form_message = NULL;

if($_SESSION["FAIL"] >= 5) {
	// If the users session record indicates that they have attempted to login 5 or more times and failed; do not
	// show this page at all.  Simply redirect them base to the homepage for this site immediatly.
	header("Location: /");
	exit;
} elseif($CLEAN["logout"] == "1") {
	// log out
	$_SESSION["USER_ID"] = "";
	$message = "Logout Successful";
} elseif($CLEAN["login"] == "1") {
	// Login credentials posted, test them.
	if(!ccms_badIPCheck($_SERVER["REMOTE_ADDR"])) {
		$message = "There is a problem with your login, your IP Address is currently being blocked.  Please contact the website administrators directly by either phone or email if you feel this message is in error for more information.";
	} elseif($CLEAN["loginEmail"] == "") {
		$message = "'Email' field missing content.";
	} elseif($CLEAN["loginEmail"] == "MAXLEN") {
		$message = "'Email' field exceeded its maximum number of 255 character.";
	} elseif($CLEAN["loginEmail"] == "INVAL") {
		$message = "'Email' field either contains invalid characters or an invalid email address!";

	} elseif($CLEAN["loginPassword"] == "") {
		$message = "'Password' field missing content.";
	} elseif($CLEAN["loginPassword"] == "MINLEN") {
		$message = "'Password' field is too short, must be a minimum of 8 characters.";
	} elseif($CLEAN["loginPassword"] == "INVAL") {
		$message = "Something is wrong with your password, it came up as INVALID when testing is with with an open (.+) expression.";

	} elseif($CLEAN["g-recaptcha-response"] == "") {
		$message = "'g-recaptcha-response' field missing content.";
	} elseif($CLEAN["g-recaptcha-response"] == "MAXLEN") {
		$message = "'g-recaptcha-response' field exceeded its maximum number of 2048 character.";
	} elseif($CLEAN["g-recaptcha-response"] == "INVAL") {
		$message = "'g-recaptcha-response' field contains invalid characters!";
	} elseif($CLEAN["g-recaptcha-response"] != "" && $CLEAN["g-recaptcha-response"] != "MAXLEN" && $CLEAN["g-recaptcha-response"] != "INVAL") {
		$resp = file_get_contents( "https://www.google.com/recaptcha/api/siteverify?secret={$CFG['GOOGLE_RECAPTCHA_PRIVATEKEY']}&response={$CLEAN['g-recaptcha-response']}&remoteip={$_SERVER['REMOTE_ADDR']}" );
		$resp = json_decode($resp);
		if($resp->success == false) {
			$message = 'Google reCAPTCHA failed.';
		}
	}

	if($message == "") {
		$qry = $CFG["DBH"]->prepare("SELECT * FROM `ccms_user` WHERE `email` = :loginEmail && `status` = 1 LIMIT 1;");
		$qry->execute(array(':loginEmail' => $CLEAN["loginEmail"]));
		$row = $qry->fetch(PDO::FETCH_ASSOC);
		if($row) {
			// An active user with the same email address WAS found in the database.
			if(password_verify($CLEAN["loginPassword"], $row["hash"])) {
				// The submitted password matches the hashed password stored on the server.
				// Rehash the password and replace original password hash on the server to make even more secure.
				// See https://alias.io/2010/01/store-passwords-safely-with-php-and-mysql/ for more details.
				$options = ['cost' => 10];
				$hash = password_hash($CLEAN["loginPassword"], PASSWORD_BCRYPT, $options);

				$qry = $CFG["DBH"]->prepare("UPDATE `ccms_user` SET `hash` = :hash WHERE `id` = :id LIMIT 1;");
				$qry->execute(array(':hash' => $hash, ':id' => $row["id"]));

				$_SESSION["USER_ID"] = $row["id"];
				$_SESSION["FAIL"] = 0;

				//header("Refresh:30; url=/" . $CLEAN["ccms_lng"] . "/user/".$CFG["INDEX"]);
				header("Location: /" . $CLEAN["ccms_lng"] . "/user/".$CFG["INDEX"]);
				exit;
			} else {
				// Password failed so we increment the fail field by 1, once it reaches 5 the login page wont
				// even be available to the user anymore till their session expires.
				$_SESSION["FAIL"] = $_SESSION["FAIL"] + 1;

				if($_SESSION["FAIL"] >= 5) {
					// Maximum number of fails for this session have been reached.  Do not accept anymore tries till this session record expires.
					header("Location: /");
					exit;
				} else {
					$message = "Password failed, please try again.";
				}
			}
		} else {
			// An active user with the same email address WAS NOT found in the database.
			// Login failed so we increment the fail field by 1, once it reaches 5 the login page wont
			// even be available to the user anymore till their session expires.
			$_SESSION["FAIL"] = $_SESSION["FAIL"] + 1;

			if($_SESSION["FAIL"] >= 5) {
				// Maximum number of fails for this session have been reached.  Do not accept anymore tries till this session record expires.
				header("Location: /");
				exit;
			} else {
				$message = "Login failed, please try again.";
			}
		}
	} else {
		// Login failed so we increment the fail field by 1, once it reaches 5 the login page wont
		// even be available to the user anymore till their session expires.
		$_SESSION["FAIL"] = $_SESSION["FAIL"] + 1;

		if($_SESSION["FAIL"] >= 5) {
			// Maximum number of fails for this session have been reached.  Do not accept anymore tries till this session record expires.
			header("Location: /");
			exit;
		} else {
			$message .= " Login failed, please try again.";
		}
	}
}
















 elseif($CLEAN["ccms_pass_reset_form"] == "1") {
	if(!ccms_badIPCheck($_SERVER["REMOTE_ADDR"])) {
		$ccms_pass_reset_form_message["fail"] = "There is a problem with your login, your IP Address is currently being blocked.  Please contact the website administrators directly by either phone or email if you feel this message is in error for more information.";
	} elseif($CLEAN["ccms_pass_reset_form_email"] == "") {
		$ccms_pass_reset_form_message["fail"] = "'ccms_pass_reset_form_email' field missing content.";
	} elseif($CLEAN["ccms_pass_reset_form_email"] == "MAXLEN") {
		$ccms_pass_reset_form_message["fail"] = "'ccms_pass_reset_form_email' field exceeded its maximum number of 255 character.";
	} elseif($CLEAN["ccms_pass_reset_form_email"] == "INVAL") {
		$ccms_pass_reset_form_message["fail"] = "'ccms_pass_reset_form_email' field contains invalid characters!";
	}

	if($ccms_pass_reset_form_message["fail"] == "") {
		$qry = $CFG["DBH"]->prepare("SELECT * FROM `ccms_user` WHERE `email` = :ccms_pass_reset_form_email && `status` = 1 LIMIT 1;");
		$qry->execute(array(':ccms_pass_reset_form_email' => $CLEAN["ccms_pass_reset_form_email"]));
		$row = $qry->fetch(PDO::FETCH_ASSOC);
		if($row) {
			// An active user with the same email address WAS found in the database.
			// So create a new session record which can be linked to in email and used to help recover a lost password.
			$a = time() + 10;
			$b = $a;
			$a = md5($a);
			$c = $b + ($CFG["COOKIE_SESSION_EXPIRE"] * 60);

			$qry = $CFG["DBH"]->prepare("INSERT INTO `ccms_session` (code, first, last, exp, ip, user_agent, user_id, prf) VALUES (:code, :first, :last, :exp, :ip, :user_agent, :id, 1);");
			$qry->execute(array(':code' => $a, ':first' => $b, ':last' => $b, ':exp' => $c, ':ip' => $_SERVER["REMOTE_ADDR"], ':id' => $row["id"], ':user_agent' => $CLEAN["SESSION"]["user_agent"]));

			$boundary = uniqid('np');

			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "From: " . $CFG["EMAIL_FROM"] . "\r\n";
			$headers .= "Reply-To: " . $CFG["EMAIL_FROM"] . "\r\n";
			//$headers .= "Content-type: text/html; charset=UTF-8\r\n";
			$headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";

			$email_message = "This is a MIME encoded message.\r\n\r\n--" . $boundary . "\r\nContent-type: text/plain;charset=utf-8\r\n\r\n";

			//Plain text body
			$email_message .= 'To whom it may concern,

We received a password reset request for an account at ' . $CFG["DOMAIN"] . ' using this email address for verification. If you did not request this email please ignore this message.

Click or Copy and Paste the link bellow into your browser to proceed.

' . $_SERVER["REQUEST_SCHEME"] . '://' . $CFG["DOMAIN"] . '/en/user/?ccms_pass_reset_form_prf=1&ccms_pass_reset_form_code='.$a.'

Note: This recovery link will only work once, and must be used as soon as possible.  If you have any questions please contact us directly at ' . $CFG["EMAIL_FROM"] . '.

Regards,

' . $CFG["DOMAIN"] . '

--------------------
This e-mail may be privileged and/or confidential, and the sender does not waive any related rights and obligations. Any distribution, use or copying of this e-mail or the information it contains by other than an intended recipient is unauthorized. If you received this e-mail in error, please advise us (by return e-mail or otherwise) immediately.';

$email_message .= "\r\n\r\n--" . $boundary . "\r\nContent-type: text/html;charset=utf-8\r\n\r\n";

//Html body
$email_message .= '<html><body>
To whom it may concern,<br />
<br />
We received a password reset request for an account at ' . $CFG["DOMAIN"] . ' using this email address for verification. If you did not request this email please ignore this message.<br />
<br />
Click or Copy and Paste the link bellow into your browser to proceed.<br />
<br />
<a href="' . $_SERVER["REQUEST_SCHEME"] . '://' . $CFG["DOMAIN"] . '/en/user/?ccms_pass_reset_form_prf=1&ccms_pass_reset_form_code='.$a.'">' . $CFG["DOMAIN"] . '/en/user/?ccms_pass_reset_form_prf=1&ccms_pass_reset_form_code='.$a.'</a><br />
<br />
Note: This recovery link will only work once, and must be used as soon as possible.  If you have any questions please contact us directly at ' . $CFG["EMAIL_FROM"] . '.<br />
<br />
Regards,<br />
<br />
' . $CFG["DOMAIN"] . '<br />
<br />
<hr style="height: 1px; width:100%;" />
<span style="font-size: .8em;">This e-mail may be privileged and/or confidential, and the sender does not waive any related rights and obligations. Any distribution, use or copying of this e-mail or the information it contains by other than an intended recipient is unauthorized. If you received this e-mail in error, please advise us (by return e-mail or otherwise) immediately.</span>
</body></html>';
$email_message .= "\r\n\r\n--" . $boundary . "--";

			mail( $CLEAN["ccms_pass_reset_form_email"], "Temporary password reset link from " . $CFG["DOMAIN"], $email_message, $headers, "-f" . $CFG["EMAIL_BOUNCES_RETURNED_TO"] );

			$ccms_pass_reset_form_message["success"] = "A temporary password reset link has been emailed to " . $CLEAN["ccms_pass_reset_form_email"] . ". Follow the instructions in the email to reset your password. If you do not receive an email soon, please contact support at: " . $CFG["EMAIL_FROM"] . ".";
		} else {
			// An active user with the same email address WAS NOT found in the database.
			// Password reset failed so we increment the fail field by 1, once it reaches 5 the login page wont
			// even be available to the user anymore till their session expires.
			//$CLEAN["SESSION"]["fail"] = $CLEAN["SESSION"]["fail"] + 1;
			$_SESSION["FAIL"] = $_SESSION["FAIL"] + 1;

			$qry = $CFG["DBH"]->prepare("UPDATE `ccms_session` SET `fail` = :fail WHERE `code` = :code LIMIT 1;");
			//$qry->execute(array(':fail' => $CLEAN["SESSION"]["fail"], ':code' => $CLEAN["SESSION"]["code"]));
			$qry->execute(array(':fail' => $_SESSION["FAIL"], ':code' => $CLEAN["SESSION"]["code"]));
			//if($CLEAN["SESSION"]["fail"] >= 5) {
			if($_SESSION["FAIL"] >= 5) {
					// Maximum number of fails for this session have been reached.  Do not accept anymore tries till this session record expires.
				header("Location: /");
				exit;
			} else {
				$ccms_pass_reset_form_message["fail"] = "Password reset failed.  An active user with the email address you submitted was not found.";
			}
		}
	} else {
		// Password reset failed so we increment the fail field by 1, once it reaches 5 the login page wont
		// even be available to the user anymore till their session expires.
		//$CLEAN["SESSION"]["fail"] = $CLEAN["SESSION"]["fail"] + 1;
		$_SESSION["FAIL"] = $_SESSION["FAIL"] + 1;
		$qry = $CFG["DBH"]->prepare("UPDATE `ccms_session` SET `fail` = :fail WHERE `code` = :code LIMIT 1;");
		//$qry->execute(array(':fail' => $CLEAN["SESSION"]["fail"], ':code' => $CLEAN["SESSION"]["code"]));
		$qry->execute(array(':fail' => $_SESSION["FAIL"], ':code' => $CLEAN["SESSION"]["code"]));
		//if($CLEAN["SESSION"]["fail"] >= 5) {
		if($_SESSION["FAIL"] >= 5) {
				// Maximum number of fails for this session have been reached.  Do not accept anymore tries till this session record expires.
			header("Location: /");
			exit;
		} else {
			$ccms_pass_reset_form_message["fail"] .= "Password reset failed, please try again.";
		}
	}
} elseif($CLEAN["ccms_pass_reset_form_prf"] == "1") {
	// prf = pass reset flag
	if(!ccms_badIPCheck($_SERVER["REMOTE_ADDR"])) {
		$ccms_pass_reset_form_message["fail"] = "There is a problem with your login, your IP Address is currently being blocked.  Please contact the website administrators directly by either phone or email if you feel this message is in error for more information.";
	} elseif($CLEAN["ccms_pass_reset_form_code"] == "") {
		$ccms_pass_reset_form_message["fail"] = "'ccms_pass_reset_form_code' field missing content.";
	} elseif($CLEAN["ccms_pass_reset_form_code"] == "MAXLEN") {
		$ccms_pass_reset_form_message["fail"] = "'ccms_pass_reset_form_code' field exceeded its maximum number of 64 character.";
	} elseif($CLEAN["ccms_pass_reset_form_code"] == "INVAL") {
		$ccms_pass_reset_form_message["fail"] = "'ccms_pass_reset_form_code' field contains invalid characters!";
	}

	if($ccms_pass_reset_form_message["fail"] == "") {
		// This is an incoming password reset hyperlink, so first we need to make sure the prf session is still available.
		// Check the 'ccms_session' table for matches.
		$qry = $CFG["DBH"]->prepare("SELECT * FROM `ccms_session` WHERE `code` = :ccms_session AND `ip` = :ip AND `user_agent` = :user_agent AND `prf` = 1 LIMIT 1;");
		$qry->execute(array(':ccms_session' => $CLEAN["ccms_pass_reset_form_code"], ':ip' => $_SERVER["REMOTE_ADDR"], ':user_agent' => $CLEAN["SESSION"]["user_agent"]));
		$row = $qry->fetch(PDO::FETCH_ASSOC);

		if(!$row) {
			// The prf session in the URL is either expired, invalid from this device or location.
			$CLEAN["ccms_pass_reset_form_prf"] = "";
			// Password reset failed so we increment the fail field by 1, once it reaches 5 the login page wont
			// even be available to the user anymore till their session expires.
			//$CLEAN["SESSION"]["fail"] = $CLEAN["SESSION"]["fail"] + 1;
			$_SESSION["FAIL"] = $_SESSION["FAIL"] + 1;
			$qry = $CFG["DBH"]->prepare("UPDATE `ccms_session` SET `fail` = :fail WHERE `code` = :code LIMIT 1;");
			//$qry->execute(array(':fail' => $CLEAN["SESSION"]["fail"], ':code' => $CLEAN["SESSION"]["code"]));
			$qry->execute(array(':fail' => $_SESSION["FAIL"], ':code' => $CLEAN["SESSION"]["code"]));
			//if($CLEAN["SESSION"]["fail"] >= 5) {
			if($_SESSION["FAIL"] >= 5) {
					// Maximum number of fails for this session have been reached.  Do not accept anymore tries till this session record expires.
				header("Location: /");
				exit;
			} else {
				$ccms_pass_reset_form_message["fail"] = "Password reset failed.  This link is expired or invalid from this browser/device/location.  Please request a new password reset Email for this device from this location.";
			}
		}
	} else {
		// Something was wrong with the ccms_pass_reset_form_code variable.
		$CLEAN["ccms_pass_reset_form_prf"] = "";
		// Password reset failed so we increment the fail field by 1, once it reaches 5 the login page wont
		// even be available to the user anymore till their session expires.
		//$CLEAN["SESSION"]["fail"] = $CLEAN["SESSION"]["fail"] + 1;
		$_SESSION["FAIL"] = $_SESSION["FAIL"] + 1;
		$qry = $CFG["DBH"]->prepare("UPDATE `ccms_session` SET `fail` = :fail WHERE `code` = :code LIMIT 1;");
		//$qry->execute(array(':fail' => $CLEAN["SESSION"]["fail"], ':code' => $CLEAN["SESSION"]["code"]));
		$qry->execute(array(':fail' => $_SESSION["FAIL"], ':code' => $CLEAN["SESSION"]["code"]));
		//if($CLEAN["SESSION"]["fail"] >= 5) {
		if($_SESSION["FAIL"] >= 5) {
				// Maximum number of fails for this session have been reached.  Do not accept anymore tries till this session record expires.
			header("Location: /");
			exit;
		} else {
			$ccms_pass_reset_form_message["fail"] .= " Password reset failed, please try again.";
		}
	}
} elseif($CLEAN["ccms_pass_reset_form_prf"] == "2") {
	// prf = pass reset flag
	// This is an incoming password reset hyperlink
	if(!ccms_badIPCheck($_SERVER["REMOTE_ADDR"])) {
		$ccms_pass_reset_form_message["fail"] = "There is a problem with your login, your IP Address is currently being blocked.  Please contact the website administrators directly by either phone or email if you feel this message is in error for more information.";
	} elseif($CLEAN["ccms_pass_reset_form_code"] == "") {
		$ccms_pass_reset_form_message["fail"] = "'ccms_pass_reset_form_code' field missing content.";
	} elseif($CLEAN["ccms_pass_reset_form_code"] == "MAXLEN") {
		$ccms_pass_reset_form_message["fail"] = "'ccms_pass_reset_form_code' field exceeded its maximum number of 64 character.";
	} elseif($CLEAN["ccms_pass_reset_form_code"] == "INVAL") {
		$ccms_pass_reset_form_message["fail"] = "'ccms_pass_reset_form_code' field contains invalid characters!";

	} elseif($CLEAN["password1"] == "") {
		$ccms_pass_reset_form_message["fail"] = "'password1' field missing content.";
	} elseif($CLEAN["password1"] == "MINLEN") {
		$ccms_pass_reset_form_message["fail"] = "'password1' field is too short, must be a minimum of 8 characters.";
	} elseif($CLEAN["password1"] == "INVAL") {
		$ccms_pass_reset_form_message["fail"] = "Something is wrong with your password1, it came up as INVALID when testing is with with an open (.+) expression.";

	} elseif($CLEAN["password2"] == "") {
		$ccms_pass_reset_form_message["fail"] = "'password2' field missing content.";
	} elseif($CLEAN["password2"] == "MINLEN") {
		$ccms_pass_reset_form_message["fail"] = "'password2' field is too short, must be a minimum of 8 characters.";
	} elseif($CLEAN["password2"] == "INVAL") {
		$ccms_pass_reset_form_message["fail"] = "Something is wrong with your password2, it came up as INVALID when testing is with with an open (.+) expression.";

	} elseif($CLEAN["password1"] != $CLEAN["password2"]) {
		$ccms_pass_reset_form_message["fail"] = "Password1 and password2 do not match.";

	} elseif($CLEAN["g-recaptcha-response"] == "") {
		$ccms_pass_reset_form_message["fail"] = "'g-recaptcha-response' field missing content.";
	} elseif($CLEAN["g-recaptcha-response"] == "MAXLEN") {
		$ccms_pass_reset_form_message["fail"] = "'g-recaptcha-response' field exceeded its maximum number of 2048 character.";
	} elseif($CLEAN["g-recaptcha-response"] == "INVAL") {
		$ccms_pass_reset_form_message["fail"] = "'g-recaptcha-response' field contains invalid characters!";
	} elseif($CLEAN["g-recaptcha-response"] != "" && $CLEAN["g-recaptcha-response"] != "MAXLEN" && $CLEAN["g-recaptcha-response"] != "INVAL") {
		$resp = file_get_contents( "https://www.google.com/recaptcha/api/siteverify?secret={$CFG['GOOGLE_RECAPTCHA_PRIVATEKEY']}&response={$CLEAN['g-recaptcha-response']}&remoteip={$_SERVER['REMOTE_ADDR']}" );
		$resp = json_decode($resp);
		if($resp->success == false) {
			$ccms_pass_reset_form_message["fail"] = 'Google reCAPTCHA failed.';
		}
	}

	if($ccms_pass_reset_form_message["fail"] == "") {
		// This is an password reset submittion, so first we need to make sure the prf session is still available.
		// Check the 'ccms_session' table for matches.
		$qry = $CFG["DBH"]->prepare("SELECT * FROM `ccms_session` WHERE `code` = :ccms_session AND `ip` = :ip AND `user_agent` = :user_agent AND `prf` = 1 LIMIT 1;");
		$qry->execute(array(':ccms_session' => $CLEAN["ccms_pass_reset_form_code"], ':ip' => $_SERVER["REMOTE_ADDR"], ':user_agent' => $CLEAN["SESSION"]["user_agent"]));
		$row = $qry->fetch(PDO::FETCH_ASSOC);

		if(!$row) {
			// The prf session in the URL is either expired, invalid from this device or location.
			$CLEAN["ccms_pass_reset_form_prf"] = "";
			$ccms_pass_reset_form_message["fail"] = "Password reset failed.  This link is expired or invalid from this browser/device/location.  Please request a new password reset Email for this device from this location.";
		} else {
			// The prf session is valid.
			// Remove the Password Reset session from the database because they are one time use only.
			$id = $row["user_id"];
			$qry = $CFG["DBH"]->prepare("DELETE FROM `ccms_session` WHERE `id` = :id LIMIT 1;");
			$qry->execute(array(':id' => $row["id"]));
			// Confirm there is a live, active, user account under the specified user id.
			$qry = $CFG["DBH"]->prepare("SELECT * FROM `ccms_user` WHERE `id` = :id && `status` = 1 LIMIT 1;");
			$qry->execute(array(':id' => $id));
			$row = $qry->fetch(PDO::FETCH_ASSOC);
			if(!$row) {
				// Failed, an active user of the provided ID was not found.
				$qry = $CFG["DBH"]->prepare("DELETE FROM `ccms_session` WHERE `code` = :ccms_session LIMIT 1;");
				$qry->execute(array(':ccms_session' => $CLEAN["ccms_pass_reset_form_code"]));
				$CLEAN["ccms_pass_reset_form_prf"] = "";
				$ccms_pass_reset_form_message["fail"] = "Password reset failed.  An active user of the provided ID was not found.  Please request a new password reset Email for this browser/device/location because they are one-time use only.";
			} else {
				// Success, an active user of the provided ID WAS found.
				// Encrypt the new password and overwrite the old one.
				// Rehash the password and replace original password hash on the server to make even more secure.
				// See https://alias.io/2010/01/store-passwords-safely-with-php-and-mysql/ for more details.
				$options = ['cost' => 10];
				$hash = password_hash($CLEAN["password1"], PASSWORD_BCRYPT, $options);

				$qry = $CFG["DBH"]->prepare("UPDATE `ccms_user` SET `hash` = :hash WHERE `id` = :id LIMIT 1;");
				$qry->execute(array(':hash' => $hash, ':id' => $id));

				$qry = $CFG["DBH"]->prepare("DELETE FROM `ccms_session` WHERE `code` = :ccms_session LIMIT 1;");
				$qry->execute(array(':ccms_session' => $CLEAN["ccms_pass_reset_form_code"]));
				$CLEAN["ccms_pass_reset_form_prf"] = "";

				$ccms_pass_reset_form_message["success"] = "Success!  Your password has been updated, you can now log in with it using the form above.";
			}
		}
	} else {
		// Something is wrong with one or more of the required fields.  The password reset attempt failed and must be done again.
		// Remove the Password Reset session from the database because they are one time use only.
		$qry = $CFG["DBH"]->prepare("DELETE FROM `ccms_session` WHERE `code` = :ccms_session LIMIT 1;");
		$qry->execute(array(':ccms_session' => $CLEAN["ccms_pass_reset_form_code"]));
		$CLEAN["ccms_pass_reset_form_prf"] = "";

		// Password reset failed so we increment the fail field by 1, once it reaches 5 the login page wont
		// even be available to the user anymore till their session expires.
		//$CLEAN["SESSION"]["fail"] = $CLEAN["SESSION"]["fail"] + 1;
		$_SESSION["FAIL"] = $_SESSION["FAIL"] + 1;
		$qry = $CFG["DBH"]->prepare("UPDATE `ccms_session` SET `fail` = :fail WHERE `code` = :code LIMIT 1;");
		//$qry->execute(array(':fail' => $CLEAN["SESSION"]["fail"], ':code' => $CLEAN["SESSION"]["code"]));
		$qry->execute(array(':fail' => $_SESSION["FAIL"], ':code' => $CLEAN["SESSION"]["code"]));
		//if($CLEAN["SESSION"]["fail"] >= 5) {
		if($_SESSION["FAIL"] >= 5) {
				// Maximum number of fails for this session have been reached.  Do not accept anymore tries till this session record expires.
			header("Location: /");
			exit;
		} else {
			$ccms_pass_reset_form_message["fail"] .= " Please request a new password reset Email for this browser/device/location because they are one-time use only.";
		}
	}
}

if($CLEAN["logout"] == "" && $CLEAN["login"] == "" && $CLEAN["ccms_pass_reset_form"] == "" && $CLEAN["ccms_pass_reset_form_prf"] == "") {
	$CLEAN["login"] = "1";
}
?>















<!DOCTYPE html>
<html id="no-fouc" lang="en" style="opacity: 0;">
	<head>
		<meta charset="utf-8">
		<title>Login</title>
		<meta name="description" content="" />
		{CCMS_TPL:header-head.html}
	</head>
	<body>
<?php if($CLEAN["logout"] == "1" || $CLEAN["login"] == "1" || $CLEAN["ccms_pass_reset_form"] == "1" || $CLEAN["ccms_pass_reset_form_prf"] == "2"): ?>

		<div class="container">
			<div class="row">
				<div class="col-md-5 col-md-offset-3">

					<div>
						<!-- img alt="Custodian CMS Banner." src="/ccmsusr/_img/ccms-535x107.png" style="height: 56px; margin-top: 20px;" title="Custodian CMS Banner.  Easy gears no spilled beers." -->







<svg style="margin-top:20px" version="1.1" viewBox="0 0 210 63.26" xmlns="http://www.w3.org/2000/svg">
	<g transform="translate(-3.591e-7 -.02177)">
		<g transform="matrix(.4872 0 0 .4872 125.8 -147.1)" style="stroke-width:2.053">
			<g transform="matrix(1.108 0 0 1.108 5.208 -39.89)" style="stroke-width:1.852">
				<g transform="matrix(.9136 0 0 .9136 -455.2 203.5)" style="fill:#d7680f">
					<path transform="scale(.2646)" d="m1114 434.6c-6.721-0.0385-12.5 4.758-13.7 11.37l-5.848 32.23c-20.11 4.774-39.36 12.6-57.1 23.21l-26.65-18.92c-5.48-3.889-12.96-3.279-17.74 1.445l-42.06 41.58c-4.778 4.724-5.473 12.2-1.646 17.72l18.62 26.87c-10.81 17.61-18.85 36.78-23.86 56.83l-32.29 5.48c-6.627 1.123-11.49 6.845-11.53 13.57l-0.3359 59.14c-0.0378 6.719 4.757 12.49 11.37 13.69l32.23 5.85c4.774 20.11 12.6 39.36 23.21 57.1l-18.92 26.65c-3.889 5.48-3.279 12.96 1.445 17.74l41.58 42.06c4.724 4.778 12.2 5.473 17.72 1.646l26.87-18.62c17.61 10.81 36.78 18.85 56.83 23.86l5.482 32.29c1.123 6.626 6.844 11.49 13.56 11.53l59.14 0.3359c6.721 0.0385 12.5-4.758 13.7-11.37l5.848-32.23c20.11-4.774 39.36-12.6 57.1-23.21l26.66 18.92c5.48 3.889 12.96 3.279 17.74-1.445l42.06-41.58-88.3-62.29c-2.142 2.537-4.495 5.078-7.201 7.754-39.18 38.74-97.85 50.06-148.6 28.69-50.78-21.38-83.69-71.24-83.38-126.3 0.3131-55.1 33.78-104.6 84.81-125.4 51.02-20.8 109.6-8.808 148.3 30.38 2.676 2.706 5 5.273 7.113 7.834l89.01-61.28-41.58-42.06c-4.724-4.778-12.2-5.473-17.72-1.646l-26.87 18.62c-17.61-10.81-36.78-18.85-56.83-23.86l-5.482-32.29c-1.123-6.626-6.844-11.49-13.56-11.53zm64.77 47.94c-0.081 0.3182-0.1416 0.641-0.1817 0.9668-12.29 746.7-432.6 51.93 0.1817-0.9668z" style="fill:#d7680f"/>
				</g>
				<g transform="matrix(.3344 0 0 -.3344 559.1 249.8)" style="stroke-width:1.465">
					<g style="fill:#d7680f;stroke-width:1.465">
						<path d="m-1770-357.9c1.382 1.688 2.867 3.289 4.447 4.794h0.01c9.829 9.439 22.94 14.69 36.57 14.65 0.8652 0 1.737-0.0199 2.609-0.063 28-1.665 49.99-24.61 50.47-52.65 0.136-16.13-7.118-31.44-19.69-41.55-2.276-1.927-3.567-4.773-3.518-7.755v-7.39c-0.018-4.2-2.671-7.936-6.631-9.336v-3.925c-0.01-2.455-0.9303-4.819-2.586-6.631 3.448-3.748 3.448-9.514 0-13.26 3.676-4.01 3.404-10.24-0.6054-13.92-1.064-0.9747-2.328-1.704-3.705-2.136-1.514-9-10.04-15.07-19.04-13.56-6.945 1.168-12.39 6.61-13.56 13.56-5.194 1.616-8.096 7.136-6.48 12.33 0.4302 1.383 1.16 2.654 2.137 3.722-3.448 3.748-3.448 9.514 0 13.26-1.656 1.812-2.577 4.176-2.586 6.631v3.925c-3.96 1.4-6.613 5.136-6.631 9.336v7.775c-0.058 2.968-1.449 5.752-3.786 7.582-22.65 18.55-25.98 51.95-7.431 74.6zm41.06-146.3c4.214 6e-3 7.969 2.66 9.379 6.631h-18.76c1.41-3.971 5.166-6.626 9.379-6.631zm-13.26 13.26h26.52c1.831 0 3.315 1.484 3.315 3.315s-1.484 3.315-3.315 3.315h-26.52c-1.831 0-3.315-1.484-3.315-3.315s1.484-3.315 3.315-3.315zm0 13.26h26.52c1.831 0 3.315 1.484 3.315 3.315 0 1.831-1.484 3.315-3.315 3.315h-26.52c-1.831 0-3.315-1.484-3.315-3.315 0-1.831 1.484-3.315 3.315-3.315zm-3.315 16.58c0-1.831 1.484-3.315 3.315-3.315h26.52c1.831 0 3.315 1.484 3.315 3.315v3.315h-33.15z" style="fill:#d7680f;stroke-width:1.465"/>
						<path d="m-1729-331.8c-1.831 0-3.316 1.484-3.316 3.315v13.26c0 1.831 1.485 3.315 3.316 3.315s3.316-1.484 3.316-3.315v-13.26c0-1.831-1.484-3.315-3.316-3.315z" style="fill:#d7680f;stroke-width:1.465"/>
						<path d="m-1758-339.8c0-7.4e-4 -0.01-2e-3 -0.01-2e-3 -1.586-0.9146-3.614-0.3705-4.528 1.216 0 4.2e-4 -4e-4 7.5e-4 -4e-4 7.5e-4l-6.631 11.48c-0.9258 1.58-0.3962 3.611 1.184 4.537s3.611 0.3962 4.537-1.184c0.01-0.0124 0.015-0.0252 0.023-0.0381l6.631-11.48c0.9171-1.585 0.3754-3.613-1.21-4.53z" style="fill:#d7680f;stroke-width:1.465"/>
						<path d="m-1785-362.9c-0.013 8e-3 -0.026 0.015-0.039 0.0223l-11.48 6.631c-1.592 0.9047-2.149 2.929-1.244 4.521 0.9046 1.592 2.929 2.149 4.52 1.244 0.013-8e-3 0.026-0.015 0.039-0.0224l11.48-6.631c1.592-0.9047 2.149-2.929 1.244-4.521-0.9047-1.592-2.929-2.149-4.52-1.244z" style="fill:#d7680f;stroke-width:1.465"/>
						<path d="m-1788-391.5c0-1.831-1.484-3.315-3.316-3.315h-13.26c-1.831 0-3.316 1.484-3.316 3.315s1.485 3.315 3.316 3.315h13.26c1.831 0 3.316-1.484 3.316-3.315z" style="fill:#d7680f;stroke-width:1.465"/>
						<path d="m-1796-426.7c0.012 8e-3 0.025 0.0145 0.038 0.0219l11.48 6.631c1.58 0.9258 3.611 0.3962 4.537-1.184 0.9258-1.58 0.3961-3.611-1.184-4.537-0.013-8e-3 -0.025-0.0145-0.038-0.0219l-11.48-6.631c-1.58-0.9258-3.611-0.3962-4.537 1.184-0.9258 1.579-0.3962 3.61 1.184 4.537z" style="fill:#d7680f;stroke-width:1.465"/>
						<path d="m-1674-419.7c0.5819 0 1.154-0.1533 1.658-0.4443l11.48-6.631c1.592-0.9051 2.148-2.929 1.243-4.521s-2.929-2.148-4.521-1.243c-0.012 7e-3 -0.025 0.0145-0.038 0.0219l-11.48 6.631c-1.586 0.9155-2.129 2.943-1.213 4.529 0.5929 1.026 1.687 1.658 2.872 1.658z" style="fill:#d7680f;stroke-width:1.465"/>
						<path d="m-1652-394.8h-13.26c-1.831 0-3.315 1.484-3.315 3.315s1.484 3.315 3.315 3.315h13.26c1.831 0 3.315-1.484 3.315-3.315s-1.484-3.315-3.315-3.315z" style="fill:#d7680f;stroke-width:1.465"/>
						<path d="m-1661-356.2c-0.013-8e-3 -0.026-0.015-0.039-0.0223l-11.48-6.631c-1.58-0.9262-3.611-0.3966-4.537 1.183-0.9263 1.579-0.3966 3.611 1.183 4.537 0.012 8e-3 0.026 0.015 0.039 0.0224l11.48 6.631c1.58 0.9262 3.611 0.3966 4.537-1.183 0.9263-1.579 0.3966-3.611-1.183-4.537z" style="fill:#d7680f;stroke-width:1.465"/>
						<path d="m-1688-327.1-6.631-11.48c-0.9047-1.592-2.929-2.149-4.52-1.244-1.592 0.9047-2.149 2.929-1.244 4.521 0.01 0.0128 0.015 0.0257 0.023 0.0385l6.631 11.48c0.9048 1.592 2.929 2.149 4.521 1.244 1.592-0.9047 2.149-2.929 1.244-4.521-0.01-0.0128-0.015-0.0257-0.023-0.0385z" style="fill:#d7680f;stroke-width:1.465"/>
					</g>
				</g>
				<g transform="matrix(1.76 0 0 1.76 -186.7 -2091)" style="fill:#86b135;font-feature-settings:normal;font-variant-caps:normal;font-variant-ligatures:normal;font-variant-numeric:normal" aria-label="ust    dian">
					<path d="m49.47 1391v25.92h-8.027l0.1367-2.148q-0.8203 1.309-2.031 1.973-1.191 0.6445-2.754 0.6445-1.777 0-2.949-0.6249-1.172-0.625-1.738-1.66-0.5469-1.035-0.6836-2.148-0.1367-1.133-0.1367-4.473v-17.48h7.891v17.64q0 3.027 0.1758 3.594 0.1953 0.5664 1.016 0.5664 0.8789 0 1.035-0.5859 0.1758-0.5859 0.1758-3.77v-17.44z" style="fill:#86b135;font-feature-settings:normal;font-variant-caps:normal;font-variant-ligatures:normal;font-variant-numeric:normal;stroke-width:.2783"/>
					<path d="m68.46 1399h-6.992v-1.406q0-1.797-0.2148-2.266-0.1953-0.4883-1.016-0.4883-0.6641 0-0.9961 0.4493-0.332 0.4296-0.332 1.308 0 1.191 0.1562 1.758 0.1758 0.5468 0.9961 1.211 0.8398 0.664 3.418 1.934 3.437 1.68 4.512 3.164 1.074 1.484 1.074 4.316 0 3.164-0.8203 4.785-0.8203 1.602-2.754 2.48-1.914 0.8593-4.629 0.8593-3.008 0-5.156-0.9374-2.129-0.9375-2.93-2.539-0.8008-1.602-0.8008-4.844v-1.25h6.992v1.641q0 2.09 0.2539 2.715 0.2734 0.625 1.055 0.625 0.8398 0 1.172-0.4101 0.332-0.4297 0.332-1.777 0-1.855-0.4297-2.324-0.4492-0.4687-4.59-2.773-3.477-1.953-4.238-3.535-0.7617-1.602-0.7617-3.789 0-3.105 0.8203-4.57 0.8203-1.484 2.773-2.285 1.973-0.8008 4.57-0.8008 2.578 0 4.375 0.6641 1.816 0.6445 2.773 1.719 0.9766 1.074 1.172 1.992 0.2148 0.918 0.2148 2.871z" style="fill:#86b135;font-feature-settings:normal;font-variant-caps:normal;font-variant-ligatures:normal;font-variant-numeric:normal;stroke-width:.2783"/>
					<path d="m79.49 1387v4.082h2.129v4.102h-2.129v13.87q0 2.559 0.2539 2.852 0.2734 0.2929 2.207 0.2929v4.18h-3.184q-2.695 0-3.848-0.2148-1.152-0.2344-2.031-1.035-0.8789-0.8203-1.094-1.856-0.2148-1.055-0.2148-4.922v-13.16h-1.699v-4.102h1.699v-4.082z" style="fill:#86b135;font-feature-settings:normal;font-variant-caps:normal;font-variant-ligatures:normal;font-variant-numeric:normal;stroke-width:.2783"/>
					<path d="m129.4 1385v31.62h-7.891v-1.875q-1.133 1.172-2.383 1.758-1.25 0.5859-2.617 0.5859-1.836 0-3.184-0.957-1.348-0.9765-1.738-2.246-0.3711-1.27-0.3711-4.141v-12.13q0-2.988 0.3711-4.238 0.3906-1.25 1.758-2.187 1.367-0.9571 3.262-0.9571 1.465 0 2.676 0.5274 1.23 0.5273 2.227 1.582v-7.344zm-7.891 12.23q0-1.426-0.2539-1.914-0.2344-0.4883-0.957-0.4883-0.7031 0-0.957 0.4493-0.2344 0.4296-0.2344 1.953v12.7q0 1.582 0.2344 2.09 0.2344 0.4882 0.8984 0.4882 0.7617 0 1.016-0.5468 0.2539-0.5664 0.2539-2.734z" style="fill:#86b135;font-feature-settings:normal;font-variant-caps:normal;font-variant-ligatures:normal;font-variant-numeric:normal;stroke-width:.2783"/>
					<path d="m140.4 1385v4.121h-8.125v-4.121zm0 5.703v25.92h-8.125v-25.92z" style="fill:#86b135;font-feature-settings:normal;font-variant-caps:normal;font-variant-ligatures:normal;font-variant-numeric:normal;stroke-width:.2783"/>
					<path d="m150.3 1401h-7.402v-1.738q0-3.008 0.6836-4.629 0.7031-1.641 2.793-2.891t5.43-1.25q4.004 0 6.035 1.426 2.031 1.406 2.441 3.476 0.4102 2.051 0.4102 8.477v13.01h-7.676v-2.305q-0.7226 1.387-1.875 2.09-1.133 0.6835-2.715 0.6835-2.07 0-3.809-1.152-1.719-1.172-1.719-5.098v-2.129q0-2.91 0.918-3.965 0.918-1.055 4.551-2.461 3.887-1.523 4.16-2.051 0.2734-0.5274 0.2734-2.148 0-2.031-0.3125-2.637-0.293-0.625-0.9961-0.625-0.8008 0-0.9961 0.5274-0.1953 0.5078-0.1953 2.676zm2.5 3.555q-1.895 1.387-2.207 2.324-0.293 0.9374-0.293 2.695 0 2.012 0.2539 2.598 0.2734 0.5859 1.055 0.5859 0.7422 0 0.957-0.4492 0.2344-0.4687 0.2344-2.422z" style="fill:#86b135;font-feature-settings:normal;font-variant-caps:normal;font-variant-ligatures:normal;font-variant-numeric:normal;stroke-width:.2783"/>
					<path d="m171.4 1391-0.1367 2.383q0.8594-1.426 2.09-2.129 1.25-0.7227 2.871-0.7227 2.031 0 3.32 0.9571 1.289 0.957 1.66 2.422 0.3711 1.445 0.3711 4.844v18.16h-7.891v-17.95q0-2.676-0.1758-3.262-0.1758-0.586-0.9766-0.586-0.8398 0-1.055 0.6836-0.2148 0.6641-0.2148 3.594v17.52h-7.891v-25.92z" style="fill:#86b135;font-feature-settings:normal;font-variant-caps:normal;font-variant-ligatures:normal;font-variant-numeric:normal;stroke-width:.2783"/>
				</g>
				<g transform="matrix(-2.597 0 0 -3.699 2340 5059)" style="fill:#d7680f;font-feature-settings:normal;font-variant-caps:normal;font-variant-ligatures:normal;font-variant-numeric:normal" aria-label="CMS">
					<path d="m846 1262v-1.464h0.9803q0.4276 0 0.5319-0.049 0.1078-0.045 0.1078-0.2051 0-0.1807-0.1286-0.2294-0.1286-0.049-0.5562-0.049h-2.614q-0.4102 0-0.5354 0.049-0.1251 0.049-0.1251 0.219 0 0.1634 0.1251 0.2121 0.1252 0.052 0.5875 0.052h0.7057v1.464h-0.219q-0.8725 0-1.238-0.1251-0.365-0.1217-0.6396-0.5458-0.2746-0.4206-0.2746-1.039 0-0.6431 0.2329-1.06 0.2329-0.4171 0.6431-0.5527 0.4137-0.1356 1.241-0.1356h1.644q0.6084 0 0.9108 0.042 0.3059 0.042 0.5875 0.2469 0.2816 0.2085 0.4415 0.5735 0.1634 0.3685 0.1634 0.8448 0 0.6466-0.2503 1.067t-0.6257 0.5527q-0.372 0.1321-1.161 0.1321z" style="fill:#d7680f;font-feature-settings:normal;font-variant-caps:normal;font-variant-ligatures:normal;font-variant-numeric:normal;stroke-width:.1581"/>
					<path d="m848.5 1267h-5.628v-1.279h3.8l-3.8-0.511v-0.9073l3.713-0.5388h-3.713v-1.279h5.628v1.895q-0.5075 0.083-1.196 0.1773l-1.432 0.2016 2.628 0.3337z" style="fill:#d7680f;font-feature-settings:normal;font-variant-caps:normal;font-variant-ligatures:normal;font-variant-numeric:normal;stroke-width:.1581"/>
					<path d="m846.8 1271v-1.359h0.4172q0.292 0 0.372-0.052 0.0799-0.052 0.0799-0.1738 0-0.1321-0.1078-0.2016-0.1078-0.066-0.3268-0.066-0.2816 0-0.4241 0.076-0.1425 0.073-0.3442 0.4137-0.5805 0.9768-0.9525 1.231t-1.199 0.2538q-0.6014 0-0.8864-0.1426-0.285-0.139-0.4797-0.5422-0.1912-0.4033-0.1912-0.9386 0-0.5875 0.2225-1.005 0.2225-0.4137 0.5666-0.5423t0.9768-0.1286h0.3685v1.359h-0.6848q-0.3163 0-0.4067 0.056-0.0904 0.059-0.0904 0.2051 0 0.146 0.1147 0.2156 0.1147 0.073 0.3407 0.073 0.4971 0 0.6501-0.1356 0.153-0.1391 0.511-0.6848 0.3615-0.5458 0.5249-0.7231 0.1634-0.1773 0.4519-0.2955 0.2885-0.1147 0.737-0.1147 0.6466 0 0.9455 0.1634 0.299 0.1669 0.4658 0.5353 0.1703 0.3685 0.1703 0.89 0 0.5701-0.1842 0.9698-0.1842 0.4033-0.4658 0.5319-0.2781 0.1321-0.949 0.1321z" style="fill:#d7680f;font-feature-settings:normal;font-variant-caps:normal;font-variant-ligatures:normal;font-variant-numeric:normal;stroke-width:.1581"/>
				</g>
			</g>
		</g>
	</g>
</svg>







					</div>
<?php if(isset($message) && $message != ""): ?>
					<div class="alert alert-danger" style="margin-bottom: 0; margin-top: 20px;">
						<?php echo $message; ?>
					</div>
<?php endif ?>
					<div class="login-panel panel panel-default" style="margin-top: 20px;">
						<div class="panel-heading">
							<h3 class="panel-title">Login - Session Expired</h3>
						</div>
						<div class="panel-body">

							<form action="/{CCMS_LIB:_default.php;FUNC:ccms_lng}/user/" id="loginForm" method="post">
								<input type="hidden" name="login" value="1">
								<div id="login-status" style="color:#ec7f27; font-weight:bold;"></div>
								<div class="form-group">
									<label for="loginEmail">Email Address</label>
									<div class="input-group">
										<div class="input-group-addon"><i class="fa fa-at"></i></div>
										<!--input class="form-control" id="loginEmail" name="loginEmail" placeholder="Email" type="email" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"-->
										<input class="form-control" id="loginEmail" name="loginEmail" placeholder="Email" type="email">
									</div>
								</div>
								<div class="form-group">
									<label for="loginPassword">Password</label>
									<div class="input-group">
										<div class="input-group-addon"><i class="fa fa-key"></i></div>
										<!--input class="form-control" id="loginPassword" name="loginPassword" placeholder="Password" type="password" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');"-->
										<input class="form-control" id="loginPassword" name="loginPassword" placeholder="Password" type="password">
									</div>
								</div>
								<div class="form-group">
									<div class="input-group" id="form-captcha">
										<div class="g-recaptcha" data-sitekey="{CCMS_LIB:_default.php;FUNC:ccms_googleRecapPubKey}"></div>
										<label id="error-grecaptcha" class="error" for="g-recaptcha" style="display: none;"></label>
									</div>
								</div>
								<button type="submit" class="btn btn-lg btn-success btn-block">Login</button>
							</form>

						</div>
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom: 20px;">
				<div class="col-md-5 col-md-offset-3">
					<a href="/{CCMS_LIB:_default.php;FUNC:ccms_lng}/">
						<i class="fa fa-caret-square-o-left"></i> Return
					</a>
					<a href="/{CCMS_LIB:_default.php;FUNC:ccms_lng}/user/" id="loginHelpLink" style="float: right;">
						Login Help <i class="fa fa-caret-square-o-right"></i>
					</a>
				</div>
			</div>
			<div class="row" id="ccms_reset_div" style="display: none;">
				<div class="col-md-5 col-md-offset-3">
<?php if(isset($ccms_pass_reset_form_message["fail"]) && $ccms_pass_reset_form_message["fail"] != ""): ?>
					<div class="alert alert-danger" style="margin-top: 20px; margin-bottom: 20px;">
						<?php echo $ccms_pass_reset_form_message["fail"]; ?>
					</div>
<?php elseif(isset($ccms_pass_reset_form_message["success"]) && $ccms_pass_reset_form_message["success"] != ""): ?>
					<div class="alert alert-success" style="margin-top: 20px; margin-bottom: 20px;">
						<?php echo $ccms_pass_reset_form_message["success"]; ?>
					</div>
<?php endif ?>
					<div class="panel panel-yellow">
						<div class="panel-heading">Password Reset</div>
						<div class="panel-body">
							<p>Please enter your account email address. We will send you a link to reset your password.</p>
							<form action="/{CCMS_LIB:_default.php;FUNC:ccms_lng}/user/" id="ccms_pass_reset_form" method="post">
								<input type="hidden" name="ccms_pass_reset_form" value="1">
								<div id="reset-status" style="color:#ec7f27; font-weight:bold;"></div>
								<div class="form-group">
									<label for="ccms_pass_reset_form_email">Email Address</label>
									<div class="input-group">
										<div class="input-group-addon"><i class="fa fa-at"></i></div>
										<input class="form-control" id="ccms_pass_reset_form_email" name="ccms_pass_reset_form_email" placeholder="Email" type="email">
										<label id="error-ccms_pass_reset_form_email" class="error" for="ccms_pass_reset_form_email" style="display: none;"></label>
									</div>
								</div>
								<button type="submit" class="btn btn-lg btn-success btn-block">Request Link</button>
							</form>
							<p>NOTE: The link contained in the email will only work once and only within one hour of its request.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
<?php else: ?>
		<div class="container">
			<div class="row">
				<div class="col-md-5 col-md-offset-3">
					<div class="alert alert-success" style="margin-bottom: 0; margin-top: 20px;">
						Use the form below to reset your password.  Remember this link/form will only work one time.  Once you submit this form it will not work again unless you request a new Password Reset link.
					</div>
					<div class="login-panel panel panel-default" style="margin-top: 20px;">
						<div class="panel-heading">
							<h3 class="panel-title">New Password</h3>
						</div>
						<div class="panel-body">

							<form action="/{CCMS_LIB:_default.php;FUNC:ccms_lng}/user/" id="ccms_pass_reset_form_prf" method="post">
								<input type="hidden" name="ccms_pass_reset_form_prf" value="2">
								<input type="hidden" name="ccms_pass_reset_form_code" value="<?php echo $CLEAN["ccms_pass_reset_form_code"]; ?>">

								<div id="update-status" style="color:#ec7f27; font-weight:bold;"></div>

								<div class="form-group">
									<label for="password1">Password</label>
									<div class="input-group">
										<div class="input-group-addon"><i class="fa fa-key"></i></div>
										<input class="form-control" id="password1" name="password1" placeholder="Password" type="password">
									</div>
								</div>
								<div class="form-group">
									<label for="password2">Re-Type Password</label>
									<div class="input-group">
										<div class="input-group-addon"><i class="fa fa-key"></i></div>
										<input class="form-control" id="password2" name="password2" placeholder="Re-Type Password" type="password">
									</div>
								</div>
								<div class="form-group">
									<div class="input-group" id="form-captcha">
										<div class="g-recaptcha" data-sitekey="{CCMS_LIB:_default.php;FUNC:ccms_googleRecapPubKey}"></div>
										<label id="error-grecaptcha" class="error" for="g-recaptcha" style="display: none;"></label>
									</div>
								</div>
								<button type="submit" class="btn btn-lg btn-success btn-block">Update</button>
							</form>

						</div>
					</div>

					<?php echo $ccms_pass_reset_form_prf_message["success"]; ?>

				</div>
			</div>
<?php endif ?>
		<script>
			function loadFirst(e,t){var a=document.createElement("script");a.async = true;a.readyState?a.onreadystatechange=function(){("loaded"==a.readyState||"complete"==a.readyState)&&(a.onreadystatechange=null,t())}:a.onload=function(){t()},a.src=e,document.body.appendChild(a)}

			var cb = function() {
				var l = document.createElement('link'); l.rel = 'stylesheet';
				l.href = "/ccmsusr/_css/bootstrap-3.3.7.min.css";
				var h = document.getElementsByTagName('head')[0]; h.parentNode.insertBefore(l, h);

				var l = document.createElement('link'); l.rel = 'stylesheet';
				l.href = "/ccmsusr/_css/custodiancms.css";
				/*l.href = "/ccmsusr/_css/custodiancms.min.css";*/
				var h = document.getElementsByTagName('head')[0]; h.parentNode.insertBefore(l, h);

				var l = document.createElement('link'); l.rel = 'stylesheet';
				l.href = "/ccmsusr/_css/font-awesome-4.7.0.min.css";
				var h = document.getElementsByTagName('head')[0]; h.parentNode.insertBefore(l, h);
			};

			var raf = requestAnimationFrame || mozRequestAnimationFrame || webkitRequestAnimationFrame || msRequestAnimationFrame;
			if (raf) raf(cb);
			else window.addEventListener('load', cb);

			function loadJSResources() {
				loadFirst("/ccmsusr/_js/jquery-2.2.0.min.js", function() { /* JQuery */
					loadFirst("/ccmsusr/_js/bootstrap-3.3.7.min.js", function() { /* Bootstrap */
						loadFirst("https://www.google.com/recaptcha/api.js?hl={CCMS_LIB:_default.php;FUNC:ccms_lng}", function() { /* Google reCaptcha */
							loadFirst("/ccmsusr/_js/jquery.validate-1.17.0.min.js", function() { /* JQuery Validate */
								/*loadFirst("/ccmsusr/_js/custodiancms.js", function() { /* CustodianCMS JavaScript */
								loadFirst("/ccmsusr/_js/custodiancms.min.js", function() { /* CustodianCMS JavaScript */

									/* Fade in web page. */
									$("#no-fouc").delay(200).animate({"opacity": "1"}, 500);

									$("#loginHelpLink").click(function(event){
										event.preventDefault();

										if($("#ccms_reset_div").is(":visible")) {
											$("#ccms_reset_div").css("display", "none");
										} else {
											$("#ccms_reset_div").css("display", "block");
											$("#ccms_reset_div").scrollView();
										}

									});

									$("#loginForm").validate({
										rules: {
											loginEmail: {
												required: true,
												email: true,
												maxlength: 255
											},
											loginPassword: {
												required: true,
												minlength: 8
											}
										},
										messages: {
											loginEmail: {
												required: "Please enter a valid email address.",
												maxlength: "Please try to keep your email address to 255 characters or less."
											},
											loginPassword: {
												required: "Please enter your password.",
												minlength: "Passwords must be at least 8 characters in length."
											}
										}
									});

									$("#ccms_pass_reset_form").validate({
										rules: {
											ccms_pass_reset_form_email: {
												required: true,
												email: true,
												maxlength: 255
											}
										},
										messages: {
											ccms_pass_reset_form_email: {
												required: "Please enter a valid email address.",
												maxlength: "Please try to keep your email address to 255 characters or less."
											}
										}
									});

									$("#ccms_pass_reset_form_prf").validate({
										rules: {
											password1: {
												required: true,
												minlength: 8
											},
											password2: {
												required: true,
												minlength: 8,
												equalTo: "#password1"
											}
										},
										messages: {
											password1: {
												required: "Please enter your password.",
												minlength: "Passwords must be at least 8 characters in length."
											},
											password2: {
												required: "Please enter your password.",
												minlength: "Passwords must be at least 8 characters in length.",
												equalTo: "Please enter the same password as above."
											}
										}
									});
<?php if(isset($ccms_pass_reset_form_message) && ($ccms_pass_reset_form_message != "")): ?>
									$('#ccms_reset_div').css('display', 'block');
									$('#ccms_reset_div').scrollView();
<?php endif ?>
								});
							});
						});
					});
				});
			}

			if(window.addEventListener)
				window.addEventListener("load", loadJSResources, false);
			else if(window.attachEvent)
				window.attachEvent("onload", loadJSResources);
			else window.onload = loadJSResources;
		</script>
	</body>
</html>
