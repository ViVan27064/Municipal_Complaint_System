function showComplaintPopup(data){

    const container=document.getElementById("toastContainer");

    const toast=document.createElement("div");

    toast.className="toast";

    toast.innerHTML=`
        <div class="toast-title">
            🔔 New Complaint
        </div>

        <div class="toast-body">
            <b>${data.category}</b><br>
            ${data.location}
        </div>

        <div class="toast-time">
            Just now
        </div>
    `;

    toast.onclick=()=>{

        window.location.href="../admin/AllComplaints.php";

    };

    container.appendChild(toast);

    setTimeout(()=>{
        toast.classList.add("show");
    },100);

    setTimeout(()=>{

        toast.classList.remove("show");

        setTimeout(()=>{
            toast.remove();
        },350);

    },5000);

}
function refreshDashboard() {

    fetch("../admin/api/dashboard_stats.php")
    .then(response => response.json())
    .then(data => {

        document.getElementById("total_count").textContent = data.total_count;
        document.getElementById("pending_assign").textContent = data.pending_assign;
        document.getElementById("assigned_count").textContent = data.assigned_count;
        document.getElementById("resolved_count").textContent = data.resolved_count;

    })
    .catch(error => console.error(error));

}

function refreshComplaintTable(id) {

    fetch(`api/latest_complaint.php?id=${id}`)
        .then(response => response.text())
        .then(html => {

            document
                .getElementById("complaintsContainer")
                .insertAdjacentHTML("afterbegin", html);

        });

}
