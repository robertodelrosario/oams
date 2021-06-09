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
    <h5 style="text-align: center">ACCREDITORS' REPORT</h5>
    <h5 style="text-align: center">({{ $level }} - EVALUATION)</h5>
    <table class="table-borderless" >
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 14px; width: 90%">Program: {{ $program->program_name }}</th>
        </tr>
    </table>
    <table class="table-borderless" >
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 14px; width: 90%">SUC: {{ $suc['institution_name'] }}</th>
        </tr>
    </table>
    <table class="table-borderless" >
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 14px; width: 90%">Address: {{ $suc['address'] }}</th>
        </tr>
    </table>
    <table class="table-borderless" >
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 14px; width: 90%">Date: {{ $date }}</th>
        </tr>
    </table>
    <br>
    <table class="table table-bordered" >
        <thead>
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 90%">Areas</th>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 30%">Rating</th>
        </tr>
        </thead>
        <tbody>
        <?php $x = 1; ?>
        @foreach($areas as $area)
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
    <br>
    <table class="table-borderless" >
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 14px; width: 90%">Accreditors Observation, Comments and Recommendations</th>
        </tr>
    </table>
    <table class="table table-bordered" >
        <thead>
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 50%">Mandatory (to be complied with before the awards of {{ $level }})</th>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 50%">Enhancement (may be complied with after award of {{ $level }})</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th scope="row" class="small">
                <?php $x = 1; ?>
                @foreach($remarks_before_compliance as $remark)
                        {{ $x }}. {{ $remark['remark'] }}<br>
                        <?php $x = $x + 1; ?>
                    @endforeach
            </th>
            <th scope="row" class="small">
                <?php $x = 1; ?>
                @foreach($remarks_after_compliance as $remark)
                    {{ $x }}. {{ $remark['remark'] }} <br>
                    <?php $x = $x + 1; ?>
                @endforeach
            </th>
        </tr>
        </tbody>
    </table>
    <table class="table-borderless" >
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: right; font-size: 14px; width: 90%">RECOMMENDED BOARD ACTION:</th>
        </tr>
    </table>
    <table class="table table-bordered" >
        <tr>
            <td  class="small" style="border: 1px solid white; text-align: left; font-size: 12px; width: 80%">Award {{ $level }}</td>
            <td class="small" style="border: 1px solid white; text-align: right; font-size: 12px; width: 20%">______________</td>
        </tr>
        <tr>
            <td scope="col" class="small" style="border: 1px solid white; text-align: left; font-size: 12px; width: 80%">Comply with recommendations before the award of {{ $level }}</td>
            <td scope="col" class="small" style="border: 1px solid white; text-align: right; font-size: 12px; width: 20%">______________</td>
        </tr>
        <tr>
            <th scope="col" class="small" style="border: 1px solid white;  text-align: left; font-size: 12px; width: 100%">Accreditors:</th>
        </tr>
    </table>
    <table class="table table-bordered"  >
        <tr>
            @foreach($accreditors as $accreditor)
                <th scope="col" class="font-weight-bold" style=" border: 1px solid white; text-decoration: underline; text-align: center; font-size: 12px; width: 50%">{{$accreditor['first_name']}} {{$accreditor['last_name']}}</th>
            @endforeach
        </tr>
    </table>
</div>

</body>
</html>
