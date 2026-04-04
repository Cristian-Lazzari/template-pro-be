<div class="doc-flow">
    @foreach ($steps as $step)
        <article class="doc-flow__step">
            <span class="public-icon-badge public-icon-badge--soft">
                @include('guests.partials.doc-icon', ['name' => $step['icon'], 'label' => $step['title']])
            </span>

            <div>
                <h3>{{ $step['title'] }}</h3>
                <p>{{ $step['description'] }}</p>
            </div>
        </article>
    @endforeach
</div>
