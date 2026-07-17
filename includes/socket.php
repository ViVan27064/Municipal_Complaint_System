<style>
#toastContainer{
    position:fixed;
    top:20px;
    right:20px;
    width:350px;
    z-index:99999;
}

.toast{
    background:#fff;
    border-left:5px solid #0d6efd;
    border-radius:10px;
    padding:15px;
    margin-bottom:12px;
    box-shadow:0 8px 20px rgba(0,0,0,.18);
    cursor:pointer;
    overflow:hidden;

    transform:translateX(120%);
    opacity:0;

    transition:all .35s ease;
}

.toast.show{
    transform:translateX(0);
    opacity:1;
}

.toast-title{
    font-size:16px;
    font-weight:600;
    color:#0d6efd;
    margin-bottom:8px;
}

.toast-body{
    color:#444;
    font-size:14px;
    line-height:1.5;
}

.toast-time{
    margin-top:10px;
    color:#777;
    font-size:12px;
}

.toast-progress{
    position:absolute;
    left:0;
    bottom:0;
    height:4px;
    width:100%;
    background:#0d6efd;
    animation:toastTimer 5s linear forwards;
}

@keyframes toastTimer{
    from{
        width:100%;
    }
    to{
        width:0%;
    }
}
</style>

<div id="toastContainer"></div>

<script src="https://mccts-socket-server.onrender.com/socket.io/socket.io.js"></script>

<script>

const socket = io("https://mccts-socket-server.onrender.com");

socket.on("connect", () => {
    console.log("Connected to Socket.IO");
});

socket.on("disconnect", () => {
    console.log("Disconnected");
});

socket.on("newComplaint", (data) => {

    showComplaintPopup(data);

});

function showComplaintPopup(data){

    const container = document.getElementById("toastContainer");

    const toast = document.createElement("div");

    toast.className = "toast";

    toast.innerHTML = `
        <div class="toast-title">
            🔔 New Complaint
        </div>

        <div class="toast-body">
            <strong>${data.category}</strong><br>
            ${data.location}
        </div>

        <div class="toast-time">
            Just now
        </div>

        <div class="toast-progress"></div>
    `;

    toast.onclick = () => {

        window.location.href = "../admin/AllComplaints.php";

    };

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add("show");
    }, 100);

    setTimeout(() => {

        toast.classList.remove("show");

        setTimeout(() => {
            toast.remove();
        }, 350);

    }, 5000);

}

</script>