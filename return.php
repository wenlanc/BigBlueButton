<?php
require_once '../../gibbon.php';

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_view_full.php') == false) {
    //Access denied
    echo "<div class='error'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Session Ended</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #06172A;
            font-family: ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }

        .container {
            text-align: center;
            background: #263B81;
            padding: 40px;
            border-radius: 12px;
        }

        .message {
            font-size: 1.05em;
            margin-bottom: 20px;
            line-height: 1.8em;
            color: white;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #162B71;
            border-top: 4px solid #0078D7;
            border-radius: 50%;
            animation: spin 6s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="message">
            <div class="spinner"></div>
The video session has ended, and the recording is currently being processed.<br> It will be available shortly after a quick polish.</div>
    </div>
</body>
</html>
