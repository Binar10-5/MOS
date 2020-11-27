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
    <p>
        Te informamos que el pago para tu pedido {{$data['numeral']}}{{$data['order_number']}} ha sido aprobado exitosamente. Pronto te estaremos informando cuando sea despachado.
        <H3>Atentamente, equipo MOS.</H3>
    </p>
</body>
</html>


