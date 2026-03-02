# Varta - Modern Messaging Platform

## Overview

Varta is a modern, feature-rich messaging application built with a Single Page Application (SPA) architecture. The application provides real-time messaging, group conversations, contact management, and more - all with a WhatsApp/Telegram-like user interface.

## Architecture

### Frontend (SPA - Single Page Application)

The frontend is a modern SPA that runs entirely on the client-side with server-side API support:

- **Location**: `/public/app.php` (main entry point)
- **Architecture Pattern**: Client-side routing with HTML5 History API
- **Styling**: Custom CSS with WhatsApp/Telegram theme (`/public/css/spa.css`)

#### JavaScript Modules (`/public/js/`)

1. **api-client.js** - HTTP client for all API requests
   - Handles authentication tokens
   - Standardized request/response handling
   - Methods for all 7 API endpoints

2. **router.js** - Client-side routing engine
   - HTML5 History API for navigation
   - URL masking (always stays at root domain)
   - Tab-based navigation system
   - Route handler registration

3. **auth.js** - Authentication manager
   - Login/signup form handling
   - TOTP (Two-Factor Authentication) verification
   - Session management
   - Error displays

4. **chat.js** - Messaging system
   - Load conversations and messages
   - Send/edit/delete messages
   - Real-time message polling (3-second interval)
   - Typing indicators
   - Message read status tracking
   - Pagination support for message history

5. **contacts.js** - Contact management
   - Load contact list
   - Search for users
   - Add/remove contacts
   - Block/unblock users
   - Status tracking

6. **groups.js** - Group conversation management
   - Create groups
   - Group CRUD operations
   - Member management
   - Group messaging

7. **spa.js** - Main application coordinator
   - Initializes all modules
   - Settings panel
   - User profile
   - Status management
   - Logout functionality

### Backend (API Microservices)

The backend consists of RESTful microservices located in `/api/v1/`:

#### Core Components

1. **response.php** - Standardized API response handler
   - `success()` - Returns success response with data
   - `error()` - Returns error response with message
   - `paginated()` - Returns paginated data with metadata
   - Helper functions: `requireAuth()`, `getJsonInput()`, `sanitize()`

2. **auth.php** - Authentication microservice
   - `login` - User login with credentials
   - `register` - New user signup
   - `verify-otp` - TOTP verification for 2FA
   - `refresh-token` - JWT token refresh
   - `logout` - User logout

3. **messages.php** - Messaging microservice
   - `fetch` - Get messages for conversation (paginated)
   - `send` - Send new message
   - `edit` - Edit existing message
   - `delete` - Delete message
   - `get-conversation` - Get list of user conversations
   - `mark-read` - Mark message as read
   - `set-typing` - Send typing indicator

4. **users.php** - User management microservice
   - `profile` - Get current user's profile
   - `update-profile` - Update user information
   - `contacts` - Get user's contacts list
   - `search` - Search for users
   - `get-user` - Get specific user details
   - `add-contact` - Add a contact
   - `remove-contact` - Remove a contact
   - `block-user` - Block a user
   - `unblock-user` - Unblock a user
   - `set-status` - Set user online status

5. **groups.php** - Group management microservice
   - `list` - List user's groups
   - `create` - Create new group
   - `get` - Get group details
   - `update` - Update group information
   - `delete` - Delete a group
   - `add-member` - Add member to group
   - `remove-member` - Remove member from group
   - `get-members` - Get group members with pagination

6. **notifications.php** - Notification system
   - `list` - Get user's notifications
   - `mark-read` - Mark notification as read
   - `delete` - Delete notification
   - `unread-count` - Get count of unread notifications

### Database Schema

The database consists of 13 tables with proper relationships:

- **users** - User accounts with authentication credentials
- **sessions** - User session management
- **contacts** - User contact relationships
- **groups** - Group conversation metadata
- **group_members** - Group membership with roles
- **messages** - Individual messages in conversations
- **message_reads** - Message read status tracking
- **typing_indicators** - Real-time typing status
- **blocked_users** - User blocking relationships
- **notifications** - User notifications
- **otp_backups** - 2FA backup codes
- **audit_log** - System audit trail

All tables include proper indexes and foreign key constraints with cascade deletes.

## Features

### Authentication & Security
- ✅ Login/signup with email or username
- ✅ TOTP (Google Authenticator) 2FA
- ✅ JWT token-based authentication
- ✅ Password hashing with ARGON2ID
- ✅ Session management
- ✅ Prepared statements (SQL injection prevention)

### Messaging
- ✅ Direct messaging with contacts
- ✅ Message pagination with lazy loading
- ✅ Edit and delete messages
- ✅ Message read status tracking
- ✅ Typing indicators
- ✅ Real-time updates via polling
- ✅ Message drafts (client-side)

### Contacts
- ✅ Add/remove contacts
- ✅ Search users
- ✅ Block/unblock users
- ✅ User online status
- ✅ Contact list with pagination

### Groups
- ✅ Create groups
- ✅ Add/remove members
- ✅ Role-based access (admin/moderator/member)
- ✅ Group messaging
- ✅ Member management
- ✅ Group info/settings

