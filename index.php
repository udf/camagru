<?php
require_once('includes/pagebuilder.class.php');
require_once('includes/htmltag.class.php');
$_PAGE_BUILDER = new Pagebuilder('Gallery');

function makeNavItem($text, $page_i) {
    global $current_page, $num_pages;
    $extra_classes = '';
    if ($page_i <= 0 || $page_i > $num_pages)
        $extra_classes .= ' disabled';
    if ($page_i === $current_page)
        $extra_classes .= ' active';
    return HTMLTag('li')
        ->setAttr('class', "page-item {$extra_classes}")
        ->append(
            HTMLTag('a')
                ->setAttr('class', 'page-link')
                ->setAttr('href', "?page={$page_i}")
                ->setContent($text)
        );
}

$current_page = intval($_GET['page'] ?? '1');
$num_pages = ceil($DATABASE->count_images() / $PAGE_SIZE);

if ($current_page <= 0 || $current_page > $num_pages)
    $current_page = 1;

$nav_items = [];
$nav_items[] = makeNavItem('«', $current_page - 1);
for ($i=1; $i <= $num_pages; $i++) {
    $nav_items[] = makeNavItem("{$i}", $i);
}
$nav_items[] = makeNavItem('»', $current_page + 1);

$pagination = (string)HTMLTag('nav')
    ->append(
        HTMLTag('ul', ['class' => 'pagination'])
        ->addChildren($nav_items)
    )
;

echo $pagination;

$posts = $DATABASE->get_images(($current_page - 1) * $PAGE_SIZE, $_SESSION['id'] ?? -1);
foreach ($posts as $post) {
    $like_icon_class = ($post['is_liked'] === NULL ? 'like-icon' : 'unlike-icon');
?>

<div class="card mb-3 mt-3 mx-auto" <?php echo "id=${post['id']}" ?>>
    <div class="card-header">
        <?php HTMLTag('div', ['class' => 'row justify-content-between'])
            ->append(HTMLTag('span', [], "👤 {$post['username']}"))
            ->append(HTMLTag('span', [], "📅 {$post['date']}"))
            ->print();
        ?>
    </div>
    <div class="card-body">
        <?php HTMLTag('a', ['href' => "comments.php?id={$post['id']}"])
            ->append(HTMLTag('img', ['src' => "uploads/{$post['filename']}"]))
            ->print();
        ?>
    </div>
    <div class="card-footer">
        <?php HTMLTag('div', ['class' => 'row'])
            ->append(
                HTMLTag('div')
                ->setAttr('class', "col card-footer-button {$like_icon_class}")
                ->setAttr('onclick', "toggle_like(this);")
                ->setContent($post['like_count'])
            )
            ->append(
                HTMLTag('div')
                ->setAttr('class', "col card-footer-button comment-icon")
                ->setContent($post['comment_count'])
            )
            ->print();
        ?>
    </div>
</div>

<?php
}

echo $pagination;
?>
