<!DOCTYPE html>
<html lang="en">
<head>
    <title>Preliminary Survey Visit</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
    <h3 style="text-align: center">Preliminary Survey Visit</h3>
    <br><br>
    <h5 style="text-align: left">Program: {{ $program['program_name'] }}</h5>
    <br>
    <h5 style="text-align: left">SUC/Campus: {{ $suc['institution_name'] }} / {{ $campus['campus_name'] }} </h5>
    <br>
    <h5 style="text-align: left">Date of Visit: {{ $applied_program['approved_start_date'] }}</h5>
    <br>
    <h5 style="text-align: left">Address: {{ $campus['address']}}</h5>
    <br>
    <h5 style="text-align: left">Accreditor: {{ $accreditor['first_name'] }} {{ $accreditor['last_name'] }}</h5>


        <div style="page-break-after: always"></div>
        <h4 style="text-align: center">{{ $areas['area_name'] }}</h4>
        <table class="table table-bordered" >
            <thead>
            <tr>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 90%">Checklist of data/information, processes and activities</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 30%">Available</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 30%">Available but Inadequate</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 30%">Not Available</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 30%">Not Applicable</th>
            </tr>
            </thead>
            <tbody>
            @foreach($result as $score)
                <tr>
                    @if($score['degree'] == 1) <th scope="row" class="small" >{{ $score['statement'] }}</th>
                    @elseif($score['degree'] == 2) <th scope="row" class="small"><div style="margin-left: 7%"> {{ $score['statement'] }} </div> </th>
                    @elseif($score['degree'] == 3) <th scope="row" class="small"> <div style="margin-left: 14%"> {{ $score['statement'] }} </div> </th>
                    @endif
                    <td class="small" >
                        @foreach($score['score'] as $user_score)
                            @if($user_score['score'] >= 3 && $user_score['score'] <= 5)
                                {{ $user_score['last_name'] }} : {{ $user_score['score'] }}
                            @endif
                        @endforeach
                    </td>
                    <td class="small">
                        @foreach($score['score'] as $user_score)
                            @if($user_score['score'] == 1 || $user_score['score'] == 2)
                                {{ $user_score['last_name'] }} : {{ $user_score['score'] }}
                            @endif
                        @endforeach
                    </td>
                    <td class="small">
                        @foreach($score['score'] as $user_score)
                            @if($user_score['score'] == 0 && $user_score['score'] != null)
                                {{ $user_score['last_name'] }} : {{ $user_score['score'] }}
                            @endif
                        @endforeach
                    </td>
                    <td class="small">
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    <table class="table-borderless" style="width: 100%;">
        @foreach($accreditors as $acc)
            <tr>
                <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 13px; width: 90%">{{ $acc['last_name'] }}'s Total</th>
                @foreach($total_score as $ts)
                    @if($acc['last_name'] == $ts['last_name'])
                        <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 13px; width: 30%; border-bottom: 1px solid black;">{{ $ts['available'] }}</th>
                        <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 13px; width: 30%; border-bottom: 1px solid black;">{{ $ts['inadequate'] }}</th>
                    @endif
                @endforeach
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 13px; width: 30%; border-bottom: 1px solid black;">0</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 13px; width: 30%"></th>
            </tr>
        @endforeach
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 13px; width: 90%">Area Mean</th>
            @foreach($grand_mean as $gm)
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 13px; width: 30%; border-bottom: 1px solid black;">{{ $gm['area_mean'] }}</th>
            @endforeach
        </tr>
    </table>
    <br>
    <div class="font-weight-bold" style="text-align: left; font-size: 13px" >Recommendations:</div>
    <br>
    @foreach($recommendations as $recommendation)
        <div style="text-align: left; font-size: 13px" ><u>{{$recommendation['recommendation']}}</u></div>
    @endforeach
</div>

</body>
</html>
