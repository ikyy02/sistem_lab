<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul ?? 'Notifikasi SIARKA' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;color:#334155;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;background-color:#f4f4f5;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background-color:#ffffff;border:1px solid #e4e4e7;border-radius:6px;">
                    <tr>
                        <td style="padding:24px 28px 8px;font-size:13px;color:#64748b;">
                            <strong style="color:#1e293b;">SIARKA</strong> &middot; Laboratorium Jurusan Komputer dan Bisnis
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 28px 28px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px;border-top:1px solid #e4e4e7;font-size:12px;line-height:1.5;color:#64748b;">
                            Pesan otomatis dari <strong>SIARKA</strong>, Laboratorium Jurusan Komputer dan Bisnis.<br>
                            <a href="mailto:noreply@siarka.dev" style="color:#94a3b8;text-decoration:none;">noreply@siarka.dev</a>
                            &nbsp;&mdash;&nbsp;Jangan balas email ini.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
