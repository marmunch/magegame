document.addEventListener('DOMContentLoaded', function() {
    const charIcons = document.querySelectorAll('.char-icon');
    const pickedCharIcon = document.querySelector('.picked-char-icon');
    const charName = document.querySelector('.name');
    const charInfo = document.querySelector('.info');
    const analogInfo = document.querySelector('.analog-info');

    charIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            const name = this.getAttribute('data-name');
            const info = this.getAttribute('data-info');
            const image = this.getAttribute('data-image');
            charName.textContent = name;
            charInfo.textContent = info;
            analogInfo.textContent = info;

            pickedCharIcon.style.backgroundImage = `url('${image}')`;
        });
    });
});