<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Personal Notes System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .metric-card.success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .metric-card.warning {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        .metric-card.danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <select id="timeRange" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="7">Last 7 days</option>
                            <option value="30">Last 30 days</option>
                            <option value="90">Last 90 days</option>
                        </select>
                        <a href="/dashboard" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Overview Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="metric-card success rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm opacity-90">Total Actions</p>
                            <p class="text-3xl font-bold" id="totalActions">0</p>
                        </div>
                        <div class="text-4xl opacity-75">📊</div>
                    </div>
                </div>
                
                <div class="metric-card warning rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm opacity-90">Active Users</p>
                            <p class="text-3xl font-bold" id="activeUsers">0</p>
                        </div>
                        <div class="text-4xl opacity-75">👥</div>
                    </div>
                </div>
                
                <div class="metric-card danger rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm opacity-90">Avg Response Time</p>
                            <p class="text-3xl font-bold" id="avgResponseTime">0ms</p>
                        </div>
                        <div class="text-4xl opacity-75">⚡</div>
                    </div>
                </div>
                
                <div class="metric-card rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm opacity-90">Success Rate</p>
                            <p class="text-3xl font-bold" id="successRate">0%</p>
                        </div>
                        <div class="text-4xl opacity-75">✅</div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Usage Trends Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Usage Trends</h3>
                    <div class="chart-container">
                        <canvas id="usageTrendsChart"></canvas>
                    </div>
                </div>
                
                <!-- Performance Trends Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Trends</h3>
                    <div class="chart-container">
                        <canvas id="performanceTrendsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Detailed Analytics -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Popular Features -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Popular Features</h3>
                    <div id="popularFeatures" class="space-y-3">
                        <!-- Popular features will be loaded here -->
                    </div>
                </div>
                
                <!-- User Engagement -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">User Engagement</h3>
                    <div class="chart-container">
                        <canvas id="userEngagementChart"></canvas>
                    </div>
                </div>
                
                <!-- Error Analysis -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Error Analysis</h3>
                    <div id="errorAnalysis" class="space-y-3">
                        <!-- Error analysis will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Data Tables -->
            <div class="mt-8">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Detailed Analytics</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- API Performance -->
                            <div>
                                <h4 class="text-md font-semibold text-gray-900 mb-4">API Performance</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endpoint</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requests</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Time</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Success Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody id="apiPerformanceTable" class="bg-white divide-y divide-gray-200">
                                            <!-- API performance data will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Database Performance -->
                            <div>
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Database Performance</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Query</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Executions</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Time</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Time</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dbPerformanceTable" class="bg-white divide-y divide-gray-200">
                                            <!-- Database performance data will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let usageTrendsChart, performanceTrendsChart, userEngagementChart;
        
        // Initialize charts
        function initializeCharts() {
            // Usage Trends Chart
            const usageCtx = document.getElementById('usageTrendsChart').getContext('2d');
            usageTrendsChart = new Chart(usageCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Total Actions',
                        data: [],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Unique Users',
                        data: [],
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Performance Trends Chart
            const performanceCtx = document.getElementById('performanceTrendsChart').getContext('2d');
            performanceTrendsChart = new Chart(performanceCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Avg Response Time (ms)',
                        data: [],
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // User Engagement Chart
            const engagementCtx = document.getElementById('userEngagementChart').getContext('2d');
            userEngagementChart = new Chart(engagementCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            'rgb(59, 130, 246)',
                            'rgb(16, 185, 129)',
                            'rgb(245, 158, 11)',
                            'rgb(239, 68, 68)',
                            'rgb(139, 92, 246)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        // Load analytics data
        async function loadAnalyticsData() {
            const days = document.getElementById('timeRange').value;
            
            try {
                // Load performance data
                const performanceResponse = await fetch(`/analytics/performance?days=${days}`);
                const performanceData = await performanceResponse.json();
                
                if (performanceData.success) {
                    updatePerformanceMetrics(performanceData.data);
                    updatePerformanceCharts(performanceData.data);
                }
                
                // Load usage data
                const usageResponse = await fetch(`/analytics/usage?days=${days}`);
                const usageData = await usageResponse.json();
                
                if (usageData.success) {
                    updateUsageMetrics(usageData.data);
                    updateUsageCharts(usageData.data);
                }
                
                // Load user behavior data
                const behaviorResponse = await fetch(`/analytics/user-behavior?days=${days}`);
                const behaviorData = await behaviorResponse.json();
                
                if (behaviorData.success) {
                    updateBehaviorMetrics(behaviorData.data);
                }
                
            } catch (error) {
                console.error('Error loading analytics data:', error);
            }
        }
        
        // Update performance metrics
        function updatePerformanceMetrics(data) {
            const metrics = data.metrics;
            document.getElementById('totalActions').textContent = metrics.api.total_requests || 0;
            document.getElementById('avgResponseTime').textContent = Math.round(metrics.api.avg_response_time || 0) + 'ms';
            document.getElementById('successRate').textContent = (metrics.api.success_rate || 0) + '%';
        }
        
        // Update usage metrics
        function updateUsageMetrics(data) {
            const trends = data.trends;
            if (trends.length > 0) {
                const latest = trends[trends.length - 1];
                document.getElementById('activeUsers').textContent = latest.unique_users || 0;
            }
        }
        
        // Update performance charts
        function updatePerformanceCharts(data) {
            const trends = data.trends;
            if (trends.api_trends) {
                const labels = trends.api_trends.map(t => t.date);
                const responseTimes = trends.api_trends.map(t => t.avg_response_time);
                
                performanceTrendsChart.data.labels = labels;
                performanceTrendsChart.data.datasets[0].data = responseTimes;
                performanceTrendsChart.update();
            }
        }
        
        // Update usage charts
        function updateUsageCharts(data) {
            const trends = data.trends;
            if (trends.length > 0) {
                const labels = trends.map(t => t.date);
                const totalActions = trends.map(t => t.total_actions);
                const uniqueUsers = trends.map(t => t.unique_users);
                
                usageTrendsChart.data.labels = labels;
                usageTrendsChart.data.datasets[0].data = totalActions;
                usageTrendsChart.data.datasets[1].data = uniqueUsers;
                usageTrendsChart.update();
            }
            
            // Update popular features
            const popularFeatures = data.popular_features;
            const featuresContainer = document.getElementById('popularFeatures');
            featuresContainer.innerHTML = '';
            
            popularFeatures.forEach(feature => {
                const featureDiv = document.createElement('div');
                featureDiv.className = 'flex justify-between items-center p-3 bg-gray-50 rounded-lg';
                featureDiv.innerHTML = `
                    <span class="font-medium">${feature.feature}</span>
                    <span class="text-sm text-gray-600">${feature.usage_count} uses</span>
                `;
                featuresContainer.appendChild(featureDiv);
            });
        }
        
        // Update behavior metrics
        function updateBehaviorMetrics(data) {
            // Update user engagement chart
            const patterns = data.patterns;
            if (patterns.hourly_patterns) {
                const labels = patterns.hourly_patterns.map(p => p.hour + ':00');
                const usageCounts = patterns.hourly_patterns.map(p => p.usage_count);
                
                userEngagementChart.data.labels = labels;
                userEngagementChart.data.datasets[0].data = usageCounts;
                userEngagementChart.update();
            }
        }
        
        // Event listeners
        document.getElementById('timeRange').addEventListener('change', loadAnalyticsData);
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            loadAnalyticsData();
        });
    </script>
</body>
</html>
