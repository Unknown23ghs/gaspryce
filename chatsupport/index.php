<?php
session_start();
require_once 'config.php';

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $message = $_POST['message'];
    
    $stmt = $pdo->prepare("INSERT INTO support_messages (user_name, message) VALUES (?, ?)");
    $stmt->execute([$name, $message]);
    header("Location: index.php");
    exit();
}

// Get all messages
$stmt = $pdo->query("SELECT * FROM support_messages ORDER BY created_at ASC");
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chat with Support - Pryce Gas</title>
    <link href="style.css" rel="stylesheet" />
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <button class="menu-btn">
                    MENU
                </button>
                <h2>|</h2>
                <h3>Pryce Gas Support Center</h3>
            </div>
            <div class="header-right">
                <a href="../index.html" class="back-btn">Back to Main</a>
            </div>
        </header>

        <div class="chat-container">
            <div class="messages">
                <?php foreach ($messages as $message): ?>
                    <div class="message-wrapper <?php echo $message['is_admin'] ? 'admin-message' : 'user-message'; ?>">
                        <div class="message <?php echo $message['is_admin'] ? 'agent' : 'user'; ?>">
                            <div class="bubble">
                                <?php if (!$message['is_admin']): ?>
                                    <strong><?php echo htmlspecialchars($message['user_name']); ?></strong>
                                <?php endif; ?>
                                <p><?php echo htmlspecialchars($message['message']); ?></p>
                                <small><?php echo date('H:i', strtotime($message['created_at'])); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="input-area">
                <form method="POST" class="message-form">
                    <input type="text" name="name" placeholder="Your Name" required>
                    <textarea name="message" placeholder="Type your message here..." required></textarea>
                    <button type="submit">Send</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        // Auto-scroll to bottom of chat container
        const chatContainer = document.querySelector('.messages');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    </script>
</body>
</html>