<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Activation</title>

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-light">

    {{ $slot }}

    @livewireScripts
</body>
</html>
