<?php
require_once('includes/pagebuilder.class.php');
$_PAGE_BUILDER = new Pagebuilder('Upload');
?>
<div class="card mx-auto" style="width: 100%; max-width: 720px;">
    <canvas id="canvas" style="width: 100%; height: 100%;"></canvas>
    <div class="card-body">
        <p class="card-text" id="layer_info"></p>
        <button type="button" class="btn btn-success" id="webcam_pause", style="display: none;">Pause</button>
        <button type="button" class="btn btn-primary" id="layer_sel_up">↑</button>
        <button type="button" class="btn btn-primary" id="layer_sel_down">↓</button>
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <label class="btn btn-secondary active">
                <input type="radio" name="options" id="mode_move" autocomplete="off" checked> Move
            </label>
            <label class="btn btn-secondary">
                <input type="radio" name="options" id="mode_scale" autocomplete="off"> Scale
            </label>
            <label class="btn btn-secondary">
                <input type="radio" name="options" id="mode_rotate" autocomplete="off"> Rotate
            </label>
        </div>
        <button type="button" class="btn btn-dark" id="layer_move_up">↑</button>
        <button type="button" class="btn btn-dark" id="layer_move_down">↓</button>
        <button type="button" class="btn btn-danger" id="layer_delete">X</button>
        <hr>
        <div class="form-group">
            <label for="file_input">Add a custom sticker/image:</label>
            <input type="file" class="form-control-file" id="file_input" accept="image/*">
        </div>
        <hr>
        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
        <hr>
        <a class="btn btn-primary" style="color: #fff;">Upload</a>
    </div>
</div>

<video autoplay="true" id="video" style="display: none;"></video>

<script type="text/javascript">
async function try_webcam(video) {
    try {
        let stream = await navigator.mediaDevices.getUserMedia(
            {video: true, audio: false}
        );
        video.srcObject = stream;
    } catch(e) {
        let err_div = document.getElementById('server_messages');
        err_div.innerHTML = `
            <div class="alert alert-warning"><strong>Aww, we couldn't access your webcam.</strong><br>
            No worries, click the add custom sticker/image button to upload your own image!</div>
        `;
        window.scroll(0, 0);
    }
}

class OffsetVal {
    constructor(value) {
        this.value = value;
        this.offset = 0;
    }

    val() {
        return this.value + this.offset;
    }

    apply() {
        this.value += this.offset;
        this.offset = 0;
    }
}

class Layer {
    constructor(name, obj, rect) {
        this.name = name;
        this.obj = obj;
        this.width = rect.w;
        this.height = rect.h;

        this.rotation = new OffsetVal(0);
        this.x = new OffsetVal(rect.x);
        this.y = new OffsetVal(rect.y);
        this.x_scale = new OffsetVal(1);
        this.y_scale = new OffsetVal(1);
    }
}

function center_in(ow, oh, vw, vh) {
    let scale = Math.min(vw / ow, vh / oh);
    ow *= scale;
    oh *= scale;
    return {
        x: vw / 2 - ow / 2,
        y: vh / 2 - oh / 2,
        w: ow,
        h: oh
    };
}

function escape_html(unsafeText) {
    let div = document.createElement('div');
    div.innerText = unsafeText;
    return div.innerHTML;
}

window.onload = async () => {
    let layers = [];


    // Canvas stuff
    let canvas = document.getElementById('canvas');
    let ctx = canvas.getContext('2d');

    let width = 720;
    let height = 720;
    let center_x = width / 2;
    let center_y = height / 2;
    canvas.width = width;
    canvas.height = height;

    function loop() {
        requestAnimationFrame(loop);
        ctx.fillStyle = '#000';
        ctx.fillRect(0, 0, width, height);

        for (let layer of layers) {
            ctx.save();
            ctx.translate(center_x, center_y);
            ctx.rotate(layer.rotation.val());
            ctx.translate(-center_x, -center_y);
            ctx.translate(layer.x.val(), layer.y.val());
            ctx.drawImage(
                layer.obj,
                0, 0,
                layer.width * layer.x_scale.val(), layer.height * layer.y_scale.val()
            );
            ctx.restore();
        }
    }
    requestAnimationFrame(loop);


    // Layer management
    let selected_layer = null;
    let layer_info = document.getElementById('layer_info');

    function update_layer_info() {
        if (selected_layer == null) {
            out = `${layers.length} layer(s); Nothing selected, use the blue arrows to select a layer!`;
        } else {
            let sel_i = layers.findIndex(e => e == selected_layer);
            out = `${sel_i + 1}/${layers.length}: ${escape_html(selected_layer.name)}
                <br>Click and drag to transform the selected layer.`;
        }
        layer_info.innerHTML = out;
    }
    let make_layer_btn_click_handler = (e, f) => {
        document.getElementById(e).addEventListener('click', () => {
            let sel_i = layers.findIndex(item => item == selected_layer);
            f(sel_i);
            update_layer_info();
        })
    };
    make_layer_btn_click_handler('layer_sel_up', (sel_i) => {
        selected_layer = layers[sel_i + 1] || null;
    });
    make_layer_btn_click_handler('layer_sel_down', (sel_i) => {
        if (sel_i < 0)
            sel_i = layers.length;
        selected_layer = layers[sel_i - 1] || null;
    });
    layer_swap_sel = (sel_i, offset) => {
        if (layers[sel_i] == null || layers[sel_i + offset] == null)
            return;
        [layers[sel_i], layers[sel_i + offset]] = [layers[sel_i + offset], layers[sel_i]];
    };
    make_layer_btn_click_handler('layer_move_up', (sel_i) => layer_swap_sel(sel_i, 1));
    make_layer_btn_click_handler('layer_move_down', (sel_i) => layer_swap_sel(sel_i, -1));
    make_layer_btn_click_handler('layer_delete', (sel_i) => {
        layers = layers.filter(item => item !== selected_layer);
        selected_layer = null;
    });
    update_layer_info();


    // Add image layer
    let file_input = document.getElementById('file_input');
    file_input.addEventListener('change', () => {
        let reader = new FileReader();
        let file = file_input.files[0];

        reader.addEventListener('load', () => {
            let image = new Image();
            image.onload = () => {
                let rect = center_in(image.width, image.height, width, height);
                layers.push(new Layer(file.name, image, rect));
                update_layer_info();
            }
            image.onerror = () => {
                alert('What is the square root of a fish? Now I\'m sad.');
            }
            image.src = reader.result;
        });
        if (file) {
            reader.readAsDataURL(file);
            file_input.value = '';
        }
    });


    // webcam
    let video = document.getElementById('video');
    video.addEventListener('loadedmetadata', () => {
        let rect = center_in(video.videoWidth, video.videoHeight, width, height);
        layers.unshift(new Layer('Webcam', video, rect));
        update_layer_info();
        let pause_btn = document.getElementById('webcam_pause');
        pause_btn.style = '';
    });
    document.getElementById('webcam_pause').addEventListener('click', (e) => {
        e.target.innerHTML = video.paused ? 'Pause' : 'Play';
        video.paused ? video.play() : video.pause();
    });
    await try_webcam(video);
};
</script>
