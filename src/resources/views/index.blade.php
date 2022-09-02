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
                <span>@lang('laravelfiles::plf.createFolder')</span>
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
                    <span>@lang('laravelfiles::plf.uploadFiles')</span>
                </button>
                <input type="file" name="files" multiple="multiple">
            </form>
            <button type="button"  class="plf-search plf-green-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="24px" height="24px"><path fill="#ffffff" d="M 21 3 C 11.601563 3 4 10.601563 4 20 C 4 29.398438 11.601563 37 21 37 C 24.355469 37 27.460938 36.015625 30.09375 34.34375 L 42.375 46.625 L 46.625 42.375 L 34.5 30.28125 C 36.679688 27.421875 38 23.878906 38 20 C 38 10.601563 30.398438 3 21 3 Z M 21 7 C 28.199219 7 34 12.800781 34 20 C 34 27.199219 28.199219 33 21 33 C 13.800781 33 8 27.199219 8 20 C 8 12.800781 13.800781 7 21 7 Z"/></svg>
                <span>@lang("laravelfiles::plf.search")</span>
            </button>
        </div>
        <div class="plf-new-folder-pop">
            <form action="{{ route('laravel-files.newFolder') }}" class="plf-new-folder-form">
                @csrf
                <input type="hidden" name="currentFolder" value="{{ $currentFolder }}" >
                <div class="input-group">
                    <input type="text" name="foldername" placeholder="Folder name" class="form-control">
                    <button class="plf-newFolder plf-green-btn" type="button">@lang("laravelfiles::plf.create")</button>
                    <button class="plf-cancelFolder plf-red-btn" type="button">@lang("laravelfiles::plf.cancel")</button>
                </div>
            </form>
        </div>
        <div class="plf-search-pop">
            <form action="{{ route('laravel-files.search') }}" class="plf-search-form">
                @csrf
                <input type="hidden" name="currentFolder" value="{{ $currentFolder }}" >
                <div class="input-group">
                    <input type="text" name="s" placeholder="@lang("laravelfiles::plf.fileName")" class="form-control">
                    <button class="plf-go-search plf-green-btn" type="button">@lang("laravelfiles::plf.search")</button>
                    <button class="plf-cancelSearch plf-red-btn" type="button">@lang("laravelfiles::plf.cancel")</button>
                </div>
            </form>
        </div>


    </div>
    <div class="plf-body">
        @include('laravelfiles::partials.items')
    </div>
</div>


