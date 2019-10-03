<?php
    class UserManager {

          public function returnUsers() {
            return Db::multiQuery("select user_id, name, email, name_r, surname, admin, user_hexid from users");
          }

          public function returnHash($pw) {
            return password_hash($pw, PASSWORD_DEFAULT);
          }

          public function register($name, $email, $pw, $pwA, $yr, $hexId) {
              if ($name == "" or $name == " ") {
                throw new UserError("Přezdívka je povinné pole!");
              }
              if ($yr != date("Y")) {
                throw new UserError("Chybně vyplněn antispam!");
              }
              if ($pw != $pwA) {
                throw new UserError("Hesla se neshodují!");
              }
              if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new UserError("Email nebyl zadán ve správném formátu!");
              }
              $user = array(
                'name' => $name,
                'email' => $email,
                'name_r' => "",
                'surname' => "",
                'password' => $this->returnHash($pw),
                'admin' => 0,
                'user_hexid' => $hexId
              );
              try {
                Db::insert('users', $user);
              } catch (PDOException $e) {
                throw new UserError("Uživatel s tímto jménem již existuje.");
              }
          }

          public function login($name, $password) {
            $user = $this->selectUser($name);
            if (!$user or !password_verify($password, $user['password'])) {
              throw new UserError("Neplatné údaje!");
            }
            $_SESSION['user'] = $user;
          }

          public function logout() {
              unset($_SESSION['user']);
          }


          public function selectUser($name) {
            $user = Db::singleQuery('SELECT user_id, name, email, name_r, surname, admin, password, user_hexid FROM users where name = ?', array($name));
            return $user;
          }

          public function generateHexId() {
            $currentHexIds = Db::multiQuery("select user_hexid from users");
            $curIds = array();
            for ($i=0; $i < count($currentHexIds); $i++) {
                $curIds[] = $currentHexIds[$i]['user_hexid'];
            }
              $hexId = bin2hex(random_bytes(2));
              if (in_array($hexId, $curIds)) {
                return strtoupper($hexId);
              } else {
                do {
                  $hexId = bin2hex(random_bytes(2));
                } while (in_array($hexId, $curIds));
                return strtoupper($hexId);
              }
          }

          public function returnUser() {
            if (isset($_SESSION['user'])) {
              return $_SESSION['user'];
            }
            return null;
          }

          public static function authAdmin() {
            if (!isset($_SESSION['user'])) {
              return false;
            } else {
              $admin = Db::singleQuery("Select admin from users where user_id = ?", array($_SESSION['user']['user_id']));
              $auth = ($admin[0] == 1) ? true : false;
              return $auth;
            }

          }
          //to be done when I get some fucking sleep
          /*
          public function liveSearchUsers($str) {

              if (strlen($str) > 0) {
                  $users = $this->returnUsers();

              }
          } */

    }








?>
