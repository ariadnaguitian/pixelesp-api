<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

require 'vendor/autoload.php';
require 'Models/User.php';
require 'Models/Imagen.php';
require 'Models/Noticia.php';
require 'Models/Favoritos.php';
require 'Models/Post.php';
require 'Models/comment.php';
require 'Models/newscomment.php';
require 'Models/empleocomment.php';
require 'Models/imgcomment.php';
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
            'msg'   => 'Se requiere nombre de usuario',
        ));
	}

	
	$password = $input['password'];
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere contraseña',
        ));
	}


	$db = $app->db->getConnection();
	$user =$db->table('usuarios')->select()->where('username', $username)->first();
    if(empty($user)){
        $app->render(500,array(
            'error' => TRUE,
            'msg'   => 'El usuario no existe',
        ));
    }

        if($user->password != $password){
        $app->render(500,array(
            'error' => TRUE,
            'msg'   => 'La contraseña no coincide',
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
            'msg'   => 'Se requiere nombre de usuario',
        ));
	}

	
	$password = $input['password'];
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere contraseña',
        ));
	}

	


	$db = $app->db->getConnection();
	$user =$db->table('usuarios')->select()->where('username', $username)->where('userlevel', '1')->first();
    if(empty($user)){
        $app->render(500,array(
            'error' => TRUE,
            'msg'   => 'El usuario no existe',
        ));
    }

        if($user->password != $password){
        $app->render(500,array(
            'error' => TRUE,
            'msg'   => 'La contraseña no coincide',
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
            'msg'   => 'No has iniciado sesión 1',
        ));
	}
	

	$id_user_token = simple_decrypt($token, $app->enc_key);

	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'No has iniciado sesión 2',
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
            'msg'   => 'Se requiere nombre de usuario',
        ));
	}

	

	$password = $input['password'];
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere contraseña',
        ));
	}


	$email = $input['email'];


	if(empty($email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere email',
        ));
	}
  
  $imagen = 'paintprogram.png';


	
    $user = new User();
  
    $user->password = $password;
  
     $user->username = $username;
   $user->email = $email;
       $user->imagen = $imagen;

     
    $user->save();
    $app->render(200,array('data' => $user->toArray()));
});


$app->put('/usuarios/:id', function ($id) use ($app) {
	$input = $app->request->getBody();
	
	$name = $input['name'];
	if(empty($name)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere nombre',
        ));
	}
	
	$username = $input['username'];
	if(empty($username)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere nombre de usuario',
        ));

	}
$email = $input['email'];
	if(empty($email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere email',
        ));
	}
	$city = $input['city'];
	if(empty($city)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere ciudad',
        ));
	}

$country = $input['country'];
	if(empty($country)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere país',
        ));
	}
