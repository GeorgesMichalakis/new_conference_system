# Conference System - Complete Testing Guide

## 🎯 Purpose
This document provides comprehensive testing procedures for all implemented features in the Conference Paper Submission System. Follow these test cases to verify that every feature works correctly.

## 🚀 Pre-Testing Setup

### Start the System
```bash
cd /Users/gmichalakis/Workspace/diplomaLine/new_conference_system
podman-compose up -d
```

### Access Points
- **Main Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081 (for database inspection)

### Default Test Accounts
- **Admin**: admin@example.com / admin123
- **Author**: author@example.com / author123
- **Reviewer**: reviewer@example.com / reviewer123

---

## 📋 Test Cases by Feature

### 1. Authentication & User Management

#### 1.1 Login System
- [ ] **Test**: Navigate to http://localhost:8080/login.php
- [ ] **Test**: Login with admin credentials
  - Expected: Redirect to /admin/ dashboard
  - Expected: See "Welcome, [Admin Name]" in header
- [ ] **Test**: Login with wrong password
  - Expected: Error message "Invalid email or password"
- [ ] **Test**: Logout
  - Expected: Redirect to login page
  - Expected: Cannot access protected pages

#### 1.2 Registration
- [ ] **Test**: Navigate to http://localhost:8080/register.php
- [ ] **Test**: Register new user (use unique email)
  - Fill: Full name, email, password (min 6 chars)
  - Select role: Author or Reviewer
  - Expected: Success message and redirect to login
- [ ] **Test**: Register with existing email
  - Expected: Error "Email already registered"
- [ ] **Test**: Register with password < 6 characters
  - Expected: Error about password length

#### 1.3 Password Reset
- [ ] **Test**: Click "Forgot your password?" on login page
- [ ] **Test**: Enter valid email address
  - Expected: See reset link (for testing)
  - Expected: Message about instructions sent
- [ ] **Test**: Click reset link
  - Expected: Show password reset form
- [ ] **Test**: Enter new password (min 6 chars) and confirm
  - Expected: Success message
  - Expected: Redirect to login
- [ ] **Test**: Login with new password
  - Expected: Successful login
- [ ] **Test**: Try using expired token (wait 1 hour or manually expire in DB)
  - Expected: Error "Invalid or expired reset token"

#### 1.4 Profile Management
- [ ] **Test**: Login as any user, click "Profile" in header
- [ ] **Test**: Update profile information
  - Change: Full name, email, affiliation, website
  - Add: Research interests, biography
  - Expected: Success message "Profile updated successfully"
- [ ] **Test**: Change password
  - Enter: Current password, new password, confirm
  - Expected: Success message
  - Expected: Can login with new password
- [ ] **Test**: Try changing password with wrong current password
  - Expected: Error "Current password is incorrect"

---

### 2. Admin Features

#### 2.1 Admin Dashboard with Charts ✨ NEW
- [ ] **Test**: Login as admin, view dashboard
- [ ] **Test**: Verify statistics cards show:
  - Total papers with breakdown (Submitted, Under Review, Accepted, Rejected)
  - Total users (Authors, Reviewers)
  - Review statistics (Completed, Pending)
- [ ] **Test**: Verify 3 charts are displayed:
  - **Pie Chart**: Paper Status Distribution
  - **Line Chart**: Submissions Over Time (last 6 months)
  - **Bar Chart**: Review Progress
- [ ] **Test**: Verify Reviewer Workload table shows:
  - Reviewer names
  - Assigned, Completed, Pending counts
  - Completion rate percentage with color coding
- [ ] **Test**: Verify Recent Submissions table (last 5 papers)

#### 2.2 Paper Management with Search/Filter ✨ NEW
- [ ] **Test**: Navigate to "Manage Papers"
- [ ] **Test**: Search functionality
  - Enter paper title in search box
  - Expected: Only matching papers shown
  - **Test**: Search by author name
  - **Test**: Search by keywords
- [ ] **Test**: Filter by status
  - Select "Under Review"
  - Expected: Only papers with that status shown
- [ ] **Test**: Date range filter
  - Select "From Date" and "To Date"
  - Expected: Papers submitted in that range
- [ ] **Test**: Sortable columns
  - Click "Title" column header
  - Expected: Papers sorted alphabetically, arrow shows direction
  - **Test**: Click again
  - Expected: Reverse sort order
  - **Test**: Sort by "Submitted", "Status", "Reviews"
