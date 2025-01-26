document.addEventListener('DOMContentLoaded', function() {
    const charIcons = document.querySelectorAll('.char-icon');
    const pickedCharIcon = document.querySelector('.picked-char-icon');
    const charName = document.querySelector('.name');
    const charInfo = document.querySelector('.info');
    const analogInfo = document.querySelector('.analog-info');
    const roomsContainer = document.getElementById('roomsContainer');
    const inviteBox = document.getElementById('invite-box');
    const closeInviteBtn = document.getElementById('close-btn');
    const yesBtn = document.querySelector('.yes');
    const noBtn = document.querySelector('.no');
    const inviterElement = document.querySelector('.inviter');

  
    inviteBox.style.display = 'none';

    charIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            const name = this.getAttribute('data-name');
            const info = this.getAttribute('data-info');
            const image = this.getAttribute('data-image');
            charName.textContent = name;
            charInfo.textContent = info;
            analogInfo.textContent = info;

            pickedCharIcon.style.backgroundImage = `url('${image}')`;

            fetch('update_character.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `character=${image}`
            })
            .then(response => response.text())
            .then(data => console.log(data))
            .catch(error => console.error('Error:', error));
        });
    });

    document.getElementById('createRoomButton').addEventListener('click', function() {
        fetch('create_room.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = `wait.html?room_id=${data.room_id}`;
            } else {
                alert('Ошибка при создании комнаты: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    function loadRooms() {
        fetch('get_rooms.php')
        .then(response => response.json())
        .then(data => {
            const existingRooms = new Set();
            roomsContainer.innerHTML = '';
            data.rooms.forEach(room => {
                if (!existingRooms.has(room.room_id)) {
                    existingRooms.add(room.room_id);
                    const roomDiv = document.createElement('div');
                    roomDiv.classList.add('room');
                    roomDiv.innerHTML = `
                        <p>${room.creator}</p>
                        <p>Комната ${room.room_id}</p>
                        <p>${room.player_count}/4</p>
                        <button type="button" class="come_in" data-room-id="${room.room_id}">Войти</button>
                    `;
                    roomsContainer.appendChild(roomDiv);
                }
            });

            document.querySelectorAll('.come_in').forEach(button => {
                button.addEventListener('click', function() {
                    const roomId = this.getAttribute('data-room-id');
                    fetch(`check_player_in_room.php?room_id=${roomId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.player_count < 4 && !data.is_player_in_room) {
                            fetch('join_room.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `room_id=${roomId}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    window.location.href = `wait.html?room_id=${roomId}`;
                                } else {
                                    alert('Ошибка при входе в комнату: ' + data.message);
                                }
                            })
                            .catch(error => console.error('Error:', error));
                        } else if (data.is_player_in_room) {
                            alert('Вы уже находитесь в этой комнате.');
                        } else {
                            alert('Комната уже заполнена.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });
        })
        .catch(error => console.error('Error:', error));
    }

    function checkInvitations() {
        fetch('check_invitations.php')
        .then(response => response.json())
        .then(data => {
            if (data.inviter_login && data.room_id) {
                console.log('Приглашение получено:', data);
                inviterElement.textContent = data.inviter_login;
                inviteBox.style.display = 'block';
                inviteBox.setAttribute('data-room-id', data.room_id);
            } else {
                inviteBox.style.display = 'none';
            }
        })
        .catch(error => console.error('Error:', error));
    }

    closeInviteBtn.addEventListener('click', function() {
        inviteBox.style.display = 'none';
    });

    yesBtn.addEventListener('click', function() {
        const roomId = inviteBox.getAttribute('data-room-id');
        fetch('join_room.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `room_id=${roomId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = `wait.html?room_id=${roomId}`;
            } else {
                alert('Ошибка при входе в комнату: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    noBtn.addEventListener('click', function() {
        inviteBox.style.display = 'none';
    });

    loadRooms();
    setInterval(loadRooms, 5000);
    setInterval(checkInvitations, 5000);
});