/**
 * Chat Application JavaScript
 * Handles real-time messaging with AJAX and optimized polling
 */

// Global State
const ChatApp = {
    selectedUserId: null,
    selectedUserName: '',
    selectedUserStatus: '',
    lastMessageId: 0,
    isSending: false,
    pollTimers: {
        users: null,
        messages: null,
        status: null
    }
};

// DOM Elements
const elements = {
    usersList: document.getElementById('usersList'),
    chatArea: document.getElementById('chatArea'),
    messageInput: document.getElementById('messageInput'),
    messageForm: document.getElementById('messageForm'),
    chatTitle: document.getElementById('chatTitle'), // Assuming you have a header title
    backButton: document.getElementById('backButton') // For mobile
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Chat app initialized');
    
    // Initial Loads
    loadUsers();
    updateUserStatus();

    // Start Polling (using recursive timeout pattern to prevent congestion)
    scheduleUserRefresh();
    scheduleStatusUpdate();
    
    // Event Delegation for User List (Better performance than inline onclick)
    if (elements.usersList) {
        elements.usersList.addEventListener('click', handleUserClick);
    }
});

/* =========================================
   User Management & Sidebar
   ========================================= */

function loadUsers() {
    fetch('chat/get_users.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayUsers(data.users);
            }
        })
        .catch(console.error)
        .finally(() => scheduleUserRefresh());
}

function scheduleUserRefresh() {
    clearTimeout(ChatApp.pollTimers.users);
    ChatApp.pollTimers.users = setTimeout(loadUsers, 5000);
}

function displayUsers(users) {
    if (!elements.usersList) return;

    if (users.length === 0) {
        elements.usersList.innerHTML = '<div class="text-center p-4 text-muted">No users found</div>';
        return;
    }

    // specific check to see if we need to redraw to avoid losing scroll position in sidebar
    // In a real React/Vue app, virtual DOM handles this. Here we just rebuild.
    
    let html = '';
    users.forEach(user => {
        const isActive = ChatApp.selectedUserId === user.id ? 'active' : '';
        const statusClass = user.status === 'online' ? 'online' : 'offline';
        const statusText = user.status === 'online' ? 'Online' : 'Offline';
        const initial = user.name.charAt(0).toUpperCase();

        // We use data attributes instead of inline onclick for better security/stability
        html += `
            <div class="user-item ${isActive}" 
                 data-id="${user.id}" 
                 data-name="${escapeHtml(user.name)}" 
                 data-status="${user.status}">
                <div class="user-avatar">${initial}</div>
                <div class="user-info">
                    <div class="user-name">${escapeHtml(user.name)}</div>
                    <div class="user-status">
                        <span class="status-indicator ${statusClass}"></span>
                        <span>${statusText}</span>
                    </div>
                </div>
            </div>
        `;
    });

    elements.usersList.innerHTML = html;
}

function handleUserClick(e) {
    // Find the closest user-item parent
    const userItem = e.target.closest('.user-item');
    if (!userItem) return;

    const userId = parseInt(userItem.dataset.id);
    const userName = userItem.dataset.name;
    const userStatus = userItem.dataset.status;

    selectUser(userId, userName, userStatus);
}

/* =========================================
   Chat Selection & UI
   ========================================= */

function selectUser(userId, userName, userStatus) {
    if (ChatApp.selectedUserId === userId) return; // Don't reload if already active

    ChatApp.selectedUserId = userId;
    ChatApp.selectedUserName = userName;
    ChatApp.selectedUserStatus = userStatus;
    ChatApp.lastMessageId = 0;

    // UI Updates
    document.querySelectorAll('.user-item').forEach(item => item.classList.remove('active'));
    const activeItem = document.querySelector(`.user-item[data-id="${userId}"]`);
    if (activeItem) activeItem.classList.add('active');

    // Handle Mobile View (Hide list, show chat)
    document.querySelector('.col-md-4')?.classList.remove('show-mobile'); // Hide sidebar on mobile
    
    showChatInterface();
    loadMessages();
    
    // Reset Message Polling
    clearTimeout(ChatApp.pollTimers.messages);
    scheduleMessageRefresh();
}

