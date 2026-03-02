# Varta SPA - Testing & Validation Checklist

## Pre-Deployment Testing

This document provides a comprehensive checklist to validate the Varta SPA application before deployment.

## 1. Environment Setup ✅

- [ ] PHP 8.0+ installed and CLI working
- [ ] MySQL/MariaDB running
- [ ] Apache with mod_rewrite enabled
- [ ] `.htaccess` file present in `/public/`
- [ ] Database tables created from `database/schema.sql`
- [ ] Session directory writable
- [ ] File uploads directory created and writable

## 2. Database Validation ✅

```sql
-- Verify all tables exist
SHOW TABLES;

-- Expected tables (13):
-- users, sessions, contacts, groups, group_members, 
-- messages, message_reads, typing_indicators, blocked_users,
-- notifications, otp_backups, audit_log
```

- [ ] All 13 tables created
- [ ] Primary keys present on all tables
- [ ] Foreign keys properly configured
- [ ] Indexes created for performance
- [ ] Test data inserted (optional)

## 3. Backend API Testing 🔧

### Authentication Endpoints

```bash
# Test signup
curl -X POST "http://localhost:8000/api/v1/auth.php?action=register" \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","email":"test@test.com","password":"Test123!"}'

# Test login
curl -X POST "http://localhost:8000/api/v1/auth.php?action=login" \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","password":"Test123!"}'

# Expected response:
# {
#   "success": true,
#   "message": "Login successful",
#   "data": {
#     "token": "eyJhbGc...",
#     "user_id": 1,
#     "username": "testuser"
#   }
# }
```

- [ ] Signup creates user account
- [ ] Signup validates email format
- [ ] Signup prevents duplicate usernames
- [ ] Login with correct credentials returns token
- [ ] Login with incorrect password fails
- [ ] Password hashing works (ARGON2ID)
- [ ] TOTP setup generates QR code
- [ ] TOTP verification validates code

### Messages Endpoints

```bash
# Get conversations
curl -X GET "http://localhost:8000/api/v1/messages.php?action=get-conversation&page=1&limit=20" \
  -H "Authorization: Bearer {token}"

# Send message
curl -X POST "http://localhost:8000/api/v1/messages.php?action=send" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"conversation_id":1,"type":"text","content":"Hello"}'
```

- [ ] Fetch conversations returns paginated list
- [ ] Send message stores in database
- [ ] Edit message updates content
- [ ] Delete message soft-deletes
- [ ] Mark read updates status
- [ ] Pagination works correctly
- [ ] Message validation prevents empty messages

### Users Endpoints

```bash
# Get profile
curl -X GET "http://localhost:8000/api/v1/users.php?action=profile" \
  -H "Authorization: Bearer {token}"

# Search users
curl -X GET "http://localhost:8000/api/v1/users.php?action=search&query=test" \
  -H "Authorization: Bearer {token}"
```

- [ ] Profile returns correct user data
- [ ] Search returns matching users
- [ ] Add contact creates relationship
- [ ] Remove contact deletes relationship
- [ ] Block user prevents messaging
- [ ] Status updates correctly
- [ ] Contacts pagination works

### Groups Endpoints

```bash
# Create group
curl -X POST "http://localhost:8000/api/v1/groups.php?action=create" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Group","description":"Test","member_ids":[2,3]}'
```

- [ ] Create group stores data
- [ ] Add member succeeds
- [ ] Remove member succeeds
- [ ] Get members returns paginated list
- [ ] Only admin can update group
- [ ] Delete group removes all data

### Notifications Endpoints

```bash
# Get notifications
curl -X GET "http://localhost:8000/api/v1/notifications.php?action=list&page=1&limit=20" \
  -H "Authorization: Bearer {token}"
```

- [ ] List returns paginated notifications
- [ ] Mark read updates status
- [ ] Delete removes notification
- [ ] Unread count accurate
- [ ] Notifications created for new messages

## 4. Frontend SPA Testing 🎨

### Authentication Flow

- [ ] Login page displays correctly
- [ ] Signup page displays correctly
- [ ] Tab switching works (Login <-> Signup)
- [ ] Form validation shows errors
- [ ] Login with valid creds shows app
- [ ] Login with invalid creds shows error
- [ ] TOTP verification modal appears
- [ ] TOTP code validation works
- [ ] Logout clears session

### Navigation & Routing

- [ ] Tab buttons change views
- [ ] URL stays at root (/)
- [ ] Back/forward buttons work
- [ ] Direct URL navigation works
- [ ] Conversation click loads messages
- [ ] No page reloads during navigation

### Messaging Interface

- [ ] Conversations list displays
- [ ] Click conversation loads messages
- [ ] Messages display in order (newest last)
- [ ] Send message works
- [ ] Message appears immediately (optimistic)
- [ ] Edit message updates content
- [ ] Delete message removes it
- [ ] Pagination loads older messages
- [ ] Typing indicator shows
- [ ] Read status updates
- [ ] Unread badge shows count

### Contacts Management

- [ ] Contacts list displays
- [ ] Search finds users
- [ ] Add contact succeeds
- [ ] Remove contact works
- [ ] Block user prevents messaging
- [ ] Unblock user restores messaging
- [ ] Status shows online/offline
- [ ] Pagination works

### Groups

- [ ] Groups list displays
- [ ] Create group form shows
- [ ] Select members works
- [ ] Group created successfully
- [ ] Group messages display
- [ ] Add member succeeds
- [ ] Remove member works
- [ ] Member count updates

### Settings

- [ ] Settings panel displays user info
- [ ] Status buttons work (online/away/offline)
- [ ] Preferences save
- [ ] Logout button works and clears data
- [ ] About section displays version

## 5. Performance Testing ⚡

### Load Testing

