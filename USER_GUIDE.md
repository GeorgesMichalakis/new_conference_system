# Quick User Guide - New Features

## For Authors

### Editing Your Paper
1. Go to "My Papers" dashboard
2. Find papers with status "Submitted" or "Revision Required"
3. Click the **"Edit"** button
4. Modify title, abstract, keywords, or other fields
5. Optionally upload a new version of your paper
6. Click "Save Changes"

**Note**: You can only edit papers that haven't started the review process yet.

### Submitting Revisions
1. When your paper status is "Revision Required":
2. Click the **"Revise"** button (orange)
3. Read the editor's feedback
4. Upload your revised paper file
5. Add revision notes explaining your changes
6. Submit the revision
7. Paper status will change back to "Under Review"

### Managing Your Profile
1. Click **"Profile"** in the top navigation
2. Update your:
   - Full name and email
   - Affiliation (university/organization)
   - Website URL
   - Research interests
   - Biography
3. Click "Save Changes"

### Changing Your Password
1. Go to Profile page
2. Scroll to "Change Password" section
3. Enter current password
4. Enter new password (minimum 6 characters)
5. Confirm new password
6. Click "Change Password"

### Forgot Password?
1. On login page, click **"Forgot your password?"**
2. Enter your email address
3. Check for reset link (shown on screen for testing)
4. Click the reset link
5. Enter your new password twice
6. Login with new password

---

## For Reviewers

### Editing Your Review
1. Go to "Review Papers" dashboard
2. Find papers you've already reviewed
3. Click **"Edit Review"** button
4. Modify your ratings, recommendation, or comments
5. Click "Update Review"

**Note**: You can edit reviews until the paper is finalized by the admin.

### Your Reviews Include
- Overall rating (1-10)
- Technical quality, novelty, significance, clarity (1-5 each)
- Recommendation (strong accept → strong reject)
- Comments for authors (they will see these)
- Confidential comments (only admins see these)

---

## For Administrators

### Making Decisions on Papers
1. Go to Admin Dashboard
2. Click **"Make Decisions"** or go to "Manage Papers"
3. Find papers with status "Under Review"
4. Click **"Decide"** button
5. Review all submitted reviews and ratings
6. Read reviewer recommendations
7. Select decision: Accept, Revision Required, or Reject
8. Add comments for the author
9. Submit decision

### Viewing Complete Paper Details
1. From any paper list, click **"View"**
2. You'll see:
   - Complete paper information
   - Author details
   - All reviews (with ratings breakdown)
   - Confidential reviewer comments
   - Current status and decision
3. Download paper PDF from this page

### Managing Users
1. Click **"Manage Users"** from Admin Dashboard
2. **Search/Filter Users**:
   - Search by name or email
   - Filter by role (Admin, Author, Reviewer)
   - Filter by status (Active, Inactive)

3. **Create New User**:
   - Click "Create New User"
   - Fill in full name, email, role
   - Set affiliation and other details
   - Click "Create User"
   - Default password is "password123"

4. **Edit Existing User**:
   - Click "Edit" next to any user
   - Update any field except role
   - Save changes

5. **Deactivate User**:
   - Click "Deactivate" (soft delete)
   - User cannot login but data is preserved
   - You cannot deactivate yourself

6. **Reset Password**:
   - Click "Reset Password"
   - New password is "password123"
   - User should change it on first login

### Dashboard Statistics
Your dashboard shows:
- Total papers (with breakdown by status)
- Total users (authors and reviewers)
- Review statistics (completed and pending)
- Recent submissions (last 5 papers)

---

## Common Workflows

### Complete Review Process (Admin View)
1. Author submits paper → Status: "Submitted"
2. Admin assigns reviewers → Status: "Under Review"
3. Reviewers submit reviews
4. Admin makes decision:
   - **Accept** → Status: "Accepted" (done)
   - **Revision Required** → Author submits revision → Back to step 2
   - **Reject** → Status: "Rejected" (done)

### Author Revision Workflow
1. Paper receives "Revision Required" decision
2. Author views decision and feedback
3. Author clicks "Revise" button
4. Uploads revised file with notes
5. Status changes to "Under Review"
6. Reviewers review again (or admin decides)

### Reviewer Workflow
1. Admin assigns paper
2. Reviewer views paper details
3. Downloads and reads paper
4. Submits review with ratings
5. (Optional) Edits review if needed
6. Admin uses review for final decision

---

## Quick Tips

### For All Users
- Keep your profile up to date
- Use strong passwords
- Log out when finished

### For Authors
- Edit papers before they enter review
- Respond promptly to revision requests
- Add detailed revision notes

### For Reviewers
- Meet review deadlines
- Be constructive in comments
- Use confidential comments for concerns

### For Admins
- Assign at least 2-3 reviewers per paper
- Read all reviews before deciding
- Provide clear feedback to authors
- Regularly check dashboard statistics

---

## Troubleshooting

**Can't edit my paper?**
- Papers under review or already decided cannot be edited
- You can submit revisions if status is "Revision Required"

**Forgot password not working?**
- Reset link expires after 1 hour
- Request a new reset if expired

**Can't see a paper?**
- Authors only see their own papers
- Reviewers only see assigned papers
- Admins see all papers

**Review button not showing?**
- You must be assigned to review that paper
- Contact admin if you should have access

---

## File Upload Guidelines

### Accepted Formats
- PDF (preferred)
- DOC/DOCX

### Maximum File Size
- 10 MB per file

### Naming
- Files are automatically renamed for security
- Original filename is preserved in database

---

## Need Help?

Contact system administrator:
- Email: admin@conference.com
- Login as admin to manage the system

---

## Technical Notes

### Browser Compatibility
- Chrome, Firefox, Safari, Edge (latest versions)
- JavaScript enabled
- Cookies enabled for sessions

### Security
- Passwords are encrypted (bcrypt)
- Sessions timeout after inactivity
- File uploads are validated
- SQL injection protected

### Data Retention
- Deactivated users: Data preserved but login disabled
- Deleted papers: Marked inactive but not removed
- All changes tracked with timestamps
