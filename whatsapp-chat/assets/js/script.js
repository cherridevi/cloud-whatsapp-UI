
let getById = (id, parent) => parent ? parent.getElementById(id) : getById(id, document);
let getByClass = (className, parent) => parent ? parent.getElementsByClassName(className) : getByClass(className, document);
let userList = document.getElementById('user_list');
let nextmessagelimit = 40;
// Get DOM elements
let chatMenu = ""
const saveContact = document.getElementById('save_contact');
const contactModal = document.getElementById('contactModal');
const closeModal = document.querySelector('.close');
const nameInput = document.getElementById('contactname');
const phoneInput = document.getElementById('contact_phone');
let caption = document.getElementById('caption');
const inputArea = document.getElementById('input-area');


// New Contact Modal Elements
const newcontact = document.getElementById('newcontact');
const newcontactmodel = document.getElementById('newcontactmodel');
const newCloseModal = newcontactmodel.querySelector('.close');
const newNameInput = document.getElementById('new_contactname');
const newPhoneInput = document.getElementById('new_contact_phone');
const newcontactForm = document.getElementById('newcontactForm');
let lastMessageId = 0;
let chatuserid = '';
const DOM =  {
	chatListArea: getById("chat-list-area"),
	messageArea: getById("message-area"),
	inputArea: getById("input-area"),
	chatList: getById("chat-list"),
	messages: getById("messages"),
	chatListItem: getByClass("chat-list-item"),
	messageAreaName: getById("name", this.messageArea),
	messageAreaPic: getById("pic", this.messageArea),
	messageAreaNavbar: getById("navbar", this.messageArea),
	messageAreaDetails: getById("details", this.messageAreaNavbar),
	messageAreaOverlay: getByClass("overlay", this.messageArea)[0],
	messageInput: getById("input"),
	profileSettings: getById("profile-settings"),
	profilePic: getById("profile-pic"),
	profilePicInput: getById("profile-pic-input"),
	inputName: getById("input-name"),
	username: getById("username"),
	displayPic: getById("display-pic"),
};

let mClassList = (element) => {
	return {
		add: (className) => {
			element.classList.add(className);
			return mClassList(element);
		},
		remove: (className) => {
			element.classList.remove(className);
			return mClassList(element);
		},
		contains: (className, callback) => {
			if (element.classList.contains(className))
				callback(mClassList(element));
		}
	};
};


let areaSwapped = false;

// 'chat' is used to store the current chat
// which is being opened in the message area
let chat = null;

// this will contain all the chats that is to be viewed
// in the chatListArea
let chatList = [];

// this will be used to store the date of the last message
// in the message area
let lastDate = "";



let addDateToMessageArea = (date) => {
	DOM.messages.innerHTML += `	
	<div class="dt-message mx-auto my-2 bg-primary text-white small py-1 px-2 rounded">
	    <span class="dt-message-span">
		    ${date}
		</span>
	</div>
	`;
};
let oldDateToMessageArea = (date) => {
	msgdata = `	
	<div class="dt-message mx-auto my-2 bg-primary text-white small py-1 px-2 rounded">
	<span class="dt-message-span">
	${date}
	</span>
	</div>
	`;
	DOM.messages.insertAdjacentHTML('afterbegin', msgdata);
};