$biografia = $input['biografia'];		
	if(empty($biografia)){		
		$app->render(500,array(		
			'error' => TRUE,		
            'msg'   => 'Se requiere descripción',		
        ));		
	}		



	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'Usuario no encontrado',
        ));
	}
    $user->name = $name;
    $user->username = $username;
    $user->email = $email;
    $user->city = $city;
    $user->country = $country;
    $user->biografia = $biografia;

    $user->save();
    $app->render(200,array('data' => $user->toArray()));
});
$app->get('/usuarios/:id', function ($id) use ($app) {
	$db = $app->db->getConnection();
	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'Usuario no encontrado',
        ));
	}
	unset($user->password);

 
 	$user->imagenes = $db->table('imagenes')->select('Titulo')->where('IdUsuario', $user->id)->get();
	$app->render(200,array('data' => $user->toArray()));
});
$app->delete('/usuarios/:id', function ($id) use ($app) {
	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'Usuario no encontrado',
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
	$images = $db->table('noticias')->select('noticias.*','usuarios.username','usuarios.imagen')
	->leftjoin('usuarios', 'usuarios.id', '=', 'noticias.idusuario')
	->orderby('created_at','desc')

	->get();



	foreach ($images as $key => $value) {
		$newscomment =  NewsComments::where('id_noticia', '=', $value->id)
		->select('newscomments.*','usuarios.username','usuarios.imagen')
		->leftjoin('usuarios', 'usuarios.id', '=', 'newscomments.idusuario')
		->orderby('created_at','desc')
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
$app->get('/noticiasusuario/:id', function ($id) use ($app) {

	$db = $app->db->getConnection();
		$usuario = User::find($id);


$noticias =  Noticia::where('idusuario', '=', $usuario->id)->get();
 	if(empty($noticias->toArray())){
 		$result = array();
 	} else{
 		$result = $noticias->toArray(); 
 	}
 	$usuario->noticias = $result;




	$app->render(200,array('data' => $noticias));
});

$app->post('/noticias', function () use ($app) {
	$input = $app->request->getBody();

$idusuario = $input['idusuario'];
	if(empty($idusuario)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere descripción',
        ));
	}

	$Titulo = $input['Titulo'];

 	if(empty($Titulo)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere titulo',
        ));
	}
	$Descripcion = $input['Descripcion'];
	if(empty($Descripcion)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere descripción',
        ));
	}
	
		
    $noticia = new Noticia();
    $noticia->IdUsuario = $idusuario;
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
            'msg'   => 'Se requiere titulo',
        ));
	}
	$Descripcion = $input['Descripcion'];
	if(empty($Descripcion)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere descripción',
        ));
	}
	

	$noticia = Noticia::find($id);
	if(empty($noticia)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'Noticia no encontrada',
        ));
	}
    $noticia->Titulo = $Titulo;
    $noticia->Descripcion = $Descripcion;
 
    $noticia->save();
    $app->render(200,array('data' => $noticia->toArray()));
});
$app->get('/noticias/:id', function ($id) use ($app) {
	$noticia = Noticia::find($id);


$newscomments =  NewsComments::where('id_noticia', '=', $noticia->id)->get();
 	if(empty($newscomments->toArray())){
 		$result = array();
 	} else{
 		$result = $newscomments->toArray(); 
 	}
 	$noticia->comentarios = $result;

	if(empty($noticia)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'Noticia no encontrada',
        ));
	}
	$app->render(200,array('data' => $noticia->toArray()));
});


$app->delete('/noticias/:id', function ($id) use ($app) {
	$noticia = Noticia::find($id);
	if(empty($noticia)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'Noticia no encontrada',
        ));
	}
	$noticia->delete();
	$app->render(200);
});


$app->get('/imagenes', function () use ($app) {
	$db = $app->db->getConnection();
$imagenes = $db->table('imagenes')->select('imagenes.*','usuarios.username')			
	->leftjoin('usuarios', 'usuarios.id', '=', 'imagenes.idusuario')		
	->orderby('created_at','desc')->get();
	
	$app->render(200,array('data' => $imagenes));
});