### User Experience
- ✅ WhatsApp/Telegram-like UI
- ✅ SPA with client-side routing
- ✅ No page reloads during navigation
- ✅ Responsive design
- ✅ Dark theme support
- ✅ Loading states and error handling
- ✅ Real-time notifications

## Technology Stack

### Frontend
- **Language**: Vanilla JavaScript (ES6+)
- **Styling**: Custom CSS with CSS Variables
- **Routing**: HTML5 History API
- **HTTP Client**: Fetch API
- **Testing**: Browser DevTools

### Backend
- **Language**: PHP 8.x
- **Database**: MySQL/MySQLi
- **Authentication**: JWT + Sessions
- **2FA**: TOTP with PHPGangsta GoogleAuthenticator
- **Security**: Prepared Statements, Password Hashing (ARGON2ID)

### Libraries
- [Firebase PHP JWT](https://github.com/firebase/php-jwt) - JWT token handling
- [PHPGangsta GoogleAuthenticator](https://github.com/PHPGangsta/GoogleAuthenticator) - TOTP generation
- [spomky-labs/otphp](https://github.com/spomky-labs/otphp) - OTP handling

## Installation & Setup

### Prerequisites
- PHP 8.0+
- MySQL 5.7+
- Apache with mod_rewrite (for URL masking)
- Composer (for dependencies)

### Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd Varta
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Setup database**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

4. **Configure environment**
   - Update `/resources/db.php` with database credentials
   - Ensure `/public/.htaccess` is properly configured

5. **Start development server**
   ```bash
   php -S localhost:8000
   ```

6. **Access application**
   - Navigate to `http://localhost:8000/public/app.php`
   - Or `https://varta-n.unaux.com/` for production

## API Documentation

### Request Format

All API endpoints follow this pattern:

```
POST /api/v1/{endpoint}.php?action={action}
```

### Response Format

All responses are JSON with this structure:

```json
{
  "success": true,
  "message": "Operation successful",
  "timestamp": "2024-01-01T12:00:00Z",
  "data": {
    // Endpoint-specific data
  }
}
```

### Pagination

List endpoints support pagination:

```
GET /api/v1/messages.php?action=fetch&page=1&limit=30
```

Response includes pagination metadata:

```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total_items": 150,
    "items_per_page": 30
  }
}
```

### Authentication

Include JWT token in header:

```
Authorization: Bearer {token}
```

## URL Masking

The application uses `.htaccess` to keep the URL at the root domain:

- User navigates to `/messages/123`
- Browser address bar shows `/`
- Server internally loads `/app.php`
- Client-side router handles the view change

This is achieved through Apache's mod_rewrite:

```apache
RewriteRule ^(.*)$ /app.php?path=$1 [QSA,L]
```

## Real-Time Features

The application uses polling for real-time updates:

- **Message polling**: Every 3 seconds, fetch new messages
- **Typing indicators**: Sent when user types, cleared after 2 seconds
- **Unread badges**: Update when new messages arrive
- **Online status**: May be extended to WebSocket for true real-time

## Performance Optimization

- **Message pagination**: Load messages in batches of 30
- **Contact pagination**: Load contacts in batches of 50
- **Image lazy loading**: Avatars loaded on demand
- **CSS optimization**: Minified and optimized styles
- **JavaScript optimization**: Modular, tree-shakeable code
- **HTTP caching**: Static assets cached for 1 year

## Security Considerations

- ✅ All user input is sanitized
- ✅ All database queries use prepared statements
- ✅ HTTPS enforced in production
- ✅ CORS headers configured
- ✅ CSRF protection via session tokens
- ✅ XSS prevention via output escaping
- ✅ Passwords hashed with ARGON2ID
- ✅ JWT tokens have expiration

## Development Workflow

### Adding New Features

1. **Plan database changes** in `/database/schema.sql`
2. **Create API endpoint** in `/api/v1/{entity}.php`
3. **Add API method** to `ApiClient` class in `api-client.js`
4. **Create UI component** in appropriate JS module
5. **Add styling** to `/public/css/spa.css`
6. **Test thoroughly** in browser

### Debugging

- **API errors**: Check browser Network tab and server logs
- **UI issues**: Check browser Console for JavaScript errors
- **Database issues**: Check error logs in session data
- **Routing issues**: Verify route registration in `router.js`

## Future Enhancements

- [ ] WebSocket support for true real-time messaging
- [ ] File uploading and sharing
- [ ] Voice/video calling
- [ ] Message reactions and emojis
- [ ] Advanced search with filters
- [ ] Dark mode toggle
- [ ] Multi-language support (i18n)
- [ ] Mobile app (React Native)
- [ ] End-to-end encryption

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write/update tests
5. Submit a pull request

## License

This project is licensed under the MIT License - see LICENSE file for details.

## Support

For support, documentation, or feature requests:
- Email: support@varta.local
- GitHub Issues: [Create an issue](https://github.com/varta/varta/issues)

---

**Varta v1.0** - Modern Messaging Platform built with PHP & Vanilla JavaScript