- [ ] App loads in < 2 seconds
- [ ] Message loading in < 500ms
- [ ] Contact search in < 1 second
- [ ] No memory leaks (check DevTools)
- [ ] CPU usage stays low

### Pagination

- [ ] Initial load: 30 messages
- [ ] Scroll up loads older messages
- [ ] Pagination metadata correct
- [ ] No duplicate messages
- [ ] No missing messages

### Real-Time Features

- [ ] Messages poll every 3 seconds
- [ ] New messages appear
- [ ] Typing indicators appear/disappear
- [ ] Read receipts update
- [ ] Unread badges update

## 6. UI/UX Testing 👁️

### Visual Design

- [ ] Color scheme consistent
- [ ] Font sizes readable
- [ ] Spacing/padding correct
- [ ] Buttons accessible
- [ ] Icons clear and meaningful
- [ ] Dark theme works (if enabled)

### Responsive Design

- [ ] Desktop (1920x1080) layout correct
- [ ] Tablet (768px) layout works
- [ ] Mobile (375px) layout adapts
- [ ] Scrollbars appear when needed
- [ ] Sidebar doesn't overflow

### Accessibility

- [ ] Tab navigation works
- [ ] Form labels present
- [ ] Keyboard shortcuts work
- [ ] Color contrast sufficient
- [ ] Images have alt text
- [ ] ARIA labels present

## 7. Security Testing 🔒

### Input Validation

- [ ] SQL injection prevented (prepared statements)
- [ ] XSS prevented (HTML escaping)
- [ ] CSRF tokens validated
- [ ] File upload size limits
- [ ] File type validation

### Authentication

- [ ] Tokens expire correctly
- [ ] Token refresh works
- [ ] Logout clears tokens
- [ ] Unauthorized requests fail
- [ ] Password reset works

### Data Protection

- [ ] Passwords hashed (not plain text)
- [ ] Sensitive data not exposed
- [ ] No passwords in logs
- [ ] HTTPS enforced (production)
- [ ] Secure cookies (HttpOnly, Secure flags)

## 8. Browser Compatibility 🌐

- [ ] Chrome 90+
- [ ] Firefox 88+
- [ ] Safari 14+
- [ ] Edge 90+
- [ ] Mobile browsers (iOS Safari, Chrome Android)

## 9. Error Handling 📢

### User Errors

- [ ] Invalid login shows message
- [ ] Empty form shows validation
- [ ] Network error shows retry
- [ ] Server error shows message
- [ ] Timeout shows appropriate message

### Developer Errors

- [ ] Console shows no errors
- [ ] Network tab shows valid responses
- [ ] Database errors don't leak info
- [ ] API errors return proper status codes
- [ ] Logs are detailed for debugging

## 10. Integration Testing 🔗

### Full User Workflow

1. [ ] New user signs up
2. [ ] User logs in
3. [ ] Add contacts
4. [ ] Send message to contact
5. [ ] Create group
6. [ ] Add members to group
7. [ ] Send group message
8. [ ] Search for user
9. [ ] Block user
10. [ ] Logout

### Data Consistency

- [ ] Messages appear in both conversations
- [ ] Contact relationships bidirectional
- [ ] Read status consistent
- [ ] Typing indicators clear
- [ ] Notifications sent correctly

## 11. Deployment Readiness ✈️

### Before Going Live

- [ ] All tests pass
- [ ] No console errors
- [ ] Performance acceptable
- [ ] Security audit complete
- [ ] Database backups working
- [ ] Logging configured
- [ ] Error reporting enabled
- [ ] Monitoring set up
- [ ] Caching enabled
- [ ] Minification applied

### Production Checklist

- [ ] HTTPS configured
- [ ] Database credentials secured
- [ ] Session management hardened
- [ ] Rate limiting enabled
- [ ] DDoS protection enabled
- [ ] Backup schedule configured
- [ ] Monitoring alerts set
- [ ] Incident response plan ready

## 12. Smoke Tests (Post-Deployment)

```bash
# Quick validation after deployment
curl -s "https://varta-n.unaux.com/api/v1/auth.php" | grep -q "error" && echo "API working"

# Test API endpoint
curl -s "https://varta-n.unaux.com/api/v1/auth.php?action=login" \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"test"}' | jq .

# Test SPA loads
curl -s "https://varta-n.unaux.com/" | grep -q "Varta" && echo "SPA loaded"
```

- [ ] API responds
- [ ] Database connected
- [ ] SPA loads
- [ ] Static assets served
- [ ] HTTPS working
- [ ] Redirects working

## 13. Monitoring & Analytics

After deployment, monitor:

- [ ] Server uptime
- [ ] Response times
- [ ] Error rates
- [ ] User metrics
- [ ] Database performance
- [ ] Cache hit rates
- [ ] Disk space usage
- [ ] Memory usage

## Test Execution

### Running Tests

```bash
# Manual testing flow:
1. Start PHP server: php -S localhost:8000
2. Open browser: http://localhost:8000/public/app.php
3. Follow test cases above
4. Check browser console for errors
5. Check server logs for issues
6. Verify database records created
```

### Automated Testing (Future)

```javascript
// Example Jest test
describe('LoginForm', () => {
  it('should login with valid credentials', async () => {
    // Test implementation
  });

  it('should show error with invalid credentials', async () => {
    // Test implementation
  });
});
```

## Known Issues & Workarounds

None currently documented. Issues will be tracked in GitHub Issues.

## Sign-Off

- [ ] All tests passed
- [ ] No critical bugs
- [ ] Performance acceptable
- [ ] Security validated
- [ ] Ready for production

**Tested By**: ________________  
**Date**: ________________  
**Environment**: ________________  
**Notes**: ________________________________________________

---

**For issues or questions**, please create an issue in the project repository.
