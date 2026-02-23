document.addEventListener("DOMContentLoaded", function () {

    // ===== Course Color Sync =====
    const courseColor = document.getElementById('CourseColor');
    const courseColorHex = document.getElementById('CourseColorHex');
    const courseColorPreview = document.getElementById('CourseColorPreview');

    if (courseColor) {
        courseColor.addEventListener('input', function () {
            courseColorHex.value = this.value;
            courseColorPreview.style.backgroundColor = this.value;
        });
    }

    // ===== Prevent form resubmission on refresh =====
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // ===== Clear / Reset Button =====
    const resetBtn = document.getElementById("reset_all");

    if (resetBtn) {
        resetBtn.addEventListener("click", function () {

            // Clear text inputs immediately
            document.querySelectorAll("input[type='text']").forEach(input => {
                input.value = "";
            });

            if (confirm("Are you sure you want to clear all fields and reset the session?")) {
                const form = this.closest("form");

                const hidden = document.createElement("input");
                hidden.type = "hidden";
                hidden.name = "reset_all";
                hidden.value = "1";

                form.appendChild(hidden);
                form.submit();
            }
        });
    }

});