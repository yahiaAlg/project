document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts if canvas elements exist
    if (document.getElementById('loansChart')) {
        initializeLoansChart();
    }
    
    if (document.getElementById('categoryChart')) {
        initializeCategoryChart();
    }
    
    if (document.getElementById('finesChart')) {
        initializeFinesChart();
    }
    
    // Add click event to stats cards for animation
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('click', function() {
            this.classList.add('pulse');
            setTimeout(() => {
                this.classList.remove('pulse');
            }, 2000);
        });
    });
});

function initializeLoansChart() {
    const ctx = document.getElementById('loansChart').getContext('2d');
    
    // Get data from the data attribute
    const chartData = JSON.parse(document.getElementById('loansChart').dataset.loans);
    
    // Process data for chart
    const labels = chartData.map(item => {
        const [year, month] = item.month.split('-');
        return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    });
    
    const data = chartData.map(item => item.loan_count);
    
    // Create chart
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Loans',
                data: data,
                backgroundColor: 'rgba(26, 115, 232, 0.2)',
                borderColor: 'rgba(26, 115, 232, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(26, 115, 232, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(26, 115, 232, 1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return `Loans: ${context.raw}`;
                        }
                    }
                }
            }
        }
    });
}

function initializeCategoryChart() {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    // Get data from the data attribute
    const chartData = JSON.parse(document.getElementById('categoryChart').dataset.categories);
    
    // Process data for chart
    const labels = chartData.map(item => item.category);
    const data = chartData.map(item => item.count);
    
    // Generate background colors
    const backgroundColors = [
        'rgba(26, 115, 232, 0.7)',
        'rgba(52, 168, 83, 0.7)',
        'rgba(251, 188, 4, 0.7)',
        'rgba(234, 67, 53, 0.7)',
        'rgba(103, 58, 183, 0.7)',
        'rgba(0, 188, 212, 0.7)',
        'rgba(255, 152, 0, 0.7)',
        'rgba(139, 195, 74, 0.7)'
    ];
    
    // Create chart
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColors,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        font: {
                            size: 12
                        },
                        padding: 15
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            const percent = Math.round((context.raw / context.dataset.data.reduce((a, b) => a + b, 0)) * 100);
                            return `${context.label}: ${context.raw} (${percent}%)`;
                        }
                    }
                }
            }
        }
    });
}

function initializeFinesChart() {
    const ctx = document.getElementById('finesChart').getContext('2d');
    
    // Get data from the data attribute
    const chartData = JSON.parse(document.getElementById('finesChart').dataset.fines);
    
    // Process data for chart
    const labels = chartData.map(item => {
        const [year, month] = item.month.split('-');
        return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    });
    
    const data = chartData.map(item => parseFloat(item.fine_amount));
    
    // Create chart
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Fines Collected',
                data: data,
                backgroundColor: 'rgba(234, 67, 53, 0.7)',
                borderColor: 'rgba(234, 67, 53, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return `Amount: $${context.raw.toFixed(2)}`;
                        }
                    }
                }
            }
        }
    });
}