// Helper function to create image message
function createImageMessage(parsedMessage, msg, sendStatus) {

	let caption = "";
	let url = "";
	let senderDiv = "";
	let mark = "";
	if(parsedMessage.caption != "" && parsedMessage.caption != null){
		caption = `<div class="body m-2" >${parsedMessage.caption}</div>
		<div class="time ml-auto small text-right flex-shrink-0 align-self-end text-muted" style="width:75px; position: relative; top: 7px; font-size: 11px;">
				${mDate(msg.time).getTime()}
				${(msg.sender === user.id) ? sendStatus : ""}
			</div>
		`;
	}else{
		caption = `<span class="time">${mDate(msg.time).getTime()}</span>`;
	}
	if(parsedMessage.url){
		url = parsedMessage.url;
	}else{
		url = parsedMessage.image.link;
		let updatedUrl = url.split('/').pop(); 
		senderDiv= "senderDiv";
		url = updatedUrl;
		 mark = `<i class="fa fa-check double-tick" aria-hidden="true"></i>`;
	}
	return `
	<div class="align-self-${msg.sender === "1" ? "end self" : "start"} p-1-apagar my-1 mx-5 rounded-apagar bg-white shadow-sm-apagar rounded-7-5 m-p-r m-shadow message-item tail">
		<span class="tail-container"></span>
		<div>
           <ul class="${msg.sender === "1" ? "my-droup" : "droup-li"}"  id ="msg_${msg.id}">
              <div>
			    <li>
                  <li onclick= "deleteMsg(${msg.id})" >Delete</li>
			    </li>
			   </div>
			</ul>
		</div>
		<div class="options">
			<a href="#" onclick="option(msg_${msg.id})" ><i class="fas fa-angle-down text-muted px-2"></i></a>
		</div>
		<div>
			<div class="body mr-2 ${senderDiv}">
				<a href="/whatsfile/${chatuserid}/${url}" target="_blank">
				<img src="/whatsfile/${chatuserid}/${url}" alt="img" style="object-fit: cover; height:300px; width:250px; position: relative;" >
				</a>
				</div>
				<div class="time ml-auto small text-right flex-shrink-0 align-self-end text-muted" style="width:75px; position: relative; top: 7px; font-size: 11px;" bis_skin_checked="1">
					${caption} ${mark}

				
				</div>

		</div>
	</div>
	`;
}
function createDocumentMessage(parsedMessage, msg, sendStatus){
	// console.log(parsedMessage)
	let url = "";
	let caption = "";
	let updatedUrl = "";
	if(parsedMessage.url){
		url = parsedMessage.url;
		updatedUrl = `/whatsfile/${chatuserid}/${url}`;
		caption = parsedMessage.caption;
	}else{
		updatedUrl = parsedMessage.document.link;

		url = updatedUrl.split('/').pop(); 
		caption = parsedMessage.document.caption;
	}
	return `
	<div class="align-self-${msg.sender === "1" ? "end self" : "start"} p-1-apagar my-1 mx-5 rounded-apagar bg-white shadow-sm-apagar rounded-7-5 m-p-r m-shadow message-item tail">
		<span class="tail-container"></span>
		<div>
           <ul class="${msg.sender === "1" ? "my-droup" : "droup-li"}"  id ="msg_${msg.id}">
              <div>
			    <li>
                  <li onclick= "deleteMsg(${msg.id})" >Delete</li>
			    </li>
			   </div>
			</ul>
		</div>
		<div class="options">
			<a href="#" onclick="option(msg_${msg.id})" ><i class="fas fa-angle-down text-muted px-2"></i></a>
			
		</div>
		<div>
		<div class ="card-type d-flex flex-row body mr-2">
			<div class = "icon-div"><i class="fa fa-file-text fa-3x" aria-hidden="true"></i></div>
			<div class="body m-2 document-body">${url}</div>
			<div class="body mr-2"><a href="${updatedUrl}" download><i class="fa fa-arrow-circle-down fa-2x" aria-hidden="true"></i></a></div>
			</div>
			<div class="body m-2" bis_skin_checked="1">${caption}</div>
			<div class="time ml-auto small text-right flex-shrink-0 align-self-end text-muted" style="width:75px; position: relative; top: 7px; font-size: 11px;">
			

			${mDate(msg.time).getTime()}
			${(msg.sender === user.id) ? sendStatus : ""}
			${msg.sender === "1" ? `<i class="fa fa-check double-tick" aria-hidden="true"></i>` : ""}
			</div>
		</div>
	</div>
	`;
}

