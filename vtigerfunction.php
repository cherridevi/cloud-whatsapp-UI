<?

$servername = "";
$username = "";
$password = "";
$dbname = "";

   //**************** META business account */
   $apiUrl = '';
   $accessToken = '';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//insert qauery 
function insertquery($query) {
    global $conn;

    // Set the character set to utf8mb4 to support emojis
    if (!$conn->set_charset("utf8mb4")) {
        return ["status" => "error", "message" => "Failed to set charset: " . $conn->error];
    }

    // Execute the query
    if ($conn->query($query) === TRUE) {
        // Retrieve the ID of the last inserted record
        $last_id = $conn->insert_id;
        return ["status" => "ok", "data" => $last_id];
    } else {
        return ["status" => "error", "message" => $conn->error];
    }
}

// Function to execute SELECT query
function selectQuery($query) {
    global $conn;
    // Set the character set to utf8mb4 to support emojis
    if (!$conn->set_charset("utf8mb4")) {
        return ["status" => "error", "message" => "Failed to set charset: " . $conn->error];
    }
    $result = $conn->query($query);

    if ($result) {
        if ($result->num_rows > 0) {
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            return ["status" => "ok", "data" => $rows];
        } else {
            // If no rows are found, check affected rows count
            if ($conn->affected_rows > 0) {
                return ["status" => "ok", "message" => "No rows found, but operation affected " . $conn->affected_rows . " row(s)."];
            } else {
                return ["status" => "error", "message" => "No rows found and no rows affected."];
            }
        }
    } else {
        // If there's an error in the query
        return ["status" => "error", "message" => $conn->error];
    }
}

function zonechangeapi($username,$apiUsername,$apiPassword){

    $url = "https://admin.cfnet.in/api/v1/get_user_by_username/$username";
    // Initialize cURL session
    $ch = curl_init();

    // Set the URL
    curl_setopt($ch, CURLOPT_URL, $url);
    
    // Set the return transfer option
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Set the HTTP authentication
    curl_setopt($ch, CURLOPT_USERPWD, "$apiUsername:$apiPassword");
    
    // Execute the cURL request
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
       // Close the cURL session
       curl_close($ch);
    
       // Return the response
       return $response;
}
function getUserDetailsId($username) {

    $apiarray = [""];


    foreach($apiarray as $api) {
        $apiUsername = $api['name']; // Assuming 'name' is used as apiUsername
        $apiPassword = $api['password'];
    
        // Call the API function
        $response = zonechangeapi($username, $apiUsername, $apiPassword);
       
        $jsondata = json_decode($response,true);
        // Check the response status
        if ($jsondata['status'] === 'error') {
            // If error, continue to the next set of credentials
            continue;
        }
      
        return $response;
        // If the status is not 'error', handle success (if needed)
        // For example:
        
    
        
    }
   

    return $response;
 
}


function getuserdetails($phone){

    $curl = curl_init();

    $username = 'cfibercommunications';
    $password = '930402fcd02ba210f0351d36c9c002e1b353990b';
    $auth = base64_encode("$username:$password");

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://admin.cfnet.in/api/v1/get_user_by_phone/' . $phone . '/:status',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            "Authorization: Basic $auth"
        ),
    ));

    $response = curl_exec($curl);
    
    // Check for cURL errors
    if ($response === false) {
        $error = curl_error($curl);
        curl_close($curl);
        return "cURL Error: $error";
    }

    // Get the HTTP response code
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    // Check the HTTP response code
    if ($http_code != 200) {
        return false;
    }

    return $response;
}
function voiceticket($alldata,$priority,$category,$priority_id){
 
    $query = array();
    $token =  random_int(100000, 999999);

    $phone_no = $alldata['CallFrom'];
    $sql = "SELECT `answer` as user_id
    FROM alt_userchat 
    WHERE username = '$phone_no' 
    ORDER BY `updated_date` DESC 
    LIMIT 1;";
    $start = selectQuery($sql);

    $userId = $start['data'][0]['user_id'];

    $user_Details =  getUserDetailsId($userId);


    $userDetails=  json_decode($user_Details,true);




    $sql = "SELECT * FROM `vtiger_crmentity_seq` WHERE 1";
    $result = selectQuery($sql);
    // Fetch the current highest ticket number from vtiger_modentity_num table
    $moduleName = 'HelpDesk';
    $sql = "SELECT cur_id FROM vtiger_modentity_num WHERE semodule = '$moduleName'";

    $second = selectQuery($sql);
    if(isset($result['data'][0]['id']) && $result['data'][0]['id'] !== 0 && isset($second['data'][0]['cur_id'])){
        $id =  $result['data'][0]['id'];
        $ticket_no =  $second['data'][0]['cur_id'];
    }else{
        exit;
    }
      
    $id++;
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
             (".$id.", 1, ".$priority_id.", 1, 'HelpDesk','".$current_date_time."','".$current_date_time."', 0, 1, 0, 0, 'Call', '".$token."')";
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
         (".$id.",0,NOW(),NOW(),'Call Support',NOW(),'L','".$sub_plan_name."','".$phone_no."','".$area_name."','".$username."')";
     // echo $query;
     $four = selectQuery($query);




     if($one['status'] == "ok" && $two['status'] == "ok" && $three['status'] == "ok" && $four['status'] == "ok"){
        
                header("HTTP/1.1 200 OK");
        
            die;
     }

 
         header("HTTP/1.1 404 Not Found");
         
             die;
      
}

