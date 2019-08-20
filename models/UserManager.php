<?php
    class UserManager {
          public function returnHash($pw) {
            return password_hash($pw, PASSWORD_DEFAULT);
          }

          public function register($name, $email, $nameR, $surName, $pw, $pwA, $yr) {
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
                'name_r' => $nameR,
                'surname' => $surName,
                'password' => $this->returnHash($pw)
              );
              try {
                Db::insert('users', $user);
              } catch (PDOException $e) {
                throw new UserError("Uživatel s tímto jménem již existuje.");
              }
          }

          public function login($name, $password) {
            $user = Db::singleQuery('SELECT user_id, name, email, name_r, surname, admin, password FROM users where name = ?', array($name));
            if (!$user or !password_verify($password ,$user['password'])) {
              throw new UserError("Neplatné údaje!");
            }
            $_SESSION['user'] = $user;
          }

          public function selectUser($name) {
            $user = Db::singleQuery('SELECT user_id, name, email, name_r, surname, admin, password FROM users where name = ?', array($name));
            return $user;
          }

          public function logout() {
              unset($_SESSION['user']);
          }

          public function returnUser() {
            if (isset($_SESSION['user'])) {
              return $_SESSION['user'];
            }
            return null;
          }

    }








?>
