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
    @foreach($instrument_programs as $instrument_program)
        <p class="font-weight-bold" style="text-align: left" >{{ $instrument_program['area_name'] }}</p>
        <table class="table table-bordered" >
            <thead>
            <tr>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 20%">ACCREDITOR</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 20%">BEST PRACTICES</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 20%">STRENGTH REMARKS</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 20%">WEAKNESS REMARKS</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 20%">RECOMMENDATIONS</th>
            </tr>
            </thead>
            <tbody>
            @foreach($collections as $collection)
                @if($instrument_program['id'] == $collection['instrument_program_id'])
                    <tr>
                        <th scope="row" class="small">{{ $collection['user_name'] }}</th>
                        <td class="small" >
                            @foreach($collection['best_practices'] as $best_practice)
                                {{ $best_practice }}<br>
                            @endforeach
                        </td>
                        <td class="small" >
                            @foreach($collection['strength_remarks'] as $strength_remark)
                                {{ $strength_remark }}<br>
                            @endforeach
                        </td>
                        <td class="small" >
                            @foreach($collection['weakness_remarks'] as $weakness_remarks)
                                {{ $weakness_remarks }}<br>
                            @endforeach
                        </td>
                        <td class="small" >
                            @foreach($collection['recommendations'] as $recommendation)
                                {{ $recommendation }}<br>
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
