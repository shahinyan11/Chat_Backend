<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat</title>
    @if(auth()->user())
    <script>
        window.user = {
            id: "{{ auth()->id() }}",
            name: "{{ auth()->user()->name }}"
        };
        window.csrfToken = "{{ csrf_token() }}";
    </script>
    @endif
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
{{--    <link href="{{ asset('css/login.css') }}" rel="stylesheet">--}}
    <link href="{{'https://fonts.googleapis.com/icon?family=Material+Icons'}}" rel="stylesheet">

</head>
<body>
<div id="root"></div>

<script>
    if(!localStorage.getItem('token')){
        let token = '{!! json_encode($userSession) !!}';
        if(token){
            localStorage.setItem('token', "{{ $userSession}}")
        }
    }

</script>
<script src="{{ asset('js/app.js') }}"></script>

</body>
</html>
