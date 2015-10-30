<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

mysql://bbe7913f482805:05885e55@/

require 'vendor/autoload.php';
require 'Models/User.php';
$app = new \Slim\Slim();
$app->config('databases', [
    'default' => [
        'driver'    => 'mysql',
        'host'      => 'us-cdbr-iron-east-03.cleardb.net',
        'database'  => 'heroku_3953fb4ef720f6e',
        'username'  => 'bbe7913f482805',
        'password'  => '05885e55',
        'charset'   => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'    => ''
    ]
    ]);

$app->add(new Zeuxisoo\Laravel\Database\Eloquent\ModelMiddleware);
$app->view(new \JsonApiView());
$app->add(new \JsonApiMiddleware());
$app->add(new \Slim\Middleware\ContentTypes());

$app->options('/(:id+)', function() use ($app) {
    $app->render(200,array('msg' => 'pixelesp'));
});

$app->get('/', function () use ($app) {
	$app->render(200,array('msg' => 'pixelesp'));
});


$app->get('/usuarios', function () use ($app) {
	$db = $app->db->getConnection();
	$usuarios = $db->table('usuarios')->select('id', 'Nombre')->get();
	$app->render(200,array('data' => $usuarios));
});



$app->post('/usuarios', function () use ($app) {
	$input = $app->request->getBody();
	$Nombre = $input['Nombre'];
 	if(empty($Nombre)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Nombre is required',
        ));
	}
	$Contrasena = $input['Contrasena'];
	if(empty($Contrasena)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Contrasena is required',
        ));
	}
	$Email = $input['Email'];
	if(empty($Email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Email is required',
        ));
	}
    $user = new User();
    $user->Nombre = $Nombre;
    $user->Contrasena = $Contrasena;
   $user->Email = $Email;
     
    $user->save();
    $app->render(200,array('data' => $user->toArray()));
});
$app->put('/usuarios/:id', function ($id) use ($app) {
	$input = $app->request->getBody();
	
	$Nombre = $input['Nombre'];
	if(empty($Nombre)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Nombre is required',
        ));
	}
	$Contrasena = $input['Contrasena'];
	if(empty($Contrasena)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Contrasena is required',
        ));
	}
$Email = $input['Email'];
	if(empty($Email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Email is required',
        ));
	}
	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'user not found',
        ));
	}
    $user->Nombre = $Nombre;
    $user->Contrasena = $Contrasena;
    $user->Email = $Email;
    $user->save();
    $app->render(200,array('data' => $user->toArray()));
});
$app->get('/usuarios/:id', function ($id) use ($app) {
	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'user not found',
        ));
	}
	$app->render(200,array('data' => $user->toArray()));
});
$app->delete('/usuarios/:id', function ($id) use ($app) {
	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'user not found',
        ));
	}
	$user->delete();
	$app->render(200);
});
$app->run();
?>
