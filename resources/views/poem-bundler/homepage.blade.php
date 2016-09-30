@extends('app')

@section('content')

<div class="inner">
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