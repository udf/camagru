function form_submit(event) {
    event.preventDefault();

    let form = event.target;
    let data = new FormData(form);
    let req = new XMLHttpRequest();
    let output = document.getElementById('server_messages');

    req.open('POST', '', true);
    req.onload = function(event) {
        output.innerHTML = req.response;
        let redir_location = form.getAttribute('redirect');
        if (redir_location != null && req.status == 200) {
            window.location.replace(redir_location);
        }
    };
    req.send(data);
}

// Attach an event listener to the first form on the page
function form_init() {
    let form = document.forms[0];
    if (form === undefined)
        return;
    form.addEventListener('submit', form_submit, false);
}

function init() {
    form_init();
}

document.addEventListener('DOMContentLoaded', init, false);
