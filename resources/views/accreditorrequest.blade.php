<div width="100%" style="background: #f8f8f8; padding: 0px 0px; font-family:arial; line-height:28px; height:100%;  width: 100%; color: #514d6a;">
    <div style="max-width: 700px; padding:50px 0px;  margin: 0px auto; font-size: 14px">
        <table border="0" cellpadding="0" cellspacing="0" style="width: 100%; margin-bottom: 20px">
            <tbody>
            <tr>
                <td align="center">
                    <h2>{{ $details['title'] }}</h2>
                </td>
            </tr>
            </tbody>
        </table>
        <div style="padding: 40px; background: #fff;">
            <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
                <tbody>
                <tr>
                    <td align="justify" style="font-size: 17px; color: #2b2b2b;">
                        <p style="margin-top: 50px">Hi, </p>
                        <p>Accreditor request:</p>
                        <p><strong>SUC: </strong>{{ $details['suc'] }}<br>
                            <strong>Program: </strong>{{ $details['program'] }}<br>
                            <strong>Area: </strong>{{ $details['area_number'] }} {{ $details['area_name'] }}<br>
                            <strong>Date: </strong>{{ $details['start_date'] }} - {{ $details['start_end'] }}</p>
                        <p>{{$details['link']}}</p>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div style="text-align: center; font-size: 12px; color: #b2b2b5; margin-top: 20px">
            <p> Powered by Digital Transformation
        </div>
    </div>
</div>
