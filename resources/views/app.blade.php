<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $headTitle }}</title>
	<link href="{{ asset('/css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
	@yield('content')
	@yield('footer')
    </div>

	<!-- Scripts -->
	<script src="{{ asset('/js/app.js') }}"></script>
</body>
</html>
