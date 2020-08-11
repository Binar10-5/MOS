<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>'new email'</title>
</head>
<body>
    <H2>Hola {{$data['name']}} Email: {{$data['email']}}</H2>
    <H1><a href="https://mosbeautyshop.com/forgetPass/{{$data['email_code']}}/">Link de verificación de cuenta</a></H1>
</body>
</html>


