<div class="plf-outer">
    <div class="plf-header">
        @if($breadcrumbs)
        <ul class="plf-path">
            @foreach($breadcrumbs as $bcItem)
                @if(!$loop->first)
                    <li class="plf-path-slash">/</li>
                @endif
                <li data-path="{{ $bcItem['path'] }}" class="plf-path-li">{{ $bcItem['title'] }}</li>
            @endforeach
         </ul>
        @endif
        <div class="plf-btns">
            <button type="button"  class="plf-addFolder plf-green-btn">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="16" height="16" viewBox="0 0 533.333 533.333" style="enable-background:new 0 0 533.333 533.333;" xml:space="preserve">
                <path fill="#ffffff" d="M516.667,200H333.333V16.667C333.333,7.462,325.871,0,316.667,0h-100C207.462,0,200,7.462,200,16.667V200H16.667   C7.462,200,0,207.462,0,216.667v100c0,9.204,7.462,16.666,16.667,16.666H200v183.334c0,9.204,7.462,16.666,16.667,16.666h100   c9.204,0,16.667-7.462,16.667-16.666V333.333h183.333c9.204,0,16.667-7.462,16.667-16.666v-100   C533.333,207.462,525.871,200,516.667,200z"/>
            </svg>
                <span>Створити директорію</span>
            </button>
            <form action="{{ route('laravel-files.newFile') }}" class="plf-files-form">
                @csrf
                <input type="hidden" name="folder" value="{{ $currentFolder }}">
                 <button type="button" class="plf-green-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"  stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="#ffffff" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline stroke="#ffffff" points="7 10 12 15 17 10"/>
                        <line stroke="#ffffff" x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    <span>Завантажити файл</span>
                </button>
                <input type="file" name="files" multiple="multiple">
            </form>
        </div>
        <div class="plf-new-folder-pop">
            <form action="{{ route('laravel-files.newFolder') }}" class="plf-new-folder-form">
                @csrf
                <input type="hidden" name="currentFolder" value="{{ $currentFolder }}" >
                <div class="input-group">
                    <input type="text" name="foldername" placeholder="Folder name" class="form-control">
                    <button class="plf-newFolder plf-green-btn" type="button">Создать</button>
                    <button class="plf-cancelFolder plf-red-btn" type="button">Отмена</button>
                </div>
            </form>
        </div>


    </div>
    <div class="plf-body">
        @foreach($files as $file)
            <div class="plf-file-item plf-file-item-{{ $file['type'] }}" data-path="{{ $file['minPath'] }}">
                <div class="plf-file-img">
                    @if($file['type'] == 'dir')
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="100px" height="100px">    <path d="M 5 4 C 3.346 4 2 5.346 2 7 L 2 13 L 3 13 L 47 13 L 48 13 L 48 11 C 48 9.346 46.654 8 45 8 L 18.044922 8.0058594 C 17.765922 7.9048594 17.188906 6.9861875 16.878906 6.4921875 C 16.111906 5.2681875 15.317 4 14 4 L 5 4 z M 3 15 C 2.448 15 2 15.448 2 16 L 2 43 C 2 44.657 3.343 46 5 46 L 45 46 C 46.657 46 48 44.657 48 43 L 48 16 C 48 15.448 47.552 15 47 15 L 3 15 z" fill="#8fbd56"/></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="100px" height="100px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve"><path d="M28.5,1.2c-9.7,0-17.6,7.9-17.6,17.6v62.5c0,9.7,7.9,17.6,17.6,17.6h43c9.7,0,17.6-7.9,17.6-17.6v-41H61.7  C55.2,40.2,50,35,50,28.5V1.2H28.5z M56.8,1.2v28.2c0,2.3,1.8,4.1,4.1,4.1h28.2L56.8,1.2z" fill="#8fbd56"/></svg>
                        <span class="plf-file-extension">{{ $file['extension'] }}</span>
                    @endif
                </div>
                <span class="plf-filename">{{ $file['name'] }}</span>
            </div>
        @endforeach
    </div>
</div>


