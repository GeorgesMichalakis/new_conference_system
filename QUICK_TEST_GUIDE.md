# 🚀 Quick Start & Test Reference Card

## System Access
```
Main App:     http://localhost:8080
phpMyAdmin:   http://localhost:8081
```

## Test Accounts
```
Admin:    admin@example.com / admin123
Author:   author@example.com / author123  
Reviewer: reviewer@example.com / reviewer123
```

## Quick Commands
```bash
# Start system
podman-compose up -d

# Stop system
podman-compose down

# View logs
podman-compose logs -f

# Access database
podman-compose exec db mysql -u conference_user -p conference_db
# Password: conference_pass
```

## Feature Test Checklist (Quick)

### ✅ Must Test (Core)
- [ ] Login as admin/author/reviewer
- [ ] Submit paper (author)
- [ ] Assign reviewers (admin)
- [ ] Submit review (reviewer)
- [ ] Make decision (admin)
- [ ] View charts (admin dashboard)
- [ ] Search papers (admin)

### ✅ Should Test (Important)
- [ ] Edit paper (author)
- [ ] Submit revision (author)
- [ ] Edit review (reviewer)
- [ ] Password reset
- [ ] Profile update
- [ ] Export CSV (admin)

### ✅ Nice to Test (Additional)
- [ ] User management (admin)
- [ ] Sort papers columns
- [ ] Filter by status
- [ ] View paper details
- [ ] Download paper files

## Quick Test Workflow

### 1. Author Flow (5 min)
```
1. Login as author
2. Submit New Paper
   - Title: "Test Paper"
   - Abstract: "This is a test"
   - Upload: sample.pdf
3. Verify paper in "My Papers"
4. Click "View" to see details
5. Logout
```

### 2. Admin Flow (5 min)
```
1. Login as admin
2. View Dashboard
   - Check charts display
   - Check statistics
3. Go to "Manage Papers"
   - Search for "Test Paper"
   - Filter by status
   - Click "Assign" next to paper
4. Assign 2 reviewers
   - Set deadline (tomorrow)
5. Logout
```

### 3. Reviewer Flow (5 min)
```
1. Login as reviewer
2. View assigned papers
3. Click "Submit Review"
4. Fill review:
   - Overall: 8/10
   - All criteria: 4/5
   - Recommendation: Accept
   - Comments: "Good work"
5. Submit review
6. Logout
```

### 4. Admin Decision (3 min)
```
1. Login as admin
2. Go to "Manage Papers"
3. Click "Decide" on reviewed paper
4. Review all reviews
5. Select "Accept"
6. Add comments: "Congratulations"
7. Submit decision
8. Logout
```

### 5. Author View Decision (2 min)
```
1. Login as author
2. Go to "My Papers"
3. Click "View" on decided paper
4. Verify status shows "Accepted"
5. Verify decision comments visible
```

## Feature Locations

### Admin Features
```
Dashboard Charts:     /admin/ (index.php)
Paper Search/Filter:  /admin/papers.php
User Management:      /admin/users.php
Assign Reviewers:     /admin/assign_reviewers.php
Make Decisions:       /admin/make_decision.php
View Paper Details:   /admin/view_paper.php
```

### Author Features
```
Submit Paper:         /author/submit.php
My Papers:            /author/index.php
Edit Paper:           /author/edit.php
Submit Revision:      /author/revise.php
View Paper:           /author/view.php
```

### Reviewer Features
```
Assigned Papers:      /reviewer/index.php
Submit Review:        /reviewer/review.php
Edit Review:          /reviewer/edit_review.php
View Paper:           /reviewer/view.php
Download Paper:       /reviewer/download.php
```

### User Features
```
Profile:              /profile.php
Password Reset:       /forgot_password.php
Login:                /login.php
Register:             /register.php
```

## Chart Types (Admin Dashboard)

### Pie Chart
- Shows paper status distribution
- Colors: Blue (Submitted), Orange (Under Review), Green (Accepted), Red (Rejected)

### Line Chart
- Shows submissions over last 6 months
- X-axis: Months, Y-axis: Count

### Bar Chart
- Shows review progress
- Completed (green) vs Pending (orange)

### Workload Table
- Lists top 10 reviewers
- Shows assigned/completed/pending counts
- Color-coded completion rates

## Search & Filter Options

### Search Field
- Searches: Paper title, keywords, author name
- Use any keyword or phrase

### Status Filter
- Submitted
- Under Review
- Accepted
- Rejected
- Revision Required

### Date Filter
- From Date: YYYY-MM-DD
- To Date: YYYY-MM-DD

### Sort Options
- Title (A-Z or Z-A)
- Status
- Submitted Date
- Review Count

### Actions
- **Filter**: Apply all filters
- **Clear**: Reset to default
- **Export CSV**: Download results

## Common Test Scenarios

