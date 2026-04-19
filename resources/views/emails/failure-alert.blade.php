<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Failure alert</title>
</head>
<body style="margin:0; padding:24px; background:#f4f4f4; color:#111827; font-family:Arial, Helvetica, sans-serif;">
    <div style="max-width:920px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
        <div style="padding:24px; background:#111827; color:#ffffff;">
            <h1 style="margin:0; font-size:24px;">Alert fallimento {{ $alert['flow_label'] }}</h1>
            <p style="margin:8px 0 0; font-size:14px;">Segnalazione inviata automaticamente a Future Plus.</p>
        </div>

        <div style="padding:24px;">
            <h2 style="margin:0 0 12px; font-size:18px;">Contesto</h2>
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <tbody>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb; width:220px;"><strong>Segnalato il</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['reported_at'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;"><strong>Ristorante</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['restaurant']['name'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;"><strong>Database</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['restaurant']['database'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;"><strong>App URL</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['restaurant']['app_url'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;"><strong>Slot richiesto</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['slot'] ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <h2 style="margin:24px 0 12px; font-size:18px;">Cliente</h2>
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <tbody>
                    @forelse ($alert['customer'] as $key => $value)
                        <tr>
                            <td style="padding:8px 12px; border:1px solid #e5e7eb; width:220px;"><strong>{{ $key }}</strong></td>
                            <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $value }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="padding:8px 12px; border:1px solid #e5e7eb;">Nessun dato cliente disponibile</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <h2 style="margin:24px 0 12px; font-size:18px;">Errore</h2>
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <tbody>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb; width:220px;"><strong>Tipo</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['error']['type'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;"><strong>Messaggio</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['error']['message'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;"><strong>Status risposta</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['error']['response_status'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;"><strong>Classe eccezione</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['error']['exception_class'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;"><strong>File</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['error']['file'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;"><strong>Linea</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['error']['line'] ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <h2 style="margin:24px 0 12px; font-size:18px;">Risorsa</h2>
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <tbody>
                    @forelse ($alert['resource'] as $key => $value)
                        <tr>
                            <td style="padding:8px 12px; border:1px solid #e5e7eb; width:220px;"><strong>{{ $key }}</strong></td>
                            <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $value }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="padding:8px 12px; border:1px solid #e5e7eb;">Nessun identificativo disponibile</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <h2 style="margin:24px 0 12px; font-size:18px;">Richiesta</h2>
            <table style="width:100%; border-collapse:collapse; font-size:14px;">
                <tbody>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb; width:220px;"><strong>Metodo</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['request']['method'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;"><strong>URL</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['request']['url'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;"><strong>Route</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['request']['route_name'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;"><strong>IP</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['request']['ip'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;"><strong>User agent</strong></td>
                        <td style="padding:8px 12px; border:1px solid #e5e7eb;">{{ $alert['request']['user_agent'] ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <h2 style="margin:24px 0 12px; font-size:18px;">Dettagli errore</h2>
            <pre style="margin:0; padding:16px; background:#111827; color:#f9fafb; border-radius:8px; overflow:auto; white-space:pre-wrap;">{{ $alert['details_json'] }}</pre>

            <h2 style="margin:24px 0 12px; font-size:18px;">Payload richiesta</h2>
            <pre style="margin:0; padding:16px; background:#111827; color:#f9fafb; border-radius:8px; overflow:auto; white-space:pre-wrap;">{{ $alert['payload_json'] }}</pre>

            @if (!empty($alert['trace']))
                <h2 style="margin:24px 0 12px; font-size:18px;">Trace</h2>
                <pre style="margin:0; padding:16px; background:#111827; color:#f9fafb; border-radius:8px; overflow:auto; white-space:pre-wrap;">{{ $alert['trace'] }}</pre>
            @endif
        </div>
    </div>
</body>
</html>
