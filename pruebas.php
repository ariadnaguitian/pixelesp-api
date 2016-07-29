<? php

 //imagenes

$app->get('/imagenes', function () use ($app) {
	$db = $app->db->getConnection();
$imagenes = $db->table('imagenes')->select('imagenes.*','usuarios.username')			
	->leftjoin('usuarios', 'usuarios.id', '=', 'imagenes.idusuario')		
	->orderby('created_at','desc')->get();
	
	$app->render(200,array('data' => $imagenes));
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

	if(empty($imagen)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'imagen no encontrada',
        ));
	}
	$app->render(200,array('data' => $imagen->toArray()));
});

//comentarios de imagenes:

$app->get('/imgcomments', function () use ($app) {		
	$db = $app->db->getConnection();		
	$imgcomments = $db->table('imgcomments')->select()->orderby('created_at','desc')->get();		
	$app->render(200,array('data' => $imgcomments));		
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














?>