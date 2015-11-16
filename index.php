<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);


require 'vendor/autoload.php';
require 'Models/User.php';
require 'Models/image.php';

function simple_encrypt($text,$salt){  
   return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
}
 
function simple_decrypt($text,$salt){  
    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
}

$enc_key = '1234567891234567';

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

	$token = simple_encrypt($user->id, $app->enc_key);
	$app->render(200,array('token' => $token));
});

$app->get('/logout', function() use($app) {
 	$token="";
});


$app->get('/me', function () use ($app) {

	
	$token = $app->request->headers->get('auth-token');
	if(empty($token)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged1',
        ));
	}
	
	$id_user_token = simple_decrypt($token, $app->enc_key);
	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged2',
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


$app->get('/imagenes', function () use ($app) {
	$db = $app->db->getConnection();
	$images = $db->table('imagenes')->select('Id', 'IdUsuario')->get();
	$app->render(200,array('data' => $images));
});


$app->get('/imagenes/:Id', function ($Id) use ($app) {
	$image=Image::find($Id);
	if(empty($image)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'Anuncio not found',
        ));
	}
	$app->render(200,array('data' => $image->toArray()));
});




$app->run();
?>
