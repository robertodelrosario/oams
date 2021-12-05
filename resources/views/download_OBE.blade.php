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
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 55%;">Indicators</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 10%;writing-mode: vertical-lr"">Item Rating (IR)</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 20%; -webkit-writing-mode: sideways-lr"">System-Implementation-outcome Mean (SIOM)</th>
                <th scope="col" class="font-weight-bold" style="text-align: center; font-size: 12px; width: 15%;writing-mode: vertical-lr"">Parameter Mean (PM)</th>
            </tr>
        </thead>
    </table>
</div>
</body>
</html>
