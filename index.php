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
require 'Models/newscomment.php';
require 'Models/trabajo.php';



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

	$username = $input['username'];
	if(empty($username)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'username is required',
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
	$user =$db->table('usuarios')->select()->where('username', $username)->first();
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


$app->post('/loginadmin', function () use ($app) {
	$input = $app->request->getBody();

	$username = $input['username'];
	if(empty($username)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'username is required',
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
	$user =$db->table('usuarios')->select()->where('username', $username)->where('userlevel', '1')->first();
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

      if($user->userlevel != 1){
        $app->render(500,array(
            'error' => TRUE,
            'msg'   => 'Acceso denegado',
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


	$username = $input['username'];

 	if(empty($username)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'username is required',
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
     $user->username = $username;
   $user->email = $email;

     if($user->email == $email){
        $app->render(500,array(
            'error' => TRUE,
            'msg'   => 'email ya registrado',
        ));
    } 
     if($user->username == $username){
        $app->render(500,array(
            'error' => TRUE,
            'msg'   => 'username ya registrado',
        ));
    } 
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

	$username = $input['username'];
	if(empty($username)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'username is required',
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
        $user->username = $username;
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
	unset($user->username);
 
 	$user->imagenes = $db->table('imagenes')->select('Titulo')->where('IdUsuario', $user->id)->get();
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


$app->get('/post/:id', function ($id) use ($app) {
 	$db = $app->db->getConnection();
 	$post = Image::find($id);
 	if(empty($post)){
 		$app->render(404,array(
 			'error' => TRUE,
             'msg'   => 'post not found',
         ));
 	}
 
 	/*
 	$post->user = User::find($post->id_usuario);
 	*/
 
 	$post->user = $db->table('usuarios')->select('id','name', 'email','username')->where('id', $post->id_usuario)->get();
 
 	unset($post->id_usuario);
 	
 	$app->render(200,array('data' => $post->toArray()));
 });
 

$app->get('/noticias', function () use ($app) {
	$db = $app->db->getConnection();
	$images = $db->table('noticias')->select('noticias.*','usuarios.name')
	->leftjoin('usuarios', 'usuarios.id', '=', 'noticias.idusuario')
	->orderby('created_at','desc')
	->get();
	foreach ($images as $key => $value) {
		$newscomment = NewsComments::where('id_noticia', '=', $value->id)
		->select('newscomments.*','usuarios.name')
		->leftjoin('usuarios', 'usuarios.id', '=', 'newscomments.idusuario')
		->get();
		if(empty($newscomment)){
			$result = array();
		} else{
			$result = $newscomment->toArray(); 
		}
		$images[$key]->comentarios = $result;
	}
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
      $imagen->IdUsuario = $user->id;
 
     
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


//comentarios:

$app->get('/comments-by-post/:id', function ($id) use ($app) {	
	$db = $app->db->getConnection();
	$comments = $db->table('comments')->select()->where('id_imagen', $id)->orderby('created_at','desc')->get();
	$app->render(200,array('data' => $comments));	
});


$app->get('/comments', function () use ($app) {
	$db = $app->db->getConnection();
	$comments = $db->table('comments')->select()->orderby('created_at','desc')->get();
	$app->render(200,array('data' => $comments));
});



$app->put('/comments/:id', function ($id) use ($app) {
	$input = $app->request->getBody();
	
	$text = $input['text'];
	if(empty($text)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'text is required',
        ));
	}

	$comment = Comment::find($id);
	if(empty($comment)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'comment not found',
        ));
	}
    $comment->text = $text;
    $comment->save();
    $app->render(200,array('data' => $comment->toArray()));
});
$app->get('/comments/:id', function ($id) use ($app) {
	$comment = Comment::find($id);
	if(empty($comment)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'comment not found',
        ));
	}
	$app->render(200,array('data' => $comment->toArray()));
});
$app->delete('/comments/:id', function ($id) use ($app) {
	$comment = Comment::find($id);
	if(empty($comment)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'comment not found',
        ));
	}
	$comment->delete();
	$app->render(200);
});

//comentarios de noticias:

$app->get('/newscomments', function () use ($app) {
	$db = $app->db->getConnection();
	$newscomments = $db->table('newscomments')->select()->orderby('created_at','desc')->get();
	$app->render(200,array('data' => $newscomments));
});

$app->post('/newscomments', function () use ($app) {
	$input = $app->request->getBody();

	$text = $input['text'];
	$idusuario = $input['idusuario'];
	$id_noticia = $input['id_noticia'];
	
	if(empty($text)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'text is required',
        ));
	}	
	
	if(empty($idusuario)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'idusuario is required',
        ));
	}
	
	if(empty($id_noticia)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'id_noticia is required',
        ));
	}


		
    $newscomment = new NewsComments();
    $newscomment->idusuario  = $idusuario;
    $newscomment->id_noticia = $id_noticia;
    $newscomment->text 		 = $text;
 
     
    $newscomment->save();
    $app->render(200,array('data' => $newscomment->toArray()));
});

