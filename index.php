<!-- Main home page show blog posts -->
<?php
include 'connection.php';
include 'navbar.php';

//fetch posts with like counts, comment counts and days old
$fetchSql = "
    SELECT 
        p.post_id,
        p.user_id,
        p.title,
        p.blog_image,
        p.excerpt,
        p.created_at,
        u.username,
        up.profile_picture,
        COUNT(DISTINCT l.like_id) AS like_count,
        COUNT(DISTINCT c.comment_id) AS comment_count,
        DATEDIFF(NOW(), p.created_at) AS days_old
    FROM posts p
    INNER JOIN users u ON u.user_id = p.user_id
    LEFT JOIN user_profiles up ON u.user_id = up.user_id
    LEFT JOIN likes l ON p.post_id = l.post_id
    LEFT JOIN comments c ON p.post_id = c.post_id
    GROUP BY p.post_id
";

$fetchResult = mysqli_query($conn, $fetchSql);

//Ranking algorithm

//  Fetch all blogs into array
$posts = [];
while ($row = mysqli_fetch_assoc($fetchResult)) {
    $posts[] = $row;
}

//  Calculate rank score for each blog
function calculateRankScore($blog) {
    $likeWeight    = 2;  //  2 points
    $commentWeight = 3;  //  3 points
    $dayPenalty    = 1;  //  1 point

    $likes    = isset($blog['like_count'])    ? $blog['like_count']    : 0;
    $comments = isset($blog['comment_count']) ? $blog['comment_count'] : 0;
    $days     = isset($blog['days_old'])      ? $blog['days_old']      : 0;

    $score = ($likes    * $likeWeight)
           + ($comments * $commentWeight)
           - ($days     * $dayPenalty);

    return $score;
}

//  Sort blogs by rank score using usort
usort($posts, function($a, $b) {
    $scoreA = calculateRankScore($a);
    $scoreB = calculateRankScore($b);

    // Higher score comes first
    return $scoreB - $scoreA;
});

//End of ranking algortihm
?>

<link rel="stylesheet" href="style.css">

<main class="row container-lg mx-auto">
    <div class="col-lg-8">

        <?php foreach ($posts as $result) {
            $postId = $result['post_id'];
        ?>

        <div class="card d-flex m-2 p-2">

            <!-- AUTHOR -->
            <div class="mb-2">
                <img src="uploads/<?= !empty($result['profile_picture']) ? $result['profile_picture'] : 'default.png'; ?>"
                     class="rounded-circle" style="width:32px;height:32px;">

                <a href="viewProfile.php?user_id=<?= $result['user_id']; ?>"
                   class="text-decoration-none text-dark fw-bold">
                    <?= htmlspecialchars($result['username']); ?>
                </a>
            </div>

            <!-- POST -->
            <a href="read-blog.php?id=<?= $postId; ?>" class="text-decoration-none text-dark">
                <div class="d-flex justify-content-between">

                    <div class="w-75">
                        <h3 class="fw-bolder"><?= htmlspecialchars($result['title']); ?></h3>
                        <h5 class="text-secondary"><?= htmlspecialchars($result['excerpt']); ?></h5>

                        <div class="d-flex gap-4 mt-2 text-secondary">
                            <!-- this is used to show creation date -->
                            <div><?= $result['created_at']; ?></div>

                            <!-- LIKE COUNT -->
                            <div>
                                <i class="fa-solid fa-heart text-danger"></i>
                                <?= $result['like_count']; ?>
                            </div>

                            <!-- COMMENT COUNT -->
                            <div>
                                <i class="fa-solid fa-comment"></i>
                                <?= $result['comment_count']; ?>
                            </div>
                        </div>
                    </div>

                    <div class="w-25">
                        <img src="uploads/<?= $result['blog_image']; ?>"
                             class="rounded"
                             style="width:150px;height:120px;object-fit:cover;">
                    </div>
                </div>
            </a>
        </div>

        <?php } ?>
    </div>

    <!-- TOP POSTS (BY COMMENTS) -->
    <?php
    $topPosts = $conn->query("
        SELECT 
            p.post_id,
            p.title,
            u.username,
            up.profile_picture,
            COUNT(c.comment_id) AS comment_count
        FROM posts p
        LEFT JOIN comments c ON p.post_id = c.post_id
        LEFT JOIN users u ON p.user_id = u.user_id
        LEFT JOIN user_profiles up ON u.user_id = up.user_id
        GROUP BY p.post_id
        ORDER BY comment_count DESC
        LIMIT 5
    ");
    ?>

    <div class="col-lg-4">
        <div class="text-success fw-bold h5">Top Posts:</div>

        <?php while ($row = $topPosts->fetch_assoc()) { ?>
            <a href="read-blog.php?id=<?= $row['post_id']; ?>" class="text-decoration-none text-dark">
                <div class="mt-3 card p-2">
                    <img src="uploads/<?= !empty($row['profile_picture']) ? $row['profile_picture'] : 'default.png'; ?>"
                         class="rounded-circle" style="width:32px;height:32px;">
                    <span class="fw-bold"><?= htmlspecialchars($row['username']); ?></span>
                    <p class="mt-2"><?= htmlspecialchars($row['title']); ?></p>
                </div>
            </a>
        <?php } ?>
    </div>
</main>