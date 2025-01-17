<?php 
    include "../vtigerfunction.php";
	session_start();
	// Get the protocol (http or https)
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

	// Get the host name
	$host = $_SERVER['HTTP_HOST'];

	// Construct the base URL
	$base_url = $protocol . $host;
	// connect to database
	$db = mysqli_connect('localhost', 'cherr1rk_vtiger3', 'rgCVa;3GIsTT', 'cherr1rk_vtiger3');

	// variable declaration
	$username = "";
	$email    = "";
	$errors   = array(); 

	// call the register() function if register_btn is clicked
	if (isset($_POST['register_btn']))
	{
		register();
	}

	// call the login() function if register_btn is clicked
	if (isset($_POST['login_btn']))
	{
		login();
	}

	if (isset($_GET['logout']))
	{
		session_destroy();
		unset($_SESSION['user']);
		header("location: ../login.php");
	}

	// REGISTER USER
	function register()
	{
		global $db, $errors;

		// receive all input values from the form
		$username    =  e($_POST['username']);
		$email       =  e($_POST['email']);
		$password_1  =  e($_POST['password_1']);
		$password_2  =  e($_POST['password_2']);

		// form validation: ensure that the form is correctly filled
		if (empty($username))
		{ 
			array_push($errors, "Username is required"); 
		}
		if (empty($email))
		{ 
			array_push($errors, "Email is required"); 
		}
		if (empty($password_1))
		{ 
			array_push($errors, "Password is required"); 
		}
		if ($password_1 != $password_2)
		{
			array_push($errors, "The two passwords do not match");
		}

		// register user if there are no errors in the form
		if (count($errors) == 0)
		{
			$password = md5($password_1);//encrypt the password before saving in the database

			if (isset($_POST['user_type']))
			{
				$user_type = e($_POST['user_type']);
				$query = "INSERT INTO users (username, email, user_type, password) 
						  VALUES('$username', '$email', '$user_type', '$password')";
				mysqli_query($db, $query);
				$_SESSION['success']  = "New user successfully created!!";
				header('location: home.php');
			}
			else
			{
				$query = "INSERT INTO users (username, email, user_type, password) 
						  VALUES('$username', '$email', 'user', '$password')";
				mysqli_query($db, $query);

				// get id of the created user
				$logged_in_user_id = mysqli_insert_id($db);

				$_SESSION['user'] = getUserById($logged_in_user_id); // put logged in user in session
				$_SESSION['success']  = "You are now logged in";
				header('location: index.php');				
			}
		}
	}

	// return user array from their id
	function getUserById($id)
	{
		global $db;
		
		$query = "SELECT * FROM users WHERE id=" . $id;
		
		$result = mysqli_query($db, $query);

		$user = mysqli_fetch_assoc($result);
		return $user;
	}	

	// LOGIN USER
	function login()
	{
		global $db, $username, $errors;

		// grap form values
		$username = e($_POST['username']);
		$password = e($_POST['password']);
		
		// make sure form is filled properly
		if (empty($username))
		{
			array_push($errors, "Username is required");
		}
		if (empty($password))
		{
			array_push($errors, "Password is required");
		}

		// attempt login if no errors on form
		if (count($errors) == 0)
		{
			$password = md5($password);
			
			$query = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
			
			$results = mysqli_query($db, $query);

			if (mysqli_num_rows($results) == 1)
			{ // user found
				// check if user is admin or user
				$logged_in_user = mysqli_fetch_assoc($results);
				if ($logged_in_user['user_type'] == 'admin') {

					$_SESSION['user'] = $logged_in_user;
					$_SESSION['success']  = "You are now logged in";
					header('location: admin/home.php');		  
				}else{
					$_SESSION['user'] = $logged_in_user;
					$_SESSION['success']  = "You are now logged in";

					header('location: index.php');
				}
			}
			else
			{
				array_push($errors, "Wrong username/password combination");
			}
		}
	}

	function isLoggedIn()
	{
		if (isset($_SESSION['user']))
		{
			return true;
		}else
		{
			return false;
		}
	}

	function isAdmin()
	{
		if (isset($_SESSION['user']) && $_SESSION['user']['user_type'] == 'admin' )
		{
			return true;
		}else
		{
			return false;
		}
	}

	// escape string
	function e($val)
	{
		global $db;
		return mysqli_real_escape_string($db, trim($val));
	}

	function display_error()
	{
		global $errors;

		if (count($errors) > 0)
		{
			echo '<div class="error">';
				foreach ($errors as $error) {
					echo $error .'<br>';
				}
			echo '</div>';
		}
	}
	
	
	// CHAT FUNCTIONS	
	if(isset($_GET["action"]))
	{
       if($_GET["action"] == "getUser")
	   {
          getUser();
       }
	   else if($_GET["action"] == "getAllContacts")
	   {
          getAllContacts();
       }
	   else if($_GET["action"] == "getAllGroups")
	   {
          getAllGroups();
       }
	   else if($_GET["action"] == "getAllMessages")
	   {
          getAllMessages();
       }
	   else if($_GET["action"] == "sendMessage")
	   {
          sendMessage();
       }
	   else if($_GET["action"] == "checkNewMessage")
	   {
          checkNewMessage();
       }
	   else if($_GET["action"] == "markRead")
	   {
          markRead();
       	}else if($_GET["action"] == "deletedchat"){
			deletedchat();
		}else if($_GET["action"] == "usersdetails"){
			usersdetails();
		}
		else if($_GET["action"] == "messagedata"){
			messagedata();
		}else if($_GET["action"] == "savecontact"){
			savecontact();
		}else if($_GET["action"] == "newcontact"){
			newcontact();
		}else if($_GET["action"] == "statuschange"){
			statuschange();
		}else if($_GET['action'] == "sendImage"){
			sendImage();
		}
		else if($_GET['action'] == "previces"){
			previces();
		}else if($_GET['action'] == "DeleteMsg"){
			DeleteMsg();
		}
	}
	function DeleteMsg(){
		$data =  file_get_contents("php://input");
		$data = json_decode($data);
		$id = $data->id;
		$query = "DELETE FROM `message` WHERE id = ".$id;
		$data = selectQuery($query);
		if($data['status'] != "ok"){
			echo json_encode(['status'=>"error","message" => "database error"]);
			exit;
		}
		echo json_encode(['status'=>"ok","message" =>"deleted"]);
		exit;
	}
	function sendImage(){
		 global $base_url;
		 $userid = $_POST['userId'];
		 $userno = $_POST['userno'];
		 $caption = $_POST['caption'];
		 $type = $_POST['type'];
		 $path = '/whatsfile/'.$userid;
	
		$data = savemedia($_FILES,$path);
		
		

		if($data != 0){
			foreach($data as $key => $doc){
				$mes['link'] = $base_url.$path.'/'.$doc;
	
				if($key == 0){
					$mes['caption'] = $caption ;
				}
				if($type == "image"){
					$message =  bodymessage(3,$mes,$userno);
				}else if($type == "document"){
					$message =  bodymessage(8,$mes,$userno);
					
				}
			  echo 	firstresponse($message);
			}
			
		}
		
		die;
	}
	function previces(){
		$data =  file_get_contents("php://input");
		$data = json_decode($data);
	
		$user_id = $data->user_id;
		$offset = $data->nextmes;

		$query = "SELECT * FROM (
					SELECT u.id AS user_id, u.username, u.pic, m.*, u.number 
					FROM users AS u 
					LEFT JOIN message AS m 
					ON u.id = m.sender OR u.id = m.recvId 
					WHERE u.id = '".$user_id."' 
					ORDER BY m.id DESC 
					LIMIT 40 OFFSET ".$offset."
				) AS subquery 
				ORDER BY subquery.id DESC";
		$data = selectQuery($query);
		if($data['status'] != "ok"){
			echo json_encode(['status'=>"error","message" => "database error"]);
			exit;
		}
		echo json_encode(['status'=>"ok","message" => $data['data']]);
		exit;
	}
	function statuschange(){
		$data =  file_get_contents("php://input");
		
		$id = json_decode($data);
	
		if(isset($id->id)){
			$query = "UPDATE `message` SET `status`='2' where id= ".$id->id;
			$data = selectQuery($query);
		if($data['status'] != "ok"){
			echo json_encode(['status'=>"error","message" => "database error"]);
			exit;
		}
		echo json_encode(['status'=>"ok","message" => "insert"]);
		exit;
		}
		echo json_encode(['status'=>"error","message" => "database error"]);
		exit;
	}
	function newcontact(){
		$data =  file_get_contents("php://input");
		
		$user_details = json_decode($data); 
	
		$phone_no = "91".trim($user_details->phone);
	
		$query = "INSERT INTO `users`(`username`, `user_type`, `number`, `pic`) VALUES ('".$user_details->name."','user','".$phone_no."','removedp.png')";
		$data = selectQuery($query);
		if($data['status'] != "ok"){
			echo json_encode(['status'=>"error","message" => "database error"]);
			exit;
		}
		echo json_encode(['status'=>"ok","message" => "insert"]);
		exit;
	}
 function savecontact(){
	$data =  file_get_contents("php://input");
	$user_details = json_decode($data);
	$query = "UPDATE `users` SET `username`='".$user_details->name."' WHERE `number` = '".$user_details->phone."'";
	$data = selectQuery($query);
	if($data['status'] != "ok"){
		echo json_encode(['status'=>"error","message" => "database error"]);
		exit;
	}
	echo json_encode(['status'=>"ok","message" => "insert"]);
	exit;
 }
