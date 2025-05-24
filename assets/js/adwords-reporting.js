document.addEventListener('DOMContentLoaded', function() {
    try {
        // Check if we're on the dashboard page
        const performanceChartEl = document.getElementById('performanceChart');
        if (performanceChartEl) {
            initializeDashboard();
        }

        // Check if we're on the campaigns page
        const campaignsTable = document.querySelector('.table-container');
        if (campaignsTable) {
            initializeCampaigns();
        }
    } catch (error) {
        console.error('Error initializing Adwords Reporting:', error);
    }
});

function initializeDashboard() {
    // Initialize charts
    const performanceChart = new Chart(
        document.getElementById('performanceChart'),
        {
            type: 'line',
            data: {
                labels: [],
                datasets: []
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Campaign Performance'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        }
    );

    // Add loading state
    const chartContainer = document.querySelector('.chart-container');
    if (chartContainer) {
        const loadingEl = document.createElement('div');
        loadingEl.id = 'loadingIndicator';
        loadingEl.className = 'loading';
        loadingEl.textContent = 'Loading data...';
        chartContainer.appendChild(loadingEl);
    }

    // Load campaign data
    loadCampaignData()
        .then(data => {
            updateCharts(data);
            if (chartContainer) {
                const loadingEl = document.getElementById('loadingIndicator');
                if (loadingEl) {
                    loadingEl.remove();
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (chartContainer) {
                const loadingEl = document.getElementById('loadingIndicator');
                if (loadingEl) {
                    loadingEl.textContent = 'Error loading data. Please check your settings.';
                    loadingEl.className = 'error';
                }
            }
        });
}

function initializeCampaigns() {
    const tableBody = document.querySelector('.table tbody');
    const loadingRow = document.createElement('tr');
    loadingRow.innerHTML = '<td colspan="6" class="loading">Loading campaign data...</td>';
    tableBody.appendChild(loadingRow);

    loadCampaignData()
        .then(data => {
            updateCampaignTable(data);
            loadingRow.remove();
        })
        .catch(error => {
            console.error('Error:', error);
            loadingRow.innerHTML = '<td colspan="6" class="error">Error loading data. Please check your settings.</td>';
        });
}

function loadCampaignData() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    if (loadingIndicator) {
        loadingIndicator.style.display = 'block';
    }

    return fetch(adwordsReporting.ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'adwords_reporting_get_campaign_data',
            nonce: adwordsReporting.nonce
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(response => {
        if (!response.success) {
            throw new Error(response.data || 'Unknown error occurred');
        }
        return response.data;
    })
    .catch(error => {
        console.error('Error loading campaign data:', error);
        const errorMessage = document.getElementById('errorMessage');
        if (errorMessage) {
            errorMessage.textContent = 'Error loading data: ' + error.message;
            errorMessage.style.display = 'block';
        }
        throw error;
    })
    .finally(() => {
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
    });
}

function updateCharts(data) {
    const performanceChart = Chart.getChart('performanceChart');
    if (!performanceChart) {
        console.error('Performance chart not found');
        return;
    }

    try {
        // Use dates as labels
        performanceChart.data.labels = data.dates;
        
        // Aggregate impressions and clicks across all campaigns for each date
        const impressionsByDate = data.dates.map((date, dateIndex) => {
            return data.campaigns.reduce((sum, campaign) => {
                return sum + (campaign.impressions[dateIndex] || 0);
            }, 0);
        });

        const clicksByDate = data.dates.map((date, dateIndex) => {
            return data.campaigns.reduce((sum, campaign) => {
                return sum + (campaign.clicks[dateIndex] || 0);
            }, 0);
        });
        
        // Create datasets for each metric
        performanceChart.data.datasets = [
            {
                label: 'Impressions',
                data: impressionsByDate,
                borderColor: '#4CAF50',
                fill: false,
                tension: 0.1
            },
            {
                label: 'Clicks',
                data: clicksByDate,
                borderColor: '#2196F3',
                fill: false,
                tension: 0.1
            }
        ];
        
        // Update chart title to show date range
        performanceChart.options.plugins.title.text = 'Campaign Performance (Last 30 Days)';
        
        performanceChart.update();
    } catch (error) {
        console.error('Error updating charts:', error);
    }
}

function updateCampaignTable(data) {
    const tableBody = document.querySelector('.table tbody');
    if (!tableBody) return;

    // Add date range info to the table header
    const tableHeader = document.querySelector('.table thead tr');
    if (tableHeader) {
        const dateRangeCell = document.createElement('th');
        dateRangeCell.colSpan = 6;
        dateRangeCell.style.textAlign = 'center';
        dateRangeCell.style.fontSize = '0.9em';
        dateRangeCell.style.color = '#666';
        dateRangeCell.textContent = 'Data for the last 30 days';
        tableHeader.parentNode.insertBefore(dateRangeCell, tableHeader);
    }

    tableBody.innerHTML = data.campaigns.map(campaign => `
        <tr>
            <td>${campaign.name}</td>
            <td>${campaign.status || 'Active'}</td>
            <td>${campaign.budget || 'N/A'}</td>
            <td>${campaign.clicks ? campaign.clicks.reduce((a, b) => a + b, 0) : '0'}</td>
            <td>${campaign.impressions ? campaign.impressions.reduce((a, b) => a + b, 0) : '0'}</td>
            <td>${campaign.cost || '0'}</td>
        </tr>
    `).join('');
}

function getRandomColor() {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
} 