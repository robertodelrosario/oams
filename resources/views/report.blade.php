<!DOCTYPE html>
<html lang="en">
<body>
<div class="container mt-5">

    <table class="table table-bordered mb-5">
        <thead>
        <tr class="table-danger">
            <th scope="col">Parameters</th>
            <th scope="col">Benchmark Statements</th>
            <th scope="col">Remarks</th>
        </tr>
        </thead>
        <tbody>
        @foreach($remarks as $data)
            <tr>
                <th scope="row">{{ $data->parameter }}</th>
                <td>{{ $data->statement }}</td>
                <td>{{ $data->remark }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>

<script src="{{ asset('js/app.js') }}" type="text/js"></script>
</body>

</html>
