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
        <table class="table table-bordered" >
            <thead>
            <tr>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 20%">ACCREDITOR</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 80%">REMARKS</th>
            </tr>
            </thead>
            <tbody>
            @foreach($collections as $collection)
                @if($instrument_program['id'] == $collection['instrument_program_id'])
                    <tr>
                        <th scope="row" class="small">{{ $collection['user_name'] }}</th>
                        <td class="small" >
                            <div class="font-weight-bold" style="text-align: left">Best Practices</div>
                            <?php $x = 1; ?>
                            @foreach($collection['best_practices'] as $best_practice)
                                {{$x}}. {{ $best_practice }}<br>
                                <?php $x = $x + 1; ?>
                            @endforeach
                            <?php $x = 1; ?>
                            <br><div class="font-weight-bold" style="text-align: left">Strengths</div>
                            @foreach($collection['strength_remarks'] as $strength_remark)
                                {{$x}}. {{ $strength_remark }}<br>
                                <?php $x = $x + 1; ?>
                            @endforeach
                            <?php $x = 1; ?>
                            <br><div class="font-weight-bold" style="text-align: left">Weaknesses</div>
                            @foreach($collection['weakness_remarks'] as $weakness_remarks)
                                {{$x}}. {{ $weakness_remarks }}<br>
                                <?php $x = $x + 1; ?>
                            @endforeach
                            <?php $x = 1; ?>
                            <br><div class="font-weight-bold" style="text-align: left">Recommendations</div>
                            @foreach($collection['recommendations'] as $recommendation)
                                {{$x}}. {{ $recommendation }}<br>
                                <?php $x = $x + 1; ?>
                            @endforeach
                        </td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    @endforeach


</div>

</body>
</html>
<?php
