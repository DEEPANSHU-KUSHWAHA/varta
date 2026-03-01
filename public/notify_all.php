<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Broadcast Notification</title>
    <link rel="stylesheet" href="public/css/navbar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../app/navbar/index.php'; ?>

    <h2>Broadcast Notification (Admin Simulation)</h2>
    <form id="notifyAllForm">
        <label>Message:</label>
        <input type="text" name="message" required><br>

        <label>Type:</label>
        <select name="type">
            <option value="info">Info</option>
            <option value="success">Success</option>
            <option value="warning">Warning</option>
            <option value="error">Error</option>
        </select><br>

        <button type="submit">Send to All Users</button>
    </form>

    <div id="notifyAllResult"></div>

    <script>
    $("#notifyAllForm").on("submit", function(e) {
        e.preventDefault();
        const token = localStorage.getItem("access_token"); // must be admin token
        $.ajax({
            url: "../api/notify_all.php",
            type: "POST",
            data: $(this).serialize() + "&token=" + token,
            success: function(response) {
                let res = JSON.parse(response);
                if(res.message){
                    $("#notifyAllResult").text(res.message);
                } else {
                    $("#notifyAllResult").text(res.error || "Failed to send broadcast");
                }
            }
        });
    });
    </script>
</body>
</html>
