<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Management - Personal Notes System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .tab-button {
            transition: all 0.3s ease;
        }
        .tab-button.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .status-healthy { color: #10b981; }
        .status-warning { color: #f59e0b; }
        .status-critical { color: #ef4444; }
        .priority-critical { background-color: #fee2e2; border-color: #fca5a5; }
        .priority-high { background-color: #fef3c7; border-color: #fcd34d; }
        .priority-medium { background-color: #dbeafe; border-color: #93c5fd; }
        .priority-low { background-color: #f3f4f6; border-color: #d1d5db; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">Database Management</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/dashboard" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6">
                        <button class="tab-button active py-4 px-1 border-b-2 border-transparent font-medium text-sm" data-tab="overview">
                            Overview
                        </button>
                        <button class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm" data-tab="performance">
                            Performance
                        </button>
                        <button class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm" data-tab="optimization">
                            Optimization
                        </button>
                        <button class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm" data-tab="replication">
                            Replication
                        </button>
                        <button class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm" data-tab="queries">
                            Query Analysis
                        </button>
                    </nav>
                </div>

                <!-- Overview Tab -->
                <div id="overview" class="tab-content active p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Database Health -->
                        <div class="lg:col-span-2">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Database Health</h3>
                            <div id="healthChecks" class="space-y-4">
                                <!-- Health checks will be loaded here -->
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h3>
                            <div class="space-y-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Total Tables</span>
                                        <span id="totalTables" class="text-sm text-gray-900">-</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Database Size</span>
                                        <span id="databaseSize" class="text-sm text-gray-900">-</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Active Connections</span>
                                        <span id="activeConnections" class="text-sm text-gray-900">-</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Buffer Pool Hit Ratio</span>
                                        <span id="bufferPoolHitRatio" class="text-sm text-gray-900">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Connection Pool Stats -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Connection Pool</h3>
                        <div id="connectionPoolStats" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Connection pool stats will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Performance Tab -->
                <div id="performance" class="tab-content p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Performance Metrics -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Metrics</h3>
                            <div class="bg-white border rounded-lg p-4">
                                <canvas id="performanceChart" width="400" height="200"></canvas>
                            </div>
                        </div>

                        <!-- Table Sizes -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Table Sizes</h3>
                            <div id="tableSizes" class="space-y-2">
                                <!-- Table sizes will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- Slow Queries -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Slow Queries</h3>
                        <div id="slowQueries" class="bg-white border rounded-lg overflow-hidden">
                            <!-- Slow queries will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Optimization Tab -->
                <div id="optimization" class="tab-content p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Optimization Recommendations -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Optimization Recommendations</h3>
                            <div id="optimizationRecommendations" class="space-y-4">
                                <!-- Recommendations will be loaded here -->
                            </div>
                        </div>

                        <!-- Missing Indexes -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Missing Indexes</h3>
                            <div id="missingIndexes" class="space-y-2">
                                <!-- Missing indexes will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- Unused Indexes -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Unused Indexes</h3>
                        <div id="unusedIndexes" class="space-y-2">
                            <!-- Unused indexes will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Replication Tab -->
                <div id="replication" class="tab-content p-6">
                    <div id="replicationStatus">
                        <div class="text-center py-8">
                            <p class="text-gray-500">Replication not configured</p>
                        </div>
                    </div>
                </div>

                <!-- Query Analysis Tab -->
                <div id="queries" class="tab-content p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Query Builder -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Query Builder</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">SQL Query</label>
                                    <textarea id="customQuery" rows="8" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="SELECT * FROM notes WHERE user_id = ?"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Parameters (JSON)</label>
                                    <textarea id="queryParams" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder='["1"]'></textarea>
                                </div>
                                <button id="executeQuery" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                                    Execute Query
                                </button>
                            </div>
                        </div>

                        <!-- Query Results -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Query Results</h3>
                            <div id="queryResults" class="bg-white border rounded-lg p-4 min-h-64">
                                <p class="text-gray-500 text-center">Execute a query to see results</p>
                            </div>
                        </div>
                    </div>

                    <!-- Query Performance -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Query Performance</h3>
                        <div id="queryPerformance" class="bg-white border rounded-lg p-4">
                            <p class="text-gray-500 text-center">Query performance metrics will appear here</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.dataset.tab;
                
                // Update button states
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                // Update content visibility
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');
                
                // Load data for specific tabs
                if (tabId === 'performance') {
                    loadPerformanceData();
                } else if (tabId === 'optimization') {
                    loadOptimizationData();
                } else if (tabId === 'replication') {
                    loadReplicationData();
                }
            });
        });

        // Load overview data
        async function loadOverviewData() {
            try {
                // Load health checks
                const healthResponse = await fetch('/database/health');
                const healthData = await healthResponse.json();
                
                if (healthData.success) {
                    displayHealthChecks(healthData.data);
                }
                
                // Load connection pool stats
                const poolResponse = await fetch('/database/connection-pool-stats');
                const poolData = await poolResponse.json();
                
                if (poolData.success) {
                    displayConnectionPoolStats(poolData.data);
                }
                
            } catch (error) {
                console.error('Error loading overview data:', error);
            }
        }

        // Display health checks
        function displayHealthChecks(healthChecks) {
            const container = document.getElementById('healthChecks');
            container.innerHTML = '';
            
            healthChecks.forEach(check => {
                const statusClass = `status-${check.status}`;
                const statusIcon = check.status === 'healthy' ? '✓' : check.status === 'warning' ? '⚠' : '✗';
                
                const checkElement = document.createElement('div');
                checkElement.className = 'bg-white border rounded-lg p-4';
                checkElement.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="text-lg ${statusClass}">${statusIcon}</span>
                            <div>
                                <h4 class="font-medium text-gray-900">${check.check_name}</h4>
                                <p class="text-sm text-gray-600">${check.message}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-medium ${statusClass}">${check.status.toUpperCase()}</span>
                            ${check.value ? `<div class="text-xs text-gray-500">${check.value}${check.threshold ? ` / ${check.threshold}` : ''}</div>` : ''}
                        </div>
                    </div>
                `;
                container.appendChild(checkElement);
            });
        }

        // Display connection pool stats
        function displayConnectionPoolStats(data) {
            const container = document.getElementById('connectionPoolStats');
            const stats = data.stats;
            
            container.innerHTML = `
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-blue-900">Active Connections</div>
                    <div class="text-2xl font-bold text-blue-600">${stats.active_connections}</div>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-green-900">Idle Connections</div>
                    <div class="text-2xl font-bold text-green-600">${stats.idle_connections}</div>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-yellow-900">Max Connections</div>
                    <div class="text-2xl font-bold text-yellow-600">${stats.max_connections}</div>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-purple-900">Utilization</div>
                    <div class="text-2xl font-bold text-purple-600">${stats.utilization_percent}%</div>
                </div>
            `;
        }

        // Load performance data
        async function loadPerformanceData() {
            try {
                const response = await fetch('/database/analyze-performance');
                const data = await response.json();
                
                if (data.success) {
                    displayPerformanceData(data.data);
                }
            } catch (error) {
                console.error('Error loading performance data:', error);
            }
        }

        // Display performance data
        function displayPerformanceData(data) {
            // Display table sizes
            const tableSizesContainer = document.getElementById('tableSizes');
            tableSizesContainer.innerHTML = '';
            
            data.table_sizes.forEach(table => {
                const tableElement = document.createElement('div');
                tableElement.className = 'bg-gray-50 rounded p-3';
                tableElement.innerHTML = `
                    <div class="flex justify-between items-center">
                        <span class="font-medium">${table.TABLE_NAME}</span>
                        <span class="text-sm text-gray-600">${table.Size_MB} MB</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        ${table.TABLE_ROWS} rows | Data: ${table.Data_MB} MB | Index: ${table.Index_MB} MB
                    </div>
                `;
                tableSizesContainer.appendChild(tableElement);
            });
            
            // Display slow queries
            const slowQueriesContainer = document.getElementById('slowQueries');
            if (data.slow_queries.length > 0) {
                slowQueriesContainer.innerHTML = `
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Query</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Executions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max Time</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${data.slow_queries.map(query => `
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">${query.query}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">${query.executions}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">${query.avg_time_seconds}s</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">${query.max_time_seconds}s</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            } else {
                slowQueriesContainer.innerHTML = '<p class="p-4 text-gray-500 text-center">No slow queries found</p>';
            }
        }

        // Load optimization data
        async function loadOptimizationData() {
            try {
                const response = await fetch('/database/optimization-recommendations');
                const data = await response.json();
                
                if (data.success) {
                    displayOptimizationData(data.data);
                }
            } catch (error) {
                console.error('Error loading optimization data:', error);
            }
        }

        // Display optimization data
        function displayOptimizationData(recommendations) {
            const container = document.getElementById('optimizationRecommendations');
            container.innerHTML = '';
            
            recommendations.forEach(rec => {
                const priorityClass = `priority-${rec.priority}`;
                const recElement = document.createElement('div');
                recElement.className = `border rounded-lg p-4 ${priorityClass}`;
                recElement.innerHTML = `
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-medium text-gray-900">${rec.title}</h4>
                            <p class="text-sm text-gray-600 mt-1">${rec.description}</p>
                            ${rec.estimated_impact ? `<p class="text-xs text-gray-500 mt-1">Impact: ${rec.estimated_impact}</p>` : ''}
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">${rec.priority}</span>
                            <button class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700" onclick="updateRecommendationStatus(${rec.id}, 'completed')">
                                Mark Complete
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(recElement);
            });
        }

        // Load replication data
        async function loadReplicationData() {
            try {
                const response = await fetch('/database/replication-status');
                const data = await response.json();
                
                if (data.success) {
                    displayReplicationData(data.data);
                } else {
                    document.getElementById('replicationStatus').innerHTML = `
                        <div class="text-center py-8">
                            <p class="text-gray-500">${data.message}</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading replication data:', error);
            }
        }

        // Display replication data
        function displayReplicationData(data) {
            const container = document.getElementById('replicationStatus');
            container.innerHTML = `
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Master Status</h3>
                        <div class="bg-white border rounded-lg p-4">
                            <pre class="text-sm">${JSON.stringify(data.master, null, 2)}</pre>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Slave Status</h3>
                        <div class="bg-white border rounded-lg p-4">
                            <pre class="text-sm">${JSON.stringify(data.slaves, null, 2)}</pre>
                        </div>
                    </div>
                </div>
            `;
        }

        // Execute custom query
        document.getElementById('executeQuery').addEventListener('click', async () => {
            const query = document.getElementById('customQuery').value;
            const paramsText = document.getElementById('queryParams').value;
            
            if (!query.trim()) {
                alert('Please enter a query');
                return;
            }
            
            let params = [];
            if (paramsText.trim()) {
                try {
                    params = JSON.parse(paramsText);
                } catch (e) {
                    alert('Invalid JSON in parameters');
                    return;
                }
            }
            
            try {
                const response = await fetch('/database/execute-query', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        query: query,
                        params: params
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayQueryResults(data.data, data.execution_time_ms, data.rows_returned);
                } else {
                    alert('Query failed: ' + data.error);
                }
            } catch (error) {
                alert('Error executing query: ' + error.message);
            }
        });

        // Display query results
        function displayQueryResults(results, executionTime, rowsReturned) {
            const resultsContainer = document.getElementById('queryResults');
            const performanceContainer = document.getElementById('queryPerformance');
            
            if (results.length === 0) {
                resultsContainer.innerHTML = '<p class="text-gray-500 text-center">No results returned</p>';
            } else {
                const headers = Object.keys(results[0]);
                const tableHTML = `
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                ${headers.map(header => `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">${header}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${results.slice(0, 100).map(row => `
                                <tr>
                                    ${headers.map(header => `<td class="px-6 py-4 text-sm text-gray-900">${row[header] || ''}</td>`).join('')}
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    ${results.length > 100 ? `<p class="text-sm text-gray-500 mt-2">Showing first 100 of ${results.length} results</p>` : ''}
                `;
                resultsContainer.innerHTML = tableHTML;
            }
            
            performanceContainer.innerHTML = `
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">${executionTime}ms</div>
                        <div class="text-sm text-gray-600">Execution Time</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">${rowsReturned}</div>
                        <div class="text-sm text-gray-600">Rows Returned</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">${(rowsReturned / (executionTime / 1000)).toFixed(0)}</div>
                        <div class="text-sm text-gray-600">Rows/Second</div>
                    </div>
                </div>
            `;
        }

        // Update recommendation status
        async function updateRecommendationStatus(recommendationId, status) {
            try {
                const response = await fetch('/database/update-recommendation-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        recommendation_id: recommendationId,
                        status: status
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadOptimizationData(); // Reload optimization data
                } else {
                    alert('Failed to update recommendation status');
                }
            } catch (error) {
                alert('Error updating recommendation status: ' + error.message);
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadOverviewData();
        });
    </script>
</body>
</html>
