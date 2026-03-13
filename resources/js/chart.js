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
                width: 1800,
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
                                fontSize: "13px",
                                fontWeight: 900,
                            },
                        },
                    },
                },
            },
            stroke: {
                width: 1,
            },
            title: {
                text: "Consentimientos por parques",
            },
            grid: {
                padding: { left: 0, right: 20, bottom: 0, top: 0 },
            },
            legend: {
                position: "bottom",
                horizontalAlign: "center",
                inverseOrder: false,
                tooltipHoverFormatter: undefined,
                height: 60,
                fontSize: "20 px",
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
}

document.addEventListener("DOMContentLoaded", chart1);

document.addEventListener("livewire:navigated", chart1);

document.addEventListener("livewire:load", chart1);

window.addEventListener("livewire:updated", (event) => {
    chart1();
});
