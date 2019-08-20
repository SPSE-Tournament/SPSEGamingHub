<?php
  class HomeController extends Controller {
      public function parse($params) {
      $this->header['page_title'] = "SPSE Gaming Hub";
      $this->header['page_desc'] = "Uvodni strana";
      $this->view = "home";
    }
  }





 ?>
