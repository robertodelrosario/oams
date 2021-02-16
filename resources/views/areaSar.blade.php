<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bootstrap Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
    <h2>Bordered Table</h2>
    <p>The .table-bordered class adds borders on all sides of the table and the cells:</p>
    <table class="table table-bordered" >
        <thead>
            <tr>
                <th scope="col" class="small" style="text-align: center; font-size: 12px"><div class="font-weight-bold">PARAMETER</div></th>
                <th scope="col" class="small" style="text-align: center; font-size: 12px"><div class="font-weight-bold">ACCREDITOR RATING</div></th>
                <th scope="col" class="small" style="text-align: center; font-size: 12px"><div class="font-weight-bold">AVERAGE RATING</div></th>
                <th scope="col" class="small" style="text-align: center; font-size: 12px"><div class="font-weight-bold">DESCRIPTIVE RATING</div></th>
            </tr>
        </thead>
        <tbody>
        @foreach($parameters as $parameter)
            <tr>
                <th scope="row" class="small">{{ $parameter->parameter }}</th>
                <td class="small">
                    @foreach($means as $mean)
                        @if($mean->program_parameter_id == $parameter->id) {{ $mean->first_name }} {{ $mean->last_name }} : {{$mean->parameter_mean}}<br>
                        @endif
                    @endforeach
                </td>
                <td class="small">
                    @foreach($results as $result)
                        @if($result['program_parameter_id'] == $parameter->id) {{ $result['average_mean'] }} ({{$result['status']}})
                        @endif
                    @endforeach
                </td>
                <td class="small">
                    @foreach($results as $result)
                        @if($result['program_parameter_id'] == $parameter->id) {{ $result['descriptive_rating'] }}
                        @endif
                    @endforeach
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

</body>
</html>
