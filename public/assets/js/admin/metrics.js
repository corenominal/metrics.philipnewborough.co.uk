/* global Chart */

// ── Sidebar active links ───────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    const path = window.location.pathname;

    document.querySelectorAll("#sidebar .nav-link").forEach((link) => {
        const href = link.getAttribute("href");
        if (!href) return;
        if (path === href || (path.startsWith("/admin/metrics/domain") && href === "/admin/metrics/domains")) {
            link.classList.remove("text-white-50");
            link.classList.add("active");
        }
    });
});

// ── Domain page chart ──────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
    const dataEl = document.getElementById("dashboard-data");
    if (!dataEl) return;

    let data;
    try {
        data = JSON.parse(dataEl.textContent);
    } catch (e) {
        return;
    }

    const gridColor = "rgba(255,255,255,0.07)";
    const tickColor = "rgba(255,255,255,0.4)";

    // Hits line chart (domain drill-down)
    const hitsCanvas = document.getElementById("hits-chart");
    if (hitsCanvas && data.hitsLabels) {
        new Chart(hitsCanvas, {
            type: "line",
            data: {
                labels: data.hitsLabels,
                datasets: [{
                    label: "Hits",
                    data: data.hitsValues,
                    fill: true,
                    borderColor: "rgba(255,211,0,0.9)",
                    backgroundColor: "rgba(255,211,0,0.12)",
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    tension: 0.3,
                }],
            },
            options: {
                responsive: true,
                animation: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: "index", intersect: false },
                },
                scales: {
                    x: {
                        ticks: { color: tickColor, maxTicksLimit: 10, maxRotation: 0 },
                        grid:  { color: gridColor },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: tickColor, precision: 0 },
                        grid:  { color: gridColor },
                    },
                },
            },
        });
    }

    // Device doughnut chart
    const deviceCanvas = document.getElementById("device-chart");
    if (deviceCanvas && data.deviceLabels && data.deviceLabels.length) {
        const DEVICE_COLOURS = {
            desktop: "rgba(13,110,253,0.85)",
            mobile:  "rgba(25,135,84,0.85)",
            tablet:  "rgba(255,193,7,0.85)",
        };

        const colours = data.deviceLabels.map(
            (l) => DEVICE_COLOURS[l.toLowerCase()] || "rgba(108,117,125,0.85)"
        );

        document.querySelectorAll(".device-legend-dot").forEach((dot) => {
            const c = DEVICE_COLOURS[dot.dataset.device?.toLowerCase()] || "rgba(108,117,125,0.85)";
            dot.style.backgroundColor = c;
            dot.style.width     = "10px";
            dot.style.height    = "10px";
            dot.style.flexShrink = "0";
            dot.style.display  = "inline-block";
        });

        new Chart(deviceCanvas, {
            type: "doughnut",
            data: {
                labels: data.deviceLabels,
                datasets: [{
                    data: data.deviceValues,
                    backgroundColor: colours,
                    borderWidth: 2,
                    borderColor: "#212529",
                }],
            },
            options: {
                responsive: false,
                animation: false,
                cutout: "65%",
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: {
                        label: (ctx) => ` ${ctx.label}: ${ctx.formattedValue} hits`,
                    }},
                },
            },
        });
    }
});
