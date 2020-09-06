<?php
require_once('includes/htmltag.class.php');

function make_post_item($id, $username, $date, $filename, $is_liked, $like_count, $comment_count, $comments = []) {
  $like_icon_class = ($is_liked ? 'unlike-icon' : 'like-icon');
?>

<div class="card mb-3 mt-3 mx-auto" style="max-width: 720px;" <?php echo "id={$id}" ?>>
    <div class="card-header">
        <?php HTMLTag('div', ['class' => 'row justify-content-between'])
            ->append(HTMLTag('span', [], "👤 {$username}"))
            ->append(HTMLTag('span', [], "📅 {$date}"))
            ->print();
        ?>
    </div>
    <div class="card-body">
        <?php HTMLTag('a', ['href' => "comments.php?id={$id}"])
            ->append(HTMLTag('img', ['src' => "uploads/{$filename}"]))
            ->print();
        ?>
    </div>
    <div class="card-footer">
        <?php HTMLTag('div', ['class' => 'row'])
            ->append(
                HTMLTag('div')
                ->setAttr('class', "col card-footer-button {$like_icon_class}")
                ->setAttr('onclick', "toggle_like(this);")
                ->setContent($like_count)
            )
            ->append(
                HTMLTag('div')
                ->setAttr('class', "col card-footer-button comment-icon")
                ->setContent($comment_count)
            )
            ->print();
        ?>
    </div>
<?php
    foreach ($comments as $comment) {
        $comment->print();
    }
?>
</div>

<?php
}