### Test 1: Happy Path (Everything Works)
```
Author submits → Admin assigns → Reviewer reviews → Admin accepts
Expected: Paper status = "Accepted", Author sees decision
Time: ~15 minutes
```

### Test 2: Revision Flow
```
Author submits → Admin assigns → Reviewer reviews (needs work) 
→ Admin requests revision → Author submits revision → Repeat
Expected: Paper status cycles through revision
Time: ~20 minutes
```

### Test 3: Rejection Flow
```
Author submits → Admin assigns → Reviewer gives low scores
→ Admin rejects → Author sees rejection
Expected: Paper status = "Rejected", Clear feedback
Time: ~15 minutes
```

### Test 4: Edit Paper
```
Author submits → Author edits (before review) → Changes saved
Expected: Updates reflected, File can be replaced
Time: ~5 minutes
```

### Test 5: Edit Review
```
Reviewer submits review → Reviewer edits review → Changes saved
Expected: Updated values shown, Timestamp updated
Time: ~5 minutes
```

### Test 6: User Management
```
Admin creates user → Admin edits user → Admin resets password
→ New user logs in → Admin deactivates user → Cannot login
Expected: All operations succeed
Time: ~10 minutes
```

### Test 7: Password Reset
```
User clicks "Forgot password" → Enters email → Clicks reset link
→ Sets new password → Logs in with new password
Expected: Successful password change
Time: ~5 minutes
```

### Test 8: Search & Export
```
Admin searches papers → Applies filters → Sorts results 
→ Exports CSV → Opens in Excel
Expected: CSV contains filtered data
Time: ~5 minutes
```

## Error Tests (Quick)

### Test Invalid Inputs
- [ ] Login with wrong password → Error shown
- [ ] Upload file > 10MB → Error shown
- [ ] Upload wrong file type → Error shown
- [ ] Submit form with missing fields → Validation error
- [ ] Access admin page as author → Permission denied

### Test Edge Cases
- [ ] Search with no results → "No papers found"
- [ ] Export with 0 papers → Empty CSV with headers
- [ ] Assign 0 reviewers → Error or warning
- [ ] Make decision with 0 reviews → Allowed but warned
- [ ] Reset password with invalid token → Error

## Database Quick Checks (phpMyAdmin)

### Tables to Inspect
```
users         - Check password hashed, reset_token exists
papers        - Check file paths, status transitions
reviews       - Check ratings within ranges
review_assignments - Check no duplicates
```

### Quick SQL Queries
```sql
-- Count papers by status
SELECT status, COUNT(*) FROM papers GROUP BY status;

-- See reviewer workload
SELECT u.first_name, u.last_name, COUNT(ra.id) as assigned
FROM users u
LEFT JOIN review_assignments ra ON u.id = ra.reviewer_id
WHERE u.role = 'reviewer'
GROUP BY u.id;

-- Recent activity
SELECT * FROM papers ORDER BY submission_date DESC LIMIT 10;

-- Check password hashing
SELECT id, email, LEFT(password, 7) as password_prefix FROM users LIMIT 5;
-- Should see: $2y$10$ (bcrypt hash prefix)
```

## Troubleshooting

### Containers Won't Start
```bash
podman-compose down
podman-compose up -d
# Wait 15 seconds for MySQL
```

### Can't Login
- Check email/password exactly
- Try default accounts from top of this doc
- Check database is running: `podman-compose ps`

### Charts Not Showing
- Check browser console for errors
- Verify Chart.js loaded (check network tab)
- Check if JavaScript enabled

### Upload Fails
- Check file size < 10MB
- Check file type (PDF, DOC, DOCX only)
- Check uploads/ directory exists and writable

### Export CSV Empty
- Check if any papers exist
- Check filters aren't too restrictive
- Try "Clear" filters first

## Performance Benchmarks

### Expected Times
- Page load: < 2 seconds
- Chart render: < 1 second
- Search results: < 500ms
- CSV export (100 papers): < 3 seconds
- File upload (5MB): Depends on connection

### If Slow
- Check container resources
- Check database indexes
- Clear browser cache
- Restart containers

## Final Checklist

Before reporting "Ready for Production":
- [ ] All test accounts work
- [ ] Can submit paper
- [ ] Can review paper
- [ ] Can make decision
- [ ] Charts display
- [ ] Search works
- [ ] Export works
- [ ] No console errors
- [ ] Mobile responsive
- [ ] Database backed up

## Success Criteria

✅ System is ready when:
- All 10 features tested
- No critical bugs
- Documentation read
- Test workflow completed
- Charts visible and accurate
- Search returns correct results
- Export produces valid CSV

---

**Total Test Time**: ~60 minutes for complete testing
**Quick Test Time**: ~20 minutes for core features
**Smoke Test Time**: ~5 minutes for basic functionality

**For detailed tests**: See TESTING_GUIDE.md  
**For help**: See USER_GUIDE.md  
**For technical info**: See IMPLEMENTATION_SUMMARY.md
