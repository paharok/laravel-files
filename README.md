##  Файлменеджер для LARAVEL

### Встановлення
    composer require paharok/laravel-files

### Публікація
    php artisan vendor:publish --tag=laravel-files-assets --force
## Оновлення
### Очищення кешу компонентів
    php artisan view:clear

### Обмеження доступу
За замовчуванням файлменеджер доступний будь-якому залогіненому користувачу (`auth_middleware => true`, стандартне Laravel `auth`).

Щоб звузити доступ до конкретних користувачів — наприклад, лише адмінам (`is_admin` в БД) — опублікуйте конфіг і задайте `users_access` у `config/laravelfiles.php`:

    php artisan vendor:publish --tag=laravel-files-assets --force

```php
'auth_middleware' => true,
'users_access' => ['is_admin' => [true]],
```

Це означає: доступ мають лише залогінені користувачі, у яких `$user->is_admin` строго дорівнює `true`. Можна перелічити декілька дозволених значень (`['is_admin' => [true, false]]`) або декілька полів одразу — тоді мають збігтись усі (AND):

```php
'users_access' => [
    'is_admin' => [true],
    'role' => ['admin', 'editor'],
],
```

Порожній `users_access` (за замовчуванням) — без додаткових обмежень, лише `auth_middleware`.

### Заборонені розширення файлів
За замовчуванням заборонено завантажувати файли з розширеннями, які потенційно можуть виконатись на сервері або втручатись у його конфігурацію (`php`, `phtml`, `phar`, `cgi`, `pl`, `sh`, `htaccess`, `exe` тощо — повний список у `config/laravelfiles.php`, ключ `forbidden_extensions`). Перевіряється кожен сегмент імені файлу після крапки, тому `shell.php.jpg` теж буде відхилено.

Щоб змінити список — опублікуйте конфіг і відредагуйте його:

    php artisan vendor:publish --tag=laravel-files-assets --force

Файли з забороненим розширенням просто пропускаються при завантаженні; їхні імена повертаються у відповіді (`rejected`) і показуються користувачу.

### Підключення стилей та скриптів
Пакет має дві версії JS-скрипта — оберіть одну з них.

#### Варіант 1: з jQuery (потребує підключеного jQuery на сторінці)
    <script src="{{ asset('/vendor/laravel-files/js/pahar-laravel-files.js') }}"></script>
    <link href="{{ asset('/vendor/laravel-files/css/pahar-laravel-files.css') }}" rel="stylesheet">

#### Варіант 2: без jQuery (чистий JavaScript)
    <script src="{{ asset('/vendor/laravel-files/js/pahar-laravel-files-vanilla.js') }}"></script>
    <link href="{{ asset('/vendor/laravel-files/css/pahar-laravel-files.css') }}" rel="stylesheet">

Підключати обидва скрипти одночасно не потрібно — вони дублюють одну й ту саму функціональність.

### Компоненти
#### Вивести поле для вибору файлу
    <x-plf-field :name="$name" :value="$value"/>

#### Вивести поля для вибору файлів
    <x-plf-field-multiple :name="name[]" :values="array $value"/>

### Підтримка CKEditor 4
Щоб відкрити файлменеджер із CKEditor 4 і вставити обраний файл у редактор, перед відкриттям попапу присвойте `plf.target` інстанс редактора:

    plf.open();
    plf.target = CKEDITOR.instances['editor1'];
    plf.getData(lastPath);

### Підтримка Quill (ql-editor)
Аналогічно працює й з Quill — присвойте `plf.target` інстанс Quill (той, у якого `.root` має клас `ql-editor`):

    plf.open();
    plf.target = quillInstance;
    plf.getData(lastPath);

Зображення вставляються як embed (`insertEmbed`), інші файли — посиланням для завантаження.
