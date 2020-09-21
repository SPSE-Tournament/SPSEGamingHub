<?php
  class ErrorController extends Controller
  {
      public function parse($params)
      {

        if (empty($params)) {
          $this->header['page_title'] = 'Error';
          $this->data['error'] = "si";
          $this->view = 'errorpage';
        } else {
          $this->view = "errorpage";
        }

      }
  }
