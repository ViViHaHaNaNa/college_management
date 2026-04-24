    </main>

    <!-- ================= GLOBAL FOOTER ================= -->
<footer id="contact" class="bg-gray-900 text-gray-300 py-12 mt-20">

  <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-10">

    <!-- Brand -->
    <div>
      <h2 class="text-xl font-bold text-white mb-3">CampusBook</h2>
      <p class="text-sm text-gray-400">
        Smart campus booking system for classrooms, libraries, and recreational spaces.
      </p>
    </div>

    <!-- Links -->
    <div>
      <h3 class="text-white font-semibold mb-3">Quick Links</h3>
      <ul class="space-y-2 text-sm">
        <li><a href="book.php" class="hover:text-white transition">Book Space</a></li>
        <li><a href="my_bookings.php" class="hover:text-white transition">My Bookings</a></li>
        <li><a href="#spaces" class="hover:text-white transition">Explore</a></li>
      </ul>
    </div>

    <!-- Contact -->
    <div>
      <h3 class="text-white font-semibold mb-3">Contact</h3>
      <p class="text-sm text-gray-400">
        support@campusbook.com<br>
        +91 98765 43210
      </p>
    </div>

  </div>

  <!-- Bottom -->
  <div class="text-center text-gray-500 text-sm mt-10 border-t border-gray-700 pt-6">
    © <?php echo date("Y"); ?> CampusBook. All rights reserved.
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
