<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>'new email'</title>
</head>
<body>
    <H2>Saludos, {{$data['name']}}</H2><br>
    <h3>A continuación la PQR que nos enviastes.</h3>
    <p>
        Correo: {{$data['email']}}<br>
        pqrs: {{$data['pqrs']}}<br>
        Mensaje: {{$data['message']}}<br>
        Número de pqrs: {{$data['pqrs_id']}}<br>
    </p>

    <H3>Atentamente, equipo MOS.</H3>
</body>
</html>