function voicenewlead($name,$phone,$address,$area,$pincode){
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
    VALUES ('".$leadid."','21','8','1','Leads','".$current_date_time."','".$current_date_time."',0,1,0,0,'Call support','".$label."')";

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
        echo "here";
        die;
        header("HTTP/1.1 200 OK");
        
        die;
    }
    header("HTTP/1.1 404 Not Found");
         
    die;

}

function client_message($data){
    $client_message = json_encode($data);

    if (isset($data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'])){
        $client_message =$data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];
        return $client_message;
    }
    if(isset($data['entry'][0]['changes'][0]['value']['messages'][0]['interactive'])){
        // Store the interactive type
        $interactive_type = $data['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['type'];


        // Check if the interactive type is "list_reply"
        if ($interactive_type === 'list_reply') {
            // Store the list reply ID
            $list_reply_id = $data['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['list_reply']['id'];
            $client_message = $list_reply_id;

        // Check if the interactive type is "button_reply"
        } elseif ($interactive_type === 'button_reply') {
            // Store the button reply ID
            $button_reply_id = $data['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['title'];
            $client_message =  $button_reply_id;

        }
        
        return $client_message;
    }
    
    if (isset($data['entry'][0]['changes'][0]['value']['messages'][0]['type'])) {
        
        $type = $data['entry'][0]['changes'][0]['value']['messages'][0]['type'];
        $details = $data['entry'][0]['changes'][0]['value']['messages'][0];
        $details['userId'] = $data['userId'];
  
        $save_details = array();
        if($type == "image"){
            // Get the image ID
            $data_id = $details['image']['id'];
            $save_details['type']= "image";
            $save_details['caption'] =  $data['entry'][0]['changes'][0]['value']['messages'][0]['image']['caption'];
        }else if($type == "document"){
            $data_id = $details['document']['id'];
            $save_details['caption'] =  $data['entry'][0]['changes'][0]['value']['messages'][0]['document']['caption'];
            $save_details['type']= "document";
        }else if($type == 'audio'){
            $data_id = $details['audio']['id'];
            $save_details['type']= "audio";
        }else if($type == 'video'){
            $data_id = $details['video']['id'];
            $save_details['type']= "video";
            $save_details['caption'] =  $data['entry'][0]['changes'][0]['value']['messages'][0]['video']['caption'];
        }else{
            return $client_message;
        }
       
 
        $client_data = downloadWhatsappMedia($data_id,$details,$type);
        
        if($client_data['status'] == "ok"){
            $save_details['url'] = $client_data['data'];
            $client_message = json_encode($save_details);
        }
    }
    return $client_message;
}
//whatsapp chat ui store the message 
function receive_chat($data){
    $data = json_decode($data,true);
    if(isset($data['entry'][0]['changes'][0]['value']['messages'][0]['from'])){
    
        $phone_no = $data['entry'][0]['changes'][0]['value']['messages'][0]['from'];
        $query = "SELECT * FROM `users` WHERE `number` = '".$phone_no."'";

        $check = selectQuery(query: $query);
        $client_message = json_encode($data);


        // Check if any row exists
    if ($check['status'] == "ok" && isset($check['data']) && count($check['data']) > 0) {

        $user_id = $check['data'][0]['id'];
        $data['userId'] = $user_id;

  
        $client_message =  client_message($data);


        $query = "INSERT INTO `message`(`sender`, `recvId`, `body`, `time`, `status`, `recvIsGroup`) VALUES ('".$user_id."','1','".$client_message."',NOW(),'1','0')";

        $check = selectQuery($query);

        // Access data with $check['data']
    }else if($check['status'] == "error" && $check['message'] == 'No rows found and no rows affected.'){
        
        
        
        $query = "INSERT INTO `users`( `username`,  `user_type`, `number`, `pic`) VALUES ('".$phone_no."','user','".$phone_no."','removedp.png')";

        $user_iid = insertquery($query);


      
        if($user_iid['status'] == 'error'){
  
            return 0;
        }
        $data['userId'] = $user_iid['data'];

        $client_message =  client_message($data);

        $query = "INSERT INTO `message`(`sender`, `recvId`, `body`, `time`, `status`, `recvIsGroup`) VALUES ('".$user_iid['data']."','1','".$client_message."',NOW(),'2','0')";

        $check = selectQuery($query);
    

    }
    }
}
function firstresponse($message) {
    global $apiUrl;
    global $accessToken;
    $user_id = $message['to'];

    $query = "SELECT * FROM `users` WHERE `number` = '".$user_id."'";

    $check = selectQuery($query);
    if ($check['status'] == "ok" && isset($check['data']) && count($check['data']) > 0) {
        $interactive_type = $message['interactive']['type'];

        if($message['type'] == "text"){
            $text = $message['text']['body'];
            $client_message = json_encode($text,JSON_UNESCAPED_UNICODE);
        }else{
            // list template
            if($interactive_type == "list"){
                $interactive_list['body'] = $message['interactive']['body'];
                $interactive_list['action'] = $message['interactive']['action']['button'];
                $interactive_list['sections'] = $message['interactive']['action']['sections'];
                $interactive_list['type'] =$interactive_type;
                $client_message = json_encode($interactive_list,JSON_UNESCAPED_UNICODE);

            }else if($interactive_type == "button"){
                $jsonString = str_replace("\n", ' ', subject: $message['interactive']['body']); // Replace newlines with spaces
                $jsonString = str_replace("\r", '', $jsonString); 
                $client_message['body'] = $jsonString;
                $client_message['header'] = $message['interactive']['header']['text'];
    
                // Step 3: Extract id and title
                $resultArray = [];
                foreach ($message['interactive']['action']['buttons'] as $item) {
                    if (isset($item['reply']['id'], $item['reply']['title'])) {
                        $resultArray[] = [
                            'id' => str_replace("\n", "", $item['reply']['id']), // Remove newline from id
                            'title' => str_replace("\n", "", $item['reply']['title']) // Remove newline from title
                        ];
                    }
                }
                $client_message['buttons'] =$resultArray;
                $client_message['type'] =$interactive_type;
    
                $client_message = json_encode($client_message,JSON_UNESCAPED_UNICODE);
            }else{
                $client_message = json_encode($message,JSON_UNESCAPED_UNICODE);
            }

       
        }
        
        $user_id = $check['data'][0]['id'];
        $query = "INSERT INTO `message`(`sender`, `recvId`, `body`, `time`, `status`, `recvIsGroup`) VALUES ('1','".$user_id."','".$client_message."',NOW(),'1','0')";
       
    
        $check = insertquery($query);
    }



 

    // $message = $data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];
    $ch = curl_init($apiUrl);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
    } else {
        // Check HTTP status code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode == 200) {

            // Decode and display the response
            $responseData = json_decode($response, true);
            echo 'Response: ' . print_r($responseData, true);
        } else {
            echo 'HTTP Status Code: ' . $httpCode;
            echo 'Response: ' . $response;
        }
    }

    // Close cURL
    curl_close($ch);
}
function whatsappsendtext($message) {
    $user_id = $message['to'];

    $query = "SELECT * FROM `users` WHERE `number` = '".$user_id."'";

    $check = selectQuery($query);
    if ($check['status'] == "ok" && isset($check['data']) && count($check['data']) > 0) {
        
        $client_message = json_encode($message);
        $user_id = $check['data'][0]['id'];
        // $query = "INSERT INTO `message`(`sender`, `recvId`, `body`, `time`, `status`, `recvIsGroup`) VALUES ('1','".$user_id."','".$client_message."',NOW(),'2','0')";

        // $check = selectQuery($query);
    }



    //**************** META business account */
    global $apiUrl;
    global $accessToken;
    // $message = $data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];
    $ch = curl_init($apiUrl);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
    } else {
       // Decode the response from JSON to an array
    $decodedResponse = json_decode($response, true);

    // Check HTTP status code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        // Return the error message from the response
        return ['status' => 'error', 'message' => $decodedResponse['error']['message'] ?? 'message not send'];
    } else {
        return ['status' => 'ok', 'message' => 'message send', 'response' => $decodedResponse];
    }

    }

    // Close cURL
    curl_close($ch);
}

