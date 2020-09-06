<?php
require_once('includes/pagebuilder.class.php');
require_once('includes/htmltag.class.php');
require_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $img_data = preg_replace('/^data:image\/\w+;base64,/', '', $_POST['image']);
    $img_data = base64_decode($img_data);
    if ($img_data === false)
        die_with_alert('danger', 'Error', 'Invalid or missing image data');

    $WIDTH = 720;
    $HEIGHT = 720;

    $img = imagecreatefromstring($img_data);
    if ($img === false)
        die_with_alert('danger', 'Error', 'Invalid image data');
    if (imagesx($img) !== $WIDTH || imagesy($img) !== $HEIGHT)
        die_with_alert('danger', 'Error', 'Invalid image data');

    $out_img = imagecreatetruecolor($WIDTH, $HEIGHT); 
    imagefill($out_img, 0, 0, imagecolorallocatealpha($out_img, 0, 0, 0, 255)); 
    imagecopy($out_img, $img, 0, 0, 0, 0, $WIDTH, $HEIGHT);

    ob_start(); 
    imagepng($out_img);
    $out_img_data = ob_get_contents(); 
    ob_end_clean(); 

    $filename = hash('sha256', $out_img_data) . '.png';

    try {
        $DATABASE->add_image($_SESSION['id'], $filename);
    } catch (RuntimeException $e) {
        die_with_alert('danger', 'Error', $e->getMessage());
    }

    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/uploads/${filename}", $out_img_data);
    die_with_alert('success', 'Success', 'Your image has been posted', 200);
}

try {
    $posts = $DATABASE->get_user_images($_SESSION['id']);
} catch (RuntimeException $e) {
    die_with_alert('danger', 'Error', $e->getMessage());
}

$_PAGE_BUILDER = new Pagebuilder('Upload');
?>

<div class="card mx-auto" style="width: 100%; max-width: 720px;">
        <div class="btn-group btn-group-toggle" style="align-items: center; flex-wrap: wrap; justify-content: space-between;" data-toggle="buttons">
            <div class="btn-group btn-group-toggle">
                <button type="button" class="btn btn-success" onclick="document.getElementById('file_input').click();">＋</button>
                <button type="button" class="btn btn-success" id="webcam_pause", style="display: none;">⏯</button>

                <button type="button" class="btn btn-primary" id="layer_sel_up">↑</button>
                <button type="button" class="btn btn-primary" id="layer_sel_down">↓</button>

                <label class="btn btn-secondary">
                    <input type="radio" name="options" id="mode_move" autocomplete="off" checked> Move
                </label>
                <label class="btn btn-secondary">
                    <input type="radio" name="options" id="mode_scale" autocomplete="off"> Scale
                </label>
                <label class="btn btn-secondary">
                    <input type="radio" name="options" id="mode_rotate" autocomplete="off"> Rotate
                </label>

                <button type="button" class="btn btn-dark" id="layer_move_up">↑</button>
                <button type="button" class="btn btn-dark" id="layer_move_down">↓</button>
                <button type="button" class="btn btn-danger" id="layer_delete">X</button>
            </div>

            <p class="card-text" id="layer_info" style="padding: 8px"></p>
        </div>

    <canvas id="canvas" style="width: 100%; height: 100%;"></canvas>

    <div class="card-body">
        <a class="btn btn-primary" style="color: #fff;" id="upload">Upload</a>
        <hr>

        <div class="card mx-auto" style="width: 100%; height: 200px;">
            <div class="card-body" style="display: flex; flex-wrap: wrap; overflow-y: scroll; background-color: #333; padding: 10px;">
                <?php
                foreach (glob('stickers/*.*') as $file) {
                    HTMLTag('img')
                    ->setAttr('src', $file)
                    ->setAttr('class', 'thumbnail sticker')
                    ->setAttr('style', 'padding: 5px; width: 25%;')
                    ->print();
                }
                ?>
            </div>
        </div>
        <div class="form-group">
            <label for="file_input">Add a custom sticker/image:</label>
            <input type="file" class="form-control-file" id="file_input" accept="image/*">
        </div>
        <hr>

        <div class="card mx-auto" style="width: 100%; max-height: 200px;">
            <div class="card-body" style="display: flex; flex-wrap: wrap; overflow-y: scroll; padding: 10px;">
                <?php
                foreach ($posts as $post) {
                    HTMLTag('a')
                    ->setAttr('href', "comments.php?id={$post['id']}")
                    ->setAttr('class', 'thumbnail')
                    ->setAttr('style', 'width: 25%;')
                    ->append(
                        HTMLTag('img')
                        ->setAttr('src', "uploads/{$post['filename']}")
                        ->setAttr('class', 'thumbnail')
                    )
                    ->print();
                }
                ?>
            </div>
        </div>
    </div>
</div>

<video autoplay="true" id="video" style="display: none;"></video>

<script src="js/ft_snapchat.js"></script>