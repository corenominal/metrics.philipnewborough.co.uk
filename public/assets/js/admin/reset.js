/* global bootstrap */

// ── Sidebar active link ─────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("#sidebar .nav-link").forEach((link) => {
        if (link.getAttribute("href") === "/admin/reset") {
            link.classList.remove("text-white-50");
            link.classList.add("active");
        }
    });
});

// ── Reset modal ─────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    const CONFIRM_PHRASE = "RESET ALL DATA";

    const openBtn    = document.getElementById("open-reset-modal");
    const modalEl    = document.getElementById("reset-modal");
    const phraseInput = document.getElementById("reset-phrase");
    const confirmBtn  = document.getElementById("confirm-reset");
    const alertsEl   = document.getElementById("reset-alerts");

    if (!openBtn || !modalEl || !phraseInput || !confirmBtn) return;

    const modal = new bootstrap.Modal(modalEl);

    openBtn.addEventListener("click", function () {
        phraseInput.value = "";
        confirmBtn.disabled = true;
        modal.show();
        setTimeout(function () { phraseInput.focus(); }, 300);
    });

    phraseInput.addEventListener("input", function () {
        confirmBtn.disabled = phraseInput.value !== CONFIRM_PHRASE;
    });

    confirmBtn.addEventListener("click", function () {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Resetting\u2026';

        fetch("/admin/reset", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({}),
        })
            .then(function (res) {
                if (!res.ok) {
                    throw new Error("Server error " + res.status);
                }
                return res.json();
            })
            .then(function (data) {
                modal.hide();
                if (data.status === "success") {
                    showAlert("success", '<i class="bi bi-check-circle-fill me-1"></i> All stats have been reset successfully.');
                } else {
                    showAlert("danger", data.message || "An unexpected error occurred.");
                }
            })
            .catch(function () {
                modal.hide();
                showAlert("danger", '<i class="bi bi-exclamation-circle-fill me-1"></i> An error occurred. Please try again.');
            });
    });

    modalEl.addEventListener("hidden.bs.modal", function () {
        phraseInput.value = "";
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="bi bi-trash3-fill me-1"></i> Confirm Reset';
    });

    function showAlert(type, message) {
        if (!alertsEl) return;
        const alert = document.createElement("div");
        alert.className = "alert alert-" + type + " alert-dismissible fade show mb-4";
        alert.setAttribute("role", "alert");
        alert.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        alertsEl.innerHTML = "";
        alertsEl.appendChild(alert);
        alertsEl.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }
});
