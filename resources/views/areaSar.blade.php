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
    <p class="font-weight-bold" style="text-align: center">{{ $program['program_name'] }} <br> {{ $area['area_name'] }}</p>
    <table class="table table-bordered" >
        <thead>
            <tr>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 90%">PARAMETER</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 30%">ACCREDITOR RATING</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 30%">RATING</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 30%">DESCRIPTIVE RATING</th>
            </tr>
        </thead>
        <tbody>
        @foreach($parameters as $parameter)
            <tr>
                <th scope="row" class="small">{{ $parameter->parameter }}</th>
                <td class="small">
                    @foreach($means as $mean)
                        @if($mean->program_parameter_id == $parameter->id) {{ $mean->last_name }} : {{$mean->parameter_mean}}<br>
                        @endif
                    @endforeach
                </td>
                <td class="small" style="text-align: center">
                    @foreach($results as $result)
                        @if($result['program_parameter_id'] == $parameter->id) {{ $result['average_mean'] }} @if($result['status'] != 'accepted') ({{$result['status']}})@endif
                        @endif
                    @endforeach
                </td>
                <td class="small" style="text-align: center">
                    @foreach($results as $result)
                        @if($result['program_parameter_id'] == $parameter->id) {{ $result['descriptive_rating'] }}
                        @endif
                    @endforeach
                </td>
            </tr>
        @endforeach
        <tr>
            <th scope="row" class="small"></th>
            <td class="small" style="text-align: right">
                <div class="font-weight-bold">Total</div>
            </td>
            <td class="small" style="text-align: center">
                {{ $area_mean[0]['total'] }}
            </td>
            <td class="small" style="text-align: center">
            </td>
        </tr>
        <tr>
            <th scope="row" class="small"></th>
            <td class="small" style="text-align: right">
                <div class="font-weight-bold">Mean</div>
            </td>
            <td class="small" style="text-align: center">
                {{ $area_mean[0]['area_mean'] }}
            </td>
            <td class="small" style="text-align: center">
                @foreach($area_mean as $am)
                    @if($area_mean[0]['area_mean'] < 1.50) Poor
                    @elseif ($area_mean[0]['area_mean'] < 2.50) Fair
                    @elseif ($area_mean[0]['area_mean'] < 3.50) Satisfactory
                    @elseif ($area_mean[0]['area_mean'] < 4.50) Very Satisfactory
                    @else Excellent
                    @endif
                @endforeach
            </td>
        </tr>
        </tbody>
    </table>

    <br><br>
    <p class="font-weight-bold" style="text-align: left">Accreditor/s:</p>
    <br><br>
    <table class="table-borderless">
        <thead>
            <tr>
                @foreach($accreditors as $accreditor)
                    @if($accreditor['role'] == '[leader] external accreditor' || $accreditor['role'] == '[leader] external accreditor - area 7')
                        <th scope="col"  style=" text-decoration: underline ;text-align: center; font-size: 14px; width: 265px">{{ $accreditor['name'] }}</th>
                    @endif
                @endforeach
                @foreach($accreditors as $accreditor)
                        @if($accreditor['role'] == 'external accreditor' || $accreditor['role'] == 'external accreditor - area 7' || $accreditor['role'] == 'internal accreditor')
                            <th scope="col"  style="text-decoration: underline  ;text-align: center; font-size: 14px; width: 265px">{{ $accreditor['name'] }}</th>
                        @endif
                    @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach($accreditors as $accreditor)
                    @if($accreditor['role'] == '[leader] external accreditor' || $accreditor['role'] == '[leader] external accreditor - area 7')
                        <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 14px; width: 100%" >Lead Accreditor</th>
                    @endif
                @endforeach
                    @foreach($accreditors as $accreditor)
                        @if($accreditor['role'] == 'external accreditor' || $accreditor['role'] == 'external accreditor - area 7' || $accreditor['role'] == 'internal accreditor')
                            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 14px; width: 100%;">Accreditor</th>
                        @endif
                    @endforeach
            </tr>
        </tbody>
    </table>
</div>

</body>
</html>
