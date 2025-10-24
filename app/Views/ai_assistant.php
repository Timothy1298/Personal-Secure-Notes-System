<?php include __DIR__ . '/partials/header.php'; ?>

<!-- Main Content -->
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">AI Assistant</h1>
        <p class="text-gray-600 dark:text-gray-300 mt-2">Get smart suggestions, analyze content, and generate text with AI</p>
    </div>

    <!-- AI Assistant Interface -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Content Analysis -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Content Analysis</h2>
            
            <form id="analyzeForm">
                <div class="mb-4">
                    <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Content to Analyze</label>
                    <textarea id="content" name="content" rows="6" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Enter text to analyze..."></textarea>
                </div>
                
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-search mr-2"></i>Analyze Content
                </button>
            </form>
            
            <div id="analysisResults" class="mt-6 hidden">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Analysis Results</h3>
                <div id="analysisContent" class="space-y-3"></div>
            </div>
        </div>

        <!-- Smart Suggestions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Smart Suggestions</h2>
            
            <form id="suggestionsForm">
                <div class="mb-4">
                    <label for="suggestionContent" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Content for Suggestions</label>
                    <textarea id="suggestionContent" name="content" rows="6" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Enter text to get suggestions..."></textarea>
                </div>
                
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-lightbulb mr-2"></i>Get Suggestions
                </button>
            </form>
            
            <div id="suggestionsResults" class="mt-6 hidden">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Smart Suggestions</h3>
                <div id="suggestionsContent" class="space-y-3"></div>
            </div>
        </div>
    </div>

    <!-- Content Generation -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Content Generation</h2>
        
        <form id="generateForm">
            <div class="mb-4">
                <label for="prompt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prompt</label>
                <textarea id="prompt" name="prompt" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Enter a prompt for content generation..."></textarea>
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-magic mr-2"></i>Generate Content
                </button>
                <button type="button" id="summarizeBtn" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-compress mr-2"></i>Summarize
                </button>
                <button type="button" id="titleBtn" class="bg-indigo-500 hover:bg-indigo-600 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-heading mr-2"></i>Generate Title
                </button>
            </div>
        </form>
        
        <div id="generationResults" class="mt-6 hidden">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Generated Content</h3>
            <div id="generationContent" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg"></div>
        </div>
    </div>

    <!-- Recent Interactions -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Recent Interactions</h2>
        <div id="recentInteractions" class="space-y-3">
            <!-- Recent interactions will be loaded here -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Content Analysis Form
    document.getElementById('analyzeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const content = document.getElementById('content').value;
        
        if (!content.trim()) {
            alert('Please enter content to analyze');
            return;
        }
        
        fetch('/ai-assistant/analyze', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ content: content })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAnalysisResults(data.analysis);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while analyzing content');
        });
    });

    // Smart Suggestions Form
    document.getElementById('suggestionsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const content = document.getElementById('suggestionContent').value;
        
        if (!content.trim()) {
            alert('Please enter content for suggestions');
            return;
        }
        
        fetch('/ai-assistant/suggestions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ content: content })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySuggestionsResults(data.suggestions);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while getting suggestions');
        });
    });

    // Content Generation Form
    document.getElementById('generateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const prompt = document.getElementById('prompt').value;
        
        if (!prompt.trim()) {
            alert('Please enter a prompt');
            return;
        }
        
        generateContent(prompt, 'generate');
    });

    // Summarize Button
    document.getElementById('summarizeBtn').addEventListener('click', function() {
        const content = document.getElementById('content').value || document.getElementById('suggestionContent').value;
        
        if (!content.trim()) {
            alert('Please enter content to summarize');
            return;
        }
        
        generateContent(content, 'summarize');
    });

    // Title Generation Button
    document.getElementById('titleBtn').addEventListener('click', function() {
        const content = document.getElementById('content').value || document.getElementById('suggestionContent').value;
        
        if (!content.trim()) {
            alert('Please enter content to generate a title for');
            return;
        }
        
        generateContent(content, 'title');
    });

    function generateContent(prompt, type) {
        fetch('/ai-assistant/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ prompt: prompt, type: type })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayGenerationResults(data.content);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while generating content');
        });
    }

    function displayAnalysisResults(analysis) {
        const resultsDiv = document.getElementById('analysisResults');
        const contentDiv = document.getElementById('analysisContent');
        
        contentDiv.innerHTML = '';
        
        if (analysis.keywords && analysis.keywords.length > 0) {
            contentDiv.innerHTML += `
                <div class="p-3 bg-blue-50 dark:bg-blue-900 rounded-lg">
                    <h4 class="font-medium text-blue-900 dark:text-blue-100">Keywords</h4>
                    <p class="text-blue-700 dark:text-blue-300">${analysis.keywords.join(', ')}</p>
                </div>
            `;
        }
        
        if (analysis.sentiment) {
            contentDiv.innerHTML += `
                <div class="p-3 bg-green-50 dark:bg-green-900 rounded-lg">
                    <h4 class="font-medium text-green-900 dark:text-green-100">Sentiment</h4>
                    <p class="text-green-700 dark:text-green-300">${analysis.sentiment}</p>
                </div>
            `;
        }
        
        if (analysis.summary_suggestion) {
            contentDiv.innerHTML += `
                <div class="p-3 bg-purple-50 dark:bg-purple-900 rounded-lg">
                    <h4 class="font-medium text-purple-900 dark:text-purple-100">Summary Suggestion</h4>
                    <p class="text-purple-700 dark:text-purple-300">${analysis.summary_suggestion}</p>
                </div>
            `;
        }
        
        resultsDiv.classList.remove('hidden');
    }

    function displaySuggestionsResults(suggestions) {
        const resultsDiv = document.getElementById('suggestionsResults');
        const contentDiv = document.getElementById('suggestionsContent');
        
        contentDiv.innerHTML = '';
        
        suggestions.forEach(suggestion => {
            contentDiv.innerHTML += `
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h4 class="font-medium text-gray-900 dark:text-white">${suggestion.title}</h4>
                    <p class="text-gray-600 dark:text-gray-300">${Array.isArray(suggestion.data) ? suggestion.data.join(', ') : suggestion.data}</p>
                    <span class="text-xs text-gray-500">Confidence: ${Math.round(suggestion.confidence * 100)}%</span>
                </div>
            `;
        });
        
        resultsDiv.classList.remove('hidden');
    }

    function displayGenerationResults(content) {
        const resultsDiv = document.getElementById('generationResults');
        const contentDiv = document.getElementById('generationContent');
        
        contentDiv.textContent = content;
        resultsDiv.classList.remove('hidden');
    }

    // Load recent interactions
    loadRecentInteractions();
});

function loadRecentInteractions() {
    fetch('/ai-assistant/history?limit=5')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.history) {
                const container = document.getElementById('recentInteractions');
                container.innerHTML = '';
                
                data.history.forEach(interaction => {
                    container.innerHTML += `
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h4 class="font-medium text-gray-900 dark:text-white">${interaction.request_type}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-300">${interaction.prompt.substring(0, 100)}...</p>
                            <span class="text-xs text-gray-500">${new Date(interaction.created_at).toLocaleString()}</span>
                        </div>
                    `;
                });
            }
        })
        .catch(error => {
            console.error('Error loading recent interactions:', error);
        });
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>