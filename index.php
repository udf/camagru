<?php
require_once('includes/pagebuilder.class.php');
require_once('includes/htmltag.class.php');
require_once('view/post.php');

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

try {
    $posts = $DATABASE->get_images(($current_page - 1) * $PAGE_SIZE, $_SESSION['id'] ?? -1);
} catch (RuntimeException $e) {
    die_with_alert('danger', 'Error', $e->getMessage());
}
foreach ($posts as $post) {
    make_post_item(
        $post['id'],
        $post['username'],
        $post['date'],
        $post['filename'],
        $post['is_liked'] === '1',
        $post['like_count'],
        $post['comment_count']
    );
}

echo $pagination;
?>
