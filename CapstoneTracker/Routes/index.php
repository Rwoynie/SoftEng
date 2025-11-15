<?php


require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../core/App.php';
require_once __DIR__ . '/../Database/config.php';


spl_autoload_register(function ($className) {
	$controllerPath = __DIR__ . '/../app/Controllers/' . $className . '.php';
	if (file_exists($controllerPath)) {
		require_once $controllerPath;
	}
});


if (session_status() === PHP_SESSION_NONE) {
	session_start();
}


$app = new App();

?>