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

                   public function addMessage($zprava) {
                       if (isset($_SESSION['zpravy'])) {
                         $_SESSION['zpravy'][] = $zprava;
                       } else {
                         $_SESSION['zpravy'] = array($zprava);
                       }
                     }

                   public static function returnMessages() {
                     if (isset($_SESSION['zpravy'])) {
                       $zpravy = $_SESSION['zpravy'];
                       unset($_SESSION['zpravy']);
                       return $zpravy;
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

                   public function log() {
                     $logMan = new LogManager();
                     $logMan->log();

                   }




  }


 ?>
