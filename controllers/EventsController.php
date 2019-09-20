<?php
  class EventsController extends Controller {
    public function parse($params) {
      $eventManager = new EventManager();


      $this->data['events'] = $eventManager->returnEvents();
      $this->view = "events";
    }


  }

?>
