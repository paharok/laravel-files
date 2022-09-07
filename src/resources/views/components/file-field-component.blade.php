<div {{ $attributes->merge(['class' => 'plf-field-outer']) }}>
    <button class="plf-field-remove" type="button">&#10006;</button>
    <div class="plf-field-body">
        <img src="{{ $thumbnail }}" class="plf-field-img" data-placeholder="{{ $placeholder }}">
        @if($needExtension)
            <span class="plf-field-body-extension">{{ $extension ?? '' }}</span>
        @endif
    </div>
    <div class="plf-field-name">{{ $fileName ?? '' }}</div>
    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
</div>
