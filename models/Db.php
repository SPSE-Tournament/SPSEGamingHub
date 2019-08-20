<?php
  class Db {
    private static $connection;

    private static $settings = array(
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
      PDO::ATTR_EMULATE_PREPARES => false
    );

    public static function connect($host, $user, $pw, $db) {
          if (!isset(self::$connection)) {
            self::$connection = @new PDO("mysql:dbname=$db;host=$host",$user,$pw,self::$settings);
          }
    }

    public static function singleQuery($query, $params = array()) {
      $return = self::$connection->prepare($query);
      $return->execute($params);
      return $return->fetch();
    }

    public static function multiQuery($query, $params = array()) {
      $return = self::$connection->prepare($query);
      $return->execute($params);
      return $return->fetchAll();
    }

    public static function singleColQuery($query, $params = array()) {
      $return = self::singleQuery($query, $params);
      return $return[0];
    }

    public static function query($query, $params = array()) {
      $return = self::$connection->prepare($query);
      $return->execute($params);
      return $return->rowCount();
    }

    public static function insert($table, $params = array()) {
      return self::query("INSERT INTO `$table` (`" .
       implode('`, `', array_keys($params)).
       "`) VALUES (".str_repeat('?,', sizeOf($params)-1)."?)",
               array_values($params));
    }

    public static function edit($table, $values = array(), $condition, $params = array()) {
      return self::query("UPDATE `$table` SET `".
      implode('` = ?, `', array_keys($values)).
      "` = ? " . $condition,
      array_merge(array_values($values), $params));
    }

    public static function getLastId() {
        return self::$connection->lsatInsertId();
    }


  }

?>
