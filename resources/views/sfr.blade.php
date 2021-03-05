<!DOCTYPE html>
<html lang="en">
<head>
    <title>SUMMARY OF RATINGS</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
    <h4 style="text-align: center">SUMMARY OF FINDINGS AND RECOMMENDATIONS</h4>
    <p class="font-weight-bold" style="text-align: center">{{ $program->program_name }}</p>
    <br>
    @foreach($collections as $collection)
        <p class="font-weight-bold" style="text-align: left">{{ $collection['area_name'] }}</p>
        <div  style="text-align: left">STRENGTHS</div>
            <?php $x = 1; ?>
            @foreach($collection['strengths'] as $strength)
                 {{$x}}. {{ $strength }}<br>
                 <?php $x = $x + 1; ?>
            @endforeach
         <br>
        <div style="text-align: left">AREAS NEEDING IMPROVEMENT</div>
        <?php $x = 1; ?>
        @foreach($collection['weaknesses'] as $weakness)
            {{$x}}. {{ $weakness }}<br>
            <?php $x = $x + 1; ?>
        @endforeach
        <br>
        <div style="text-align: left">RECOMMENDATIONS</div>
        <?php $x = 1; ?>
        @foreach($collection['recommendations'] as $recommendation)
            {{$x}}. {{ $recommendation }} <br>
            <?php $x = $x + 1; ?>
        @endforeach
        <br><br><br>
    @endforeach


</div>

</body>
</html>
<?php
