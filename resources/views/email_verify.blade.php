<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verify</title>
</head>
<body>
    <H2>Hola {{$data['name']}} Email: {{$data['email']}}</H2>
    <a href="https://mosbeautyshop.com/forgetPass/{{$data['email_code']}}/{{$data['password_code']}}">Link de verificación de cuenta.</a>
</body>
</html>

