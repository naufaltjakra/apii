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
            verifyRequiredParams(array('username', 'nim', 'password'));

            $response = array();

            // reading post params
            $username = $app->request->post('username');
            $nim = $app->request->post('nim');
            $password = $app->request->post('password');

            // validating email address
            // validateNim($nim);
            validateUsername($username);

            $db = new DbHandler();
            $res = $db->createUser($username, $nim, $password);

			$response["error"] = false;
            $response["results"] = array();

			$tmp = array();

            if ($res == USER_CREATED_SUCCESSFULLY) {
                $response["error"] = false;

				$tmp["username"] = $username;
				$tmp["nim"] = $nim;
				$tmp["password"] = $password;

                $response["message"] = "You are successfully registered";
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;

				$tmp["username"] = $username;
				$tmp["nim"] = $nim;
				$tmp["password"] = $password;

                $response["message"] = "Oops! An error occurred while registering";
            } else if ($res == USER_ALREADY_EXISTED) {
                $response["error"] = true;

				$tmp["username"] = $username;
				$tmp["nim"] = $nim;
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
            verifyRequiredParams(array('nim', 'password'));

            // reading post params
            $nim = $app->request()->post('nim');
            $password = $app->request()->post('password');
            $response = array();

            $db = new DbHandler();
            // check for correct email and password
            if ($db->checkLogin($nim, $password)) {
                // get the user by email
                $user = $db->getUserByUsername($nim);

                if ($user != NULL) {
                    $response["error"] = false;
                    $response["message"] = "Login successfully . . .";
                    $response['username'] = $user['username'];
                    $response['nim'] = $user['nim'];
                    $response['apiKey'] = $user['api_key'];
                    $response['createdAt'] = $user['created_at'];
                    $response['ipk'] = $user['ipk'];
                    $response['pembimbing'] = $user['pembimbing'];
                    $response['judul_ta'] = $user['judul_ta'];
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
            verifyRequiredParams(array('username', 'email','nama', 'kategori', 'alamat', 'no_telp', 'foto'));

            global $user_id;
            $username = $app->request->put('username');
            $email = $app->request->put('email');
            $nama = $app->request->put('nama');
			$kategori = $app->request->put('kategori');
			$alamat = $app->request->put('alamat');
			$no_telp = $app->request->put('no_telp');
			$foto = $app->request->put('foto');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateUser($user_id, $email, $nama, $kategori, $alamat, $no_telp, $foto);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Data berhasil diperbaharui.";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Tidak ada data yang diperbaharui.";
            }
            echoRespnse(200, $response);
        });
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
                $tmp["foto"] = $timeline["foto"];
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
                $response["foto"] = $result["foto"];
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The requested resource doesn't exists";
                echoRespnse(404, $response);
            }
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

            if ($timeline != NULL) {
                $response["error"] = false;
                $response["message"] = "Permintaan Pertolongan berhasil dibuat.";
                $response["foto"] = $foto;
                $response["kd_mintol"] = $timeline;
                echoRespnse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Permintaan anda gagal dibuat. Silakan coba lagi.";
                $response["foto"] = "Tidak Ada";
                echoRespnse(200, $response);
            }
        });

 /**
 * Updating existing task
 * method PUT
 * params task, status
 * url - /tasks/:id
 */
$app->post('/tolongin', 'authenticate', function() use($app) {
            // check for required params
            verifyRequiredParams(array('kd_mintol', 'status', 'create_at'));

            global $user_id;
            $kd_mintol = $app->request->put('kd_mintol');
            $status = $app->request->put('status');
            $create_at = $app->request->put('create_at');

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->updateUser($kd_mintol, $user_id, $status, $create_at);
            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Terimakasih niatnya. Segera lakukan!";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Terjadi kesalahan. Silakan coba lagi.";
            }
            echoRespnse(200, $response);
        });

/**
 * Listing all tasks of particual user
 * method GET
 * url /tasks
 */
$app->get('/leaderboard', 'authenticate', function() {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user tasks
            $result = $db->getLeader($user_id);

            $response["error"] = false;
            $response["leaderboard"] = array();

            // looping through result and preparing tasks array
            while ($timeline = $result->fetch_assoc()) {
                $tmp = array();
                $tmp["username"] = $timeline["username"];
                $tmp["poin_masuk"] = $timeline["poin_masuk"];
                $tmp["foto"] = $timeline["foto"];

                array_push($response["leaderboard"], $tmp);
            }

            echoRespnse(200, $response);
        });

/**
 * Listing all tasks of particual user
 * method GET
 * url /tasks
 */
$app->get('/leaderboard/:id', 'authenticate', function($user_id) {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching tasks
            $result = $db->getLeadUser($user_id);


            if ($result != NULL) {
                $response["error"] = false;
                $response["username"] = $result["username"];
                $response["poin_masuk"] = $result["poin_masuk"];
                $response["foto"] = $result["foto"];
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The requested resource doesn't exists";
                echoRespnse(404, $response);
            }

        });

 /**
 * Listing all tasks of particual user
 * method GET
 * url /tasks
 */
$app->get('/momen', 'authenticate', function() {
            global $user_id;
            $response = array();
            $db = new DbHandler();

            // fetching all user tasks
            $result = $db->getAllMomen($user_id);

            $response["error"] = false;
            $response["momen"] = array();

            // looping through result and preparing tasks array
            while ($timeline = $result->fetch_assoc()) {
                $tmp = array();
                $tmp["kd_moment"] = $timeline["kd_moment"];
                $tmp["judul"] = $timeline["judul"];
                $tmp["foto"] = $timeline["foto"];
                $tmp["penolong"] = $timeline["penolong"];
                $tmp["ditolong"] = $timeline["ditolong"];
                $tmp["deskripsi"] = $timeline["deskripsi"];
                $tmp["tanggal"] = $timeline["tgl"];
                array_push($response["momen"], $tmp);
            }

            echoRespnse(200, $response);
        });


