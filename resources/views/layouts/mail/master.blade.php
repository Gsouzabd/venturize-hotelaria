<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }}</title>
</head>
<body
    style="background-color: #f6f6f6; color:#3B3B3B; font-family: Helvetica, sans-serif; font-size: 14px; padding: 40px;">
<div style="background-color: #281c40; border-radius: 6px; width: 600px; margin: 0 auto 10px auto; padding: 0 20px;">
    <table style="border: 0; width: 100%; margin: 0; padding: 0;">
        <tr>
            <td style="padding: 20px 0; text-align: center; vertical-align: middle;">
                <img src="{{ asset('assets/mail/images/logo.png') }}" alt="Logo">
            </td>
        </tr>
    </table>
</div>
<div
    style="background-color: #fff; border-radius: 6px; border: 1px solid #ddd; width: 600px; margin: 0 auto; padding: 20px;">
    <table style="border: 0; width: 100%; margin: 0; padding: 0;">
        <tr>
            <td>@yield('content')</td>
        </tr>
    </table>
</div>
</body>
</html>
