<?
/**
 * This is a Chipla frontcontroller.
 *
 */

// Get environment & autoloader.
require __DIR__.'/config_with_app.php'; 

// Set grid theme
$app->theme->configure(ANAX_APP_PATH . 'config/theme.php');

// Set url cleaner url links
$app->url->setUrlType(\Anax\Url\CUrl::URL_CLEAN);

// Set me navbar
$app->navbar->configure(ANAX_APP_PATH . 'config/navbar.php');

// Include database support
$di->setShared('db', function() {
    $db = new \Mos\Database\CDatabaseBasic();
    $db->setOptions(require "config_sqlite.php");
    $db->connect();
    return $db;
});

$di->set('ContentController', function() use ($di) {
    $controller = new Chp\TextContent\ContentController();
    $controller->setDI($di);
    return $controller;
});

$di->set('BlogController', function() use ($di) {
    $controller = new Chp\TextContent\BlogController();
    $controller->setDI($di);
    return $controller;
});

$di->set('PageController', function() use ($di) {
    $controller = new Chp\TextContent\PageController();
    $controller->setDI($di);
    return $controller;
});

$app = new \Anax\Kernel\CAnax($di);
$app->theme->addStylesheet('css/text-content.css');

// Create services and inject into the app. 
$di  = new \Anax\DI\CDIFactoryDefault();

// Route to all other
$app->router->add('', function() use ($app) {
  $app->theme->setTitle("Content-test");
  $app->views->add('text-content/menu');
});

// Check for matching routes and dispatch to controller/handler of route
$app->router->handle();

// Render the page
$app->theme->render();
?>