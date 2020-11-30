<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>'new email'</title>
</head>
<body>
    <H2>Saludos, {{$data['admin_name']}}</H2><br>
    <p>
        Cliente: {{$data['name']}} <br>
        Correo: {{$data['email']}} <br>
        Celular: {{$data['cell_phone']}}<br>
        Asunto: {{$data['subject']}}<br>
        Descripción: {{$data['description']}}<br>
        PQRS: {{$data['pqrs']}}<br>
        Mensaje: {{$data['message']}}<br>
    </p>
</body>
</html>