function downloadWhatsappMedia($media_id,$details,$type)
{
    global $accessToken;
 
    // Define the Graph API URL for the media ID
    $graph_api_url = 'https://graph.facebook.com/v20.0/' . $media_id;


    // Set up cURL to get the media URL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $graph_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);

    // Execute the request to get the media metadata
    $response = curl_exec(handle: $ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        curl_close($ch);
        return 'Request Error: ' . curl_error($ch);
    }

    // Decode the JSON response
    $response_data = json_decode($response, true);
    curl_close($ch);

 
    // Check if the URL is present in the response if type is image
    if($type == "image"){
        if (isset($response_data['url'])) {
            $media_url = $response_data['url'];
    

            // Fetch the image from the media URL
            $data = imagesData($media_url,$accessToken,$details);
         
            if($data['status'] == "ok"){
                return $data;
            }
    
        } else {
            return "No media URL found in the response.\n";
        }
    }else if($type == "audio"){
        if (isset($response_data['url'])) {
            $media_url = $response_data['url'];
    
          
    
            // Fetch the image from the media URL
            $data = imagesData($media_url,$accessToken,$details);
    
            if($data['status'] == "ok"){
                return $data;
            }
    
        } else {
            return "No media URL found in the response.\n";
        }
    }else{
    

      
        if (isset($response_data['url'])) {
            $media_url = $response_data['url'];
    
          
    
            // Fetch the image from the media URL
            $data = imagesData($media_url,$accessToken,$details);
    
            if($data['status'] == "ok"){
                return $data;
            }
    
        } else {
            return "No media URL found in the response.\n";
        }
    }

}


