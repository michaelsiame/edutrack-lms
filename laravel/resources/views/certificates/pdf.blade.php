<table cellpadding="10" cellspacing="0" border="0" style="width:100%; border:3px solid #1e3a5f;">
    <tr>
        <td style="border:2px solid #c9a227; padding:20px;">
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tr>
                    <td style="width:20%; text-align:center;">
                        @if(file_exists(public_path('assets/images/logo-sm.png')))
                            <img src="{{ public_path('assets/images/logo-sm.png') }}" style="height:80px;" />
                        @endif
                    </td>
                    <td style="width:60%; text-align:center;">
                        <p style="font-size:12px; margin:0; color:#1e3a5f;">EDUTRACK COMPUTER TRAINING COLLEGE</p>
                        <p style="font-size:10px; margin:0; color:#666;">TEVETA Registered | Kalomo, Zambia</p>
                    </td>
                    <td style="width:20%; text-align:center;">
                        @if(file_exists(public_path('assets/images/teveta-logo-sm.png')))
                            <img src="{{ public_path('assets/images/teveta-logo-sm.png') }}" style="height:60px;" />
                        @endif
                    </td>
                </tr>
            </table>

            <br><br>

            <p style="font-size:24px; text-align:center; color:#1e3a5f; font-family:times; margin:10px 0;">
                <strong>CERTIFICATE OF COMPLETION</strong>
            </p>

            <p style="font-size:12px; text-align:center; color:#666; margin:5px 0;">
                This is to certify that
            </p>

            <p style="font-size:28px; text-align:center; color:#1e3a5f; font-family:times; margin:15px 0;">
                <strong>{{ $student_name }}</strong>
            </p>

            <p style="font-size:12px; text-align:center; color:#666; margin:5px 0;">
                has successfully completed the course
            </p>

            <p style="font-size:20px; text-align:center; color:#1e3a5f; font-family:times; margin:15px 0;">
                <strong>{{ $course_title }}</strong>
            </p>

            <p style="font-size:11px; text-align:center; color:#666; margin:10px 0;">
                Certificate Number: <strong>{{ $certificate_number }}</strong><br>
                Date of Issue: <strong>{{ $issued_date }}</strong><br>
                @if($final_score)
                    Final Score: <strong>{{ $final_score }}%</strong>
                @endif
            </p>

            <br><br>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tr>
                    <td style="width:33%; text-align:center; border-top:1px solid #333; padding-top:5px;">
                        <p style="font-size:10px; margin:0;">Director</p>
                    </td>
                    <td style="width:33%; text-align:center;">
                        <p style="font-size:9px; color:#999; margin:0;">
                            Verify: {{ $verification_code }}
                        </p>
                    </td>
                    <td style="width:33%; text-align:center; border-top:1px solid #333; padding-top:5px;">
                        <p style="font-size:10px; margin:0;">Instructor</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
