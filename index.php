<?php
// ... (PHP code for fetching posts remains the same) ...
require_once 'db_config.php';

// Pagination settings
$posts_per_page = 5; 
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}
$offset = ($current_page - 1) * $posts_per_page;

$total_posts_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM posts");
$total_posts_row = mysqli_fetch_assoc($total_posts_query);
$total_posts = $total_posts_row['total'];
$total_pages = ceil($total_posts / $posts_per_page);

$posts = [];
$sql = "SELECT id, title, LEFT(content, 200) AS excerpt, created_at 
        FROM posts 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $posts_per_page, $offset);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $posts[] = $row;
            }
        }
    } else { /* ... error handling ... */ }
    mysqli_stmt_close($stmt);
} else { /* ... error handling ... */ }
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Blog Posts - Bootstrap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> </head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Latest Posts</h1>

        <form action="search.php" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="query" class="form-control" placeholder="Search posts..." required
                       value="<?php echo isset($_GET['query_main']) ? htmlspecialchars($_GET['query_main']) : ''; ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <?php if (!empty($posts)): ?>
            <div class="list-group">
                <?php foreach ($posts as $post): ?>
                    <a href="post_detail.php?id=<?php echo $post['id']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($post['title']); ?></h5>
                            <small class="text-muted"><?php echo date("M j, Y", strtotime($post['created_at'])); ?></small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($post['excerpt']); ?>...</p>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php if($current_page <= 1){ echo 'disabled'; } ?>">
                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if($i == $current_page) { echo 'active'; } ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php if($current_page >= $total_pages){ echo 'disabled'; } ?>">
                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-info" role="alert">
                No posts to display.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>