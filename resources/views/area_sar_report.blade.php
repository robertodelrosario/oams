<!DOCTYPE html>
<html lang="en">
<body>
<div class="container mt-5">

    <table class="table table-bordered mb-5">
        <thead>
        <tr class="table-danger">
            <th scope="col">PARAMETER</th>
            <th scope="col">ACCREDITOR RATING</th>
            <th scope="col">AVERAGE RATING</th>
            <th scope="col">DESCRIPTIVE RATING</th>
        </tr>
        </thead>
        <tbody>
        @foreach($parameters as $parameter)
            <tr>
                <th scope="row">{{ $parameter->parameter }}</th>
                <td>
                    @foreach($means as $mean)
                        @if($mean->program_parameter_id == $parameter->id) <br>{{ $mean->first_name }} {{ $mean->last_name }} : {{$mean->parameter_mean}}
                        @endif
                    @endforeach
                </td>
                <td>
                    @foreach($results as $result)
                        @if($result['program_parameter_id'] == $parameter->id) {{ $result['average_mean'] }} ({{$result['status']}})
                        @endif
                    @endforeach
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>

<script src="{{ asset('js/app.js') }}" type="text/js"></script>
</body>

</html>
<?php
