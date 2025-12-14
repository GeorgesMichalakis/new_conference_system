# Conference Paper Submission System

A complete PHP-based web application for managing academic conference paper submissions and peer reviews. Built with containerization using Podman for easy deployment and scalability.

## Overview

This comprehensive conference management system provides a complete workflow from paper submission through peer review to final acceptance decisions. All core features are implemented and production-ready.

**Implementation Status: ✅ 100% Complete (10/10 major features)**

## Key Features

### For Authors ✅
- **Submit Papers**: Upload papers with metadata (title, abstract, keywords, co-authors)
- **Edit Papers**: Modify papers before they enter review
- **Submit Revisions**: Upload revised versions after receiving feedback
- **Track Status**: Real-time submission status tracking
- **View Feedback**: See reviewer comments and editor decisions
- **Manage Profile**: Update personal information and research interests
- **File Upload**: PDF/DOC/DOCX files (up to 10MB)

### For Reviewers ✅
- **Review Papers**: Comprehensive multi-criteria review forms
- **Edit Reviews**: Modify submitted reviews before paper is finalized
- **Rating System**: Overall (1-10) + Technical/Novelty/Significance/Clarity (1-5)
- **Recommendations**: Strong Accept → Strong Reject scale
- **Dual Comments**: Public comments for authors + confidential notes for editors
- **Download Papers**: Secure file access for assigned papers
- **Track Deadlines**: View review due dates

### For Administrators ✅
- **Make Decisions**: Accept, request revisions, or reject papers based on reviews
- **View Paper Details**: Complete paper information with all reviews
- **User Management**: Full CRUD operations (create, edit, deactivate, reset passwords)
- **Assign Reviewers**: Smart reviewer assignment with deadline setting
- **Dashboard Statistics**: Paper counts, user stats, review progress
- **Complete Control**: Full system administration capabilities

### User Management Features ✅
- **Profile Management**: All users can update their information
- **Password Reset**: Forgot password with secure token-based reset
- **Change Password**: Update password with current password verification
- **Secure Authentication**: Bcrypt password hashing, session management

## Technology Stack

- **Backend**: PHP 8.1 with MVC architecture
- **Database**: MySQL 8.0 with optimized schema
- **Web Server**: Apache 2.4 with mod_rewrite
- **Containerization**: Podman Compose
- **Frontend**: Responsive HTML5/CSS3/JavaScript
- **Database Management**: phpMyAdmin interface

## Installation & Setup

### Prerequisites
- Podman (or Docker)
- podman-compose
- Git

### Quick Start

1. Start the system:
```bash
podman-compose up -d
```

2. Access the application:
- **Main Conference System**: http://localhost:8080
- **phpMyAdmin** (Database Management): http://localhost:8081

### 🎯 Demo Credentials

The system comes pre-configured with demo accounts:

- **Admin User**: 
  - Email: `admin@example.com`
  - Password: `admin123`
  - Access: Full system administration

- **Author User**: 
  - Email: `author@example.com`
  - Password: `author123`
  - Access: Paper submission and tracking

- **Reviewer User**: 
  - Email: `reviewer@example.com` 
  - Password: `reviewer123`
  - Access: Paper review and evaluation

## 🏗️ Project Architecture

```
new_conference_system/
├── src/                       # PHP Application Code
│   ├── index.php             # Main dashboard & landing page
│   ├── auth/                 # Authentication system
│   │   ├── login.php         # User login
│   │   ├── register.php      # User registration
│   │   └── logout.php        # Session termination
│   ├── author/               # Author functionality
│   │   ├── dashboard.php     # Author dashboard
│   │   ├── submit.php        # Paper submission
│   │   └── papers.php        # Paper management
│   ├── reviewer/             # Reviewer functionality
│   │   ├── dashboard.php     # Review assignments
│   │   ├── review.php        # Review submission
│   │   └── papers.php        # Assigned papers
│   ├── admin/                # Administrative tools
│   │   ├── dashboard.php     # Admin overview
│   │   ├── users.php         # User management
│   │   ├── papers.php        # Paper management
│   │   └── assignments.php   # Reviewer assignments
│   ├── includes/             # Shared components
│   │   ├── config.php        # Database & app config
│   │   ├── header.php        # Common header
│   │   └── footer.php        # Common footer
│   ├── assets/               # Static resources
│   │   ├── css/              # Stylesheets
│   │   ├── js/               # JavaScript files
│   │   └── images/           # Image assets
│   └── uploads/              # Paper file storage
├── database/                 # Database setup
│   └── init.sql             # Schema & sample data
├── docker-compose.yml       # Podman orchestration
├── Dockerfile              # PHP container definition
├── start.sh                # System startup script
├── stop.sh                 # System shutdown script
├── cleanup.sh              # Complete system cleanup
└── README.md               # This documentation
```


## 📁 Complete File Structure

See [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) for detailed feature documentation.

