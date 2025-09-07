"use strict";

async function previewTitle() {
    const form = new FormData();
    form.append('auth', document.body.dataset.auth);
    form.append('title', document.getElementById('title').value);
    form.append('label', document.forms['custom-title-preview'].elements['label'].value);
    const response = await fetch(
        '?action=prepare', {
            'method': 'POST',
            'body': form,
        }
    );
    const data = await response.json();
    document.getElementById('preview').innerHTML =
        data.status === 'success'
            ? data.response[0]
            : data.status;
}

async function validateBonusUsername() {
    const status   = document.getElementById('bonus-other-status');
    const username = document.getElementById('bonus-user-other').value.trim();
    const purchase = document.getElementById('bonus-other-purchase')
    purchase.disabled = true;
    if (username === '') {
        status.innerHTML = '';
        return;
    }
    const form = new FormData();
    form.append('auth', document.body.dataset.auth);
    form.append('bonus-user-other', username);
    const response = await fetch(
        '?action=prepare', {
            'method': 'POST',
            'body': form,
        }
    );
    const data = await response.json();
    let message;
    if (data.status !== 'success') {
        message = status;
    } else {
        const user = data.response;
        if (!user.found) {
            message = '⛔️ ' + 'user not found';
        } else if (user.id == document.body.dataset.id) {
            message = '⛔️ You cannot gift tokens to yourself';
        } else if (!user.enabled) {
            message = '⛔️ ' + user.username + ' is currently not enabled';
        } else if (!user.accept) {
            message = '🚫 ' + user.username + ' does not wish to receive tokens';
        } else {
            message = '✅';
            document.forms['bonus-other'].elements['user'].value = user.username;
            purchase.disabled = false;
        }
    }
    status.innerHTML = message;
}

document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('preview-title');
    if (button) {
        button.addEventListener('click', () => previewTitle());
    }
    const username = document.getElementById('bonus-user-other');
    if (username) {
        username.addEventListener('change', () => validateBonusUsername());
    }
});
