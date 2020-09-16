<?php
  class HTTP {

    public function status(int $statusCode) {
      switch ($statusCode) {
        case 403:
          header("HTTP/1.1 403 Forbidden");
          exit;
          break;
          case 401:
            header("HTTP/1.1 401 Unauthorized");
            exit;
            break;
          case 400:
            header("HTTP/1.1 400 Bad Request");
      }

    }

  }


?>