function  createListMessage(parsedMessage, msg, sendStatus){
	let section = parsedMessage.sections[0].rows;
	let sectiondiv = section.map(ele => {
		return `<li class="body mr-2">${ele.title}</li>`; 
	}).join('');
	
	
	return  `
	<div class="align-self-${msg.sender === "1" ? "end self" : "start"} p-1-apagar my-1 mx-5 rounded-apagar bg-white shadow-sm-apagar rounded-7-5 m-p-r m-shadow message-item tail">
		<span class="tail-container"></span>
		<div>
           <ul class="${msg.sender === "1" ? "my-droup" : "droup-li"}"  id ="msg_${msg.id}">
              <div>
			    <li>
                  <li onclick= "deleteMsg(${msg.id})" >Delete</li>
			    </li>
			   </div>
			</ul>
		</div>
		<div class="options">
			<a href="#" onclick="option(msg_${msg.id})" ><i class="fas fa-angle-down text-muted px-2"></i></a>
			
		</div>
		
		<div class=" flex-row">
			<div class=" d-flex body mr-2">${parsedMessage.body.text} <div class="time ml-auto small text-right flex-shrink-0 align-self-end text-muted" style="width:75px; position: relative; top: 7px; font-size: 11px;">
				${mDate(msg.time).getTime()}
				${(msg.sender === user.id) ? sendStatus : ""}
				${msg.sender === "1" ? `<i class="fa fa-check double-tick" aria-hidden="true"></i>` : ""}
			</div></div>
			<hr id="hrs">
			<ul style ="list-style: none;">
				${sectiondiv}
			</ul>
		</div>
	</div>
	`;
}
function createTemplateMessage(parsedMessage, msg, sendStatus){
	return `
	<div class="align-self-${msg.sender === "1" ? "end self" : "start"} p-1-apagar my-1 mx-5 rounded-apagar bg-white shadow-sm-apagar rounded-7-5 m-p-r m-shadow message-item tail">
		<span class="tail-container"></span>
		<div>
           <ul class="${msg.sender === "1" ? "my-droup" : "droup-li"}"  id ="msg_${msg.id}">
              <div>
			    <li>
                  <li onclick= "deleteMsg(${msg.id})" >Delete</li>
			    </li>
			   </div>
			</ul>
		</div>
		<div class="options">
			<a href="#" onclick="option(msg_${msg.id})" ><i class="fas fa-angle-down text-muted px-2"></i></a>
		</div>
		
		<div class="d-flex flex-row">
			<div class="body mr-2">${parsedMessage.title}</div>
			<div class="time ml-auto small text-right flex-shrink-0 align-self-end text-muted" style="width:75px; position: relative; top: 7px; font-size: 11px;">
				${mDate(msg.time).getTime()}
				${(msg.sender === user.id) ? sendStatus : ""}
				${msg.sender === "1" ? `<i class="fa fa-check double-tick" aria-hidden="true"></i>` : ""}
			</div>
		
		</div>
	</div>
	`;
}
function createTextWithButtonsMessage(parsedMessage, msg, sendStatus){
	let body = parsedMessage.body.text;
	let button = parsedMessage.buttons;
	let htmlbtn = button.map(ele => {
	   return `<div style="margin-top:-2px !important;" data-setid="${ele.id}" class="querry-btn align-self-end self p-1-apagar my-1 mx-5 rounded-apagar bg-white shadow-sm-apagar rounded-7-5 m-p-r m-shadow message-item">
		   <div class="buttons new-button">${ele.title}</div>
	   </div>`;
   }).join('');

   // Convert Unicode escape sequences to actual emojis
   let header = parsedMessage.header;

   //button 

   return `<div class="align-self-${msg.sender === "1" ? "end self" : "start"} weight p-1-apagar my-1 mx-5 rounded-apagar bg-white shadow-sm-apagar rounded-7-5 m-p-r m-shadow message-item tail">
	   <div>
           <ul class="${msg.sender === "1" ? "my-droup" : "droup-li"}"  id ="msg_${msg.id}">
              <div>
			    <li>
                  <li onclick= "deleteMsg(${msg.id})" >Delete</li>
			    </li>
			   </div>
			</ul>
		</div>
	   <div class="whatsapp-message">
		   <div class="header">
			   <h4>${header}</h4>
		   </div>
		   <div class="options">
			<a href="#" onclick="option(msg_${msg.id})" ><i class="fas fa-angle-down text-muted px-2"></i></a>
			</div>
		   <div class="body">
			   <div class="d-flex flex-row">
				   <div class="body mr-2">
				   ${body}
				   </div>
				   
			   </div>
		   </div>
		   <div class="time ml-auto small text-right flex-shrink-0 align-self-end text-muted" style="width:75px; position: relative; top: 7px; font-size: 11px;">
				   ${mDate(msg.time).getTime()}
				   ${(msg.sender === user.id) ? sendStatus : ""}
				   ${msg.sender === "1" ? `<i class="fa fa-check double-tick" aria-hidden="true"></i>` : ""}
			   </div>
	   </div>
	   </div>
	   
	   
	   </div>
	   ${htmlbtn}
	   </div>
	   `;
}
function createPlainTextMessage(msg, sendStatus){
	return `
	<div class="align-self-${msg.sender === "1" ? "end self" : "start"} p-1-apagar my-1 mx-5 rounded-apagar bg-white shadow-sm-apagar rounded-7-5 m-p-r m-shadow message-item tail">
		<span class="tail-container"></span>
		<div>
           <ul class="${msg.sender === "1" ? "my-droup" : "droup-li"}"  id ="msg_${msg.id}">
              <div>
			    <li>
                  <li onclick= "deleteMsg(${msg.id})" >Delete</li>
			    </li>
			   </div>
			</ul>
		</div>
		<div class="options">
			<a href="#" onclick="option(msg_${msg.id})" ><i class="fas fa-angle-down text-muted px-2"></i></a>
		</div>
		<div class="d-flex flex-row">
			<div class="body mr-2">${msg.body}</div>
			<div class="time ml-auto small text-right flex-shrink-0 align-self-end text-muted" style="width:75px; position: relative; top: 7px; font-size: 11px;">
				${mDate(msg.time).getTime()}
				${(msg.sender === user.id) ? sendStatus : ""}
				${msg.sender === "1" ? `<i class="fa fa-check double-tick" aria-hidden="true"></i>` : ""}
			</div>
		
		</div>
	</div>
	`;
}

