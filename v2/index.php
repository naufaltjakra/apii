<?php

require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        } else {
            global $user_id;
            // get user primary key id
            $user_id = $db->getUserId($api_key);
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * ----------- METHODS WITHOUT AUTHENTICATION ---------------------------------
 */
/**
 * User Registration
 * url - /register
 * method - POST
 * params - name, email, password
 */
$app->post('/register', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('username', 'email', 'password'));

            $response = array();

            // reading post params
            $username = $app->request->post('username');
            $email = $app->request->post('email');
            $password = $app->request->post('password');

            // validating email address
            validateEmail($email);
            validateUsername($username);

            $db = new DbHandler();
            $res = $db->createUser($username, $email, $password);
			
			$response["error"] = false;
            $response["results"] = array();
			
			$tmp = array();
			
            if ($res == USER_CREATED_SUCCESSFULLY) {
                $response["error"] = false;

				$tmp["username"] = $username;
				$tmp["email"] = $email;
				$tmp["password"] = $password;

                $response["message"] = "You are successfully registered";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
				
				$tmp["username"] = $username;
				$tmp["email"] = $email;
				$tmp["password"] = $password;

                $response["message"] = "Oops! An error occurred while registereing";
            } else if ($res == USER_ALREADY_EXISTED) {
                $response["error"] = true;
				
				$tmp["username"] = $username;
				$tmp["email"] = $email;
				$tmp["password"] = $password;

                $response["message"] = "Sorry, username or email already existed";
            }else{
				$response["error"] = true;
				$response["message"]="UNKNOWN RESPONSE";
			}
			array_push($response["results"], $tmp);
            // echo json response
            echoRespnse(201, $response);
        });

/**
 * User Login
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('username', 'password'));

            // reading post params
            $username = $app->request()->post('username');
            $password = $app->request()->post('password');
            $response = array();

            $db = new DbHandler();
            // check for correct email and password
            if ($db->checkLogin($username, $password)) {
                // get the user by email
                $user = $db->getUserByUsername($username);

                if ($user != NULL) {
                    $response["error"] = false;
                    $response['username'] = $user['username'];
                    $response['email'] = $user['email'];
                    $response['apiKey'] = $user['api_key'];
                    $response['createdAt'] = $user['created_at'];
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                // user credentials are wrong
                $response['error'] = true;
                $response['message'] = 'Login failed. Incorrect credentials';
            }

            echoRespnse(200, $response);
        });

/*
 * ------------------------ METHODS WITH AUTHENTICATION ------------------------
 */

/**
 * Listing all tasks of particual user
 * method GET
 * url /tasks
 */
$app->get('/timeline', 'authenticate', function() {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user tasks
            $result = $db->getAllTimeline();

            $response["error"] = false;
            $response["timeline"] = array();

            // looping through result and preparing tasks array
            while ($timeline = $result->fetch_assoc()) {
                $tmp = array();
                $tmp["username"] = $timeline["username"];
                $tmp["kd_mintol"] = $timeline["kd_mintol"];
                $tmp["kategori"] = $timeline["nama_kategori"];
                $tmp["judul"] = $timeline["judul"];
                $tmp["deskripsi"] = $timeline["deskripsi"];
                array_push($response["timeline"], $tmp);
            }

            echoRespnse(200, $response);
        });
		
 
/**
 * Listing single task of particual user
 * method GET
 * url /tasks/:id
 * Will return 404 if the task doesn't belongs to user
 */
$app->get('/timeline/:id', 'authenticate', function($timeline_id) {
            global $user_id; // username
            $response = array();
            $db = new DbHandler();

            // fetch task
            $result = $db->getTimeline($timeline_id);

            if ($result != NULL) {
                $response["error"] = false;
                $response["id"] = $result["id"];
                $response["username"] = $result["username"];
                $response["judul"] = $result["judul"];
                $response["deskripsi"] = $result["deskripsi"];
                $response["kategori"] = $result["kategori"];
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The requested resource doesn't exists";
                echoRespnse(404, $response);
            }
        });
		

		
/**
 * Detail User
 * url - /detailuser
 * method - GET
 * params - name, email, alamat, notelp, foto
 */
