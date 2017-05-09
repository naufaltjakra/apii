<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbHandler {

    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `users` table method ------------------ */

    /**
     * Creating new user
     * @param String $name User full name
     * @param String $email User login email id
     * @param String $password User login password
     */
    public function createUser($username, $email, $password) {
        require_once 'PassHash.php';
        $response = array();

        // First check if user already existed in db
        if (!$this->isUserExists($username,$email)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);

            // Generating API key
            $api_key = $this->generateApiKey();

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO user(username, email, password_hash, api_key, kategori) values(?, ?, ?, ?,1)");
            $stmt->bind_param("ssss", $username, $email, $password_hash, $api_key);

            $result = $stmt->execute();

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }

        return $response;
    }

    /**
     * Checking user login
     * @param String $email User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkLogin($username, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password_hash FROM user WHERE username = ?");

        $stmt->bind_param("s", $username);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
    }

    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isUserExists($username,$email) {
        $stmt = $this->conn->prepare("SELECT username from user WHERE username = ? OR email= ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getUserByUsername($username) {
        $stmt = $this->conn->prepare("SELECT username, email, api_key, kategori, created_at FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($username, $email, $api_key, $kategori, $created_at);
            $stmt->fetch();
            $user = array();
            $user["username"] = $username;
            $user["email"] = $email;
            $user["api_key"] = $api_key;
            $user["kategori"] = $kategori;
            $user["created_at"] = $created_at;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user api key
     * @param String $user_id user id primary key in user table
     */
    public function getApiKeyById($user_id) {
      // user_id= username
        $stmt = $this->conn->prepare("SELECT api_key FROM user WHERE username = ?");
        $stmt->bind_param("s", $user_id);
        if ($stmt->execute()) {
            // $api_key = $stmt->get_result()->fetch_assoc();
            // TODO
            $stmt->bind_result($api_key);
            $stmt->close();
            return $api_key;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user id by api key
     * @param String $api_key user api key
     */
    public function getUserId($api_key) {
        $stmt = $this->conn->prepare("SELECT username FROM user WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($user_id);
            $stmt->fetch();
            // TODO
            // $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user_id;
        } else {
            return NULL;
        }
    }

    /**
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key user api key
     * @return boolean
     */
    public function isValidApiKey($api_key) {
        $stmt = $this->conn->prepare("SELECT username from user WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }

    /* ------------- `timelines` table method ------------------ */
	
	/**
     * Buat Kode Timeline
     * @param 
     */
    public function getKodeTimeline() {
        $stmt = $this->conn->prepare("SELECT count(*) as jml FROM minta_tolong");
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($jml);
            $stmt->fetch();
            $res = "TLG".($jml+1);
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }
	
    /**
     * Creating new timeline
     * @param String $user_id user id to whom timeline belongs to
     * @param String $timeline timeline text
     */
    public function createTimeline($user_id, $judul, $deskripsi, $foto, $lokasi, $kategori) {
		$kd_mintol=$this->getKodeTimeline();
        $stmt = $this->conn->prepare("INSERT INTO minta_tolong(kd_mintol,judul,deskripsi,foto,lokasi,username) VALUES(?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $kd_mintol,$judul,$deskripsi,$foto,$lokasi,$user_id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            // timeline row created
            // now assign the timeline to user
			$keterangan='coba masukin keterangan';
            $new_timeline_id = $kd_mintol;
            $res = $this->createUserTimeline($kategori, $new_timeline_id, $keterangan);
            if ($res) {
                // timeline created successfully
                return $new_timeline_id;
            } else {
                // timeline failed to create
                return 'ERROR1'.$kd_mintol.NULL;
            }
        } else {
            // timeline failed to create
            return 'ERROR2'.$kd_mintol.NULL;
        }
    }
	
    /**
     * Fetching single timeline
     * @param String $timeline_id id of the timeline
     */
    public function getTimeline($timeline_id) {
        $stmt = $this->conn->prepare("SELECT mt.username, mt.kd_mintol, mt.judul,mt.deskripsi,k.nama_kategori,mt.foto FROM kon_kat_mintol kkm INNER JOIN minta_tolong mt ON kkm.kd_mintol=mt.kd_mintol LEFT JOIN kategori k ON kkm.kd_kategori=k.kd_kategori WHERE mt.kd_mintol = ?");
        $stmt->bind_param("s", $timeline_id);
        if ($stmt->execute()) {
            $res = array();
            $stmt->bind_result($username, $id, $judul, $deskripsi, $nama_kategori, $foto);
            // TODO
            // $timeline = $stmt->get_result()->fetch_assoc();
            $stmt->fetch();
            $res["id"] = $id;
            $res["username"] = $username;
            $res["judul"] = $judul;
            $res["deskripsi"] = $deskripsi;
            $res["kategori"] = $nama_kategori;
            $res["foto"] = $foto;
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching all user timelines
     * @param String $user_id id of the user
     */
    public function getAllTimeline() {
        $stmt = $this->conn->prepare("SELECT mt.username, mt.kd_mintol, mt.judul,mt.deskripsi,k.nama_kategori FROM kon_kat_mintol kkm INNER JOIN minta_tolong mt ON kkm.kd_mintol=mt.kd_mintol LEFT JOIN kategori k ON kkm.kd_kategori=k.kd_kategori");
        $stmt->execute();
        $timeline = $stmt->get_result();
        $stmt->close();
        return $timeline;
    }
	
	/**
     * Fetching single momen
     * @param String $momen_id id of the timeline
     */
    public function getMomen($momen_id) {
        $stmt = $this->conn->prepare("SELECT * FROM moment WHERE kd_moment=?");
        $stmt->bind_param("s", $momen_id);
        if ($stmt->execute()) {
            $res = array();
            $stmt->bind_result($kd_moment, $judul, $foto, $penolong, $ditolong, $deskripsi, $tgl);
            // TODO
            // $timeline = $stmt->get_result()->fetch_assoc();
            $stmt->fetch();
            $res["kd_moment"] = $kd_moment;
            $res["judul"] = $judul;
            $res["foto"] = $foto;
            $res["penolong"] = $penolong;
            $res["ditolong"] = $ditolong;
            $res["deskripsi"] = $deskripsi;
            $res["tanggal"] = $tgl;
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }
	
	/**
     * Fetching all user moment
     * @param String $user_id id of the user
     */
    public function getAllMomen($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM moment WHERE penolong='$user_id' OR ditolong='$user_id'");
        $stmt->execute();
        $timeline = $stmt->get_result();
        $stmt->close();
        return $timeline;
    }


    /**
     * Updating timeline
     * @param String $timeline_id id of the timeline
     * @param String $timeline timeline text
     * @param String $status timeline status
     */
    public function updateTask($user_id, $timeline_id, $timeline, $status) {
        $stmt = $this->conn->prepare("UPDATE timelines t, user_timelines ut set t.timeline = ?, t.status = ? WHERE t.id = ? AND t.id = ut.timeline_id AND ut.user_id = ?");
        $stmt->bind_param("siii", $timeline, $status, $timeline_id, $user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /**
     * Deleting a timeline
     * @param String $timeline_id id of the timeline to delete
     */
    public function deleteTimeline($user_id, $timeline_id) {
        $stmt = $this->conn->prepare("DELETE FROM minta_tolong WHERE kd_mintol = ? AND username = ?");
        $stmt->bind_param("ss", $timeline_id, $user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    /* ------------- `user_timelines` table method ------------------ */

    /**
     * Function to assign a timeline to user
     * @param String $user_id id of the user
     * @param String $timeline_id id of the timeline
     */
    public function createUserTimeline($kategori, $timeline_id, $keterangan) {
        $stmt = $this->conn->prepare("INSERT INTO kon_kat_mintol(kd_kategori, kd_mintol, keterangan) values(?, ?, ?)");
        $stmt->bind_param("sss", $kategori, $timeline_id, $keterangan);
        $result = $stmt->execute();

        if (false === $result) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();
        return $result;
    }

}

?>