function createPlainaudio(parsedMessage, msg, sendStatus){

	return `
	<div class="align-self-${msg.sender === "1" ? "end self" : "start"} p-1-apagar my-1 mx-5 rounded-apagar bg-white shadow-sm-apagar rounded-7-5 m-p-r m-shadow message-item tail">
		<span class="tail-container"></span>
		<div>
          <div>
           <ul class="${msg.sender === "1" ? "my-droup" : "droup-li"}"  id ="msg_${msg.id}">
              <div>
			    <li>
                  <li onclick= "deleteMsg(${msg.id})" >Delete</li>
			    </li>
			   </div>
			</ul>
		</div>
		<div class="options">
			<a href="#" onclick="option(msg_${msg.id})" ><i class="fas fa-angle-down text-muted px-2"></i></a>
			
		</div>
		<div class="d-flex flex-row">
			<audio controls src="/whatsfile/${chatuserid}/${parsedMessage.url}" type="audio/ogg">
			  
			  Your browser does not support the audio element.
			</audio>
		</div>
		<div class="time ml-auto small text-right flex-shrink-0 align-self-end text-muted" style="width:75px; position: relative; top: 7px; font-size: 11px;">
				${mDate(msg.time).getTime()}
				${(msg.sender === user.id) ? sendStatus : ""}
				${msg.sender === "1" ? `<i class="fa fa-check double-tick" aria-hidden="true"></i>` : ""}
			</div>
	</div>
	`;
}
function createPlainVideo(parsedMessage, msg, sendStatus){
	let caption = "";
	 if(parsedMessage.caption != ""){
		caption = `<div class="body m-2" >${parsedMessage.caption}</div>`;
	 }
		return `<div class="align-self-${msg.sender === "1" ? "end self" : "start"} p-1-apagar my-1 mx-5 rounded-apagar bg-white shadow-sm-apagar rounded-7-5 m-p-r m-shadow message-item tail">
		<span class="tail-container"></span>
		<div>
           <ul class="${msg.sender === "1" ? "my-droup" : "droup-li"}"  id ="msg_${msg.id}">
              <div>
			    <li>
                  <li onclick= "deleteMsg(${msg.id})" >Delete</li>
			    </li>
			   </div>
			</ul>
		</div>
		<div class="options">
			<a href="#" onclick="option(msg_${msg.id})" ><i class="fas fa-angle-down text-muted px-2"></i></a>
		</div>
		<div>
			<div class="body mr-2 " style="margin-top:10px;">
							<video controls style="max-height: 400px; max-width:350px;">
				<source src="/whatsfile/${chatuserid}/${parsedMessage.url}" type="video/mp4">
				Your browser does not support the video tag.
			</video>
				</div>
				${caption}
			<div class="time ml-auto small text-right flex-shrink-0 align-self-end text-muted" style="width:75px; position: relative; top: 7px; font-size: 11px;">
				   ${mDate(msg.time).getTime()}
				   ${(msg.sender === user.id) ? sendStatus : ""}
				   ${msg.sender === "1" ? `<i class="fa fa-check double-tick" aria-hidden="true"></i>` : ""}
			   </div>
		</div>
	</div>`
}
let addMessageToMessageArea = async (msg) => {

	if(msg.id){
		lastMessageId = msg.id;
	}
	let msgdate = new Date(msg.time);

	// Get formatted date parts (day, month, year)
	let day = msgdate.getDate();
	let month = msgdate.getMonth() + 1; // getMonth() returns 0-11, so add 1
	let year = msgdate.getFullYear();

	// Format the output as "DD/MM/YYYY"
	let formattedDate = `${day}/${month}/${year}`;


	// let msgDate = mDate(msg.time).getDate();

	if (lastDate != formattedDate) {
		addDateToMessageArea(formattedDate);
		lastDate = formattedDate;
	}

	// let htmlForGroup = `           lastMessageId
	// <div class="small font-weight-bold text-primary">
	// 	${contactList.find(contact => contact.id === msg.sender).number}
	// </div>
	// `;
	let sendStatus = `<i class="${msg.status < 2 ? "far" : "fas"} fa-check-circle"></i>`;	
	//let sendStatus = `<span class="${msg.status < 2 ? "undelivered" : "read"}"></span>`;
	let newmess = msg.body;
	
	try {
		
		const parsedMessage = JSON.parse(newmess);
		
		if(parsedMessage.type == "image"){
			messageHtml = createImageMessage(parsedMessage, msg, sendStatus);
		
		}else if(parsedMessage.type == "document"){
			messageHtml = createDocumentMessage(parsedMessage, msg, sendStatus);
			
		}else if(parsedMessage.type == "list"){
			messageHtml = createListMessage(parsedMessage, msg, sendStatus)
			
		}else
		if(parsedMessage.id){
			messageHtml = createTemplateMessage(parsedMessage, msg, sendStatus)
			
		}else if(parsedMessage.type == "audio"){
             messageHtml = createPlainaudio(parsedMessage, msg, sendStatus)
		}else if(parsedMessage.type == "video"){
			messageHtml = createPlainVideo(parsedMessage, msg, sendStatus)
		}
		
		else {
			messageHtml = createTextWithButtonsMessage(parsedMessage, msg, sendStatus);
		
		}
		   // Append the generated message to the DOM
		   DOM.messages.innerHTML += messageHtml;


	// console.log("Parsed JSON message:", parsedMessage);
    } catch (e) {
		if(msg.body != null){
			DOM.messages.innerHTML += createPlainTextMessage(msg, sendStatus);
			
		}
    }
	


	
	$('.align-self-end + .align-self-start').removeClass('tail').addClass('tail');
	$('.align-self-start + .align-self-end').removeClass('tail').addClass('tail');
	
	$('.dt-message + .align-self-start').removeClass('tail').addClass('tail');
	$('.dt-message + .align-self-end').removeClass('tail').addClass('tail');

	DOM.messages.scrollTo(0, DOM.messages.scrollHeight);
};
let oldMessageToMessageArea = async (msg) => {
	
	// if(msg.id){
	// 	lastMessageId = msg.id;
	// }
	
	let msgdate = new Date(msg.time);

	// Get formatted date parts (day, month, year)
	let day = msgdate.getDate();
	let month = msgdate.getMonth() + 1; // getMonth() returns 0-11, so add 1
	let year = msgdate.getFullYear();

	// Format the output as "DD/MM/YYYY"
	let formattedDate = `${day}/${month}/${year}`;


	// let msgDate = mDate(msg.time).getDate();

	if (lastDate != formattedDate) {
		oldDateToMessageArea(formattedDate);
		lastDate = formattedDate;
	}
	// let htmlForGroup = `           lastMessageId
	// <div class="small font-weight-bold text-primary">
	// 	${contactList.find(contact => contact.id === msg.sender).number}
	// </div>
	// `;
	
	let sendStatus = `<i class="${msg.status < 2 ? "far" : "fas"} fa-check-circle"></i>`;	
	//let sendStatus = `<span class="${msg.status < 2 ? "undelivered" : "read"}"></span>`;
	let newmess = msg.body;
	let messageHtml = "";
	try {
		
        const parsedMessage = JSON.parse(newmess);
		if(parsedMessage.type == "image"){
			messageHtml = createImageMessage(parsedMessage, msg, sendStatus);
		
		}else if(parsedMessage.type == "document"){
			messageHtml = createDocumentMessage(parsedMessage, msg, sendStatus);
			
		}else if(parsedMessage.type == "list"){
			messageHtml = createListMessage(parsedMessage, msg, sendStatus)
			
		}else
		if(parsedMessage.id){
			messageHtml = createTemplateMessage(parsedMessage, msg, sendStatus)
			
		}else {
			messageHtml = createTextWithButtonsMessage(parsedMessage, msg, sendStatus);
		
		}
	


	// console.log("Parsed JSON message:", parsedMessage);
    } catch (e) {
		if(msg.body != null){
			messageHtml = createPlainTextMessage(msg, sendStatus);
			
			// DOM.messages.insertAdjacentHTML("afterbegin", messageHtml);
		}
    }

	// Prepend the new div to the top of the #messages container
	DOM.messages.insertAdjacentHTML('afterbegin', messageHtml);

	
	
	// $('.align-self-end + .align-self-start').removeClass('tail').addClass('tail');
	// $('.align-self-start + .align-self-end').removeClass('tail').addClass('tail');
	
	// $('.dt-message + .align-self-start').removeClass('tail').addClass('tail');
	// $('.dt-message + .align-self-end').removeClass('tail').addClass('tail');

	// DOM.messages.scrollTo(0, DOM.messages.scrollHeight);
};