$app->post('/imagenes', function () use ($app) {
	$input = $app->request->getBody();

	$Titulo = $input['Titulo'];

 	if(empty($Titulo)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere título',
        ));
	}
	$Descripcion = $input['Descripcion'];
	if(empty($Descripcion)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere descripción',
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
            'msg'   => 'Se requiere título',
        ));
	}
	$Descripcion = $input['Descripcion'];
	if(empty($Descripcion)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Se requiere descripción',
        ));
	}

	$imagen = Image::find($id);
	if(empty($imagen)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'imagen no encontrada',
        ));
	}
    $imagen->Titulo = $Titulo;
    $imagen->Descripcion = $Descripcion;
    $imagen->save();
    $app->render(200,array('data' => $imagen->toArray()));
});
$app->get('/imagenes/:id', function ($id) use ($app) {
	$imagen = Image::find($id);

	$imgComments =  ImgComments::where('id_imagen', '=', $imagen->id)			
	->select('imgcomments.*','usuarios.username', 'usuarios.imagen')		
	->leftjoin('usuarios', 'usuarios.id', '=', 'imgcomments.idusuario')		
	->orderby('created_at','desc')->get();
 	if(empty($imgComments->toArray())){
 		$result = array();
 	} else{
 		$result = $imgComments->toArray(); 
 	}
 	$imagen->comentarios = $result;



	    $imgfavoritos =  Favorito::where('idimagen', '=', $imagen->id)
		->select('imgfavoritos.*','usuarios.username','usuarios.imagen')
		->leftjoin('usuarios', 'usuarios.id', '=', 'imgfavoritos.idusuario')
		->orderby('created_at','desc')
		->get();
		if(empty($imgfavoritos)){
						$result = array();
		} else{
			$result = $imgfavoritos->toArray(); 
		}
		$imagen->favoritos = $result;
	

	if(empty($imagen)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'imagen no encontrada',
        ));
	}
	$app->render(200,array('data' => $imagen->toArray()));
});
$app->delete('/imagenes/:id', function ($id) use ($app) {
	$imagen = Image::find($id);

	$imgComments =  ImgComments::where('id_imagen', '=', $imagen->id)->get();
	if(empty($imgComments->toArray())){
		$result = array();
	} else{
		$result = $imgComments->toArray(); 
	}
	$imagen->comentarios = $result;
	if(empty($imagen)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'imagen no encontrada',
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
            'msg'   => 'se requiere texto',
        ));
	}

	$comment = Comment::find($id);
	if(empty($comment)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'comentario no encontrado',
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
            'msg'   => 'comentario no encontrado',
        ));
	}
	$app->render(200,array('data' => $comment->toArray()));
});
$app->delete('/comments/:id', function ($id) use ($app) {
	$comment = Comment::find($id);
	if(empty($comment)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'comentario no encontrado',
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


	//comentarios imagenes:		
$app->get('/imgcomments', function () use ($app) {		
	$db = $app->db->getConnection();		
	$imgcomments = $db->table('imgcomments')->select()->orderby('created_at','desc')->get();		
	$app->render(200,array('data' => $imgcomments));		
});		
$app->post('/imgcomments', function () use ($app) {		
	$input = $app->request->getBody();		
	$text = $input['text'];		
	$idusuario = $input['idusuario'];		
	$id_imagen = $input['id_imagen'];		
			
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
			
	if(empty($id_imagen)){		
		$app->render(500,array(		
			'error' => TRUE,		
            'msg'   => 'id_imagen is required',		
        ));		
	}		
				
    $imgcomment = new ImgComments();		
    $imgcomment->idusuario  = $idusuario;		
    $imgcomment->id_imagen = $id_imagen;		
    $imgcomment->text 		 = $text;		
 		
     		
    $imgcomment->save();		
    $app->render(200,array('data' => $imgcomment->toArray()));		
});		
$app->put('/imgcomments/:id', function ($id) use ($app) {		
	$input = $app->request->getBody();		
			
	$text = $input['text'];		
	if(empty($text)){		
		$app->render(500,array(		
			'error' => TRUE,		
            'msg'   => 'text is required',		
        ));		
	}		
	$imgcomment = ImgComments::find($id);		
	if(empty($imgcomment)){		
		$app->render(404,array(		
			'error' => TRUE,		
            'msg'   => 'imgcomment not found',		
        ));		
	}		
    $imgcomment->text = $text;		
    $imgcomment->save();		
    $app->render(200,array('data' => $imgcomment->toArray()));		
});		
$app->get('/imgcomments/:id', function ($id) use ($app) {		
	$imgcomment = ImgComments::find($id);		
	if(empty($imgcomment)){		
		$app->render(404,array(		
			'error' => TRUE,		
            'msg'   => 'imgcomment not found',		
        ));		
	}		
	$app->render(200,array('data' => $imgcomment->toArray()));		
});		
$app->delete('/imgcomments/:id', function ($id) use ($app) {		
	$imgcomment = ImgComments::find($id);		
	if(empty($imgcomment)){		
		$app->render(404,array(		
			'error' => TRUE,		
            'msg'   => 'comment not found',		
        ));		
	}		
	$comment->delete();		
	$app->render(200);		
});

//comentarios de trabajos:
$app->get('/empleocomments', function () use ($app) {
	$db = $app->db->getConnection();
	$empleocomments = $db->table('empleocomments')->select()->orderby('created_at','desc')->get();
	$app->render(200,array('data' => $empleocomments));
});

$app->post('/empleocomments', function () use ($app) {
	$input = $app->request->getBody();

	$text = $input['text'];
	$idusuario = $input['idusuario'];
	$id_empleo = $input['id_empleo'];
	
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
	
	if(empty($id_empleo)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'id_empleo is required',
        ));
	}


		
    $empleocomment = new EmpleoComments();
    $empleocomment->idusuario  = $idusuario;
    $empleocomment->id_empleo = $id_empleo;
    $empleocomment->text 		 = $text;
 
     
    $empleocomment->save();
    $app->render(200,array('data' => $empleocomment->toArray()));
});

$app->put('/empleocomments/:id', function ($id) use ($app) {
	$input = $app->request->getBody();
	
	$text = $input['text'];
	if(empty($text)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'text is required',
        ));
	}

	$empleocomment = empleoComments::find($id);
	if(empty($empleocomment)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'empleocomment not found',
        ));
	}
    $empleocomment->text = $text;
    $empleocomment->save();
    $app->render(200,array('data' => $empleocomment->toArray()));
});
$app->get('/empleocomments/:id', function ($id) use ($app) {
	$empleocomment = EmpleoComments::find($id);
	if(empty($empleocomment)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'empleocomment not found',
        ));
	}
	$app->render(200,array('data' => $empleocomment->toArray()));
});
$app->delete('/empleocomments/:id', function ($id) use ($app) {
	$empleocomment = EmpleoComments::find($id);
	if(empty($empleocomment)){
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
	$trabajos = $db->table('trabajos')->select('trabajos.*','usuarios.username','usuarios.imagen')
	->leftjoin('usuarios', 'usuarios.id','=', 'trabajos.idusuario')
	->orderby('created_at','desc')
	->get();
	foreach ($trabajos as $key => $value){
		$empleocomment =  EmpleoComments::where('id_empleo', '=', $value->id)
		->select('empleocomments.*','usuarios.username','usuarios.imagen')
		->leftjoin('usuarios', 'usuarios.id', '=', 'empleocomments.idusuario')
		->orderby('created_at','desc')
		->get();
		if(empty($empleocomment)){
			$result = array();
		} else{
			$result = $empleocomment->toArray(); 
		}
		$trabajos[$key]->comentarios = $result;
	}
	$app->render(200,array('data' => $trabajos));
});

$app->post('/trabajos', function () use ($app) {
	$input = $app->request->getBody();

	$idusuario = $input['idusuario'];		
	if(empty($idusuario)){		
		$app->render(500,array(		
			'error' => TRUE,		
            'msg'   => 'Se requiere descripción',		
        ));		
	}
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
    $trabajo->IdUsuario = $idusuario;
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
	$empleocomments =  EmpleoComments::where('id_empleo', '=', $trabajo->id)->get();
 	if(empty($empleocomments->toArray())){
 		$result = array();
 	} else{
 		$result = $empleocomments->toArray(); 
 	}
 	$trabajo->comentarios = $result;
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
            'msg'   => 'No has iniciado sesión ',
        ));
	}

	$id_user_token = simple_decrypt($token, $app->enc_key);

	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'No has iniciado sesión ',
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

		$idusuario = $input['idusuario'];
 	if(empty($idusuario)){
 		$app->render(500,array(
 			'error' => TRUE,
             'msg'   => 'Se requiere descripción',
         ));
 	}
	
	$imagen = new Image();
	$imagen->title = $title;
	$imagen->IdUsuario = $idusuario;
   
    $imagen->save();
    $app->render(200,array('data' => $imagen->toArray()));
});

