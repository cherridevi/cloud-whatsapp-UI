let user;
let contactList;
let groupList ;
let messages;

// async function insertRecord() {
//     while (true) {
  

//         try {
//             // Fetch contacts
//             contactList = await $.ajax({
//                 type: 'GET',
//                 contentType: 'application/json',
//                 url: '../functions.php?action=getAllContacts'
//             });
    

//             // Fetch messages
//             messages = await $.ajax({
//                 type: 'GET',
//                 contentType: 'application/json',
//                 url: '../functions.php?action=getAllMessages'
//             });
   
//         } catch (error) {
//             console.error("Error fetching records:", error);
//         }

//         await sleep(10000);  // Sleep for 1 second before fetching again
//     }
// }

// Helper sleep function to wait before the next iteration
// function sleep(ms) {
//     return new Promise(resolve => setTimeout(resolve, ms));
// }

// Fetch user data once
async function fetchUserData() {
    try {
        user = await $.ajax({
            type: 'GET',
            contentType: 'application/json',
            url: '../functions.php?action=getUser'
        });
  
    } catch (error) {
        console.error("Error fetching user data:", error);
    }
}

// Fetch group data once
async function fetchGroupData() {
    try {
        groupList = await $.ajax({
            type: 'GET',
            contentType: 'application/json',
            url: '../functions.php?action=getAllGroups'
        });

    } catch (error) {
        console.error("Error fetching group data:", error);
    }
}

// Message utility functions
let MessageUtils = {
    getByGroupId: (groupId) => {
        return messages.filter(msg => msg.recvIsGroup && msg.recvId === groupId);
    },
    getByContactId: (contactId) => {
        return messages.filter(msg => {
            return !msg.recvIsGroup && ((msg.sender === user.id && msg.recvId === contactId) || (msg.sender === contactId && msg.recvId === user.id));
        });
    },
    getMessages: () => {
        return messages;
    },
    changeStatusById: (options) => {
        messages = messages.map((msg) => {
            if (options.isGroup) {
                if (msg.recvIsGroup && msg.recvId === options.id) msg.status = 2;
            } else {
                if (!msg.recvIsGroup && msg.sender === options.id && msg.recvId === user.id) msg.status = 2;
            }
            return msg;
        });
    },
    addMessage: (msg) => {
        msg.id = messages.length + 1;
        messages.push(msg);
    }
};

// Start fetching user and group data
fetchUserData();
fetchGroupData();

// Start inserting new records every second
// insertRecord();
