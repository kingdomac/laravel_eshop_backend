<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <h2>Congratulation!</h2>
    <p>You have successfully purshased your order with the number #{{ $order->number }}</p>
    <a href="{{ $url }}">view order details</a>
    <p>Thanks for using our application,<br>
        {{ config('app.name') }}</p>
</body>

</html>