$app->post('/imagenes/:id/comment', function ($id) use ($app) {
	$token = $app->request->headers->get('auth-token');

	if(empty($token)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'No has iniciado sesión ',
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
            'msg'   => 'No has iniciado sesión ',
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
            'msg'   => 'No has iniciado sesión ',
        ));
	}

	$id_user_token = simple_decrypt($token, $app->enc_key);

	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'No has iniciado sesión ',
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
            'msg'   => 'No has iniciado sesión ',
        ));
	}

	$id_user_token = simple_decrypt($token, $app->enc_key);

	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'No has iniciado sesión ',
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
            'msg'   => 'No has iniciado sesión ',
        ));
	}
	$id_user_token = simple_decrypt($token, $app->enc_key);
	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'No has iniciado sesión ',
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
            'msg'   => 'No has iniciado sesión ',
        ));
	}
	$id_user_token = simple_decrypt($token, $app->enc_key);
	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'No has iniciado sesión ',
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




//Conexion con la tabla favoritos
$app->get('/favimg', function () use ($app) {
	$db = $app->db->getConnection();
	$fav = $db->table('imgfavoritos')->select('id', 'idimagen', 'idusuario')->get();
	$app->render(200,array('data' => $fav));
});


// agregar favoritos
$app->post('/imgfavoritos', function () use ($app) {
  $token = $app->request->headers->get('auth-token');
	if(empty($token)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'No has iniciado sesión ',
        ));
	}
	$id_user_token = simple_decrypt($token, $app->enc_key);
	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'No has iniciado sesión ',
        ));
	}
	
  $input = $app->request->getBody();
  
  $idimagen = $input['idimagen'];
	if(empty($idimagen)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Id imagen is required',
        ));
	}
	
	$favoritoimg = new Favorito();
    $favoritoimg->idimagen = $idimagen;
    $favoritoimg->idusuario = $user->id;
    $favoritoimg->save();
    $app->render(200,array('data' => $favoritoimg->toArray()));
});

	

