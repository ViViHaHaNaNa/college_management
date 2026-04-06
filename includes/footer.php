    </main>

    <!-- <footer class="bg-dark text-white text-center py-3 fixed-bottom">


        <p class="mb-0">&copy; <?= date("Y"); ?> Campus Space Booking System. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script> -->

    <footer class="bg-dark text-white text-center mt-5 py-4 custom-navbar">
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y') ?> College Management</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>

<!-- </body>
</html> -->

    
        
    <!-- jQuery + Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>

    <script>
        $(document).ready(function() {
        $.ajax({
            url: "get_notifications.php",
            method: "GET",
            dataType: "json",
            success: function(data) {
            if (data && data.length > 0) {
                let html = '<div class="alert alert-warning text-center mb-0" role="alert">';
                data.forEach(n => {
                html += `<p class="mb-0">${n.message}</p>`;
                });
                html += '</div>';
                $("body").prepend(html); // show at top of page
            }
            }
        });
        });
    </script>

    <script>
        $(document).ready(function () {
        $.ajax({
            url: "get_notifications.php",
            method: "GET",
            dataType: "json",
            success: function (data) {
            if (data.length > 0) {
                let messages = "";
                data.forEach(n => {
                messages += `<p>${n.message}</p>`;
                });
                $("#notificationBody").html(messages);
                $("#userNotificationModal").modal("show");
            }
            },
            error: function () {
            console.error("Error fetching notifications.");
            }
        });
        });
    </script>

    <script>
        $(document).ready(function () {
        $.ajax({
            url: "get_notifications.php",
            method: "GET",
            dataType: "json",
            success: function (data) {
            if (data && data.length > 0) {
                let messages = "";
                data.forEach(n => {
                messages += `<p class='mb-1'>${n.message}</p>`;
                });
                $("#notificationMessage").html(messages);
                $("#notificationArea").show(); // make visible
            }
            },
            error: function (xhr, status, error) {
            console.error("Error fetching notifications:", error);
            }
        });
        });
    </script>

    <script>
function updateCountdowns() {
    const cards = document.querySelectorAll('.booking-card');

    cards.forEach(card => {
        const startTime = new Date(card.dataset.start).getTime();
        const now = new Date().getTime();
        const diff = startTime - now;

        const countdownEl = card.querySelector('.booking-countdown');

        if (diff <= 0) {
            countdownEl.innerHTML = "Started";
            countdownEl.classList.remove("text-danger");
            countdownEl.classList.add("text-success");
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        let text = "Starts in: ";

        if (days > 0) text += days + "d ";
        text += hours + "h " + minutes + "m " + seconds + "s";

        countdownEl.innerHTML = text;
    });
}

setInterval(updateCountdowns, 1000);
updateCountdowns();
</script>


</body>
</html>
