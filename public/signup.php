<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup - Varta</title>
    <link rel="stylesheet" href="public/css/navbar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../app/navbar/index.php'; ?>

    <h2>Signup</h2>
    <form id="signupForm">
        <label>Username:</label>
        <input type="text" name="username" required><br>

        <label>Password:</label>
        <input type="password" name="password" required><br>

        <button type="submit">Signup</button>
    </form>

    <div id="signupResult"></div>

    <script>
    $("#signupForm").on("submit", function(e) {
        e.preventDefault();
        $.ajax({
            url: "../api/signup.php",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                let res = JSON.parse(response);
                if(res.message){
                    $("#signupResult").html("Signup successful. Save this TOTP secret: <b>" + res.totp_secret + "</b>");
                } else {
                    $("#signupResult").text("Signup failed");
                }
            }
        });
    });
    </script>
</body>
</html>