$app->put('/newscomments/:id', function ($id) use ($app) {
	$input = $app->request->getBody();
	
	$text = $input['text'];
	if(empty($text)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'text is required',
        ));
	}

	$newscomment = NewsComments::find($id);
	if(empty($newscomment)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'newscomment not found',
        ));
	}
    $newscomment->text = $text;
    $newscomment->save();
    $app->render(200,array('data' => $newscomment->toArray()));
});
$app->get('/newscomments/:id', function ($id) use ($app) {
	$newscomment = NewsComments::find($id);
	if(empty($newscomment)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'newscomment not found',
        ));
	}
	$app->render(200,array('data' => $newscomment->toArray()));
});
$app->delete('/newscomments/:id', function ($id) use ($app) {
	$newscomment = NewsComments::find($id);
	if(empty($newscomment)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'comment not found',
        ));
	}
	$comment->delete();
	$app->render(200);
});
//trabajos:

$app->get('/trabajos', function () use ($app) {
	$db = $app->db->getConnection();
	$trabajos = $db->table('trabajos')->select()->orderby('created_at','desc')->get();
	$app->render(200,array('data' => $trabajos));
});

$app->post('/trabajos', function () use ($app) {
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
		
    $trabajo = new Trabajo();
    $trabajo->Titulo = $Titulo;
    $trabajo->Descripcion = $Descripcion;
 
     
    $trabajo->save();
    $app->render(200,array('data' => $trabajo->toArray()));
});


$app->put('/trabajos/:id', function ($id) use ($app) {
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

	$trabajo = Trabajo::find($id);
	if(empty($trabajo)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'trabajo not found',
        ));
	}
    $trabajo->Titulo = $Titulo;
    $trabajo->Descripcion = $Descripcion;
    $trabajo->save();
    $app->render(200,array('data' => $trabajo->toArray()));
});
$app->get('/trabajos/:id', function ($id) use ($app) {
	$trabajo = Trabajo::find($id);
	if(empty($trabajo)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'trabajo not found',
        ));
	}
	$app->render(200,array('data' => $trabajo->toArray()));
});
$app->delete('/trabajos/:id', function ($id) use ($app) {
	$trabajo = Trabajo::find($id);
	if(empty($trabajo)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'trabajo not found',
        ));
	}
	$trabajo->delete();
	$app->render(200);
});



//imagenes:


$app->get('/imagenes/:id', function ($id) use ($app) {
	$db = $app->db->getConnection();
	$imagen = Image::find($id);
	if(empty($imagenes)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'imagen not found',
        ));
	}

	/*
	$post->user = User::find($post->id_usuario);
	*/

	$imagen->user = $db->table('usuarios')->select('id','name', 'email','username')->where('id', $imagen->IdUsuario)->get();

	unset($imagen->id_usuario);
	
	$app->render(200,array('data' => $imagen->toArray()));
});

$app->post('/imagen', function () use ($app) {
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
	
	$imagen = new Image();
	$imagen->title = $title;
    $imagen->IdUsuario = $user->id;
    $imagen->save();
    $app->render(200,array('data' => $imagen->toArray()));
});

$app->post('/imagenes/:id/comment', function ($id) use ($app) {
	$token = $app->request->headers->get('auth-token');

	if(empty($token)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}
$app->get('/imagenes/:id/comment', function () use ($app) {
	$db = $app->db->getConnection();
	$comments = $db->table('comments')->select()->orderby('created_at','desc')->get();
	$app->render(200,array('data' =>$comments));


});
	$id_user_token = simple_decrypt($token, $app->enc_key);

	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}

	$db = $app->db->getConnection();
	$imagen = Image::find($id);
	if(empty($imagen)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'imagen not found',
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
	$comment->id_imagen= $imagen->id;
	$comment->save();
	
	$app->render(200,array('data' => $comment->toArray()));
});

