<?php

namespace PHPMailer\PHPMailer {
	if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
		class PHPMailer {
			public const ENCRYPTION_STARTTLS = 'tls';
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

			public function __construct($exceptions = null) {}
			public function isSMTP() {}
			public function setFrom($address, $name = '') {}
			public function addAddress($address, $name = '') {}
			public function addReplyTo($address, $name = '') {}
			public function isHTML($isHtml = true) {}
			public function send() { return true; }
		}
	}

	if (!class_exists('PHPMailer\PHPMailer\SMTP')) {
		class SMTP {}
	}

	if (!class_exists('PHPMailer\PHPMailer\Exception')) {
		class Exception extends \Exception {}
	}
}
?>

