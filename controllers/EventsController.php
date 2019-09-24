<?php
  class EventsController extends Controller {
    public function parse($params) {
      $eventManager = new EventManager();
      $this->data['admin'] = $_SESSION['admin'];
      if (!empty($params[0]) && $params[0] == 'hello') {
        $this->view = "event";
      } else {
        $this->data['events'] = $eventManager->returnEvents();
        $this->view = "events";
      }




    }


  }

?>
