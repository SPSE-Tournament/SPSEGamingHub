<?php
    class FileManager {
      public function uploadFile(array $file,bool $image, array $allowedExtensions, $name) {
        $uploads = "../SPSEGamingHub/public/uploads/";
        $ext = strtolower(pathinfo(basename($file['name']), PATHINFO_EXTENSION));
        $targetFile = $uploads . $name . ".".$ext;
        if ($image) {
          if (getimagesize($file['tmp_name'])) {
              if ($file['size'] < 5000000) {
                if (in_array($ext, $allowedExtensions)) {
                  if (move_uploaded_file($file['tmp_name'],$targetFile)) {
                    return "public/uploads/$name".".".$ext;
                  } else {
                    throw new UserError("Move failed");
                  }
                } else {
                  throw new UserError("Bad file extension");
                }
              } else {
                throw new UserError("File too big");
              }
          } else {
            throw new UserError("File not an image");
          }
        } else {
          if ($file['size'] < 5000000) {
            if (in_array($ext, $allowedExtensions)) {
              if (move_uploaded_file($file['tmp_name'],$targetFile)) {
                return "public/uploads/$name".".".$ext;
              } else {
                throw new UserError("Move failed");
              }
            } else {
              throw new UserError("Bad file extension");
            }
          } else {
            throw new UserError("File too big");
          }
        }
      }

    }


?>
