<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Varta</title>
    <link rel="stylesheet" href="public/css/navbar.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../app/navbar/index.php'; ?>

    <h2>Login</h2>
<form id="loginForm">
    <label>Username:</label>
    <input type="text" name="username" required><br>

    <label>Password:</label>
    <input type="password" name="password" required><br>

    <label>OTP:</label>
    <input type="text" name="otp" required><br>

    <label>
        <input type="checkbox" name="remember" value="1"> Remember Me
    </label><br>

    <button type="submit">Login</button>
</form>


    <div id="loginResult"></div>

    <script>
    $("#loginForm").on("submit", function(e) {
        e.preventDefault();
        $.ajax({
            url: "../api/login.php",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                let res = JSON.parse(response);
                if(res.access_token){
                    window.location.href = "home.php?token=" + res.access_token;
                } else {
                    $("#loginResult").text(res.error || "Login failed");
                }
            }
        });
    });
    </script>
</body>
</html>
