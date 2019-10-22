<?php
    class RouterController extends Controller {

      protected $controller;

        public function parse($params){
          $userMan = new UserManager();
          $mesMan = new MessageManager();
          $date = new DateTime();
          $parsedU = $this->parseURL($params[0]);
          if (empty($parsedU[0])) {
            $this->redir('home');
          }

          $controllerClass = $this->toCamelCase(array_shift($parsedU)) . 'Controller';
          if (file_exists('controllers/'.$controllerClass . '.php')) {
            $this->controller = new $controllerClass;
          } else {
            $this->redir('error');
          }



          $this->controller->parse($parsedU);

          if (isset($_POST['message-add'])) {
            $parsedHexName = $userMan->parseHexname($_POST['receiver']);
            $receiverId = $userMan->selectUser($parsedHexName['name']);
            $mesMan->sendMessage($_POST['message'],'message', $date->format('Y-m-d H:i:s'),$_SESSION['user']['user_id'],$_SESSION['user']['name'],$receiverId['user_id'],$parsedHexName['name']);
            $this->addMessage("Your message has been sent.");
            $this->log("Message sent. Sender: ".$_SESSION['user']['name']."#".$_SESSION['user']['user_hexid'].', Receiver: '. $_POST['receiver'] . ", Message: ". $_POST['message'] ,'message_sent');
            $this->redir("profile");
        }

          if ($this->checkLogged()) {
            $this->data['usrname'] = $_SESSION['user']['name'];
          }
          $this->checkAdmin();
          $this->data['title'] = $this->controller->header['page_title'];
          $this->data['desc'] = $this->controller->header['page_desc'];
          $this->data['keywords'] = $this->controller->header['page_keywords'];
          $this->data['messages'] = $this->returnMessages();
          $this->data['logged'] = $_SESSION['logged'];
          if (isset($_SESSION['user'])) {
            $this->data['user'] = $_SESSION['user'];

          }
            $this->view = "layout";
        }

        private function parseURL($url) {
          $prsU = parse_url($url);
          $prsU['path'] = trim(ltrim($prsU['path'], "/"));
          $divPath = explode("/", $prsU['path']);
          return $divPath;
        }

        private function toCamelCase($txt) {
          $sent = str_replace('-', ' ', $txt);
          $sent = ucwords($sent);
          $sent = str_replace(' ', '', $sent);
          return $sent;
        }








    }







 ?>
