@extends('app')

@section('content')

<div class="inner">
    <h2>{{ $poem->title }}</h2>
    @if (count($alexandrines))
            @foreach ($alexandrines as $alexandrine)
            <blockquote>
                <p>{{ $alexandrine->text }}</p>
                <footer>
                    {{-- <img class="avatar" src="{{ $alexandrine->profile_image_url }}" alt="{{ '@' . $alexandrine->screen_name  }}" /> --}}
                    <cite title="{{ '@' . $alexandrine->screen_name }}">Tweeté par
                        <a href="//www.twitter.com/statuses/{{ $alexandrine->tweet_id }}" target="_blank">{{ '@' . $alexandrine->screen_name }}
                        </a>
                    </cite>
                </footer>
            </blockquote>
            @endforeach
    @endif
</div>

@stop