<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);


require 'vendor/autoload.php';
require 'Models/User.php';
require 'Models/Imagen.php';
require 'Models/Noticia.php';
require 'Models/Post.php';
require 'Models/comment.php';

function simple_encrypt($text,$salt){  
   return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
}
 
function simple_decrypt($text,$salt){  
    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
}



$app = new \Slim\Slim();

$app->enc_key ='1234567891234567';
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
$user = (object) $db->table('usuarios')->select()->where('email', $email)->first();
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
	$usuarios = $db->table('usuarios')->select()->get();
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
	$city = $input['city'];
	if(empty($city)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'city is required',
        ));
	}

$country = $input['country'];
	if(empty($country)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'country is required',
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
    $user->city = $city;
    $user->country = $country;

    $user->save();
    $app->render(200,array('data' => $user->toArray()));
});
$app->get('/usuarios/:id', function ($id) use ($app) {
	$db = $app->db->getConnection();
	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'user not found',
        ));
	}
	unset($user->password);
	unset($user->email);

	$user->posts = $db->table('posts')->select('title')->where('id_usuario', $user->id)->get();


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


$app->get('/noticias', function () use ($app) {
	$db = $app->db->getConnection();
	$images = $db->table('noticias')->select()->orderby('created_at','desc')->get();
	$app->render(200,array('data' => $images));
});

$app->post('/noticias', function () use ($app) {
	$input = $app->request->getBody();

	$Titulo = $input['Titulo'];

 	if(empty($Titulo)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Titulo is required',
        ));
	}
	$Descripcion = $input['Descripcion'];
	if(empty($Descripcion)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Descripcion is required',
        ));
	}
		
    $noticia = new Noticia();
    $noticia->Titulo = $Titulo;
    $noticia->Descripcion = $Descripcion;
 
     
    $noticia->save();
    $app->render(200,array('data' => $noticia->toArray()));
});


$app->put('/noticias/:id', function ($id) use ($app) {
	$input = $app->request->getBody();
	
	$Titulo = $input['Titulo'];
	if(empty($Titulo)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Titulo is required',
        ));
	}
	$Descripcion = $input['Descripcion'];
	if(empty($Descripcion)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Descripcion is required',
        ));
	}

	$noticia = Noticia::find($id);
	if(empty($noticia)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'noticia not found',
        ));
	}
    $noticia->Titulo = $Titulo;
    $noticia->Descripcion = $Descripcion;
    $noticia->save();
    $app->render(200,array('data' => $noticia->toArray()));
});
$app->get('/noticias/:id', function ($id) use ($app) {
	$noticia = Noticia::find($id);
	if(empty($noticia)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'noticia not found',
        ));
	}
	$app->render(200,array('data' => $noticia->toArray()));
});
$app->delete('/noticias/:id', function ($id) use ($app) {
	$noticia = Noticia::find($id);
	if(empty($noticia)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'noticia not found',
        ));
	}
	$noticia->delete();
	$app->render(200);
});








$app->get('/imagenes', function () use ($app) {
	$db = $app->db->getConnection();
	$imagenes = $db->table('imagenes')->select()->orderby('created_at','desc')->get();
	$app->render(200,array('data' => $imagenes));
});

$app->post('/imagenes', function () use ($app) {
	$input = $app->request->getBody();

	$Titulo = $input['Titulo'];

 	if(empty($Titulo)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Titulo is required',
        ));
	}
	$Descripcion = $input['Descripcion'];
	if(empty($Descripcion)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Descripcion is required',
        ));
	}
		
    $imagen = new Image();
    $imagen->Titulo = $Titulo;
    $imagen->Descripcion = $Descripcion;
 
     
    $imagen->save();
    $app->render(200,array('data' => $imagen->toArray()));
});


