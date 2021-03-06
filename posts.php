<?php

require_once 'includes/utils.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$user_id = $_SESSION['id'];
$username = $_SESSION['username'];
$post = "";

if (htmlspecialchars(post('post'))) {
    $post = htmlspecialchars(post('post'));;
}

if (post("action") == "add_post") {
    $conn = db();
    $query = 'INSERT INTO posts(user_id, username, content) VALUES("%d", "%s", "%s")';
    $query = sprintf($query, $user_id, $username, $post);
    mysqli_query($conn, $query) or die('Error');
    $_SESSION['message'] = "New post added!";
    $_SESSION['type'] = 'alert-success';
}
if (post('action') == "delete_post") {
    $post_to_del = post('post_id');
    delete_post($post_to_del);
}
if (post('action') == 'like') {
    $post_id_like = post('post_id_like');
    like_post($post_id_like, $user_id);
}

$posts = get_posts($user_id);
?>

<div class="post_field">
<h3>Make Post:</h3>

    <form method="post">

        <input type="hidden" name="action" value="add_post">

        <div class="post_area">
            <label>Content:</label>
            <label>
                <textarea name="post" rows="6" cols="50"></textarea>
            </label>
        </div>

        <div>
            <input class="button" type="submit" value="Add New Post">
        </div>

    </form>



    <?php if (count($posts) === 0) { ?>
        <h3>You have no posts yet.</h3>
    <?php } else { ?>
        <h3>My Posts:</h3>
        <ul class="posts">
            <?php foreach ($posts as $previous_post) { ?>
                <li class="post">
                    <form method="post">
                        <div class="post_content">
                            <input type="hidden" name="action" value="delete_post">
                            <input type="hidden" name="post_id" value="<?php echo $previous_post->id; ?>">
                            <p><?php echo $previous_post->created_at; ?></p>
                            <p style="align-self: center"><?php echo $previous_post->content; ?></p>
                            <input class="button" style="align-self: flex-end" type="submit" value="Delete this post">
                        </div>
                    </form>
                    <div class="buttons">
                    <form method="post">
                        <input type="hidden" name="action" value="like">
                        <input type="hidden" name="post_id_like" value="<?php echo $previous_post->id?>">
                        <?php $likes = get_likes($previous_post->id)?>
                        <input class="button" type="submit" value="Like <?php echo count($likes)?>">
                    </form>
                    <a class="linkBtn" href="comments.php?post_id=<?php echo $previous_post->id?>&location=myPage.php">View Comments: <?php echo count(get_comments($previous_post->id))?></a>
                    </div>
                </li>
                <br>
            <?php } ?>
        </ul>
    <?php } ?>
</div>
