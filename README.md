##  Файлменеджер для LARAVEL

### Встановлення
    composer require paharok/laravel-files

### Публікація
    php artisan vendor:publish --tag=laravel-files-assets --force

### Вимоги
- PHP 8.1+
- [Intervention Image](https://image.intervention.io/) 3.x або 4.x (для 4.x потрібен PHP 8.3+). Composer сам підбере версію під ваш PHP; пакет працює з обома.

## Оновлення з 2.x на 3.0
Версія 3.0 містить зміни, що ламають сумісність:

- **Intervention Image 2.x → 3.x / 4.x.** Пакет більше не працює з Intervention 2.x, тому `composer update` підніме Intervention у вашому застосунку. Якщо ваш власний код використовує API 2.x (`Image::make()`, `->fit()`, `->resize()` тощо) — перепишіть його під [v3](https://image.intervention.io/v3) або [v4](https://image.intervention.io/v4).
- **Фасад `Intervention\Image\Facades\Image` більше не існує.** Якщо в `config/app.php` є рядок `'Image' => Intervention\Image\Facades\Image::class` — видаліть його, інакше Laravel впаде з «Class not found». Пакету цей аліас не потрібен.
- **PHP 8.1+** (раніше 8.0).
- **Заборонені розширення.** Файли з серверними/виконуваними розширеннями (`php`, `exe`, `sh`, `htaccess` …) більше не завантажуються — див. [Заборонені розширення файлів](#заборонені-розширення-файлів).
- **Роути файлменеджера приймають лише AJAX-запити** (не-AJAX → 404). Відповідь на завантаження тепер `{"success":"ok","rejected":[...]}` замість ехо запиту — важливо, лише якщо у вас свій JS поверх API пакета.

Після оновлення перепублікуйте ассети, очистіть кеш компонентів і (рекомендовано) мініатюри:

    php artisan vendor:publish --tag=laravel-files-assets --force
    php artisan view:clear
    php artisan laravel-files:clear-thumbnails

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

### Очищення мініатюр
Мініатюри (100x100) генеруються при завантаженні зображення і кешуються в підпапках `__thumbnails__` поряд з оригіналами. Щоб примусово перестворити всі мініатюри (наприклад, після зміни розміру/якості або пошкодження кешу), видаліть ці папки командою:

    php artisan laravel-files:clear-thumbnails

За замовчуванням команда рекурсивно шукає й видаляє всі папки `__thumbnails__` у `public/vendor/laravel-files/files`. Мініатюри перестворяться автоматично при наступному відкритті файлменеджера.

Щоб вказати інший шлях для пошуку — тільки відносний до `public/` (вихід за межі `public/`, зокрема через `../` чи symlink, заблоковано):

    php artisan laravel-files:clear-thumbnails vendor/laravel-files/files/uploads

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