function showChatInterface() {
    const chatArea = document.getElementById('chatArea');
    const template = document.getElementById('chatTemplate');
    
    if (!template || !chatArea) return;

    // Clone template
    const clone = template.content.cloneNode(true);
    
    // Update Header Info
    const nameEl = clone.getElementById('chatUserName');
    if (nameEl) nameEl.textContent = ChatApp.selectedUserName;
    
    const statusEl = clone.getElementById('chatUserStatus');
    if (statusEl) {
        const statusClass = ChatApp.selectedUserStatus === 'online' ? 'online' : 'offline';
        statusEl.innerHTML = `<span class="status-indicator ${statusClass}"></span> ${ChatApp.selectedUserStatus === 'online' ? 'Online' : 'Offline'}`;
    }

    // Mobile Back Button Logic
    const backBtn = clone.getElementById('backButton');
    if (backBtn) {
        backBtn.addEventListener('click', () => {
            document.querySelector('.col-md-4').classList.add('show-mobile');
        });
    }

    // Render
    chatArea.innerHTML = '';
    chatArea.appendChild(clone);
    
    // Re-attach listener to the new form
    const form = document.getElementById('messageForm');
    if (form) form.addEventListener('submit', sendMessage);
}

/* =========================================
   Messaging Logic
   ========================================= */

function loadMessages() {
    if (!ChatApp.selectedUserId) return;

    fetch(`chat/fetch_messages.php?receiver_id=${ChatApp.selectedUserId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMessages(data.messages);
            }
        })
        .catch(console.error)
        .finally(() => scheduleMessageRefresh());
}

function scheduleMessageRefresh() {
    if (!ChatApp.selectedUserId) return;
    clearTimeout(ChatApp.pollTimers.messages);
    ChatApp.pollTimers.messages = setTimeout(loadMessages, 2000);
}

function displayMessages(messages) {
    const container = document.getElementById('messagesContainer');
    if (!container) return;

    // Smart Scroll Logic:
    // Only scroll to bottom if user is already at bottom OR it's the first load
    const isAtBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;
    
    if (messages.length === 0) {
        container.innerHTML = '<div class="no-messages">No messages yet. Say hello!</div>';
        return;
    }

    let html = '';
    messages.forEach(msg => {
        const type = msg.is_sender ? 'sent' : 'received';
        const time = formatMessageTime(msg.created_at);
        
        html += `
            <div class="message-wrapper ${type}">
                <div class="message-bubble ${type}">
                    <div class="message-text">${escapeHtml(msg.message)}</div>
                    <div class="message-time">${time}</div>
                </div>
            </div>
        `;
    });

    // Only update DOM if content changed (Simple check)
    // For a large app, use a diffing library or append child
    if (container.innerHTML !== html) {
        container.innerHTML = html;
        
        // Handle Scrolling
        if (isAtBottom || ChatApp.lastMessageId === 0) {
            scrollToBottom(container);
        }
    }

    if (messages.length > 0) {
        ChatApp.lastMessageId = messages[messages.length - 1].id;
    }
}

function scrollToBottom(container) {
    container.scrollTop = container.scrollHeight;
}

function sendMessage(event) {
    event.preventDefault();
    if (ChatApp.isSending) return; // Prevent double submit

    const input = document.getElementById('messageInput');
    const message = input.value.trim();

    if (!message || !ChatApp.selectedUserId) return;

    ChatApp.isSending = true;

    fetch('chat/send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            receiver_id: ChatApp.selectedUserId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            input.focus();
            loadMessages(); // Fetch immediately
        } else {
            alert('Failed: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error sending message');
    })
    .finally(() => {
        ChatApp.isSending = false;
    });
}

/* =========================================
   Utilities & Helpers
   ========================================= */

function updateUserStatus() {
    fetch('chat/update_status.php', { method: 'POST' })
        .finally(() => scheduleStatusUpdate());
}

function scheduleStatusUpdate() {
    clearTimeout(ChatApp.pollTimers.status);
    ChatApp.pollTimers.status = setTimeout(updateUserStatus, 30000);
}

function formatMessageTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    
    // Use Intl.DateTimeFormat for cleaner localizing
    const timeStr = new Intl.DateTimeFormat('en-US', { 
        hour: 'numeric', minute: 'numeric', hour12: true 
    }).format(date);

    if (date.toDateString() === now.toDateString()) {
        return timeStr;
    }
    
    const dateStr = new Intl.DateTimeFormat('en-US', { 
        month: 'short', day: 'numeric' 
    }).format(date);

    return `${dateStr} ${timeStr}`;
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Cleanup on exit
window.addEventListener('beforeunload', function() {
    Object.values(ChatApp.pollTimers).forEach(clearTimeout);
});