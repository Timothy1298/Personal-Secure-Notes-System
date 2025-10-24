# Personal Notes Desktop Application

A cross-platform desktop application built with Electron and React for the Personal Notes System, providing a native desktop experience with system integration and offline capabilities.

## Features

- **Cross-Platform**: Windows, macOS, and Linux support
- **Native Integration**: System tray, global shortcuts, file associations
- **Offline Support**: Local data storage with cloud synchronization
- **Rich UI**: Material-UI components with dark/light theme support
- **Auto-Updates**: Automatic application updates
- **Security**: Secure credential storage and encrypted local data
- **Performance**: Optimized for desktop with native performance

## Prerequisites

- Node.js (>= 16)
- npm or yarn
- Git

## Installation

1. Clone the repository and navigate to the desktop directory:
```bash
cd desktop
```

2. Install dependencies:
```bash
npm install
```

## Development

### Start Development Server
```bash
npm run dev
```

This will start both the React development server and Electron in development mode.

### Build for Production
```bash
npm run build
```

### Package for Distribution
```bash
# Build for current platform
npm run dist

# Build for specific platforms
npm run dist-win    # Windows
npm run dist-mac    # macOS
npm run dist-linux  # Linux
```

## Configuration

### API Configuration
Update the API base URL in `src/hooks/useAuth.js`:
```javascript
const API_BASE_URL = 'http://your-server-url:8000';
```

### Build Configuration
Modify `package.json` build section for custom distribution settings:
- App ID and product name
- Icons and assets
- Code signing certificates
- Auto-updater settings

## Features Overview

### System Integration
- **Global Shortcuts**: Quick access to create notes and tasks
- **System Tray**: Background operation with quick access
- **File Associations**: Open notes files directly
- **Auto-Start**: Launch with system startup
- **Native Menus**: Platform-specific menu bars

### Security
- **Secure Storage**: Encrypted local data storage
- **Credential Management**: Secure token storage
- **Content Security Policy**: XSS protection
- **Context Isolation**: Secure renderer process

### Performance
- **Lazy Loading**: Components loaded on demand
- **Virtual Scrolling**: Efficient large list rendering
- **Memory Management**: Optimized resource usage
- **Background Processing**: Non-blocking operations

### User Experience
- **Responsive Design**: Adapts to window resizing
- **Keyboard Navigation**: Full keyboard support
- **Accessibility**: Screen reader support
- **Themes**: Dark and light mode support

## Architecture

### Main Process (`src/main.js`)
- Application lifecycle management
- Window creation and management
- Menu and shortcut handling
- Auto-updater integration
- Security policies

### Renderer Process (`src/App.js`)
- React application
- UI components and pages
- State management
- API communication

### Preload Script (`src/preload.js`)
- Secure IPC bridge
- API exposure to renderer
- File system operations
- Store management

### Components Structure
```
src/
├── components/          # Reusable UI components
├── pages/              # Main application pages
├── hooks/              # Custom React hooks
├── utils/              # Utility functions
├── styles/             # Global styles
└── assets/             # Static assets
```

## Keyboard Shortcuts

### Global Shortcuts
- `Ctrl/Cmd + Shift + N`: New Note
- `Ctrl/Cmd + Shift + T`: New Task
- `Ctrl/Cmd + Shift + D`: Toggle Dark Mode

### Application Shortcuts
- `Ctrl/Cmd + N`: New Note
- `Ctrl/Cmd + T`: New Task
- `Ctrl/Cmd + ,`: Settings
- `Ctrl/Cmd + /`: Show Shortcuts
- `Ctrl/Cmd + Q`: Quit (macOS: `Cmd + Q`)

## Auto-Updates

The application supports automatic updates through electron-updater:

1. **Development**: Updates disabled
2. **Production**: Automatic update checks
3. **GitHub Releases**: Publish releases for auto-updates
4. **User Control**: Users can disable auto-updates

## Building and Distribution

### Code Signing
For production builds, configure code signing:

**Windows:**
```json
"win": {
  "certificateFile": "path/to/certificate.p12",
  "certificatePassword": "password"
}
```

**macOS:**
```json
"mac": {
  "identity": "Developer ID Application: Your Name"
}
```

### Notarization (macOS)
For macOS distribution outside the App Store:
```bash
# Configure notarization in build settings
"afterSign": "scripts/notarize.js"
```

## Security Considerations

1. **Context Isolation**: Enabled to prevent direct Node.js access
2. **Node Integration**: Disabled in renderer process
3. **Remote Module**: Disabled for security
4. **Content Security Policy**: Implemented for XSS protection
5. **External Links**: Opened in default browser
6. **File Access**: Restricted to application directory

## Performance Optimization

1. **Code Splitting**: Lazy load components
2. **Bundle Analysis**: Monitor bundle size
3. **Memory Profiling**: Regular memory leak checks
4. **CPU Profiling**: Optimize heavy operations
5. **Network Optimization**: Efficient API calls

## Testing

```bash
# Run tests
npm test

# Run tests with coverage
npm run test:coverage

# Run E2E tests
npm run test:e2e
```

## Debugging

### Development Tools
- React Developer Tools
- Redux DevTools
- Electron DevTools

### Logging
```javascript
// Main process
console.log('Main process log');

// Renderer process
console.log('Renderer process log');
```

### Performance Monitoring
```bash
# Enable performance monitoring
npm run dev -- --enable-logging
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For issues and questions:
- GitHub Issues: [Repository Issues](https://github.com/your-repo/issues)
- Documentation: [Wiki](https://github.com/your-repo/wiki)
- Community: [Discussions](https://github.com/your-repo/discussions)
