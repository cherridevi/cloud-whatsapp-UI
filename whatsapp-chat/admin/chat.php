<?php 
	include('../functions.php');

	if (!isAdmin()) {
		$_SESSION['msg'] = "You must log in first";
		header('location: ../login.php');
	}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<title>Whatsapp</title>
	<link rel="stylesheet" href="../assets/framework/bootstrap/3.3.6/css/bootstrap.min.css">
	<link rel="stylesheet" href="../assets/framework/fontawesome/v5.0.10/css/all.css">	
	<link rel="stylesheet" href="../assets/css/chat.css">
	<link rel="stylesheet" href="../assets/css/chat-adapter-bootstrap-4.css">
	
	<link rel="icon" type="image/ico" href="../assets/images/favicon-64x64.ico" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="chat-body">
<style>
/* Modal Background */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
    background-color: #fefefe;
    margin: 5% auto; /* Centered */
    padding: 20px;
    border: 1px solid #888;
    width: 50%; /* Adjust width as needed */
    max-width: 500px; /* Max width for large screens */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Shadow effect */
}

/* Close Button */
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
}

/* Form Fields */
.form-group {
    margin-bottom: 15px;
    text-align: left; /* Align label to the left */
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    font-size: 14px; /* Smaller font size for labels */
}

.form-group input {
    width: calc(100% - 20px); /* Full width minus padding */
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px; /* Smaller font size for input fields */
}

/* Submit Button */
button {
    background-color: #4CAF50; /* Green */
    color: white;
    border: none;
    padding: 15px 32px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 14px; /* Smaller font size for button */
    margin: 10px 2px;
    cursor: pointer;
    border-radius: 4px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

button:hover {
    background-color: #45a049; /* Darker green */
}

.container{
	width: 36%;
}

/* hr */
#hrs{

    margin:5px 0 !important ; 
    border-top: 1px solid #9f9a94;
}

/* Loader styles */
#loading_ring {
    border: 8px solid #f3f3f3; /* Light grey */
    border-top: 8px solid #3498db; /* Blue */
    border-radius: 50%; /* Rounded circle */
    width: 40px; /* Size of the loader */
    height: 40px; /* Size of the loader */
    animation: spin 1s linear infinite; /* Animation properties */
    margin: auto; /* Center the loader */
}

/* Spin animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Loading spinner container */
.loading-spinner {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    text-align: center;
}

/* Spinner ring */
.spinner-ring {
    border: 4px solid #f3f3f3; /* Light grey */
    border-top: 4px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 24px;
    height: 24px;
    animation: spin 1s linear infinite;
    margin-right: 10px;
}

