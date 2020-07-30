<?php

require_once './../db/Database.php';

Class Service{

    // variables representing a user
    protected $id;
    protected $name;
    protected $db;

    function __construct() {
        $this->db = Database::getInstance();
    }


    public function __destruct()
    {

    }

    /** Get summaries for a specific customer 
    * @ param customerId
    */
    public function getcustomersummary($cusId){
        try {
            $stmt = $this->db->prepare("SELECT TotalRepaid, TotalCredit, (SELECT SeasonName FROM Seasons where seasonid=cust.seasonid) FROM CustomerSummaries where customerid = :id");
            $stmt->bindParam(":id", $cusId);
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res !== false) {
                return json_encode(['summary'=>$res, 'success'=>true ]);
            }
            return json_encode(['success'=>false ]);
        } catch (PDOException $e) {
            echo $e->getMessage();
            return json_encode(['success'=>false ]);
        }
    }
    
     /** Get all customer summaries */
     public function getcustomersummaries(){
        try {
            $stmt = $this->db->prepare("SELECT cust.TotalRepaid, cust.TotalCredit,cust.customerid, (TotalCredit-TotalRepaid) as owes, (SELECT seasonname FROM seasons where seasonid=cust.seasonid) as seasonname, (SELECT customername FROM customers where customerid= cust.customerid) as name, cust.seasonid FROM customersummaries as cust");
            $stmt->execute();
            $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($res !== false) {
                return $res;
            }
            return false;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /** Only get the customer summaries with outstanding debt for all customers  
    * @ param customerId
    */
    public function getallcustomersummarydebt(){  
         try {
            $stmt = $this->db->prepare("SELECT cust.TotalRepaid, cust.customerid, cust.TotalCredit, (TotalCredit-TotalRepaid) as owes, (SELECT seasonname FROM seasons where seasonid=cust.seasonid) as seasonname, (SELECT customername FROM customers where customerid= cust.customerid) as name, cust.seasonid FROM customersummaries as cust where  TotalCredit > TotalRepaid");
            $stmt->execute();
            $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($res !== false) {
                return $res;
            }
            return false;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }
    
    /** Only get the customer summaries with outstanding debt 
    * @ param customerId
    */
    public function getcustomersummarydebt($cusId){
        try {
            $stmt = $this->db->prepare("SELECT cust.id, cust.TotalRepaid, cust.customerid, cust.TotalCredit, (TotalCredit-TotalRepaid) as owes, (SELECT seasonname FROM seasons where seasonid=cust.seasonid) as seasonname, (SELECT customername FROM customers where customerid= cust.customerid) as name, cust.seasonid FROM customersummaries as cust where  TotalCredit > TotalRepaid and  customerid = :id");
            $stmt->bindParam(":id", $cusId);
            $stmt->execute();
            $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($res !== false) {
                return $res;
            }
            return false;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /* Creating a payment record */ 
    public function createrepaymentrec($data){
        try {
            $stmt = $this->db->prepare("insert into repayments(customerid, seasonid, amount, parentid) values (:customerid, :seasonid, :amount, :parentid)");
            $params = array(":customerid"=>$data['customerid'],":seasonid"=>$data['seasonid'], ":amount"=>$data['amount'],  ":parentid"=>$data['parentid']);
            $stmt->execute($params);
            if ($stmt->rowCount() > 0) {
               return true;
            }
            return false; 
        } catch (PDOException $e) {
            echo $e;
            return false;
        }
    }


    /* Creating a repayment upload record */ 
    public function createrepaymentupload($data){
        try {
            $stmt = $this->db->prepare("insert into repaymentuploads(customerid, seasonid, amount) values (:customerid, :seasonid, :amount)");
            $params = array(":customerid"=>$data['customerid'],":seasonid"=>$data['seasonid'], ":amount"=>$data['amount']);
            $stmt->execute($params);
            if ($stmt->rowCount() > 0) {
               return true;
            }
            return false; 
        } catch (PDOException $e) {
            echo $e;
            return false;
        }
    }
    
     public function updatesummaries($id,$amount){
        try {   
                if($amount==0) $stmt1 = $this->db->prepare("UPDATE customersummaries SET totalrepaid=totalcredit WHERE id='$id'");
                else $stmt1 = $this->db->prepare("UPDATE customersummaries SET totalrepaid=totalrepaid+$amount WHERE id='$id'");
                $stmt1->execute();
                return true;
            }catch (PDOException $e) {
                echo $e->getMessage();
                return false;
            }   
    }

   
    /** Make payments for a certain customer given certain amount */
    public function makepayment($amount, $customerid){
        $msg = "";
        // If the payment is less than the customer's debt then only one payment record shall be kept for one season
        $summaries = $this->getcustomersummarydebt($customerid); 
        if(sizeof($summaries)==0) return $msg;
        $counter = 0;
        $resamt = $summaries[$counter]['owes'] - $amount;
        if($resamt>0){
           
            $data['amount'] =  $amount;
            $data['customerid'] = $customerid;
            // $data['Date'] =  this will be a timestamp  
            $data['parentid'] = $summaries[$counter]['seasonid'];
            $data['seasonid'] = $summaries[$counter]['seasonid'];
            $data['seasonname'] = $summaries[$counter]['seasonname'];
            //create one payment transaction and exit
            $msg = $msg."-	Repaymend record #".$counter." - Season =".$data['seasonname'].",Amount = +".$amount." - original repayment record\n";
            $this->createrepaymentrec($data);
            $this->updatesummaries( $summaries[$counter]['id'],$amount);
            //$this->createrepaymentupload($data)
            $counter++;
            return $msg;
        }
        
        $resamt = $summaries[$counter]['owes'] + $amount;
        $countp=1;
        // Otherwise if the payment amount exceeds the debt, one keeps paying other debts from other seasons 
        for ($counter; $counter < sizeof($summaries); $counter++) {
             $resamt = $summaries[$counter]['owes'] - $amount;
            // End the function when debt has been satified by the given amount 
            if($resamt>0) return $msg; 
            $this->updatesummaries( $summaries[$counter]['id'],0);
            // Get the previous season id so that it can be used as a parentid
            $prevcounter = $counter - 1; 
            
            if($counter>0) $seasonid = $summaries[$prevcounter]['seasonid'];
            else $seasonid = $summaries[$counter]['seasonid'];

            $data['amount'] =  $amount;
            $data['customerid'] = $customerid;
            // $data['Date'] =  this will be a timestamp  
            $data['parentid'] = $seasonid;
            $data['seasonid'] = $summaries[$counter]['seasonid'];
            //create one payment transaction and exit
            // $this->createrepaymentupload($data)
            if($counter==0) {
                 $msg = $msg."-	Repaymend record #".($countp++)." - Season =".$summaries[$counter]['seasonname'].",Amount = +".$amount." - original repayment record\n";
                $data['amount'] =  $amount;
                $this->createrepaymentrec($data);
            }
            // $this->createrepaymentupload($data)
            $msg = $msg."-	Repaymend record #".($countp++)." - Season =".$summaries[$counter]['seasonname'].",Amount = ".$resamt." - adjustment repayment record\n";
            $data['amount'] =  $resamt;
            $this->createrepaymentrec($data);
            
            if(-$resamt>0 && $counter != sizeof($summaries)-1){
                $msg = $msg."-	Repaymend record #".($countp++)." - Season =".$summaries[$counter+1]['seasonname'].",Amount = +".-$resamt." - adjustment repayment record\n";
                $data['amount'] =  -$resamt;
                $data['seasonid'] = $summaries[$counter]['seasonid'];
                $this->createrepaymentrec($data);
                 //update amount
                $amount = -$resamt;
                $this->updatesummaries($summaries[$counter+1]['id'],$amount);
            }
        }
    }
    
    
}

?>