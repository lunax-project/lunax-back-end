<?php

class DBConnect
{
  private static $PDO = null;

  /**
   * Get configurations of file by direcory
   */
  private function getFileConfigDB($dir) {
      $filename = implode([$dir, 'configs', 'database.json'], DS);
      return file_exists($filename) ? $filename : false;
  }

  /**
   * Get configurations of database
   */
  private static function getConfigDB()
  {
      if (!$fileConfigs = self::getFileConfigDB(APPDIR)) {
          $fileConfigs = self::getFileConfigDB(LUNAXDIR);
      }

      if ($fileConfigs) {
          return json_decode(file_get_contents($fileConfigs));
      } else {
          Utils::error('Database configs not found');
      }
  }

  public function run($query, $param)
  {
      $paramLog = json_encode($param);

      $message = (
          PHP_EOL . "Query: $query" .
          PHP_EOL . "Param: $paramLog" .
          PHP_EOL . 'Status: '
      );

      $qPdo = self::$PDO->prepare($query);

      if ($qPdo->execute($param)) {
          Utils::log("$message Success!");
      } else {
          Utils::log("$message Error!");
      }

      return $qPdo;
  }

  public static function connect()
  {
    if (is_null(self::$PDO)) {
      self::$PDO = 10;

      # Dados da configuração com o banco de dados
      $dbConfig = self::getConfigDB();

      try {
          self::$PDO = new PDO(
            'mysql:host=' . $dbConfig->host .
            ';dbname='    . $dbConfig->db,
                            $dbConfig->user,
                            $dbConfig->pass,
            [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
          );
      } catch (PDOException $e) {
        Utils::error('Database connection failed: ' . $e->getMessage(), true);
      }
    }
  }
}
