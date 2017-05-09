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
    public function createUser($username, $nim, $password) {
        require_once 'PassHash.php';
        $response = array();

        // First check if user already existed in db
        if (!$this->isUserExists($nim)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);

            // Generating API key
            $api_key = $this->generateApiKey();

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO user(username, nim, password, api_key) values(?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $nim, $password_hash, $api_key);

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
    public function checkLogin($nim, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password_hash FROM user WHERE nim = ?");

        $stmt->bind_param("s", $nim);

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
    private function isUserExists($nim) {
        $stmt = $this->conn->prepare("SELECT nim from user WHERE nim = ?");
        $stmt->bind_param("s", $nim);
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
    public function getUserByUsername($nim) {
        $stmt = $this->conn->prepare("SELECT username, nim, api_key, created_at,ipk, pembimbing_ta, judul_ta FROM user WHERE nim = ?");
        $stmt->bind_param("s", $nim);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($username, $nim, $api_key, $created_at, $ipk, $pembimbing, $judul_ta);
            $stmt->fetch();
            $user = array();
            $user["username"] = $username;
            $user["nim"] = $nim;
            $user["api_key"] = $api_key;
            $user["created_at"] = $created_at;
            $user["ipk"] = $ipk;
            $user["pembimbing"] = $pembimbing;
            $user["judul_ta"] = $judul_ta;
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
        $stmt = $this->conn->prepare("SELECT api_key FROM user WHERE nim = ?");
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
        $stmt = $this->conn->prepare("SELECT nim FROM user WHERE api_key = ?");
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
        $stmt = $this->conn->prepare("SELECT nim from user WHERE api_key = ?");
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
/* ------------------------------------------------------------- `batas` table method ----------------------------------------------------------------------- */
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
        $stmt = $this->conn->prepare("INSERT INTO minta_tolong(kd_mintol,judul,deskripsi,foto,lokasi,username,status) VALUES(?,?,?,?,?,?,0)");
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
                return NULL;
            }
        } else {
            // timeline failed to create
            return NULL;
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
     * Fetching detail user
     * @param String $username
     */
    public function getDetailUser($username) {
        $stmt = $this->conn->prepare("SELECT username, email, nama, kategori, alamat, no_telp, foto, created_at FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($username, $email, $nama, $kategori, $alamat, $no_telp, $foto, $created_at);
            $stmt->fetch();
            $user = array();
            $user["username"] = $username;
            $user["email"] = $email;
            $user["nama"] = $nama;
            $user["kategori"] = $kategori;
			$user["alamat"] = $alamat;
			$user["no_telp"] = $no_telp;
			$user["foto"] = $foto;
            $user["created_at"] = $created_at;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching all user timelines
     * @param String $user_id id of the user
     */
    public function getAllTimeline() {
        //$stmt = $this->conn->prepare("SELECT mt.username, mt.kd_mintol, mt.judul,mt.deskripsi,k.nama_kategori,mt.foto FROM kon_kat_mintol kkm INNER JOIN minta_tolong mt ON kkm.kd_mintol=mt.kd_mintol LEFT JOIN kategori k ON kkm.kd_kategori=k.kd_kategori");
		$stmt = $this->conn->prepare("SELECT mt.username, mt.kd_mintol, mt.judul,mt.deskripsi,k.nama_kategori,mt.foto FROM kon_kat_mintol kkm INNER JOIN minta_tolong mt ON kkm.kd_mintol=mt.kd_mintol LEFT JOIN trx_pertolongan tp ON mt.kd_mintol=tp.kd_mintol LEFT JOIN kategori k ON kkm.kd_kategori=k.kd_kategori WHERE mt.status=0 GROUP BY mt.kd_mintol ORDER BY mt.createAt DESC ");
        $stmt->execute();
        $timeline = $stmt->get_result();
        $stmt->close();
        return $timeline;
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
     * Fetching all chat
     * @param String $user_id id of the user
     */
    public function getAllChat($user_id) {
        //$stmt = $this->conn->prepare("SELECT mt.username, mt.kd_mintol, mt.judul,mt.deskripsi, u.foto, m.penolong,m.ditolong FROM kon_kat_mintol kkm INNER JOIN minta_tolong mt ON kkm.kd_mintol=mt.kd_mintol INNER JOIN moment m ON mt.kd_mintol=m.kd_mintol INNER ");
        $stmt = $this->conn->prepare("SELECT u.username, u.foto, mt.judul, mt.deskripsi, mt.createAt, tp.status, m.penolong, m.ditolong FROM user u INNER JOIN minta_tolong mt ON u.username=mt.username LEFT JOIN moment m ON mt.kd_mintol=m.kd_mintol INNER JOIN trx_pertolongan tp ON mt.kd_mintol=tp.kd_mintol WHERE u.username='$user_id' AND (tp.username='$user_id' OR m.ditolong='$user_id')");

		$stmt->execute();
        $timeline = $stmt->get_result();
        $stmt->close();
        return $timeline;
    }

	/**
     * Fetching all leader
     * @param String $user_id id of the user
     */
    public function getLeader() {
        $stmt = $this->conn->prepare("SELECT kon_history_poin.username , kon_history_poin.poin_masuk , user.foto FROM kon_history_poin INNER JOIN user ON kon_history_poin.username=user.username ORDER BY kon_history_poin.poin_masuk DESC");
        $stmt->execute();
        $timeline = $stmt->get_result();
        $stmt->close();
        return $timeline;
    }
	/**
     * Fetching all user moment
     * @param String $user_id id of the user
     */
    public function getLeadUser($user_id) {

		//$stmt = $this->conn->prepare("SELECT kon_history_poin.username , kon_history_poin.poin_masuk , user.foto FROM kon_history_poin INNER JOIN user ON kon_history_poin.username=user.username WHERE kon_history_poin.username=?");
		$stmt = $this->conn->prepare("SELECT * FROM kon_history_poin  WHERE username=?");
        $stmt->bind_param("s", $user_id);
        if ($stmt->execute()) {
            $res = array();
            $stmt->bind_result($username, $poin_masuk, $foto);
            // TODO
            // $timeline = $stmt->get_result()->fetch_assoc();
            $stmt->fetch();
            $result["username"] = $username;
            $result["poin_masuk"] = $poin_masuk;
            $result["foto"] = $foto;
            $stmt->close();
            return $result;
        } else {
            return NULL;
        }
    }

    /**
     * Updating timeline
     * @param String $timeline_id id of the timeline
     * @param String $timeline timeline text
     * @param String $status timeline status
     */


	/**
     * Creating new timeline
     * @param String $user_id user id to whom timeline belongs to
     * @param String $timeline timeline text
     */
    public function createMomen($user_id, $judul, $deskripsi, $foto, $penolong, $ditolong) {
        $stmt = $this->conn->prepare("INSERT INTO moment (kd_moment, judul, foto, penolong, ditolong, deskripsi, tgl) VALUES('',?,?,?,?,?,'')");
        $stmt->bind_param("sssss", $judul, $foto, $penolong, $ditolong, $deskripsi);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return TRUE;
        } else {
            // timeline failed to create
            return NULL;
        }
    }

    public function updateUser($user_id, $email, $nama, $kategori, $alamat, $no_telp, $foto) {
        $stmt = $this->conn->prepare("UPDATE user  set email = ?, nama = ?, kategori = ?, alamat = ?, no_telp = ?, foto = ? WHERE username = ?");
        $stmt->bind_param("sssssss", $email, $nama,  $kategori, $alamat, $no_telp, $foto, $user_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

	/**
     * Creating new chat
     * @param String $user_id user id to whom chat belongs to
     * @param String $chat chat text
     */
    public function createChat($kd_mintol, $user_id, $status) {
        $stmt = $this->conn->prepare("INSERT INTO trx_pertolongan (kd_mintol, username, status) VALUES(?,?,?)");
        $stmt->bind_param("sss", $kd_mintol, $user_id, $status);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return TRUE;
        } else {
            // timeline failed to create
            return NULL;
        }
    }

	/**
     * Konfirmasi Pertolongan
     * @param String $user_id user id to whom chat belongs to
     * @param String $chat chat text
     */
    public function konfirmasiPertolongan($kd_mintol, $user_id) {
        $stmt = $this->conn->prepare("UPDATE minta_tolong SET status='1' WHERE kd_mintol=?");
        $stmt->bind_param("s", $kd_mintol);
        $result0 = $stmt->execute();
        $stmt->close();

		$stmt = $this->conn->prepare("UPDATE trx_pertolongan SET status='1' WHERE kd_mintol=? AND username=?");
        $stmt->bind_param("ss", $kd_mintol, $user_id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result0 /*&& $result*/) {
            return TRUE;
        }elseif(!$result0){
			echo 'RESULT0';
			return NULL;
		}elseif(!$result){
			echo 'RESULT';
			return NULL;
		} else {
            // timeline failed to create
            return NULL;
        }
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
