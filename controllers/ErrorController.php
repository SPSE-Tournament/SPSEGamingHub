<?php
  class ErrorController extends Controller {
    public function parse($params)
     {
         header("HTTP/1.0 404 Not Found");
         $this->header['page_title'] = 'Chyba 404';
         $this->view = 'errorpage';
     }
  }









?>
