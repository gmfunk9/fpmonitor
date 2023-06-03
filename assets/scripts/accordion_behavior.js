var acc = document.getElementsByClassName("accordion-title");
var charts = {};
for (let i = 0; i < acc.length; i++) {
    var button = acc[i].querySelector('.accordion-button');
    button.addEventListener("click", function () {
        this.classList.toggle("active");
        var panel = this.parentNode.nextElementSibling;
        panel.style.display = panel.style.display === "block" ? "none" : "block";
    });
}
document.querySelectorAll('.load-more').forEach(button => {
    button.addEventListener('click', loadMoreData);
});
document.querySelectorAll('.toggle-chart').forEach(button => {
  button.addEventListener('click', toggleChart);
});

function loadMoreData() {
  const tableName = this.dataset.table;
  let offset = parseInt(this.dataset.offset);
  fetch(`app/templates/load_more.php?table=${tableName}&offset=${offset}`)
    .then(response => response.json())
    .then(data => {
      this.parentNode.querySelector('table tbody').insertAdjacentHTML('beforeend', data.rowsHTML);
      offset += 5;
      this.dataset.offset = offset;
      if (!data.moreData) {
        this.textContent = 'Finished';
        this.disabled = true;
      }
    })
    .catch(error => {
      console.error('There was a problem with the fetch operation: ', error);
    });
}

function toggleChart() {
  const tableName = this.dataset.table;
  const chartContainer = document.getElementById(`${tableName}-chart-container`);
  const tableContainer = this.parentNode.querySelector('.table-container');
  const dateSelector = document.getElementById(`${tableName}-date-selector`);
  const granularitySelector = document.getElementById(`${tableName}-granularity-selector`);
  dateSelector.addEventListener('change', fetchChartData.bind(this));
  granularitySelector.addEventListener('change', fetchChartData.bind(this));
  if (chartContainer.style.display === 'none') {
    if (!charts[tableName]) {
      fetchChartData.call(this);
    }
    chartContainer.style.display = 'block';
    tableContainer.style.display = 'none';
    this.textContent = 'Toggle Table';
  } else {
    chartContainer.style.display = 'none';
    tableContainer.style.display = 'block';
    this.textContent = 'Toggle Chart';
  }
}

function fetchChartData() {
    const tableName = this.dataset.table;
    const dateSelector = document.getElementById(`${tableName}-date-selector`);
    const granularitySelector = document.getElementById(`${tableName}-granularity-selector`);
    if (!dateSelector || !granularitySelector) {
        console.error('Cannot find date or granularity selector.');
        return;
    }
    const dateRange = dateSelector.value;
    const granularity = granularitySelector.value;

    const currentDate = new Date().toISOString().slice(0, 10);

    fetch(`app/templates/load_chart_data.php?table=${tableName}&dateRange=${dateRange}&granularity=${granularity}`)
        .then(response => response.json())
        .then(data => {
            if (charts[tableName]) {
                charts[tableName].destroy();
            }
            let chartData = JSON.parse(data.chartData);
            let currentDate = chartData.labels[0];
            let fillData = {
                labels: [],
                ttfbData: [],
                totalData: []
            };
            chartData.labels.forEach((label, index) => {
                fillData.labels.push(label);
                fillData.ttfbData.push(chartData.ttfbData[index]);
                fillData.totalData.push(chartData.totalData[index]);
            });
            charts[tableName] = new Chart(document.getElementById(`${tableName}-chart`), {
                type: 'line',
                data: {
                    labels: fillData.labels,
                    datasets: [{
                        label: 'TTFB',
                        data: fillData.ttfbData,
                        borderColor: 'rgba(0, 123, 255, 1)',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    }, {
                        label: 'Total',
                        data: fillData.totalData,
                        borderColor: 'rgba(220, 53, 69, 1)',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Time'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Average'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        },
                        title: {
                            display: true,
                            text: 'Average TTFB and Total per Hour'
                        }
                    }
                }
            });
    });


    function getMaxAllowedDateRange(dateSelector) {
        const selectedOption = dateSelector.options[dateSelector.selectedIndex];
        const currentDate = new Date();
        const selectedValue = parseInt(selectedOption.value);
    
        let maxAllowedDate = new Date();
        maxAllowedDate.setDate(currentDate.getDate() - selectedValue );
        maxAllowedDate.setHours(0, 0, 0, 0);
    
        const maxAllowedDays = Math.ceil((currentDate - maxAllowedDate) / (1000 * 60 * 60 * 24));
        return Math.min(selectedValue, maxAllowedDays);
    }

}
