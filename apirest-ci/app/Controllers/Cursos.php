<?php namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\CursosModel;
use App\Models\ClientesModel;

class Cursos extends Controller
{
    /*=============================================
	Mostrar todos los registros
	=============================================*/

    public function index(){

    	$request = \Config\Services::request(); 
    	$headers = $request->getHeaders();
    	
    	$clientesModel = new ClientesModel();
        $clientes = $clientesModel->findAll();

       	$db = \Config\Database::connect();

        foreach($clientes as $key => $value){

        	if(array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])){

	    		if($request->getHeader('Authorization') == 'Authorization: Basic '.base64_encode($value["id_cliente"].":".$value["llave_secreta"])){

	    			// $cursosModel = new CursosModel();
        			// $cursos = $cursosModel->findAll();

	    			if(isset($_GET["page"])){

	    				$cantidad = 10;
	    				$desde = ($_GET["page"]-1)*$cantidad;

	    				$query = $db->query('SELECT cursos.id, titulo, descripcion, instructor, imagen, precio, id_creador, nombre, apellido FROM cursos INNER JOIN clientes ON cursos.id_creador = clientes.id LIMIT '.$cantidad.' OFFSET '.$desde);
						$cursos = $query->getResult();

	    			}else{

	    				$query = $db->query('SELECT cursos.id, titulo, descripcion, instructor, imagen, precio, id_creador, nombre, apellido FROM cursos INNER JOIN clientes ON cursos.id_creador = clientes.id');
						$cursos = $query->getResult();

	    			}	    			

			        if(!empty($cursos)){

			        	$json = array(

			        		"status"=>200,
			        		"total_results"=>count($cursos),
			        		"message"=>$cursos

			        	);

			        }else{

			    		$json = array(

			        		"status"=>404,
			        		"total_results"=>0,
			        		"message"=>"Ningún registro cargado"

			        	);

			        }		        

	    		}else{

    				$json = array(

			    		"status"=>404,
			    		"detalles"=>"El token es inválido"
			    		
			    	);

	    		}

	    	}else{

    			$json = array(

		    		"status"=>404,
		    		"detalles"=>"No está autorizado para recibir los registros"
		    		
		    	);

	    	}

        }

        return json_encode($json, true);	  	           
      
    }

    /*=============================================
	Crear nuevo registro
	=============================================*/

    public function create(){

    	$request = \Config\Services::request(); 
    	$validation = \Config\Services::validation();

    	$headers = $request->getHeaders();

    	$clientesModel = new ClientesModel();
        $clientes = $clientesModel->findAll();

        foreach($clientes as $key => $value){

        	if(array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])){

	    		if($request->getHeader('Authorization') == 'Authorization: Basic '.base64_encode($value["id_cliente"].":".$value["llave_secreta"])){

			    	// Tomar datos
			    	
			    	$datos = array("titulo"=>$request->getVar("titulo"),
			    				   "descripcion"=>$request->getVar("descripcion"),
			    				   "instructor"=>$request->getVar("instructor"),
			    				   "imagen"=>$request->getVar("imagen"),
			    				   "precio"=>$request->getVar("precio"));

			    	if(!empty($datos)){

				    	//Validar datos

				    	$validation->setRules([
						    'titulo' => 'required|string|max_length[255]is_unique[cursos.titulo]',
						    'descripcion' => 'required|string|max_length[255]is_unique[cursos.descripcion]',
						    'instructor' => 'required|string|max_length[255]',
						    'imagen' => 'required|string|max_length[255]',
						    'precio' => 'required|numeric'
						   
						]);

						$validation->withRequest($this->request)
				           ->run();

				        if($validation->getErrors()){

				        	$errors = $validation->getErrors();

				        	$json = array(
				        	 	"status"=>404,
					    		"detalle"=>$errors
					    	); 
					    	
					    	return json_encode($json, true); 

				        }else{

				        	$datos = array("titulo"=>$datos["titulo"],
				    				   	   "descripcion"=>$datos["descripcion"],
				    				   	   "instructor"=>$datos["instructor"],
				    				   	   "imagen"=>$datos["imagen"],
				    				   	   "precio"=>$datos["precio"],
				    				   	   "id_creador"=>$value["id"]);
				        	
				        	$cursosModel = new CursosModel();
				        	$cursosModel->save($datos);

				        	$json = array(
				        	 	"status"=>200,
					    		"detalle"=>"Registro exitoso, su curso ha sido guardado"

					    	); 
					    	
					    	return json_encode($json, true);     	

				        }

				    }else{

				    	$json = array(

				    		"status"=>404,
				    		"detalle"=>"Registro con errores"
				    	);

				    	return json_encode($json, true);
				
				    }
				
				}else{

					$json = array(

			    		"status"=>404,
			    		"detalles"=>"El token es inválido"
			    		
			    	);		    		    	  	

				}

			}else{

    			$json = array(

		    		"status"=>404,
		    		"detalles"=>"No está autorizado para guardar los registros"
		    		
		    	);	  	

	    	}

	    }

	    return json_encode($json, true);	
	    
    }

    /*=============================================
	Mostrar un solo registro
	=============================================*/

	public function show($id){

    	$request = \Config\Services::request(); 
    	$headers = $request->getHeaders();

    	$clientesModel = new ClientesModel();
        $clientes = $clientesModel->findAll();

        foreach($clientes as $key => $value){

        	if(array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])){

	    		if($request->getHeader('Authorization') == 'Authorization: Basic '.base64_encode($value["id_cliente"].":".$value["llave_secreta"])){

	    			$cursosModel = new CursosModel();
			        $curso = $cursosModel->find($id);

			        if(!empty($curso)){

			        	$json = array(

			        		"status"=>200,
			        		"message"=>$curso

			        	);

			        }else{

			    		$json = array(

			        		"status"=>404,
			        		"message"=>"No hay ningún curso registrado"

			        	);

			        }		        

	    		}else{

    				$json = array(

			    		"status"=>404,
			    		"detalles"=>"El token es inválido"
			    		
			    	);

	    		}

	    	}else{

    			$json = array(

		    		"status"=>404,
		    		"detalles"=>"No está autorizado para recibir los registros"
		    		
		    	);

	    	}

        }

        return json_encode($json, true);	  	           
      
    }

    /*=============================================
	Editar un registro
	=============================================*/

    public function update($id){

    	$request = \Config\Services::request(); 
    	$validation = \Config\Services::validation();

    	$headers = $request->getHeaders();

    	$clientesModel = new ClientesModel();
        $clientes = $clientesModel->findAll();

        foreach($clientes as $key => $value){

        	if(array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])){

	    		if($request->getHeader('Authorization') == 'Authorization: Basic '.base64_encode($value["id_cliente"].":".$value["llave_secreta"])){

			    	// Tomar datos
			    	
			    	$datos = $this->request->getRawInput();

			    	if(!empty($datos)){

				    	//Validar datos

				    	$validation->setRules([
						    'titulo' => 'required|string|max_length[255]',
						    'descripcion' => 'required|string|max_length[255]',
						    'instructor' => 'required|string|max_length[255]',
						    'imagen' => 'required|string|max_length[255]',
						    'precio' => 'required|numeric'
						   
						]);

						$validation->withRequest($this->request)
				           ->run();

				        if($validation->getErrors()){

				        	$errors = $validation->getErrors();

				        	$json = array(
				        	 	"status"=>404,
					    		"detalle"=>$errors
					    	); 
					    	
					    	return json_encode($json, true); 

				        }else{


				        	$cursosModel = new CursosModel();
			        		$curso = $cursosModel->find($id);

			        		if($value["id"] == $curso["id_creador"]){		        		

					        	$datos = array("titulo"=>$datos["titulo"],					        
					    				   	   "descripcion"=>$datos["descripcion"],
					    				   	   "instructor"=>$datos["instructor"],
					    				   	   "imagen"=>$datos["imagen"],
					    				   	   "precio"=>$datos["precio"]);
					        	
								$cursosModel = new CursosModel();
					        	$cursosModel->update($id, $datos);

					        	$json = array(
					        	 	"status"=>200,
						    		"detalle"=>"Registro exitoso, su curso ha sido actualizado"

						    	); 
						    	
						    	return json_encode($json, true);   


			        		}else{

		        				$json = array(

						    		"status"=>404,
						    		"detalle"=>"No está autorizado para modificar este curso"
						    	
						    	);

						    	return json_encode($json, true);
			        		
			        		}

				        }

				    }else{

				    	$json = array(

				    		"status"=>404,
				    		"detalle"=>"Registro con errores"
				    	);

				    	return json_encode($json, true);
				
				    }
				
				}else{

					$json = array(

			    		"status"=>404,
			    		"detalles"=>"El token es inválido"
			    		
			    	);		    		    	  	

				}

			}else{

    			$json = array(

		    		"status"=>404,
		    		"detalles"=>"No está autorizado para editar los registros"
		    		
		    	);	  	

	    	}

	    }

	    return json_encode($json, true);	
	    
    }

    /*=============================================
   	Borrar registro
    =============================================*/

    public function delete($id){

    	$request = \Config\Services::request(); 
    	$validation = \Config\Services::validation();

    	$headers = $request->getHeaders();

    	$clientesModel = new ClientesModel();
        $clientes = $clientesModel->findAll();

        foreach($clientes as $key => $value){

        	if(array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])){

	    		if($request->getHeader('Authorization') == 'Authorization: Basic '.base64_encode($value["id_cliente"].":".$value["llave_secreta"])){

	    			$cursosModel = new CursosModel();
	        		$validar = $cursosModel->find($id);

	        		if(!empty($validar)){

	        			if($value["id"] == $validar["id_creador"]){		 

	        				$cursosModel = new CursosModel();
					        $cursosModel->delete($id);

					        $json = array(

					    		"status"=>200,
					    		"detalle"=>"Se ha borrado su curso con éxito"
					    		
					    	);

				    		return json_encode($json, true);

	        			}else{

	    					$json = array(

						    		"status"=>404,
						    		"detalle"=>"No está autorizado para eliminar este curso"
						    	);

						    return json_encode($json, true);

	    				}


	        		}else{

	        			$json = array(

			    			"status"=>404,
			    			"detalle"=>"El curso no existe"
				    	);

					    return json_encode($json, true);

	        		}


	    		}else{

	    			$json = array(

			    		"status"=>404,
			    		"detalles"=>"El token es inválido"
			    		
			    	);		    		    	  		    			  	

		    	}

	    	}else{

				$json = array(

			    		"status"=>404,
			    		"detalles"=>"No está autorizado para editar los registros"
			    		
			    	);	
			
			}

	    
	    }

	    return json_encode($json, true);

    }
    
}