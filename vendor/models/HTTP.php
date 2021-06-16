<?php
  class HTTP {

    public static $codes = [400 => "Bad Request",401 => "Unauthorized",403 => "Forbidden",404 => "Not Found"];

    public function status(int $statusCode) {
      switch ($statusCode) {
        case 403:
          header("HTTP/1.1 403 Forbidden");
          break;
          case 401:
            header("HTTP/1.1 401 Unauthorized");
            break;
          case 400:
            header("HTTP/1.1 400 Bad Request");
            break;
            case 404:
            header("HTTP/1.1 404 Not Found");
            break;
      }

    }

  }


?>
