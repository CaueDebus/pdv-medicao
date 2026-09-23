<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\CrudController;
use App\Controllers\PageController;
use App\Core\Session;

$page = (string) ($_GET['page'] ?? 'dashboard');
$action = (string) ($_GET['action'] ?? '');
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
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

$user = $session->user();
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

if (in_array($action, ['create', 'edit', 'store', 'update', 'destroy'], true)) {
	$crud = new CrudController();

	if ($action === 'create') {
		echo $crud->form($page, $user);
		exit;
	}

	if ($action === 'edit' && $id !== null) {
		echo $crud->form($page, $user, $id);
		exit;
	}

	if ($isPost && $id === null && in_array($action, ['update', 'destroy'], true)) {
		header('Location: ?page=' . urlencode($page));
		exit;
	}

	if ($isPost) {
		$redirect = match ($action) {
			'store' => $crud->save($page, $user, $_POST),
			'update' => $crud->save($page, $user, $_POST, $id),
			'destroy' => $crud->destroy($page, $user, $_POST, (int) $id),
			default => '?page=' . urlencode($page),
		};

		header('Location: ' . $redirect);
		exit;
	}

	header('Location: ?page=' . urlencode($page));
	exit;
}

$controller = new PageController();
echo $controller->render($page, $user);
