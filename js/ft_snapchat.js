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

function make_threshold_alpha_img(img, ctx, canvas) {
    ctx.globalCompositeOperation = 'source-over';
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    ctx.save();
    ctx.scale(0.997, 0.997);
    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
    ctx.restore();

    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const pixels = imageData.data;
    for (let i = 3, n = canvas.width * canvas.height * 4; i < n; i += 4) {
        pixels[i] = pixels[i] < 50 ? 0 : 255
    }
    ctx.putImageData(imageData, 0, 0);
    const e = document.createElement('img');
    e.src = canvas.toDataURL('image/png');
    return e;
}

class Layer {
  constructor(obj, originalWidth, originalHeight, scale, ctx, canvas) {
        this.obj = obj;
        this.border_img = make_threshold_alpha_img(obj, ctx, canvas);
        const relativeScale = Math.min(
            canvas.width / originalWidth,
            canvas.height / originalHeight
        );
        this.width = originalWidth * relativeScale;
        this.height = originalHeight * relativeScale;

        this.transform = {
            rotation: 0,
            x: canvas.width / 2  - this.width * scale / 2,
            y: canvas.height / 2  - this.height * scale / 2,
            x_scale: scale,
            y_scale: scale
        };
        this.clearTransform();
    }

    getTransform() {
        return {
            rotation: this.transform.rotation + this.transform_offset.rotation,
            x: this.transform.x + this.transform_offset.x,
            y: this.transform.y + this.transform_offset.y,
            x_scale: this.transform.x_scale + this.transform_offset.x_scale,
            y_scale: this.transform.y_scale + this.transform_offset.y_scale
        };
    }

    applyTransform() {
        this.transform = this.getTransform();
        this.clearTransform();
    }

    clearTransform() {
        this.transform_offset = {
            rotation: 0,
            x: 0,
            y: 0,
            x_scale: 0,
            y_scale: 0
        };
    }

    doTransform(ctx) {
        const t = this.getTransform();

        const center_x = t.x + this.width / 2 * t.x_scale;
        const center_y = t.y + this.height / 2 * t.y_scale;

        ctx.translate(center_x, center_y);
        ctx.rotate(t.rotation * Math.PI / 180);
        ctx.translate(-center_x, -center_y);
        ctx.scale(t.x_scale, t.y_scale);
        ctx.translate(t.x / t.x_scale, t.y / t.y_scale);

        return t;
    }
}