- [ ] **Test**: Export to CSV
  - Click "Export CSV" button
  - Expected: Download CSV file with paper data
  - **Test**: Open CSV in spreadsheet
  - Expected: See all paper details
- [ ] **Test**: Clear filters
  - Click "Clear" button
  - Expected: Reset to default view

#### 2.3 User Management (CRUD)
- [ ] **Test**: Navigate to "Manage Users"
- [ ] **Test**: Search users by name
  - Expected: Matching users shown
- [ ] **Test**: Filter by role (Admin/Author/Reviewer)
  - Expected: Only users with selected role shown
- [ ] **Test**: Filter by status (Active/Inactive)
- [ ] **Test**: Create new user
  - Click "Create New User"
  - Fill all fields (name, email, role, affiliation)
  - Expected: Success message
  - Expected: User appears in list
- [ ] **Test**: Edit user
  - Click "Edit" next to a user
  - Modify name, email, or affiliation
  - Expected: Success message
  - Expected: Changes reflected in list
- [ ] **Test**: Deactivate user
  - Click "Deactivate"
  - Expected: User marked as Inactive
  - Expected: Cannot login with deactivated account
- [ ] **Test**: Reset user password
  - Click "Reset Password"
  - Expected: Password set to "password123"
  - Expected: Can login with default password
- [ ] **Test**: Try to deactivate self
  - Expected: Error or button disabled

#### 2.4 Reviewer Assignment
- [ ] **Test**: Navigate to "Assign Reviewers"
- [ ] **Test**: Select a paper
  - Expected: See paper details
- [ ] **Test**: Assign 2-3 reviewers
  - Select reviewers from dropdown
  - Set deadline (future date)
  - Expected: Success message
- [ ] **Test**: View current assignments
  - Expected: See list of assigned reviewers
  - Expected: See deadline dates
- [ ] **Test**: Try assigning duplicate reviewer
  - Expected: Error or prevented

#### 2.5 View Paper Details
- [ ] **Test**: From papers list, click "View"
- [ ] **Test**: Verify paper information displayed:
  - Title, abstract, keywords, co-authors
  - Author details (name, email, institution)
  - Submission information
  - File details with download link
- [ ] **Test**: Verify reviews section shows:
  - All assigned reviewers
  - Completed reviews with ratings breakdown
  - Pending reviews
  - Confidential comments (admin only)
- [ ] **Test**: Download paper file
  - Expected: PDF/DOC file downloads
- [ ] **Test**: If decision made, verify decision info shown

#### 2.6 Make Decisions
- [ ] **Test**: Find paper with "Under Review" status
- [ ] **Test**: Click "Decide" button
- [ ] **Test**: Verify review summary displayed:
  - Average ratings calculated
  - Individual reviews with all criteria
  - Reviewer recommendations
  - Confidential comments
- [ ] **Test**: Make decision: Accept
  - Add comments for author
  - Submit decision
  - Expected: Paper status changes to "Accepted"
  - Expected: Author sees decision
- [ ] **Test**: Make decision: Revision Required
  - Expected: Paper status changes to "Revision Required"
  - Expected: Author can submit revision
- [ ] **Test**: Make decision: Reject
  - Expected: Paper status changes to "Rejected"

---

### 3. Author Features

#### 3.1 Submit Paper
- [ ] **Test**: Login as author, navigate to "Submit New Paper"
- [ ] **Test**: Fill submission form:
  - Title, abstract, keywords
  - Co-authors (optional)
  - Category, conference track
  - Upload file (PDF/DOC/DOCX, max 10MB)
- [ ] **Test**: Submit paper
  - Expected: Success message
  - Expected: Paper appears in "My Papers"
  - Expected: Status is "Submitted"
- [ ] **Test**: Try uploading file > 10MB
  - Expected: Error about file size
- [ ] **Test**: Try uploading invalid file type (.txt, .jpg)
  - Expected: Error about file type

#### 3.2 Edit Paper
- [ ] **Test**: From "My Papers", click "Edit" on submitted paper
- [ ] **Test**: Modify paper details:
  - Change title, abstract, keywords
  - Update co-authors
  - Replace file (optional)
- [ ] **Test**: Save changes
  - Expected: Success message
  - Expected: Changes reflected in paper view
- [ ] **Test**: Try editing paper "Under Review"
  - Expected: No "Edit" button or error

