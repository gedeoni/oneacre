<?php
/**
*Class to create and issue database connections
*This would force the system to use an available database instance or create a new one
**/

header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$_POST = json_decode(file_get_contents("php://input"),true);


class Database{

   //singleton design pattern
   private static $dbh = NULL;
   private $conn =null;

   private function __construct(){       
      
      //$this->conn = new
      //This is for the production
      //$this->conn = new PDO("mysql:host=localhost;port=3306;dbname=scandale","root","");
      $this->conn = new PDO("mysql:host=localhost;port=3306;dbname=u360325267_rca","u360325267_ged","Misererenobis1!");
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   }

   /**
   *Method to get a database instance or create one if none is to be found
   *@param void
   *@return database connection instance
   */
   public static function getInstance(){
      if(NULL === self::$dbh){
         self::$dbh = (new Database())->conn;
      }
      return self::$dbh;
   }

   /**
     * This function gets the id of the newly registered user 
   */
   public static function getId($column, $table)
   {
        $db = self::getInstance();
        try {
            $stmt = $db->prepare("SELECT ".$column." from ".$table." ORDER BY id DESC LIMIT 1");
                  $stmt->execute();
             $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res !== false) {
                return $res['id'];
            }
            return false;
        } catch (PDOException $e) {
             echo $e->getMessage();
             return false;
        }
   }

   /**
   *Method to update a particular column in a particular table
   **/
   public static function UpdateColumn($table, $column, $value, $condition)
   {
         $db = self::getInstance();
         try {
            $stmt = $db->prepare("update ".$table." set " .$column. " = :trans_type where id = :msisdn");
            $params = array(":msisdn"=>$condition,":trans_type"=>$value);
            $stmt->execute($params);
            if ($stmt->rowCount() > 0) {
               return true;
            }
         } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
         }
   }


   /**
    * A function to delete a row from a particular table 
   */
   public static function delete($tableName,$column, $columnValue){
      $db = self::getInstance();
      try {
            $stmt = $db->prepare("Delete FROM ".$tableName." where ".$column." = :id");
            $stmt->bindParam(":id", $columnValue);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
               return true;
            }
            return false;
      }catch (PDOException $e) {
            echo $e->getMessage();
            return false;
      }
   }

   /**
    * This function can be modified to query any info related to a primary key
    */

   public static function queryPK($colSearch, $criterial, $criterialVal,  $tableName)
   {
      $db = self::getInstance();
      try {
            $stmt = $db->prepare("SELECT ".$colSearch." FROM ".$tableName." where ".$criterial." = :id");
            $stmt->bindParam(":id", $criterialVal);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res !== false) {
               return $res[$colSearch];
            }
            return false;
      } catch (PDOException $e) {
            #echo $e->getMessage();
            return false;
      }
   }
}
