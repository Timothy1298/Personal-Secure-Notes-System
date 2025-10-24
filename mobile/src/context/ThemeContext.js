import React, {createContext, useContext, useState, useEffect} from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import {useColorScheme} from 'react-native';

const ThemeContext = createContext();

export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
};

export const ThemeProvider = ({children}) => {
  const systemColorScheme = useColorScheme();
  const [isDarkMode, setIsDarkMode] = useState(systemColorScheme === 'dark');
  const [isSystemTheme, setIsSystemTheme] = useState(true);

  useEffect(() => {
    loadThemePreference();
  }, []);

  const loadThemePreference = async () => {
    try {
      const savedTheme = await AsyncStorage.getItem('theme_preference');
      const savedSystemTheme = await AsyncStorage.getItem('use_system_theme');

      if (savedTheme !== null) {
        setIsDarkMode(savedTheme === 'dark');
        setIsSystemTheme(savedSystemTheme === 'true');
      } else {
        setIsDarkMode(systemColorScheme === 'dark');
        setIsSystemTheme(true);
      }
    } catch (error) {
      console.error('Error loading theme preference:', error);
    }
  };

  const toggleTheme = async () => {
    const newTheme = !isDarkMode;
    setIsDarkMode(newTheme);
    setIsSystemTheme(false);
    
    try {
      await AsyncStorage.setItem('theme_preference', newTheme ? 'dark' : 'light');
      await AsyncStorage.setItem('use_system_theme', 'false');
    } catch (error) {
      console.error('Error saving theme preference:', error);
    }
  };

  const setSystemTheme = async () => {
    setIsSystemTheme(true);
    setIsDarkMode(systemColorScheme === 'dark');
    
    try {
      await AsyncStorage.setItem('use_system_theme', 'true');
      await AsyncStorage.removeItem('theme_preference');
    } catch (error) {
      console.error('Error setting system theme:', error);
    }
  };

  const setLightTheme = async () => {
    setIsDarkMode(false);
    setIsSystemTheme(false);
    
    try {
      await AsyncStorage.setItem('theme_preference', 'light');
      await AsyncStorage.setItem('use_system_theme', 'false');
    } catch (error) {
      console.error('Error setting light theme:', error);
    }
  };

  const setDarkTheme = async () => {
    setIsDarkMode(true);
    setIsSystemTheme(false);
    
    try {
      await AsyncStorage.setItem('theme_preference', 'dark');
      await AsyncStorage.setItem('use_system_theme', 'false');
    } catch (error) {
      console.error('Error setting dark theme:', error);
    }
  };

  // Update theme when system theme changes
  useEffect(() => {
    if (isSystemTheme) {
      setIsDarkMode(systemColorScheme === 'dark');
    }
  }, [systemColorScheme, isSystemTheme]);

  const value = {
    isDarkMode,
    isSystemTheme,
    toggleTheme,
    setSystemTheme,
    setLightTheme,
    setDarkTheme,
  };

  return <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>;
};
