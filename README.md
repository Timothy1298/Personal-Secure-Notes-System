# Personal Notes System

A comprehensive, modern notes and task management system built with PHP 8.2, featuring advanced AI integration, real-time collaboration, mobile apps, and enterprise-grade architecture.

## 🚀 Features

### Core Features
- **Notes Management**: Rich text editor with markdown support, tags, categories, and search
- **Task Management**: Kanban boards, priority levels, due dates, and subtasks
- **User Authentication**: Secure login with 2FA support and password reset
- **Data Export/Import**: JSON, CSV, XML, and ZIP backup formats
- **Search**: Global search across notes and tasks with advanced filtering

### Advanced Features
- **AI Integration**: Smart suggestions, content analysis, and automated categorization
- **Voice Notes**: Record, transcribe, and convert voice notes to text
- **OCR Support**: Extract text from images and documents
- **Real-time Collaboration**: WebSocket-based live editing and notifications
- **Analytics**: User behavior tracking, performance metrics, and usage insights
- **Automation**: Workflow automation with triggers, actions, and scheduled tasks
- **Third-party Integrations**: Google Drive, Microsoft Graph, Slack, and more

### Mobile & Desktop
- **React Native Mobile App**: iOS and Android applications
- **Electron Desktop App**: Cross-platform desktop application
- **Progressive Web App**: Offline support and push notifications

### Enterprise Features
- **API**: RESTful API with GraphQL support and OAuth 2.0
- **Security**: HTTPS, CSP headers, rate limiting, and audit logging
- **Monitoring**: Prometheus metrics, Grafana dashboards, and ELK stack logging
- **Docker**: Containerized deployment with Docker Compose
- **CI/CD**: GitHub Actions pipeline with automated testing and deployment

## 🏗️ Architecture

### Technology Stack
- **Backend**: PHP 8.2 with PSR-4 autoloading
- **Database**: MySQL 8.0 with Redis caching
- **Frontend**: HTML5, CSS3, JavaScript (ES6+), Tailwind CSS
- **Mobile**: React Native with Expo
- **Desktop**: Electron with React
- **WebSocket**: ReactPHP for real-time features
- **Containerization**: Docker with multi-stage builds
- **Monitoring**: Prometheus, Grafana, ELK Stack

### Microservices Architecture
- **Web Application**: Main PHP application with Nginx
- **WebSocket Server**: Real-time communication service
- **Background Worker**: Job processing and scheduled tasks
- **Load Balancer**: Nginx with upstream configuration
- **Database**: MySQL with Redis cache
- **Monitoring**: Prometheus, Grafana, and ELK stack

## 📦 Installation

### Prerequisites
- Docker and Docker Compose
- PHP 8.2+ (for local development)
- MySQL 8.0+
- Redis 7.0+
- Node.js 18+ (for mobile/desktop apps)

### Quick Start with Docker

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/personal-notes-system.git
   cd personal-notes-system
   ```

2. **Start the application**
   ```bash
   # Production environment
   docker-compose up -d
   
   # Development environment
   docker-compose --profile development up -d
   
   # With monitoring
   docker-compose --profile monitoring up -d
   
   # With logging
   docker-compose --profile logging up -d
   ```

3. **Access the application**
   - Web Application: http://localhost
   - Development: http://localhost:8080
   - Grafana: http://localhost:3000 (admin/admin)
   - Prometheus: http://localhost:9090
   - Kibana: http://localhost:5601

### Manual Installation

1. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

3. **Setup database**
   ```bash
   mysql -u root -p < database/schema.sql
   php create_missing_tables.php
   ```

4. **Start services**
   ```bash
   # Start PHP development server
   php -S localhost:8000 -t public
   
   # Start WebSocket server
   php websocket_server.php
   
   # Start background worker
   php worker.php
   ```

## 🔧 Configuration

### Environment Variables
```bash
# Database
DB_HOST=localhost
DB_DATABASE=personal
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Redis
REDIS_HOST=localhost
REDIS_PORT=6379