function imagesData($url, $token,$detail) {
    $userId = $detail['userId'];
    $type = $detail['type'];
    
    $uploadDir =  'whatsfile/' . $userId;
    // Create the directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if($type == "image"){
        // Generate a unique filename using the current timestamp and a unique ID
        $timestamp = time(); // Current timestamp
        $uniqueId = uniqid(); // Unique identifier
        $imageName = "image_{$timestamp}_{$uniqueId}.jpeg"; // Unique image name
        $filePath = $uploadDir.$imageName; // Full path to the image file
    }else if($type == "audio"){
        $timestamp = time(); 
        $imageName = "image_{$timestamp}.ogg"; // Unique image name
        $filePath = $uploadDir.$imageName; // Full path to the image file
    }else if($type == "video"){
        $timestamp = time(); 
        $imageName = "video_{$timestamp}.mp4"; // Unique image name
        $filePath = $uploadDir.$imageName; // Full path to the image file
    }else{
        $timestamp = time(); // Current timestamp
        $uniqueId = $detail['document']['filename'];
        $imageName = "image_{$timestamp}_{$uniqueId}"; // Unique image name
       
        $filePath = $uploadDir.$imageName; // Full path to the image file
    }

    // Build the cURL command
    $curlCommand = "cd {$uploadDir} && curl --location \"$url\" --header \"Authorization: Bearer $token\" -o \"$imageName\"";

    

    $output = shell_exec(command: $curlCommand);
    
        return ['status'=>'ok', "data"=> $imageName];

 
}

function savemedia($files, $uploadDir) {
    $media_arry = array();
    $uploadDir =  __DIR__ .$uploadDir;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true); // Create the directory with permissions
    }
    $uploadDir = $uploadDir . '/';
    foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
        // Ensure there is no error in the file upload
        if ($_FILES['files']['error'][$key] === 0) {
            // Create a unique file name using time and the original file name
            $uniqueFileName = time() . '_' . $_FILES['files']['name'][$key];
            
            // Full path to where the file will be moved
            $destination = $uploadDir . $uniqueFileName;
    
            // Move the file to the destination
            if (move_uploaded_file($tmpName, $destination)) {
                // Add the unique file name to the array
                $media_arry[$key] = $uniqueFileName;
            } else {
                // Return 0 if the file couldn't be moved
                return 0;
            }
        } else {
            // Return 0 if there was an error with the file upload
            return 0;
        }
    }
    
    // Return the array of uploaded file names after all files are processed
    return $media_arry;
}

