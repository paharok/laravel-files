@foreach($files as $file)
    <div class="plf-file-item plf-file-item-{{ $file['type'] }}" data-path="{{ $file['minPath'] }}" data-url="{{ $file['url'] }}" data-publicPath="{{ $file['pulicPath'] }}">
        <div class="plf-file-img">
            @if($file['type'] == 'dir')
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="100px" height="100px">    <path d="M 5 4 C 3.346 4 2 5.346 2 7 L 2 13 L 3 13 L 47 13 L 48 13 L 48 11 C 48 9.346 46.654 8 45 8 L 18.044922 8.0058594 C 17.765922 7.9048594 17.188906 6.9861875 16.878906 6.4921875 C 16.111906 5.2681875 15.317 4 14 4 L 5 4 z M 3 15 C 2.448 15 2 15.448 2 16 L 2 43 C 2 44.657 3.343 46 5 46 L 45 46 C 46.657 46 48 44.657 48 43 L 48 16 C 48 15.448 47.552 15 47 15 L 3 15 z" fill="#8fbd56"/></svg>
            @else
                <img src="{{ $file['thumbnail'] ?? '' }}" alt="" width="100" height="100" />
            @endif

            @if($file['needExtension'])
                <span class="plf-file-extension">{{ $file['extension'] }}</span>
            @endif
        </div>
        <span class="plf-filename">{{ $file['name'] }}</span>

        <button class="plf-pop-remove" data-path="{{ $file['minPath'] }}"
                @if($file['type'] == 'dir')
                    data-action="{{ route('laravel-files.removeDir') }}"
                    data-confirm="@lang('laravelfiles::plf.questRemoveDir')"
                @else
                    data-action="{{ route('laravel-files.removeFile') }}"
                    data-confirm="@lang('laravelfiles::plf.questRemoveFile')"
            @endif
        >&#10006;</button>
    </div>
@endforeach
