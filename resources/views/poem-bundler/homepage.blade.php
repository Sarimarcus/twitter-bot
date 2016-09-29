@extends('app')

@section('content')

<div class="content center-block">
    @if (count($poem))
        <ul>
            @foreach ($poem as $alexandrine)
            <li>
                {{ $alexandrine->text }}
            </li>
            @endforeach
        </ul>
    @endif
</div>

@stop