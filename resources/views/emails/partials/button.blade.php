{{--
    Partial tombol CTA email (neon ala situs).
    Pakai: @include('emails.partials.button', ['url' => $url, 'label' => 'Tombol'])
--}}
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:30px auto 10px;">
    <tr>
        <td align="center" bgcolor="#01F501" style="border-radius:8px;background-color:#01F501;box-shadow:0 4px 14px rgba(1,245,1,.35);">
            <a href="{{ $url }}"
               style="display:inline-block;padding:14px 36px;font-family:'Sora','Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:14px;font-weight:700;color:#150A34;text-decoration:none;border-radius:8px;">
                {{ $label }} &#9889;
            </a>
        </td>
    </tr>
</table>
