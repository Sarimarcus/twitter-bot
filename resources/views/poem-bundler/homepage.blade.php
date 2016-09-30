@extends('app')

@section('content')

<div class="inner">
    @if (count($poem))
            @foreach ($poem as $alexandrine)
            <blockquote>
                <p>{{ $alexandrine->text }}</p>
                <footer><cite title="{{ '@' . $alexandrine->screen_name}}">{{ '@' . $alexandrine->screen_name}}</cite></footer>
            </blockquote>
            @endforeach
    @endif
</div>

@stop