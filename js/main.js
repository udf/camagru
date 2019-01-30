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
        } else {
            window.scroll(0, 0);
        }
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

document.addEventListener('DOMContentLoaded', init, false);
