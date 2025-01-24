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
                console.log("Response from server:", response);
                if (response.trim() === "Вход успешен!") {
                    window.location.href = "php.php";
                } else {
                    $('#loginMessage').html(response);
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                $('#loginMessage').html('Ошибка: ' + error);
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
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                $('#registerMessage').html('Ошибка: ' + error);
            }
        });
    });
});