function bodymessage($type,$message,$phone){
    global $client_name;
    $threebtn = "";
  // multiple button template
    if($type == 1){
        if(isset($message['text'][2])){
            $threebtn =  [
                "type" => "reply",
                "reply" => [
                    "id" =>$message['id'][2],
                    "title" => $message['text'][2]
                ]
                ];
        }
        $welcome = "🚀🚀Welcome to C Fibernet🚀🚀";
        $messageData = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "interactive",
            "interactive" => [
                "type" => "button",
                "header" => [
                    "type" => "text",
                    "text" => $welcome
                ],
                "body" => [
                    "text" => $message['body']
                ],
                "action" => [
                    "buttons" => [
                        [
                            "type" => "reply",
                            "reply" => [
                                "id" => $message['id'][0],
                                "title" => $message['text'][0]
                            ]
                        ],
                        [
                            "type" => "reply",
                            "reply" => [
                                "id" =>$message['id'][1],
                                "title" => $message['text'][1]
                            ]
                            ],$threebtn
                    ]
                ]
            ]
        ];
    }
    // text template
    if($type == 2){
        $messageData = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "text",
            "text" => [
                "preview_url" => false,
                "body" => $message
            ]
            ];

    }
    // image template
    if($type ==3){
        $messageData = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "image",
            "image" => [
                "link" => $message['link'], // Optional, only if linking to your media
                "caption" => $message["caption"]
            ]
        ];
        
    }
    //single button template
    if($type == 4){
        $messageData = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "interactive",
            "interactive" => [
                "type" => "button",
                "header" => [
                    "type" => "text",
                    "text" => "Welcome to Our Service!"
                ],
                "body" => [
                    "text" => $message['body']
                ],
                "action" => [
                    "buttons" => [
                        [
                            "type" => "reply",
                            "reply" => [
                                "id" => $message['id'][0],
                                "title" => $message['text'][0]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
    if($type == 5){
        $messageData = [
            "recipient_type" => "individual",
            "messaging_product" => "whatsapp",
            "to" =>  $phone,
            "type" => "interactive",
            "interactive" => [
                "type" => "flow",
                "body" => [
                    "text" => "Please enter your details"
                ],
              
                "action" => [
                    "name" => "flow",
                    "parameters" => [
                        "flow_message_version" => "3",
                        "flow_token" => "1",
                        "flow_id" => "1534495710806335",
                        "flow_cta" => "Click Here",
                        "flow_action" => "navigate",
                        "flow_action_payload" => [
                            "screen" => "QUESTION_ONE",
                            "data" => [
                                "product_name" => "name",
                                "product_description" => "description",
                                "product_price" => 100
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
    }
    // location 
    if($type == 6){
        $messageData = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "type" => "interactive",
            "to" => $phone,
            "interactive" => [
                "type" => "location_request_message",
                "body" => [
                    "text" => $message
                ],
                "action" => [
                    "name" => "send_location"
                ]
            ]
        ];
        
    }
    // list template
    if($type == 7){
        $messageData = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "interactive",
            "interactive" => [
                "type" => "list",
                "body" => [
                    "text" => "Choose your Internet Issue"
                ],
                "action" => [
                    "sections" => [
                        [
                            "title" => "<SECTION_TITLE_TEXT>",
                            "rows" => [
                                [
                                    "id" => "Speed issues",
                                    "title" => "Speed issues"
                                ],
                                [
                                    "id" => "Red Light on Router",
                                    "title" => "Red Light on Router"
                                ],
                                [
                                    "id" => "Router issue",
                                    "title" => "Router issue"
                                ],
                                [
                                    "id" => "Router place shifting",
                                    "title" => "Router place shifting"
                                ],
                                [
                                    "id" => "Technical Doubt",
                                    "title" => "Technical Doubt"
                                ],
                                [
                                    "id" => "Wifi Issue",
                                    "title" => "Wifi Issue"
                                ]
                            ]
                        ]
                    ],
                    "button" => "View"
                ]
            ]
        ];
    }
    //document
    if($type == 8){
    
            $messageData = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $phone,
                "type" => "document",
                "document" => [
                    "link" => $message['link'], // Optional, only if linking to your media
                    "caption" => $message["caption"]
                ]
            ];
            

    }
   return $messageData;
}






// function alreadyticket($){

// }
?>