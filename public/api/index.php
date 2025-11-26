<?php
declare(strict_types=1);

use App\Auth\TokenService;
use App\Config\Config;
use App\Controllers\AuthController;
use App\Controllers\CandidateController;
use App\Controllers\AdminUserController;
use App\Controllers\RankingController;
use App\Controllers\VoteController;
use App\Http\Cors;
use App\Http\Response;
use App\Http\Router;
use App\Http\SecurityHeaders;

$app = require __DIR__ . '/../../bootstrap.php';

/** @var Config $config */
$config = $app['config'];
$pdo = $app['pdo'];

Cors::apply($config);
SecurityHeaders::apply($config);
header('Cache-Control: no-store, no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    Response::empty();
    return;
}

$tokens = new TokenService($pdo, $config);
$authController = new AuthController($pdo, $tokens, $config);
$candidateController = new CandidateController($pdo);
$adminUserController = new AdminUserController($pdo, $config);
$voteController = new VoteController($pdo);
$rankingController = new RankingController($pdo, $config);

$router = new Router();

$router->add('POST', '/login', fn() => $authController->login());
$router->add('POST', '/logout', fn() => $authController->logout());
$router->add('GET', '/me', function () use ($tokens, $authController) {
    $user = $tokens->requireUser();
    $authController->me($user);
});
$router->add('GET', '/candidates', function () use ($tokens, $candidateController) {
    $tokens->requireUser();
    $candidateController->list();
});
$router->add('GET', '/admin/users', function () use ($tokens, $adminUserController) {
    $user = $tokens->requireUser();
    $adminUserController->list($user);
});
$router->add('POST', '/admin/users', function () use ($tokens, $adminUserController) {
    $user = $tokens->requireUser();
    $adminUserController->create($user);
});
$router->add('POST', '/admin/users/delete', function () use ($tokens, $adminUserController) {
    $user = $tokens->requireUser();
    $adminUserController->delete($user);
});
$router->add('POST', '/vote', function () use ($tokens, $voteController) {
    $user = $tokens->requireUser();
    $voteController->vote($user);
});
$router->add('GET', '/ranking', function () use ($tokens, $rankingController) {
    $user = $tokens->requireUser();
    $rankingController->ranking($user);
});

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($base && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
}

$router->dispatch($_SERVER['REQUEST_METHOD'], $uri);
