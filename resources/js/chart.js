import ApexCharts from "apexcharts";

// Mantener referencias a todas las instancias
let chart2Instance = null;
let radialInstance = null;
let userGraphInstance = null;

function destroyCharts() {
    if (chart2Instance) {
        chart2Instance.destroy();
        chart2Instance = null;
    }
    if (radialInstance) {
        radialInstance.destroy();
        radialInstance = null;
    }
    if (userGraphInstance) {
        userGraphInstance.destroy();
        userGraphInstance = null;
    }
}

function chart1() {
    // 1. Gráfica Bar (chart2)
    const el = document.querySelector("#chart2");
    if (el && el.dataset.series && el.dataset.labels) {
        if (chart2Instance) chart2Instance.destroy();
        el.innerHTML = "";

        try {
            const options = {
                series: JSON.parse(el.dataset.series || "[]"),
                chart: {
                    type: "bar",
                    height: 450,
                    width: "100%", // Se recomienda 100% para evitar desbordamientos
                    stacked: true,
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        dataLabels: {
                            total: {
                                enabled: true,
                                offsetX: 0,
                                style: { fontSize: "15px", fontWeight: 900 },
                            },
                        },
                    },
                },
                stroke: { width: 1 },
                grid: { padding: { left: 0, right: 20, bottom: 0, top: 0 } },
                yaxis: { labels: { style: { fontSize: "15px" } } },
                legend: {
                    position: "bottom",
                    horizontalAlign: "center",
                    height: 60,
                    fontSize: "15px",
                },
                xaxis: {
                    categories: JSON.parse(el.dataset.labels || "[]"),
                },
            };

            chart2Instance = new ApexCharts(el, options);
            chart2Instance.render();
        } catch (e) {
            console.error("Error en chart2:", e);
        }
    }

    // 2. Gráfica Radial (chart_radial)
    const radialEl = document.querySelector("#chart_radial");
    if (radialEl && radialEl.dataset.series && radialEl.dataset.labels) {
        if (radialInstance) radialInstance.destroy();
        radialEl.innerHTML = "";

        try {
            let rawSeries = JSON.parse(radialEl.dataset.series || "[]");
            let rawLabels = JSON.parse(radialEl.dataset.labels || "[]");

            const seriesData = Array.isArray(rawSeries)
                ? rawSeries.map((v) =>
                      v === null || v === undefined ? 0 : Number(v),
                  )
                : [];
            const labelsData = Array.isArray(rawLabels)
                ? rawLabels.map((v) =>
                      v === null || v === undefined ? "" : String(v),
                  )
                : [];
            const total = radialEl.dataset.total ?? "0";

            if (seriesData.length > 0) {
                const optionsRadial = {
                    series: seriesData,
                    chart: { height: 350, type: "radialBar" },
                    plotOptions: {
                        radialBar: {
                            dataLabels: {
                                total: {
                                    show: true,
                                    label: "Total",
                                    formatter: function () {
                                        return String(total);
                                    },
                                },
                            },
                        },
                    },
                    labels: labelsData,
                };

                radialInstance = new ApexCharts(radialEl, optionsRadial);
                radialInstance.render();
            }
        } catch (error) {
            console.error("Error al renderizar RadialBar:", error);
        }
    }

    // 3. Gráfica Usuarios (chart_by_park)
    const userGraph = document.querySelector("#chart_by_park");
    if (userGraph && userGraph.dataset.series && userGraph.dataset.labels) {
        if (userGraphInstance) userGraphInstance.destroy();
        userGraph.innerHTML = "";

        try {
            const seriesData = JSON.parse(userGraph.dataset.series || "[]");
            const labelsData = JSON.parse(userGraph.dataset.labels || "[]");

            const optionsUserGraph = {
                labels: labelsData,
                series: [{ name: "Consentimientos", data: seriesData }],
                chart: {
                    height: 350,
                    width: "100%",
                    type: "bar",
                },
                plotOptions: {
                    bar: { columnWidth: "45%", distributed: true },
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                xaxis: {
                    categories: labelsData,
                    labels: { style: { fontSize: "12px" } },
                },
            };

            userGraphInstance = new ApexCharts(userGraph, optionsUserGraph);
            userGraphInstance.render();
        } catch (error) {
            console.error("Error al renderizar chart_by_park:", error);
        }
    }
}

function safeInitCharts() {
    destroyCharts();
    requestAnimationFrame(() => {
        setTimeout(() => {
            chart1();
        }, 100);
    });
}

// EVENTOS DE NAVEGACIÓN Y CARGA
document.addEventListener("livewire:navigated", safeInitCharts);

document.addEventListener("livewire:initialized", () => {
    Livewire.hook("morph.updated", ({ el }) => {
        const hasCharts = el.querySelector(
            "#chart2, #chart_by_park, #chart_radial",
        );
        if (hasCharts) {
            safeInitCharts();
        }
    });
});
// Al final de tu archivo chart.js agrega esto:
window.safeInitCharts = safeInitCharts;
document.addEventListener("DOMContentLoaded", safeInitCharts);
