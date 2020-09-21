<?php
  class StatusController extends Controller
  {
      public function parse($params)
      {
          if (!empty($params) && array_key_exists($params[0], HTTP::$codes)) {
              $this->header['page_title'] = "Status " . $params[0];
              $this->data['status'] = $params[0] . " " .  HTTP::$codes[$params[0]];
              $this->view = "errorpage";
          } else {
              $this->header['page_title'] = 'Error 404';
              $this->data['status'] =  "404 Not Found";
              $this->view = 'errorpage';
          }
      }
  }
