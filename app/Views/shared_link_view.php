<?php
$pageTitle = "Shared " . ucfirst($link['resource_type']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Shared <?= ucfirst($link['resource_type']) ?></h1>
                        <p class="text-gray-600 mt-1">Shared by <?= htmlspecialchars($link['created_by_name']) ?></p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button onclick="copyToClipboard(window.location.href)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-share mr-2"></i>Share Link
                        </button>
                        <button onclick="printContent()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-print mr-2"></i>Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="p-8">
                <?php if ($link['resource_type'] === 'note'): ?>
                    <div class="mb-6">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($resource['title']) ?></h2>
                        <div class="flex items-center space-x-4 text-sm text-gray-500 mb-6">
                            <span><i class="fas fa-calendar mr-1"></i>Created <?= date('M j, Y', strtotime($resource['created_at'])) ?></span>
                            <?php if ($resource['updated_at'] !== $resource['created_at']): ?>
                                <span><i class="fas fa-edit mr-1"></i>Updated <?= date('M j, Y', strtotime($resource['updated_at'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="prose max-w-none">
                        <?= nl2br(htmlspecialchars($resource['content'])) ?>
                    </div>
                <?php elseif ($link['resource_type'] === 'task'): ?>
                    <div class="mb-6">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($resource['title']) ?></h2>
                        <div class="flex items-center space-x-4 text-sm text-gray-500 mb-6">
                            <span><i class="fas fa-calendar mr-1"></i>Created <?= date('M j, Y', strtotime($resource['created_at'])) ?></span>
                            <?php if ($resource['due_date']): ?>
                                <span><i class="fas fa-clock mr-1"></i>Due <?= date('M j, Y', strtotime($resource['due_date'])) ?></span>
                            <?php endif; ?>
                            <span class="bg-<?= $resource['status'] === 'completed' ? 'green' : ($resource['priority'] === 'high' ? 'red' : 'blue') ?>-100 text-<?= $resource['status'] === 'completed' ? 'green' : ($resource['priority'] === 'high' ? 'red' : 'blue') ?>-800 px-2 py-1 rounded-full text-xs">
                                <?= ucfirst($resource['status']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if ($resource['description']): ?>
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                            <div class="prose max-w-none">
                                <?= nl2br(htmlspecialchars($resource['description'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Details</h3>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-700">Priority</dt>
                                    <dd class="text-sm text-gray-600"><?= ucfirst($resource['priority']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-700">Status</dt>
                                    <dd class="text-sm text-gray-600"><?= ucfirst($resource['status']) ?></dd>
                                </div>
                                <?php if ($resource['due_date']): ?>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-700">Due Date</dt>
                                        <dd class="text-sm text-gray-600"><?= date('M j, Y', strtotime($resource['due_date'])) ?></dd>
                                    </div>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>This content was shared via SecureNotes</p>
            <p>Access granted: <?= date('M j, Y g:i A') ?></p>
        </div>
    </div>

    <script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Link copied to clipboard');
        });
    }

    function printContent() {
        window.print();
    }

    // Add print styles
    const printStyles = `
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .bg-gray-50 { background: white !important; }
        }
    `;
    
    const styleSheet = document.createElement("style");
    styleSheet.type = "text/css";
    styleSheet.innerText = printStyles;
    document.head.appendChild(styleSheet);
    </script>
</body>
</html>
