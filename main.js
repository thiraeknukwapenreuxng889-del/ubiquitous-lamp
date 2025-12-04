// ---------- ดึง element จาก DOM ----------
const loginForm = document.getElementById('loginForm');
const signupForm = document.getElementById('signupForm');
const postForm = document.getElementById('postForm');
const postList = document.getElementById('postList');
const chatForm = document.getElementById('chatForm');
const chatBox = document.getElementById('chatBox');
const notificationsList = document.getElementById('notificationsList');

// ---------- ฟังก์ชันช่วยเหลือ ----------
function createPostElement(post) {
    const div = document.createElement('div');
    div.classList.add('post');
    div.innerHTML = `
        <div class="username">${post.username}</div>
        <div class="content">${post.content}</div>
        ${post.image ? `<img src="${post.image}" alt="Post Image">` : ''}
        <button onclick="deletePost(${post.id})">ลบโพสต์</button>
    `;
    return div;
}

function createMessageElement(message) {
    const div = document.createElement('div');
    div.classList.add('message', message.type);
    div.textContent = message.text;
    return div;
}

function createNotificationElement(notification) {
    const div = document.createElement('div');
    div.classList.add('notification');
    div.textContent = notification.text;
    return div;
}

// ---------- Login ----------
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);
        const response = await fetch('auth.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            alert('ล็อกอินสำเร็จ!');
            window.location.href = 'feed.html';
        } else {
            alert('ผิดพลาด: ' + result.message);
        }
    });
}

// ---------- Signup ----------
if (signupForm) {
    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(signupForm);
        const response = await fetch('auth.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            alert('สมัครสมาชิกสำเร็จ!');
            window.location.href = 'login.html';
        } else {
            alert('ผิดพลาด: ' + result.message);
        }
    });
}

// ---------- Posts ----------
if (postForm) {
    postForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(postForm);
        const response = await fetch('postHandler.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            postList.prepend(createPostElement(result.post));
            postForm.reset();
        } else {
            alert('ผิดพลาด: ' + result.message);
        }
    });
}

async function loadPosts() {
    const response = await fetch('postHandler.php?action=get');
    const result = await response.json();
    postList.innerHTML = '';
    result.posts.forEach(post => {
        postList.appendChild(createPostElement(post));
    });
}

async function deletePost(postId) {
    if (!confirm('คุณต้องการลบโพสต์นี้หรือไม่?')) return;
    const response = await fetch('postHandler.php?action=delete&id=' + postId);
    const result = await response.json();
    if (result.success) {
        loadPosts();
    } else {
        alert('ผิดพลาด: ' + result.message);
    }
}

// โหลดโพสต์เมื่อเปิดหน้า feed
if (postList) loadPosts();

// ---------- Chat ----------
if (chatForm) {
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const messageInput = chatForm.querySelector('input[name="message"]');
        const messageText = messageInput.value.trim();
        if (!messageText) return;

        const formData = new FormData(chatForm);
        const response = await fetch('chatHandler.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            chatBox.appendChild(createMessageElement({text: messageText, type: 'user'}));
            chatBox.scrollTop = chatBox.scrollHeight;
            messageInput.value = '';
        }
    });
}

async function loadChat() {
    const response = await fetch('chatHandler.php?action=get');
    const result = await response.json();
    chatBox.innerHTML = '';
    result.messages.forEach(msg => {
        chatBox.appendChild(createMessageElement(msg));
    });
    chatBox.scrollTop = chatBox.scrollHeight;
}

if (chatBox) setInterval(loadChat, 2000); // โหลดแชททุก 2 วินาที

// ---------- Notifications ----------
async function loadNotifications() {
    const response = await fetch('notificationHandler.php?action=get');
    const result = await response.json();
    notificationsList.innerHTML = '';
    result.notifications.forEach(noti => {
        notificationsList.appendChild(createNotificationElement(noti));
    });
}

if (notificationsList) setInterval(loadNotifications, 3000); // โหลดแจ้งเตือนทุก 3 วินาที
