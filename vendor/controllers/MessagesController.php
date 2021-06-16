<?php
  class MessagesController extends Controller
  {

      public function parse($params)
      {
      $messageManager = new MessageManager();
      $userManager = new UserManager();
          if (!empty($params)) {
            $m = $messageManager->returnMessageById($params[0]);
            if ($m['user_receiverid'] == $userManager->returnUser()['user_id']) {
              $this->view = "message";
              $this->data['message'] = $m;
            } else {
              HTTP::status(403);
              $this->redir("status/403");
            }
          } else if ($userManager->returnUser()) {
            $this->view = "messages";
          } else {
            HTTP::status(403);
            $this->redir("status/403");
          }

      }
  }
