<div id="sessionsList"></div>
<div id="pagination"></div>

<script>
function loadSessions(page=1){
    $.ajax({
        url: "../api/sessions.php?page=" + page,
        type: "GET",
        success: function(response){
            let res = JSON.parse(response);
            let html = "<ul>";
            res.sessions.forEach(s => {
                html += "<li>User ID: " + s.user_id + " | Token: " + s.token.substring(0,20) + "...</li>";
            });
            html += "</ul>";
            $("#sessionsList").html(html);

            let pag = "";
            for(let i=1; i<=res.totalPages; i++){
                pag += "<a href='#' onclick='loadSessions("+i+")'>"+i+"</a> ";
            }
            $("#pagination").html(pag);
        }
    });
}
loadSessions();
</script>
