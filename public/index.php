<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];

$app->get('/', function ($request, $response) {
    return $response->write('Welcome to Slim!');
});

$app->get('/users', function ($request, $response, array $args) use ($users) {
    $term = $request->getQueryParam('term', '');
    $params = [
        'users' => array_filter($users, fn ($userName) => str_contains($userName, $term)),
        'term' => $term
    ];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
});

$app->post('/users', function ($request, $response) {
    return $response->withStatus(302);
});

$app->get('/users/{id}', function ($request, $response, $args) {
    $params = ['id' => $args['id'], 'nickname' => 'user-' . $args['id']];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$app->get('/courses/{id}', function ($request, $response, array $args) {
    $id = $args['id'];
    return $response->write("Course id: {$id}");
});

$app->run();