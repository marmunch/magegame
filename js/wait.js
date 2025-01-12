console.log('JavaScript file loaded');

function toggleButton() {
    var button = document.getElementsByClassName('btn')[0];
    if (button.textContent === 'Готов') {
        button.textContent = 'Не готов';
        button.style.setProperty('--btn-bg-color', '#E42828');
        button.style.setProperty('--btn-hover-bg-color', '#F19393');
    } 
    else {
        button.textContent = 'Готов';
        button.style.setProperty('--btn-bg-color', '#51E03F');
        button.style.setProperty('--btn-hover-bg-color', '#8CEC7F');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const inviteButton = document.querySelector('.invite');
    const popup = document.getElementById('popup');
    const closeBtn = document.getElementById('close-btn');

    inviteButton.addEventListener('click', function() {
        popup.style.display = 'block';
    });

    closeBtn.addEventListener('click', function() {
        popup.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target == popup) {
            popup.style.display = 'none';
        }
    });
});