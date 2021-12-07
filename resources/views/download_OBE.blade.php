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

@foreach($accreditors as $accreditor)
    <div style="margin-right: 0px;margin-left: 0px;width: 100%;">
        <h4 style="text-align: center">{{ $instrument['area_name'] }}</h4>
        <table class="table table-bordered" >
            <tr>
                <td class="small" colspan="7" style="text-align: center;font-weight: bold; font-size: 10px">RATING SCALE</td>
            </tr>
            <tr>
                <td class="small" style="text-align: center; font-size: 9px; font-weight: bold;">NA</td>
                <td class="small" style="text-align: center;font-weight: bold; font-size: 9px">0</td>
                <td class="small" style="text-align: center;font-weight: bold; font-size: 9px">1</td>
                <td class="small" style="text-align: center;font-weight: bold; font-size: 9px">2</td>
                <td class="small" style="text-align: center;font-weight: bold; font-size: 9px">3</td>
                <td class="small" style="text-align: center;font-weight: bold; font-size: 9px">4</td>
                <td class="small" style="text-align: center;font-weight: bold; font-size: 9px">5</td>
            </tr>
            <tr>
                <td class="small" style="text-align: center;font-weight: bold; font-size: 9px">-</td>
                <td class="small" style="text-align: center;font-weight: bold; font-size: 9px">-</td>
                <td class="small" style="text-align: center;font-weight: bold; font-size: 9px">Poor</td>
                <td class="small" style="text-align: center;font-weight: bold; font-size: 9px">Fair</td>
                <td class="small" style="text-align: center;font-weight: bold; font-size: 9px">Satisfactory</td>
                <td class="small" style="text-align: center;font-weight: bold; font-size: 9px">Very Satisfactory</td>
                <td class="small" style="text-align: center;font-weight: bold; font-size: 9px">Excellent</td>
            </tr>
            <tr>
                <td class="small" style="text-align: center;font-style: italic; font-size: 8px">
                    <p>
                        Not Applicable
                    </p>
                </td>
                <td class="small" style="text-align: center;font-style: italic; font-size: 8px">
                    <p>
                        Missing
                    </p>
                </td>
                <td class="small" style="text-align: center;font-style: italic; font-size: 8px">
                    <p>
                        Criterion is met minimally in some respects, but much improvement is needed to overcome weaknesses
                    </p>
                    <br>
                    <p>
                        (75% lesser than the standards)
                    </p>
                </td>
                <td class="small" style="text-align: center;font-style: italic; font-size: 8px">
                    <p>
                        Criterion is met in most respects, but some improvement is needed to overcome weaknesses
                    </p>
                    <br>
                    <p>
                        (50% lesser that the standards)
                    </p>
                </td>
                <td class="small" style="text-align: center;font-style: italic; font-size: 8px">
                    <p>
                        Criterion is met all respects
                    </p>
                    <br>
                    <p>
                        (100% compliance with the standards)
                    </p>
                </td>
                <td class="small" style="text-align: center;font-style: italic; font-size: 8px">
                    <p>
                        Criterion is fully met in all respects, at a level that demonstrates good practice
                    </p>
                    <br>
                    <p>
                        (50% greater that the standards)
                    </p>
                </td>
                <td class="small" style="text-align: center;font-style: italic; font-size: 8px">
                    <p>
                        Criterion is fully met with substantial number of good practices, at a level that provides a model for others
                    </p>
                    <br>
                    <p>
                        (75% greater than the standards)
                    </p>
                </td>
            </tr>
        </table>
        <table class="table table-bordered" style="table-layout: fixed; width: 100%">
            <thead>
            <tr>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 60%;">Indicators</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 10%;">Item Rating (IR)</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 18%;">System-Implementation-outcome Mean (SIOM)</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 12%;">Parameter Mean (PM)</th>
            </tr>
            </thead>
            <tbody>
                @foreach($parameter_results as $parameter_result)
                        @foreach($parameter_result['parameter_mean'] as $param_mean)
                            @if($param_mean['id'] == $accreditor['id'])
                                <tr>
                                    <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 60%;font-weight: bold;">{{ $parameter_result['parameter'] }}</th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 10%;"></th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 18%;"></th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 14px; width: 12%; font-weight: bold;">{{ $param_mean['parameter_mean'] }}</th>
                                </tr>
                            @endif
                        @endforeach
                        @foreach($parameter_result['system_input'] as $system_input)
                            @if($system_input['id'] == $accreditor['id'])
                                <tr>
                                    <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 60%; font-weight: bold;">SYSTEM-INPUTS AND PROCESSES</th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 10%;"></th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 14px; width: 18%;font-weight: bold;">{{ $system_input['score'] }}</th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 12%;"></th>
                                </tr>
                            @endif
                        @endforeach
                        @foreach($statements as $statement)
                            @if($statement['parameter_id'] == $parameter_result['parameter_id'])
                                @if($statement['type'] == "System Input")
                                    @foreach($statement['score'] as $score)
                                        @if($score['id'] == $accreditor['id'])
                                            <tr>
                                                @if($statement['degree'] == 1)
                                                    <th scope="row" class="small" style="font-size: 12px; width: 60%;">{{ $statement['benchmark_statement'] }}</th>
                                                    <th scope="row" class="small" style="text-align: center; font-size: 14px; width: 10%;font-weight: bold;">{{ $score['score'] }}</th>
                                                @elseif($statement['degree'] == 2)
                                                    <th scope="row" class="small" style="font-size: 12px; width: 60%;"><div style="margin-left: 7%"> {{ $statement['benchmark_statement'] }}</div></th>
                                                    <th scope="row" class="small" style="text-align: center; font-size: 13px; width: 10%;">{{ $score['score'] }}</th>
                                                @elseif($statement['degree'] == 3)
                                                    <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 60%;"><div style="margin-left: 14%"> {{ $statement['benchmark_statement'] }}</div></th>
                                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 10%;">{{ $score['score'] }}</th>
                                                @endif
                                                <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 18%;"></th>
                                                <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 12%;"></th>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                            @endif
                        @endforeach
                        @foreach($parameter_result['implementation'] as $implementation)
                            @if($implementation['id'] == $accreditor['id'])
                                <tr>
                                    <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 60%;font-weight: bold;">IMPLEMENTATION</th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 10%;"></th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 14px; width: 18%;font-weight: bold;">{{ $implementation['score'] }}</th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 12%;"></th>
                                </tr>
                            @endif
                        @endforeach
                        @foreach($statements as $statement)
                            @if($statement['parameter_id'] == $parameter_result['parameter_id'])
                                @if($statement['type'] == "Implementation")
                                    @foreach($statement['score'] as $score)
                                        @if($score['id'] == $accreditor['id'])
                                            <tr>
                                                @if($statement['degree'] == 1)
                                                    <th scope="row" class="small" style="font-size: 12px; width: 60%;">{{ $statement['benchmark_statement'] }}</th>
                                                    <th scope="row" class="small" style="text-align: center; font-size: 14px; width: 10%;font-weight: bold;">{{ $score['score'] }}</th>
                                                @elseif($statement['degree'] == 2)
                                                    <th scope="row" class="small" style="font-size: 12px; width: 60%;"><div style="margin-left: 7%"> {{ $statement['benchmark_statement'] }}</div></th>
                                                    <th scope="row" class="small" style="text-align: center; font-size: 13px; width: 10%;">{{ $score['score'] }}</th>
                                                @elseif($statement['degree'] == 3)
                                                    <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 60%;"><div style="margin-left: 14%"> {{ $statement['benchmark_statement'] }}</div></th>
                                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 10%;">{{ $score['score'] }}</th>
                                                @endif
                                                <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 18%;"></th>
                                                <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 12%;"></th>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                            @endif
                        @endforeach
                        @foreach($parameter_result['outcome'] as $outcome)
                            @if($outcome['id'] == $accreditor['id'])
                                <tr>
                                    <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 60%;font-weight: bold;">OUTCOME/S</th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 10%;"></th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 14px; width: 18%;font-weight: bold;">{{ $outcome['score'] }}</th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 12%;"></th>
                                </tr>
                            @endif
                        @endforeach
                        @foreach($statements as $statement)
                            @if($statement['parameter_id'] == $parameter_result['parameter_id'])
                                @if($statement['type'] == "Outcome")
                                    @foreach($statement['score'] as $score)
                                        @if($score['id'] == $accreditor['id'])
                                            <tr>
                                                @if($statement['degree'] == 1)
                                                    <th scope="row" class="small" style="font-size: 12px; width: 60%;">{{ $statement['benchmark_statement'] }}</th>
                                                    <th scope="row" class="small" style="text-align: center; font-size: 14px; width: 10%;font-weight: bold;">{{ $score['score'] }}</th>
                                                @elseif($statement['degree'] == 2)
                                                    <th scope="row" class="small" style="font-size: 12px; width: 60%;"><div style="margin-left: 7%"> {{ $statement['benchmark_statement'] }}</div></th>
                                                    <th scope="row" class="small" style="text-align: center; font-size: 13px; width: 10%;">{{ $score['score'] }}</th>
                                                @elseif($statement['degree'] == 3)
                                                    <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 60%;"><div style="margin-left: 14%"> {{ $statement['benchmark_statement'] }}</div></th>
                                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 10%;">{{ $score['score'] }}</th>
                                                @endif
                                                <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 18%;"></th>
                                                <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 12%;"></th>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                            @endif
                        @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    <table class="table-borderless" style="width: 100%;">
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 14px; width: 100%; text-decoration: underline;">{{ $accreditor['first_name'] }} {{ $accreditor['last_name'] }}</th>
        </tr>
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 14px; width: 100%">Accreditor</th>
        </tr>
    </table>
    <div style="page-break-after: always"></div>
    <h4 style="text-align: center">SUMMARY OF RATINGS</h4>
    <table class="table table-bordered" style="table-layout: fixed; width: 100%">
        <thead>
        <tr>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 70%;">Parameters</th>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 15%;">Numerical Rating</th>
            <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 15%;">Descriptive Rating</th>
        </tr>
        </thead>
        <tbody>
        @foreach($parameter_results as $parameter_result)
            <tr>
                <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 70%;font-weight: bold;">{{ $parameter_result['parameter'] }}</th>
                @foreach($parameter_result['parameter_mean'] as $parameter_mean)
                    @if($parameter_mean['id'] == $accreditor['id'])
                        <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 15%;">{{ $parameter_mean['parameter_mean'] }}</th>
                        <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 15%;">{{ $parameter_mean['descriptive_rating'] }}</th>
                    @endif
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
    <table class="table-borderless" style="table-layout: fixed; width: 100%">
        @foreach($total_parameter_means as $total_parameter_mean)
            @if($total_parameter_mean['id'] == $accreditor['id'])
                <tr>
                    <th scope="row" class="small" style="text-align: right; font-size: 12px; width: 70%;font-weight: bold;">Total:</th>
                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 15%;">{{ $total_parameter_mean['total'] }}</th>
                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 15%;"></th>
                </tr>
            @endif
        @endforeach
        @foreach($area_means as $area_mean)
            <tr>
                <th scope="row" class="small" style="text-align: right; font-size: 12px; width: 70%;font-weight: bold;">Mean:</th>
                <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 15%;">{{ $area_mean['area_mean'] }}</th>
                <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 15%;">{{ $area_mean['descriptive_rating'] }}</th>
            </tr>
        @endforeach
    </table>
    <p style="text-align: center;font-size: 12px;">LEAD ACCREDITOR/S</p>
    <div style="page-break-after: always"></div>
@endforeach

</body>
</html>
