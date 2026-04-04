<div class="doc-email-preview">
    <div class="doc-email-preview__bar">
        <span><i class="bi bi-envelope-open"></i> {{ $subject }}</span>
        <span>{{ $preheader }}</span>
    </div>

    <div class="doc-email">
        <div class="doc-email__hero">
            <span class="badge text-bg-light">{{ $badge }}</span>
            <h3>{{ $title }}</h3>
            <p>{{ $intro }}</p>
        </div>

        <div class="doc-email__body">
            <p>{{ $greeting }}</p>
            <p>{{ $intro }}</p>

            <div class="doc-email__details">
                @foreach ($items as $item)
                    <div class="doc-email__detail">{{ $item }}</div>
                @endforeach
            </div>

            <a href="#" class="doc-email__cta">{{ $cta }}</a>
            <p class="doc-email__footer">{{ $footer }}</p>
        </div>
    </div>
</div>
