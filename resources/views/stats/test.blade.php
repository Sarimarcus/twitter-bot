@extends('app')

@section('content')

<div class="content">
    <div id="stocks-div"></div>
    @linechart('Stocks', 'stocks-div');
</div>

@stop