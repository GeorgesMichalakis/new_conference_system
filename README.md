# Conference Paper Submission System

A modern PHP-based web application for managing academic conference paper submissions and peer reviews. Built with containerization using Podman for easy deployment and scalability.

## Overview

This comprehensive conference management system provides:
- **Authors** can submit papers, track review status, and manage revisions
- **Reviewers** can evaluate assigned papers and provide detailed feedback
- **Administrators** have full control over the conference workflow and user management

## Key Features

### For Authors
- Submit papers with metadata (title, abstract, keywords)
- Upload PDF/DOC/DOCX files (up to 10MB)
- Real-time submission status tracking
- View detailed reviewer feedback
- Submit paper revisions

### For Reviewers  
- Dashboard with assigned papers
- Comprehensive review forms with scoring
- Download and review paper files
- Submit recommendations (accept/reject/revise)
- Track review deadlines

### For Administrators
- Complete user and role management
- Smart reviewer assignment system
- Conference statistics and analytics
- Final decision workflow
- System configuration controls

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


## Usage Guide

### For Authors
1. **Register**: Create account or login with demo credentials
2. **Submit Paper**: Use the submission form with:
   - Paper title and abstract
   - Keywords and categories
   - Upload PDF/DOC file (max 10MB)
3. **Track Status**: Monitor review progress on dashboard
4. **View Reviews**: Access reviewer feedback and ratings
5. **Submit Revisions**: Upload updated versions if requested

### For Reviewers  
1. **Login**: Access reviewer dashboard
2. **View Assignments**: See papers assigned for review
3. **Download Papers**: Access submitted documents
4. **Submit Reviews**: Complete evaluation forms with:
   - Technical quality assessment
   - Originality and significance ratings
   - Detailed comments and suggestions
   - Final recommendation (Accept/Reject/Major Revision/Minor Revision)

### For Administrators
1. **User Management**: Create, edit, and manage user accounts
2. **Paper Oversight**: Monitor all submitted papers and their status  
3. **Reviewer Assignment**: Assign papers to appropriate reviewers
4. **Final Decisions**: Make acceptance/rejection decisions based on reviews
5. **System Analytics**: View conference statistics and reports