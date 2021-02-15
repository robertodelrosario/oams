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
        @foreach($data as $score)
            <tr>
                <th scope="row">{{ $score->parameter }}</th>
                <td>{{ $score->statement }}</td>
                <td>{{ $score->remark }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>

<script src="{{ asset('js/app.js') }}" type="text/js"></script>
</body>

</html>
<?php