#### 3.3 Submit Revision
- [ ] **Test**: Wait for paper to receive "Revision Required" decision
- [ ] **Test**: Click "Revise" button (orange)
- [ ] **Test**: View editor feedback
  - Expected: See decision comments
- [ ] **Test**: Upload revised file
  - Select new PDF/DOC/DOCX file
  - Add revision notes explaining changes
- [ ] **Test**: Submit revision
  - Expected: Success message
  - Expected: Status changes to "Under Review"
  - Expected: Revision notes stored

#### 3.4 View Paper Status
- [ ] **Test**: Click "View" on any paper
- [ ] **Test**: Verify information shown:
  - Paper details and status
  - File download link
  - Reviews (if any, after completion)
  - Decision and comments (if decided)

---

### 4. Reviewer Features

#### 4.1 View Assigned Papers
- [ ] **Test**: Login as reviewer, view dashboard
- [ ] **Test**: Verify assigned papers table shows:
  - Paper titles (author blinded)
  - Submission dates
  - Review deadlines
  - Review status (Pending/Completed)
- [ ] **Test**: Papers sorted by deadline (soonest first)

#### 4.2 Submit Review
- [ ] **Test**: Click "Submit Review" on assigned paper
- [ ] **Test**: View paper details
  - Expected: Can download paper
  - Expected: Cannot see author identity
- [ ] **Test**: Fill review form:
  - Overall rating (1-10)
  - Technical quality, novelty, significance, clarity (1-5 each)
  - Recommendation (Strong Accept → Strong Reject)
  - Comments for authors
  - Confidential comments for editors
- [ ] **Test**: Submit review
  - Expected: Success message
  - Expected: Status changes to "Completed"
- [ ] **Test**: Validate ratings
  - Try invalid values (0, 11, etc.)
  - Expected: Validation error

#### 4.3 Edit Review
- [ ] **Test**: Click "Edit Review" on completed review
- [ ] **Test**: Modify any review fields:
  - Change ratings
  - Update recommendation
  - Modify comments
- [ ] **Test**: Save changes
  - Expected: Success message
  - Expected: "Last Updated" timestamp shown
- [ ] **Test**: Try editing after paper finalized
  - Expected: Error or edit disabled

#### 4.4 Download Papers
- [ ] **Test**: Click "View Paper" or "Download Paper"
- [ ] **Test**: Verify file downloads correctly
  - Expected: PDF/DOC opens properly

---

### 5. Cross-Feature Integration Tests

#### 5.1 Complete Submission → Review → Decision Workflow
- [ ] **Test**: Full workflow as multiple users:
  1. Author submits paper
  2. Admin assigns 2 reviewers
  3. Reviewers submit reviews
  4. Admin makes decision (Accept/Reject/Revise)
  5. If revision: Author submits revision
  6. Repeat review process

#### 5.2 Search Across Roles
- [ ] **Test**: Author searches their own papers
- [ ] **Test**: Reviewer finds assigned papers
- [ ] **Test**: Admin searches all papers

#### 5.3 Email/Password Changes
- [ ] **Test**: Change email in profile
  - Logout and login with new email
  - Expected: Successful login
- [ ] **Test**: Reset password, then change via profile
  - Expected: Both methods work

---

## 🔍 Advanced Feature Testing

### Dashboard Charts (Admin)
- [ ] **Test**: Submit several papers over time
  - Expected: Submissions chart updates
- [ ] **Test**: Change paper statuses
  - Expected: Status distribution pie chart updates
- [ ] **Test**: Complete reviews
  - Expected: Review progress bar chart updates
- [ ] **Test**: Assign papers to reviewers
  - Expected: Workload table updates with completion rates

### Search & Filter
- [ ] **Test**: Complex search
  - Search + Status filter + Date range
  - Expected: All filters apply simultaneously
- [ ] **Test**: Export filtered results
  - Apply filters, then export CSV
  - Expected: CSV contains only filtered papers
- [ ] **Test**: Sort with filters active
  - Expected: Sorting works on filtered results

---

## ⚠️ Error Handling Tests

### Authentication Errors
- [ ] **Test**: Access protected page without login
  - Expected: Redirect to login
- [ ] **Test**: Access admin page as author
  - Expected: Permission denied or redirect
- [ ] **Test**: Session timeout
  - Wait 30+ minutes idle
  - Try to access page
  - Expected: Redirect to login

