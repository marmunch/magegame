document.addEventListener('DOMContentLoaded', function() {
    const burgerMenu = document.getElementById('burgerMenu');
    const menu = document.getElementById('menu');

    burgerMenu.addEventListener('click', function() {
        burgerMenu.classList.toggle('active');
        menu.classList.toggle('active');
    });
});