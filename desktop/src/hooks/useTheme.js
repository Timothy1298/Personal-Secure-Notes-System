import { useState, useEffect } from 'react';
import { useElectronAPI } from './useElectronAPI';

export const useTheme = () => {
  const [isDarkMode, setIsDarkMode] = useState(false);
  const electronAPI = useElectronAPI();

  useEffect(() => {
    loadThemePreference();
  }, [electronAPI]);

  const loadThemePreference = async () => {
    if (!electronAPI) return;
    
    try {
      const savedTheme = await electronAPI.getStoreValue('theme');
      if (savedTheme !== null) {
        setIsDarkMode(savedTheme === 'dark');
      } else {
        // Default to system preference
        setIsDarkMode(window.matchMedia('(prefers-color-scheme: dark)').matches);
      }
    } catch (error) {
      console.error('Error loading theme preference:', error);
    }
  };

  const toggleTheme = async () => {
    const newTheme = !isDarkMode;
    setIsDarkMode(newTheme);
    
    if (electronAPI) {
      try {
        await electronAPI.setStoreValue('theme', newTheme ? 'dark' : 'light');
      } catch (error) {
        console.error('Error saving theme preference:', error);
      }
    }
  };

  const setLightTheme = async () => {
    setIsDarkMode(false);
    
    if (electronAPI) {
      try {
        await electronAPI.setStoreValue('theme', 'light');
      } catch (error) {
        console.error('Error saving theme preference:', error);
      }
    }
  };

  const setDarkTheme = async () => {
    setIsDarkMode(true);
    
    if (electronAPI) {
      try {
        await electronAPI.setStoreValue('theme', 'dark');
      } catch (error) {
        console.error('Error saving theme preference:', error);
      }
    }
  };

  return {
    isDarkMode,
    toggleTheme,
    setLightTheme,
    setDarkTheme,
  };
};