function messagedata(){
	$id = file_get_contents("php://input");
	
	 $user_id = json_decode($id)->id;
	$query = "SELECT * FROM ( SELECT u.id AS user_id, u.username, u.pic, m.*, u.number FROM users AS u LEFT JOIN message AS m ON u.id = m.sender OR u.id = m.recvId WHERE u.id = '".$user_id."' ORDER BY m.id DESC LIMIT 40 ) AS subquery ORDER BY subquery.id ASC";

	$data = selectQuery($query);
	echo json_encode($data);
}
function deletedchat(){

	$data = file_get_contents("php://input");
	
	$user_no = json_decode($data);
	if(isset($user_no->user_no)){
		$query = "DELETE message
		FROM message
		JOIN users ON (message.sender = users.id OR message.recvId = users.id)
		WHERE users.number = '".$user_no->user_no."'";
		$dir = '../whatsfile/'.$user_no->userId; 

// Print the real directory path for debugging
echo "Directory path: " . realpath($dir) . "<br>";

// Verify if the directory exists
if (is_dir($dir)) {
    echo "Directory exists. Proceeding to delete...<br>";

    // Build the cURL command
    $curlCommand = 'rm -rf "' . $dir . '"';

    // Execute the shell command
    $output = shell_exec($curlCommand);

    // Check if the directory is deleted
    if (!is_dir($dir)) {
        echo "Folder successfully deleted.";
    } else {
        echo "Failed to delete folder.";
    }

    // Print the output from shell_exec for debugging
    echo "Output: " . $output;

} else {
    echo "Directory does not exist.";
}

		

			
		
		
	}



}