$app->put('/imagenes/:id', function ($id) use ($app) {
	$input = $app->request->getBody();
	
	$Titulo = $input['Titulo'];
	if(empty($Titulo)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Titulo is required',
        ));
	}
	$Descripcion = $input['Descripcion'];
	if(empty($Descripcion)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Descripcion is required',
        ));
	}

	$imagen = Image::find($id);
	if(empty($imagen)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'imagen not found',
        ));
	}
    $imagen->Titulo = $Titulo;
    $imagen->Descripcion = $Descripcion;
    $imagen->save();
    $app->render(200,array('data' => $imagen->toArray()));
});
$app->get('/imagenes/:id', function ($id) use ($app) {
	$imagen = Image::find($id);
	if(empty($imagen)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'imagen not found',
        ));
	}
	$app->render(200,array('data' => $imagen->toArray()));
});
$app->delete('/imagenes/:id', function ($id) use ($app) {
	$imagen = Image::find($id);
	if(empty($imagen)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'imagen not found',
        ));
	}
	$imagen->delete();
	$app->render(200);
});










$app->get('/post/:id', function ($id) use ($app) {
	$db = $app->db->getConnection();
	$post = Post::find($id);
	if(empty($post)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'post not found',
        ));
	}

	/*
	$post->user = User::find($post->id_usuario);
	*/

	$post->user = $db->table('usuarios')->select('id','name', 'email')->where('id', $post->id_usuario)->get();

	unset($post->id_usuario);
	
	$app->render(200,array('data' => $post->toArray()));
});

$app->post('/post', function () use ($app) {
	$token = $app->request->headers->get('auth-token');

	if(empty($token)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}

	$id_user_token = simple_decrypt($token, $app->enc_key);

	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}

	$input = $app->request->getBody();
	
	$title = $input['title'];
	if(empty($title)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'title is required',
        ));
	}
	
	$post = new Post();
	$post->title = $title;
    $post->id_usuario = $user->id;
    $post->save();
    $app->render(200,array('data' => $post->toArray()));
});

$app->post('/post/:id/comment', function ($id) use ($app) {
	$token = $app->request->headers->get('auth-token');

	if(empty($token)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}

	$id_user_token = simple_decrypt($token, $app->enc_key);

	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}

	$db = $app->db->getConnection();
	$post = Post::find($id);
	if(empty($post)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'post not found',
        ));
	}

	$input = $app->request->getBody();
	$text = $input['text'];
	if(empty($text)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'text is required',
        ));
	}

	$comment = new Comment();
	$comment->text = $text;
	$comment->id_usuario = $user->id;
	$comment->id_post = $post->id;
	$comment->save();
	
	$app->render(200,array('data' => $comment->toArray()));
});

$app->post('/post/:id/multicomment', function ($id) use ($app) {
	$token = $app->request->headers->get('auth-token');

	if(empty($token)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}

	$id_user_token = simple_decrypt($token, $app->enc_key);

	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}

	$db = $app->db->getConnection();
	$post = Post::find($id);
	if(empty($post)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'post not found',
        ));
	}

	$input = $app->request->getBody();
	$text = $input['text'];
	if(empty($text)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'text is required',
        ));
	}

	$text_array = explode(',', $text);

	$created = array();

	foreach ($text_array as $key => $text) {
		$comment = new Comment();
		$comment->text = $text;
		$comment->id_usuario = $user->id;
		$comment->id_post = $post->id;
		$comment->save();
		$created[] = $comment->toArray();
	}

	$app->render(200,array('data' => $created));
});

$app->get('/profile', function () use ($app) {
	$token = $app->request->headers->get('auth-token');

	if(empty($token)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}

	$id_user_token = simple_decrypt($token, $app->enc_key);

	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}

	$db = $app->db->getConnection();
	$posts = $db->table('posts')->select()->where('id_usuario', $user->id)->get();

	foreach ($posts as $key => $post) {
		$comments = $db->table('comments')->select()->where('id_post', $post->id)->get();
		foreach ($comments as $keyc => $comment) {
			$comments[$keyc]->user = User::find($comment->id_usuario);
		}
		$posts[$key]->comments = $comments;
	}
	
	$app->render(200,array('data' => $posts));
});

$app->post('/findcomments', function () use ($app) {
	$input = $app->request->getBody();
	$text = $input['text'];
	if(empty($text)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'text is required',
        ));
	}


	$db = $app->db->getConnection();
	$query = $db->table('comments')->select()->where('text', 'like', '%'.$text.'%');

	if(isset($input['user'])){
		$user = $input['user'];
		if(!empty($user)){
			$query = $query->where('id_usuario', $user);
		}
	}	

	$comments = $query->get();

	$app->render(200,array('data' => $comments));
});

$app->run();
?>
