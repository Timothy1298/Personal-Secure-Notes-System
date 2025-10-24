// API Configuration
export const API_BASE_URL = 'http://localhost:8000';

// API Endpoints
export const API_ENDPOINTS = {
  // Authentication
  LOGIN: '/api/auth/login',
  REGISTER: '/api/auth/register',
  LOGOUT: '/api/auth/logout',
  REFRESH_TOKEN: '/api/auth/refresh',
  
  // Notes
  NOTES: '/api/notes',
  NOTE_BY_ID: (id) => `/api/notes/${id}`,
  CREATE_NOTE: '/api/notes',
  UPDATE_NOTE: (id) => `/api/notes/${id}`,
  DELETE_NOTE: (id) => `/api/notes/${id}`,
  
  // Tasks
  TASKS: '/api/tasks',
  TASK_BY_ID: (id) => `/api/tasks/${id}`,
  CREATE_TASK: '/api/tasks',
  UPDATE_TASK: (id) => `/api/tasks/${id}`,
  DELETE_TASK: (id) => `/api/tasks/${id}`,
  
  // Voice Notes
  VOICE_NOTES: '/api/voice-notes',
  VOICE_NOTE_BY_ID: (id) => `/api/voice-notes/${id}`,
  UPLOAD_VOICE_NOTE: '/api/voice-notes/upload',
  TRANSCRIBE_VOICE_NOTE: (id) => `/api/voice-notes/${id}/transcribe`,
  
  // OCR
  OCR_RESULTS: '/api/ocr',
  OCR_BY_ID: (id) => `/api/ocr/${id}`,
  UPLOAD_IMAGE: '/api/ocr/upload',
  PROCESS_IMAGE: '/api/ocr/process',
  
  // Tags
  TAGS: '/api/tags',
  CREATE_TAG: '/api/tags',
  UPDATE_TAG: (id) => `/api/tags/${id}`,
  DELETE_TAG: (id) => `/api/tags/${id}`,
  
  // Search
  SEARCH: '/api/search',
  GLOBAL_SEARCH: '/api/search/global',
  
  // Analytics
  ANALYTICS: '/api/analytics',
  USER_STATS: '/api/analytics/user-stats',
  PRODUCTIVITY_INSIGHTS: '/api/analytics/productivity',
  
  // Settings
  USER_SETTINGS: '/api/settings',
  UPDATE_SETTINGS: '/api/settings/update',
  CHANGE_PASSWORD: '/api/settings/change-password',
  
  // Backup
  BACKUP: '/api/backup',
  CREATE_BACKUP: '/api/backup/create',
  RESTORE_BACKUP: '/api/backup/restore',
  
  // File Upload
  UPLOAD_FILE: '/api/upload',
  DELETE_FILE: (id) => `/api/upload/${id}`,
};

// API Request Helper
export const apiRequest = async (endpoint, options = {}) => {
  const url = `${API_BASE_URL}${endpoint}`;
  
  const defaultOptions = {
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
  };

  try {
    const response = await fetch(url, {...defaultOptions, ...options});
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('API request failed:', error);
    throw error;
  }
};

// Authenticated API Request Helper
export const authenticatedApiRequest = async (endpoint, token, options = {}) => {
  const authOptions = {
    ...options,
    headers: {
      ...options.headers,
      'Authorization': `Bearer ${token}`,
    },
  };

  return apiRequest(endpoint, authOptions);
};

// File Upload Helper
export const uploadFile = async (file, endpoint, token, onProgress = null) => {
  const formData = new FormData();
  formData.append('file', file);

  const xhr = new XMLHttpRequest();

  return new Promise((resolve, reject) => {
    xhr.upload.addEventListener('progress', (event) => {
      if (event.lengthComputable && onProgress) {
        const progress = (event.loaded / event.total) * 100;
        onProgress(progress);
      }
    });

    xhr.addEventListener('load', () => {
      if (xhr.status === 200) {
        try {
          const response = JSON.parse(xhr.responseText);
          resolve(response);
        } catch (error) {
          reject(new Error('Invalid JSON response'));
        }
      } else {
        reject(new Error(`Upload failed with status: ${xhr.status}`));
      }
    });

    xhr.addEventListener('error', () => {
      reject(new Error('Upload failed'));
    });

    xhr.open('POST', `${API_BASE_URL}${endpoint}`);
    xhr.setRequestHeader('Authorization', `Bearer ${token}`);
    xhr.send(formData);
  });
};