$app->post('/imagenes/:id/multicomment', function ($id) use ($app) {
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
	$iamgen = Image::find($id);
	if(empty($iamgen)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'imagen not found',
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
		$comment->id_imagen= $imagen->id;
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
	$imagenes = $db->table('imagenes')->select()->where('IdUsuario', $user->id)->get();

	foreach ($imagenes as $key => $imagen) {
		$comments = $db->table('comments')->select()->where('id_imagen', $imagen->id)->get();
		foreach ($comments as $keyc => $comment) {
			$comments[$keyc]->user = User::find($comment->id_usuario);
		}
		$imagenes[$key]->comments = $comments;
	}
	
	$app->render(200,array('data' => $imagenes));
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









//Conexion con la tabla favoritos
$app->get('/fav', function () use ($app) {
	$db = $app->db->getConnection();
	$fav = $db->table('favoritos')->select('id', 'id_imagen', 'id_usuario')->get();
	$app->render(200,array('data' => $fav));
});
// agregar favoritos
$app->post('/favoritos', function () use ($app) {
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
  
  $id_imagen = $input['id_imagen'];
	if(empty($id_imagen)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Id anuncio is required',
        ));
	}
	
	$favorito = new Favorito();
    $favorito->id_imagen = $id_imagen;
    $favorito->id_usuario = $user->id;
    $favorito->save();
    $app->render(200,array('data' => $favorito->toArray()));
});
// Traer favorito especifico para borrar
$app->get('/misfavoritos', function () use ($app) {
  $token = $app->request->headers->get('auth-token');
	if(empty($token)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged 12',
        ));
	}
	$id_user_token = simple_decrypt($token, $app->enc_key);
	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged 15',
        ));
	}
	
	$input = $app->request->getBody();
  
	  $idanuncio = $input['id_imagen'];
		if(empty($id_imagen)){
			$app->render(500,array(
				'error' => TRUE,
				'msg'   => 'Id imagen is required',
			));
		}
	
	$db = $app->db->getConnection();
	
	$favoritos = $db->table('favoritos')->select('id', 'id_usuario', 'id_imagen')->where('id_usuario', $user->id)->where('id_imagen', $id_imagen)->get();
	
	$app->render(200,array('data' => $favoritos));
});
// ver favorito y borrar 
$app->delete('/delfavoritos', function () use ($app) {
  $token = $app->request->headers->get('auth-token');
	if(empty($token)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged 13',
        ));
	}
	$id_user_token = simple_decrypt($token, $app->enc_key);
	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged 15',
        ));
	}
	
	
	$input = $app->request->getBody();
  
	  $id_imagen = $input['id_imagen'];
		if(empty($id_imagen)){
			$app->render(500,array(
				'error' => TRUE,
				'msg'   => 'Id imagen is required',
			));
		}
	
	$db = $app->db->getConnection();
	
	$favoritos = $db->table('favoritos')->select('id', 'id_usuario', 'id_imagen')->where('id_usuario', $user->id)->where('id_imagen', $id_imagen)->get();
	
	$idfav = $favoritos->id;
	
	$favorito = Favorito::find($idfav);
	if(empty($favorito)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'favorito not found 4',
        ));
	}
	$favorito->delete();
	$app->render(200);
		
});
// listar mis favoritos
$app->get('/misfavoritoslist', function () use ($app) {
	
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
	
	$favoritos = $db->table('favoritos')->select('id', 'id_usuario', 'id_imagen')->where('id_usuario', $user->id)->get();
	foreach ($favoritos as $key => $favoritos) {
		$imagenes = $db->table('imagenes')->select('id', 'id_usuario', 'titulo', 'precio', 'descripcion', 'barrio')->where('id', $favoritos->id_imagen)->get();
		
		$favoritos[$key]->imagenes = $imagenes;
	}
		
	$app->render(200,array('data' => $favoritos));
});
// chat con el anunciante
//Conexion con la tabla favoritos
$app->get('/chats', function () use ($app) {
	$db = $app->db->getConnection();
	$chats = $db->table('chats')->select('id', 'iduserreceptor', 'iduseremisor', 'mensaje')->get();
	$app->render(200,array('data' => $chats));
});
//Buscar por ID
$app->get('/chat/:id', function ($idr) use ($app) {
	
	$userr = User::find($idr);
	if(empty($userr)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'user not found',
        ));
	}
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
	$chats = $db->table('chats')->select('id', 'iduserreceptor', 'iduseremisor', 'mensaje')
								->where('iduserreceptor', $idr)
								->where('iduseremisor', $user->id)
								->get();
	$app->render(200,array('data' => $userr->toArray()));
	$app->render(200,array('data' => $chats->toArray()));
});
//Insertar Mensaje
$app->post('/enviarchat', function () use ($app) {
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
  
  $iduserreceptor = $input['iduserreceptor'];
	if(empty($iduserreceptor)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Id receptor is required',
        ));
	}
	$mensaje = $input['mensaje'];
	if(empty($mensaje)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Mensaje es necesario',
        ));
	}
	
	$chat = new Chat();
    $chat->iduserreceptor = $iduserreceptor;
    $chat->mensaje = $mensaje;
    $chat->iduseremisor = $user->id;
    $chat->save();
    $app->render(200,array('data' => $chat->toArray()));
});



$app->run();
?>
