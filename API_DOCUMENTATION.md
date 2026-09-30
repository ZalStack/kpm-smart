# KPM Smart API Documentation

**Base URL:** `http://localhost:8000`

**Authentication:** None (all endpoints are currently publicly accessible)

**Content Type:** `application/json` for API endpoints, `multipart/form-data` for file uploads

---

## Table of Contents

1. [Public Pages](#public-pages)
2. [Authentication](#authentication)
3. [Admin - Dashboard](#admin---dashboard)
4. [Admin - Users Management](#admin---users-management)
5. [Admin - Packages Management](#admin---packages-management)
6. [Admin - Practice Statistics](#admin---practice-statistics)
7. [Admin - Announcements](#admin---announcements)
8. [Admin - Leave Requests](#admin---leave-requests)
9. [Admin - Login Logs](#admin---login-logs)
10. [Admin - Notifications](#admin---notifications)
11. [Admin - Profile](#admin---profile)
12. [User - Dashboard](#user---dashboard)
13. [User - Packages](#user---packages)
14. [User - Practice](#user---practice)
15. [User - Gamification](#user---gamification)
16. [User - Leave Requests](#user---leave-requests)
17. [User - Notifications](#user---notifications)
18. [User - Announcements](#user---announcements)
19. [User - Profile](#user---profile)
20. [Push Notifications](#push-notifications)
21. [API - Admin](#api---admin)
22. [API - User](#api---user)
23. [API - Auth](#api---auth)
24. [Response Format](#response-format)
25. [Notes](#notes)

---

## Public Pages

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Redirect to login page |  
| GET | `/fitur` | Features page |
| GET | `/panduan` | User guide page |
| GET | `/faq` | FAQ page |

---

## Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/login` | Show login form |
| POST | `/login` | Process login |
| POST | `/logout` | Process logout |
| GET | `/forgot-password` | Show forgot password form |
| POST | `/forgot-password` | Send password reset link |
| GET | `/forgot-password/sent` | Confirmation page after reset link sent |
| GET | `/forgot-password/reset/{token}` | Show reset password form |
| POST | `/forgot-password/reset` | Process password reset |

---

## Admin - Dashboard

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/dashboard` | Admin dashboard with statistics |

---

## Admin - Users Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/users` | List all users (with search, filter by status/bidang/level/kelas) |
| GET | `/admin/users/create` | Show create user form |
| POST | `/admin/users` | Create new user |
| GET | `/admin/users/{user}` | Show user details |
| GET | `/admin/users/{user}/edit` | Show edit user form |
| PUT | `/admin/users/{user}` | Update user data |
| DELETE | `/admin/users/{user}` | Delete user |
| POST | `/admin/users/{user}/toggle-active` | Toggle user active status |
| PUT | `/admin/users/{user}/update-level` | Update user level (AJAX inline edit) |
| GET | `/admin/users/import-excel` | Show Excel import form |
| POST | `/admin/users/import-excel` | Process Excel import |
| POST | `/admin/users/reset-imported` | Delete all imported users |

### User Level Values

| Level | Description |
|-------|-------------|
| `A1` | Beginner |
| `A2` | Elementary |
| `B1` | Intermediate |
| `B2` | Upper Intermediate |
| `C1` | Advanced |
| `C2` | Proficient |

### Excel Import Format

| Column | Required | Description |
|--------|----------|-------------|
| Nama | Yes | Full name |
| Kelas | No | Class (1-12) |
| Bidang | No | Field of study (e.g., MATEMATIKA, IPA) |
| Asal Sekolah | No | School name |
| Password | No | Password (default: "password") |
| Level | No | Level (A1, A2, B1, B2, C1, C2) |

---

## Admin - Packages Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/packages` | List all packages |
| GET | `/admin/packages/create` | Show create package form |
| POST | `/admin/packages` | Create new package |
| GET | `/admin/packages/{package}` | Show package details |
| GET | `/admin/packages/{package}/edit` | Redirect to edit info page |
| GET | `/admin/packages/{package}/edit/informasi` | Edit package information |
| GET | `/admin/packages/{package}/edit/cards` | Edit package cards |
| GET | `/admin/packages/{package}/edit/questions` | Edit package questions |
| PUT | `/admin/packages/{package}` | Update package |
| DELETE | `/admin/packages/{package}` | Delete package |
| GET | `/admin/packages/{package}/confirm-delete` | Confirm delete (redirects to index) |
| POST | `/admin/packages/{package}/cards` | Add new card |
| DELETE | `/admin/packages/{package}/cards/{cardId}` | Remove card |
| GET | `/admin/packages/{package}/questions/create` | Show create question form |
| GET | `/admin/packages/{package}/questions/{questionId}/edit` | Show edit question form |
| POST | `/admin/packages/{package}/questions` | Add new question |
| PUT | `/admin/packages/{package}/questions/{questionId}` | Update question |
| DELETE | `/admin/packages/{package}/questions/{questionId}` | Delete question |
| GET | `/admin/packages/{package}/import-pdf` | Show PDF import form |
| POST | `/admin/packages/{package}/import-pdf` | Import questions from PDF |
| POST | `/admin/packages/{package}/ajax/toggle-setting` | Toggle package settings (AJAX) |
| POST | `/admin/packages/{package}/ajax/update-schedule` | Update schedule (AJAX) |
| POST | `/admin/packages/{package}/upload-image` | Upload image for question |

---

## Admin - Practice Statistics

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/practice-statistics` | Practice statistics dashboard |
| GET | `/admin/practice-statistics/export/excel` | Export statistics to Excel |
| GET | `/admin/practice-statistics/export/pdf` | Export statistics to PDF |
| GET | `/admin/practice-statistics/{session}` | Show session details |

---

## Admin - Announcements

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/announcements` | List all announcements |
| POST | `/admin/announcements` | Create new announcement |
| GET | `/admin/announcements/{announcement}` | Show announcement details |
| DELETE | `/admin/announcements/{announcement}` | Delete announcement |
| POST | `/admin/announcements/{announcement}/toggle` | Toggle announcement active status |

---

## Admin - Leave Requests

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/leave-requests` | List all leave requests |
| GET | `/admin/leave-requests/{id}` | Show leave request details |
| PUT | `/admin/leave-requests/{id}/status` | Update leave request status |
| DELETE | `/admin/leave-requests/{id}` | Delete leave request |

---

## Admin - Login Logs

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/login-logs` | List all login logs |

---

## Admin - Notifications

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/notifications` | List all notifications |
| POST | `/admin/notifications/dropdown` | Get latest notifications (AJAX) |
| POST | `/admin/notifications/read-all` | Mark all notifications as read |
| GET | `/admin/notifications/unread-count` | Get unread notification count |
| POST | `/admin/notifications/{id}/read` | Mark notification as read |

---

## Admin - Profile

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/profile` | Show admin profile |
| PUT | `/admin/profile` | Update admin profile |
| GET | `/admin/profile/change-password` | Show change password form |
| PUT | `/admin/profile/change-password` | Update admin password |

---

## User - Dashboard

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/dashboard` | User dashboard |

---

## User - Packages

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/packages` | List all active packages |
| GET | `/packages/{package}` | Show package details |

---

## User - Practice

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/practice` | Redirect to practice history |
| GET | `/practice/history` | Practice history |
| GET | `/practice/statistics` | Practice statistics |
| GET | `/practice/start/{package}` | Redirect to package details |
| POST | `/practice/start/{package}` | Start practice session |
| POST | `/practice/submit/{session}` | Submit practice answers |
| POST | `/practice/save-answers/{session}` | Save answers (autosave) |
| GET | `/practice/submit/{session}` | Redirect to practice result |
| GET | `/practice/{session}` | Show practice session/result |
| GET | `/practice/{session}/certificate` | Download certificate PDF |

---

## User - Gamification

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/leaderboard` | Leaderboard |
| GET | `/analytics` | User analytics |

---

## User - Leave Requests

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/leave-requests` | List user leave requests |
| GET | `/leave-requests/create` | Show create leave request form |
| POST | `/leave-requests` | Submit leave request |

---

## User - Notifications

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notifications` | List all notifications |
| POST | `/notifications/dropdown` | Get latest notifications (AJAX) |
| POST | `/notifications/read-all` | Mark all notifications as read |
| GET | `/notifications/unread-count` | Get unread notification count |
| POST | `/notifications/{id}/read` | Mark notification as read |

---

## User - Announcements

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/announcements/{announcement}` | Show announcement details |

---

## User - Profile

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/profile` | Show user profile |
| PUT | `/profile` | Update user profile |
| GET | `/profile/change-password` | Show change password form |
| PUT | `/profile/change-password` | Update user password |

---

## Push Notifications

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/push/status` | Get push notification status |
| GET | `/push/vapid-key` | Get VAPID public key |
| POST | `/push/subscribe` | Subscribe to push notifications |
| POST | `/push/unsubscribe` | Unsubscribe from push notifications |
| POST | `/push/resubscribe` | Resubscribe to push notifications |

---

## API - Admin

**Base URL:** `/api/admin/v1`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/v1/dashboard` | Admin dashboard data |
| GET | `/api/admin/v1/users` | List all users |
| GET | `/api/admin/v1/users/{user}` | Get user details |
| GET | `/api/admin/v1/packages` | List all packages |
| GET | `/api/admin/v1/packages/{package}` | Get package details |
| GET | `/api/admin/v1/practice-statistics` | Practice statistics |
| GET | `/api/admin/v1/practice-statistics/{session}` | Session details |
| GET | `/api/admin/v1/announcements` | List announcements |
| GET | `/api/admin/v1/announcements/{announcement}` | Announcement details |
| GET | `/api/admin/v1/leave-requests` | List leave requests |
| GET | `/api/admin/v1/leave-requests/{id}` | Leave request details |
| GET | `/api/admin/v1/login-logs` | List login logs |
| GET | `/api/admin/v1/notifications` | List notifications |
| GET | `/api/admin/v1/notifications/unread-count` | Unread count |
| GET | `/api/admin/v1/profile` | Admin profile |

---

## API - User

**Base URL:** `/api/user/v1`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/user/v1/dashboard` | User dashboard data |
| GET | `/api/user/v1/packages` | List packages |
| GET | `/api/user/v1/packages/{package}` | Package details |
| GET | `/api/user/v1/practice/history` | Practice history |
| GET | `/api/user/v1/practice/statistics` | Practice statistics |
| GET | `/api/user/v1/practice/{session}` | Practice session details |
| GET | `/api/user/v1/leaderboard` | Leaderboard |
| GET | `/api/user/v1/analytics` | User analytics |
| GET | `/api/user/v1/announcements` | List announcements |
| GET | `/api/user/v1/announcements/{announcement}` | Announcement details |
| GET | `/api/user/v1/leave-requests` | List leave requests |
| GET | `/api/user/v1/notifications` | List notifications |
| GET | `/api/user/v1/notifications/unread-count` | Unread count |
| GET | `/api/user/v1/profile` | User profile |

---

## API - Auth

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/auth/login` | User login |
| POST | `/api/v1/auth/logout` | User logout |
| GET | `/api/v1/auth/me` | Get current user info |

---

## Response Format

All endpoints return Inertia.js responses (HTML) or JSON for AJAX endpoints.

### Success Response (JSON)

```json
{
    "success": true,
    "message": "Operation successful",
    "data": {}
}
```

### Error Response (JSON)

```json
{
    "success": false,
    "message": "Error description"
}
```

---

## Notes

- All endpoints are currently **publicly accessible** (no authentication required)
- File uploads use `multipart/form-data` encoding
- Excel import supports `.xlsx`, `.xls`, and `.csv` formats
- PDF import supports `.pdf` format with OCR fallback
- Image uploads support `.jpg`, `.jpeg`, `.png`, `.webp` formats
- Maximum file size for images: 2MB
- Maximum file size for Excel: 10MB
- Maximum file size for PDF: 2MB
- Maximum file size for ZIP: 20MB

---

## User Level Reference

| Level | CEFR Equivalent | Description |
|-------|-----------------|-------------|
| A1 | Beginner | Basic understanding of the language |
| A2 | Elementary | Can communicate in simple tasks |
| B1 | Intermediate | Can deal with most travel situations |
| B2 | Upper Intermediate | Can interact with fluency and spontaneity |
| C1 | Advanced | Can use language flexibly and effectively |
| C2 | Proficient | Can express oneself spontaneously and precisely |

---

## Package Fields

| Field | Type | Description |
|-------|------|-------------|
| title | string | Package title |
| description | text | Package description |
| kelas | string | Grade level (1-12) |
| bidang | string | Field of study |
| level | string | Difficulty level |
| is_active | boolean | Whether package is active |
| start_date | date | Start date for practice |
| end_date | date | End date for practice |
| start_time | time | Start time for practice |
| end_time | time | End time for practice |
| show_answer_key | boolean | Show answer key after submission |
| show_explanation | boolean | Show explanation after submission |
| show_score | boolean | Show score after submission |
| thumbnail | image | Package thumbnail image |
| cards | array | Array of card objects |
| questions | array | Array of question objects |

---

## Question Fields

| Field | Type | Description |
|-------|------|-------------|
| id | string | Unique question ID |
| card_id | string | Reference to card |
| question | text | Question text |
| type | string | Question type (pilihan_ganda / isian_singkat) |
| options | array | Array of answer options |
| correct_answer | string | Correct answer |
| explanation | text | Explanation for the answer |
| image | string | Optional image path |
| created_at | datetime | Creation timestamp |
| imported_from_pdf | boolean | Whether imported from PDF |

---

## User Fields

| Field | Type | Description |
|-------|------|-------------|
| id | integer | Unique user ID |
| name | string | Full name |
| email | string | Email address |
| password | string | Hashed password |
| phone | string | Phone number |
| student_name | string | Student name |
| student_class | string | Class (1-12) |
| bidang | string | Field of study |
| level | string | Level (A1, A2, B1, B2, C1, C2) |
| school_name | string | School name |
| address | text | Address |
| gender | string | Gender (Laki-laki / Perempuan) |
| religion | string | Religion |
| profile_photo | string | Profile photo path |
| role | string | Role (admin / user) |
| is_verified | boolean | Email verification status |
| is_active | boolean | Account active status |
| created_at | datetime | Creation timestamp |
| updated_at | datetime | Last update timestamp |

---

**Last Updated:** 2026-09-30

**Version:** 1.0.0
