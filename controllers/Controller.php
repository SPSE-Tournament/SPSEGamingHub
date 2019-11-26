<?php
  abstract class Controller {
      protected $data = array();
      protected $view = "";
      protected $header = array('page_title' => '', 'page_keywords' => '', 'page_desc' => '');

       abstract function parse($params);

       #Sanitizes all view variables to htmlspecialchars, if want == nosanitize then use prefix $_
       private function sanitize($x = null) {
         if (!isset($x)) {
           return null;
         } elseif(is_string($x)) {
           return htmlspecialchars($x, ENT_QUOTES);
         } elseif(is_array($x)) {
           foreach ($x as $key => $v) {
             $x[$key] = $this->sanitize($v);
           }
           return $x;
         } else {
           return $x;
         }
       }
                   public function showView() {
                     if ($this->view) {
                       extract($this->sanitize($this->data));
                       extract($this->data, EXTR_PREFIX_ALL, "");
                       require("views/" . $this->view . ".phtml");
                     }
                   }

                   public function redir($where) {
                     header("Location: /$where");
                     header("Connection: close");
                     exit;
                   }

                   public function addMessage($message) {
                       if (isset($_SESSION['messages'])) {
                         $_SESSION['messages'][] = $message;
                       } else {
                         $_SESSION['messages'] = array($message);
                       }
                     }

                   public static function returnMessages() {
                     if (isset($_SESSION['messages'])) {
                       $messages = $_SESSION['messages'];
                       unset($_SESSION['messages']);
                       return $messages;
                     } else {
                       return array();
                     }
                   }

                   public function authenticateUser($admin = false) {
                     $userManager = new UserManager();
                     $usr = $userManager->returnUser();
                     if (!$usr or ($admin and !$usr['admin'])) {
                       $this->addMessage("Nedostatečná oprávnění.");
                       $this->redir('login');
                     }
                   }

                   public function log($msg, $type) {
                     $logMan = new LogManager();
                     $date = new DateTime();
                     $logMan->log($_SESSION['user']['user_id'], $msg, $type, $date->format('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR']);
                   }

                   public function logDifferentUser($userId,$msg, $type) {
                     $logMan = new LogManager();
                     $date = new DateTime();
                     $logMan->log($userId, $msg, $type, $date->format('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR']);
                   }

                   public function checkLogged() {
                     if (isset($_SESSION['user'])) {
                       $_SESSION['logged'] = true;
                       return true;
                     } else {
                       $_SESSION['logged'] = false;
                       return false;
                     }
                   }

                   public function checkAdmin() {
                     if (isset($_SESSION['user']) && $_SESSION['user']['admin'] == 1) {
                       $_SESSION['admin'] = true;
                     } else {
                       $_SESSION['admin'] = false;
                     }
                   }

                   public function isParam($paramIndex, $paramQuery) {
                     if (!empty($params[$paramIndex]) && $params[$paramIndex] == "$paramQuery") {
                       return true;
                     } else {
                       return false;
                     }
                   }

  }


 ?>
