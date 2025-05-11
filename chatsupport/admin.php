<?php
session_start();
require_once 'config.php';

// Initialize session variables if not set
if (!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_logged_in'] = false;
}

// Admin credentials
$admin_username = "admin";
$admin_password = "admin123";

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "Invalid credentials";
    }
}

// Handle admin message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_message'])) {
    if (!$_SESSION['admin_logged_in']) {
        header("Location: admin.php");
        exit();
    }
    
    $message = $_POST['message'];
    $user_id = $_POST['user_id'];
    
    $stmt = $pdo->prepare("INSERT INTO support_messages (user_name, message, is_admin) VALUES (?, ?, 1)");
    $stmt->execute(['Admin', $message]);
    header("Location: admin.php");
    exit();
}

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_messages'])) {
    // Delete messages older than 5 minutes with no response
    $stmt = $pdo->prepare("
        DELETE FROM support_messages 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        AND is_admin = 0
        AND NOT EXISTS (
            SELECT 1 FROM support_messages m2 
            WHERE m2.user_name = support_messages.user_name 
            AND m2.created_at > support_messages.created_at
            AND m2.is_admin = 1
        )
    ");
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Handle clear all messages
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clear_all'])) {
    $stmt = $pdo->prepare("DELETE FROM support_messages");
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

// Check if admin is logged in
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Get statistics
if ($is_logged_in) {
    // Total messages
    $stmt = $pdo->query("SELECT COUNT(*) FROM support_messages");
    $total_messages = $stmt->fetchColumn();
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(DISTINCT user_name) FROM support_messages WHERE is_admin = 0");
    $total_users = $stmt->fetchColumn();
    
    // Recent messages
    $stmt = $pdo->query("SELECT * FROM support_messages ORDER BY created_at ASC");
    $recent_messages = $stmt->fetchAll();
    
    // Get unique users who have sent messages
    $stmt = $pdo->query("SELECT DISTINCT user_name FROM support_messages WHERE is_admin = 0 ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pryce Gas Support</title>
    <link href="style.css" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-box h3 {
            margin: 0;
            color: #666;
        }
        .stat-box p {
            font-size: 24px;
            margin: 10px 0 0;
            color: #007BFF;
        }
        .login-form {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .user-select {
            width: 200px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .chat-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }
        .message {
            margin: 10px 0;
            max-width: 80%;
        }
        .message.user {
            align-self: flex-start;
        }
        .message.agent {
            align-self: flex-end;
        }
        .bubble {
            padding: 12px 16px;
            border-radius: 18px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .agent .bubble {
            background: #e0f7fa;
            border-bottom-right-radius: 0;
        }
        .user .bubble {
            background: #c8e6c9;
            border-bottom-left-radius: 0;
        }
        .input-area {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 20px;
            border-top: 1px solid #eee;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .action-button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .delete-button {
            background-color: #dc3545;
            color: white;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
        .clear-button {
            background-color: #6c757d;
            color: white;
        }
        .clear-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php if (!$is_logged_in): ?>
            <!-- Login Form -->
            <div class="login-form">
                <h2>Admin Login</h2>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="input-area">
                        <input type="text" name="username" placeholder="Username" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <button type="submit" name="login">Login</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Admin Dashboard -->
            <header class="header">
                <div class="header-left">
                    <h3>Admin Dashboard</h3>
                </div>
                <div class="header-right">
                    <a href="admin.php?logout=1" class="back-btn">Logout</a>
                </div>
            </header>

            <div class="stats-container">
                <div class="stat-box">
                    <h3>Total Messages</h3>
                    <p><?php echo $total_messages; ?></p>
                </div>
                <div class="stat-box">
                    <h3>Total Users</h3>
                    <p><?php echo $total_users; ?></p>
                </div>
            </div>

            <div class="action-buttons">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="delete_messages" class="action-button delete-button">
                        Delete Old Messages
                    </button>
                </form>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="clear_all" class="action-button clear-button" 
                            onclick="return confirm('Are you sure you want to delete all messages? This cannot be undone.')">
                        Clear All Messages
                    </button>
                </form>
            </div>

            <div class="chat-container">
                <h2>Chat History</h2>
                <?php foreach ($recent_messages as $message): ?>
                    <div class="message <?php echo $message['is_admin'] ? 'agent' : 'user'; ?>">
                        <div class="bubble">
                            <strong><?php echo htmlspecialchars($message['user_name']); ?></strong>
                            <p><?php echo htmlspecialchars($message['message']); ?></p>
                            <small><?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="input-area">
                <form method="POST" class="message-form">
                    <input type="hidden" name="admin_message" value="1">
                    <select name="user_id" class="user-select" required>
                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo htmlspecialchars($user['user_name']); ?>">
                                <?php echo htmlspecialchars($user['user_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <textarea name="message" placeholder="Type your response here..." required></textarea>
                    <button type="submit">Send Response</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <script>
        // Auto-scroll to bottom of chat container
        const chatContainer = document.querySelector('.chat-container');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    </script>
</body>
</html> 