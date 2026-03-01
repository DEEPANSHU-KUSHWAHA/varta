<button id="logoutBtn">Logout</button>
<div id="logoutResult"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$("#logoutBtn").on("click", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get("token");

    $.ajax({
        url: "../api/logout.php",
        type: "POST",
        data: { token: token },
        success: function(response) {
            let res = JSON.parse(response);
            if(res.message){
                window.location.href = "login.php";
            } else {
                $("#logoutResult").text(res.error || "Logout failed");
            }
        }
    });
});
</script>
<button id="refreshBtn">Refresh Session</button>
<div id="refreshResult"></div>

<script>
$("#refreshBtn").on("click", function() {
    const refreshToken = localStorage.getItem("refresh_token");

    $.ajax({
        url: "../api/refresh.php",
        type: "POST",
        data: { refresh_token: refreshToken },
        success: function(response) {
            let res = JSON.parse(response);
            if(res.access_token){
                localStorage.setItem("access_token", res.access_token);
                $("#refreshResult").text("Session refreshed!");
            } else {
                $("#refreshResult").text(res.error || "Refresh failed");
            }
        }
    });
});
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Get tokens from localStorage
let accessToken = localStorage.getItem("access_token");
let refreshToken = localStorage.getItem("refresh_token");

// Function to refresh session
function refreshSession() {
    $.ajax({
        url: "../api/refresh.php",
        type: "POST",
        data: { refresh_token: refreshToken },
        success: function(response) {
            let res = JSON.parse(response);
            if(res.access_token){
                accessToken = res.access_token;
                localStorage.setItem("access_token", accessToken);
                console.log("Session auto‑refreshed");
            } else {
                console.log("Refresh failed:", res.error);
                window.location.href = "login.php"; // force re‑login
            }
        }
    });
}

// Auto‑refresh every 55 minutes (before 1‑hour expiry)
setInterval(refreshSession, 55 * 60 * 1000);
</script>
