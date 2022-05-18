<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use App\Validator;

session_start();

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function ($request, $response) {
    return $this->get('renderer')->render($response, 'index.phtml');
})->setName('root');

$app->get('/users', function ($request, $response, array $args){
    $messages = $this->get('flash')->getMessages();
    $term = $request->getQueryParam('term', '');
    $users = json_decode(file_get_contents('./public/user.json'), true) ?? [];
    $params = [
        'users' => array_filter($users, fn ($user) => str_contains($user['name'], $term)),
        'term' => $term,
        'flash' => $messages
    ];

    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
})->setName('users');

$app->post('/users', function ($request, $response) use ($router) {
    $validator = new Validator();
    $user = $request->getParsedBodyParam('user');
    $errors = $validator->validate($user);
    if (count($errors) === 0) {
        $currData = file_get_contents('./public/user.json');
        $decodedData = json_decode($currData, true);
        $user['id'] = $decodedData ? count($decodedData) : 0;
        $decodedData[] = $user;
        $encodedData = json_encode($decodedData, JSON_PRETTY_PRINT);
        file_put_contents('./public/user.json', $encodedData);
        $this->get('flash')->addMessage('success', 'User was added');

        return $response->withRedirect($router->urlFor('users'), 302);
    }
    $params = [
        'user' => $user,
        'errors' => $errors
    ];  
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
})->setName('addUser');

$app->get('/users/show/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $users = json_decode(file_get_contents('./public/user.json'), true);
    $params = [
        'user' => $users[$id]
    ];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('user');

$app->get('/users/new', function ($request, $response, $args) {
    $params = [
        'user' => ['name' => '', 'email' => ''],
        'errors' => []
    ];
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
})->setName('newUser');

$app->run();