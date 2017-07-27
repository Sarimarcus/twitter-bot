<div class="inner">
@if (count($alexandrines))
        @foreach ($alexandrines as $alexandrine)
        <blockquote>
            <p>{{ $alexandrine->text }}</p>
            <footer>
                <cite title="{{ '@' . $alexandrine->screen_name }}">Tweeté par
                    <a href="//www.twitter.com/{{ $alexandrine->screen_name }}/status/{{ $alexandrine->tweet_id }}" target="_blank">{{ '@' . $alexandrine->screen_name }}
                    </a>
                </cite>
            </footer>
        </blockquote>
        @endforeach
@endif
</div>