import React, {useEffect, useState} from 'react';
import {
  SafeAreaView,
  StatusBar,
  StyleSheet,
  useColorScheme,
  Alert,
  PermissionsAndroid,
  Platform,
} from 'react-native';

import {NavigationContainer} from '@react-navigation/native';
import {createStackNavigator} from '@react-navigation/stack';
import {createBottomTabNavigator} from '@react-navigation/bottom-tabs';
import {PaperProvider, MD3LightTheme, MD3DarkTheme} from 'react-native-paper';
import Icon from 'react-native-vector-icons/MaterialIcons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import PushNotification from 'react-native-push-notification';
import NetInfo from '@react-native-community/netinfo';

// Import screens
import LoginScreen from './src/screens/LoginScreen';
import RegisterScreen from './src/screens/RegisterScreen';
import DashboardScreen from './src/screens/DashboardScreen';
import NotesScreen from './src/screens/NotesScreen';
import TasksScreen from './src/screens/TasksScreen';
import VoiceNotesScreen from './src/screens/VoiceNotesScreen';
import OCRScreen from './src/screens/OCRScreen';
import SettingsScreen from './src/screens/SettingsScreen';
import ProfileScreen from './src/screens/ProfileScreen';

// Import context
import {AuthProvider, useAuth} from './src/context/AuthContext';
import {ThemeProvider, useTheme} from './src/context/ThemeContext';
import {NetworkProvider} from './src/context/NetworkContext';

const Stack = createStackNavigator();
const Tab = createBottomTabNavigator();

// Configure push notifications
PushNotification.configure({
  onRegister: function (token) {
    console.log('TOKEN:', token);
  },
  onNotification: function (notification) {
    console.log('NOTIFICATION:', notification);
  },
  permissions: {
    alert: true,
    badge: true,
    sound: true,
  },
  popInitialNotification: true,
  requestPermissions: Platform.OS === 'ios',
});

function MainTabs() {
  const {isDarkMode} = useTheme();
  const theme = isDarkMode ? MD3DarkTheme : MD3LightTheme;

  return (
    <Tab.Navigator
      screenOptions={({route}) => ({
        tabBarIcon: ({focused, color, size}) => {
          let iconName;

          if (route.name === 'Dashboard') {
            iconName = 'dashboard';
          } else if (route.name === 'Notes') {
            iconName = 'note';
          } else if (route.name === 'Tasks') {
            iconName = 'assignment';
          } else if (route.name === 'Voice') {
            iconName = 'mic';
          } else if (route.name === 'OCR') {
            iconName = 'camera-alt';
          } else if (route.name === 'Settings') {
            iconName = 'settings';
          }

          return <Icon name={iconName} size={size} color={color} />;
        },
        tabBarActiveTintColor: theme.colors.primary,
        tabBarInactiveTintColor: theme.colors.onSurfaceVariant,
        tabBarStyle: {
          backgroundColor: theme.colors.surface,
          borderTopColor: theme.colors.outline,
        },
        headerStyle: {
          backgroundColor: theme.colors.surface,
        },
        headerTintColor: theme.colors.onSurface,
      })}>
      <Tab.Screen name="Dashboard" component={DashboardScreen} />
      <Tab.Screen name="Notes" component={NotesScreen} />
      <Tab.Screen name="Tasks" component={TasksScreen} />
      <Tab.Screen name="Voice" component={VoiceNotesScreen} />
      <Tab.Screen name="OCR" component={OCRScreen} />
      <Tab.Screen name="Settings" component={SettingsScreen} />
    </Tab.Navigator>
  );
}

function AppNavigator() {
  const {isAuthenticated, isLoading} = useAuth();

  if (isLoading) {
    return null; // Show loading screen
  }

  return (
    <NavigationContainer>
      <Stack.Navigator screenOptions={{headerShown: false}}>
        {isAuthenticated ? (
          <>
            <Stack.Screen name="MainTabs" component={MainTabs} />
            <Stack.Screen name="Profile" component={ProfileScreen} />
          </>
        ) : (
          <>
            <Stack.Screen name="Login" component={LoginScreen} />
            <Stack.Screen name="Register" component={RegisterScreen} />
          </>
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
}

function App() {
  const isDarkMode = useColorScheme() === 'dark';
  const [isDarkTheme, setIsDarkTheme] = useState(isDarkMode);

  useEffect(() => {
    // Load saved theme preference
    AsyncStorage.getItem('theme').then(theme => {
      if (theme) {
        setIsDarkTheme(theme === 'dark');
      }
    });

    // Request permissions
    requestPermissions();
  }, []);

  const requestPermissions = async () => {
    if (Platform.OS === 'android') {
      try {
        const granted = await PermissionsAndroid.requestMultiple([
          PermissionsAndroid.PERMISSIONS.CAMERA,
          PermissionsAndroid.PERMISSIONS.RECORD_AUDIO,
          PermissionsAndroid.PERMISSIONS.WRITE_EXTERNAL_STORAGE,
          PermissionsAndroid.PERMISSIONS.READ_EXTERNAL_STORAGE,
        ]);

        const allGranted = Object.values(granted).every(
          permission => permission === PermissionsAndroid.RESULTS.GRANTED,
        );

        if (!allGranted) {
          Alert.alert(
            'Permissions Required',
            'Some features may not work without the required permissions.',
          );
        }
      } catch (err) {
        console.warn(err);
      }
    }
  };

  const theme = isDarkTheme ? MD3DarkTheme : MD3LightTheme;

  return (
    <NetworkProvider>
      <ThemeProvider>
        <AuthProvider>
          <PaperProvider theme={theme}>
            <SafeAreaView style={styles.container}>
              <StatusBar
                barStyle={isDarkTheme ? 'light-content' : 'dark-content'}
                backgroundColor={theme.colors.surface}
              />
              <AppNavigator />
            </SafeAreaView>
          </PaperProvider>
        </AuthProvider>
      </ThemeProvider>
    </NetworkProvider>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
});

export default App;
