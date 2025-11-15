<?php

namespace PHPMailer\PHPMailer {
	if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
		class PHPMailer {
			public const ENCRYPTION_STARTTLS = 'tls';
			public const ENCRYPTION_SMTPS = 'ssl'; // ADD THIS MISSING CONSTANT
			public $Host;
			public $SMTPAuth;
			public $Username;
			public $Password;
			public $SMTPSecure;
			public $Port;
			public $Subject;
			public $Body;
			public $AltBody;
			public $ErrorInfo;
			public $Timeout;
			public $SMTPKeepAlive;
			public $CharSet;
			public $SMTPOptions;

			public function __construct($exceptions = null) {}
			public function isSMTP() {}
			public function setFrom($address, $name = '') {}
			public function addAddress($address, $name = '') {}
			public function addReplyTo($address, $name = '') {}
			public function isHTML($isHtml = true) {}
			public function send() { 
				// For testing, you might want to log that send was called
				error_log("PHPMailer stub: send() method called");
				return true; 
			}
		}
	}

	if (!class_exists('PHPMailer\PHPMailer\SMTP')) {
		class SMTP {
			public const DEBUG_SERVER = 2; // ADD THIS CONSTANT
		}
	}

	if (!class_exists('PHPMailer\PHPMailer\Exception')) {
		class Exception extends \Exception {}
	}
}
?>