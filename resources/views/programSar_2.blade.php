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
    <h3 style="text-align: center">ACCREDITORS' REPORT</h3>
    <h3 style="text-align: center">({{ $level }} - EVALUATION)</h3>
    <table class="table-borderless" >
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 12px; width: 90%">Program: {{ $program->program_name }}</th>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 12px; width: 30%">Date: {{ $date }}</th>
        </tr>
    </table>
    <table class="table-borderless" >
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 12px; width: 90%">SUC: {{ $suc['institution_name'] }}</th>
        </tr>
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 12px; width: 90%">Address: {{ $suc['address'] }}</th>
        </tr>
    </table>
    <table class="table table-bordered" >
        <thead>
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 90%">Areas</th>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 30%">Rating</th>
        </tr>
        </thead>
        <tbody>
        @foreach($areas as $area)
            <?php $x = 1; ?>
            <tr>
                <th scope="row" class="small">{{ $x }}. {{ $area['area'] }}</th>
                <td class="small" >{{ $area['area_mean'] }}</td>
            </tr>
            <?php $x = $x + 1; ?>
        @endforeach
        <tr>
            <th scope="row" class="small" style="text-align: right">
                <div class="font-weight-bold">Grand Mean</div>
            </th>
            <td class="small">
                {{ $result[0]['grand_mean'] }} - {{ $result[0]['descriptive_result'] }}
            </td>
        </tr>
        </tbody>
    </table>
</div>

</body>
</html>
