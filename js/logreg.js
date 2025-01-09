$(document).ready(function() {
    $('#loginForm').submit(function(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        console.log(formData);
        $.ajax({
            type: 'POST',
            url: 'login.php',
            data: formData,
            success: function(response) {
                console.log(response);
                if (response === "Вход успешен!") {
                    window.location.href = "php.php";
                } else {
                    $('#loginMessage').html(response);
                }
            }
        });
    });

    $('#registerForm').submit(function(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        console.log(formData);
        $.ajax({
            type: 'POST',
            url: 'reg.php',
            data: formData,
            success: function(response) {
                console.log(response);
                $('#registerMessage').html(response);
            }
        });
    });
});