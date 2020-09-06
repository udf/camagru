function form_submit(event) {
    event.preventDefault();

    let form = event.target;
    let data = new FormData(form);
    let req = new XMLHttpRequest();
    let output = document.getElementById('server_messages');

    req.open('POST', form.getAttribute('action') || '', true);
    req.onload = function(event) {
        output.innerHTML = req.response;
        let redir_location = form.getAttribute('redirect');
        if (redir_location != null && req.status == 200) {
            window.location.replace(redir_location);
            return;
        }
        output.scrollIntoView();
    };
    req.send(data);
}

function form_init() {
    for (let form of document.forms) {
        form.addEventListener('submit', form_submit, false);
    }
}

function init() {
    form_init();
}

function toggle_like(e) {
    let like_count = parseInt(e.innerHTML) || 0;
    const id = e.closest('.card').id;

    let data = new FormData();
    data.append('id', id);

    let req = new XMLHttpRequest();
    req.open('POST', 'controller/toggle_like.php', true);
    req.onload = () => {
        let err_div = document.getElementById('server_messages');
        if (req.status !== 200) {
            err_div.innerHTML = req.response;
            err_div.scrollIntoView();
            return;
        }

        const isLiked = req.response === '1';
        like_count += isLiked ? 1 : -1;
        e.innerHTML = `${like_count}`;
        
        e.classList.remove('like-icon');
        e.classList.remove('unlike-icon');
        e.classList.add(isLiked ? 'unlike-icon' : 'like-icon');
    };
    req.send(data);
}

document.addEventListener('DOMContentLoaded', init, false);
