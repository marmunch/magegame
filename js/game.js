document.addEventListener('DOMContentLoaded', function() {
    const burgerMenu = document.getElementById('burgerMenu');
    const menu = document.getElementById('menu');
    const playButton = document.querySelector('.play');
    const timeElement = document.querySelector('.time');
    const effectElement = document.querySelector('.effect');
    const myCardsContainer = document.querySelector('.mycards');
    const livesElements = document.querySelectorAll('.lives-count');
    const tokensElements = document.querySelectorAll('.tokens-count');
    const cardsContainer = document.querySelector('.cards');    
    const playersContainer = document.getElementById('playersContainer');

    let timer;
    let selectedCards = { 1: null, 2: null, 3: null }; 
    let currentPlayerIndex = 0;
    let players = {};
    let room_id;
    let current_timee;
    let phase = 1; 
    let cardQueue = [];
    let allPlayersReady = false;
    let allCardsSelected = false;
    let myLogin;
    let checkPlayersReady = true; 

    burgerMenu.addEventListener('click', function() {
        burgerMenu.classList.toggle('active');
        menu.classList.toggle('active');
    });

    function updateCardsChosenStatus() {
        return fetch(`update_cards_chosen_status.php?room_id=${room_id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сети: ' + response.statusText);
                }
                return response.text(); 
            })
            .then(text => {
                console.log('Server response:', text); 
                try {
                    const data = JSON.parse(text); 
                    if (data.success) {
                        console.log('Cards chosen status updated successfully');
                    } else {
                        console.error('Ошибка:', data.message);
                    }
                } catch (e) {
                    console.error('Ошибка парсинга JSON:', e);
                }
            })
            .catch(error => console.error('Ошибка:', error));
    }

    function loadGameInfo() {
        const urlParams = new URLSearchParams(window.location.search);
        room_id = urlParams.get('room_id');
    
        fetch(`get_game_info.php?room_id=${room_id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.statusText);
            }
            return response.text(); 
        })
        .then(text => {
            console.log('Server response:', text); 
            try {
                const data = JSON.parse(text); 
                if (data.success) {
                    players = {};
                    myLogin = data.my_login;
                    current_timee = data.current_timee;
    
                    console.log('Loaded players:', data.players);
                    console.log('Current time:', current_timee);
    
                    
                    playersContainer.innerHTML = '';
    
                    data.players.forEach((player, index) => {
                        const playerIndex = index + 1; 
                        players[playerIndex] = player;
                        if (player.login !== myLogin) {
                            let playerElement = document.createElement('div');
                            playerElement.classList.add('player', `enemy${playerIndex}`); 
                            playerElement.dataset.id = player.id_player; 
                            playersContainer.appendChild(playerElement);
                            console.log(`Created player element for id ${player.id_player}`);
    
                            playerElement.innerHTML = `
                                <div class="icon" style="background-image: url('${player.character}')"></div>
                                <p class="nick">${player.login}</p>
                                <div class="tokens">Tokens: <span class="tokens-count">${player.tokens}</span></div>
                                <div class="lives">Lives: <span class="lives-count">${player.lives}</span></div>
                            `;
                        }
                    });
    
                    const myPlayer = Object.values(players).find(player => player.login === myLogin);
                    if (myPlayer) {
                        const myCharElement = document.querySelector('.mychar');
                        myCharElement.classList.add('player'); 
                        myCharElement.dataset.id = myPlayer.id_player; 
                        myCharElement.querySelector('.myicon').style.backgroundImage = `url('${myPlayer.character}')`;
                        myCharElement.querySelector('.lives-count').textContent = myPlayer.lives;
                        myCharElement.querySelector('.tokens-count').textContent = myPlayer.tokens;
                    } else {
                        console.error('My player not found');
                    }
    
                    const myCards = data.my_cards;
                    console.log('My Cards:', myCards); 
                    myCardsContainer.innerHTML = '';
                    if (myCards && myCards.length > 0) {
                        myCards.forEach(card => {
                            const cardElement = document.createElement('div');
                            cardElement.classList.add('mycard');
                            cardElement.style.backgroundImage = `url('${card.png}')`;
                            cardElement.dataset.cardtype = card.cardtype; 
                            cardElement.addEventListener('click', () => selectCard(card.id_card, card.descr, card.cardtype));
                            myCardsContainer.appendChild(cardElement);
                        });
                    } else {
                        console.error('No cards found for the player.');
                       
                        updateCardsInHand();
                    }
    
                    
                    allPlayersReady = data.all_players_ready;
                    console.log('All players ready:', allPlayersReady); 
    
                    
                    phase = data.phase;
                    if (phase === 2) {
                        console.log('Starting phase 2...');
                        startPhase2();
                    } else {
                        console.log('Phase 1 continues...');
                        startTimer(current_timee);
                    }
                } else {
                    console.error('Ошибка:', data.message);
                }
            } catch (e) {
                console.error('Ошибка парсинга JSON:', e);
            }
        })
        .catch(error => console.error('Ошибка:', error));
    }
    
    function selectCard(cardId, cardDescr, cardType) {
        if (!selectedCards[cardType] && !Object.values(selectedCards).includes(cardType)) {
            selectedCards[cardType] = cardId;
            effectElement.innerHTML += `<p>${cardDescr}</p>`;
        } else {
            console.error('Card type already selected or invalid card type');
        }
    }

    function startTimer(initialTime) {
        let timeLeft = initialTime;
        let startTime = Date.now();
    
        function updateTimer() {
            const elapsedTime = Math.floor((Date.now() - startTime) / 1000);
            timeLeft = initialTime - elapsedTime;
            timeElement.textContent = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(timer);
                playButton.disabled = false;
                playButton.textContent = 'Сыграть';
                playButton.style.backgroundColor = '#51E03F';
                playButton.style.cursor = 'pointer';
                playCards();
            }
        }
    
        updateTimer();
        timer = setInterval(updateTimer, 1000);
    
        setInterval(() => {
            fetch(`get_current_timee.php?room_id=${room_id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const serverTime = data.current_timee;
                    const timeDifference = serverTime - timeLeft;
                    startTime += timeDifference * 1000;
    
                    // Обновляем время в базе данных
                    updateGameTime(room_id, timeLeft);
                }
            })
            .catch(error => console.error('Ошибка:', error));
        }, 1000);
    }
    
    function updateGameTime(room_id, new_time) {
        fetch('update_current_timee.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `room_id=${room_id}&new_time=${new_time}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.statusText);
            }
            return response.text();
        })
        .then(text => {
            console.log('Server response:', text);
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    console.log('Game time updated successfully');
                } else {
                    console.error('Ошибка:', data.message);
                }
            } catch (e) {
                console.error('Ошибка парсинга JSON:', e);
            }
        })
        .catch(error => console.error('Ошибка:', error));
    }
    
    // Пример использования функции
    ;
    
    function playCards() {
        console.log('Playing cards...');
        console.log('Selected Cards:', selectedCards); 
        console.log('Room ID:', room_id); 

        fetch('play_cards.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `room_id=${room_id}&selected_cards=${encodeURIComponent(JSON.stringify(selectedCards))}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.statusText);
            }
            return response.text(); 
        })
        .then(text => {
            console.log('Server response:', text); 
            try {
                const data = JSON.parse(text); 
                if (data.success) {
                    players = {};
                    data.players.forEach((player, index) => {
                        const playerIndex = index + 1; 
                        players[playerIndex] = player;
                    });
                    console.log('Updated players:', players);

                    Object.values(players).forEach(player => {
                        let playerElement = document.querySelector(`.player[data-id="${player.id_player}"]`);
                        if (playerElement) {
                            console.log(`Updating player element for id ${player.id_player}`);
                            playerElement.querySelector('.lives-count').textContent = player.lives;
                            playerElement.querySelector('.tokens-count').textContent = player.tokens;
                        } else {
                            console.error(`Player element with id ${player.id_player} not found`);
                        }
                    });

                    selectedCards = { 1: null, 2: null, 3: null }; 
                    effectElement.innerHTML = '';

                    currentPlayerIndex = (currentPlayerIndex + 1) % Object.keys(players).length;

                    
                    allPlayersReady = Object.values(players).every(player => player.cards_chosen);
                    allCardsSelected = Object.values(selectedCards).every(card => card !== null);
                    if (allPlayersReady && allCardsSelected && phase === 1) {
                        phase = 2;
                        startPhase2();
                    } else {
                        loadGameInfo(); 
                    }
                } else {
                    alert('Ошибка при выполнении хода: ' + data.message);
                }
            } catch (e) {
                console.error('Ошибка парсинга JSON:', e);
            }
        })
        .catch(error => console.error('Ошибка:', error));
    }

    function startPhase2() {
        console.log('Phase 2 started!');
        checkPlayersReady = false;
    
        clearInterval(timer);
    
        updateCardsChosenStatus()
        .then(() => {
            fetch(`get_chosen_cards.php?room_id=${room_id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сети: ' + response.statusText);
                }
                return response.text();
            })
            .then(text => {
                console.log('Server response:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        const groupedCards = {};
                        data.chosen_cards.forEach(card => {
                            if (!groupedCards[card.id_player]) {
                                groupedCards[card.id_player] = [];
                            }
                            groupedCards[card.id_player].push(card);
                        });
    
                        for (const playerId in groupedCards) {
                            groupedCards[playerId].sort((a, b) => a.card_position - b.card_position);
                        }
    
                        const playerLeadValues = {};
                        for (const playerId in groupedCards) {
                            const cardType3 = groupedCards[playerId].find(card => card.cardtype === 3);
                            playerLeadValues[playerId] = cardType3 ? cardType3.lead : 0;
                        }
    
                        const sortedPlayerIds = Object.keys(playerLeadValues).sort((a, b) => playerLeadValues[b] - playerLeadValues[a]);
    
                        cardQueue = [];
                        sortedPlayerIds.forEach(playerId => {
                            for (let i = 1; i <= 3; i++) {
                                const card = groupedCards[playerId].find(card => card.card_position === i);
                                if (card) {
                                    cardQueue.push({
                                        ...card,
                                        id_player: playerId
                                    });
                                } else {
                                    cardQueue.push({
                                        id_card: 1,
                                        cardtype: 0,
                                        lead: 0,
                                        heal: 0,
                                        damage: 0,
                                        descr: 'Пустая карта',
                                        png: 'images/card0.jpg',
                                        card_position: i,
                                        id_player: playerId
                                    });
                                }
                            }
                        });
    
                        console.log('Chosen cards:', cardQueue);
                        playNextCard(myLogin);
                    } else {
                        console.error('Ошибка:', data.message);
                    }
                } catch (e) {
                    console.error('Ошибка парсинга JSON:', e);
                }
            })
            .catch(error => console.error('Ошибка:', error));
        })
        .catch(error => console.error('Ошибка обновления состояния cards_chosen:', error));
    }
    
    function playNextCard(myLogin) {
        if (cardQueue.length > 0) {
            const currentPlayerId = cardQueue[0].id_player;
            const playerCards = cardQueue.filter(card => card.id_player === currentPlayerId);
    
            cardsContainer.innerHTML = '';
    
            for (let i = 1; i <= 3; i++) {
                const cardContainer = document.createElement('div');
                cardContainer.classList.add('cardtype_' + i);
                cardsContainer.appendChild(cardContainer);
            }
    
            playerCards.forEach(card => {
                const cardElement = document.createElement('div');
                cardElement.classList.add('card', `cardtype_${card.cardtype}`);
                cardElement.style.backgroundImage = `url('${card.png}')`;
    
                const cardPosition = card.card_position;
                const cardContainer = document.querySelector(`.cardtype_${cardPosition}`);
                if (cardContainer) {
                    cardContainer.appendChild(cardElement);
                }
    
                applyCardEffects(card, myLogin);
            });
    
            setTimeout(() => {
                document.querySelectorAll('.card').forEach(cardElement => cardElement.remove());
    
                setTimeout(() => {
                    cardQueue = cardQueue.filter(card => card.id_player !== currentPlayerId);
    
                    console.log('Removing cards for player ID:', currentPlayerId);
                    fetch(`remove_spells.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id_player=${currentPlayerId}`
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Ошибка сети: ' + response.statusText);
                        }
                        return response.text();
                    })
                    .then(text => {
                        console.log('Server response:', text);
                        try {
                            const data = JSON.parse(text);
                            if (data.success) {
                                console.log('Cards removed from Spells table');
                            } else {
                                console.error('Ошибка:', data.message);
                            }
                        } catch (e) {
                            console.error('Ошибка парсинга JSON:', e);
                        }
                    })
                    .catch(error => console.error('Ошибка:', error));
    
                    playNextCard(myLogin);
                }, 1000);
            }, 1000);
        } else {
            fetch(`check_spells_empty.php?room_id=${room_id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сети: ' + response.statusText);
                }
                return response.text();
            })
            .then(text => {
                console.log('Server response:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.success && data.is_empty) {
                        phase = 1;
                        loadGameInfo();
                        updateCardsInHand();
                        resetCardsChosen();
                        cardsContainer.innerHTML = '';
                        checkPlayersReady = true;
    
                        // Сброс таймера до 120 секунд
                        startTimer(120);
    
                        // Активация кнопки "Сыграть"
                        playButton.disabled = false;
                        playButton.textContent = 'Сыграть';
                        playButton.style.backgroundColor = '#51E03F';
                        playButton.style.cursor = 'pointer';
    
                        // Проверка наличия победителя
                        checkWinner();
                    } else {
                        console.log('Waiting for more cards to be played...');
                    }
                } catch (e) {
                    console.error('Ошибка парсинга JSON:', e);
                }
            })
            .catch(error => console.error('Ошибка:', error));
        }
    }
    
    function checkWinner() {
        fetch(`check_winner.php?room_id=${room_id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.statusText);
            }
            return response.text();
        })
        .then(text => {
            console.log('Server response:', text);
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    if (data.winner) {
                        console.log('Winner found:', data.winner);
                        // Перенаправление на php.php
                        window.location.href = 'php.php';
                    } else {
                        console.log('No winner yet');
                    }
                } else {
                    console.error('Ошибка:', data.message);
                }
            } catch (e) {
                console.error('Ошибка парсинга JSON:', e);
            }
        })
        .catch(error => console.error('Ошибка:', error));
    }
    
    function applyCardEffects(card, myLogin) {
        console.log(`applyCardEffects called with card: ${card.descr}`);
        console.log(`Applying effects for card: ${card.descr}`);
        console.log(`Card damage: ${card.damage}, Card heal: ${card.heal}`);
    
        fetch('apply_card_effects.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `room_id=${room_id}&card_id=${card.id_card}&card_position=${card.card_position}&player_id=${card.id_player}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.statusText);
            }
            return response.text();
        })
        .then(text => {
            console.log('Server response:', text);
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    console.log('Card effects applied successfully');
                    // Обновите интерфейс после применения эффектов карт
                    updatePlayerUI();
                } else {
                    console.error('Ошибка:', data.message);
                }
            } catch (e) {
                console.error('Ошибка парсинга JSON:', e);
            }
        })
        .catch(error => console.error('Ошибка:', error));
    }
    
    function updatePlayerUI() {
        // Обновите интерфейс игроков после применения эффектов карт
        fetch(`get_game_info.php?room_id=${room_id}`)
        .then(response => response.text())
        .then(text => {
            console.log('Server response:', text);
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    players = {};
                    data.players.forEach((player, index) => {
                        const playerIndex = index + 1;
                        players[playerIndex] = player;
                    });
                    console.log('Updated players:', players);
    
                    Object.values(players).forEach(player => {
                        let playerElement = document.querySelector(`.player[data-id="${player.id_player}"]`);
                        if (playerElement) {
                            console.log(`Updating player element for id ${player.id_player}`);
                            playerElement.querySelector('.lives-count').textContent = player.lives;
                            playerElement.querySelector('.tokens-count').textContent = player.tokens;
                        } else {
                            console.error(`Player element with id ${player.id_player} not found`);
                        }
                    });
    
                    const myPlayer = Object.values(players).find(player => player.login === myLogin);
                    if (myPlayer) {
                        console.log(`Updating my lives to ${myPlayer.lives}`);
                        document.querySelector('.mychar .lives-count').textContent = myPlayer.lives;
                        document.querySelector('.mychar .tokens-count').textContent = myPlayer.tokens;
                    } else {
                        console.error('My player not found');
                    }
                } else {
                    console.error('Ошибка:', data.message);
                }
            } catch (e) {
                console.error('Ошибка парсинга JSON:', e);
            }
        })
        .catch(error => console.error('Ошибка:', error));
    }
    
    function updateCardsInHand() {
        fetch(`update_cards_in_hand.php?room_id=${room_id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.statusText);
            }
            return response.text(); 
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    
                    myCardsContainer.innerHTML = '';
                    if (data.cards && data.cards.length > 0) {
                        data.cards.forEach(card => {
                            const cardElement = document.createElement('div');
                            cardElement.classList.add('mycard');
                            cardElement.style.backgroundImage = `url('${card.png}')`;
                            cardElement.dataset.cardtype = card.cardtype; 
                            cardElement.addEventListener('click', () => selectCard(card.id_card, card.descr, card.cardtype));
                            myCardsContainer.appendChild(cardElement);
                        });
                    } else {
                        console.error('No cards found for the player.');
                    }
                } else {
                    console.error('Ошибка:', data.message);
                }
            } catch (e) {
                console.error('Ошибка парсинга JSON:', e);
            }
        })
        .catch(error => console.error('Ошибка:', error));
    }

    function resetCardsChosen() {
        fetch(`reset_cards_chosen.php?room_id=${room_id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.statusText);
            }
            return response.text(); 
        })
        .then(text => {
            console.log('Server response:', text); 
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    console.log('Cards chosen reset successfully');
                } else {
                    console.error('Ошибка:', data.message);
                }
            } catch (e) {
                console.error('Ошибка парсинга JSON:', e);
            }
        })
        .catch(error => console.error('Ошибка:', error));
    }

    function checkAllPlayersReady() {
        if (!checkPlayersReady) {
            return; 
        }
        console.log('Checking all players ready...');
        fetch(`get_game_info.php?room_id=${room_id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.statusText);
            }
            return response.text(); 
        })
        .then(text => {
            console.log('Server response:', text);
            try {
                const data = JSON.parse(text); 
                if (data.success) {
                    allPlayersReady = data.all_players_ready;
                    console.log('Checking all players ready:', allPlayersReady); 
                    if (allPlayersReady && phase === 1) {
                        console.log('Starting phase 2...');
                        phase = 2;
                        startPhase2();
                    }
                } else {
                    console.error('Ошибка:', data.message);
                }
            } catch (e) {
                console.error('Ошибка парсинга JSON:', e);
            }
        })
        .catch(error => console.error('Ошибка:', error));
    }

    playButton.addEventListener('click', function() {
        clearInterval(timer);
        playButton.disabled = true;
        playButton.textContent = 'Играет...';
        playButton.style.backgroundColor = '#E42828';
        playButton.style.cursor = 'not-allowed';
        playCards();
    });

    updateCardsInHand();

    loadGameInfo();

    setInterval(checkAllPlayersReady, 1000);
});
