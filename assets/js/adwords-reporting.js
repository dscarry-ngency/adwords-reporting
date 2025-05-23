document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts if we're on the dashboard
    if (document.getElementById('performanceChart')) {
        initializeCharts();
    }

    // Initialize campaign table if we're on the campaigns page
    if (document.querySelector('.table-container')) {
        loadCampaignData();
    }
});

function initializeCharts() {
    // Fetch data from server
    fetch(adwordsReporting.ajaxUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'adwords_reporting_get_campaign_data',
            nonce: adwordsReporting.nonce
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const performanceData = data.data.performance;
            createPerformanceChart(performanceData);
            createCampaignChart(performanceData);
        } else {
            console.error('Error loading data:', data.data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function createPerformanceChart(data) {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Clicks',
                    data: data.clicks,
                    borderColor: '#4CAF50',
                    tension: 0.1
                },
                {
                    label: 'Impressions',
                    data: data.impressions,
                    borderColor: '#2196F3',
                    tension: 0.1
                },
                {
                    label: 'Cost',
                    data: data.cost,
                    borderColor: '#FFC107',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
}

function createCampaignChart(data) {
    const ctx = document.getElementById('campaignChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Clicks',
                    data: data.clicks,
                    backgroundColor: '#4CAF50'
                },
                {
                    label: 'Impressions',
                    data: data.impressions,
                    backgroundColor: '#2196F3'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
}

function loadCampaignData() {
    fetch(adwordsReporting.ajaxUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'adwords_reporting_get_campaign_data',
            nonce: adwordsReporting.nonce
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const campaigns = data.data.campaigns;
            const tbody = document.querySelector('.table tbody');
            tbody.innerHTML = campaigns.map(campaign => `
                <tr>
                    <td>${campaign.name}</td>
                    <td>${campaign.status}</td>
                    <td>${campaign.budget}</td>
                    <td>${campaign.clicks}</td>
                    <td>${campaign.impressions}</td>
                    <td>${campaign.cost}</td>
                </tr>
            `).join('');
        } else {
            console.error('Error loading data:', data.data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
} 