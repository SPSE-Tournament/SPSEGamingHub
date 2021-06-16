<?php
  class StringManager {
      public function stripSpaces(string $string) {
        return implode("",explode(" ", $string));
      }


  }


?>
