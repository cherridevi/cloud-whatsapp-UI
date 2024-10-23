<?php
//  $hubVerifyToken = 'phpmaster_token';

// // // // The access token for sending messages (replace with your actual token)
// $accessToken = ''; // Replace with your permanent or temporary WhatsApp token

// // Handle the GET request for webhook verification
// if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['hub_challenge']) && isset($_GET['hub_verify_token'])) {
//     if ($_GET['hub_verify_token'] === $hubVerifyToken) {
//         // If the verify token matches, return the hub challenge to verify the webhook
//         echo $_GET['hub_challenge'];
//     } else {
//         // If the token doesn't match, respond with a 403 forbidden status
//         header('HTTP/1.1 403 Forbidden');
//         echo 'Verification token mismatch';
//     }
//     exit;
// }
include "vtigerfunction.php";
$data = file_get_contents('php://input');


receive_chat($data);

$client_message = "";
$phone_no = "";
$raydata = $data;
$data = json_decode($data, associative: true);
$client_name = "";


if (!isset($data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']) && !isset($data['entry'][0]['changes'][0]['value']['messages'][0]['interactive']) ){
   
    die;
}
$client_name = $client_name = $data['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'];
if (isset($data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'])){
    $client_message =$data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];
}
if(isset($data['entry'][0]['changes'][0]['value']['messages'][0]['interactive'])){
    $quicky_rply = true;  
    $client_message = $data['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['id'];

}

$phone_no = $data['entry'][0]['changes'][0]['value']['messages'][0]['from'];


function menupage($data,$phone_no){
    $sql = "SELECT * FROM `alt_menu`";
    $firstmenu = selectQuery($sql);

    if ($firstmenu['status'] == "ok") {
    
        $template['body'] ="\nThank you for reaching out to us.\nWe’re excited to assist you with all your broadband needs.\nTo ensure you receive the best service,\nPlease choose one of the following options:";

        foreach ($firstmenu['data'] as $key => $menuItem) {
            $template['id'][$key] = $menuItem['id'];
            $template['text'][$key] = $menuItem['name'];
        }
        $quickyreplay =  bodymessage(1,$template,$phone_no);
        firstresponse($quickyreplay);

        // firstresponse($data['reply'], $data['user']['phone'], $menuMessage);
        $sql = "INSERT INTO `alt_userchat`( `username`, `updated_date`, `created_date`) 
        VALUES (".$phone_no.",NOW(),NOW())";
        selectQuery($sql);
        exit;
    }
}

function insertTicket($priority,$category,$priority_id,$userId){
    // echo $userId;
    // die;
    // vtiger_crmentity_seq
    global $phone_no;
    $query = array();
    $token =  random_int(100000, 999999);

    // user id using get the name and other details 
    // $sql = "SELECT * FROM `alt_users` WHERE `name` = '".$request['user']['phone']."' ORDER BY `id` DESC LIMIT 1";
    // $result = selectQuery($sql);

    if(isset($userId)){
        // $string = (string) $result['data'][0]['user_id'];
     
        $userDetails =  getUserDetailsId($userId);
        $userDetails=  json_decode($userDetails,true);

        if($userDetails['status'] == "error"){
     
            $ticket_id = "Sorry some think wrong 😵";
         
            $template = bodymessage(2,$ticket_id,$phone_no);
         
            firstresponse($template);
            exit;
        }
        
    }else{
        $ticket_id = "Sorry some think wrong 😵";
            $template = bodymessage(2,$ticket_id,$phone_no);
            firstresponse($template);
            exit;
    }

    $sql = "SELECT * FROM `vtiger_crmentity_seq` WHERE 1";
    $result = selectQuery($sql);
    // Fetch the current highest ticket number from vtiger_modentity_num table
    $moduleName = 'HelpDesk';
    $sql = "SELECT cur_id FROM vtiger_modentity_num WHERE semodule = '$moduleName'";

    $second = selectQuery($sql);

    if($result['status'] == "error"){
        exit;
    }
    if(isset($result['data'][0]['id']) && $result['data'][0]['id'] !== 0 && isset($second['data'][0]['cur_id'])){
        $id =  $result['data'][0]['id'];
        $ticket_no =  $second['data'][0]['cur_id'];
    }else{
        exit;
    }
    
    $id++;
    
    // $numericPart = (int) substr($ticket_no, 2); // Extracts "20672" as integer



    
    // Increment the numeric part
    $incrementedNumeric = $ticket_no + 1;
    
    // Format it back into the original format
    $nextTicket = "TT" . $incrementedNumeric;
 
    // ticket number from vtiger_modentity_num table
    $query = "UPDATE vtiger_modentity_num SET cur_id = ".$incrementedNumeric." WHERE semodule = '".$moduleName."'";
    $six = selectQuery($query);

    // vtiger_crmentity_seq
    $query = "UPDATE vtiger_crmentity_seq SET id =".$id." where 1";
  
    $one = selectQuery($query);
 
    // vtiger_crmentity
    // Get the current date and time
    $current_date_time = date("Y-m-d H:i:s");


    $query = "INSERT INTO `vtiger_crmentity`
            (`crmid`, `smcreatorid`, `smownerid`, `modifiedby`, `setype`, `createdtime`, `modifiedtime`, `version`, `presence`, `deleted`, `smgroupid`, `source`, `label`) 
        VALUES 
            (".$id.", 1, ".$priority_id.", 1, 'HelpDesk','".$current_date_time."','".$current_date_time."', 0, 1, 0, 0, 'Whatsapp', '".$token."')";
    $two = selectQuery($query);
  
    // vtiger_troubletickets 193994
    $query = "INSERT INTO `vtiger_troubletickets`
        (`ticketid`, `ticket_no`,  `parent_id`, `product_id`, `priority`,  `status`, `category`, `title`, `tags`,`created_time`) 
    VALUES 
        (".$id.",'".$nextTicket."',0,0,'".$priority."','Open','".$category."','".$userId."',0,NOW())";

    $three =  selectQuery($query);
 
    // vtiger_ticketcf vtiger_ticketcf
    
    $username = '';
    $sub_plan_name = '';
    $area = '';
   
   
  
    foreach ($userDetails[0] as $item) {
         
        if (isset($item->User)) {
            $username = $item->User->name;
            $sub_plan_name = $item->User->profile_name;
            if ($sub_plan_name == "Fiber Fast-500Mbps" ||$sub_plan_name == "Fiber Hot-600Mbps") {
                $sub_plan_name = "Basic";
            }else if($sub_plan_name == "Fiber Pace-800Mbps" || $sub_plan_name == "Fiber Electric-800Mbps"){
                $sub_plan_name = "Standard";
            }else if($sub_plan_name == "Fiber Thunder-800Mbps" || $sub_plan_name == "SME-1GIG"){
                $sub_plan_name = "Premium";
            }else if($sub_plan_name == "SME2-1GIG" || $sub_plan_name ==  "SME1-1GIG"){
                $sub_plan_name = "Enterprise";
            }
        }else if(isset($item['User'])){
            $username = $item['User']['name'];
            $sub_plan_name = $item['User']['profile_name'];
            if ($sub_plan_name == "Fiber Fast-500Mbps" ||$sub_plan_name == "Fiber Hot-600Mbps") {
                $sub_plan_name = "Basic";
            }else if($sub_plan_name == "Fiber Pace-800Mbps" || $sub_plan_name == "Fiber Electric-800Mbps"){
                $sub_plan_name = "Standard";
            }else if($sub_plan_name == "Fiber Thunder-800Mbps" || $sub_plan_name == "SME-1GIG"){
                $sub_plan_name = "Premium";
            }else if($sub_plan_name == "SME2-1GIG" || $sub_plan_name ==  "SME1-1GIG"){
                $sub_plan_name = "Enterprise";
            }
        }
        if (isset($item->location)) {
            $area = $item->location->area;
        }else if(isset($item['location'])){
            $area = $item['location']['area'];
        }
    }
    // print_r($userDetails[0][0]['User']['name']);

    $pos = strpos($area, ' - ');

    // Extract the area name from the beginning of the string up to the position of the dash and space
    if ($pos !== false) {
        $area_name = substr($area, 0, $pos);
    }
    $query = "INSERT INTO `vtiger_ticketcf`
        (`ticketid`, `from_portal`, `cf_871`, `cf_873`,`cf_875`,`cf_879`,`cf_881`,`cf_893`, `cf_883`,`cf_889`,`cf_891`) 
    VALUES 
        (".$id.",0,NOW(),NOW(),'Whatsapp Support',NOW(),'L','".$sub_plan_name."','".$phone_no."','".$area_name."','".$username."')";
    // echo $query;
    $four = selectQuery($query);
    $sql = "INSERT INTO `alt_userchat` (`username`, `updated_date`,`created_date`,`answer`) 
    VALUES (".$phone_no.",NOW(),NOW(),0)";

    selectQuery($sql);
    if($one['status'] == "ok" && $two['status'] == "ok" && $three['status'] == "ok" && $four['status'] == "ok"){

        return $nextTicket;
    }

    $statuses = [
        'one' => $one['status'],
        'two' => $two['status'],
        'three' => $three['status'],
        'four' => $four['status']
    ];


    // Loop through the statuses to check which ones are not "ok"
    foreach ($statuses as $variable => $status) {
        if ($status !== "ok") {
            echo  $variable;
            die;
        }
    }

 
}

function insertnewlead($name,$phone,$address,$area,$pincode){
    // echo $name."\n";
    // echo $phone."\n".$address."\n".$area.'\n'.$pincode."\n";
    // die;

    $current_date_time = date("Y-m-d H:i:s");
    $sql = "SELECT * FROM `vtiger_crmentity_seq` WHERE 1";
    $result = selectQuery($sql);

    // Fetch the current highest ticket number from vtiger_modentity_num table
    $moduleName = 1;
    $sql = "SELECT cur_id FROM vtiger_modentity_num WHERE num_id = '$moduleName'";

    $second = selectQuery($sql);
   
    if($result['status'] == "error" && $second == "error"){
        exit;
    }

    if(isset($result['data'][0]['id']) && isset($second['data'][0]['cur_id'])){
        $leadid =  $result['data'][0]['id'];
        $lead_no =  $second['data'][0]['cur_id'];

    }else{
      
        exit;
    }
    $leadid++;
   


    // $numericPart = (int) substr($lead_no, 4); // Extracts "20672" as integer

    $query = "UPDATE vtiger_crmentity_seq SET id =".$leadid." where 1"; //194005
  
    $one = selectQuery($query);

    
    // Increment the numeric part
    $incrementedNumeric = $lead_no + 1;

    // Format it back into the original format
    $nextLead_no = "LEAD" . $incrementedNumeric;



    // ticket number from vtiger_modentity_num table
    $query = "UPDATE `vtiger_modentity_num`
            SET `cur_id` = '".$incrementedNumeric."'
            WHERE `num_id` = 1";

    $six = selectQuery($query);

    

    // vtiger_crmentity
    $label = $name."\nNew Enquiry";
    $query = "INSERT INTO `vtiger_crmentity`(`crmid`, `smcreatorid`, `smownerid`, `modifiedby`, `setype`, `createdtime`, `modifiedtime`, `version`, `presence`, `deleted`, `smgroupid`, `source`, `label`) 
    VALUES ('".$leadid."','21','8','1','Leads','".$current_date_time."','".$current_date_time."',0,1,0,0,'Whatsapp','".$label."')";

    $five = selectQuery($query);
 

    // vtiger_leaddetails
    $query = "INSERT INTO `vtiger_leaddetails`(`leadid`, `lead_no`, `firstname`, `lastname`, `rating`, `leadstatus`,`emailoptout`) VALUES ('".$leadid."','".$nextLead_no."','".$name."','New Enquiry','Hot','Need to Contact','0')";
    $two = selectQuery($query);
  

    $query = "INSERT INTO `vtiger_leadaddress`(`leadaddressid`, `code`, `mobile`, `lane`, `leadaddresstype`) 
          VALUES ('".$leadid."','".$pincode."','".$phone."','".$address."','Billing')";

    $three = selectQuery($query);


    // vtiger_leadsubdetails
    $query = "INSERT INTO `vtiger_leadsubdetails`(`leadsubscriptionid`, `callornot`, `readornot`, `empct`,`website`) VALUES ('".$leadid."',0,0,0,'')";
    $four = selectQuery($query);

     // vtiger_leadsubdetails
     $query = "INSERT INTO `vtiger_leadscf`(`leadid`, `cf_899`) VALUES ('".$leadid."','".$area."')";
     $seven = selectQuery($query);
 

    if($one['status'] == "ok" && $two['status'] == "ok" && $three['status'] == "ok" && $four['status'] == "ok" && $five['status'] == "ok" && $seven['status'] == "ok"){
  
        return $nextLead_no;
    }
    // Define the statuses of each variable
    $statuses = [
        'one' => $one['status'],
        'two' => $two['status'],
        'three' => $three['status'],
        'four' => $four['status']
    ];
    $notOkVariables = [];
 
    // Loop through the statuses to check which ones are not "ok"
    foreach ($statuses as $variable => $status) {
        if ($status !== "ok") {
            echo  $variable;
            die;
        }
    }

}
function NewLeadDetails($alldata,$assocArray){

    // Use regular expressions to extract the name and phone number

    $name = $alldata["name"];
    $phone = $alldata['phone'];
    $address = $alldata['address'];
    $area = $alldata['area'];
    $pincode = $alldata['usage'];
    // echo $name."\n";
    // echo $phone."\n".$address."\n".$area.'\n'.$pincode."\n";
    // die;
    if ($name != null && $phone != null &&
        $address != null  ) {
    
        $name = trim($name);
        $phone = trim($phone);
        $address = trim($address);
        $area = trim($area);
        $pincode = trim($pincode);
    
        // Validate the phone number length
        if (strlen($phone) != 10) {
   
            $Message = "Not Valid Phone";
            $template = bodymessage(2,$Message,$assocArray);
        
            firstresponse($template);
            exit;
        }
   
        $re = insertnewlead($name,$phone,$address,$area,$pincode);
       
        if($re){
            $Message = "Thank you 🙏 Our customer Support Executive Will Call you shortly Your Lead ID-".$re;

            $template = bodymessage(2,$Message,$assocArray);
 
            firstresponse($template);
           
            exit;
        }else{
            $Message = "error !";
            $template = bodymessage(2,$Message,$assocArray);
            firstresponse($template);
            exit;
        }
        // vtiger_leaddetails
        // vtiger_leadaddress
        // vtiger_leadsubdetailsv
        // vtiger_crmentity


        ///vtiger_leadscf

    } else {
       
        $Message = "Miss Match Data";
        $template = bodymessage(2,$Message,$assocArray);
        firstresponse($template);
        exit;
    }

}

function autovalidation($assocArray){
    $text = "Please wait... ⏳";
    $template = bodymessage(2,$text,$assocArray);
    firstresponse($template);

    $phon = substr($assocArray, 2);
    $test = getuserdetails($phon);
    $response = json_decode($test);

    global $conn;
    if($response->status == "error"){
       return false;
    }
    if($response){

        foreach($response as $res){
            // Create a DateTime object from the expirationTime
            $dateTime = new DateTime($res[0]->User->expirationTime);
         
            // Format the date to only show the date part
            $expirationDate = $dateTime->format('Y-m-d');
            $userDetails['body'] = 'Name: ' . $res[0]->User->name . "\n" .
               'User id: ' . $res[0]->User->username . "\n" .
               'Address: ' . $res[0]->User->address . "\n" .
               'Your Plan Expired: ' . $expirationDate. "\n";
                $userDetails['id'][0] =  $res[0]->User->username;
               $userDetails['text'][0] = "Select";  
               $userDetails['id'][1] =  0;
               $userDetails['text'][1] = "Go to Menu";
               $template = bodymessage(1,$userDetails,$assocArray);
               firstresponse($template);
        }

        $asking_user_id = "Pleaze Select the User id";
        bodymessage(2,$asking_user_id,$assocArray);
        $sql = "INSERT INTO `alt_userchat` (`username`, `updated_date`,`created_date`,`answer`) 
                        VALUES (".$assocArray.", NOW(),NOW(),13)";
                       
        selectQuery($sql);
        exit;
    }
    return false;
}
function validation($alldata,$assocArray){

    // print_r($alldata);

    // $message = $alldata['message']['text'];
    $text = "Please wait... ⏳";
    $template = bodymessage(2,$text,$assocArray);
    firstresponse($template);



    $trimmedMessage = trim($alldata);
 
    $messageLength = strlen($trimmedMessage);

  
    if($messageLength == 10){
        
        $test = getuserdetails($trimmedMessage);
 
    }else if($messageLength == 6){
     
        $test = getUserDetailsId($trimmedMessage);
   
    }else{
      
        $text = [];  // Ensure $text is defined as an array

        $text['body'] = "User Not Exist 🙅‍♀️ Try again or go to menu";  // Message body
        
        // Initialize the id and text arrays before assigning values
        $text['id'] = [];  // Initialize as an empty array
        $text['text'] = [];  // Initialize as an empty array
        
        // Assign values
        $text['id'][0] = 0;  // First ID
        $text['text'][0] = "Go To Menu";  // First option
   
    

        $template = bodymessage(4,$text,$assocArray);
        firstresponse($template);
        exit;
    }

    $response = json_decode(json: $test);

   global $conn;
 
    // $result = $conn->query($validate_sql);
    if($response->status == "error"){
   
        $text = [];  // Ensure $text is defined as an array

        $text['body'] = "User Not Exist 🙅‍♀️ Try again or go to menu";  // Message body
        
        // Initialize the id and text arrays before assigning values
        $text['id'] = [];  // Initialize as an empty array
        $text['text'] = [];  // Initialize as an empty array
        
        // Assign values
        $text['id'][0] = 0;  // First ID
        $text['text'][0] = "Go To Menu";  // First option
        $template = bodymessage(4,$text,$assocArray);
        firstresponse($template);
        exit;
    }

    if(count($response) == 1){

        foreach($response as $res){
        
            // Create a DateTime object from the expirationTime
            $dateTime = new DateTime($res[0]->User->expirationTime);

            // Format the date to only show the date part
            $expirationDate = $dateTime->format('Y-m-d');
            $userDetails['body'] = 'Name: ' . $res[0]->User->name . "\n" .
               'User id: ' . $res[0]->User->username . "\n" .
               'Address: ' . $res[0]->User->address . "\n" .
               'Your Plan Expired: ' . $expirationDate. "\n";
            $userDetails['id'][0] = $res[0]->User->username;
            $userDetails['text'][0] = "Select"; 
            $userDetails['id'][1] =  0;
            $userDetails['text'][1] = "Go to Menu"; 
               $template = bodymessage(1,$userDetails,$assocArray);
             
               firstresponse($template);
               $sql = "INSERT INTO `alt_userchat` (`username`, `updated_date`,`created_date`,`answer`) 
               VALUES (".$assocArray.", NOW(),NOW(),13)";
              
                selectQuery($sql);
        }
    }else if($response){

        foreach($response as $res){
            // Create a DateTime object from the expirationTime
            $dateTime = new DateTime($res[0]->User->expirationTime);
         
            // Format the date to only show the date part
            $expirationDate = $dateTime->format('Y-m-d');
            $userDetails['body'] = 'Name: ' . $res[0]->User->name . "\n" .
               'User id: ' . $res[0]->User->username . "\n" .
               'Address: ' . $res[0]->User->address . "\n" .
               'Your Plan Expired: ' . $expirationDate. "\n";
                $userDetails['id'][0] =  $res[0]->User->username;
               $userDetails['text'][0] = "Select";  
               $userDetails['id'][1] =  0;
               $userDetails['text'][1] = "Go to Menu";
               $template = bodymessage(1,$userDetails,$assocArray);
               firstresponse($template);
        }

        $asking_user_id = "Pleaze Select the User id";
        bodymessage(2,$asking_user_id,$assocArray);
        $sql = "INSERT INTO `alt_userchat` (`username`, `updated_date`,`created_date`,`answer`) 
                        VALUES (".$assocArray.", NOW(),NOW(),13)";
                       
        selectQuery($sql);

    }

 exit;
}


function token_gen($user_id,$issues){
   $token =  random_int(100000, 999999);
   $sql = "INSERT INTO `alt_token`(`token`, `user_id`, `created_at`,`isues`) VALUES (".$token." ,".$user_id.",NOW(),".$issues.")";
   selectQuery($sql);
   return $token;
}

$delete = selectQuery("DELETE FROM alt_userchat
WHERE `created_date` < NOW() - INTERVAL 5 DAY");



if ($client_message === 0 || $client_message === "0"|| $client_message === "hi" || $client_message === "Hi") {

    menupage(" ",$phone_no);

}

// Check if there is recent interaction within 2 minutes
$sql = "SELECT * 
FROM alt_userchat 
WHERE username = '$phone_no' 
AND `updated_date` >= NOW() - INTERVAL 25 MINUTE 
ORDER BY `updated_date` DESC 
LIMIT 1;";
$start = selectQuery($sql);


if ($start['status'] == "error") {
 
    menupage($data,$phone_no);
}

// if($client_message == 18239321){
//     $text = "texting";
//     $template = bodymessage(1,$text,$phone_no);
//     firstresponse($template);
// }

// Traverse the decoded array to find the interactive type 
if (isset($data['entry'][0]['changes'][0]['value']['messages'][0]['interactive'])) {
    $interactive = $data['entry'][0]['changes'][0]['value']['messages'][0]['interactive'];
    
    // Check if the interactive type is 'nfm_reply'
    if ($interactive['type'] === 'nfm_reply') {

        // // Decode the response_json to get the values
        $responseJson = json_decode($interactive['nfm_reply']['response_json'], true);
        NewLeadDetails($responseJson,$phone_no); // name,  orderNumber, description, topicRadio

        die;
    }
        // Check if the interactive type is 'nfm_reply'
        if ($interactive['type'] === 'list_reply') {

            $sql = "SELECT `user_id` FROM `alt_users` WHERE `name` = '$phone_no' ORDER BY `id` DESC LIMIT 1";
            $result = selectQuery($sql);
            //     $template = bodymessage(2,$raydata,$phone_no);
            // firstresponse($template);
            if(!isset($result['data'][0]['user_id'])){
                $ticket_id = "Sorry some thing wrong";
                $template = bodymessage(2,$ticket_id,$phone_no);
                firstresponse($template);
            }
            $user_id =$result['data'][0]['user_id'];
     
            $priority =  $interactive['list_reply']['id'];
            $priority_id = 47;
            $category = "Technical Team";
    
            $ticket_no =  insertTicket($priority,$category,$priority_id,$user_id);
            // echo $ticket_no;
            //         $template = bodymessage(2,$raydata,$phone_no);
            // firstresponse($template);
            $randomNumber = rand(100000, 999999);
            $query = "INSERT INTO `alt_otpValiddation`(`ticket_no`, `otp`, `created_date`) VALUES 
            ('".$ticket_no."', '".$randomNumber."',NOW())";
            
            $two = selectQuery($query);
            $ticket_id = "We have registered your complaint your ticket id-".$ticket_no.".\nplease share this code ".$randomNumber."\nThank You 🙏";
            $template = bodymessage(2,$ticket_id,$phone_no);
            firstresponse($template);
    
            die;
        }
}

if(isset($start['data'][0]['answer']) && $start['data'][0]['answer'] == 10){
    validation($client_message,$phone_no);
    exit;
}
//name add the database for new leads
if(isset($start['data'][0]['answer']) && $start['data'][0]['answer'] == 12){

    // newconnection($text,$phone_no);
    $sql = "INSERT INTO `alt_leads`(`user_id`, `name`, `create_at`) VALUES ('".$phone_no."','".$client_message."',NOW())";

    selectQuery($sql);
    $sql = "INSERT INTO `alt_userchat` (`username`, `updated_date`,`created_date`,`answer`) 
    VALUES (".$phone_no.",NOW(),NOW(),15)";
    selectQuery($sql);
    $text = "Enter Your Mobile Number";
    $template = bodymessage(2,$text,$phone_no);
    firstresponse($template);

    exit;
}
//mobile no add the database
if(isset($start['data'][0]['answer']) && $start['data'][0]['answer'] == 15){
        // Validate the phone number length
        if (strlen($client_message) != 10) {
   
            $Message['body'] = "Not Valid Phone Number\nTry again or go to menu";
            $Message['id'][0] = 0;
            $Message['text'][0] = "Go to Menu";
            $template = bodymessage(4,$Message,$phone_no);
        
            firstresponse($template);
            exit;
        }
        $sql = "SELECT * FROM alt_leads  WHERE user_id  = '".$phone_no."' ORDER BY id DESC LIMIT 1";
        $firstmenu = selectQuery($sql);
        if ($firstmenu['status'] == "ok") {
    
            foreach ($firstmenu['data'] as  $menuItem) {
                $alldata['name'] = $menuItem['name'];
               
            }
        }
        $alldata['phone'] = $client_message;
        $alldata['address'] = "null";
    // $sql = "UPDATE alt_leads  JOIN ( SELECT id FROM alt_leads  WHERE user_id  = '".$phone_no."' ORDER BY id DESC LIMIT 1 ) AS latest_record ON alt_leads .id = latest_record.id SET alt_leads.Mobile = '".$client_message."'";
    // selectQuery($sql);
    $sql = "INSERT INTO `alt_userchat` (`username`, `updated_date`,`created_date`,`answer`) 
    VALUES (".$phone_no.",NOW(),NOW(),0)";
    selectQuery($sql);
    NewLeadDetails($alldata,$phone_no);
    // $text = "Enter Your address";
    // $template = bodymessage(2,$text,$phone_no);
    // firstresponse($template);
    exit;
}
// address add the database 
if(isset($start['data'][0]['answer']) && $start['data'][0]['answer'] == 16){

    $sql = "SELECT * FROM alt_leads  WHERE user_id  = '".$phone_no."' ORDER BY id DESC LIMIT 1";
    $firstmenu = selectQuery($sql);
    if ($firstmenu['status'] == "ok") {

        foreach ($firstmenu['data'] as  $menuItem) {
            $alldata['name'] = $menuItem['name'];
            $alldata['phone'] = $menuItem['Mobile'];
           
        }
    }
    $alldata['area'] = "Null";
    $alldata['usage']  = "null";
    $alldata['address'] = $client_message;
    $sql = "INSERT INTO `alt_userchat` (`username`, `updated_date`,`created_date`,`answer`) 
    VALUES (".$phone_no.",NOW(),NOW(),0)";

    selectQuery($sql);
    NewLeadDetails($alldata,$phone_no);
exit;
    $text = "Enter Your Current Location";
    //$template = bodymessage(6,$text,$phone_no);
    $template = bodymessage(6,$text,$phone_no);
    // $template = bodymessage(2,$template,$phone_no);
    firstresponse($template);

    exit;

}
// geolocation get 
if(isset($start['data'][0]['answer']) && $start['data'][0]['answer'] == 17){
   
    // $sql = "UPDATE alt_leads  JOIN ( SELECT id FROM alt_leads  WHERE user_id  = '".$phone_no."' ORDER BY id DESC LIMIT 1 ) AS latest_record ON alt_leads .id = latest_record.id SET alt_leads.location = '".$client_message."'";
    // selectQuery($sql);
    // $sql = "UPDATE alt_userchat JOIN ( SELECT id FROM alt_userchat WHERE username = '".$phone_no."' ORDER BY id DESC LIMIT 1 ) AS latest_record ON alt_userchat.id = latest_record.id SET alt_userchat.answer = '17'";
    // selectQuery($sql);
    // $text = "Enter Your address";

    $template = bodymessage(2,$raydata,$phone_no);
    firstresponse($template);
    exit;
}
if(isset($start['data'][0]['answer']) && $start['data'][0]['answer'] == 13){

    if(!$quicky_rply){
        $text['body'] = "Incorrect input please choose again or return to the menu";
        $text['id'][0] = 0;
        $text['text'][0] = "Go To Menu";

        $template = bodymessage(4,$text,$phone_no);
        firstresponse($template);
        
        exit;
    }
    $newtext = $client_message;
    $sql ="INSERT INTO `alt_users`( `name`, `user_id`) VALUES ('".$phone_no."','".$newtext."')";
    selectQuery($sql);
 
    $sql = "INSERT INTO `alt_userchat` (`username`, `updated_date`,`created_date`,`answer`) 
    VALUES (".$phone_no.", NOW(),NOW(),14)";
   
    selectQuery($sql);

    $text = "Go back to menu send 0 or Hi";
    // $text['id'][0] = 15;
    // $text['text'][0] = "Speed Issue";
    // $text['id'][1] = 16;
    // $text['text'][1] = "Red Light on Router";
    // $text['id'][2] = 17;
    // $text['text'][2] = "Other Issue";
    $template = bodymessage(7,$text,$phone_no);
    firstresponse($template);
    exit;
}

if(!isset($data['entry'][0]['changes'][0]['value']['messages'][0]['interactive'])){ 

    exit;
 }
if(isset($start['data'][0]['qsk_id']) && $start['data'][0]['qsk_id'] == 0 ){
    
    if($client_message == 1){

        $text = "Enter your Name";
        $template = bodymessage(2,$text,$phone_no);
        firstresponse($template);
        $id = $start['data'][0]['id'];
        $sql = "INSERT INTO `alt_userchat` (`username`, `updated_date`,`created_date`,`answer`) 
        VALUES (".$phone_no.",NOW(),NOW(),12)";
        selectQuery($sql);
        exit;
 
    }else
    if($client_message == 4){
        $text['caption'] = "Go back to menu send 0 or Hi";
        $text['link'] = "https://cfnetcrm.cherritech.us/whatsappFile/plan_details.jpg";
        $template = bodymessage(3,$text,$phone_no);
        firstresponse($template);

    }else
    if($client_message == 6){
        $text = "Go back to menu send 0 or Hi \n\nPayment link as 🔍 https://client.cfnet.in/";
        $template = bodymessage(2,$text,$phone_no);
        firstresponse($template);
        
    }else
    if($client_message == 7){
        $text = "Direct call need to attend by \ncc team ☎️ 9787713333";
        $template = bodymessage(2,$text,$phone_no);
        firstresponse($template);
    }else
    if($client_message == 2){

        autovalidation($phone_no);

        $text = "Kindly share your registered mobile number 📞 Or User id 🪪";
 
        $template = bodymessage(2,$text,$phone_no);
        firstresponse($template);
        $id = $start['data'][0]['id'];
        $sql = "INSERT INTO `alt_userchat` (`username`, `updated_date`,`created_date`,`answer`) 
        VALUES (".$phone_no.",NOW(),NOW(),10)";
        selectQuery($sql);
        exit;
    }else
    if($client_message == 15){


        $sql = "SELECT `user_id` FROM `alt_users` WHERE `name` = '$phone_no' ORDER BY `id` DESC LIMIT 1";
        $result = selectQuery($sql);
   
        if(!isset($result['data'][0]['user_id'])){
            $ticket_id = "Sorry some thing wrong";
            $template = bodymessage(2,$ticket_id,$phone_no);
            firstresponse($template);
        }
        $user_id = $result['data'][0]['user_id'];
       
        $priority = "Speed Issue";
        $priority_id = 47;
        $category = "Technical Team";
    
        $ticket_no =  insertTicket($priority,$category,$priority_id,$user_id);
    

        $ticket_id = "We have registered your complaint your ticket id-".$ticket_no.".\nplease share this code\nThank You 🙏";
        $template = bodymessage(2,$ticket_id,$phone_no);
        firstresponse($template);
    }else
    if($client_message == 16){

        $sql = "SELECT `user_id` FROM `alt_users` WHERE `name` = '$phone_no' ORDER BY `id` DESC LIMIT 1";
        $result = selectQuery($sql);

        if(!isset($result['data'][0]['user_id'])){
            $ticket_id = "Sorry some thing wrong";
            $template = bodymessage(2,$ticket_id,$phone_no);
            firstresponse($template);
        }
        $user_id =$result['data'][0]['user_id'];
 
        $priority = "Red Light on Router";
        $priority_id = 47;
        $category = "Technical Team";

        $ticket_no =  insertTicket($priority,$category,$priority_id,$user_id);
        // echo $ticket_no;
        //         $template = bodymessage(2,$raydata,$phone_no);
        // firstresponse($template);
        $ticket_id = "We have registered your complaint your ticket id-".$ticket_no.".\nplease share this code\nThank You 🙏";
        $template = bodymessage(2,$ticket_id,$phone_no);
        firstresponse($template);
    }else
    if($client_message == 17){
        $sql = "SELECT `user_id` FROM `alt_users` WHERE `name` = '$phone_no' ORDER BY `id` DESC LIMIT 1";
        $result = selectQuery($sql);
        if(!isset($result['data'][0]['user_id'])){
            $ticket_id = "Sorry some thing wrong";
            $template = bodymessage(2,$ticket_id,$phone_no);
            firstresponse($template);
        }
        $user_id =$result['data'][0]['user_id'];
 
        $priority = "Other Issue";
        $priority_id = 47;
        $category = "Technical Team";

        $ticket_no =  insertTicket($priority,$category,$priority_id,$user_id);

        $ticket_id = "We have registered your complaint your ticket id-".$ticket_no.".\nplease share this code\nThank You 🙏";
        $template = bodymessage(2,$ticket_id,$phone_no);
        firstresponse($template);
    }

    $id = $start['data'][0]['id'];
    $sql = "INSERT INTO `alt_userchat` (`username`, `updated_date`,`created_date`,`answer`) 
    VALUES (".$phone_no.",NOW(),NOW(),'".$client_message."')";
    selectQuery($sql);
    exit;
    

 }





// Handle specific user interactions based on the message content


// Close statement and connection
$stmt->close();



$conn->close();
?>
