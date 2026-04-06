

// ========== SPACE DROPDOWN LOADING ==========
document.addEventListener("DOMContentLoaded", function () {
    const typeSelect = document.getElementById("spaceType");
    const spaceList = document.getElementById("spaceList");

    if (typeSelect && spaceList) {
        typeSelect.addEventListener("change", function () {
            const type = this.value;
            if (type) {
                fetch(`getspaces.php?type=${type}`)
                    .then(res => res.json())
                    .then(data => {
                        spaceList.innerHTML = "";
                        if (data.length > 0) {
                            data.forEach(space => {
                                const option = document.createElement("option");
                                option.value = space.id;
                                option.textContent = `${space.name} (Capacity: ${space.capacity})`;
                                spaceList.appendChild(option);
                            });
                        } else {
                            const opt = document.createElement("option");
                            opt.textContent = "No available spaces";
                            spaceList.appendChild(opt);
                        }
                    })
                    .catch(err => console.error("Error fetching spaces:", err));
            }
        });
    }
});

// ========== CONFIRM ACTION FUNCTION ==========
function confirmAction(message) {
    return confirm(message || "Are you sure?");
}

// =========================
// 30-MINUTE BOOKING REMINDER
// =========================
$(document).ready(function () {

    console.log("[Reminder] Initialized");

    // Run immediately and every 2 minutes
    checkUpcomingBookings();
    setInterval(checkUpcomingBookings, 2 * 60 * 1000);

    function checkUpcomingBookings() {
        $.ajax({
            url: "get_user_bookings.php",
            method: "GET",
            dataType: "json",
            success: function (bookings) {
                console.log("[Reminder] Fetched bookings:", bookings);
                if (!Array.isArray(bookings) || bookings.length === 0) return;

                const now = new Date();

                bookings.forEach(function (b) {
                    // Combine date and time in a local-friendly way
                   // Combine date + time in a local-friendly way (avoid UTC shift)
                    const startTime = new Date(`${b.booking_date} ${b.start_time}`);
                    const diffMs = startTime.getTime() - Date.now();
                    const diffMin = diffMs / 60000;

                    // Debug logs
                    console.log(`[Debug] Booking ID ${b.id}`);
                    console.log("Booking Date:", b.booking_date, "Start Time:", b.start_time);
                    console.log("Parsed startTime:", startTime.toString());
                    console.log("Now:", new Date().toString());
                    console.log("Difference (mins):", diffMin);



                    // Only show if within 30 minutes and not in the past
                    if (diffMin > 0 && diffMin <= 30) {
                        const key = `reminder_shown_${b.id}`;
                        if (sessionStorage.getItem(key)) return;

                        console.log(`[Reminder] Showing popup for booking ${b.id}`);
                        const msg = `
                            Reminder: Your booking for <strong>${b.space_name}</strong> 
                            starts at <strong>${b.start_time}</strong> on <strong>${b.booking_date}</strong>.
                            Reason: ${b.reason || "N/A"}
                        `;
                        showReminderAlert(msg);
                        sessionStorage.setItem(key, "true");
                    }
                });
            },
            error: function (xhr, status, err) {
                console.error("[Reminder] Error checking upcoming bookings:", status, err);
            }
        });
    }

    function showReminderAlert(message) {
        const alert = $(`
            <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                ⏰ ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        // Add alert at the top of the page (under navbar)
        const container = $("main, .container, body").first();
        container.prepend(alert);

        // Auto-remove after 10 seconds
        setTimeout(() => {
            alert.alert("close");
        }, 10000);
    }
});



