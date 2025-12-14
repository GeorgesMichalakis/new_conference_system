<?php
require_once '../includes/config.php';
requireRole('admin');

// Handle export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=papers_export_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Title', 'Author', 'Status', 'Submitted', 'Reviews', 'Category', 'Track']);
    
    $stmt = $pdo->query("
        SELECT p.id, p.title, p.status, p.submission_date, p.category, p.conference_track,
               u.first_name, u.last_name,
               (SELECT COUNT(*) FROM reviews WHERE paper_id = p.id) as review_count
        FROM papers p
        JOIN users u ON p.author_id = u.id
        WHERE p.is_active = 1
        ORDER BY p.submission_date DESC
    ");
    
    while ($row = $stmt->fetch()) {
        fputcsv($output sortable-table">
        <thead>
            <tr>
                <th>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'title', 'order' => ($sort === 'title' && $order === 'ASC') ? 'DESC' : 'ASC'])); ?>">
                        Title <?php if ($sort === 'title') echo $order === 'ASC' ? '↑' : '↓'; ?>
                    </a>
                </th>
                <th>Author</th>
                <th>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'status', 'order' => ($sort === 'status' && $order === 'ASC') ? 'DESC' : 'ASC'])); ?>">
                        Status <?php if ($sort === 'status') echo $order === 'ASC' ? '↑' : '↓'; ?>
                    </a>
                </th>
                <th>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'submission_date', 'order' => ($sort === 'submission_date' && $order === 'ASC') ? 'DESC' : 'ASC'])); ?>">
                        Submitted <?php if ($sort === 'submission_date') echo $order === 'ASC' ? '↑' : '↓'; ?>
                    </a>
                </th>
                <th>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'review_count', 'order' => ($sort === 'review_count' && $order === 'ASC') ? 'DESC' : 'ASC'])); ?>">
                        Reviews <?php if ($sort === 'review_count') echo $order === 'ASC' ? '↑' : '↓'; ?>
                    </a>
                ],
            $row['conference_track']
        ]);
    }
    
    fclose($output);
    exit;
}

$pageTitle = 'Manage Papers';
include '../includes/header.php';

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$sort = $_GET['sort'] ?? 'submission_date';
$order = $_GET['order'] ?? 'DESC';

// Build query
$where = ["p.is_active = 1"];
$params = [];

if ($search) {
    $where[] = "(p.title LIKE ? OR p.keywords LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($status_filter) {
    $where[] = "p.status = ?";
    $params[] = $status_filter;
}

if ($date_from) {
    $where[] = "p.submission_date >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where[] = "p.submission_date <= ?";
    $params[] = $date_to . ' 23:59:59';
}

$where_clause = implode(' AND ', $where);

// Validate sort column
$valid_sorts = ['title', 'submission_date', 'status', 'review_count'];
if (!in_array($sort, $valid_sorts)) {
    $sort = 'submission_date';
}

$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

// Get papers
$stmt = $pdo->prepare("
    SELECT p.id, p.title, p.status, p.submission_date,
           u.first_name, u.last_name,
           (SELECT COUNT(*) FROM reviews WHERE paper_id = p.id) as review_count
    FROM papers p
    JOIN users u ON p.author_id = u.id
    WHERE $where_clause
    ORDER BY $sort $order
");
$stmt->execute($params);
$papers = $stmt->fetchAll();
?>

<h2>Manage Papers</h2>

<div class="search-filter-section">
    <form method="GET" class="search-filter-form">
        <div class="filter-row">
            <input type="text" name="search" placeholder="Search by title, keywords, or author..." 
                   value="<?php echo htmlspecialchars($search); ?>" class="search-input">
            
            <select name="status">
                <option value="">All Status</option>
                <option value="submitted" <?php echo $status_filter === 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                <option value="under_review" <?php echo $status_filter === 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                <option value="accepted" <?php echo $status_filter === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                <option value="revision_required" <?php echo $status_filter === 'revision_required' ? 'selected' : ''; ?>>Revision Required</option>
            </select>
            
            <input type="date" name="date_from" placeholder="From Date" value="<?php echo htmlspecialchars($date_from); ?>">
            <input type="date" name="date_to" placeholder="To Date" value="<?php echo htmlspecialchars($date_to); ?>">
            
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="papers.php" class="btn btn-secondary">Clear</a>
            <a href="?export=csv<?php echo $search || $status_filter ? '&' . http_build_query($_GET) : ''; ?>" 
               class="btn btn-secondary">Export CSV</a>
        </div>
    </form>
    
    <div class="results-info">
        Showing <?php echo count($papers); ?> paper(s)
    </div>
</div>

<?php if (!empty($papers)): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Reviews</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($papers as $paper): ?>
                <tr>
                    <td><?php echo htmlspecialchars($paper['title']); ?></td>
                    <td><?php echo htmlspecialchars($paper['first_name'] . ' ' . $paper['last_name']); ?></td>
                    <td>
                        <span class="status status-<?php echo $paper['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $paper['status'])); ?>
                 found matching your criteria.</p>
<?php endif; ?>

<style>
.search-filter-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.search-filter-form .filter-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto auto auto;
    gap: 0.5rem;
    align-items: center;
}

.search-input {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.results-info {
    margin-top: 1rem;
    color: #666;
    font-size: 0.9rem;
}

.sortable-table th a {
    color: inherit;
    text-decoration: none;
    display: block;
}

.sortable-table th a:hover {
    color: #3498db;
}

@media (max-width: 1024px) {
    .search-filter-form .filter-row {
        grid-template-columns: 1fr;
    }
}
</style      </td>
                    <td><?php echo date('M j, Y', strtotime($paper['submission_date'])); ?></td>
                    <td><?php echo $paper['review_count']; ?></td>
                    <td>
                        <a href="view_paper.php?id=<?php echo $paper['id']; ?>" class="btn btn-small">View</a>
                        <a href="assign_reviewers.php?paper_id=<?php echo $paper['id']; ?>" class="btn btn-small">Assign</a>
                        <?php if ($paper['status'] === 'under_review'): ?>
                            <a href="make_decision.php?id=<?php echo $paper['id']; ?>" class="btn btn-small btn-primary">Decide</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No papers submitted yet.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>