const generateMessageArea = async (elem, chatIndex) => {
	chatuserid = chatIndex;
	nextmessagelimit = 40;
	DOM.messages.innerHTML = "";

	DOM.messageAreaName.innerHTML = ""
	DOM.messageAreaPic.src = ""
	DOM.messageAreaDetails.innerHTML = '';
	if(docPage.style.display == 'block'){
		docPage.style.display = 'none';
		inputArea.style = "";
	}
    try {
        // Fetch message data
        const messagedata = await getmessages(chatIndex);
        
        // Log or handle the fetched data
        if(messagedata.status != "ok"){
			mClassList(DOM.inputArea).contains("d-none", (elem) => elem.remove("d-none").add("d-flex"));
			mClassList(DOM.messageAreaOverlay).add("d-none");
			return;
		}
        let messagedetails = messagedata.data;
        // Example: Populate the message area with the fetched data
        // Note: You should adjust the `addMessageToMessageArea` function based on the actual structure of `messagedata`
        // addMessageToMessageArea(messagedata);
		DOM.messageAreaName.innerHTML = messagedetails[0].username;
		DOM.messageAreaPic.src = '../assets/images/' + messagedetails[0].pic;
		DOM.messageAreaDetails.innerHTML = messagedetails[0].number;
		
		
		messagedetails.forEach(element => {
			lastMessageId = element.id;
			addMessageToMessageArea(element)
			
		});
		statuschange()
		// console.log(messagedetails)
        mClassList(DOM.inputArea).contains("d-none", (elem) => elem.remove("d-none").add("d-flex"));
        mClassList(DOM.messageAreaOverlay).add("d-none");

    } catch (error) {
        // Handle any errors that occurred during the fetch
        console.error('Error generating message area:', error);
    }
};