### Form Validation Errors
- [ ] **Test**: Submit forms with missing required fields
  - Expected: Validation errors shown
- [ ] **Test**: Enter invalid email format
  - Expected: Email validation error
- [ ] **Test**: Enter mismatched passwords
  - Expected: Password confirmation error

### File Upload Errors
- [ ] **Test**: Upload file exceeding size limit
  - Expected: Clear error message
- [ ] **Test**: Upload unsupported file type
  - Expected: File type error
- [ ] **Test**: Submit form without file (when required)
  - Expected: Required file error

### Database Errors
- [ ] **Test**: Create user with duplicate email
  - Expected: Duplicate email error
- [ ] **Test**: Access non-existent paper ID
  - Expected: "Not found" error
- [ ] **Test**: Delete/deactivate user with dependencies
  - Expected: Soft delete (data preserved)

---

## 📊 Performance & UI Tests

### Page Load Performance
- [ ] **Test**: Dashboard loads in < 2 seconds
- [ ] **Test**: Paper list with 50+ papers loads quickly
- [ ] **Test**: Charts render without delay

### Responsive Design
- [ ] **Test**: Open on mobile device or resize browser
  - Navigation menu works
  - Tables are scrollable
  - Forms are usable
  - Charts resize appropriately

### Browser Compatibility
- [ ] **Test**: Chrome (latest)
- [ ] **Test**: Firefox (latest)
- [ ] **Test**: Safari (latest)
- [ ] **Test**: Edge (latest)

---

## 🗄️ Database Verification Tests

### Using phpMyAdmin (http://localhost:8081)
- [ ] **Test**: Verify papers table
  - Check uploaded files have unique names
  - Check timestamps (created_at, updated_at)
  - Check foreign keys (author_id)
- [ ] **Test**: Verify users table
  - Check passwords are hashed (not plain text)
  - Check reset_token and expiry for password resets
- [ ] **Test**: Verify reviews table
  - Check ratings within valid ranges
  - Check review_status values
- [ ] **Test**: Verify review_assignments table
  - Check no duplicate assignments
  - Check deadlines are future dates

---

## 🔒 Security Tests

### Authentication Security
- [ ] **Test**: Passwords are hashed in database
  - Check users table - no plain text passwords
- [ ] **Test**: SQL injection attempts
  - Try: `' OR '1'='1` in login
  - Expected: No SQL errors, login fails
- [ ] **Test**: XSS attempts
  - Enter: `<script>alert('XSS')</script>` in text fields
  - Expected: Escaped, not executed
- [ ] **Test**: File upload security
  - Try uploading PHP file renamed to .pdf
  - Expected: Validation catches it

### Access Control
- [ ] **Test**: Author cannot access admin pages
  - Direct URL: http://localhost:8080/admin/
  - Expected: Permission denied
- [ ] **Test**: Reviewer cannot see other reviewers' reviews
- [ ] **Test**: Author can only see their own papers
- [ ] **Test**: Reviewer can only access assigned papers

### Session Security
- [ ] **Test**: Session expires after logout
  - Logout, press back button
  - Try to access protected page
  - Expected: Must login again
- [ ] **Test**: Multiple logins (same user, different browsers)
  - Expected: Both sessions work independently

---

## 📝 Data Integrity Tests

### Paper Workflow
- [ ] **Test**: Paper status transitions
  - Submitted → Under Review → Accepted/Rejected/Revision Required
  - Expected: Cannot skip states inappropriately
- [ ] **Test**: Revision replaces file
  - Submit revision
  - Check old file still exists or replaced
  - Expected: New file linked to paper

### Review Consistency
- [ ] **Test**: Multiple reviews for same paper
  - All reviewers can submit independently
  - Expected: No conflicts or overwrites
- [ ] **Test**: Edit review before and after submission
  - Expected: Timestamps update correctly

### User Data Consistency
- [ ] **Test**: Deactivate user
  - Expected: Papers/reviews still accessible
  - Expected: User cannot login
  - Expected: User still appears in old records
- [ ] **Test**: Change user email
  - Expected: Can login with new email
  - Expected: Old sessions invalidated

---

## 🎨 UI/UX Tests

### Navigation
- [ ] **Test**: All navigation links work
- [ ] **Test**: Breadcrumbs show correct path
- [ ] **Test**: Back button works correctly

### Forms
- [ ] **Test**: Tab order makes sense
- [ ] **Test**: Required fields marked with *
- [ ] **Test**: Helpful error messages
- [ ] **Test**: Success messages clear

