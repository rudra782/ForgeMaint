document.addEventListener('DOMContentLoaded', function () {
    const healthCtx = document.getElementById('healthChart').getContext('2d');
    const healthChart = new Chart(healthCtx, {
        type: 'bar',
        data: {
            labels: ['Pumps', 'Conveyors', 'HVAC', 'Generators', 'Compressors', 'Motors'],
            datasets: [
                {
                    label: 'Healthy',
                    data: [18, 12, 8, 15, 10, 14],
                    backgroundColor: '#2ecc71',
                    borderColor: '#27ae60',
                    borderWidth: 1
                },
                {
                    label: 'Warning',
                    data: [3, 5, 4, 2, 6, 3],
                    backgroundColor: '#f39c12',
                    borderColor: '#e67e22',
                    borderWidth: 1
                },
                {
                    label: 'Critical',
                    data: [1, 2, 3, 0, 2, 1],
                    backgroundColor: '#e74c3c',
                    borderColor: '#c0392b',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true }
            }
        }
    });

    const failureCtx = document.getElementById('failureChart').getContext('2d');
    const failureChart = new Chart(failureCtx, {
        type: 'doughnut',
        data: {
            labels: ['Low Risk', 'Medium Risk', 'High Risk'],
            datasets: [{
                data: [65, 25, 10],
                backgroundColor: ['#2ecc71', '#f39c12', '#e74c3c'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    setInterval(() => {
        healthChart.data.datasets.forEach(dataset => {
            dataset.data = dataset.data.map(value => Math.max(0, value + Math.floor(Math.random() * 3) - 1));
        });

        let low = Math.max(50, Math.floor(Math.random() * 70));
        let med = Math.max(10, Math.floor(Math.random() * 30));
        let high = Math.max(5, Math.floor(Math.random() * 15));
        let total = low + med + high;
        failureChart.data.datasets[0].data = [
            Math.round((low / total) * 100),
            Math.round((med / total) * 100),
            Math.round((high / total) * 100)
        ];

        healthChart.update();
        failureChart.update();
    }, 5000);
});