<?php

require_once './../db/Database.php';

Class Customer{

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

    /*
        customerid (int)
        seasonid (int)
        TotalRepaid (dec)
        TotalCredit (dec)
    */

    /** Get customer summary */
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

    /** Get customer summary */
    public function getcustomersummarydebt($cusId){  
        try {
            $stmt = $this->db->prepare("SELECT (TotalCredit - TotalRepaid) as outstanding, cust.seasonid, (SELECT SeasonName FROM Seasons where seasonid=cust.seasonid) FROM CustomerSummaries where customerid = :id and  TotalCredit > TotalRepaid ");
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

     /** Get all customer summary */
     public function getcustomersummaries(){
        try {
            $stmt = $this->db->prepare("SELECT cust.TotalRepaid, cust.TotalCredit, (SELECT SeasonName FROM Seasons where seasonid=cust.seasonid), cust.seasonid FROM CustomerSummaries as cust");
            $stmt->execute();
            $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($res !== false) {
                return json_encode(['summary'=>$res, 'success'=>true ]);
            }
            return json_encode(['success'=>false ]);
        } catch (PDOException $e) {
            echo $e->getMessage();
            return json_encode(['success'=>false ]);
        }
    }

    // A payment can be its own parent 
    public function createrepaymentrec($data){
        try {
            $stmt = $this->db->prepare("insert into repayments(customerid, seasonid, Date, amount, parentid) values (:customerid, :seasonid, :Date, :amount, :parentid)");
            $params = array(":customerid"=>$data['customerid'],":seasonid"=>$data['seasonid'], ":Date"=>$data['Date'], ":amount"=>$data['amount'],  ":parentid"=>$data['parentid']);
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

    /*
    RepaymentID (int)
    customerid (int)
    seasonid (int)
    Date (date)
    amount (dec)
    parentid (int)
    */
    /** Get customer summary */
    public function makepayment($amount, $customerid){
        //what do you need to process payment
        // - Get seasons that have outstanding amount from oldest
        // - apply process season on each retrieved season
        // Get outstanding seasons for a specific customer

        // If the payment is less than the customer's debt then one payment record shall be kept for one season
        $summaries = $this->getcustomersummarydebt($customerid)  
        $counter = 0;
        $resamt = $summaries[$counter]['outstanding']- $amount 
        if($resamt>0){
            //customerid, seasonid, Date, amount, parentid
            //outstanding, seasonid, SeasonName, customerid
            $data['amount'] =  $amount;
            $data['customerid'] = $customerid;
            // $data['Date'] =  this will be a timestamp  
            $data['parentid'] = $summaries[$counter]['seasonid']
            $data['seasonid'] = $summaries[$counter]['seasonid']
            //create one payment transaction and exit
            $this->createrepaymentrec($data)
            return true;
        }

        // Otherwise if the payment amount exceeds the debt, one keeps paying other debts 
        for ($counter; $counter < sizeof($summaries); $counter++) {
            //code to be executed;
            //create a transaction 
            //outstanding
            // if $resamt is posititive This means that the given amount cannot satisfy the debt -- hence the program should stop
            // repayments(customerid, seasonid, Date, amount, parentid)

            if($resamt<0) return true;
            //create 2 transactions 
            $prevcounter = $counter - 1 
            
            if($counter>0) $seasonid = $summaries[$prevcounter]['seasonid'];
            else $seasonid = $summaries[$counter]['seasonid'];

            $data['amount'] =  $amount;
            $data['customerid'] = $customerid;
            // $data['Date'] =  this will be a timestamp  
            $data['parentid'] = $seasonid
            $data['seasonid'] = $summaries[$counter]['seasonid']
            //create one payment transaction and exit
            $this->createrepaymentrec($data)
            $data['amount'] =  $resamt;
            $this->createrepaymentrec($data)
            $resamt = $summaries[$counter]['outstanding'] + $resamt 
        }
    }
    
    
}

?>