# Эпичные схватки боевых магов

## Описание

"Эпичные схватки боевых магов" — это веб-приложение, которое реализует популярную настольную игру в цифровом формате. Игроки могут соревноваться друг с другом в реальном времени, используя магические заклинания и стратегии для победы.

## Технологии

- **Веб-сокеты**: Для реализации реального времени взаимодействия между игроками.
- **PHP**: Для серверной логики и управления данными.
- **JavaScript**: Для клиентской логики и взаимодействия с пользователем.

## Подключение веб-сокета

- cd public_html/mages
- php server.php

## Использование

1. Откройте браузер и перейдите по адресу `https://se.ifmo.ru/~s333884/mages/php.php`.
2. Зарегистрируйтесь или войдите в систему.
3. Создайте или присоединитесь к игровой комнате.
4. Начните игру и наслаждайтесь схватками!

## Описание API

Этот API предоставляет функциональность для управления игрой "Эпичные схватки боевых магов". Основные функции включают создание и управление комнатами, управление игроками, выбор персонажей, выполнение ходов и проверку состояния игры.

## Основные функции

1. **Создание комнаты**
   - Создает новую комнату и добавляет создателя в список игроков.

2. **Присоединение к комнате**
   - Добавляет игрока в существующую комнату.

3. **Получение информации о комнате**
   - Возвращает информацию о комнате и игроках в ней.

4. **Получение информации о текущей игре**
   - Возвращает информацию о текущей игре, включая состояние игроков и их карты.

5. **Выполнение хода**
   - Выполняет ход игрока, применяя эффекты выбранных карт.

6. **Проверка победителя**
   - Проверяет, есть ли победитель в текущей игре.

## Основные эндпоинты

### 1. Создание комнаты

- **URL:** `create_room.php`
- **Метод:** POST
- **Описание:** Создает новую комнату и добавляет создателя в список игроков.

### 2. Присоединение к комнате

- **URL:** `join_room.php`
- **Метод:** POST
- **Описание:** Добавляет игрока в существующую комнату.

### 3. Получение информации о комнате

- **URL:** `get_room_info.php`
- **Метод:** GET
- **Описание:** Возвращает информацию о комнате и игроках в ней.

### 4. Получение информации о текущей игре

- **URL:** `get_game_info.php`
- **Метод:** GET
- **Описание:** Возвращает информацию о текущей игре, включая состояние игроков и их карты.

### 5. Выполнение хода

- **URL:** `play_cards.php`
- **Метод:** POST
- **Описание:** Выполняет ход игрока, применяя эффекты выбранных карт.

### 6. Проверка победителя

- **URL:** `check_winner.php`
- **Метод:** GET
- **Описание:** Проверяет, есть ли победитель в текущей игре.

## Лицензия

Этот проект лицензирован под лицензией MIT. Подробности смотрите в файле [LICENSE](LICENSE).

## Контакты

Если у вас есть вопросы или предложения, пожалуйста, свяжитесь с нами:

- **Email**: munichka710@gmail.com
- **GitHub Issues**: [Открыть issue](https://github.com/ваш-пользователь/эпичные-схватки-боевых-магов/issues)

## Благодарности

Спасибо всем, кто внес свой вклад в этот проект!
