<?php
    class RouterController extends Controller {

      protected $controller;

        public function parse($params){
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
          $this->data['title'] = $this->controller->header['page_title'];
          $this->data['desc'] = $this->controller->header['page_desc'];
          $this->data['keywords'] = $this->controller->header['page_keywords'];
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
