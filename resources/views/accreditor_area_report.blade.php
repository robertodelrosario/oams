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
    <table class="table-borderless" >
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 14px; width: 90%">Program: {{ $program['program_name'] }}</th>
        </tr>
    </table>
    <table class="table-borderless" >
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 14px; width: 90%">SUC/Campus: {{ $suc['institution_name'] }} / {{ $campus['campus_name'] }} </th>
        </tr>
    </table>
    <table class="table-borderless" >
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 14px; width: 90%">Address: {{ $campus['address']}}</th>
        </tr>
    </table>
    <table class="table-borderless" >
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 14px; width: 90%">Accreditor: {{ $accreditor['first_name'] }} {{ $accreditor['last_name'] }}</th>
        </tr>
    </table>
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
                    @elseif($score['degree'] == 2) <div style="margin-left: 5%"></div><th scope="row" class="small" >{{ $score['statement'] }}</th></div></div>
                    @elseif($score['degree'] == 3) <div style="margin-left: 8%"><th scope="row" class="small" >{{ $score['statement'] }}</th></div>
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
                            @if($user_score['score'] == 0)
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
</div>

</body>
</html>
