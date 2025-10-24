const { contextBridge, ipcRenderer } = require('electron');

// Expose protected methods that allow the renderer process to use
// the ipcRenderer without exposing the entire object
contextBridge.exposeInMainWorld('electronAPI', {
  // App info
  getAppVersion: () => ipcRenderer.invoke('get-app-version'),
  
  // Store operations
  getStoreValue: (key) => ipcRenderer.invoke('get-store-value', key),
  setStoreValue: (key, value) => ipcRenderer.invoke('set-store-value', key, value),
  deleteStoreValue: (key) => ipcRenderer.invoke('delete-store-value', key),
  
  // Dialog operations
  showSaveDialog: (options) => ipcRenderer.invoke('show-save-dialog', options),
  showOpenDialog: (options) => ipcRenderer.invoke('show-open-dialog', options),
  showMessageBox: (options) => ipcRenderer.invoke('show-message-box', options),
  showErrorBox: (title, content) => ipcRenderer.invoke('show-error-box', title, content),
  
  // Window operations
  minimizeWindow: () => ipcRenderer.invoke('minimize-window'),
  maximizeWindow: () => ipcRenderer.invoke('maximize-window'),
  closeWindow: () => ipcRenderer.invoke('close-window'),
  setWindowTitle: (title) => ipcRenderer.invoke('set-window-title', title),
  
  // Menu events
  onMenuNewNote: (callback) => ipcRenderer.on('menu-new-note', callback),
  onMenuNewTask: (callback) => ipcRenderer.on('menu-new-task', callback),
  onMenuImport: (callback) => ipcRenderer.on('menu-import', callback),
  onMenuExport: (callback) => ipcRenderer.on('menu-export', callback),
  onMenuSettings: (callback) => ipcRenderer.on('menu-settings', callback),
  onMenuShortcuts: (callback) => ipcRenderer.on('menu-shortcuts', callback),
  
  // Global shortcuts
  onGlobalShortcutNewNote: (callback) => ipcRenderer.on('global-shortcut-new-note', callback),
  onGlobalShortcutNewTask: (callback) => ipcRenderer.on('global-shortcut-new-task', callback),
  onGlobalShortcutToggleDarkMode: (callback) => ipcRenderer.on('global-shortcut-toggle-dark-mode', callback),
  
  // Remove listeners
  removeAllListeners: (channel) => ipcRenderer.removeAllListeners(channel),
  
  // Platform info
  platform: process.platform,
  
  // File system operations
  readFile: async (filePath) => {
    const fs = require('fs').promises;
    return await fs.readFile(filePath, 'utf8');
  },
  
  writeFile: async (filePath, data) => {
    const fs = require('fs').promises;
    return await fs.writeFile(filePath, data, 'utf8');
  },
  
  // Path operations
  joinPath: (...paths) => {
    const path = require('path');
    return path.join(...paths);
  },
  
  getHomeDir: () => {
    const os = require('os');
    return os.homedir();
  },
  
  getAppDataDir: () => {
    const os = require('os');
    const path = require('path');
    return path.join(os.homedir(), '.personal-notes');
  }
});

// Expose a limited set of Node.js APIs for the renderer process
contextBridge.exposeInMainWorld('nodeAPI', {
  // Crypto for secure operations
  createHash: (algorithm) => {
    const crypto = require('crypto');
    return crypto.createHash(algorithm);
  },
  
  // UUID generation
  generateUUID: () => {
    const crypto = require('crypto');
    return crypto.randomUUID();
  },
  
  // Date utilities
  getCurrentTimestamp: () => Date.now(),
  
  // Environment variables (safe ones only)
  getEnv: (key) => {
    if (['NODE_ENV', 'ELECTRON_IS_DEV'].includes(key)) {
      return process.env[key];
    }
    return undefined;
  }
});

// Security: Remove node integration
delete window.require;
delete window.exports;
delete window.module;
