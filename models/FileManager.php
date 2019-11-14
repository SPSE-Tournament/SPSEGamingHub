<?php
    class FileManager {
      public function uploadFile(array $file,bool $image, array $allowedExtensions, $name) {
        $uploads = "../SPSEGamingHub/public/uploads/";
        $ext = strtolower(pathinfo(basename($file['name']), PATHINFO_EXTENSION));
        $targetFile = $uploads . $name . ".".$ext;
        $response = "";
        if ($image) {
          if (getimagesize($file['tmp_name'])) {
              if ($file['size'] < 5000000) {
                if (in_array($ext, $allowedExtensions)) {
                  if (move_uploaded_file($file['tmp_name'],$targetFile)) {
                    $response = "public/uploads/$name".".".$ext;
                  } else {
                    $response = "Move failed";
                  }
                } else {
                  $response = "Bad file extension";
                }
              } else {
                $response = "File too big";
              }
          } else {
            $response = "File not an image";
          }
        } else {
          if ($file['size'] < 5000000) {
            if (in_array($ext, $allowedExtensions)) {
              if (move_uploaded_file($file['tmp_name'],$targetFile)) {
                $response = "public/uploads/$name".".".$ext;
              } else {
                $response = "Move failed";
              }
            } else {
              $response = "Bad file extension";
            }
          } else {
            $response = "File too big";
          }
        }
        return $response;
      }

    }


?>