/* Spin animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
	<div class="container-fluid" id="main-container">
		<div class="chat-row h-100">
		
			<div class="col-xs-12 col-sm-5 col-md-4 d-flex flex-column" id="chat-list-area" style="position:relative;">
				<!-- Navbar Left-->
				<div class="chat-row d-flex flex-row align-items-center p-2" id="navbar">
					<img alt="Profile Photo" class="img-fluid rounded-circle mx-2 mr-2" style="height:50px; cursor:pointer;" onclick="showProfileSettings()" id="display-pic">
					<div class="text-black font-weight-bold" id="username" style="display:none"></div>					
					<div class="d-flex flex-row align-items-center ml-auto">
						<span href="#"><i class="fas fa-power-off mx-3 text-muted d-none d-md-block"></i></span>
						<span href="#"><i class="fas fa-comment mx-3 text-muted d-none d-md-block"></i></span>
						<div class="nav-item dropdown ml-auto">
						    <span class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
						        <i class="fas fa-ellipsis-v text-muted"></i>
						    </span>
						    <ul class="dropdown-menu dropdown-menu-right">
							    <li>
								    <a class="dropdown-item" href="#" onclick="checkNewMessage()" id="check-message" style="color:white; background-color:#337ab7">Check new message</a>
							        <a class="dropdown-item" id="newcontact" href="#">New Contact</a>
							        <!-- <a class="dropdown-item" href="#">Archived</a>
							        <a class="dropdown-item" href="#">Starred</a>
							        <a class="dropdown-item" href="#">Settings</a> -->
								<a class="dropdown-item" href="home.php">Back Home</a>
							        <a class="dropdown-item" href="chat.php?logout='1'" style="color:red;">Log Out</a>
								</li>
						    </ul>
					    </div>
					</div>
				</div>
				<div id="chat-search" class="chat-row p-2" style="border-bottom: 1px solid #dadbdb;">
					<div class="form-search form-inline" style="width:100%">
					    <input type="text" class="search-query border-0" placeholder="Search or start new chat" style="width:100%; height: 32px; font-size: 14px;  border-radius: 20px;"/>
					</div>
				</div>
				<!-- Chat List -->
				<div class="chat-row" id="chat-list" style="overflow:auto;"></div>

				<div class="chat-row" id="user_list" style="overflow:auto; height:600px;"></div>

				<!-- Profile Settings -->
				<div class="d-flex flex-column w-100 h-100" id="profile-settings" style="z-index:2">
					<div class="chat-row d-flex flex-row align-items-center p-2 m-0" style="background:#009688; min-height:65px;">
						<i class="fas fa-arrow-left p-2 mx-3 my-1 text-white" style="font-size: 24px; cursor: pointer;" onclick="hideProfileSettings()"></i>
						<div class="text-white font-weight-bold">Profile</div>
					</div>
					<div class="d-flex flex-column" style="overflow:auto;">
						<img alt="Profile Photo" class="img-fluid rounded-circle my-5 justify-self-center mx-auto" id="profile-pic">
						<input type="file" id="profile-pic-input" class="d-none">
						<div class="bg-white px-3 py-2">
							<div class="text-muted mb-2"><label for="input-name">Your Name</label></div>
							<input type="text" name="name" id="input-name" class="w-100 border-0 py-2 profile-input">
						</div>
						<div class="text-muted p-3 small">
							This is not your username or pin. This name will be visible to your WhatsApp contacts.
						</div>
						<div class="bg-white px-3 py-2">
							<div class="text-muted mb-2"><label for="input-about">About</label></div>
							<input type="text" name="name" id="input-about" value="" class="w-100 border-0 py-2 profile-input">
						</div>
					</div>
				</div>				
			</div>
			
			<!-- Message Area -->
			<div class="d-none d-sm-flex flex-column col-xs-12 col-sm-7 col-md-8 p-0 h-100" id="message-area">
				<div class="w-100 h-100 overlay"></div>
				<!-- Navbar Right-->
				<div class="chat-row d-flex flex-row align-items-center p-2 m-0 w-100" id="navbar" style="border-bottom: 1px solid #d7d0ca;">
					<div class="d-block d-sm-none">
						<i class="fas fa-arrow-left p-2 mr-2 text-white" style="font-size: 24px; cursor: pointer;" onclick="showChatList()"></i>
					</div>
					<a href="#"><img src="" alt="Profile Photo" class="img-fluid rounded-circle mx-2 mr-2" style="height:50px;" id="pic"></a>
					<div class="d-flex flex-column">
						<div class="text-black font-weight-bold-apagar" id="name"></div>
						<div class="text-black small" id="details" style="color: rgba(0, 0, 0, 0.6);"></div>
					</div>
					<div class="d-flex flex-row align-items-center ml-auto">
						<a href="#"><i class="fas fa-search mx-3 text-muted d-none d-md-block"></i></a>
						<!-- <a href="#"><i class="fas fa-paperclip mx-3 text-muted d-none d-md-block"></i></a> -->
						<div class="nav-item dropdown ml-auto">
						    <span class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
						        <i class="fas fa-ellipsis-v text-muted"></i>
						    </span>
						    <ul class="dropdown-menu dropdown-menu-right">
							    <li>
								    <a class="dropdown-item" href="#"  id="deleted_chat" style="color:white; background-color:#337ab7">Deleted Chat</a>
							        <a class="dropdown-item" id="save_contact" href="#">Save Contact</a>
								</li>
						    </ul>
					    </div>
					</div>
				</div>
				<!-- Messages -->
				<div class="doc-page">
						<div class="doc-top">
							<i class="fa-solid fa-xmark" id="closeIcon" style="cursor: pointer;"></i>
							<h4 style="text-align: center;" id="heading_Sec"></h4>
						</div>
						<div class="select_div">
						</div>
						<div style="position: relative;">
						<form class="doc-form">
								<input type="text" id="caption" name="" placeholder="Add a caption" class="doc-input">
						</form>
						<i class="fa-solid fa-paper-plane send-icon" id="sendIcon" onclick="sendmedia()"></i>
					</div>
						<!-- <div class="doc-foot">
							<i class="fa-solid fa-paper-plane send-icon"></i>
						</div> -->
					</div>

				<div class="d-flex flex-column messages-bg" id="messages"></div>

				<!-- Input -->
				<div class="d-none justify-self-end align-items-center flex-row" id="input-area">
					
					<i class="far fa-smile text-muted px-4" style="font-size:24px; cursor:pointer;"></i>
					<div>
						<a href="#" id="toggleDoc"><i class="fas fa-paperclip mx-3 text-muted d-none d-md-block"></i></a>
						<span class="doc-span" id="docSpan">
							<div class="doc-div">
								<ul>

								    <li id="getDoc">
										<i class="fa-solid fa-file-lines doc-icon" style="color: #5d00a3;"></i>
										<p>Document</p>
									</li>
									<input type="file" id="fileInput" style="display: none;" multiple>
				
									<li id="getImg">
									    <i class="fa-solid fa-images doc-icon" style="color: #ff2e74;"></i>
								        <p>Image/Videos</p>
								    </li>
								    <input type="file" id="ImageInput" style="display: none;" multiple>
									
								</ul>
							</div>
						</span>
				    </div>
					<input type="text" name="message" id="input" placeholder="Type a message" class="flex-grow-1 border-0 px-3 py-2 my-3 rounded-20 -shadow-sm; word-wrap: break-word;">
					<i class="fas fa-paper-plane text-muted px-4" style="cursor:pointer;" onclick="sendMessage()"></i>
				</div>
				</div>

			</div>
			
		</div>

	</div>
<!-- The Modal for Existing Contact -->
<div id="contactModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Contact Form</h2>
        <form id="contactForm">
            <input type="hidden" id="contactId" name="contactId">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="contactname" name="name" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="contact_phone" name="phone" required>
            </div>
            <button type="submit">Save</button>
        </form>
    </div>
</div>

<!-- The Modal for New Contact -->
<div id="newcontactmodel" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>New Contact Form</h2>
        <form id="newcontactForm">
            <div class="form-group">
                <label for="new_name">Name:</label>
                <input type="text" id="new_contactname" name="name" required>
            </div>
            <div class="form-group">
                <label for="new_phone">Phone:</label>
                <input type="text" id="new_contact_phone" name="phone" required>
            </div>
            <button type="submit">Save</button>
        </form>
    </div>
</div>
	<script src="../assets/framework/jquery/jquery.min.js?ver=2.2.4"></script>
	<!--<script src="assets/js/jquery-3.3.1.slim.min.js"></script>
	<script src="../assets/js/popper.min.js"></script>-->
	<script src="../assets/framework/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script src="../assets/js/datastore.js"></script>
	<script src="../assets/js/date-utils.js"></script>
	<script src="../assets/js/script.js"></script>

	<script type="text/javascript">
		// document-select-start
const uploadBtn = document.getElementById('getDoc');
const fileInput = document.getElementById('fileInput');
const docPage = document.querySelector('.doc-page');
const selectDiv = document.getElementById('heading_Sec');
const getImg = document.getElementById('getImg'); 
const imageInput = document.getElementById('ImageInput'); 
const selectedDiv = document.querySelector('.select_div'); // Changed to use class
const fileSizeDisplay = document.getElementById('file-size');





uploadBtn.addEventListener('click', () => {
	fileInput.value = '';
    fileInput.click(); // Simulate a click on the hidden file input
    console.log('uploadBtn')
});


fileInput.addEventListener('change', (event) => {
	caption.value = "";
	selectDiv.innerHTML = '';
	selectedDiv.innerHTML = `<i class="fa-solid fa-file my-doc"></i>
							<h3>No preview available</h3>
							<p id="file-size"></p>`;
    const files = event.target.files;
     // Clear previous file names
    if (files.length > 0) {
        docPage.style.display = 'block'; // Show the doc-page
        for (let i = 0; i < files.length; i++) {
            const fileName = document.createElement('p');
            fileName.classList.add('truncate');
            fileName.textContent = truncateFileName(files[i].name); // Set the file name
            heading_Sec.appendChild(fileName); // Add the file name to the select_div
        }
    } else {
        console.log('No files selected.');
    }
    console.log('fileInput')
});
function truncateFileName(name) {
    const maxLength = 50; // Set your max length
    return name.length > maxLength ? name.slice(0, maxLength) + '...' : name;
}
// document-select-end


// Close the doc-page when the x-mark icon is clicked
document.getElementById('closeIcon').addEventListener('click', () => {
    docPage.style.display = 'none'; // Hide the doc-page
    inputArea.style.display = ""; 
});



fileInput.addEventListener('change', () => {
    inputArea.style.setProperty('display', 'none', 'important'); // Hide input-area
});


// image-toggle


getImg.addEventListener('click', () => {
	imageInput.value = '';
    imageInput.click(); // Simulate a click on the hidden file input
});

imageInput.addEventListener('change', (event) => {
	caption.value = "";
	selectDiv.innerHTML = '';
	selectedDiv.innerHTML = ''; // Clear previous content
	selectedDiv.innerHTML = `<h3>No preview available</h3>
							<p id="file-size"></p>`;
    const files = event.target.files;
   
    if (files.length > 0) {
        docPage.style.display = 'block'; // Show the doc-page
        const file = files[0];

        // Create an image element for the preview
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file); // Use object URL for the image source
        img.alt = file.name; // Set alt text
		img.id = "imageFiles";
        img.style.maxWidth = '300px'; // Adjust as necessary
        img.style.maxHeight = '300px';
        img.style.width = 'auto';
        img.style.height = 'auto';

        selectedDiv.innerHTML = '';

        selectedDiv.appendChild(img)

        // Remove the icon
        const icon = selectedDiv.querySelector('.my-doc');
        if (icon) {
            icon.remove(); // Remove the icon
        }



    } else {
        console.log('No files selected.');
    }
});


imageInput.addEventListener('change', () => {
    inputArea.style.setProperty('display', 'none', 'important'); // Hide input-area
});

imageInput.addEventListener('change', () => {
    docSpan.style.display = "none"; // Hide docSpan
});



	</script>
</body>

</html>
