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
                                    <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 60%;">{{ $parameter_result['parameter'] }}</th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 10%;"></th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 18%;"></th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 12%;">{{ $param_mean['parameter_mean'] }}</th>
                                </tr>
                            @endif
                        @endforeach
                        @foreach($parameter_result['system_input'] as $system_input)
                            @if($system_input['id'] == $accreditor['id'])
                                <tr>
                                    <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 60%;">SYSTEM-INPUTS AND PROCESSES</th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 10%;"></th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 18%;">{{ $system_input['score'] }}</th>
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
                                                @if($statement['degree'] == 1) <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 60%;">{{ $statement['benchmark_statement'] }}</th>
                                                @elseif($statement['degree'] == 2) <th scope="row" class="small" style="margin-left: 7%;text-align: left; font-size: 12px; width: 60%;">{{ $statement['benchmark_statement'] }}</th>
                                                @elseif($statement['degree'] == 3) <th scope="row" class="small" style="margin-left: 14%;text-align: left; font-size: 12px; width: 60%;">{{ $statement['benchmark_statement'] }}</th>
                                                @endif
                                                <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 10%;">{{ $score['score'] }}</th>
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
                                    <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 60%;">IMPLEMENTATION</th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 10%;"></th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 18%;">{{ $implementation['score'] }}</th>
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
                                                @if($statement['degree'] == 1) <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 60%;">{{ $statement['benchmark_statement'] }}</th>
                                                @elseif($statement['degree'] == 2) <th scope="row" class="small" style="margin-left: 7%;text-align: left; font-size: 12px; width: 60%;">{{ $statement['benchmark_statement'] }}</th>
                                                @elseif($statement['degree'] == 3) <th scope="row" class="small" style="margin-left: 14%;text-align: left; font-size: 12px; width: 60%;">{{ $statement['benchmark_statement'] }}</th>
                                                @endif
                                                <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 10%;">{{ $score['score'] }}</th>
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
                                    <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 60%;">OUTCOME/S</th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 10%;"></th>
                                    <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 18%;">{{ $outcome['score'] }}</th>
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
                                                @if($statement['degree'] == 1) <th scope="row" class="small" style="text-align: left; font-size: 12px; width: 60%;">{{ $statement['benchmark_statement'] }}</th>
                                                @elseif($statement['degree'] == 2) <th scope="row" class="small" style="margin-left: 7%;text-align: left; font-size: 12px; width: 60%;">{{ $statement['benchmark_statement'] }}</th>
                                                @elseif($statement['degree'] == 3) <th scope="row" class="small" style="margin-left: 14%;text-align: left; font-size: 12px; width: 60%;">{{ $statement['benchmark_statement'] }}</th>
                                                @endif
                                                <th scope="row" class="small" style="text-align: center; font-size: 12px; width: 10%;">{{ $score['score'] }}</th>
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
@endforeach

</body>
</html>
