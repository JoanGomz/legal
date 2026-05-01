import ApexCharts from "apexcharts";

let chart2Instance = null;
let userGraphInstance = null;
function chart1() {
    const el = document.querySelector("#chart2");

    if (el && el.dataset.series && el.dataset.labels) {
        if (chart2Instance) {
            chart2Instance.destroy();
            chart2Instance = null;
        }

        el.innerHTML = "";

        const options = {
            // Obtenemos los datos frescos que Livewire inyectó en el dataset
            series: JSON.parse(el.dataset.series || "[]"),
            chart: {
                type: "bar",
                height: 450,
                width: "1800px",
                stacked: true,
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    dataLabels: {
                        total: {
                            enabled: true,
                            offsetX: 0,
                            style: {
                                fontSize: "15px",
                                fontWeight: 900,
                            },
                        },
                    },
                },
            },
            stroke: {
                width: 1,
            },
            grid: {
                padding: { left: 0, right: 20, bottom: 0, top: 0 },
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: "15px",
                    },
                },
            },
            legend: {
                position: "bottom",
                horizontalAlign: "center",
                inverseOrder: false,
                tooltipHoverFormatter: undefined,
                height: 60,
                fontSize: "15px",
            },
            xaxis: {
                categories: JSON.parse(el.dataset.labels || "[]"),
            },
        };

        // 4. Crear la nueva instancia y renderizar
        chart2Instance = new ApexCharts(el, options);
        chart2Instance.render();
    }

    //Grafica Radial
    const radialEl = document.querySelector("#chart_radial");

    if (radialEl && radialEl.dataset.series && radialEl.dataset.labels) {
        radialEl.innerHTML = "";

        try {
            const seriesData = JSON.parse(radialEl.dataset.series);
            const labelsData = JSON.parse(radialEl.dataset.labels);
            const total = radialEl.dataset.total;

            const optionsRadial = {
                series: seriesData,
                chart: {
                    height: 400,
                    type: "radialBar",
                },
                plotOptions: {
                    radialBar: {
                        dataLabels: {
                            total: {
                                show: true,
                                label: "Total",
                                formatter: function (w) {
                                    return total;
                                },
                            },
                        },
                    },
                },

                labels: labelsData,
            };

            new ApexCharts(radialEl, optionsRadial).render();
        } catch (error) {
            console.error("Error al renderizar RadialBar:", error);
        }
    }
    // Grafica de usuarios

    const userGraph = document.querySelector("#chart_by_park");

    if (userGraph && userGraph.dataset.series && userGraph.dataset.labels) {
        // 2. Destruir la instancia previa si existe para evitar que desaparezca o se duplique
        if (userGraphInstance) {
            userGraphInstance.destroy();
            userGraphInstance = null;
        }

        userGraph.innerHTML = "";

        try {
            const seriesData = JSON.parse(userGraph.dataset.series);
            const labelsData = JSON.parse(userGraph.dataset.labels);

            const optionsUserGraph = {
                labels: labelsData,
                series: [
                    {
                        name: "Consentimientos",
                        data: seriesData,
                    },
                ],
                chart: {
                    height: 350,
                    width: "100%", // Aseguramos que llene el contenedor tras el filtro
                    type: "bar",
                    events: {
                        click: function (chart, w, e) {},
                    },
                },
                plotOptions: {
                    bar: {
                        columnWidth: "45%",
                        distributed: true,
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                legend: {
                    show: false,
                },
                xaxis: {
                    categories: labelsData,
                    labels: {
                        style: {
                            fontSize: "12px",
                        },
                    },
                },
            };

            // 3. Guardamos la nueva instancia en la variable global
            userGraphInstance = new ApexCharts(userGraph, optionsUserGraph);
            userGraphInstance.render();
        } catch (error) {
            console.error("Error al renderizar chart_by_park:", error);
        }
    }
}

document.addEventListener("livewire:navigated", chart1);

document.addEventListener("livewire:initialized", () => {
    Livewire.hook("morph.updated", ({ el, component }) => {
        const hasCharts = el.querySelector(
            "#chart2, #chart_by_park, #chart_radial",
        );
        if (hasCharts) {
            setTimeout(() => {
                chart1();
            }, 50);
        }
    });
});

document.addEventListener("DOMContentLoaded", chart1);