// Traer favorito especifico para borrar
$app->get('/imgfavoritos/:id', function ($id) use ($app) {

	
	
	$favoritosimg = Favorito::find($id);	
	
	
	if(empty($favoritosimg)){		
		$app->render(404,array(		
			'error' => TRUE,		
            'msg'   => 'favoritosimg not found',		
        ));		
	}		
	$app->render(200,array('data' => $favoritosimg->toArray()));	
});
// ver favorito y borrar 

$app->delete('/imgfavoritos/:id', function ($id) use ($app) {
	
	$favoritosimg = Favorito::find($id);		
	if(empty($favoritosimg)){		
		$app->render(404,array(		
			'error' => TRUE,		
            'msg'   => 'favorito not found',		
        ));		
	}		
	$favoritosimg->delete();		
	$app->render(200);		

  
	
	

	

		
});
// listar mis favoritos
$app->get('/misfavoritosimglist', function () use ($app) {
	
	$token = $app->request->headers->get('auth-token');
	if(empty($token)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'No has iniciado sesión ',
        ));
	}
	$id_user_token = simple_decrypt($token, $app->enc_key);
	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'No has iniciado sesión ',
        ));
	}
	
	
	$db = $app->db->getConnection();


	
	$favoritosimg = $db->table('imgfavoritos')->select('id', 'idusuario', 'idimagen')->where('idusuario', $user->id)->get();
	foreach ($favoritosimg as $key => $favoritosimg) {


		$imagenes = $db->table('imagenes')->select('id', 'IdUsuario', 'Titulo', 'Descripcion', 'Imagen', 'Previa')->where('id', $favoritosimg->idimagen)->get();
		
		$favoritosimg[$key]->imagenes = $imagenes;
	}
		
	$app->render(200,array('data' => $favoritosimg));
});



$app->run();
?>