/**
 * Listing single task of particual user
 * method GET
 * url /tasks/:id
 * Will return 404 if the task doesn't belongs to user
 */
$app->get('/momen/:id', 'authenticate', function($momen_id) {
            global $user_id; // username
            $response = array();
            $db = new DbHandler();

            // fetch task
            $result = $db->getMomen($momen_id);

            if ($result != NULL) {
                $response["error"] = false;
                $response["kd_moment"] = $result["kd_moment"];
                $response["judul"] = $result["judul"];
                $response["foto"] = $result["foto"];
                $response["penolong"] = $result["penolong"];
                $response["ditolong"] = $result["ditolong"];
                $response["deskripsi"] = $result["deskripsi"];
                echoRespnse(200, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "The requested resource doesn't exists";
                echoRespnse(404, $response);
            }
        });

/**
 * Creating new task in db
 * method POST
 * params - name
 * url - /tasks/
 */
$app->post('/momen', 'authenticate', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('judul','deskripsi','foto','penolong','ditolong'));

            $judul = $app->request->post('judul');
            $deskripsi = $app->request->post('deskripsi');
            $foto = $app->request->post('foto');
            $penolong = $app->request->post('penolong');
            $ditolong = $app->request->post('ditolong');
            //$tanggal = $app->request->post('tanggal');

			global $user_id;
            $db = new DbHandler();
            $response = array();

            // creating new task
            $timeline = $db->createMomen($user_id, $judul, $deskripsi, $foto, $penolong, $ditolong);

            if ($timeline != NULL) {
                $response["error"] = false;
                $response["message"] = "Permintaan Pertolongan berhasil dibuat.";
                $response["foto"] = $foto;
                $response["kd_moment"] = $timeline;
                echoRespnse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Permintaan anda gagal dibuat. Silakan coba lagi.";
                $response["foto"] = "Tidak Ada";
                echoRespnse(200, $response);
            }
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
            $kd_mintol = $app->request->post('kd_mintol');
            $username = $app->request->post('username');
			$status = $app->request->post('status');


            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->createChat($kd_mintol, $user_id, $status);
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
 * method GET
 * params - name
 * url - /tasks/
 */
$app->get('/chat', 'authenticate', function() use($app) {

            global $user_id;
            $response = array();
            $db = new DbHandler();

			// fetching all user tasks
            $result = $db->getAllChat($user_id);

            $response["error"] = false;
            $response["chat"] = array();

            // looping through result and preparing tasks array
            while ($timeline = $result->fetch_assoc()) {
                $tmp = array();
                $tmp["foto"] = $timeline["foto"];
                $tmp["username"] = $timeline["username"];
                $tmp["judul"] = $timeline["judul"];
				$tmp["deskripsi"] = $timeline["deskripsi"];
				$tmp["createAt"] = $timeline["createAt"];
				$tmp["status"] = $timeline["status"];
				$tmp["penolong"] = $timeline["penolong"];
				$tmp["ditolong"] = $timeline["ditolong"];

                array_push($response["chat"], $tmp);
            }

            echoRespnse(200, $response);

        });

/**
 * Konfirmasi  Pertolongan (dari sisi yang minta tolong) jika sudah ditolong
 * method POST
 * params - name
 * url - /tasks/
 */
$app->post('/konfirmasi/chat', 'authenticate', function() use($app) {
            // check for required params
            verifyRequiredParams(array('kd_mintol', 'penolong')); // username calon penolong

            global $user_id;
            $kd_mintol = $app->request->post('kd_mintol');
            $username = $app->request->post('penolong'); // username penolong

            $db = new DbHandler();
            $response = array();

            // updating task
            $result = $db->konfirmasiPertolongan($kd_mintol, $username);

            if ($result) {
                // task updated successfully
                $response["error"] = false;
                $response["message"] = "Konfirmasi sukses.";
            } else {
                // task failed to update
                $response["error"] = true;
                $response["message"] = "Konfirmasi gagal. Silakan coba lagi.";
            }
            echoRespnse(200, $response);
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
 * Creating new task in db
 * method POST
 * params - name
 * url - /tasks/
 */
$app->post('/momen', 'authenticate', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('judul','deskripsi','foto','penolong','ditolong','tanggal'));

            $judul = $app->request->post('judul');
            $deskripsi = $app->request->post('deskripsi');
            $foto = $app->request->post('foto');
            $penolong = $app->request->post('penolong');
            $ditolong = $app->request->post('ditolong');
            $tanggal = $app->request->post('tanggal');

			global $user_id;
            $db = new DbHandler();
            $response = array();

            // creating new momen
            $momen = $db->createMomen($user_id, $judul, $deskripsi, $foto, $penolong, $tanggal);

            if ($timeline != NULL) {
                $response["error"] = false;
                $response["message"] = "Permintaan Pertolongan berhasil dibuat.";
                $response["foto"] = $foto;
                $response["momen"] = $momen;
                echoRespnse(201, $response);
            } else {
                $response["error"] = true;
                $response["message"] = "Permintaan anda gagal dibuat. Silakan coba lagi.";
                $response["foto"] = $foto;
                echoRespnse(200, $response);
            }
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
