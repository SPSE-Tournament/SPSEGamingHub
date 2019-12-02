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
            return $realEmails;
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

          public function register($name, $email, $pw, $hexId, $verification) {
              $user = array(
                'name' => $name,
                'email' => $email,
                'password' => $pw,
                'admin' => 0,
                'user_hexid' => $hexId,
                'user_verified'=>$verification
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
              $html ="
              <html>
              <head>
              <title>SPSEGameHub Email verification</title>
              </head>
              <body>
              <p>Hello fellow gamer! To verify your account created on
              our website,
              <a href='https://www.domasoftware.tk/register/verify/".$hash."'>Click here</a>
              </p>
              </body>
              </html>";
              try {
                Db::insert('registrations', $user);
                $mail = new PHPMailer(true);
                $mail->isSMTP(); // Send using SMTP
                $mail->Host       = 'smtp.office365.com'; // Set the SMTP server to send through
                $mail->SMTPAuth   = true; // Enable SMTP authentication
                $mail->Username   = 'roudnydo@zaci.spse.cz'; // SMTP username
                $mail->Password   = '0Pice123';  // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
                $mail->Port       = 587; // TCP port to connect to
                //Recipients
                $mail->setFrom('roudnydo@zaci.spse.cz');
                $mail->addAddress($email);
                // Content
                $mail->isHTML(true); // Set email format to HTML
                $mail->Subject = 'SPSEGameHub Email verification';
                $mail->Body    = $html;
                $mail->AltBody = $html;
                $mail->send();
              } catch (Exception $e) {
                throw new UserError("Username already exists.");
              }
          }

          public function userExistsHex($hexId):bool {
            return Db::query("SELECT user_id from users where user_hexid = ?", array(mb_strtoupper($hexId)));
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
            return Db::singleQuery('SELECT user_id, name, email, name_r, surname, admin, watchman, rootmaster, password, user_hexid, user_verified FROM users where name = ?', array($name));
          }

          public function selectUserHex($hex) {
            return Db::singleQuery('SELECT user_id, name, email, name_r, surname, admin, watchman, rootmaster, password, user_hexid, user_verified FROM users where user_hexid = ?', array($hex));
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
              $strL = mb_strtolower($str);
              if (strlen($strL) >= 3) {
                  $users = $this->returnUsers();
                  $usersToReturn = array();
                  foreach ($users as $user) {
                    if (mb_strtolower(substr($user['name'], 0, strlen($strL))) == $strL)
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

          public function verifyUser($hexId):void {
            Db::edit("users",array("user_verified"=>1), "where user_hexid = ?", array($hexId));
          }

          public function adminUser($hexId,$adminType):void {
            if ($adminType == "watchman")
              Db::edit("users",array("watchman"=>1, "admin"=>"0"), "where user_hexid = ?", array($hexId));
            else if ($adminType == "admin")
            Db::edit("users",array("admin"=>1, "watchman"=>"0"), "where user_hexid = ?", array($hexId));
            else
            throw new UserError("Wrong admin type.");
          }

          public function parseHexname($hexName):array {
              $parsedName = explode("#", $hexName);
              return array(
                'name' => $parsedName[0],
                'hexid' => $parsedName[1]
              );
          }

    }








?>