window.onload = async () => {
    let layers = []
    let selected_layer = null;
    let tool_mode = null;
    let dragStartX = 0;
    let dragStartY = 0;
    let dragCurrentX = 0;
    let dragCurrentY = 0;
    let isDragging = false

    let canvas = document.getElementById('canvas');
    let ctx = canvas.getContext('2d');
    let tmp_canvas = document.createElement('canvas');
    let tmp_ctx = tmp_canvas.getContext('2d');

    canvas.width = 720;
    canvas.height = 720;
    tmp_canvas.width = 720;
    tmp_canvas.height = 720;

    function drawBorderToTmp(layer) {
        const lineWidth = 2;
        const offsets = [0, -1, 1, 0, 0, 1, 0, 1, -1, 0, -1, 0, 0, -1, 0, -1];

        tmp_ctx.globalCompositeOperation = "source-over";
        tmp_ctx.clearRect(0, 0, tmp_canvas.width, tmp_canvas.height);

        tmp_ctx.save();
        const t = layer.doTransform(tmp_ctx);
        for(let i = 0; i < offsets.length; i += 2) {
            tmp_ctx.translate(offsets[i] * lineWidth / t.x_scale, offsets[i + 1] * lineWidth / t.y_scale);
            tmp_ctx.drawImage(layer.border_img, 0, 0, layer.width, layer.height);
        }

        tmp_ctx.restore();

        tmp_ctx.globalCompositeOperation = "source-in";
        tmp_ctx.fillStyle = "#ff00ff";
        tmp_ctx.fillRect(0, 0, tmp_canvas.width, tmp_canvas.height);

        tmp_ctx.save();
        layer.doTransform(tmp_ctx);

        tmp_ctx.globalCompositeOperation = "destination-out";
        tmp_ctx.drawImage(
            layer.border_img,
            0, 0,
            layer.width, layer.height
        );

        tmp_ctx.restore();
    }

    function loop() {
        ctx.fillStyle = '#000';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        for (let layer of layers) {
            ctx.save();
            const t = layer.doTransform(ctx);
            ctx.drawImage(
                layer.obj,
                0, 0,
                layer.width, layer.height
            );
            ctx.restore();
        }

        if (selected_layer) {
            drawBorderToTmp(selected_layer);
            ctx.drawImage(tmp_canvas, 0, 0);
        }

        requestAnimationFrame(loop);
    }
    requestAnimationFrame(loop);


    // Layer management
    function update_layer_info() {
        if (selected_layer == null) {
            out = `${layers.length} layer(s); Nothing selected`;
        } else {
            let sel_i = layers.findIndex(e => e == selected_layer);
            out = `${sel_i + 1}/${layers.length} selected`;
        }
        document.getElementById('layer_info').innerHTML = out;
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
    let layer_swap_sel = (sel_i, offset) => {
        if (layers[sel_i] == null || layers[sel_i + offset] == null)
            return;
        [layers[sel_i], layers[sel_i + offset]] = [layers[sel_i + offset], layers[sel_i]];
    };
    make_layer_btn_click_handler('layer_move_up', (sel_i) => layer_swap_sel(sel_i, 1));
    make_layer_btn_click_handler('layer_move_down', (sel_i) => layer_swap_sel(sel_i, -1));
    make_layer_btn_click_handler('layer_delete', (sel_i) => {
        layers = layers.filter(item => item !== selected_layer);
        selected_layer = layers[sel_i] || layers[sel_i - 1] || null;
    });
    for (const mode of ['move', 'scale', 'rotate']) {
        document.getElementById(`mode_${mode}`).addEventListener('click', () => {
            if (tool_mode) {
                document.getElementById(`mode_${tool_mode}`).parentNode.classList.remove('active');
            }
            tool_mode = mode;
            document.getElementById(`mode_${tool_mode}`).parentNode.classList.add('active');
        });
    }
    document.getElementById(`mode_move`).click();
    update_layer_info();


    // Dragging
    function onMouseDown(x, y) {
        dragStartX = x;
        dragStartY = y;
        isDragging = true;
    }
    function onMouseUp(x, y) {
        isDragging = false;
        if (selected_layer) {
            selected_layer.applyTransform();
        }
        let deltaX = x - dragStartX;
        let deltaY = y - dragStartY;
        let distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
        if (distance > 10) {
            return;
        }

        const rect = canvas.getBoundingClientRect()
        x = (x - rect.left) / rect.width * canvas.width;
        y = (y - rect.top) / rect.height * canvas.height;

        tmp_ctx.globalCompositeOperation = "source-over";
        tmp_ctx.clearRect(0, 0, tmp_canvas.width, tmp_canvas.height);

        tmp_ctx.globalCompositeOperation = "xor";
        hit_layers = [];
        let oldAlpha = tmp_ctx.getImageData(x, y, 1, 1).data[3];
        for (const layer of layers) {
            tmp_ctx.save();
            layer.doTransform(tmp_ctx);
            tmp_ctx.drawImage(
                layer.obj,
                0, 0,
                layer.width, layer.height
            );
            tmp_ctx.restore();

            let alpha = tmp_ctx.getImageData(x, y, 1, 1).data[3];
            if (alpha != oldAlpha) {
                hit_layers.push(layer);
            }
            oldAlpha = alpha;
        }
        if (hit_layers.length === 0) {
            selected_layer = null;
            update_layer_info();
            return;
        }
        hit_layers.reverse();
        let sel_i = hit_layers.findIndex(item => item == selected_layer);
        selected_layer = hit_layers[sel_i + 1] || hit_layers[0];
        update_layer_info();
    }
    function onMouseMove(x, y) {
        if (!isDragging || !selected_layer) {
            return;
        }
        dragCurrentX = x;
        dragCurrentY = y;
        let deltaX = x - dragStartX;
        let deltaY = y - dragStartY;
        if (tool_mode === 'move') {
            selected_layer.transform_offset.x = deltaX;
            selected_layer.transform_offset.y = deltaY;
        }
        if (tool_mode === 'scale') {
            selected_layer.transform_offset.x_scale = deltaX / canvas.width;
            selected_layer.transform_offset.y_scale = deltaY / canvas.height;
        }
        if (tool_mode === 'rotate') {
            selected_layer.transform_offset.rotation = deltaX / 10;
        }
    }
    function onMouseOut() {
        if (selected_layer) {
            selected_layer.clearTransform();
        }
        isDragging = false;
    }
    canvas.addEventListener('mousedown', e => onMouseDown(e.clientX, e.clientY));
    canvas.addEventListener('mouseup', e => onMouseUp(e.clientX, e.clientY));
    canvas.addEventListener('mousemove', e => onMouseMove(e.clientX, e.clientY));
    canvas.addEventListener('mouseout', e => onMouseOut());

    canvas.addEventListener('touchstart', e => {
        e.preventDefault();
        onMouseDown(e.touches[0].clientX, e.touches[0].clientY)
    }, false);
    canvas.addEventListener('touchend', e => {
        e.preventDefault();
        onMouseUp(dragCurrentX, dragCurrentY)
    }, false);
    canvas.addEventListener('touchmove', e => {
        e.preventDefault();
        onMouseMove(e.touches[0].clientX, e.touches[0].clientY)
    }, false);
    canvas.addEventListener('touchout', e => {
        e.preventDefault();
        onMouseOut();
    }, false);

    // Add image layer
    let file_input = document.getElementById('file_input');
    file_input.addEventListener('change', () => {
        let reader = new FileReader();
        let file = file_input.files[0];

        reader.addEventListener('load', () => {
            let image = new Image();
            image.onload = () => {
                layers.push(new Layer(
                    image,
                    image.width,
                    image.height,
                    layers.length === 0 ? 1 : 0.5,
                    tmp_ctx,
                    tmp_canvas
                ));
                selected_layer = layers[layers.length - 1];
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

    for (const element of document.getElementsByClassName('thumbnail')) {
        element.addEventListener('click', () => {
            layers.push(new Layer(
                element,
                element.naturalWidth,
                element.naturalHeight,
                layers.length === 0 ? 1 : 0.5,
                tmp_ctx,
                tmp_canvas
            ));
            selected_layer = layers[layers.length - 1];
            update_layer_info();
        })
    }

    // webcam
    let video = document.getElementById('video');
    video.addEventListener('loadedmetadata', () => {
        layers.unshift(new Layer(
            video,
            video.videoWidth,
            video.videoHeight,
            1,
            tmp_ctx,
            tmp_canvas
        ));
        selected_layer = layers[0];
        update_layer_info();
        document.getElementById('webcam_pause').style = '';
    })
    document.getElementById('webcam_pause').addEventListener('click', async (e) => {
        const hasWebcam = layers.some(v => v.obj.nodeName.toLowerCase() === 'video')
        if (!hasWebcam) {
            await try_webcam(video);
            return;
        }
        video.paused ? video.play() : video.pause();
    });
    await try_webcam(video);
}