function statuschange(){

	let url = "statuschange";
	data = {id:lastMessageId}
	id =`msg_${lastMessageId}`
	let message = document.getElementById(id)
	console.log(id);
	console.log(message)

	let fectchdata = postDataSend(url,data)


}




// Save contact modal handler
saveContact.addEventListener('click', () => {
    // Set the values of the form fields
    nameInput.value = DOM.messageAreaName.innerText;
    phoneInput.value = DOM.messageAreaDetails.innerText;

    // Show the modal
    contactModal.style.display = 'block';
});

// New contact modal handler
newcontact.addEventListener('click', () => {
    // Show the new contact modal
    newcontactmodel.style.display = 'block';
});

// Hide modal when 'x' is clicked for contact modal
closeModal.addEventListener('click', () => {
    contactModal.style.display = 'none';
});

// Hide modal when 'x' is clicked for new contact modal
newCloseModal.addEventListener('click', () => {
    newcontactmodel.style.display = 'none';
});



let showChatList = () => {
	if (areaSwapped) {
		mClassList(DOM.chatListArea).remove("d-none").add("d-flex");
		mClassList(DOM.messageArea).remove("d-flex").add("d-none");
		areaSwapped = false;
	}
};

let sendMessage = async () => {
    let value = DOM.messageInput.value;
    DOM.messageInput.value = "";
    if (value === "") return;

    let msg = {
        sender: '1',
        body: value,
        time: mDate().toString(),
        status: 1,
        recvId: chatuserid,
        recvIsGroup: 0,
    };
	
	
    try {
        const response = await fetch('../functions.php?action=sendMessage', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ msg })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

		let data = await response.text(); // Getting the response as text
		const parsedData = JSON.parse(data);
		if(parsedData.status == "ok"){
		
			lastMessageId = parsedData.data
			msg.id = lastMessageId;
			
		}
		addMessageToMessageArea(msg);
		statuschange()
        // MessageUtils.addMessage(msg);
        // generateChatList();
    } catch (error) {
        console.error('Error sending message:', error);
		addMessageToMessageArea(msg);
    }
};


let showProfileSettings = () => {
	DOM.profileSettings.style.left = 0;
	DOM.profilePic.src = user.pic;
	DOM.inputName.value = user.name;
};

let hideProfileSettings = () => {
	DOM.profileSettings.style.left = "-110%";
	DOM.username.innerHTML = user.name;
};

window.addEventListener("resize", e => {
	if (window.innerWidth > 575) showChatList();
});


let checkNewMessage = (id, no) => {
	// update();
	if(id != null && no != null){
		$.ajax({
			type: 'POST', // Use POST method
			contentType: 'application/json',
			url: '../functions.php?action=checkNewMessage',
			data: JSON.stringify({ id: id, no : no }), // Pass data in JSON format
			success: function(data) {
			
				let checked = JSON.parse(data)
				if(checked['status'] == "ok"){
					insertMessages(checked['data']);
				}
				
			},
			error: function(error) {
				console.error('Error deleting chat:', error);
			}
		});
	}

};


let init = () => {
	DOM.username.innerHTML = user.name;
	DOM.displayPic.src = user.pic;
	DOM.profilePic.stc = user.pic;
	DOM.profilePic.addEventListener("click", () => DOM.profilePicInput.click());
	DOM.profilePicInput.addEventListener("change", () => console.log(DOM.profilePicInput.files[0]));
	DOM.inputName.addEventListener("blur", (e) => user.name = e.target.value);
	generateChatList();
};

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function insertRecord() {
    while (true) {
		try{
			 //init();
			$.ajax({
				type: 'GET',		
				url: '../functions.php?action=usersdetails',
			
				success: function(data) {
					data = JSON.parse(data)
					if(data['status'] == "ok"){
						
						useradd(data['data']);
						if(DOM.messageAreaDetails.innerText != ""){

							// console.log(DOM.messageAreaDetails.innerText)
							// console.log('last message  :'+lastMessageId)
							// console.log("number : "+ DOM.messageAreaDetails.innerText)
							checkNewMessage(lastMessageId,DOM.messageAreaDetails.innerText);
				
						}
						
					}
				}
			});
		}catch(e){
			console.log(e)
		}
	
        // Your logic for inserting records goes here
        
        // Sleep for 1 second
        await sleep(3000); 
    }
}

