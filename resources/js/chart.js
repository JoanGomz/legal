import ApexCharts from "apexcharts";

function chart1() {
    const el = document.querySelector("#chart2");
    if (el) {
        el.innerHTML = "";

        const options = {
            series: JSON.parse(el.dataset.series),
            chart: {
                type: "bar",
                height: 450,
                width: 1750,
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
                categories: JSON.parse(el.dataset.categories),
            },
        };

        const chart = new ApexCharts(el, options);
        chart.render();
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

            new ApexCharts(userGraph, optionsUserGraph).render();
        } catch (error) {
            console.error("Error al renderizar chart:", error);
        }
    }
}

document.addEventListener("DOMContentLoaded", chart1);

document.addEventListener("livewire:navigated", () => {
    chart1();
});
document.addEventListener("livewire:initialized", () => {
    Livewire.hook("morph.updated", ({ el, component }) => {
        chart1();
    });
});
document.addEventListener("livewire:load", chart1);

window.addEventListener("livewire:updated", (event) => {
    chart1();
});