# Security
APP_KEY=your_secret_key
JWT_SECRET=your_jwt_secret

# AI Services
AI_API_KEY=your_ai_api_key
AI_API_ENDPOINT=https://api.openai.com/v1/chat/completions

# Third-party Integrations
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
MICROSOFT_CLIENT_ID=your_microsoft_client_id
MICROSOFT_CLIENT_SECRET=your_microsoft_client_secret
SLACK_CLIENT_ID=your_slack_client_id
SLACK_CLIENT_SECRET=your_slack_client_secret
```

### Docker Configuration
- **Production**: Optimized for performance and security
- **Development**: Includes Xdebug and development tools
- **Monitoring**: Prometheus, Grafana, and ELK stack
- **Logging**: Centralized logging with Logstash

## 🧪 Testing

### Run Tests
```bash
# PHP Unit Tests
composer test

# Code Quality
composer cs-check
composer stan

# Integration Tests
composer test-integration

# Mobile Tests
cd mobile && npm test

# Desktop Tests
cd desktop && npm test
```

### Test Coverage
```bash
composer test-coverage
# View coverage report at coverage/html/index.html
```

## 📱 Mobile App Development

### Setup React Native
```bash
cd mobile
npm install
npx expo start
```

### Build for Production
```bash
# iOS
npx expo build:ios

# Android
npx expo build:android
```

## 🖥️ Desktop App Development

### Setup Electron
```bash
cd desktop
npm install
npm start
```

### Build for Production
```bash
# All platforms
npm run build

# Specific platform
npm run build:win
npm run build:mac
npm run build:linux
```

## 🚀 Deployment

### Production Deployment
```bash
# Build production images
docker-compose build --target production

# Deploy with monitoring
docker-compose --profile production --profile monitoring up -d

# Scale services
docker-compose up -d --scale app=3 --scale worker=2
```

### CI/CD Pipeline
The project includes a comprehensive GitHub Actions CI/CD pipeline:
- **Code Quality**: PHPStan, PHPCS, ESLint
- **Security**: Security checker and audit
- **Testing**: Unit, integration, and feature tests
- **Docker**: Build and test Docker images
- **Deployment**: Automated deployment to staging and production

## 📊 Monitoring

### Metrics
- **Application Metrics**: Response times, error rates, user activity
- **System Metrics**: CPU, memory, disk usage
- **Database Metrics**: Query performance, connection pools
- **Custom Metrics**: Business logic metrics and KPIs

### Dashboards
- **Grafana**: Pre-configured dashboards for application and system metrics
- **Kibana**: Log analysis and visualization
- **Prometheus**: Metrics collection and alerting

### Alerting
- **Error Rate**: Alert when error rate exceeds threshold
- **Response Time**: Alert when response time is too high
- **Resource Usage**: Alert when CPU/memory usage is high
- **Database**: Alert on slow queries or connection issues

## 🔒 Security

### Security Features
- **HTTPS**: SSL/TLS encryption
- **CSP Headers**: Content Security Policy
- **Rate Limiting**: API and login rate limiting
- **2FA**: Two-factor authentication
- **Audit Logging**: Security event logging
- **Input Validation**: XSS and SQL injection prevention

### Security Best Practices
- Regular security updates
- Dependency vulnerability scanning
- Secure coding practices
- Regular security audits

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Ensure all tests pass
- Follow semantic versioning

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: [Wiki](https://github.com/your-username/personal-notes-system/wiki)
- **Issues**: [GitHub Issues](https://github.com/your-username/personal-notes-system/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-username/personal-notes-system/discussions)
- **Email**: support@personal-notes.com

## 🙏 Acknowledgments

- PHP Community for excellent documentation and tools
- React Native team for mobile development framework
- Electron team for desktop application framework
- Docker team for containerization platform
- All contributors and users of this project

---

**Made with ❤️ by the Personal Notes System Team**
