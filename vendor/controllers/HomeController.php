<?php
  class HomeController extends Controller
  {
      public function parse($params)
      {
          $this->header['page_title'] = "SPSE Gaming Hub";
          $this->header['page_desc'] = "SPSE Gaming Hub - Homepage";
          $this->header['page_keywords'] = "SPSE Gaming, SPSE Esport, SPSE Gaming Events, SPSE Esport Events, SPŠE Esport, SPŠE Gaming, SPŠE Gaming Events, SPŠE Esport Events,";
          $this->view = "home";
      }
  }