insertRecord();
const deleted_chat = document.getElementById('deleted_chat');
deleted_chat.addEventListener('click', deleted); // Reference the function without parentheses

function deleted() {

    var user_no = document.getElementById('details').innerText;
	if(confirm('If you want delete the chat')){
		$.ajax({
			type: 'POST', // Use POST method
			contentType: 'application/json',
			url: '../functions.php?action=deletedchat',
			data: JSON.stringify({ user_no: user_no,userId :chatuserid }), // Pass data in JSON format
			success: function(data) {
				console.log('Chat deleted successfully');
				// Handle any response or UI updates if necessary
			},
			error: function(error) {
				console.error('Error deleting chat:', error);
			}
		});
	}
  
}
//get message data 
function getmessages(id) {
    return new Promise((resolve, reject) => {
        $.ajax({
            type: 'POST',
            contentType: 'application/json',
            url: '../functions.php?action=messagedata',
            data: JSON.stringify({ id: id }),
            dataType: 'json', // Expect JSON response
            success: function(data) {
                resolve(data);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching messages:', error);
                reject(error);
            }
        });
    });
}


// custom code list the user 
function useradd(data) {

    userList.innerHTML = ''; // Clear the list


    data.forEach(element => {
		let pic = "removedp.png";
		let last_message_time = "";
		let last_message = "";
		let read = "";
		// console.log(element)
		
		if(element.pic != null){
			pic = element.pic
		}
		if(element.status == '1'){
		  read = `<div class="badge badge-success badge-pill small" id="unread-count" bis_skin_checked="1">1</div>`;
		}
		if(element.last_message != null){
			// console.log(element)
			try {
				let body ="";
				const parsedMessage = JSON.parse(element.last_message);

				if(parsedMessage.type == "image"){
					body = `<i class="fa fa-camera" aria-hidden="true"></i> Photo`;
				}else if(parsedMessage.type == "document"){
					body = `<i class="fa fa-file-text" aria-hidden="true"></i> `+parsedMessage.caption;
				}else if(parsedMessage.type == "audio"){
					body = `<i class="fa-solid fa-microphone"></i> Audio`;
				}else if(parsedMessage.type == "video"){
					body = `<i class="fa fa-video-camera" aria-hidden="true"></i> Video`;
				}

				else{

				 body = parsedMessage.body.text;
				}
				last_message = body
				
			}
			catch(e){
				last_message = element.last_message
			}
		
		}
		if(element.last_message_time != null){
			last_message_time = element.last_message_time
		}
        // Create the chat item HTML as a string
        let eletmet = `
            <div id="chat-item" class="chat-list-item d-flex flex-row w-100 p-2 border-bottom-2 _border-bottom" onclick="generateMessageArea(this, ${element.user_id})" bis_skin_checked="1">
                <img src="../assets/images/${pic}" alt="Profile Photo" class="img-fluid rounded-circle mx-2 mr-2" style="height:50px;">
                <div class="w-50" bis_skin_checked="1">
                    <div class="name" bis_skin_checked="1">${element.user_name}</div>
                    <div class="small last-message" bis_skin_checked="1"><i class="far fa-check-circle mr-1"></i> ${last_message}</div>
                </div>
                <div class="flex-grow-1 text-right" bis_skin_checked="1">
                    <div class="small time" bis_skin_checked="1">${last_message_time}</div>
					${read}
                </div>
            </div>
        `;

        // Append the HTML string as a node using innerHTML
        userList.innerHTML += eletmet;
    });
}

contactForm.addEventListener('submit',(event)=>{
 event.preventDefault()
 let name = contactname.value
 let phone = contact_phone.value

 if(name != "" && phone != ""){
	$.ajax({
		type: 'POST',
		contentType: 'application/json',
		url: '../functions.php?action=savecontact',
		data: JSON.stringify({ name: name, phone: phone }),
		dataType: 'json', // Expect JSON response
		success: function(data) {
			if (data.status === 'ok') {
                // Reload the page
                window.location.reload();
            }
		},
		error: function(xhr, status, error) {
			window.location.reload();
		}
	});
 }
})
newcontactForm.addEventListener('submit',(event)=>{
	event.preventDefault()
	let name = newNameInput.value
	let phone = newPhoneInput.value
   
	if(name != "" && phone != ""){
	   $.ajax({
		   type: 'POST',
		   contentType: 'application/json',
		   url: '../functions.php?action=newcontact',
		   data: JSON.stringify({ name: name, phone: phone }),
		   dataType: 'json', // Expect JSON response
		   success: function(data) {
			   if (data.status === 'ok') {
				window.location.reload();
			   }
		   },
		   error: function(xhr, status, error) {
			window.location.reload();
		   }
	   });
	}
})