function usersdetails(){
	$query = "SELECT u.id AS user_id, u.username AS user_name, m.body AS last_message, m.time AS last_message_time, m.mess_id, u.pic, m.status, m.recvIsGroup FROM users u LEFT JOIN ( SELECT m1.id AS mess_id, m1.sender, m1.recvId, m1.body, m1.time, m1.status, m1.recvIsGroup FROM message m1 INNER JOIN ( SELECT GREATEST(m.sender, m.recvId) AS user_id, MAX(m.time) AS last_time, MAX(m.id) AS last_id FROM message m GROUP BY GREATEST(m.sender, m.recvId) ) m2 ON (m1.sender = m2.user_id OR m1.recvId = m2.user_id) AND m1.time = m2.last_time AND m1.id = m2.last_id ) m ON (u.id = m.sender OR u.id = m.recvId) WHERE u.user_type = 'user' ORDER BY last_message_time DESC";

	$data = selectQuery($query);
	echo json_encode($data);
}
	function getUser()
	{
		$myId	= $_SESSION['user']['id'];
		
		$return = getUserByIdRow($myId);
		
		$user = [
	       'id'     => (int) $return->id,
		   'name'   => $return->username,
		   'number' => $return->number,
		   'pic'    => '../assets/images/'.$return->pic
	    ];
	    header('Content-Type: application/json');
	    echo json_encode($user);
	}
	
	function getAllContacts()
	{
		global $db;		
		$query = "SELECT * FROM users";
		$result = mysqli_query($db, $query);
		
		$list = [];
		foreach($result as $contact)
		{			
			$user = [
				'id'       => (int) $contact['id'],
				'name'     => $contact['username'],
				'number'   => $contact['number'],
				'pic'      => '../assets/images/'.$contact['pic'],
				'lastSeen' => date('Y-m-d h:i:s a', time())
			];
			array_push($list, $user);
		}
		header('Content-Type: application/json');
	    echo json_encode($list);
	}
	
	function getAllGroups()
	{
		$groups = [
	        [
	            'id'       => 1,
		        'name'     => 'Programers',
		        'number'   => '+5531975999387',
			    'members'  => [0, 1, 3, 5],
		        'pic'      => 'assets/images/0923102932_aPRkoW.jpg',
		    ],
		];
		header('Content-Type: application/json');
		echo json_encode($groups);
	}
	
	function getAllMessages()
	{
		global $db;
		$myId	= $_SESSION['user']['id'];
		$query  = "SELECT * FROM message WHERE recvId='$myId' OR sender='$myId'";
		$result = mysqli_query($db, $query) or die (mysqli_error($db));
		
		$thread = [];
		foreach($result as $message)
		{			
			$chat = [
				'id' 		  => (int) $message['id'],
				'sender' 	  => (int) $message['sender'],
				'recvId'      => (int) $message['recvId'],
				'body' 		  => $message['body'],
				'status' 	  => (int) $message['status'],
				'recvIsGroup' => false,
				'time' 		  => $message['time'],
			];
			array_push($thread, $chat);
		}
		header('Content-Type: application/json');
	    echo json_encode($thread);
	}
	
	function sendMessage()
	{
		$input = file_get_contents('php://input');
		$data = json_decode($input, true); // true to get an associative array
	
		if (!isset($data['msg'])) {
			echo json_encode(['status' => 'error', 'message' => 'Invalid message data']);
			exit;
		}
	
		$msg = $data['msg'];	
		
		$id = 0;
		$sender      = $msg['sender'];
		$recvId      = $msg['recvId'];
		$body        = $msg['body'];
		$status      = $msg['status'];
		$recvIsGroup = 0;
	    
		if ($id == 0)
		{			
			global $db;
			$query = "INSERT INTO message (sender, recvId, body, status, recvIsGroup)
						  VALUES('$sender', '$recvId', '$body', $status , '$recvIsGroup')";	
			mysqli_query($db, $query);

			// get last id of the created message
			$messageLastId = mysqli_insert_id($db);		
			
			$message = (array)returnLastMessage(messageLastId: $messageLastId);
				
			$arr = [
				'id'          => isset($message['id']) ? (int) $message['id'] : 0,
				'sender'      => isset($message['sender']) ? (int) $message['sender'] : 0,
				'recvId'      => isset($message['recvId']) ? (int) $message['recvId'] : 0,
				'body'        => isset($message['body']) ? $message['body'] : '',
				'status'      => isset($message['status']) ? (int) $message['status'] : 0,
				'recvIsGroup' => isset($message['recvIsGroup']) ? (bool) $message['recvIsGroup'] : false,
				'time'        => isset($message['time']) ? $message['time'] : '',
			];
	
			$query = "SELECT number from users where id = '".$recvId  ."'";
			$data = selectQuery(query: $query);
			if($data['status'] != "ok"){
				echo json_encode(['status'=>"error","message" => "database error"]);
				exit;
			}
	
			$phone = $data['data'][0]['number'];
		
			$messageData = [
				"messaging_product" => "whatsapp",
				"recipient_type" => "individual",
				"to" => $phone,
				"type" => "text",
				"text" => [
					"preview_url" => false,
					"body" => $body
				]];
				echo json_encode(['status'=>"ok", "data"=>$messageLastId]);
				$res = whatsappsendtext($messageData);
				if($res['status'] == "ok"){
					$qry = "UPDATE `message` SET `status`= 2 WHERE  id = ".$messageLastId;

					selectQuery($qry);
				}
				header('Content-Type: application/json');
				return  json_encode($arr);

		}
		
	}
	
	function checkNewMessage()
	{
		$data = file_get_contents("php://input");
	
		$user_data = json_decode($data);

		$query = "SELECT m.* FROM message m JOIN users u ON m.`sender` = u.id OR m.`recvId` = u.id WHERE u.number = '".$user_data->no."' AND m.id > ".$user_data->id." ORDER BY m.id ASC";
		$data = selectQuery($query);
		echo json_encode($data,JSON_UNESCAPED_UNICODE);

		
		// global $db;
		// $myId = $_SESSION['user']['id'];
		
		// $new_exists = false;		
		// $query = "SELECT * FROM last_seen WHERE user_id ='$myId'";
		// $result = mysqli_query($db, $query) or die (mysqli_error($db));
		// $object = mysqli_fetch_assoc($result);
		// $messageId = empty($object) ? 0 : $object['message_id'];
		
		// $exists = latestMessage($messageId);
		
		// if($exists)
		// {
		// 	$new_exists = true;
		// }
		// // THIS WHOLE SECTION NEED A GOOD OVERHAUL TO CHANGE THE FUNCTIONALITY
		// if ($new_exists)
		// {
		// 	$new_messages = unreadMessage();
		// 	$thread = [];
		//     foreach($new_messages as $message)
		//     {			
		// 	    $chat = [
		// 		    'id' 		  => (int) $message['id'],
		// 		    'sender' 	  => (int) $message['sender'],
		// 		    'recvId'      => (int) $message['recvId'],
		// 		    'body' 		  => $message['body'],
		// 			'status' 	  => (int) $message['status'],
		// 		    'recvIsGroup' => false,
		// 		    'time' 		  => $message['time'],
		// 	    ];
		// 	    array_push($thread, $chat);
		//     }
			
		// 	updateLastSeen();
			
		//     header('Content-Type: application/json');
	    //     echo json_encode($thread);
		// }
	}
	
	//ANOTHER FUNCTIONS FOR CHAT	
	// return object user logged by session "$myId"
	function getUserByIdRow($myId)
	{
		global $db;
		
		$query = "SELECT * FROM users WHERE id='$myId' LIMIT 1";		
		$result = mysqli_query($db, $query) or die (mysqli_error($db));
		
		while ($obj = mysqli_fetch_object($result))
		{
			return $obj;
		}
	}
	
	// return last message
	function returnLastMessage($messageLastId)
	{
		global $db;
		
		$query = "SELECT * FROM `message` WHERE id='$messageLastId' LIMIT 1";		
		$result = mysqli_query($db, $query) or die (mysqli_error($db));
		
		while ($obj = mysqli_fetch_object($result))
		{
		
			return $obj;
		}
	}
	
		function latestMessage($messageId)
	{
		global $db;
		$myId = $_SESSION['user']['id'];
		
		$query = "SELECT * FROM message WHERE recvId='$myId' AND id>'$messageId' ORDER BY time desc LIMIT 1";
		
		$result = mysqli_query($db, $query) or die (mysqli_error($db));

		if (mysqli_num_rows($result) > 0)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	function unreadMessage()
	{	
		global $db;
		$myId = $_SESSION['user']['id'];
		
		$query = "SELECT * FROM message WHERE recvId='$myId' AND status = 1 ORDER BY time asc";		
		$result = mysqli_query($db, $query) or die (mysqli_error($db));
		
		return $result;
	}
	
	function updateLastSeen()
	{
		global $db;
		$myId = $_SESSION['user']['id'];
		
		$query = "SELECT * FROM message WHERE recvId='$myId' ORDER BY time desc LIMIT 1";
		
		$result = mysqli_query($db, $query) or die (mysqli_error($db));
		$lastMessage = mysqli_fetch_assoc($result);
		$messageId = empty($lastMessage) ? 0 : $lastMessage['id'];
				
		$record = getLastUser();
		
		if(empty($record))
		{
			$query = "INSERT INTO last_seen (user_id, message_id)
						  VALUES('$myId', '$messageId')";
			$result = mysqli_query($db, $query) or die (mysqli_error($db));
		}
		else
		{
			$record = $record->id;
			$query = "UPDATE last_seen SET user_id='$myId', message_id='$messageId' WHERE id='$record'";
			$result = mysqli_query($db, $query) or die (mysqli_error($db));
		}
	}
	
	function getLastUser()
	{
		global $db;
		$myId = $_SESSION['user']['id'];
		
		$query = "SELECT * FROM last_seen WHERE user_id ='$myId' ORDER BY id desc LIMIT 1";
		$result = mysqli_query($db, $query) or die (mysqli_error($db));
		
		while ($lastUser = mysqli_fetch_object($result))
		{
			return $lastUser;
		}
	}
	
	function markRead()
	{
		global $db;
		$id = $_POST['id'];		
		$query = "UPDATE message SET status = 2 WHERE id='$id'";
		print_r($query);
		$result = mysqli_query($db, $query) or die (mysqli_error($db));		
	}

?>
