@extends('app')

@section('content')

<div class="inner" data-current="1">
    @include('poem-bundler/poem-content')
</div>
<div><span class="glyphicon glyphicon-menu-down" id="load"></span></div>

@stop