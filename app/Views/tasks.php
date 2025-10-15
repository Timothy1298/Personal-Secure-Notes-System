<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Task Management | SecureNote Pro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Suppress Tailwind production warning for development
    tailwind.config = {
      theme: {
        extend: {}
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    * {
      font-family: 'Inter', sans-serif;
    }
    
    .glassmorphism {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .task-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      transform-style: preserve-3d;
      height: 100%;
      min-height: 320px;
      overflow: visible;
      display: flex;
      flex-direction: column;
      position: relative;
      z-index: 1;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .task-card:hover {
      transform: translateY(-8px) rotateX(5deg);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(59, 130, 246, 0.1);
      z-index: 10;
    }
    
    .task-card.menu-open {
      z-index: 1000;
      transform: translateY(-4px);
      box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
      border: 2px solid rgba(59, 130, 246, 0.3);
    }
    
    .task-menu {
      position: fixed !important;
      z-index: 10000 !important;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
      border: 1px solid rgba(0, 0, 0, 0.1) !important;
      backdrop-filter: blur(10px) !important;
      background: rgba(255, 255, 255, 0.98) !important;
      min-width: 200px !important;
      max-width: 250px !important;
      border-radius: 12px !important;
    }
    
    .menu-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      z-index: 9999;
      background: rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(2px);
      opacity: 0;
      visibility: hidden;
      transition: all 0.2s ease;
    }
    
    .menu-overlay.active {
      opacity: 1;
      visibility: visible;
    }
    
    .task-actions {
      position: relative;
      z-index: 100;
      margin-top: auto;
    }
    
    .kanban-column {
      min-height: 500px;
      max-height: 80vh;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      display: flex;
      flex-direction: column;
      border-radius: 1rem;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      transition: all 0.3s ease;
    }
    
    .kanban-column:hover {
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      transform: translateY(-2px);
    }
    
    .kanban-column-header {
      flex-shrink: 0;
      margin-bottom: 1rem;
      padding: 1rem 1rem 0 1rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .kanban-column-header h3 {
      font-weight: 600;
      color: #374151;
      margin: 0;
    }
    
    .kanban-column-header .count-badge {
      font-size: 0.75rem;
      font-weight: 500;
      padding: 0.25rem 0.5rem;
      border-radius: 9999px;
    }
    
    .kanban-column-content {
      flex: 1;
      overflow-y: auto;
      min-height: 0;
      padding: 0 1rem 1rem 1rem;
      margin-bottom: 1rem;
    }
    
    .kanban-column-content::-webkit-scrollbar {
      width: 6px;
    }
    
    .kanban-column-content::-webkit-scrollbar-track {
      background: rgba(0, 0, 0, 0.1);
      border-radius: 3px;
    }
    
    .kanban-column-content::-webkit-scrollbar-thumb {
      background: rgba(0, 0, 0, 0.3);
      border-radius: 3px;
    }
    
    .kanban-column-content::-webkit-scrollbar-thumb:hover {
      background: rgba(0, 0, 0, 0.5);
    }
    
    /* Empty state styling */
    .kanban-column-content:empty::after {
      content: "No tasks in this column";
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100px;
      color: #9ca3af;
      font-size: 0.875rem;
      font-style: italic;
    }
    
    /* Drag and drop styling */
    .kanban-column.drag-over {
      background: rgba(59, 130, 246, 0.1);
      border: 2px dashed #3b82f6;
    }
    
    .kanban-column.drag-over .kanban-column-content::after {
      content: "Drop task here";
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100px;
      color: #3b82f6;
      font-size: 0.875rem;
      font-weight: 500;
    }
    
    /* Ensure equal height for all kanban columns */
    .kanban-view-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1.5rem;
      padding: 1rem;
      align-items: start;
      min-height: 600px;
    }
    
    /* Kanban view container */
    #kanbanView {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
      border-radius: 1rem;
      padding: 1rem;
      margin: 1rem 0;
    }
    
    @media (max-width: 1024px) {
      .kanban-view-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
      }
    }
    
    @media (max-width: 768px) {
      .kanban-view-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 0.5rem;
      }
    }
    
    .priority-urgent {
      border-left: 4px solid #ef4444;
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(255, 255, 255, 0.95) 100%);
    }
    
    .priority-high {
      border-left: 4px solid #f97316;
      background: linear-gradient(135deg, rgba(249, 115, 22, 0.1) 0%, rgba(255, 255, 255, 0.95) 100%);
    }
    
    .priority-medium {
      border-left: 4px solid #3b82f6;
      background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(255, 255, 255, 0.95) 100%);
    }
    
    .priority-low {
      border-left: 4px solid #10b981;
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(255, 255, 255, 0.95) 100%);
    }
    
    .floating-animation {
      animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }
    
    .pulse-glow {
      animation: pulse-glow 2s ease-in-out infinite alternate;
    }
    
    @keyframes pulse-glow {
      from { box-shadow: 0 0 20px rgba(59, 130, 246, 0.4); }
      to { box-shadow: 0 0 30px rgba(59, 130, 246, 0.8); }
    }
    
    .btn-3d {
      position: relative;
      transform-style: preserve-3d;
      transition: all 0.3s ease;
    }
    
    .btn-3d:hover {
      transform: translateY(-2px) rotateX(5deg);
      box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
    }
    
    .btn-3d:active {
      transform: translateY(0px) rotateX(2deg);
    }
    
    .progress-bar {
      background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
      border-radius: 9999px;
      transition: width 0.3s ease;
    }
    
    .drag-over {
      background: rgba(59, 130, 246, 0.1);
      border: 2px dashed #3b82f6;
    }
    
    .task-dragging {
      opacity: 0.5;
      transform: rotate(5deg);
    }
    
    .calendar-container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .modal-backdrop {
      backdrop-filter: blur(8px);
    }
    
    .modal-content {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .slide-in {
      animation: slideIn 0.8s ease-out;
    }
    
    @keyframes slideIn {
      from { 
        opacity: 0; 
        transform: translateY(30px) scale(0.95); 
      }
      to { 
        opacity: 1; 
        transform: translateY(0) scale(1); 
      }
    }
    
    .toast {
      position: fixed;
      top: 1rem;
      right: 1rem;
      z-index: 9999;
      padding: 1rem 1.5rem;
      border-radius: 0.75rem;
      color: white;
      font-weight: 500;
      transform: translateX(100%);
      transition: transform 0.3s ease;
    }
    
    .toast.show {
      transform: translateX(0);
    }
    
    .toast.success {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .toast.error {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    
    .toast.info {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }
    
    .view-toggle {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .view-toggle.active {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      color: white;
    }
    
    .loading-spinner {
      border: 3px solid rgba(59, 130, 246, 0.3);
      border-top: 3px solid #3b82f6;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .tasks-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 1.5rem;
      padding: 1rem 0;
    }
    
    @media (min-width: 640px) {
      .tasks-grid {
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 1.75rem;
      }
    }
    
    @media (min-width: 768px) {
      .tasks-grid {
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 2rem;
      }
    }
    
    @media (min-width: 1024px) {
      .tasks-grid {
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 2rem;
      }
    }
    
    @media (min-width: 1280px) {
      .tasks-grid {
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 2.25rem;
      }
    }
    
    @media (min-width: 1536px) {
      .tasks-grid {
        grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
        gap: 2.5rem;
      }
    }
    
    .task-item {
      width: 100%;
      height: 100%;
    }
    
    .tag-badge {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 500;
      display: inline-block;
      margin: 0.125rem;
      transition: all 0.2s ease;
    }
    
    .tag-badge:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .active-filter {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      color: white;
    }
    
    /* Enhanced Task Card Styling */
    .task-card-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      margin-bottom: 1rem;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .task-card-title {
      font-size: 1.125rem;
      font-weight: 600;
      color: #1f2937;
      line-height: 1.4;
      margin-bottom: 0.5rem;
      cursor: pointer;
      transition: color 0.2s ease;
    }
    
    .task-card-title:hover {
      color: #3b82f6;
    }
    
    .task-card-meta {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.75rem;
      color: #6b7280;
    }
    
    .task-card-content {
      flex: 1;
      margin-bottom: 1rem;
      overflow: hidden;
    }
    
    .task-card-description {
      color: #4b5563;
      font-size: 0.875rem;
      line-height: 1.5;
      display: -webkit-box;
      -webkit-line-clamp: 4;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .task-card-footer {
      margin-top: auto;
      padding-top: 1rem;
      border-top: 1px solid rgba(0, 0, 0, 0.05);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    .task-card-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.25rem;
      margin-bottom: 0.75rem;
    }
    
    .task-card-actions {
      position: relative;
    }
    
    .task-card-menu {
      position: absolute;
      right: 0;
      top: 100%;
      margin-top: 0.5rem;
      width: 12rem;
      background: white;
      border-radius: 0.75rem;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      border: 1px solid rgba(0, 0, 0, 0.05);
      padding: 0.5rem 0;
      z-index: 100;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
      transition: all 0.2s ease;
    }
    
    .task-card-menu.show {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }
    
    .task-card-menu-item {
      display: flex;
      align-items: center;
      width: 100%;
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
      color: #374151;
      transition: background-color 0.2s ease;
      border: none;
      background: none;
      text-align: left;
      cursor: pointer;
    }
    
    .task-card-menu-item:hover {
      background-color: #f3f4f6;
    }
    
    .task-card-menu-item i {
      width: 1rem;
      margin-right: 0.75rem;
    }
    
    .task-card-status-badge {
      display: inline-flex;
      align-items: center;
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 500;
      text-transform: capitalize;
    }
    
    .task-card-status-pending {
      background-color: #fef3c7;
      color: #92400e;
    }
    
    .task-card-status-in_progress {
      background-color: #dbeafe;
      color: #1e40af;
    }
    
    .task-card-status-completed {
      background-color: #d1fae5;
      color: #065f46;
    }
    
    .task-card-priority-badge {
      display: inline-flex;
      align-items: center;
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 500;
      text-transform: capitalize;
    }
    
    .task-card-priority-urgent {
      background-color: #fee2e2;
      color: #991b1b;
    }
    
    .task-card-priority-high {
      background-color: #fed7aa;
      color: #9a3412;
    }
    
    .task-card-priority-medium {
      background-color: #dbeafe;
      color: #1e40af;
    }
    
    .task-card-priority-low {
      background-color: #d1fae5;
      color: #065f46;
    }
    
    .task-card-date {
      font-size: 0.75rem;
      color: #6b7280;
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
    
    .task-card-category {
      font-size: 0.75rem;
      color: #6b7280;
      text-transform: capitalize;
    }
    
    /* Priority-based card styling */
    .task-card.priority-urgent {
      border-left: 4px solid #dc2626;
    }
    
    .task-card.priority-high {
      border-left: 4px solid #ea580c;
    }
    
    .task-card.priority-medium {
      border-left: 4px solid #2563eb;
    }
    
    .task-card.priority-low {
      border-left: 4px solid #16a34a;
    }
    
    /* Status-based card styling */
    .task-card[data-task-status="completed"] {
      opacity: 0.8;
      background: rgba(240, 253, 244, 0.95);
    }
    
    .task-card[data-task-status="completed"] .task-card-title {
      text-decoration: line-through;
      color: #6b7280;
    }
    
    .task-card[data-task-status="in_progress"] {
      background: rgba(239, 246, 255, 0.95);
    }
    
    .task-card[data-task-status="pending"] {
      background: rgba(255, 251, 235, 0.95);
    }
    
    /* Calendar Styling */
    .calendar-container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    #calendar {
      background: transparent;
    }
    
    /* FullCalendar Custom Styling */
    .fc {
      font-family: 'Inter', sans-serif;
    }
    
    .fc-header-toolbar {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 16px;
      margin-bottom: 20px;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .fc-button {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      border-radius: 8px;
      color: white;
      font-weight: 500;
      padding: 8px 16px;
      transition: all 0.3s ease;
    }
    
    .fc-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .fc-button:focus {
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
    }
    
    .fc-button-primary:not(:disabled):active {
      background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    }
    
    .fc-today-button {
      background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    }
    
    .fc-daygrid-day {
      background: rgba(255, 255, 255, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s ease;
    }
    
    .fc-daygrid-day:hover {
      background: rgba(255, 255, 255, 0.8);
      transform: scale(1.02);
    }
    
    .fc-daygrid-day.fc-day-today {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
      border: 2px solid rgba(102, 126, 234, 0.3);
    }
    
    .fc-daygrid-day-number {
      color: #4a5568;
      font-weight: 500;
      padding: 8px;
    }
    
    .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
      color: #667eea;
      font-weight: 600;
    }
    
    .fc-event {
      border-radius: 6px;
      border: none;
      padding: 2px 6px;
      font-size: 12px;
      font-weight: 500;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    
    .fc-event:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .fc-daygrid-event {
      margin: 1px 0;
    }
    
    .fc-col-header-cell {
      background: rgba(255, 255, 255, 0.8);
      color: #4a5568;
      font-weight: 600;
      padding: 12px 8px;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .fc-daygrid-day-frame {
      min-height: 100px;
    }
    
    .fc-scrollgrid {
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 12px;
      overflow: hidden;
    }
    
    .fc-scrollgrid-sync-table {
      border-radius: 12px;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
  
  <!-- Tasks Loader System -->
  <div id="tasksLoader" class="fixed inset-0 bg-black bg-opacity-30 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen">
      <div class="text-center text-white">
        <div class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p id="tasksLoaderMessage">Loading...</p>
      </div>
    </div>
  </div>

  <!-- Tasks Toast Container -->
  <div id="tasksToastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

  <!-- Main Container -->
  <div class="flex h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <?php 
        $page_title = "Task Management";
        include __DIR__ . '/partials/navbar.php'; 
      ?>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6">
        
        <!-- Header Section -->
        <div class="mb-8">
          <div class="mb-4">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Task Management</h1>
            <p class="text-gray-600">Organize and track your tasks with advanced features</p>
          </div>
          
          <!-- Add Task Button -->
          <div class="flex justify-end mb-6">
          <button onclick="openAddTaskModal()" class="btn-3d px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
            <i class="fas fa-plus mr-2"></i>Add Task
          </button>
          </div>
        </div>

        <!-- Search and Filters -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <div class="flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
              <div class="relative">
                <input type="text" id="searchInput" placeholder="Search tasks by title or description..." 
                       class="w-full pl-12 pr-20 py-3 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-300">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <button onclick="openAdvancedSearchModal()" class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600 transition-colors">
                  <i class="fas fa-sliders-h"></i>
                </button>
              </div>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
              <!-- Status Filter -->
              <div class="flex bg-white bg-opacity-50 rounded-xl p-1">
                <button data-filter="status" data-value="all" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 active-filter">All</button>
                <button data-filter="status" data-value="pending" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">Pending</button>
                <button data-filter="status" data-value="in_progress" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">In Progress</button>
                <button data-filter="status" data-value="completed" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">Completed</button>
              </div>
              
              <!-- Priority Filter -->
              <select id="priorityFilter" onchange="filterTasks()" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="all">All Priorities</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
              </select>
              
              <!-- Category Filter -->
              <select id="categoryFilter" onchange="filterTasks()" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="all">All Categories</option>
                <option value="work">Work</option>
                <option value="personal">Personal</option>
                <option value="health">Health</option>
                <option value="finance">Finance</option>
                <option value="education">Education</option>
              </select>
              
              <!-- Tag Filter -->
              <select id="tagFilter" onchange="filterTasks()" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="all">All Tags</option>
                <?php foreach ($tags as $tag): ?>
                  <option value="<?= htmlspecialchars($tag['id']) ?>"><?= htmlspecialchars($tag['name']) ?></option>
                <?php endforeach; ?>
              </select>
              
              <!-- Sort Options -->
              <select id="sortBy" onchange="sortTasks()" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="updated_desc">Recently Updated</option>
                <option value="created_desc">Recently Created</option>
                <option value="title_asc">Title A-Z</option>
                <option value="title_desc">Title Z-A</option>
                <option value="due_date_asc">Due Date (Earliest)</option>
                <option value="due_date_desc">Due Date (Latest)</option>
                <option value="priority">Priority</option>
              </select>
            </div>
          </div>
        </div>

        <!-- View Toggle and Quick Actions -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- View Toggle -->
            <div class="flex items-center gap-2">
              <span class="text-sm font-medium text-gray-700">View:</span>
              <div class="view-toggle flex rounded-xl p-1">
                <button onclick="switchView('kanban')" id="kanbanBtn" class="view-toggle active px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                  <i class="fas fa-columns mr-2"></i>Kanban
                </button>
                <button onclick="switchView('calendar')" id="calendarBtn" class="view-toggle px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                  <i class="fas fa-calendar mr-2"></i>Calendar
                </button>
                <button onclick="switchView('list')" id="listBtn" class="view-toggle px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                  <i class="fas fa-list mr-2"></i>List
                </button>
                <button onclick="switchView('grid')" id="gridBtn" class="view-toggle px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                  <i class="fas fa-th mr-2"></i>Grid
                </button>
              </div>
            </div>
            
            <!-- Bulk Actions (Hidden by default) -->
            <div id="bulkActions" class="hidden flex items-center gap-2">
              <span id="selectedCount" class="text-sm text-gray-600">0 selected</span>
              <button onclick="bulkComplete()" class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-check mr-1"></i>Complete
              </button>
              <button onclick="bulkDelete()" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-trash mr-1"></i>Delete
              </button>
              <button onclick="bulkArchive()" class="px-3 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                <i class="fas fa-archive mr-1"></i>Archive
              </button>
              <button onclick="bulkTag()" class="px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                <i class="fas fa-tags mr-1"></i>Tag
              </button>
              <button onclick="clearSelection()" class="px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-times mr-1"></i>Clear
              </button>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex items-center gap-2 flex-wrap">
              <div class="flex items-center gap-2">
                <button onclick="showTaskStatistics()" class="px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm">
                  <i class="fas fa-chart-bar mr-1"></i>Stats
                </button>
                <button onclick="openTemplatesModal()" class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                  <i class="fas fa-file-alt mr-1"></i>Templates
                </button>
                <button onclick="openExportModal()" class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                  <i class="fas fa-download mr-1"></i>Export
                </button>
              </div>
              <div class="flex items-center gap-2">
                <button onclick="showKeyboardShortcuts()" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                  <i class="fas fa-keyboard mr-1"></i>Shortcuts
                </button>
                <button onclick="showHelp()" class="px-3 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                  <i class="fas fa-question-circle mr-1"></i>Help
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Kanban View -->
        <div id="kanbanView" class="view-content">
          <div class="kanban-view-grid">
            <!-- Pending Column -->
            <div class="kanban-column">
              <div class="kanban-column-header">
                <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Pending</h3>
                  <span id="pendingCount" class="count-badge bg-gray-100 text-gray-600">0</span>
              </div>
              </div>
              <div id="pendingTasks" class="kanban-column-content space-y-3" ondrop="drop(event, 'pending')" ondragover="allowDrop(event)">
                <!-- Tasks will be loaded here -->
              </div>
            </div>

            <!-- In Progress Column -->
            <div class="kanban-column">
              <div class="kanban-column-header">
                <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">In Progress</h3>
                  <span id="inProgressCount" class="count-badge bg-blue-100 text-blue-600">0</span>
              </div>
              </div>
              <div id="inProgressTasks" class="kanban-column-content space-y-3" ondrop="drop(event, 'in_progress')" ondragover="allowDrop(event)">
                <!-- Tasks will be loaded here -->
              </div>
            </div>

            <!-- Completed Column -->
            <div class="kanban-column">
              <div class="kanban-column-header">
                <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Completed</h3>
                  <span id="completedCount" class="count-badge bg-green-100 text-green-600">0</span>
              </div>
              </div>
              <div id="completedTasks" class="kanban-column-content space-y-3" ondrop="drop(event, 'completed')" ondragover="allowDrop(event)">
                <!-- Tasks will be loaded here -->
              </div>
            </div>

            <!-- Overdue Column -->
            <div class="kanban-column">
              <div class="kanban-column-header">
                <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Overdue</h3>
                  <span id="overdueCount" class="count-badge bg-red-100 text-red-600">0</span>
              </div>
              </div>
              <div id="overdueTasks" class="kanban-column-content space-y-3" ondrop="drop(event, 'overdue')" ondragover="allowDrop(event)">
                <!-- Tasks will be loaded here -->
              </div>
            </div>
          </div>
        </div>

        <!-- Calendar View -->
        <div id="calendarView" class="view-content hidden">
          <div class="calendar-container rounded-2xl p-6">
            <div id="calendar"></div>
          </div>
        </div>

        <!-- List View -->
        <div id="listView" class="view-content hidden">
          <div class="glassmorphism rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-semibold text-gray-800">All Tasks</h3>
              <div class="flex items-center gap-3">
                <button onclick="sortTasks()" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium hover:bg-opacity-70 transition-all duration-200">
                  <i class="fas fa-sort mr-2"></i>Sort
                </button>
              </div>
            </div>
            <div id="taskList" class="space-y-4">
              <!-- Tasks will be loaded here -->
            </div>
          </div>
        </div>

        <!-- Grid View -->
        <div id="gridView" class="view-content hidden">
          <div class="tasks-grid" id="tasksGrid">
            <!-- Tasks will be loaded here -->
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Add/Edit Task Modal -->
  <div id="taskModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-4xl max-h-[90vh] overflow-hidden slide-in">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 id="modalTitle" class="text-2xl font-bold text-gray-800">Add New Task</h3>
          <button onclick="closeTaskModal()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
        <form id="taskForm" method="POST">
          <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">
          <input type="hidden" id="taskId" name="id">
          
          <!-- Task Title -->
          <div class="mb-6">
            <label for="taskTitle" class="block text-sm font-semibold text-gray-700 mb-2">Title</label>
            <input type="text" id="taskTitle" name="title" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                   placeholder="Enter task title...">
          </div>
          
          <!-- Task Description -->
          <div class="mb-6">
            <label for="taskDescription" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
            <textarea id="taskDescription" name="description" rows="4"
                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                      placeholder="Enter task description..."></textarea>
          </div>
          
          <!-- Task Options -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Priority -->
            <div>
              <label for="taskPriority" class="block text-sm font-semibold text-gray-700 mb-2">Priority</label>
              <select id="taskPriority" name="priority" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
            
            <!-- Category -->
            <div>
              <label for="taskCategory" class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
              <select id="taskCategory" name="category" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Category</option>
                <option value="work">Work</option>
                <option value="personal">Personal</option>
                <option value="health">Health</option>
                <option value="finance">Finance</option>
                <option value="education">Education</option>
              </select>
            </div>
            
            <!-- Due Date -->
            <div>
              <label for="taskDueDate" class="block text-sm font-semibold text-gray-700 mb-2">Due Date</label>
              <input type="datetime-local" id="taskDueDate" name="due_date"
                     class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
            </div>
          </div>
          
          <!-- Recurring Task -->
          <div class="mb-6">
            <div class="flex items-center gap-4">
              <label class="flex items-center">
                <input type="checkbox" id="isRecurring" name="is_recurring" class="mr-2">
                <span class="text-sm font-medium text-gray-700">Recurring Task</span>
              </label>
            </div>
            
            <div id="recurrenceOptions" class="mt-4 hidden">
              <label for="recurrencePattern" class="block text-sm font-semibold text-gray-700 mb-2">Recurrence Pattern</label>
              <select id="recurrencePattern" name="recurrence_pattern" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
              </select>
            </div>
          </div>
          
          <!-- Tags -->
          <div class="mb-6">
            <label for="taskTags" class="block text-sm font-semibold text-gray-700 mb-2">Tags</label>
            <select id="taskTags" name="tags[]" multiple class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
              <?php foreach ($tags as $tag): ?>
                <option value="<?= htmlspecialchars($tag['id']) ?>"><?= htmlspecialchars($tag['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </form>
      </div>
      
      <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
        <button onclick="closeTaskModal()" class="px-6 py-2 text-gray-600 hover:text-gray-800 transition-colors">
          Cancel
        </button>
        <button onclick="saveTask()" class="btn-3d px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
          <span id="saveButtonText">Save Task</span>
          <div id="saveSpinner" class="loading-spinner ml-2 hidden"></div>
        </button>
      </div>
    </div>
  </div>

  <!-- View Task Modal -->
  <div id="viewTaskModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-2xl max-h-[90vh] overflow-hidden slide-in">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 id="viewModalTitle" class="text-2xl font-bold text-gray-800">Task Details</h3>
          <button onclick="closeViewTaskModal()" class="p-2 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
        <div id="viewTaskContent">
          <!-- Task content will be populated here -->
        </div>
      </div>
      
      <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
        <button onclick="closeViewTaskModal()" class="px-6 py-2 text-gray-600 hover:text-gray-800 transition-colors">
          Close
        </button>
        <button onclick="editCurrentTask()" class="btn-3d px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
          <i class="fas fa-edit mr-2"></i>Edit Task
        </button>
      </div>
    </div>
  </div>

  <!-- Subtask Modal -->
  <div id="subtaskModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-2xl slide-in">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-800">Subtasks</h3>
          <button onclick="closeSubtaskModal()" class="p-2 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6">
        <div class="mb-4">
          <button onclick="openAddSubtaskModal()" class="btn-3d px-4 py-2 bg-blue-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
            <i class="fas fa-plus mr-2"></i>Add Subtask
          </button>
        </div>
        
        <div id="subtasksList" class="space-y-3">
          <!-- Subtasks will be loaded here -->
        </div>
      </div>
    </div>
  </div>

  <!-- Templates Modal -->
  <div id="templatesModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-4xl max-h-[90vh] overflow-hidden slide-in">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-2xl font-bold text-gray-800">Task Templates</h3>
          <button onclick="closeTemplatesModal()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- Project Task Template -->
          <div class="template-card bg-white rounded-xl p-4 border border-gray-200 hover:shadow-lg transition-shadow cursor-pointer" onclick="useTemplate('project')">
            <div class="flex items-center mb-3">
              <i class="fas fa-project-diagram text-blue-600 text-xl mr-3"></i>
              <h4 class="font-semibold text-gray-800">Project Task</h4>
            </div>
            <p class="text-sm text-gray-600 mb-3">Structured template for project-related tasks with milestones and deadlines.</p>
            <div class="text-xs text-gray-500">Includes: Title, Description, Priority, Due Date, Category</div>
          </div>
          
          <!-- Meeting Task Template -->
          <div class="template-card bg-white rounded-xl p-4 border border-gray-200 hover:shadow-lg transition-shadow cursor-pointer" onclick="useTemplate('meeting')">
            <div class="flex items-center mb-3">
              <i class="fas fa-users text-green-600 text-xl mr-3"></i>
              <h4 class="font-semibold text-gray-800">Meeting Task</h4>
            </div>
            <p class="text-sm text-gray-600 mb-3">Template for meeting preparation and follow-up tasks.</p>
            <div class="text-xs text-gray-500">Includes: Agenda, Attendees, Action Items, Follow-up</div>
          </div>
          
          <!-- Personal Task Template -->
          <div class="template-card bg-white rounded-xl p-4 border border-gray-200 hover:shadow-lg transition-shadow cursor-pointer" onclick="useTemplate('personal')">
            <div class="flex items-center mb-3">
              <i class="fas fa-user text-purple-600 text-xl mr-3"></i>
              <h4 class="font-semibold text-gray-800">Personal Task</h4>
            </div>
            <p class="text-sm text-gray-600 mb-3">Template for personal tasks and daily activities.</p>
            <div class="text-xs text-gray-500">Includes: Personal goals, health, finance, education</div>
          </div>
          
          <!-- Bug Fix Template -->
          <div class="template-card bg-white rounded-xl p-4 border border-gray-200 hover:shadow-lg transition-shadow cursor-pointer" onclick="useTemplate('bugfix')">
            <div class="flex items-center mb-3">
              <i class="fas fa-bug text-red-600 text-xl mr-3"></i>
              <h4 class="font-semibold text-gray-800">Bug Fix</h4>
            </div>
            <p class="text-sm text-gray-600 mb-3">Template for tracking and fixing software bugs.</p>
            <div class="text-xs text-gray-500">Includes: Bug description, steps to reproduce, solution</div>
          </div>
          
          <!-- Learning Task Template -->
          <div class="template-card bg-white rounded-xl p-4 border border-gray-200 hover:shadow-lg transition-shadow cursor-pointer" onclick="useTemplate('learning')">
            <div class="flex items-center mb-3">
              <i class="fas fa-graduation-cap text-orange-600 text-xl mr-3"></i>
              <h4 class="font-semibold text-gray-800">Learning Task</h4>
            </div>
            <p class="text-sm text-gray-600 mb-3">Template for educational and skill development tasks.</p>
            <div class="text-xs text-gray-500">Includes: Learning objectives, resources, milestones</div>
          </div>
          
          <!-- Blank Template -->
          <div class="template-card bg-white rounded-xl p-4 border border-gray-200 hover:shadow-lg transition-shadow cursor-pointer" onclick="useTemplate('blank')">
            <div class="flex items-center mb-3">
              <i class="fas fa-file-alt text-gray-600 text-xl mr-3"></i>
              <h4 class="font-semibold text-gray-800">Blank Task</h4>
            </div>
            <p class="text-sm text-gray-600 mb-3">Start with a clean slate and create your own structure.</p>
            <div class="text-xs text-gray-500">No predefined structure</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Export Modal -->
  <div id="exportModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-2xl slide-in">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-2xl font-bold text-gray-800">Export Tasks</h3>
          <button onclick="closeExportModal()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6">
        <div class="space-y-4">
          <!-- Export Format -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Export Format</label>
            <div class="grid grid-cols-2 gap-3">
              <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="radio" name="exportFormat" value="docx" class="mr-3" checked>
                <i class="fas fa-file-word text-blue-600 mr-2"></i>
                <span>Word Document (DOCX)</span>
              </label>
              <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="radio" name="exportFormat" value="pdf" class="mr-3">
                <i class="fas fa-file-pdf text-red-600 mr-2"></i>
                <span>PDF Document (Coming Soon)</span>
              </label>
              <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="radio" name="exportFormat" value="markdown" class="mr-3">
                <i class="fas fa-file-alt text-gray-600 mr-2"></i>
                <span>Markdown</span>
              </label>
              <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="radio" name="exportFormat" value="json" class="mr-3">
                <i class="fas fa-code text-green-600 mr-2"></i>
                <span>JSON Data</span>
              </label>
            </div>
          </div>
          
          <!-- Export Options -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Export Options</label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input type="checkbox" id="includeTags" class="mr-3" checked>
                <span>Include tags</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" id="includeMetadata" class="mr-3" checked>
                <span>Include creation date and metadata</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" id="includeSubtasks" class="mr-3" checked>
                <span>Include subtasks</span>
              </label>
            </div>
          </div>
          
          <!-- Export Scope -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Export Scope</label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input type="radio" name="exportScope" value="all" class="mr-3" checked>
                <span>All tasks</span>
              </label>
              <label class="flex items-center">
                <input type="radio" name="exportScope" value="selected" class="mr-3">
                <span>Selected tasks only</span>
              </label>
              <label class="flex items-center">
                <input type="radio" name="exportScope" value="filtered" class="mr-3">
                <span>Currently filtered tasks</span>
              </label>
            </div>
          </div>
        </div>
      </div>
      
      <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
        <button onclick="closeExportModal()" class="px-6 py-2 text-gray-600 hover:text-gray-800 transition-colors">
          Cancel
        </button>
        <button onclick="exportTasks()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          <i class="fas fa-download mr-2"></i>Export Tasks
        </button>
      </div>
    </div>
  </div>

  <!-- Bulk Tag Modal -->
  <div id="bulkTagModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-96 slide-in">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-800">Add Tags to Selected Tasks</h3>
          <button onclick="closeBulkTagModal()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Select Tags</label>
            <select id="bulkTagSelect" multiple class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
              <?php foreach ($tags as $tag): ?>
                <option value="<?= htmlspecialchars($tag['id']) ?>"><?= htmlspecialchars($tag['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Or Create New Tag</label>
            <input type="text" id="newBulkTag" placeholder="Enter new tag name" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>
      </div>
      
      <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
        <button onclick="closeBulkTagModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
          Cancel
        </button>
        <button onclick="applyBulkTags()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
          Apply Tags
        </button>
      </div>
    </div>
  </div>

  <script>
    // Tasks Loader and Toast System
    function showTasksLoader(message = 'Loading...') {
      const loader = document.getElementById('tasksLoader');
      const messageEl = document.getElementById('tasksLoaderMessage');
      if (messageEl) messageEl.textContent = message;
      if (loader) loader.classList.remove('hidden');
    }

    function hideTasksLoader() {
      const loader = document.getElementById('tasksLoader');
      if (loader) loader.classList.add('hidden');
    }

    function showTasksToast(message, type = 'info') {
      const container = document.getElementById('tasksToastContainer');
      const toast = document.createElement('div');
      
      const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
      };
      
      toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300`;
      toast.textContent = message;
      
      container.appendChild(toast);
      
      setTimeout(() => toast.classList.remove('translate-x-full'), 100);
      setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    // Global variables
    let currentTaskId = null;
    let currentView = 'kanban';
    let allTasks = <?= json_encode($tasks ?? []) ?>;
    let allTags = <?= json_encode($tags ?? []) ?>;
    let currentFilters = { status: 'all', priority: 'all', category: 'all', tag: 'all' };
    let selectedTasks = new Set();
    let currentSort = 'updated_desc';

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadTasks();
      initializeCalendar();
      setupEventListeners();
    });

    function setupEventListeners() {
      // Search input
      document.getElementById('searchInput').addEventListener('input', debounce(filterTasks, 300));
      
      // Recurring task checkbox
      document.getElementById('isRecurring').addEventListener('change', function() {
        const options = document.getElementById('recurrenceOptions');
        if (this.checked) {
          options.classList.remove('hidden');
        } else {
          options.classList.add('hidden');
        }
      });
      
      // Initialize filters
      initializeFilters();
    }

    function initializeFilters() {
      // Set active filter
      document.querySelector('[data-filter="status"][data-value="all"]').classList.add('active-filter');
    }

    // View switching
    function switchView(view) {
      currentView = view;
      
      // Update button states
      document.querySelectorAll('.view-toggle').forEach(btn => btn.classList.remove('active'));
      document.getElementById(view + 'Btn').classList.add('active');
      
      // Show/hide views
      document.querySelectorAll('.view-content').forEach(v => v.classList.add('hidden'));
      document.getElementById(view + 'View').classList.remove('hidden');
      
      // Load appropriate data
      if (view === 'calendar') {
        // Reinitialize calendar when switching to calendar view
        setTimeout(() => {
          initializeCalendar();
        }, 100);
      } else if (view === 'list') {
        loadListView();
      } else if (view === 'grid') {
        loadGridView();
      } else if (view === 'kanban') {
        loadTasks(); // This will load the kanban view
      }
    }

    // Task loading
    function loadTasks() {
      showTasksLoader('Loading tasks...');
      
      fetch('/tasks/api/get-kanban', {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Flatten the kanban data into a single array for allTasks
          allTasks = [];
          Object.values(data.data).forEach(tasks => {
            allTasks = allTasks.concat(tasks);
          });
          renderKanbanTasks(data.data);
        }
      })
      .catch(error => {
        console.error('Error loading tasks:', error);
        showTasksToast('Error loading tasks', 'error');
      })
      .finally(() => {
        hideTasksLoader();
      });
    }

    function renderKanbanTasks(kanbanData) {
      const columns = {
        pending: document.getElementById('pendingTasks'),
        in_progress: document.getElementById('inProgressTasks'),
        completed: document.getElementById('completedTasks'),
        cancelled: document.getElementById('overdueTasks') // Using overdue column for cancelled
      };
      
      const counts = {
        pending: document.getElementById('pendingCount'),
        in_progress: document.getElementById('inProgressCount'),
        completed: document.getElementById('completedCount'),
        cancelled: document.getElementById('overdueCount') // Using overdue count for cancelled
      };
      
      // Clear columns
      Object.values(columns).forEach(col => col.innerHTML = '');
      Object.values(counts).forEach(count => count.textContent = '0');
      
      // Render tasks for each status
      Object.keys(kanbanData).forEach(status => {
        const columnElement = columns[status];
        const countElement = counts[status];
        const tasks = kanbanData[status] || [];
        
        if (columnElement && countElement) {
          countElement.textContent = tasks.length;
          
          tasks.forEach(task => {
            const taskElement = createTaskCard(task);
            columnElement.appendChild(taskElement);
          });
        }
      });
    }

    function createTaskCard(task) {
      const card = document.createElement('div');
      card.className = `task-card rounded-xl p-4 cursor-move priority-${task.priority}`;
      card.draggable = true;
      card.dataset.taskId = task.id;
      card.ondragstart = drag;
      
      const progressBar = task.subtask_count > 0 ? `
        <div class="mb-3">
          <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
            <span>Progress</span>
            <span>${task.progress_percentage}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="progress-bar h-2 rounded-full" style="width: ${task.progress_percentage}%"></div>
          </div>
        </div>
      ` : '';
      
      const dueDate = task.due_date ? `
        <div class="flex items-center text-xs text-gray-500 mb-2">
          <i class="fas fa-clock mr-1"></i>
          <span>${new Date(task.due_date).toLocaleDateString()}</span>
        </div>
      ` : '';
      
      const tags = task.tags ? task.tags.split(',').map(tag => 
        `<span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-1 mb-1">${tag}</span>`
      ).join('') : '';
      
      card.innerHTML = `
        <div class="flex items-start justify-between mb-2">
          <h4 class="font-semibold text-gray-800 text-sm line-clamp-2 cursor-pointer hover:text-blue-600" onclick="openViewTaskModal(${task.id})">${task.title}</h4>
          <div class="task-actions relative">
            <button onclick="toggleTaskMenu(this)" class="p-1 text-gray-400 hover:text-gray-600">
              <i class="fas fa-ellipsis-v text-xs"></i>
            </button>
          </div>
        </div>
        
        <p class="text-xs text-gray-600 mb-3 line-clamp-2">${task.description || 'No description'}</p>
        
        ${progressBar}
        ${dueDate}
        
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">${task.priority}</span>
            ${task.category ? `<span class="text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-600">${task.category}</span>` : ''}
          </div>
          <div class="flex items-center gap-1">
            ${task.is_recurring ? '<i class="fas fa-repeat text-xs text-blue-500" title="Recurring"></i>' : ''}
            ${task.is_overdue ? '<i class="fas fa-exclamation-triangle text-xs text-red-500" title="Overdue"></i>' : ''}
          </div>
        </div>
        
        ${tags ? `<div class="mt-2">${tags}</div>` : ''}
      `;
      
      return card;
    }

    // Drag and drop functionality
    function allowDrop(ev) {
      ev.preventDefault();
      ev.currentTarget.classList.add('drag-over');
    }

    function drag(ev) {
      ev.dataTransfer.setData("text", ev.target.dataset.taskId);
      ev.target.classList.add('task-dragging');
    }

    function drop(ev, newStatus) {
      ev.preventDefault();
      ev.currentTarget.classList.remove('drag-over');
      
      const taskId = ev.dataTransfer.getData("text");
      const draggedElement = document.querySelector(`[data-task-id="${taskId}"]`);
      
      if (draggedElement) {
        draggedElement.classList.remove('task-dragging');
        updateTaskStatus(taskId, newStatus);
      }
    }

    // Task management functions
    function openAddTaskModal() {
      currentTaskId = null;
      document.getElementById('modalTitle').textContent = 'Add New Task';
      document.getElementById('taskForm').reset();
      document.getElementById('taskId').value = '';
      document.getElementById('recurrenceOptions').classList.add('hidden');
      showModal('taskModal');
    }

    function openEditTaskModal(taskId) {
      const task = allTasks.find(t => t.id == taskId);
      if (!task) {
        console.error('Task not found:', taskId);
        return;
      }
      
      // Check if modal elements exist
      const modalTitle = document.getElementById('modalTitle');
      const taskIdInput = document.getElementById('taskId');
      const taskTitle = document.getElementById('taskTitle');
      const taskDescription = document.getElementById('taskDescription');
      const taskPriority = document.getElementById('taskPriority');
      const taskCategory = document.getElementById('taskCategory');
      const taskDueDate = document.getElementById('taskDueDate');
      const isRecurring = document.getElementById('isRecurring');
      
      if (!modalTitle || !taskIdInput || !taskTitle || !taskDescription || !taskPriority || !taskCategory || !taskDueDate || !isRecurring) {
        console.error('Modal form elements not found');
        return;
      }
      
      currentTaskId = taskId;
      modalTitle.textContent = 'Edit Task';
      taskIdInput.value = taskId;
      taskTitle.value = task.title;
      taskDescription.value = task.description || '';
      taskPriority.value = task.priority;
      taskCategory.value = task.category || '';
      taskDueDate.value = task.due_date ? new Date(task.due_date).toISOString().slice(0, 16) : '';
      isRecurring.checked = task.is_recurring;
      
      if (task.is_recurring) {
        document.getElementById('recurrenceOptions').classList.remove('hidden');
        document.getElementById('recurrencePattern').value = task.recurrence_pattern;
      }
      
      // Set tags
      const tagIds = task.tag_ids ? task.tag_ids.split(',').filter(id => id.length > 0) : [];
      const tagSelect = document.getElementById('taskTags');
      Array.from(tagSelect.options).forEach(option => {
        option.selected = tagIds.includes(option.value);
      });
      
      showModal('taskModal');
    }

    function closeTaskModal() {
      hideModal('taskModal');
    }

    // View task functions
    let currentViewTaskId = null;

    function openViewTaskModal(taskId) {
      const task = allTasks.find(t => t.id == taskId);
      if (!task) {
        console.error('Task not found:', taskId);
        return;
      }
      
      const viewModalTitle = document.getElementById('viewModalTitle');
      const viewTaskContent = document.getElementById('viewTaskContent');
      
      if (!viewModalTitle || !viewTaskContent) {
        console.error('Modal elements not found');
        return;
      }
      
      currentViewTaskId = taskId;
      viewModalTitle.textContent = task.title;
      
      const tags = task.tags ? task.tags.split(',').map(tag => 
        `<span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-1 mb-1">${tag}</span>`
      ).join('') : '';
      
      const dueDate = task.due_date ? new Date(task.due_date).toLocaleDateString() : 'No due date';
      const createdDate = new Date(task.created_at).toLocaleDateString();
      const updatedDate = new Date(task.updated_at).toLocaleDateString();
      
      const priorityColors = {
        low: 'bg-green-100 text-green-800',
        medium: 'bg-blue-100 text-blue-800',
        high: 'bg-yellow-100 text-yellow-800',
        urgent: 'bg-red-100 text-red-800'
      };
      
      const statusColors = {
        pending: 'bg-gray-100 text-gray-800',
        in_progress: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800'
      };
      
      viewTaskContent.innerHTML = `
        <div class="space-y-6">
          <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-2">Description</h4>
            <p class="text-gray-600 bg-gray-50 p-4 rounded-lg">${task.description || 'No description provided'}</p>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <h4 class="text-sm font-semibold text-gray-700 mb-2">Status</h4>
              <span class="inline-block px-3 py-1 rounded-full text-sm ${statusColors[task.status] || 'bg-gray-100 text-gray-800'}">${task.status}</span>
            </div>
            <div>
              <h4 class="text-sm font-semibold text-gray-700 mb-2">Priority</h4>
              <span class="inline-block px-3 py-1 rounded-full text-sm ${priorityColors[task.priority] || 'bg-gray-100 text-gray-800'}">${task.priority}</span>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <h4 class="text-sm font-semibold text-gray-700 mb-2">Due Date</h4>
              <p class="text-gray-600">${dueDate}</p>
            </div>
            <div>
              <h4 class="text-sm font-semibold text-gray-700 mb-2">Progress</h4>
              <p class="text-gray-600">${task.progress || 0}%</p>
            </div>
          </div>
          
          ${tags ? `
            <div>
              <h4 class="text-sm font-semibold text-gray-700 mb-2">Tags</h4>
              <div class="flex flex-wrap">${tags}</div>
            </div>
          ` : ''}
          
          <div class="grid grid-cols-2 gap-4 text-sm text-gray-500">
            <div>
              <span class="font-medium">Created:</span> ${createdDate}
            </div>
            <div>
              <span class="font-medium">Updated:</span> ${updatedDate}
            </div>
          </div>
        </div>
      `;
      
      showModal('viewTaskModal');
    }

    function closeViewTaskModal() {
      hideModal('viewTaskModal');
      currentViewTaskId = null;
    }

    function editCurrentTask() {
      if (currentViewTaskId) {
        const taskId = currentViewTaskId;
        
        // Close view modal first
        hideModal('viewTaskModal');
        currentViewTaskId = null;
        
        // Small delay to ensure modal is closed before opening edit modal
        setTimeout(() => {
          openEditTaskModal(taskId);
        }, 100);
      }
    }

    function saveTask() {
      const form = document.getElementById('taskForm');
      const formData = new FormData(form);
      
      const saveButton = document.querySelector('#saveButtonText');
      const action = currentTaskId ? '/tasks/update' : '/tasks/store';
      const actionText = currentTaskId ? 'Updating task...' : 'Creating task...';
      
      showTasksLoader(actionText);
      
      fetch(action, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showTasksToast(data.message, 'success');
          closeTaskModal();
          loadTasks();
        } else {
          showTasksToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showTasksToast('An error occurred while saving the task', 'error');
      })
      .finally(() => {
        hideTasksLoader();
      });
    }

    function updateTaskStatus(taskId, status) {
      fetch('/tasks/update-status', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          task_id: taskId,
          status: status,
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Task status updated', 'success');
          loadTasks();
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
      });
    }

    function deleteTask(taskId) {
      if (confirm('Are you sure you want to move this task to trash? You can restore it later from the trash page.')) {
        fetch('/tasks/delete', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            task_id: taskId,
            csrf_token: document.querySelector('input[name="csrf_token"]').value
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToast('Task deleted successfully', 'success');
            loadTasks();
          } else {
            showToast(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('An error occurred', 'error');
        });
      }
    }

    // Subtask functions
    function openSubtasks(taskId) {
      currentTaskId = taskId;
      loadSubtasks(taskId);
      showModal('subtaskModal');
    }

    function closeSubtaskModal() {
      hideModal('subtaskModal');
    }

    function loadSubtasks(taskId) {
      fetch(`/tasks/subtasks/${taskId}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          renderSubtasks(data.subtasks);
        }
      })
      .catch(error => {
        console.error('Error loading subtasks:', error);
        showToast('Error loading subtasks', 'error');
      });
    }

    function renderSubtasks(subtasks) {
      const container = document.getElementById('subtasksList');
      container.innerHTML = '';
      
      subtasks.forEach(subtask => {
        const subtaskElement = document.createElement('div');
        subtaskElement.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-xl';
        subtaskElement.innerHTML = `
          <div class="flex items-center gap-3">
            <input type="checkbox" ${subtask.status === 'completed' ? 'checked' : ''} 
                   onchange="updateSubtaskStatus(${subtask.id}, this.checked)">
            <span class="${subtask.status === 'completed' ? 'line-through text-gray-500' : ''}">${subtask.title}</span>
          </div>
          <button onclick="deleteSubtask(${subtask.id})" class="text-red-500 hover:text-red-700">
            <i class="fas fa-trash text-xs"></i>
          </button>
        `;
        container.appendChild(subtaskElement);
      });
    }

    function openAddSubtaskModal() {
      const title = prompt('Enter subtask title:');
      if (title && title.trim()) {
        createSubtask(title.trim());
      }
    }

    function createSubtask(title) {
      fetch('/tasks/subtasks/create', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          task_id: currentTaskId,
          title: title,
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Subtask created successfully', 'success');
          loadSubtasks(currentTaskId);
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
      });
    }

    function updateSubtaskStatus(subtaskId, completed) {
      const status = completed ? 'completed' : 'pending';
      fetch('/tasks/subtasks/update', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          subtask_id: subtaskId,
          status: status,
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          loadSubtasks(currentTaskId);
          loadTasks(); // Refresh main tasks to update progress
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
      });
    }

    function deleteSubtask(subtaskId) {
      if (confirm('Are you sure you want to delete this subtask?')) {
        fetch('/tasks/subtasks/delete', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            subtask_id: subtaskId,
            csrf_token: document.querySelector('input[name="csrf_token"]').value
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToast('Subtask deleted successfully', 'success');
            loadSubtasks(currentTaskId);
            loadTasks(); // Refresh main tasks to update progress
          } else {
            showToast(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('An error occurred', 'error');
        });
      }
    }

    // Calendar functions
    function initializeCalendar() {
      const calendarEl = document.getElementById('calendar');
      if (!calendarEl) {
        console.error('Calendar element not found');
        return;
      }
      
      // Clear any existing calendar
      if (window.calendarInstance) {
        window.calendarInstance.destroy();
      }
      
      window.calendarInstance = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: function(info, successCallback, failureCallback) {
          console.log('Loading calendar events for:', info.start, 'to', info.end);
          loadCalendarTasks(info.start, info.end, successCallback, failureCallback);
        },
        eventDisplay: 'block',
        dayMaxEvents: 3,
        moreLinkClick: 'popover',
        eventClick: function(info) {
          console.log('Calendar event clicked:', info.event);
          openEditTaskModal(info.event.id);
        },
        eventDidMount: function(info) {
          console.log('Event mounted:', info.event.title);
        },
        loading: function(isLoading) {
          console.log('Calendar loading:', isLoading);
        }
      });
      
      window.calendarInstance.render();
      console.log('Calendar initialized and rendered');
    }

    function loadCalendarTasks(start, end, successCallback, failureCallback) {
      // Ensure start and end are valid dates
      if (!start || !end) {
        console.error('Invalid date range for calendar');
        if (successCallback && typeof successCallback === 'function') {
          successCallback([]);
        }
        return;
      }
      
      console.log('Fetching calendar tasks from:', start.toISOString(), 'to', end.toISOString());
      
      fetch(`/tasks/api/calendar?start=${start.toISOString()}&end=${end.toISOString()}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => {
        console.log('Calendar API response status:', response.status);
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        console.log('Calendar API response data:', data);
        
        // Handle both array and object responses
        let events = [];
        if (Array.isArray(data)) {
          events = data;
        } else if (data && Array.isArray(data.events)) {
          events = data.events;
        } else if (data && Array.isArray(data.data)) {
          events = data.data;
        }
        
        // Format events for FullCalendar
        const formattedEvents = events.map(task => ({
            id: task.id,
            title: task.title,
          start: task.start || task.due_date,
          end: task.end || task.due_date,
          backgroundColor: task.color || getPriorityColor(task.priority),
          borderColor: task.color || getPriorityColor(task.priority),
          extendedProps: {
            description: task.description,
            status: task.status,
            priority: task.priority
          }
        }));
        
        console.log('Formatted calendar events:', formattedEvents);
        
        // If no events found, create some sample data for testing
        if (formattedEvents.length === 0) {
          console.log('No calendar events found, creating sample data');
          const sampleEvents = [
            {
              id: 'sample-1',
              title: 'Sample Task 1',
              start: new Date().toISOString().split('T')[0],
              backgroundColor: '#3b82f6',
              borderColor: '#3b82f6',
              extendedProps: {
                description: 'This is a sample task for testing the calendar',
                status: 'pending',
                priority: 'medium'
              }
            },
            {
              id: 'sample-2',
              title: 'Sample Task 2',
              start: new Date(Date.now() + 86400000).toISOString().split('T')[0], // Tomorrow
              backgroundColor: '#ef4444',
              borderColor: '#ef4444',
              extendedProps: {
                description: 'This is another sample task',
                status: 'in_progress',
                priority: 'high'
              }
            }
          ];
          
          if (successCallback && typeof successCallback === 'function') {
            successCallback(sampleEvents);
          }
        } else {
          if (successCallback && typeof successCallback === 'function') {
            successCallback(formattedEvents);
          }
        }
      })
      .catch(error => {
        console.error('Error loading calendar tasks:', error);
        if (failureCallback && typeof failureCallback === 'function') {
          failureCallback(error);
        } else if (successCallback && typeof successCallback === 'function') {
          successCallback([]);
        }
      });
    }

    function getPriorityColor(priority) {
      const colors = {
        urgent: '#ef4444',
        high: '#f97316',
        medium: '#3b82f6',
        low: '#10b981'
      };
      return colors[priority] || '#6b7280';
    }

    // List view functions
    function loadListView() {
      const taskList = document.getElementById('taskList');
      taskList.innerHTML = '';
      
      allTasks.forEach(task => {
          const taskElement = createListTaskItem(task);
          taskList.appendChild(taskElement);
      });
    }

    function createListTaskItem(task) {
      const item = document.createElement('div');
      item.className = `task-card rounded-xl p-4 priority-${task.priority}`;
      
      const progressBar = task.subtask_count > 0 ? `
        <div class="mb-3">
          <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
            <span>Progress</span>
            <span>${task.progress_percentage}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="progress-bar h-2 rounded-full" style="width: ${task.progress_percentage}%"></div>
          </div>
        </div>
      ` : '';
      
      item.innerHTML = `
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <h4 class="font-semibold text-gray-800">${task.title}</h4>
              <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">${task.status}</span>
              <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">${task.priority}</span>
            </div>
            <p class="text-sm text-gray-600 mb-3">${task.description || 'No description'}</p>
            ${progressBar}
            <div class="flex items-center gap-4 text-xs text-gray-500">
              <span><i class="fas fa-calendar mr-1"></i>${new Date(task.created_at).toLocaleDateString()}</span>
              ${task.due_date ? `<span><i class="fas fa-clock mr-1"></i>${new Date(task.due_date).toLocaleDateString()}</span>` : ''}
              ${task.subtask_count > 0 ? `<span><i class="fas fa-tasks mr-1"></i>${task.completed_subtasks}/${task.subtask_count} subtasks</span>` : ''}
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button onclick="openEditTaskModal(${task.id})" class="p-2 text-blue-500 hover:text-blue-700">
              <i class="fas fa-edit"></i>
            </button>
            <button onclick="deleteTask(${task.id})" class="p-2 text-red-500 hover:text-red-700">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
      `;
      
      return item;
    }

    // Filter functions
    function setFilter(button) {
      const filterType = button.dataset.filter;
      const filterValue = button.dataset.value;
      
      // Remove active class from all buttons of same type
      document.querySelectorAll(`[data-filter="${filterType}"]`).forEach(btn => {
        btn.classList.remove('active-filter');
      });
      
      // Add active class to clicked button
      button.classList.add('active-filter');
      
      // Update current filters
      currentFilters[filterType] = filterValue;
      
      // Apply filters
      filterTasks();
    }

    function filterTasks() {
      const searchQuery = document.getElementById('searchInput').value.toLowerCase();
      const priorityFilter = document.getElementById('priorityFilter').value;
      const categoryFilter = document.getElementById('categoryFilter').value;
      const tagFilter = document.getElementById('tagFilter').value;
      
      currentFilters.priority = priorityFilter;
      currentFilters.category = categoryFilter;
      currentFilters.tag = tagFilter;
      
      // Apply filters to current view
      if (currentView === 'kanban') {
        loadTasks();
      } else if (currentView === 'list') {
        loadListView();
      } else if (currentView === 'grid') {
        loadGridView();
      }
    }

    function sortTasks() {
      const sortBy = document.getElementById('sortBy').value;
      currentSort = sortBy;
      
      // Apply sorting to current view
      if (currentView === 'list') {
        loadListView();
      } else if (currentView === 'grid') {
        loadGridView();
      }
      
      showTasksToast(`Tasks sorted by ${document.getElementById('sortBy').selectedOptions[0].text}`, 'info');
    }

    // Utility functions
    function showModal(modalId) {
      const modal = document.getElementById(modalId);
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function hideModal(modalId) {
      const modal = document.getElementById(modalId);
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    function showToast(message, type = 'info') {
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      toast.innerHTML = `
        <div class="flex items-center">
          <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
          ${message}
        </div>
      `;
      
      document.getElementById('toast-container').appendChild(toast);
      
      setTimeout(() => toast.classList.add('show'), 100);
      
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    function debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }

    function toggleTaskMenu(button) {
      const taskCard = button.closest('.task-card');
      const taskId = taskCard.dataset.taskId;
      const isOpen = document.querySelector('.menu-overlay.active') !== null;
      
      // Close any existing menu
      closeTaskMenu();
      
      if (!isOpen) {
        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'menu-overlay';
        overlay.onclick = closeTaskMenu;
        
        // Create menu
        const menu = document.createElement('div');
        menu.className = 'task-menu';
        menu.innerHTML = `
          <div class="flex justify-between items-center px-4 py-2 border-b border-gray-200">
            <span class="text-sm font-semibold text-gray-700">Task Actions</span>
            <button onclick="closeTaskMenu()" class="text-gray-400 hover:text-gray-600 transition-colors">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <button onclick="openViewTaskModal(${taskId})" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center border-b border-gray-100">
            <i class="fas fa-eye mr-3 text-green-500"></i>View Task
          </button>
          <button onclick="openEditTaskModal(${taskId})" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center border-b border-gray-100">
            <i class="fas fa-edit mr-3 text-blue-500"></i>Edit Task
          </button>
          <button onclick="openSubtasks(${taskId})" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center border-b border-gray-100">
            <i class="fas fa-tasks mr-3 text-green-500"></i>Manage Subtasks
          </button>
          <button onclick="updateTaskPriority(${taskId})" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center border-b border-gray-100">
            <i class="fas fa-flag mr-3 text-yellow-500"></i>Change Priority
          </button>
          <button onclick="duplicateTask(${taskId})" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center border-b border-gray-100">
            <i class="fas fa-copy mr-3 text-purple-500"></i>Duplicate Task
          </button>
          <button onclick="moveToTrash(${taskId})" class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 flex items-center">
            <i class="fas fa-trash mr-3 text-red-500"></i>Move to Trash
          </button>
        `;
        
        // Position menu near the button
        const rect = button.getBoundingClientRect();
        const menuWidth = 200;
        const menuHeight = 320;
        
        let left = rect.right - menuWidth;
        let top = rect.bottom + 10;
        
        // Adjust if menu goes off screen
        if (left < 10) left = 10;
        if (left + menuWidth > window.innerWidth - 10) left = window.innerWidth - menuWidth - 10;
        if (top + menuHeight > window.innerHeight - 10) top = rect.top - menuHeight - 10;
        
        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
        
        // Add to document
        document.body.appendChild(overlay);
        document.body.appendChild(menu);
        
        // Show with animation
        setTimeout(() => {
          overlay.classList.add('active');
          menu.style.opacity = '0';
          menu.style.transform = 'translateY(-10px) scale(0.95)';
          menu.style.transition = 'all 0.2s ease-out';
          
          setTimeout(() => {
            menu.style.opacity = '1';
            menu.style.transform = 'translateY(0) scale(1)';
          }, 10);
        }, 10);
      }
    }
    
    function closeTaskMenu() {
      const overlay = document.querySelector('.menu-overlay');
      const menu = document.querySelector('.task-menu');
      
      if (overlay) {
        overlay.classList.remove('active');
        setTimeout(() => {
          if (overlay.parentNode) {
            overlay.remove();
          }
        }, 200);
      }
      
      if (menu) {
        menu.style.opacity = '0';
        menu.style.transform = 'translateY(-10px) scale(0.95)';
        setTimeout(() => {
          if (menu.parentNode) {
            menu.remove();
          }
        }, 200);
      }
      
      // Remove menu-open class from all cards
      document.querySelectorAll('.task-card').forEach(card => card.classList.remove('menu-open'));
    }

    // Close menus when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.task-menu') && !e.target.closest('button[onclick*="toggleTaskMenu"]')) {
        closeTaskMenu();
      }
    });
    
    // Close menu with Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeTaskMenu();
      }
    });
    
    // Make closeTaskMenu globally accessible
    window.closeTaskMenu = closeTaskMenu;

    // Remove drag-over class when drag leaves
    document.addEventListener('dragleave', function(e) {
      if (e.target.classList.contains('kanban-column')) {
        e.target.classList.remove('drag-over');
      }
    });
        // Remove drag-over class when drag leaves
        document.addEventListener('dragleave', function(e) {
      if (e.target.classList.contains('kanban-column')) {
        e.target.classList.remove('drag-over');
      }
    });

    // ========================================
    // GRID VIEW FUNCTIONALITY
    // ========================================
    
    function loadGridView() {
      const tasksGrid = document.getElementById('tasksGrid');
      tasksGrid.innerHTML = '';
      
      const filteredTasks = getFilteredTasks();
      const sortedTasks = getSortedTasks(filteredTasks);
      
      if (sortedTasks.length === 0) {
        tasksGrid.innerHTML = `
          <div class="col-span-full text-center py-12">
            <div class="floating-animation mb-6">
              <i class="fas fa-tasks text-6xl text-gray-300"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No tasks found</h3>
            <p class="text-gray-500 mb-6">Try adjusting your search or filters</p>
            <button onclick="openAddTaskModal()" class="btn-3d px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
              <i class="fas fa-plus mr-2"></i>Create Your First Task
            </button>
          </div>
        `;
        return;
      }
      
      sortedTasks.forEach(task => {
        const taskElement = createGridTaskCard(task);
        tasksGrid.appendChild(taskElement);
      });
    }

     function createGridTaskCard(task) {
       const div = document.createElement('div');
       div.className = 'task-item';
       
       const tags = task.tags ? task.tags.split(',').filter(tag => tag && tag.trim()).map(tag => tag.trim()) : [];
       const tagIds = task.tag_ids ? task.tag_ids.split(',').filter(id => id && id.trim()).map(id => id.trim()) : [];
       const isCompleted = task.status === 'completed';
       const priority = task.priority || 'medium';
       const status = task.status || 'pending';
       
       div.innerHTML = `
         <div class="task-card rounded-2xl shadow-lg p-6 priority-${priority}" 
              data-task-id="${task.id || ''}"
              data-task-title="${task.title || ''}"
              data-task-description="${task.description || ''}"
              data-task-status="${status}"
              data-task-priority="${priority}"
              data-task-category="${task.category || ''}"
              data-task-tags="${task.tags || ''}"
              data-task-tag-ids="${task.tag_ids || ''}"
              data-task-due-date="${task.due_date || ''}"
              data-task-created-at="${task.created_at}"
              data-task-updated-at="${task.updated_at}">
           
           <!-- Task Header -->
           <div class="task-card-header">
             <div class="flex-1">
               <div class="flex items-start gap-3">
                 <input type="checkbox" class="task-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 mt-1" 
                        data-task-id="${task.id || ''}" 
                        onchange="updateBulkActions()">
                 <div class="flex-1">
                   <h3 class="task-card-title" onclick="openViewTaskModal(${task.id})">${task.title || 'Untitled'}</h3>
                   <div class="task-card-meta">
                     <span class="task-card-date">
                       <i class="fas fa-calendar-alt"></i>
                       ${task.updated_at ? new Date(task.updated_at).toLocaleDateString() : 'Unknown'}
                     </span>
                     <span>•</span>
                     <span class="task-card-category">${task.category || 'No category'}</span>
                   </div>
                 </div>
               </div>
             </div>
             
             <!-- Actions Menu -->
             <div class="task-card-actions">
               <button onclick="toggleTaskMenu(this)" class="p-2 text-gray-400 hover:text-gray-600 transition-colors rounded-lg hover:bg-gray-100">
                 <i class="fas fa-ellipsis-v"></i>
               </button>
               
               <div class="task-card-menu">
                 <button onclick="openViewTaskModal(${task.id})" class="task-card-menu-item">
                   <i class="fas fa-eye text-blue-500"></i>View
                 </button>
                 <button onclick="openEditTaskModal(${task.id})" class="task-card-menu-item">
                   <i class="fas fa-edit text-yellow-500"></i>Edit
                 </button>
                 <button onclick="openSubtasks(${task.id})" class="task-card-menu-item">
                   <i class="fas fa-tasks text-green-500"></i>Subtasks
                 </button>
                 <button onclick="updateTaskPriority(${task.id})" class="task-card-menu-item">
                   <i class="fas fa-flag text-orange-500"></i>Priority
                 </button>
                 <hr class="my-1 border-gray-200">
                 <button onclick="deleteTask(${task.id})" class="task-card-menu-item text-red-600 hover:bg-red-50">
                   <i class="fas fa-trash text-red-500"></i>Delete
                 </button>
               </div>
             </div>
           </div>
           
           <!-- Task Content -->
           <div class="task-card-content">
             <p class="task-card-description">${task.description || 'No description provided'}</p>
           </div>
           
           <!-- Tags -->
           ${tags && tags.length > 0 ? `
             <div class="task-card-tags">
               ${tags.map(tag => '<span class="tag-badge">' + (tag || '') + '</span>').join('')}
             </div>
           ` : ''}
           
           <!-- Task Footer -->
           <div class="task-card-footer">
             <div class="flex items-center gap-2">
               <span class="task-card-status-badge task-card-status-${status}">${status.replace('_', ' ')}</span>
               <span class="task-card-priority-badge task-card-priority-${priority}">${priority}</span>
             </div>
             <div class="flex items-center gap-1">
               ${task.is_recurring ? '<i class="fas fa-repeat text-xs text-blue-500" title="Recurring"></i>' : ''}
               ${task.is_overdue ? '<i class="fas fa-exclamation-triangle text-xs text-red-500" title="Overdue"></i>' : ''}
               ${task.due_date ? '<i class="fas fa-clock text-xs text-gray-500" title="Has due date"></i>' : ''}
             </div>
           </div>
         </div>
       `;
       
       return div;
     }

    // ========================================
    // BULK ACTIONS FUNCTIONALITY
    // ========================================
    
    function updateBulkActions() {
      const checkboxes = document.querySelectorAll('.task-checkbox:checked');
      selectedTasks.clear();
      checkboxes.forEach(checkbox => {
        selectedTasks.add(checkbox.dataset.taskId);
      });
      
      const bulkActions = document.getElementById('bulkActions');
      const selectedCount = document.getElementById('selectedCount');
      
      if (selectedTasks.size > 0) {
        bulkActions.classList.remove('hidden');
        selectedCount.textContent = `${selectedTasks.size} selected`;
      } else {
        bulkActions.classList.add('hidden');
      }
    }
    
    function clearSelection() {
      document.querySelectorAll('.task-checkbox').forEach(checkbox => {
        checkbox.checked = false;
      });
      selectedTasks.clear();
      updateBulkActions();
    }
    
    function bulkComplete() {
      if (selectedTasks.size === 0) return;
      
      showTasksToast(`Completing ${selectedTasks.size} tasks...`, 'info');
      
      const promises = Array.from(selectedTasks).map(taskId => {
        return fetch('/tasks/update-status', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            task_id: taskId,
            status: 'completed',
            csrf_token: document.querySelector('input[name="csrf_token"]').value
          })
        });
      });
      
      Promise.all(promises).then(() => {
        showTasksToast(`Successfully completed ${selectedTasks.size} tasks`, 'success');
        clearSelection();
        loadTasks();
      }).catch(error => {
        showTasksToast('Error completing tasks', 'error');
      });
    }
    
    function bulkDelete() {
      if (selectedTasks.size === 0) return;
      
      if (confirm(`Are you sure you want to permanently delete ${selectedTasks.size} tasks? This action cannot be undone.`)) {
        showTasksToast(`Deleting ${selectedTasks.size} tasks...`, 'warning');
        
        const promises = Array.from(selectedTasks).map(taskId => {
          return fetch('/tasks/delete', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
              task_id: taskId,
              csrf_token: document.querySelector('input[name="csrf_token"]').value
            })
          });
        });
        
        Promise.all(promises).then(() => {
          showTasksToast(`Successfully deleted ${selectedTasks.size} tasks`, 'success');
          clearSelection();
          loadTasks();
        }).catch(error => {
          showTasksToast('Error deleting tasks', 'error');
        });
      }
    }
    
    function bulkArchive() {
      if (selectedTasks.size === 0) return;
      
      showTasksToast(`Archiving ${selectedTasks.size} tasks...`, 'info');
      
      // This would need backend implementation for bulk archive operations
      showTasksToast('Bulk archive feature will be implemented in the backend', 'info');
      clearSelection();
    }
    
    function bulkTag() {
      if (selectedTasks.size === 0) return;
      showModal('bulkTagModal');
    }
    
    function closeBulkTagModal() {
      hideModal('bulkTagModal');
    }
    
    function applyBulkTags() {
      const selectedTags = Array.from(document.getElementById('bulkTagSelect').selectedOptions).map(option => option.value);
      const newTag = document.getElementById('newBulkTag').value.trim();
      
      if (selectedTags.length === 0 && !newTag) {
        showTasksToast('Please select tags or enter a new tag', 'warning');
        return;
      }
      
      showTasksToast(`Adding tags to ${selectedTasks.size} tasks...`, 'info');
      
      // This would need backend implementation for bulk tag operations
      showTasksToast('Bulk tagging feature will be implemented in the backend', 'info');
      closeBulkTagModal();
    }

    // ========================================
    // TEMPLATES FUNCTIONALITY
    // ========================================
    
    function openTemplatesModal() {
      showModal('templatesModal');
    }
    
    function closeTemplatesModal() {
      hideModal('templatesModal');
    }
    
    function useTemplate(templateType) {
      const templates = {
        project: {
          title: 'Project Task',
          description: 'Project-related task with milestones and deadlines',
          priority: 'high',
          category: 'work'
        },
        meeting: {
          title: 'Meeting Task',
          description: 'Meeting preparation and follow-up tasks',
          priority: 'medium',
          category: 'work'
        },
        personal: {
          title: 'Personal Task',
          description: 'Personal task and daily activity',
          priority: 'low',
          category: 'personal'
        },
        bugfix: {
          title: 'Bug Fix',
          description: 'Bug description and steps to reproduce',
          priority: 'urgent',
          category: 'work'
        },
        learning: {
          title: 'Learning Task',
          description: 'Educational and skill development task',
          priority: 'medium',
          category: 'education'
        },
        blank: {
          title: 'New Task',
          description: '',
          priority: 'medium',
          category: ''
        }
      };
      
      const template = templates[templateType];
      if (template) {
        document.getElementById('taskTitle').value = template.title;
        document.getElementById('taskDescription').value = template.description;
        document.getElementById('taskPriority').value = template.priority;
        document.getElementById('taskCategory').value = template.category;
        closeTemplatesModal();
        showModal('taskModal');
      }
    }

    // ========================================
    // EXPORT FUNCTIONALITY
    // ========================================
    
    function openExportModal() {
      showModal('exportModal');
    }
    
    function closeExportModal() {
      hideModal('exportModal');
    }
    
    function exportTasks() {
      const format = document.querySelector('input[name="exportFormat"]:checked').value;
      const scope = document.querySelector('input[name="exportScope"]:checked').value;
      const includeTags = document.getElementById('includeTags').checked;
      const includeMetadata = document.getElementById('includeMetadata').checked;
      const includeSubtasks = document.getElementById('includeSubtasks').checked;
      
      showTasksToast(`Exporting tasks as ${format.toUpperCase()}...`, 'info');
      
      const exportData = {
        format: format,
        scope: scope,
        include_tags: includeTags,
        include_metadata: includeMetadata,
        include_subtasks: includeSubtasks,
        csrf_token: document.querySelector('input[name="csrf_token"]').value
      };
      
      // Add selected task IDs if scope is 'selected'
      if (scope === 'selected' && selectedTasks.size > 0) {
        exportData.task_ids = Array.from(selectedTasks);
      }
      
       fetch('/tasks/export', {
         method: 'POST',
         headers: {
           'Content-Type': 'application/json',
           'X-Requested-With': 'XMLHttpRequest'
         },
         body: JSON.stringify(exportData)
       })
       .then(response => {
         if (response.ok) {
           const contentType = response.headers.get('content-type');
           if (contentType && contentType.includes('application/json')) {
             return response.json().then(data => {
               throw new Error(data.message || 'Export failed');
             });
           }
           return response.blob();
         } else {
           return response.text().then(text => {
             try {
               const data = JSON.parse(text);
               throw new Error(data.message || 'Export failed');
             } catch (e) {
               throw new Error('Export failed: ' + text);
             }
           });
         }
       })
      .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        
        let extension = 'md';
        if (format === 'json') extension = 'json';
        else if (format === 'docx' || format === 'word') extension = 'docx';
        else if (format === 'pdf') extension = 'pdf';
        
        a.download = `tasks_export_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.${extension}`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showTasksToast('Tasks exported successfully!', 'success');
        closeExportModal();
      })
      .catch(error => {
        console.error('Export error:', error);
        showTasksToast('Export failed: ' + error.message, 'error');
      });
    }

    // ========================================
    // STATISTICS AND HELP FUNCTIONALITY
    // ========================================
    
    function showTaskStatistics() {
      const totalTasks = allTasks.length;
      const completedTasks = allTasks.filter(task => task.status === 'completed').length;
      const pendingTasks = allTasks.filter(task => task.status === 'pending').length;
      const inProgressTasks = allTasks.filter(task => task.status === 'in_progress').length;
      
      const statsModal = document.createElement('div');
      statsModal.className = 'fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center z-50';
      statsModal.innerHTML = `
        <div class="modal-content rounded-2xl shadow-2xl w-96 slide-in">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-800">Task Statistics</h3>
              <button onclick="this.closest('.fixed').remove()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          
          <div class="p-6">
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">${totalTasks}</div>
                <div class="text-sm text-gray-600">Total Tasks</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">${completedTasks}</div>
                <div class="text-sm text-gray-600">Completed</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">${pendingTasks}</div>
                <div class="text-sm text-gray-600">Pending</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">${inProgressTasks}</div>
                <div class="text-sm text-gray-600">In Progress</div>
              </div>
            </div>
          </div>
        </div>
      `;
      
      document.body.appendChild(statsModal);
    }
    
    function showKeyboardShortcuts() {
      const shortcutsModal = document.createElement('div');
      shortcutsModal.className = 'fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center z-50';
      shortcutsModal.innerHTML = `
        <div class="modal-content rounded-2xl shadow-2xl w-96 slide-in">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-800">Keyboard Shortcuts</h3>
              <button onclick="this.closest('.modal-backdrop').remove()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          
          <div class="p-6">
            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-gray-700">New Task</span>
                <kbd class="px-2 py-1 bg-gray-200 rounded text-sm">Ctrl/Cmd + N</kbd>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-700">Focus Search</span>
                <kbd class="px-2 py-1 bg-gray-200 rounded text-sm">Ctrl/Cmd + F</kbd>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-700">Templates</span>
                <kbd class="px-2 py-1 bg-gray-200 rounded text-sm">Ctrl/Cmd + T</kbd>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-700">Export</span>
                <kbd class="px-2 py-1 bg-gray-200 rounded text-sm">Ctrl/Cmd + E</kbd>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-700">Close Modal</span>
                <kbd class="px-2 py-1 bg-gray-200 rounded text-sm">Escape</kbd>
              </div>
            </div>
          </div>
        </div>
      `;
      
      document.body.appendChild(shortcutsModal);
    }
    
    function showHelp() {
      const helpModal = document.createElement('div');
      helpModal.className = 'fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center z-50';
      helpModal.innerHTML = `
        <div class="modal-content rounded-2xl shadow-2xl w-96 slide-in">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-800">Help & Tips</h3>
              <button onclick="this.closest('.modal-backdrop').remove()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          
          <div class="p-6">
            <div class="space-y-4">
              <div>
                <h4 class="font-semibold text-gray-800 mb-2">Getting Started</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                  <li>• Click "Add Task" to create your first task</li>
                  <li>• Use templates for quick task creation</li>
                  <li>• Drag and drop tasks between columns</li>
                  <li>• Use bulk actions for multiple tasks</li>
                </ul>
              </div>
              
              <div>
                <h4 class="font-semibold text-gray-800 mb-2">Features</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                  <li>• Multiple view modes (Kanban, Calendar, List, Grid)</li>
                  <li>• Advanced filtering and search</li>
                  <li>• Task templates and export</li>
                  <li>• Subtasks and progress tracking</li>
                </ul>
              </div>
              
              <div>
                <h4 class="font-semibold text-gray-800 mb-2">Tips</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                  <li>• Use keyboard shortcuts for faster workflow</li>
                  <li>• Set due dates to track deadlines</li>
                  <li>• Use tags to organize tasks</li>
                  <li>• Break large tasks into subtasks</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      `;
      
      document.body.appendChild(helpModal);
    }

    // ========================================
    // ADVANCED SEARCH FUNCTIONALITY
    // ========================================
    
    function openAdvancedSearchModal() {
      const modal = document.createElement('div');
      modal.className = 'fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center z-50';
      modal.innerHTML = `
        <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-2xl slide-in">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-800">Advanced Search</h3>
              <button onclick="this.closest('.fixed').remove()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          
          <div class="p-6">
            <form id="advancedSearchForm">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Search Terms</label>
                  <input type="text" id="searchTerms" placeholder="Enter search terms..." 
                         class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date From</label>
                    <input type="date" id="dateFrom" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date To</label>
                    <input type="date" id="dateTo" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                </div>
                
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Priority</label>
                  <select id="searchPriority" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Priorities</option>
                    <option value="urgent">Urgent</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Tags</label>
                  <select id="searchTags" multiple class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    ${allTags.map(tag => '<option value="' + tag.id + '">' + tag.name + '</option>').join('')}
                  </select>
                </div>
              </div>
            </form>
          </div>
          
          <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
            <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
              Cancel
            </button>
            <button onclick="performAdvancedSearch()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              Search
            </button>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
    }

    function performAdvancedSearch() {
      const searchTerms = document.getElementById('searchTerms').value;
      const dateFrom = document.getElementById('dateFrom').value;
      const dateTo = document.getElementById('dateTo').value;
      const priority = document.getElementById('searchPriority').value;
      const tags = Array.from(document.getElementById('searchTags').selectedOptions).map(option => option.value);
      
      // Apply advanced search filters
      document.getElementById('searchInput').value = searchTerms;
      if (priority) document.getElementById('priorityFilter').value = priority;
      
      // Close modal
      document.querySelector('.fixed.inset-0').remove();
      
      // Apply filters
      filterTasks();
      
      showTasksToast('Advanced search applied', 'success');
    }

    // ========================================
    // UTILITY FUNCTIONS
    // ========================================
    
    function getFilteredTasks() {
      const searchQuery = document.getElementById('searchInput').value.toLowerCase();
      const priorityFilter = document.getElementById('priorityFilter').value;
      const categoryFilter = document.getElementById('categoryFilter').value;
      const tagFilter = document.getElementById('tagFilter').value;
      
      return allTasks.filter(task => {
        // Search filter
        const searchMatch = !searchQuery || 
          task.title.toLowerCase().includes(searchQuery) || 
          (task.description && task.description.toLowerCase().includes(searchQuery));
        
        // Status filter
        let statusMatch = false;
        switch (currentFilters.status) {
          case 'all':
            statusMatch = true;
            break;
          case 'pending':
            statusMatch = task.status === 'pending';
            break;
          case 'in_progress':
            statusMatch = task.status === 'in_progress';
            break;
          case 'completed':
            statusMatch = task.status === 'completed';
            break;
        }
        
        // Priority filter
        const priorityMatch = priorityFilter === 'all' || task.priority === priorityFilter;
        
        // Category filter
        const categoryMatch = categoryFilter === 'all' || task.category === categoryFilter;
        
        // Tag filter
        const tagIds = task.tag_ids ? task.tag_ids.split(',').filter(id => id.length > 0) : [];
        const tagMatch = tagFilter === 'all' || tagIds.includes(tagFilter);
        
        return searchMatch && statusMatch && priorityMatch && categoryMatch && tagMatch;
      });
    }
    
    function getSortedTasks(tasks) {
      return tasks.sort((a, b) => {
        switch (currentSort) {
          case 'title_asc':
            return a.title.localeCompare(b.title);
          case 'title_desc':
            return b.title.localeCompare(a.title);
          case 'updated_desc':
            return new Date(b.updated_at) - new Date(a.updated_at);
          case 'created_desc':
            return new Date(b.created_at) - new Date(a.created_at);
          case 'due_date_asc':
            if (!a.due_date && !b.due_date) return 0;
            if (!a.due_date) return 1;
            if (!b.due_date) return -1;
            return new Date(a.due_date) - new Date(b.due_date);
          case 'due_date_desc':
            if (!a.due_date && !b.due_date) return 0;
            if (!a.due_date) return -1;
            if (!b.due_date) return 1;
            return new Date(b.due_date) - new Date(a.due_date);
          case 'priority':
            const priorityOrder = { urgent: 4, high: 3, medium: 2, low: 1 };
            return priorityOrder[b.priority] - priorityOrder[a.priority];
          default:
            return 0;
        }
      });
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
      // Ctrl/Cmd + N: New task
      if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        openAddTaskModal();
      }
      
      // Ctrl/Cmd + F: Focus search
      if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        document.getElementById('searchInput').focus();
      }
      
      // Ctrl/Cmd + T: Templates
      if ((e.ctrlKey || e.metaKey) && e.key === 't') {
        e.preventDefault();
        openTemplatesModal();
      }
      
      // Ctrl/Cmd + E: Export
      if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
        e.preventDefault();
        openExportModal();
      }
      
      // Escape: Close modals
      if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('.fixed.inset-0:not(.hidden)');
        openModals.forEach(modal => {
          if (modal.id) {
            hideModal(modal.id);
          }
        });
      }
    });
    
    // Task action functions (duplicate removed - using the main implementation above)

    // openEditTaskModal function removed (duplicate - using main implementation above)

    function openSubtasks(taskId) {
      const task = allTasks.find(t => t.id == taskId);
      if (!task) {
        showTasksToast('Task not found', 'error');
        return;
      }
      
      // Load subtasks for this task
      fetch(`/tasks/api/get-subtasks/${taskId}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Show subtasks modal
          document.getElementById('subtasksModalTitle').textContent = `Subtasks for: ${task.title}`;
          document.getElementById('subtasksList').innerHTML = '';
          
          if (data.subtasks && data.subtasks.length > 0) {
            data.subtasks.forEach(subtask => {
              const subtaskItem = document.createElement('div');
              subtaskItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg mb-2';
              subtaskItem.innerHTML = `
                <div class="flex items-center">
                  <input type="checkbox" ${subtask.status === 'completed' ? 'checked' : ''} 
                         onchange="updateSubtaskStatus(${subtask.id}, this.checked)" 
                         class="mr-3">
                  <span class="${subtask.status === 'completed' ? 'line-through text-gray-500' : ''}">${subtask.title}</span>
                </div>
                <button onclick="deleteSubtask(${subtask.id})" class="text-red-500 hover:text-red-700">
                  <i class="fas fa-trash"></i>
                </button>
              `;
              document.getElementById('subtasksList').appendChild(subtaskItem);
            });
          } else {
            document.getElementById('subtasksList').innerHTML = '<p class="text-gray-500 text-center py-4">No subtasks yet</p>';
          }
          
          showModal('subtasksModal');
        } else {
          showTasksToast('Failed to load subtasks', 'error');
        }
      })
      .catch(error => {
        console.error('Error loading subtasks:', error);
        showTasksToast('Error loading subtasks', 'error');
      });
    }

    function updateTaskPriority(taskId) {
      const task = allTasks.find(t => t.id == taskId);
      if (!task) {
        showTasksToast('Task not found', 'error');
        return;
      }
      
      const newPriority = prompt('Enter new priority (low, medium, high, urgent):', task.priority || 'medium');
      if (!newPriority || !['low', 'medium', 'high', 'urgent'].includes(newPriority.toLowerCase())) {
        return;
      }
      
      fetch('/tasks/update-priority', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          task_id: taskId,
          priority: newPriority.toLowerCase()
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showTasksToast('Task priority updated successfully!', 'success');
          loadTasks(); // Reload tasks to show updated priority
        } else {
          showTasksToast(data.message || 'Failed to update priority', 'error');
        }
      })
      .catch(error => {
        console.error('Error updating priority:', error);
        showTasksToast('Error updating priority', 'error');
      });
    }

    function updateSubtaskStatus(subtaskId, isCompleted) {
      fetch('/tasks/update-subtask', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          subtask_id: subtaskId,
          status: isCompleted ? 'completed' : 'pending'
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showTasksToast('Subtask updated successfully!', 'success');
        } else {
          showTasksToast(data.message || 'Failed to update subtask', 'error');
        }
      })
      .catch(error => {
        console.error('Error updating subtask:', error);
        showTasksToast('Error updating subtask', 'error');
      });
    }

    function deleteSubtask(subtaskId) {
      if (!confirm('Are you sure you want to delete this subtask?')) {
        return;
      }
      
      fetch('/tasks/delete-subtask', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          subtask_id: subtaskId
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showTasksToast('Subtask deleted successfully!', 'success');
          // Reload subtasks
          const taskId = currentViewTaskId || currentTaskId;
          if (taskId) {
            openSubtasks(taskId);
          }
        } else {
          showTasksToast(data.message || 'Failed to delete subtask', 'error');
        }
      })
      .catch(error => {
        console.error('Error deleting subtask:', error);
        showTasksToast('Error deleting subtask', 'error');
      });
    }
    
    // Add missing CSS for template cards
    const style = document.createElement('style');
    style.textContent = `
      .template-card {
        transition: all 0.3s ease;
        cursor: pointer;
      }
      
      .template-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      }
      
      .task-checkbox {
        transition: all 0.2s ease;
      }
      
      .task-checkbox:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
      }
      
      /* Enhanced grid responsiveness */
      @media (max-width: 640px) {
        .tasks-grid {
          grid-template-columns: 1fr;
          gap: 1rem;
          padding: 0.5rem 0;
        }
        
        .task-card {
          min-height: 280px;
        }
      }
      
      @media (min-width: 640px) and (max-width: 768px) {
        .tasks-grid {
          grid-template-columns: repeat(2, 1fr);
        }
      }
      
      @media (min-width: 768px) and (max-width: 1024px) {
        .tasks-grid {
          grid-template-columns: repeat(2, 1fr);
        }
      }
      
      @media (min-width: 1024px) and (max-width: 1280px) {
        .tasks-grid {
          grid-template-columns: repeat(3, 1fr);
        }
      }
      
      @media (min-width: 1280px) and (max-width: 1536px) {
        .tasks-grid {
          grid-template-columns: repeat(4, 1fr);
        }
      }
      
      @media (min-width: 1536px) {
        .tasks-grid {
          grid-template-columns: repeat(5, 1fr);
        }
      }
    `;
    document.head.appendChild(style);
    
  </script>
</body>
</html>
