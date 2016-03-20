@extends('app')

@section('content')

<div class="content">
    @if (count($bots))
        <h1>Bots status</h1>
        <ul class="list-group">
            @foreach ($bots as $bot)
            <li class="list-group-item @if ($bot->online === 0)disabled @endif">
                <span class="badge">{{ $bot->followers_count }}</span>
                {{ '@' . $bot->screen_name }}
            </li>
            @endforeach
        </ul>
    @endif

</div>

@stop