$app->get('/user/:id', 'authenticate', function($username) {
            global $user_id; // username
            $response = array();
            $db = new DbHandler();

            // fetch task
            $result = $db->getDetailUser($username);

			
			
            if ($result != NULL) {
                $response["error"] = false;
                $response["username"] = $result["username"];
                $response["nama"] = $result["nama"];
				$response["kategori"] = $result["kategori"];
                $response["email"] = $result["email"];
                $response["alamat"] = $result["alamat"];
                $response["no_telp"] = $result["no_telp"];
				$response["foto"] = $result["foto"];
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The requested resource doesn't exists";
                echoRespnse(404, $response);
            }
        });
/**
 * Updating existing task
 * method PUT
 * params task, status
 * url - /tasks/:id
 */
$app->post('/user', 'authenticate', function() use($app) {
            // check for required params
            verifyRequiredParams(array('username', 'nama', 'kategori', 'alamat', 'no_telp', 'foto'));

            global $user_id;
            $username = $app->request->put('username');
            $nama = $app->request->put('nama');
			$kategori = $app->request->put('kategori');
			$alamat = $app->request->put('alamat');
			$no_telp = $app->request->put('no_telp');
			$foto = $app->request->put('foto');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateTask($user_id, $nama, $kategori, $alamat, $no_telp, $foto);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Task updated successfully";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Task failed to update. Please try again!";
            }
            echoRespnse(200, $response);
        });

/**
 * Creating new task in db
 * method POST
 * params - name
 * url - /tasks/
 */
$app->post('/chat', 'authenticate', function() use($app) {
            // check for required params
            verifyRequiredParams(array('kd_mintol', 'username', 'status'));

            global $user_id;
            $kd_mintol = $app->request->put('kd_mintol');
            $username = $app->request->put('username');
			$status = $app->request->put('status');
			

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->createChat($kd_mintol, $username, $status);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Task created successfully";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Task failed to create. Please try again!";
            }
            echoRespnse(200, $response);
        });		

/**
 * Creating new task in db
 * method POST
 * params - name
 * url - /tasks/
 */
$app->post('/timeline', 'authenticate', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('judul','deskripsi','foto','lokasi','kategori'));
			
            $judul = $app->request->post('judul');
            $deskripsi = $app->request->post('deskripsi');
            $foto = $app->request->post('foto');
            $lokasi = $app->request->post('lokasi');
            $kategori = $app->request->post('kategori');
		
			global $user_id;
            $db = new DbHandler();
            $response = array();
			
            // creating new task
            $timeline = $db->createTimeline($user_id, $judul, $deskripsi, $foto, $lokasi, $kategori);
			
			$path="assets/images/".$user_id.".png";
			
			if(!$timeline){
				 $status_foto="Gambar gagal diupload.";
			}else{
				file_put_contents($path,base64_decode($foto));
				$status_foto = "Gambar berhasil diupload.";
			}
			
            if ($timeline != NULL) {
                $response["error"] = false;
                $response["message"] = "Permintaan Pertolongan berhasil dibuat.";
                $response["status_foto"] = $status_foto;
                $response["kd_mintol"] = $timeline;
                echoRespnse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Permintaan anda gagal dibuat. Silakan coba lagi.";
                $response["status_foto"] = $status_foto;
                echoRespnse(200, $response);
            }
        });

/**
 * Updating existing task
 * method PUT
 * params task, status
 * url - /tasks/:id
 */
$app->put('/tasks/:id', 'authenticate', function($task_id) use($app) {
            // check for required params
            verifyRequiredParams(array('task', 'status'));

            global $user_id;
            $task = $app->request->put('task');
            $status = $app->request->put('status');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateTask($user_id, $task_id, $task, $status);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Task updated successfully";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Task failed to update. Please try again!";
            }
            echoRespnse(200, $response);
        });

/**
 * Deleting task. Users can delete only their tasks
 * method DELETE
 * url /tasks
 */
$app->delete('/tasks/:id', 'authenticate', function($timeline_id) use($app) {
            global $user_id;

            $db = new DbHandler();
            $response = array();
            $result = $db->deleteTimeline($user_id, $timeline_id);
            if ($result) {
                // task deleted successfully
                $response["error"] = false;
                $response["message"] = "Task deleted succesfully";
            } else {
                // task failed to delete
                $response["error"] = true;
                $response["message"] = "Task failed to delete. Please try again!";
            }
            echoRespnse(200, $response);
        });

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}
function validateUsername($username) {
    $app = \Slim\Slim::getInstance();
    if (!isset($username) || $username='') {
        $response["error"] = true;
        $response["message"] = 'Username is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();
?>
