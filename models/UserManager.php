<?php
    class UserManager {

          public function returnUsers() {
            return Db::multiQuery("SELECT user_id, name, email, name_r, surname, admin, watchman, rootmaster, user_hexid, user_verified from users");
          }

          public function returnEmails(){
            $emails = Db::multiQuery("SELECT email from users");
            $realEmails = array();
            foreach ($emails as $uname) {
              $realEmails[] = $uname['email'];
            }
            return $realEnames;
          }

          public function returnUsernames(){
            $unames = Db::multiQuery("SELECT name from users");
            $realUnames = array();
            foreach ($unames as $uname) {
              $realUnames[] = $uname['name'];
            }
            return $realUnames;
          }

          public function returnHash($pw) {
            return password_hash($pw, PASSWORD_DEFAULT);
          }

          public function returnRegistrationByHash($hash) {
            return Db::singleQuery("SELECT * from registrations where user_hash = ?", array($hash));
          }

          public function returnRegistrationHashes() {
            $regs = Db::multiQuery("SELECT * from registrations");
            $regHashes = array();
            foreach ($regs as $reg) {
              $regHashes[] = $reg['user_hash'];
            }
            return $regHashes;
          }

          public function register($name, $email, $pw, $hexId) {
              $user = array(
                'name' => $name,
                'email' => $email,
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

          public function requestRegister($name, $email, $pw, $pwA, $yr) {
              $hash = strtoupper(bin2hex(random_bytes(64)));
              if ($name == "" or $name == " ") {
                throw new UserError("Username empty!");
              }
              if ($yr != date("Y")) {
                throw new UserError("Current year doesn't match!");
              }
              if ($pw != $pwA) {
                throw new UserError("Passwords don't match.");
              }
              $user = array(
                'user_name' => $name,
                'user_email' => $email,
                'user_password' => $this->returnHash($pw),
                'user_hash'=> $hash
              );
              try {
                Db::insert('registrations', $user);
                mb_send_mail($email, "SPSEGameHub email verification", "Hello fellow gamer! To verify your account created on
                our website, <a href='https://www.game.spse.cz/profile/verify/$hash'>Click here<a/>");
              } catch (PDOException $e) {
                throw new UserError("Username already exists.");
              }
          }

          public function login($name, $password) {
            $user = $this->selectUser($name);
            if (!$user || !password_verify($password, $user['password'])) {
              throw new UserError("Invalid combination.");
            }
            $_SESSION['user'] = $user;
          }

          public function logout() {
              unset($_SESSION['user']);
          }


          public function selectUser($name) {
            $user = Db::singleQuery('SELECT user_id, name, email, name_r, surname, admin, watchman, rootmaster, password, user_hexid, user_verified FROM users where name = ?', array($name));
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
              $admin = Db::singleQuery("SELECT admin, watchman, rootmaster from users where user_id = ?", array($_SESSION['user']['user_id']));
              return ($admin['admin'] == 1 or $admin['rootmaster'] == 1) ? true : false;
            }

          }

          public function liveSearchUsers($str) {
              if (strlen($str) >= 3) {
                  $users = $this->returnUsers();
                  $usersToReturn = array();
                  foreach ($users as $user) {
                    if (substr($user['name'], 0, strlen($str)) == $str)
                        $usersToReturn[] = $user['name']."#".$user['user_hexid'];
                  }
                  if (count($usersToReturn) > 0)
                    return $usersToReturn;
                    else {
                    $usersToReturn[] = "No hint suggested";
                    return $usersToReturn;
                }
              } else {
                $usersToReturn[] = " ";
                return $usersToReturn;
              }
          }

          public function parseHexname($hexName) {
              $parsedName = explode("#", $hexName);
              return array(
                'name' => $parsedName[0],
                'hexid' => $parsedName[1]
              );
          }

    }








?>