DOM.messageInput.addEventListener('keydown',(event)=>{
	 // Check if the "Enter" key is pressed
	 if (event.key === 'Enter') {
		sendMessage()
    }
	
})
function insertMessages(checked){
	// console.log(checked)
	
	checked.forEach(function(item, index) {
		addMessageToMessageArea(item);
	  });
	// 
	statuschange()
}
// attached documents 
const toggleDoc = document.getElementById('toggleDoc');
const docSpan = document.getElementById('docSpan');
toggleDoc.addEventListener('click', (event) => {
event.preventDefault(); // Prevent the default anchor behavior

// console.log(docSpan.style.display)
if (docSpan.style.display === "none" || docSpan.style.display === "") {
docSpan.style.display = "block";
} else {
docSpan.style.display = "none";
}
});
fileInput.addEventListener('change', () => {
    docSpan.style.display = "none"; // Hide docSpan
});
// Close docSpan when clicking outside of it
document.addEventListener('click', (e) => {
    if (!docSpan.contains(e.target) && !toggleDoc.contains(e.target)) {
    docSpan.style.display = 'none'; // Close the div
    }
});

function postDataSend(url, data) {
    return new Promise((resolve, reject) => {
        $.ajax({
            type: 'POST',
            contentType: 'application/json',
            url: '../functions.php?action=' + url,
            data: JSON.stringify(data),
            dataType: 'json', // Expect JSON response
            success: function(response) {

                resolve(response); // Resolve the promise with the data
            },
            error: function(xhr, status, error) {
                reject({ status: 'error', message: error }); // Reject the promise on error
            }
        });
    });
}


        // Add scroll event listener to the window object
		DOM.messages.addEventListener('scroll', async function()  {
	
			if (DOM.messages.scrollTop === 0) {
				
				let data = {
					user_id : chatuserid,
					nextmes : nextmessagelimit
				}
		
				let oldmessage = await postDataSend('previces',data)
				
				if(oldmessage.status == "ok"){
		
					oldmessage.message.forEach(ele =>{

						oldMessageToMessageArea(ele)
	
						
					})
					nextmessagelimit += 40;

				}
            }
        });
		function loadingtemplate(tem) {
			if (tem) {
				DOM.messages.innerHTML += `
				<div id="loading" class="align-self-end self p-1-apagar my-1 mx-5 rounded-apagar bg-white shadow-sm-apagar rounded-7-5 m-p-r m-shadow message-item tail">
					<span class="tail-container"></span>
		
					<div class="options">
						<a href="#"><i class="fas fa-angle-down text-muted px-2"></i></a>
					</div>
					<div class ="card-type d-flex flex-row body mr-2">
						<div class = "icon-div"><i class="fa fa-file-text fa-3x" aria-hidden="true"></i></div>
						<div class="body m-2 document-body"></div>
						<div class="body mr-2"><div id="loading_ring"></div></div>
					</div>
				
				</div>`;
			} else {
				const loadingElement = document.getElementById('loading');
				if (loadingElement) {
					loadingElement.remove();
				}
			}
		}
		
// send media 
function sendmedia() {
	 
      // Create a new FormData object
	  const formData = new FormData();

    if (fileInput.files.length > 0) {
  
        
        // Append each file from fileInput.files to the FormData object
        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append('files[]', fileInput.files[i]);
        }
        formData.append('type', "document");

    } else
	if(ImageInput.files.length > 0){
		// Append each file from fileInput.files to the FormData object
		for (let i = 0; i < ImageInput.files.length; i++) {
			formData.append('files[]', ImageInput.files[i]);
		}
		formData.append('type', "image");
	}
	formData.append('userno',DOM.messageAreaDetails.innerText);
	formData.append('caption',caption.value);
	formData.append('userId',chatuserid)
	 docPage.style.display = 'none'; // Hide the doc-page
    inputArea.style.display = ""; 
	loadingtemplate(true)
	// Send the form data using Fetch API
	$.ajax({
		url: '../functions.php?action=sendImage',
		type: 'POST',
		data: formData,
		contentType: false,  // Required for FormData
		processData: false,  // Required for FormData
		success: function(data) {
			// Handle the success response
			loadingtemplate(false);
		},
		error: function(xhr, status, error) {
			loadingtemplate(false);
			// Handle any errors
			alert('Error uploading files: ' + error);
		}
	});
	
}
function option(id){
	chatMenu = id
	
}
document.addEventListener('click', function(event) {


    // Check if the click was outside the element
    if (chatMenu && !chatMenu.contains(event.target)) {
		
		if(chatMenu.style.display === "block"){
			chatMenu.style.display = "none"
			chatMenu = "";
		}else{
			chatMenu.style.display = "block"
			
		}
		
    }

});
async function  deleteMsg(id) {
	//   console.log(id)
	  let data = {id: id}
	  let res = await postDataSend('DeleteMsg',data)
	  if(res.status == "ok"){
		
		generateMessageArea('',chatuserid)
		}
	}
