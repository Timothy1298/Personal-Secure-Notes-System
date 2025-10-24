import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { ThemeProvider, createTheme } from '@mui/material/styles';
import CssBaseline from '@mui/material/CssBaseline';
import { Box } from '@mui/material';

// Import components
import Sidebar from './components/Sidebar';
import Header from './components/Header';
import Dashboard from './pages/Dashboard';
import Notes from './pages/Notes';
import Tasks from './pages/Tasks';
import VoiceNotes from './pages/VoiceNotes';
import OCR from './pages/OCR';
import Settings from './pages/Settings';
import Login from './pages/Login';

// Import hooks
import { useElectronAPI } from './hooks/useElectronAPI';
import { useTheme } from './hooks/useTheme';
import { useAuth } from './hooks/useAuth';

function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [currentPage, setCurrentPage] = useState('dashboard');
  
  const { isDarkMode, toggleTheme } = useTheme();
  const { user, login, logout } = useAuth();
  const electronAPI = useElectronAPI();

  // Create Material-UI theme
  const theme = createTheme({
    palette: {
      mode: isDarkMode ? 'dark' : 'light',
      primary: {
        main: '#3b82f6',
      },
      secondary: {
        main: '#64748b',
      },
      background: {
        default: isDarkMode ? '#0f172a' : '#f8fafc',
        paper: isDarkMode ? '#1e293b' : '#ffffff',
      },
    },
    typography: {
      fontFamily: [
        '-apple-system',
        'BlinkMacSystemFont',
        '"Segoe UI"',
        'Roboto',
        '"Helvetica Neue"',
        'Arial',
        'sans-serif',
      ].join(','),
    },
    components: {
      MuiButton: {
        styleOverrides: {
          root: {
            textTransform: 'none',
            borderRadius: 8,
          },
        },
      },
      MuiCard: {
        styleOverrides: {
          root: {
            borderRadius: 12,
            boxShadow: isDarkMode 
              ? '0 4px 6px -1px rgba(0, 0, 0, 0.3)' 
              : '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
          },
        },
      },
    },
  });

  useEffect(() => {
    // Check authentication status
    checkAuthStatus();
    
    // Set up menu event listeners
    setupMenuListeners();
    
    // Set up global shortcuts
    setupGlobalShortcuts();
  }, []);

  const checkAuthStatus = async () => {
    try {
      const token = await electronAPI.getStoreValue('auth_token');
      const userData = await electronAPI.getStoreValue('user_data');
      
      if (token && userData) {
        setIsAuthenticated(true);
      }
    } catch (error) {
      console.error('Error checking auth status:', error);
    }
  };

  const setupMenuListeners = () => {
    electronAPI.onMenuNewNote(() => {
      setCurrentPage('notes');
    });
    
    electronAPI.onMenuNewTask(() => {
      setCurrentPage('tasks');
    });
    
    electronAPI.onMenuSettings(() => {
      setCurrentPage('settings');
    });
    
    electronAPI.onMenuShortcuts(() => {
      // Show shortcuts dialog
      console.log('Show shortcuts');
    });
  };

  const setupGlobalShortcuts = () => {
    electronAPI.onGlobalShortcutNewNote(() => {
      setCurrentPage('notes');
    });
    
    electronAPI.onGlobalShortcutNewTask(() => {
      setCurrentPage('tasks');
    });
    
    electronAPI.onGlobalShortcutToggleDarkMode(() => {
      toggleTheme();
    });
  };

  const handleLogin = async (credentials) => {
    try {
      const result = await login(credentials);
      if (result.success) {
        setIsAuthenticated(true);
        await electronAPI.setStoreValue('auth_token', result.token);
        await electronAPI.setStoreValue('user_data', JSON.stringify(result.user));
      }
      return result;
    } catch (error) {
      console.error('Login error:', error);
      return { success: false, error: 'Login failed' };
    }
  };

  const handleLogout = async () => {
    try {
      await logout();
      setIsAuthenticated(false);
      await electronAPI.deleteStoreValue('auth_token');
      await electronAPI.deleteStoreValue('user_data');
    } catch (error) {
      console.error('Logout error:', error);
    }
  };

  const handlePageChange = (page) => {
    setCurrentPage(page);
  };

  const toggleSidebar = () => {
    setSidebarOpen(!sidebarOpen);
  };

  if (!isAuthenticated) {
    return (
      <ThemeProvider theme={theme}>
        <CssBaseline />
        <Login onLogin={handleLogin} />
      </ThemeProvider>
    );
  }

  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <Box sx={{ display: 'flex', height: '100vh' }}>
        <Sidebar 
          open={sidebarOpen} 
          onPageChange={handlePageChange}
          currentPage={currentPage}
        />
        <Box sx={{ flexGrow: 1, display: 'flex', flexDirection: 'column' }}>
          <Header 
            onToggleSidebar={toggleSidebar}
            onLogout={handleLogout}
            user={user}
            currentPage={currentPage}
          />
          <Box sx={{ flexGrow: 1, overflow: 'auto' }}>
            <Routes>
              <Route path="/" element={<Dashboard />} />
              <Route path="/notes" element={<Notes />} />
              <Route path="/tasks" element={<Tasks />} />
              <Route path="/voice-notes" element={<VoiceNotes />} />
              <Route path="/ocr" element={<OCR />} />
              <Route path="/settings" element={<Settings />} />
            </Routes>
          </Box>
        </Box>
      </Box>
    </ThemeProvider>
  );
}

export default App;
