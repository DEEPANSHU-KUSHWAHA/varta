<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Notification</title>
    <link rel="stylesheet" href="public/css/navbar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../app/navbar/index.php'; ?>

    <h2>Send Notification (Admin Simulation)</h2>
    <form id="notifyForm">
        <label>User ID:</label>
        <input type="number" name="user_id" required><br>

        <label>Message:</label>
        <input type="text" name="message" required><br>

        <label>Type:</label>
        <select name="type">
            <option value="info">Info</option>
            <option value="success">Success</option>
            <option value="warning">Warning</option>
            <option value="error">Error</option>
        </select><br>

        <button type="submit">Send</button>
    </form>

    <div id="notifyResult"></div>

    <script>
    $("#notifyForm").on("submit", function(e) {
        e.preventDefault();
        $.ajax({
            url: "../api/notify.php",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                let res = JSON.parse(response);
                if(res.message){
                    $("#notifyResult").text(res.message);
                } else {
                    $("#notifyResult").text(res.error || "Failed to send notification");
                }
            }
        });
    });
    </script>
</body>
</html>
