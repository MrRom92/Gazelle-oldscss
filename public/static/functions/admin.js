"use strict";

function init_pitch(sel) {
    const input = document.getElementById('pitch');
    sel.addEventListener('change', () => {
        input.value = (sel.value === '0')
            ? ''
            : sel.options[sel.selectedIndex].text;
    });
}

function init_user_audit(button) {
    button.addEventListener('click', (e) => {
        const view = document.getElementById('historical-view');
        if (view.classList.contains('hidden')) {
            button.innerHTML = 'Hide';
            view.classList.remove('hidden');
        } else {
            button.innerHTML = 'View';
            view.classList.add('hidden');
        }
        e.preventDefault();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const selector = document.getElementById('news-pitch');
    if (selector) {
        init_pitch(selector);
    }
    const historical = document.getElementById('historical-notes');
    if (historical) {
        init_user_audit(historical);
    }
});