### Tables & Lists
- [ ] **Test**: Status badges color-coded correctly
- [ ] **Test**: Action buttons clearly labeled
- [ ] **Test**: Sortable columns indicated
- [ ] **Test**: Pagination if many records

---

## 🐛 Known Limitations & Edge Cases

### Test These Edge Cases:
- [ ] **Test**: Submit paper at exactly deadline
- [ ] **Test**: Edit paper while another user viewing it
- [ ] **Test**: Make decision with 0 reviews
- [ ] **Test**: Assign reviewer to 10+ papers
- [ ] **Test**: Paper with very long title/abstract
- [ ] **Test**: User with no papers/reviews
- [ ] **Test**: Empty search results
- [ ] **Test**: Export with 0 papers
- [ ] **Test**: Password reset token used twice

---

## ✅ Test Completion Checklist

### Core Features (Must Pass)
- [ ] Authentication works
- [ ] Papers can be submitted
- [ ] Reviews can be submitted
- [ ] Decisions can be made
- [ ] Users can be managed

### Advanced Features (Must Pass)
- [ ] Charts display correctly
- [ ] Search/filter works
- [ ] Export to CSV works
- [ ] Password reset works
- [ ] Profile updates work

### Optional (Nice to Have)
- [ ] All browsers tested
- [ ] Mobile responsive
- [ ] Performance acceptable
- [ ] No console errors

---

## 📋 Test Execution Template

Use this template when testing:

```
Date: ___________
Tester: __________
Test Case: _______________________

Steps Taken:
1. ___________________________
2. ___________________________
3. ___________________________

Expected Result: _______________
Actual Result: _________________
Status: [ ] PASS [ ] FAIL

Notes/Issues:
_______________________________
_______________________________

Screenshots (if failure):
Attach or link here
```

---

## 🚨 Bug Reporting Template

If you find bugs during testing:

```
Bug ID: ___________
Severity: [ ] Critical [ ] High [ ] Medium [ ] Low

Title: ____________________________

Steps to Reproduce:
1. ___________________________
2. ___________________________
3. ___________________________

Expected Behavior: _____________
Actual Behavior: _______________

Environment:
- Browser: ________
- OS: _____________
- User Role: _______

Screenshots/Logs:
(Attach if available)

Suggested Fix:
_______________________________
```

---

## 🎯 Regression Testing

**After any code changes, re-test:**
1. Login/logout
2. Submit paper
3. Submit review
4. Make decision
5. Search/filter
6. Charts display

---

## 📊 Testing Summary Report Template

```
Testing Summary Report
Date: ___________
System Version: 2.0.0

Total Test Cases: ______
Passed: ______
Failed: ______
Skipped: ______
Pass Rate: ______%

Critical Bugs Found: ______
High Priority Bugs: ______
Medium Priority Bugs: ______
Low Priority Bugs: ______

Overall Status: [ ] Ready for Production [ ] Needs Work

Tested By: __________
Approved By: __________

Notes:
_______________________________
_______________________________
```

---

## 🔄 Continuous Testing

**Weekly Tests:**
- [ ] Submit paper
- [ ] Complete review
- [ ] Make decision

**Monthly Tests:**
- [ ] All authentication flows
- [ ] All admin features
- [ ] Data export

**Before Deployment:**
- [ ] Full regression test
- [ ] Security scan
- [ ] Performance test
- [ ] Backup database

---

## 📚 Additional Resources

- **System Documentation**: See IMPLEMENTATION_SUMMARY.md
- **User Guide**: See USER_GUIDE.md
- **Changelog**: See CHANGELOG.md
- **Database Schema**: See database/init.sql

---

## ✨ Feature Coverage Summary

**10/10 Features Implemented and Testable:**
1. ✅ Admin Decision Making
2. ✅ Admin Paper Details View
3. ✅ Admin User Management (CRUD)
4. ✅ Author Paper Editing
5. ✅ Author Revision Submission
6. ✅ Reviewer Edit Reviews
7. ✅ User Profile Management
8. ✅ Password Reset System
9. ✅ Dashboard Statistics & Charts
10. ✅ Advanced Search & Filter

**System Status: 100% Complete ✅**

---

*End of Testing Guide*

**Last Updated**: December 14, 2025
**Document Version**: 2.0
**System Version**: 2.0.0
