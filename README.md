# AlingAi Pro - Enhanced AI Integration Platform

AlingAi Pro is a comprehensive AI integration platform that provides secure, efficient, and user-friendly access to advanced AI capabilities through a web interface. The platform features enhanced chat interfaces, user management, security monitoring, and enterprise-grade features.

## Features

- **Enhanced Chat Interface** - Advanced AI conversations with context preservation and suggestions
- **User Authentication** - Secure login, registration, and account management
- **Dashboard Analytics** - Monitor AI usage and system performance
- **Quantum Security** - Enterprise-grade security features and monitoring
- **API Integration** - Comprehensive API for seamless integration with other systems
- **Responsive Design** - Works on desktop, tablet, and mobile devices

## Getting Started

### Prerequisites

- PHP 7.4 or higher
- Web server (Apache, Nginx, or the built-in PHP server)
- Modern web browser

### Installation

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/AlingAi_pro.git
   cd AlingAi_pro
   ```

2. Run the development server:
   ```
   # Windows
   start_server.bat
   
   # Linux/Mac
   php -S localhost:3000 -t public
   ```

3. Access the application:
   ```
   http://localhost:3000
   ```

### Demo Accounts

For testing purposes, you can use the following accounts:

- **Regular User**
  - Email: demo@alingai.com
  - Password: demo123

- **Administrator**
  - Email: admin@alingai.com
  - Password: admin123

## Project Structure

```
AlingAi_pro/
 config/           # Configuration files
 public/           # Publicly accessible files
    api/          # API endpoints
       v1/       # API version 1
       v2/       # API version 2 (enhanced)
    assets/       # Static assets (CSS, JS, images)
    index.html    # Main landing page
    ...           # Other HTML pages
 src/              # Source code
    Auth/         # Authentication related code
    Controllers/  # Application controllers
    Middleware/   # Request middleware
    Models/       # Data models
    Services/     # Business logic services
    ...           # Other components
 storage/          # Application storage
 templates/        # Reusable templates
```

## API Documentation

The API is divided into two versions:

- **v1** - Basic API functionality
- **v2** - Enhanced API with advanced features

### Authentication

All protected endpoints require authentication using Bearer tokens:

```
Authorization: Bearer <token>
```

### Main Endpoints

- `/api/v1/auth/login` - User login
- `/api/v1/auth/register` - User registration
- `/api/v1/user/profile` - User profile data
- `/api/v1/chat/message` - Basic chat functionality
- `/api/v2/enhanced-chat/message` - Enhanced chat with context preservation

For detailed API documentation, see the [API Documentation](public/api-docs.html).

## Security Features

- JWT-based authentication
- Rate limiting
- CORS protection
- Input validation and sanitization
- XSS protection
- CSRF protection
- Session management
- Advanced logging and monitoring

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contact

For questions or support, please contact us at support@alingai.com.
