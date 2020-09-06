<?php
require_once('includes/pagebuilder.class.php');
require_once('includes/htmltag.class.php');
require_once('view/post.php');

$image_id = intval($_GET['id'] ?? '-1');
if ($image_id <= 0) {
    header('Location: index.php');
}

try {
    $posts = $DATABASE->get_image($image_id, $_SESSION['id'] ?? -1);
} catch (RuntimeException $e) {
    die_with_alert('danger', 'Error', $e->getMessage());
}

if (empty($posts)) {
    header('Location: index.php');
}

try {
    $comments = $DATABASE->get_comments($image_id);
} catch (RuntimeException $e) {
    die_with_alert('danger', 'Error', $e->getMessage());
}

$commentItems = [];

$commentItems[] = HTMLTag('div')
    ->setAttr('class', 'card mx-auto my-1')
    ->setAttr('style', 'width: 660px')
    ->append(
        HTMLTag('div', ['class' => 'card-header'])
        ->setContent('Post a comment')
    )
    ->append(
        HTMLTag('div', ['class' => 'card-body'])
        ->append(
            HTMLTag('form', ['class' => 'form-left', 'action' => 'controller/add_comment.php', 'redirect' => ''])
            ->append(
                HTMLTag('input', ['class' => 'form-control mb-1'])
                ->setAttr('type', 'text')
                ->setAttr('name', 'text')
                ->setAttr('placeholder', 'Your text here...')
                ->setAttr('required', '')
            )
            ->append(
                HTMLTag('input', ['class' => ''])
                ->setAttr('type', 'hidden')
                ->setAttr('name', 'id')
                ->setAttr('value', $image_id)
            )
            ->append(
                HTMLTag('button', ['class' => 'btn btn-secondary mt-1', 'type' => 'submit'], 'Comment')
            )
        )
    )
;

foreach ($comments as $comment) {
    $commentItems[] = HTMLTag('div')
        ->setAttr('class', 'card mx-auto my-1')
        ->setAttr('style', 'width: 660px;')
        ->append(
            HTMLTag('div', ['class' => 'card-header'])
            ->append(
                HTMLTag('div', ['class' => 'row ml-1 mr-1 justify-content-between'])
                ->append(HTMLTag('span', [], $comment['username']))
                ->append(HTMLTag('span', [], $comment['date']))
            )
        )
        ->append(
            HTMLTag('div', ['class' => 'card-body'], $comment['text'])
        )
    ;
}

$_PAGE_BUILDER = new Pagebuilder('Comments');

$post = $posts[0];


make_post_item(
    $post['id'],
    $post['username'],
    $post['date'],
    $post['filename'],
    $post['is_liked'] === '1',
    $post['like_count'],
    $post['comment_count'],
    $commentItems
);