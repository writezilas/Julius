@extends('layouts.master')
@section('title') {{ $pageTitle }} @endsection
@section('css')
<style>
.chat-container {
    height: calc(100vh - 200px);
    max-height: 600px;
}

.chat-sidebar {
    border-right: 1px solid #e9ecef;
    height: 100%;
    overflow-y: auto;
}

.chat-main {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.messages-container {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    background-color: #f8f9fa;
}

.message {
    margin-bottom: 15px;
}

.message.own {
    text-align: right;
}

.message.own .message-bubble {
    background-color: #007bff;
    color: white;
    margin-left: auto;
}

.message-bubble {
    max-width: 70%;
    padding: 10px 15px;
    border-radius: 15px;
    background-color: #e9ecef;
    display: inline-block;
    word-wrap: break-word;
}

.message-time {
    font-size: 11px;
    color: #6c757d;
    margin-top: 5px;
}

.message-input {
    border-top: 1px solid #e9ecef;
    padding: 15px;
    background-color: white;
}

.conversation-item {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    transition: background-color 0.2s;
}

.conversation-item:hover {
    background-color: #f8f9fa;
}

.conversation-item.active {
    background-color: #e3f2fd;
    border-right: 3px solid #007bff;
}

.conversation-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.unread-badge {
    background-color: #dc3545;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: bold;
}

.chat-header {
    border-bottom: 1px solid #e9ecef;
    padding: 15px;
    background-color: white;
}

.system-message {
    text-align: center;
    font-style: italic;
    color: #6c757d;
    background-color: transparent;
}

.system-message .message-bubble {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.chat-ended-notice {
    background-color: #f8d7da;
    color: #721c24;
    text-align: center;
    padding: 10px;
    border-radius: 5px;
    margin: 10px;
}

.file-message {
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background-color: #f8f9fa;
}

.file-message i {
    margin-right: 8px;
    color: #007bff;
}

.loading {
    text-align: center;
    color: #6c757d;
    padding: 20px;
}

@media (max-width: 768px) {
    .chat-container {
        height: calc(100vh - 150px);
    }
    
    .chat-sidebar {
        display: none;
    }
    
    .chat-sidebar.mobile-show {
        display: block;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        background-color: white;
    }
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row chat-container">
                    <!-- Chat Sidebar -->
                    <div class="col-md-4 chat-sidebar" id="chatSidebar">
                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 border-bottom">
                            <h6 class="mb-0">Conversations</h6>
                            <span class="badge badge-primary" id="totalUnreadBadge" style="display: none;">0</span>
                        </div>
                        <div id="conversationsList">
                            <div class="loading">
                                <i class="ri-loader-2-line"></i> Loading conversations...
                            </div>
                        </div>
                    </div>

                    <!-- Chat Main Area -->
                    <div class="col-md-8 chat-main" id="chatMain">
                        <div id="welcomeMessage" class="d-flex align-items-center justify-content-center h-100 text-center">
                            <div>
                                <i class="ri-chat-3-line" style="font-size: 48px; color: #6c757d;"></i>
                                <h5 class="mt-3 text-muted">Welcome to Chat</h5>
                                <p class="text-muted">Select a conversation to start messaging with your trading partner.</p>
                            </div>
                        </div>

                        <div id="conversationArea" style="display: none;">
                            <!-- Chat Header -->
                            <div class="chat-header">
                                <div class="d-flex align-items-center">
                                    <button class="btn btn-link d-md-none me-2" id="backToSidebar">
                                        <i class="ri-arrow-left-line"></i>
                                    </button>
                                    <img id="otherUserAvatar" src="" alt="Avatar" class="conversation-avatar me-3">
                                    <div>
                                        <h6 class="mb-0" id="otherUserName">User Name</h6>
                                        <small class="text-muted" id="tradeName">Trade Name</small>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge bg-success" id="conversationStatus">Active</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Messages Container -->
                            <div class="messages-container" id="messagesContainer">
                                <div class="loading" id="messagesLoading">
                                    <i class="ri-loader-2-line"></i> Loading messages...
                                </div>
                            </div>

                            <!-- Message Input -->
                            <div class="message-input" id="messageInputArea">
                                <form id="messageForm" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="messageText" 
                                                       placeholder="Type your message..." maxlength="1000">
                                                <input type="file" id="messageFile" style="display: none;" 
                                                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                                <button type="button" class="btn btn-outline-secondary" id="attachFile">
                                                    <i class="ri-attachment-2"></i>
                                                </button>
                                                <button type="submit" class="btn btn-primary" id="sendMessage">
                                                    <i class="ri-send-plane-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="selectedFile" style="display: none;" class="mt-2">
                                        <div class="d-flex align-items-center">
                                            <i class="ri-file-line me-2"></i>
                                            <span id="fileName"></span>
                                            <button type="button" class="btn btn-sm btn-link text-danger ms-2" id="removeFile">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
<script>
class ChatSystem {
    constructor() {
        this.currentConversationId = null;
        this.conversations = [];
        this.chatSettings = null;
        this.init();
    }

    async init() {
        await this.loadChatSettings();
        this.applySettings();
        this.bindEvents();
        this.loadConversations();
        this.startPolling();
    }

    bindEvents() {
        // Message form submission
        $('#messageForm').on('submit', (e) => {
            e.preventDefault();
            this.sendMessage();
        });

        // File attachment
        $('#attachFile').on('click', () => {
            $('#messageFile').click();
        });

        $('#messageFile').on('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                $('#fileName').text(file.name);
                $('#selectedFile').show();
            }
        });

        $('#removeFile').on('click', () => {
            $('#messageFile').val('');
            $('#selectedFile').hide();
        });

        // Back to sidebar (mobile)
        $('#backToSidebar').on('click', () => {
            $('#chatSidebar').addClass('mobile-show');
        });

        // Enter key to send message
        $('#messageText').on('keypress', (e) => {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                $('#messageForm').submit();
            }
        });
    }

    async loadConversations() {
        try {
            const response = await fetch('/chat/conversations');
            const data = await response.json();
            
            if (data.success) {
                this.conversations = data.conversations;
                this.renderConversations();
                this.updateUnreadCount();
            }
        } catch (error) {
            console.error('Error loading conversations:', error);
            $('#conversationsList').html('<div class="text-center text-danger p-3">Failed to load conversations</div>');
        }
    }

    renderConversations() {
        const container = $('#conversationsList');
        
        if (this.conversations.length === 0) {
            container.html(`
                <div class="text-center text-muted p-4">
                    <i class="ri-chat-off-line" style="font-size: 32px;"></i>
                    <p class="mt-2">No conversations yet.</p>
                    <small>Conversations will appear when your shares are paired with other traders.</small>
                </div>
            `);
            return;
        }

        let html = '';
        this.conversations.forEach(conversation => {
            const isActive = this.currentConversationId == conversation.id;
            const unreadBadge = conversation.unread_count > 0 ? 
                `<span class="unread-badge">${conversation.unread_count}</span>` : '';
            
            const lastMessage = conversation.last_message ? 
                `<small class="text-muted d-block">${conversation.last_message.message.substring(0, 50)}${conversation.last_message.message.length > 50 ? '...' : ''}</small>
                 <small class="text-muted">${conversation.last_message.created_at}</small>` :
                '<small class="text-muted">No messages yet</small>';

            html += `
                <div class="conversation-item ${isActive ? 'active' : ''}" data-conversation-id="${conversation.id}">
                    <div class="d-flex align-items-center">
                        <img src="${conversation.other_participant.avatar || '/assets/images/users/avatar-1.jpg'}" 
                             alt="Avatar" class="conversation-avatar me-3">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-1">${conversation.other_participant.name}</h6>
                                ${unreadBadge}
                            </div>
                            <small class="text-muted d-block mb-1">${conversation.trade_name}</small>
                            ${lastMessage}
                        </div>
                    </div>
                    ${conversation.status === 'ended' ? '<small class="badge bg-secondary mt-2">Ended</small>' : ''}
                </div>
            `;
        });

        container.html(html);

        // Bind conversation click events
        $('.conversation-item').on('click', (e) => {
            const conversationId = $(e.currentTarget).data('conversation-id');
            this.selectConversation(conversationId);
        });
    }

    async selectConversation(conversationId) {
        this.currentConversationId = conversationId;
        
        // Update UI
        $('.conversation-item').removeClass('active');
        $(`.conversation-item[data-conversation-id="${conversationId}"]`).addClass('active');
        
        // Hide welcome message, show conversation area
        $('#welcomeMessage').hide();
        $('#conversationArea').show();
        
        // Hide sidebar on mobile
        $('#chatSidebar').removeClass('mobile-show');

        // Load conversation details
        const conversation = this.conversations.find(c => c.id == conversationId);
        if (conversation) {
            $('#otherUserName').text(conversation.other_participant.name);
            $('#otherUserAvatar').attr('src', conversation.other_participant.avatar || '/assets/images/users/avatar-1.jpg');
            $('#tradeName').text(conversation.trade_name);
            $('#conversationStatus').text(conversation.status === 'active' ? 'Active' : 'Ended')
                .removeClass('bg-success bg-secondary')
                .addClass(conversation.status === 'active' ? 'bg-success' : 'bg-secondary');
        }

        // Load messages
        await this.loadMessages(conversationId);
        
        // Mark as read
        this.markAsRead(conversationId);
    }

    async loadMessages(conversationId) {
        $('#messagesLoading').show();
        
        try {
            const response = await fetch(`/chat/conversations/${conversationId}/messages`);
            const data = await response.json();
            
            if (data.success) {
                this.renderMessages(data.messages.data);
                this.scrollToBottom();
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        } finally {
            $('#messagesLoading').hide();
        }
    }

    renderMessages(messages) {
        const container = $('#messagesContainer');
        let html = '';

        messages.forEach(message => {
            const isOwn = message.sender_id == {{ auth()->id() }};
            const isSystem = message.is_system_message;
            
            if (isSystem) {
                html += `
                    <div class="message system-message">
                        <div class="message-bubble">
                            <i class="ri-information-line"></i> ${message.message}
                        </div>
                        <div class="message-time">${new Date(message.created_at).toLocaleString()}</div>
                    </div>
                `;
            } else {
                let messageContent = message.message;
                
                if (message.type === 'file' || message.type === 'image') {
                    const icon = message.type === 'image' ? 'ri-image-line' : 'ri-file-line';
                    messageContent = `
                        <div class="file-message">
                            <i class="${icon}"></i>
                            <a href="/storage/${message.file_path}" target="_blank">${message.file_name}</a>
                        </div>
                        ${message.message}
                    `;
                }

                html += `
                    <div class="message ${isOwn ? 'own' : ''}">
                        <div class="message-bubble">
                            ${messageContent}
                        </div>
                        <div class="message-time">${new Date(message.created_at).toLocaleString()}</div>
                    </div>
                `;
            }
        });

        container.html(html + $('#messagesLoading')[0].outerHTML);
    }

    async sendMessage() {
        const messageText = $('#messageText').val().trim();
        const fileInput = $('#messageFile')[0];
        
        if (!messageText && !fileInput.files.length) return;
        if (!this.currentConversationId) return;

        const formData = new FormData();
        if (messageText) formData.append('message', messageText);
        if (fileInput.files.length) formData.append('file', fileInput.files[0]);

        // Add CSRF token
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        try {
            const response = await fetch(`/chat/conversations/${this.currentConversationId}/messages`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                // Clear form
                $('#messageText').val('');
                $('#messageFile').val('');
                $('#selectedFile').hide();
                
                // Reload messages
                await this.loadMessages(this.currentConversationId);
                
                // Update conversations list
                await this.loadConversations();
            } else {
                toastr.error(data.error || 'Failed to send message');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            toastr.error('Failed to send message');
        }
    }

    async markAsRead(conversationId) {
        try {
            const response = await fetch(`/chat/conversations/${conversationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (response.ok) {
                // Update unread count
                await this.loadConversations();
            }
        } catch (error) {
            console.error('Error marking as read:', error);
        }
    }

    updateUnreadCount() {
        const totalUnread = this.conversations.reduce((sum, conv) => sum + conv.unread_count, 0);
        
        if (totalUnread > 0) {
            $('#totalUnreadBadge').text(totalUnread).show();
        } else {
            $('#totalUnreadBadge').hide();
        }
    }

    scrollToBottom() {
        const container = $('#messagesContainer');
        container.scrollTop(container[0].scrollHeight);
    }

    startPolling() {
        // Poll for new messages every 5 seconds
        setInterval(() => {
            this.loadConversations();
            if (this.currentConversationId) {
                this.loadMessages(this.currentConversationId);
            }
        }, 5000);
    }

    async loadChatSettings() {
        try {
            const response = await fetch('/chat/settings');
            const data = await response.json();
            
            if (data.success) {
                this.chatSettings = data.settings;
            } else {
                // Use defaults if settings fail to load
                this.chatSettings = {
                    is_enabled: true,
                    message_character_limit: 1000,
                    allow_file_upload: true
                };
            }
        } catch (error) {
            console.error('Error loading chat settings:', error);
            // Use defaults on error
            this.chatSettings = {
                is_enabled: true,
                message_character_limit: 1000,
                allow_file_upload: true
            };
        }
    }

    applySettings() {
        if (!this.chatSettings) return;

        // Apply character limit to message input
        const messageInput = $('#messageText');
        messageInput.attr('maxlength', this.chatSettings.message_character_limit);
        
        // Update placeholder with character limit info
        const originalPlaceholder = messageInput.attr('placeholder');
        messageInput.attr('placeholder', `${originalPlaceholder} (max ${this.chatSettings.message_character_limit} chars)`);
        
        // Show/hide file upload button based on settings
        const attachButton = $('#attachFile');
        if (this.chatSettings.allow_file_upload) {
            attachButton.show();
        } else {
            attachButton.hide();
        }

        // Add character counter
        this.addCharacterCounter();
    }

    addCharacterCounter() {
        if (!this.chatSettings) return;

        const messageInput = $('#messageText');
        const maxLength = this.chatSettings.message_character_limit;
        
        // Create character counter element
        const counterHtml = `<small id="charCounter" class="text-muted ms-2">${maxLength} characters remaining</small>`;
        
        // Add counter after the input group
        if ($('#charCounter').length === 0) {
            $('.input-group').after(counterHtml);
        }

        // Update counter on input
        messageInput.on('input', () => {
            const remaining = maxLength - messageInput.val().length;
            const counter = $('#charCounter');
            
            counter.text(`${remaining} characters remaining`);
            
            if (remaining < 20) {
                counter.removeClass('text-muted').addClass('text-warning');
            } else if (remaining < 0) {
                counter.removeClass('text-muted text-warning').addClass('text-danger');
            } else {
                counter.removeClass('text-warning text-danger').addClass('text-muted');
            }
        });
    }
}

// Initialize chat system when page loads
$(document).ready(() => {
    window.chatSystem = new ChatSystem();
});
</script>
@endsection
