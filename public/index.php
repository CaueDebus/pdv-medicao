<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\PageController;
use App\Core\Session;

$page = (string) ($_GET['page'] ?? 'dashboard');
$action = (string) ($_GET['action'] ?? '');
$session = Session::instance();

$auth = new AuthController();

if ($action === 'logout') {
	$auth->logout();
	header('Location: ?page=login');
	exit;
}

if ($page === 'login') {
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$error = $auth->login($_POST);
		if ($error === null) {
			header('Location: ?page=dashboard');
			exit;
		}

		echo $auth->showLogin($error);
		exit;
	}

	if ($session->isAuthenticated()) {
		header('Location: ?page=dashboard');
		exit;
	}

	echo $auth->showLogin();
	exit;
}

if (! $session->isAuthenticated()) {
	header('Location: ?page=login');
	exit;
}

$controller = new PageController();
echo $controller->render($page, $session->user());
