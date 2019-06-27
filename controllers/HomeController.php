<?php
  class HomeController extends Controller {
      public function parse($params) {
      $this->header['page_title'] = "SPSE GAMING HUB";
      $this->header['page_desc'] = "Uvodni strana";
      $this->view = "home";
    }
  }





 ?>
