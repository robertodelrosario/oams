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
    <h3 style="text-align: center">SUMMARY OF RATINGS</h3>
    <p class="font-weight-bold" style="text-align: center">{{ $program->program_name }}</p>
    <table class="table table-bordered" >
        <thead>
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 90%">AREA</th>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 30%">WEIGHT</th>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 30%">AREA MEAN</th>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 30%">WEIGHTED MEAN</th>
        </tr>
        </thead>
        <tbody>
        @foreach($areas as $area)
            <tr>
                <th scope="row" class="small">{{ $area['area'] }}</th>
                <td class="small" >{{ $area['weight'] }}</td>
                <td class="small">{{ $area['mean'] }}</td>
                <td class="small">{{ $area['weighted_mean'] }}</td>
            </tr>
        @endforeach
        <tr>
            <th scope="row" class="small" style="text-align: right">
                <div class="font-weight-bold">Total</div>
            </th>
            <td class="small">
                {{ $result[0]['total_weight'] }}
            </td>
            <td class="small">
                {{ $result[0]['total_area_mean'] }}
            </td>
            <td class="small">
                {{ $result[0]['total_weighted_mean'] }}
            </td>
        </tr>
        <tr>
            <th scope="row" class="small" style="text-align: right">
                <div class="font-weight-bold">Grand Mean</div>
            </th>
            <td class="small">
            </td>
            <td class="small">
            </td>
            <td class="small">
                {{ $result[0]['grand_mean'] }}
            </td>
        </tr>
        <tr>
            <th scope="row" class="small" style="text-align: right">
                <div class="font-weight-bold">Descriptive Rating</div>
            </th>
            <td class="small">
            </td>
            <td class="small">
            </td>
            <td class="small">
                <div class="font-weight-bold">{{ $result[0]['descriptive_result'] }}</div>
            </td>
        </tr>
        </tbody>
    </table>
</div>

</body>
</html>
