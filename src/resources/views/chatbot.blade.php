<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GAISペディア - 生成AI協会 ナレッジアシスタント</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
        }

        .chatbot-container {
            width: 100%;
            max-width: 900px;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 40px);
        }

        /* Header */
        .chatbot-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid #e9ecef;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .avatar {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #4dabf7 0%, #339af0 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .header-title h1 {
            font-size: 24px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 4px;
        }

        .header-title p {
            font-size: 14px;
            color: #868e96;
        }

        .clear-history-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            color: #495057;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .clear-history-btn:hover {
            background-color: #f8f9fa;
            border-color: #adb5bd;
        }

        /* Chat Messages Area */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .message {
            max-width: 80%;
            padding: 16px 20px;
            border-radius: 16px;
            line-height: 1.6;
            font-size: 15px;
        }

        .message.bot {
            background-color: #f1f3f4;
            color: #212529;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }

        .message.user {
            background-color: #339af0;
            color: #ffffff;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .message.bot a,
        .message.bot .chat-link {
            color: #1971c2;
            text-decoration: none;
            border-bottom: 1px solid #a5d8ff;
            transition: all 0.2s ease;
        }

        .message.bot a:hover,
        .message.bot .chat-link:hover {
            color: #1864ab;
            border-bottom-color: #1971c2;
        }

        /* Markdown Styles */
        .message.bot h3 {
            font-size: 16px;
            font-weight: 700;
            margin: 16px 0 8px 0;
            color: #212529;
        }

        .message.bot h4 {
            font-size: 15px;
            font-weight: 600;
            margin: 12px 0 6px 0;
            color: #343a40;
        }

        .message.bot p {
            margin: 8px 0;
        }

        .message.bot ul,
        .message.bot ol {
            margin: 8px 0;
            padding-left: 24px;
        }

        .message.bot li {
            margin: 4px 0;
        }

        .message.bot table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 14px;
        }

        .message.bot th,
        .message.bot td {
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            text-align: left;
        }

        .message.bot th {
            background-color: #e9ecef;
            font-weight: 600;
        }

        .message.bot tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .message.bot code {
            background-color: #e9ecef;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }

        .message.bot pre {
            background-color: #212529;
            color: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 12px 0;
        }

        .message.bot pre code {
            background-color: transparent;
            padding: 0;
            color: inherit;
        }

        .message.bot blockquote {
            border-left: 4px solid #339af0;
            padding-left: 16px;
            margin: 12px 0;
            color: #495057;
        }

        .message.bot hr {
            border: none;
            border-top: 1px solid #dee2e6;
            margin: 16px 0;
        }

        .message.bot strong {
            font-weight: 600;
        }

        /* FAQ Section */
        .faq-section {
            padding: 20px 24px;
            border-top: 1px solid #e9ecef;
        }

        .faq-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 500;
            color: #212529;
            margin-bottom: 16px;
        }

        .faq-title .icon {
            font-size: 18px;
        }

        .faq-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .faq-btn {
            padding: 14px 16px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            color: #495057;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .faq-btn:hover {
            background-color: #f8f9fa;
            border-color: #339af0;
            color: #339af0;
        }

        /* Input Area */
        .input-area {
            padding: 16px 24px 24px;
        }

        .input-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .input-wrapper:focus-within {
            border-color: #339af0;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(51, 154, 240, 0.1);
        }

        .input-wrapper .icon {
            font-size: 20px;
            color: #adb5bd;
        }

        .input-wrapper input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 15px;
            color: #212529;
            outline: none;
        }

        .input-wrapper input::placeholder {
            color: #adb5bd;
        }

        .send-btn {
            padding: 10px 20px;
            background-color: #339af0;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #ffffff;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .send-btn:hover {
            background-color: #228be6;
        }

        .send-btn:disabled {
            background-color: #adb5bd;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 640px) {
            body {
                padding: 0;
            }

            .chatbot-container {
                border-radius: 0;
                min-height: 100vh;
            }

            .faq-buttons {
                grid-template-columns: 1fr;
            }

            .header-title h1 {
                font-size: 20px;
            }

            .clear-history-btn span {
                display: none;
            }
        }

        /* Typing indicator */
        .typing-indicator {
            display: flex;
            gap: 4px;
            padding: 16px 20px;
            background-color: #f1f3f4;
            border-radius: 16px;
            border-bottom-left-radius: 4px;
            align-self: flex-start;
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            background-color: #adb5bd;
            border-radius: 50%;
            animation: typing 1.4s infinite ease-in-out;
        }

        .typing-indicator span:nth-child(1) { animation-delay: 0s; }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-8px); }
        }

        /* Storage Warning Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 32px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
        }

        .modal-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 12px;
        }

        .modal-message {
            font-size: 14px;
            color: #495057;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .modal-storage-info {
            background-color: #fff3cd;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #856404;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .modal-btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modal-btn-primary {
            background-color: #339af0;
            color: #ffffff;
            border: none;
        }

        .modal-btn-primary:hover {
            background-color: #228be6;
        }

        .modal-btn-secondary {
            background-color: #ffffff;
            color: #495057;
            border: 1px solid #dee2e6;
        }

        .modal-btn-secondary:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="chatbot-container">
        <!-- Header -->
        <header class="chatbot-header">
            <div class="header-left">
                <div class="avatar">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="6" width="18" height="14" rx="3" stroke="white" stroke-width="2"/>
                        <circle cx="8.5" cy="12" r="1.5" fill="white"/>
                        <circle cx="15.5" cy="12" r="1.5" fill="white"/>
                        <path d="M9 16H15" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        <path d="M8 6V4C8 3.44772 8.44772 3 9 3H15C15.5523 3 16 3.44772 16 4V6" stroke="white" stroke-width="2"/>
                        <circle cx="12" cy="3" r="1" fill="white"/>
                    </svg>
                </div>
                <div class="header-title">
                    <h1>GAISペディア</h1>
                    <p>生成AI協会 ナレッジアシスタント</p>
                </div>
            </div>
            <button class="clear-history-btn" id="clearHistoryBtn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 6H5H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6H19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>履歴をクリア</span>
            </button>
        </header>

        <!-- Chat Messages -->
        <div class="chat-messages" id="chatMessages">
            <!-- Messages will be loaded from localStorage or show welcome message -->
        </div>

        <!-- FAQ Section -->
        <section class="faq-section">
            <div class="faq-title">
                <span class="icon">💡</span>
                <span>よくある質問</span>
            </div>
            <div class="faq-buttons">
                <button class="faq-btn" data-question="GAISの会員になるにはどうすればいい？">
                    GAISの会員になるにはどうすればいい？
                </button>
                <button class="faq-btn" data-question="次回の勉強会はいつ？">
                    次回の勉強会はいつ？
                </button>
                <button class="faq-btn" data-question="建築土木WGの活動内容は？">
                    建築土木WGの活動内容は？
                </button>
                <button class="faq-btn" data-question="正会員と準会員の違いは？">
                    正会員と準会員の違いは？
                </button>
            </div>
        </section>

        <!-- Input Area -->
        <div class="input-area">
            <div class="input-wrapper">
                <span class="icon">💬</span>
                <input
                    type="text"
                    id="messageInput"
                    placeholder="生成AI協会について質問してください"
                    autocomplete="off"
                >
                <button class="send-btn" id="sendBtn">送信</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatMessages = document.getElementById('chatMessages');
            const messageInput = document.getElementById('messageInput');
            const sendBtn = document.getElementById('sendBtn');
            const clearHistoryBtn = document.getElementById('clearHistoryBtn');
            const faqButtons = document.querySelectorAll('.faq-btn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Modal elements
            const storageModal = document.getElementById('storageModal');
            const modalCloseBtn = document.getElementById('modalCloseBtn');
            const modalClearBtn = document.getElementById('modalClearBtn');
            const storageInfo = document.getElementById('storageInfo');

            // LocalStorage keys
            const STORAGE_KEY_HISTORY = 'gaispedia_chat_history';
            const STORAGE_KEY_MESSAGES = 'gaispedia_chat_messages';
            const STORAGE_WARNING_SIZE = 50 * 1024 * 1024; // 50MB in bytes

            // Default welcome message
            const WELCOME_MESSAGE = 'GAISペディアへようこそ！\n\n生成AI協会（GAIS）に関するご質問にお答えします。\n何かお手伝いできることはありますか？';

            // Conversation history for context
            let conversationHistory = [];

            // Show welcome message
            function showWelcomeMessage() {
                chatMessages.innerHTML = '';
                addMessage(WELCOME_MESSAGE, 'bot');
            }

            // LocalStorage functions
            function getStorageSize() {
                let total = 0;
                for (let key in localStorage) {
                    if (localStorage.hasOwnProperty(key)) {
                        total += localStorage[key].length * 2; // UTF-16 = 2 bytes per char
                    }
                }
                return total;
            }

            function formatBytes(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            function checkStorageSize() {
                const size = getStorageSize();
                if (size >= STORAGE_WARNING_SIZE) {
                    storageInfo.textContent = `現在の使用量: ${formatBytes(size)}`;
                    storageModal.classList.add('active');
                }
            }

            function saveToLocalStorage() {
                try {
                    localStorage.setItem(STORAGE_KEY_HISTORY, JSON.stringify(conversationHistory));
                    localStorage.setItem(STORAGE_KEY_MESSAGES, chatMessages.innerHTML);
                    checkStorageSize();
                } catch (e) {
                    console.error('LocalStorage save error:', e);
                    // Storage might be full
                    storageInfo.textContent = 'ストレージが満杯です！';
                    storageModal.classList.add('active');
                }
            }

            function loadFromLocalStorage() {
                try {
                    const savedHistory = localStorage.getItem(STORAGE_KEY_HISTORY);
                    const savedMessages = localStorage.getItem(STORAGE_KEY_MESSAGES);

                    if (savedHistory) {
                        conversationHistory = JSON.parse(savedHistory);
                    }

                    if (savedMessages) {
                        chatMessages.innerHTML = savedMessages;
                        scrollToBottom();
                    } else {
                        // First time visit - show welcome message
                        showWelcomeMessage();
                    }

                    checkStorageSize();
                } catch (e) {
                    console.error('LocalStorage load error:', e);
                    showWelcomeMessage();
                }
            }

            function clearLocalStorage() {
                localStorage.removeItem(STORAGE_KEY_HISTORY);
                localStorage.removeItem(STORAGE_KEY_MESSAGES);
            }

            // Modal functions
            function closeModal() {
                storageModal.classList.remove('active');
            }

            modalCloseBtn.addEventListener('click', closeModal);
            modalClearBtn.addEventListener('click', () => {
                clearHistory();
                closeModal();
            });

            // Close modal when clicking outside
            storageModal.addEventListener('click', (e) => {
                if (e.target === storageModal) {
                    closeModal();
                }
            });

            // Load saved history on page load
            loadFromLocalStorage();

            // Send message function
            async function sendMessage(text) {
                if (!text.trim()) return;

                // Disable input while processing
                setInputEnabled(false);

                // Add user message
                addMessage(text, 'user');
                messageInput.value = '';

                // Add to history
                conversationHistory.push({ role: 'user', content: text });

                // Show typing indicator
                showTypingIndicator();

                try {
                    const response = await fetch('/api/chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            message: text,
                            history: conversationHistory.slice(0, -1), // Send history without current message
                        }),
                    });

                    const data = await response.json();

                    hideTypingIndicator();

                    if (data.success) {
                        addMessage(data.message, 'bot');
                        // Add bot response to history
                        conversationHistory.push({ role: 'model', content: data.message });
                        // Save to localStorage
                        saveToLocalStorage();
                    } else {
                        addMessage('エラーが発生しました: ' + (data.error || '不明なエラー'), 'bot');
                    }
                } catch (error) {
                    hideTypingIndicator();
                    console.error('Chat error:', error);
                    addMessage('通信エラーが発生しました。もう一度お試しください。', 'bot');
                }

                // Re-enable input
                setInputEnabled(true);
                messageInput.focus();
            }

            // Enable/disable input
            function setInputEnabled(enabled) {
                messageInput.disabled = !enabled;
                sendBtn.disabled = !enabled;
                faqButtons.forEach(btn => btn.disabled = !enabled);
            }

            // Add message to chat
            function addMessage(text, type) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${type}`;
                // Support markdown-like formatting (basic)
                messageDiv.innerHTML = formatMessage(text);
                chatMessages.appendChild(messageDiv);
                scrollToBottom();
            }

            // Configure marked.js
            marked.setOptions({
                breaks: true,  // Convert \n to <br>
                gfm: true,     // GitHub Flavored Markdown
            });

            // Custom renderer to add target="_blank" to links
            const renderer = new marked.Renderer();
            renderer.link = function(href, title, text) {
                // Only allow gais.jp links
                if (!href.includes('gais.jp')) {
                    return text; // Just show text without link
                }
                // Skip Google redirect URLs
                if (href.includes('vertexaisearch.cloud.google.com') ||
                    href.includes('grounding-api-redirect')) {
                    return text;
                }
                const titleAttr = title ? ` title="${title}"` : '';
                return `<a href="${href}" target="_blank" rel="noopener noreferrer"${titleAttr}>${text}</a>`;
            };
            marked.setOptions({ renderer });

            // Format message using marked.js
            function formatMessage(text) {
                let formatted = text;

                // Pre-process: Filter out non-gais.jp URLs in plain text
                formatted = formatted.replace(
                    /(https?:\/\/[^\s\)]+)/g,
                    function(match, url) {
                        if (!url.includes('gais.jp')) {
                            return ''; // Remove non-gais.jp URLs
                        }
                        if (url.includes('vertexaisearch.cloud.google.com') ||
                            url.includes('grounding-api-redirect')) {
                            return '';
                        }
                        return url;
                    }
                );

                // Clean up empty reference lines
                formatted = formatted.replace(/^- \s*$/gm, '');
                formatted = formatted.replace(/参照リンク:\s*(\n\s*)*$/g, '');

                // Convert markdown to HTML using marked.js
                formatted = marked.parse(formatted);

                return formatted;
            }

            // Show typing indicator
            function showTypingIndicator() {
                const typingDiv = document.createElement('div');
                typingDiv.className = 'typing-indicator';
                typingDiv.id = 'typingIndicator';
                typingDiv.innerHTML = '<span></span><span></span><span></span>';
                chatMessages.appendChild(typingDiv);
                scrollToBottom();
            }

            // Hide typing indicator
            function hideTypingIndicator() {
                const typingIndicator = document.getElementById('typingIndicator');
                if (typingIndicator) {
                    typingIndicator.remove();
                }
            }

            // Scroll to bottom of chat
            function scrollToBottom() {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // Clear chat history
            function clearHistory() {
                conversationHistory = [];
                clearLocalStorage();
                showWelcomeMessage();
            }

            // Event listeners
            sendBtn.addEventListener('click', () => sendMessage(messageInput.value));

            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage(messageInput.value);
                }
            });

            clearHistoryBtn.addEventListener('click', clearHistory);

            faqButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const question = btn.dataset.question;
                    sendMessage(question);
                });
            });
        });
    </script>

    <!-- Storage Warning Modal -->
    <div class="modal-overlay" id="storageModal">
        <div class="modal-content">
            <div class="modal-icon">&#9888;&#65039;</div>
            <h2 class="modal-title">ストレージ容量の警告</h2>
            <p class="modal-message">
                チャット履歴のデータ量が多くなっています。<br>
                パフォーマンス向上のため、履歴をクリアすることをお勧めします。
            </p>
            <div class="modal-storage-info" id="storageInfo">
                現在の使用量: 計算中...
            </div>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-secondary" id="modalCloseBtn">後で</button>
                <button class="modal-btn modal-btn-primary" id="modalClearBtn">履歴をクリア</button>
            </div>
        </div>
    </div>
</body>
</html>
