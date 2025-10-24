# Personal Notes Mobile App

A React Native mobile application for the Personal Notes System, providing a native mobile experience for managing notes, tasks, voice recordings, and OCR functionality.

## Features

- **Authentication**: Secure login and registration
- **Notes Management**: Create, edit, and organize notes
- **Task Management**: Manage tasks with priorities and due dates
- **Voice Notes**: Record and transcribe voice notes
- **OCR**: Extract text from images
- **Offline Support**: Work offline with data synchronization
- **Dark Mode**: Theme switching support
- **Push Notifications**: Real-time notifications
- **Biometric Authentication**: Secure access with fingerprint/face ID

## Prerequisites

- Node.js (>= 16)
- React Native CLI
- Android Studio (for Android development)
- Xcode (for iOS development, macOS only)
- Java Development Kit (JDK 11 or higher)

## Installation

1. Install dependencies:
```bash
npm install
```

2. For iOS, install CocoaPods dependencies:
```bash
cd ios && pod install && cd ..
```

3. For Android, ensure you have the Android SDK installed and configured.

## Running the App

### Android
```bash
npm run android
```

### iOS
```bash
npm run ios
```

### Development Server
```bash
npm start
```

## Configuration

1. Update the API base URL in `src/config/api.js`:
```javascript
export const API_BASE_URL = 'http://your-server-url:8000';
```

2. For production, update the URL to your production server.

## Building for Production

### Android
```bash
npm run build:android
```

### iOS
```bash
npm run build:ios
```

## Features Overview

### Authentication
- Secure login with username/password
- Biometric authentication support
- Automatic token refresh
- Secure storage of credentials

### Notes
- Rich text editing
- Markdown support
- Tag organization
- Search functionality
- Offline editing with sync

### Tasks
- Task creation and management
- Priority levels
- Due dates and reminders
- Subtask support
- Progress tracking

### Voice Notes
- High-quality audio recording
- Automatic transcription
- Playback controls
- Convert to text notes
- Cloud storage

### OCR
- Image capture from camera
- Text extraction from images
- Multiple language support
- Convert to text notes
- Batch processing

### Offline Support
- Local data storage
- Automatic synchronization
- Conflict resolution
- Background sync

## Architecture

The app follows a clean architecture pattern with:

- **Context Providers**: Authentication, Theme, Network
- **Screens**: UI components for each feature
- **Services**: API communication and data management
- **Utils**: Helper functions and utilities
- **Config**: Configuration and constants

## Security

- Secure credential storage using Keychain (iOS) and Keystore (Android)
- Biometric authentication
- Encrypted local storage
- Secure API communication
- Token-based authentication

## Performance

- Optimized rendering with FlatList
- Image caching and optimization
- Lazy loading of components
- Memory management
- Background processing

## Testing

```bash
npm test
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License.
