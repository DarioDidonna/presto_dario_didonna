<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Presto.it - Candidatura Revisore</title>
    @vite(['resources/css/style.css'])
</head>

<body style="background-color: #060a14; margin: 0; padding: 0; -webkit-text-size-adjust: none; text-size-adjust: none;">

    <span style="display:none; max-height:0px; max-width:0px; opacity:0; overflow:hidden; font-size: 1px;">
        {{ __('ui.revisor_mail_preview') }}
    </span>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#060a14">
        <tr>
            <td align="center" style="padding: 40px 15px;">
                
                <div class="email-container" style="background-color: #ffffff; outline: 1px solid #06b6d4; outline-offset: -4px; box-shadow: 0 10px 30px rgba(6, 182, 212, 0.25); border-radius: 8px; max-width: 600px; padding: 40px 30px; text-align: left;">

                    <div class="email-header" style="text-align: center; margin-bottom: 35px; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px;">
                        <h2 class="brand-title" style="color: #0f172a; font-weight: 900; letter-spacing: 3px; margin: 0;">
                            PRESTO<span class="brand-dot" style="color: #06b6d4;">.</span>IT
                        </h2>
                        <p class="email-subtitle" style="color: #06b6d4; font-size: 11px; font-weight: bold; tracking-widest; text-transform: uppercase; margin-top: 5px; margin-bottom: 0; font-family: monospace;">
                            // {{ __('ui.revisor_mail_badge') }}
                        </p>
                    </div>

                    <h1 class="email-title" style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 12px; text-align: center;">
                        {{ __('ui.revisor_mail_title') }}
                    </h1>
                    <p class="email-lead" style="color: #64748b; font-size: 15px; text-align: center; margin-bottom: 30px; line-height: 1.5;">
                        {{ __('ui.revisor_mail_body') }}
                    </p>

                    <div class="user-data-box" style="background-color: #f8fafc; border-radius: 6px; padding: 20px; margin-bottom: 35px; border-left: 4px solid #06b6d4; outline: 1px solid #e2e8f0;">
                        <div class="user-data-line" style="margin-bottom: 10px; font-size: 14px; color: #475569; font-family: monospace;">
                            <strong style="color: #0f172a; text-transform: uppercase;">[ID_UTENTE]:</strong> 
                            <span style="color: #334155; font-family: sans-serif; font-weight: 600; font-size: 15px;">{{ $user->name }}</span>
                        </div>
                        <div class="user-data-line last" style="font-size: 14px; color: #475569; font-family: monospace; margin: 0;">
                            <strong style="color: #0f172a; text-transform: uppercase;">[CONTATTO]:</strong> 
                            <span style="color: #06b6d4; font-weight: 600; font-size: 15px;">{{ $user->email }}</span>
                        </div>
                    </div>

                    <div class="action-section" style="text-align: center; margin-bottom: 15px;">
                        <p class="action-text" style="color: #334155; font-size: 14px; margin-bottom: 25px; line-height: 1.5;">
                            {{ __('ui.revisor_mail_action') }}
                        </p>

                        <a href="{{ route('make.revisor', compact('user')) }}" class="btn-revisor" style="display: inline-block; background-color: #06b6d4; color: #060a14; font-weight: 800; text-transform: uppercase; font-size: 13px; letter-spacing: 1.5px; text-decoration: none; padding: 14px 35px; border-radius: 4px; outline: 2px solid #06b6d4; outline-offset: 3px; box-shadow: 0 4px 14px rgba(6, 182, 212, 0.3);">
                            {{ __('ui.revisor_mail_button') }} &raquo;
                        </a>
                    </div>

                    <div class="email-footer" style="text-align: center; margin-top: 45px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                        <p class="footer-text" style="color: #94a3b8; font-size: 11px; margin: 0; font-family: monospace; letter-spacing: 0.5px;">
                            {{ __('ui.revisor_mail_footer') }}
                        </p>
                    </div>

                </div>

            </td>
        </tr>
    </table>

</body>

</html>