##  Файлменеджер для LARAVEL

### Встановлення
    composer require paharok/laravel-files

### Публікація
    php artisan vendor:publish --tag=laravel-files-assets --force
## Оновлення
### Очищення кешу компонентів
    php artisan view:clear

### Підключення стилей та скриптів
    <script src="{{ asset('/vendor/laravel-files/js/pahar-laravel-files.js') }}"></script>
    <link href="{{ asset('/vendor/laravel-files/css/pahar-laravel-files.css') }}" rel="stylesheet">

### Компоненти
#### Вивести поле для вибору файлу
    <x-plf-field :name="$name" :value="$value"/>

#### Вивести поля для вибору файлів
    <x-plf-field-multiple :name="name[]" :values="array $value"/>

### Підтримка CKEditor 4
