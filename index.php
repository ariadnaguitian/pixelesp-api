<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);


require 'vendor/autoload.php';
require 'Models/User.php';

session_start();

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

$app->options('/(:name+)', function() use ($app) {
    $app->render(200,array('msg' => 'pixelesp'));
});

$app->get('/', function () use ($app) {
	$app->render(200,array('msg' => 'pixelesp'));
});

$app->post('/login', function () use ($app) {
	$input = $app->request->getBody();

	$email = $input['email'];
	if(empty($email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'email is required',
        ));
	}
	$password = $input['password'];
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password is required',
        ));
	}

	$db = $app->db->getConnection();
	$user = $db->table('usuarios')->select()->where('email', $email)->first();

	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'user not exist',
        ));
	}

	if($user->password != $password){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password dont match',
        ));
	}

	$_SESSION["user"] = $user->id;

	$app->render(200,array());
});

$app->get('/me', function () use ($app) {

	if(empty($_SESSION["user"])){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}
	$user = User::find($_SESSION["user"]);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}
	$app->render(200,array('data' => $user->toArray()));
});







$app->get('/usuarios', function () use ($app) {
	$db = $app->db->getConnection();
	$usuarios = $db->table('usuarios')->select('id', 'name')->get();
	$app->render(200,array('data' => $usuarios));
});



$app->post('/usuarios', function () use ($app) {
	$input = $app->request->getBody();

	$name = $input['name'];

 	if(empty($name)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'name is required',
        ));
	}
	$password = $input['password'];
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password is required',
        ));
	}
	$email = $input['email'];
	if(empty($email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'email is required',
        ));
	}
	
    $user = new User();
    $user->name = $name;
    $user->password = $password;
   $user->email = $email;
     
    $user->save();
    $app->render(200,array('data' => $user->toArray()));
});
$app->put('/usuarios/:id', function ($id) use ($app) {
	$input = $app->request->getBody();
	
	$name = $input['name'];
	if(empty($name)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'name is required',
        ));
	}
	$password = $input['password'];
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password is required',
        ));
	}
$email = $input['email'];
	if(empty($email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'email is required',
        ));
	}
	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'user not found',
        ));
	}
    $user->name = $name;
    $user->password = $password;
    $user->email = $email;
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