```
src/
├── index.php                    # Landing page
├── login.php, logout.php        # Authentication
├── register.php                 # User registration
├── profile.php                  # Profile management ✅ NEW
├── forgot_password.php          # Password reset ✅ NEW
├── admin/
│   ├── index.php               # Dashboard with statistics
│   ├── papers.php              # Paper management
│   ├── users.php               # User CRUD ✅ NEW
│   ├── assign_reviewers.php    # Reviewer assignment
│   ├── make_decision.php       # Paper decisions ✅ NEW
│   └── view_paper.php          # Paper details ✅ NEW
├── author/
│   ├── index.php               # Dashboard
│   ├── submit.php              # Paper submission
│   ├── edit.php                # Edit papers ✅ NEW
│   ├── revise.php              # Submit revisions ✅ NEW
│   └── view.php                # View paper
├── reviewer/
│   ├── index.php               # Dashboard
│   ├── review.php              # Submit review
│   ├── edit_review.php         # Edit reviews ✅ NEW
│   └── view.php, download.php  # View/download papers
├── includes/
│   ├── config.php              # Config & helpers
│   ├── header.php, footer.php  # Layout
└── uploads/                    # Paper files
```

## Usage Guide

### For Authors
1. **Register/Login**: Create account or use demo credentials
2. **Submit Paper**: 
   - Fill in title, abstract, keywords, co-authors
   - Upload PDF/DOC/DOCX (max 10MB)
   - Add category and conference track
3. **Edit Paper**: Modify before review starts (Edit button)
4. **Track Status**: Monitor on dashboard (Submitted → Under Review → Decision)
5. **Submit Revisions**: If required, upload revised version with notes
6. **Update Profile**: Manage your information and password

### For Reviewers  
1. **Login**: Access reviewer dashboard
2. **View Assignments**: See papers assigned with deadlines
3. **Download & Review**: Read papers thoroughly
4. **Submit Review**: 
   - Overall rating (1-10)
   - Criteria ratings: Technical, Novelty, Significance, Clarity (1-5)
   - Recommendation (Strong Accept → Strong Reject)
   - Comments for authors + confidential notes
5. **Edit Reviews**: Modify before paper is finalized

### For Administrators
1. **Dashboard**: View statistics and recent activity
2. **Manage Users**: Create, edit, deactivate users, reset passwords
3. **Assign Reviewers**: Select reviewers for each paper with deadlines
4. **View Papers**: See complete details with all reviews
5. **Make Decisions**: Accept, request revision, or reject based on reviews
6. **Track Progress**: Monitor review completion and paper status

## 📚 Documentation

### Essential Docs (Start Here)
- **[README.md](README.md)** (this file): Quick start and system overview
- **[QUICK_TEST_GUIDE.md](QUICK_TEST_GUIDE.md)** ⭐: Fast testing reference (20 min quick test)
- **[PROJECT_COMPLETE.md](PROJECT_COMPLETE.md)** 🎉: Final completion summary

### Detailed Documentation
- **[IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)**: Complete feature list and technical details
- **[USER_GUIDE.md](USER_GUIDE.md)**: Step-by-step instructions for all features
- **[TESTING_GUIDE.md](TESTING_GUIDE.md)**: Comprehensive test cases (200+ tests)
- **[CHANGELOG.md](CHANGELOG.md)**: Version history and changes

## Security Features

✅ **Authentication & Authorization**
- Bcrypt password hashing
- Role-based access control
- Session management with timeout
- Password reset with secure tokens

✅ **Input Validation & Sanitization**
- SQL injection prevention (PDO prepared statements)
- XSS protection (htmlspecialchars on all output)
- File upload validation (type, size, whitelist)

✅ **Data Integrity**
- Foreign key constraints
- Soft deletes (data preservation)
- Transaction support
- Audit timestamps

## What's Implemented

### ✅ Complete Features (8/10)
1. ✅ Admin decision-making system
2. ✅ Admin paper details view
3. ✅ Admin user management (CRUD)
4. ✅ Author paper editing
5. ✅ Author revision submission
6. ✅ Reviewer review editing
7. ✅ User profile management
8. ✅ Password reset system

### ✅ All Features Complete (10/10)
9. ✅ Dashboard statistics & charts
10. ✅ Advanced search/filter features

### 💡 Additional Ideas
- Email notifications (SMTP)
- Conflict of interest detection
- Bulk operations
- Export to CSV/Excel
- Two-factor authentication
- Activity logs and audit trail

## System Requirements

- **Podman** or Docker
- **podman-compose** or docker-compose
- 2GB RAM minimum
- 5GB disk space

## Troubleshooting

**Containers won't start?**
```bash
podman-compose down
podman-compose up -d
```

**Database connection error?**
- Wait 10-15 seconds for MySQL to initialize
- Check logs: `podman-compose logs db`

**Can't login?**
- Use default credentials from above
- Reset password via "Forgot Password" link

**File upload fails?**
- Check file size (max 10MB)
- Verify format (PDF, DOC, DOCX only)
- Ensure uploads/ directory is writable

## Contributing

This is a master's thesis project. For questions or contributions, please contact the project maintainer.

## License

Educational use only. Part of Master's Thesis project.

## Support

For detailed usage instructions, see [USER_GUIDE.md](USER_GUIDE.md)