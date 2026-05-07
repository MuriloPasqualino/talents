<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Proposta Comercial — {{ $proposal->code }}</title>
    <style>
        @page { margin: 28mm 18mm 22mm 18mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; line-height: 1.45; }
        .header { width: 100%; border-bottom: 2px solid #4a2070; padding-bottom: 10px; margin-bottom: 18px; }
        .header table { width: 100%; }
        .header td { vertical-align: middle; }
        .header .brand { font-size: 22px; color: #4a2070; font-weight: bold; }
        .header .meta { text-align: right; font-size: 11px; color: #475569; }
        .header img { max-height: 56px; }
        h1 { font-size: 18px; color: #4a2070; margin: 0 0 4px; }
        h2 { font-size: 14px; color: #4a2070; margin: 22px 0 8px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .muted { color: #64748b; font-size: 11px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 10px; font-weight: bold; }
        .badge-open { background: #fef3c7; color: #92400e; }
        .badge-closed { background: #d1fae5; color: #065f46; }
        .grid { width: 100%; }
        .grid td { vertical-align: top; padding: 4px 0; }
        .grid .label { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; }
        .grid .value { color: #0f172a; font-size: 12px; font-weight: 600; }
        table.services { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.services th, table.services td { border-bottom: 1px solid #e5e7eb; padding: 9px 8px; text-align: left; }
        table.services th { background: #f5f0fa; color: #4a2070; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
        table.services td.value, table.services th.value { text-align: right; font-variant-numeric: tabular-nums; }
        table.services tr.total td { background: #faf5ff; font-weight: bold; font-size: 14px; color: #4a2070; border-top: 2px solid #c4b5fd; border-bottom: none; }
        .commission-box { margin-top: 14px; padding: 10px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 11px; }
        .notes { margin-top: 10px; padding: 12px; background: #fffbeb; border-left: 4px solid #fbbf24; font-size: 11px; color: #78350f; }
        .accept { margin-top: 28px; padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 11px; }
        .signature { margin-top: 56px; }
        .signature table { width: 100%; }
        .signature td { width: 50%; text-align: center; padding: 0 18px; }
        .signature .line { border-top: 1px solid #1f2937; padding-top: 4px; font-size: 10px; color: #475569; }
        .footer { position: fixed; bottom: 8mm; left: 0; right: 0; text-align: center; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
    @php
        $brl = fn (int $cents) => 'R$ '.number_format($cents / 100, 2, ',', '.');
    @endphp

    <div class="header">
        <table>
            <tr>
                <td style="width: 40%;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo">
                    @else
                        <span class="brand">Pasqualino</span>
                    @endif
                </td>
                <td class="meta">
                    <div><strong>Proposta {{ $proposal->code }}</strong></div>
                    <div>Emitida em {{ optional($proposal->created_at)->format('d/m/Y') }}</div>
                    <div>Válida até <strong>{{ $validityDate->format('d/m/Y') }}</strong></div>
                </td>
            </tr>
        </table>
    </div>

    <h1>Proposta Comercial</h1>
    <p class="muted">
        @if($proposal->is_closed)
            <span class="badge badge-closed">Fechada</span>
        @else
            <span class="badge badge-open">Em negociação</span>
        @endif
        &nbsp;Esta proposta é nominal e personalizada para o cliente abaixo.
    </p>

    <h2>Dados do cliente</h2>
    <table class="grid">
        <tr>
            <td style="width: 60%;">
                <div class="label">Razão social / Nome</div>
                <div class="value">{{ $proposal->client_name }}</div>
            </td>
            <td style="width: 40%;">
                <div class="label">CNPJ</div>
                <div class="value">{{ $proposal->client_cnpj ?: '—' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">E-mail</div>
                <div class="value">{{ $proposal->client_email ?: '—' }}</div>
            </td>
            <td>
                <div class="label">Telefone</div>
                <div class="value">{{ $proposal->client_phone ?: '—' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Indicação</div>
                <div class="value">{{ $proposal->indication ?: '—' }}</div>
            </td>
            <td>
                <div class="label">Nº de funcionários</div>
                <div class="value">{{ $proposal->employee_count }}</div>
            </td>
        </tr>
    </table>

    <h2>Serviços contratados</h2>
    @if(empty($services))
        <p class="muted">Nenhum serviço selecionado nesta proposta.</p>
    @else
        <table class="services">
            <thead>
                <tr>
                    <th style="width: 40%;">Serviço</th>
                    <th style="width: 40%;">Detalhe</th>
                    <th class="value" style="width: 20%;">Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $line)
                    <tr>
                        <td>{{ $line['label'] }}</td>
                        <td class="muted">{{ $line['detail'] }}</td>
                        <td class="value">{{ $brl($line['value_cents']) }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td colspan="2">Honorário Total</td>
                    <td class="value">{{ $brl((int) $proposal->total_final_cents) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    @if($proposal->seller)
        <div class="commission-box">
            <strong>Vendedor responsável:</strong> {{ $proposal->seller->name }}
            @if($proposal->commission_percent > 0)
                &nbsp;|&nbsp; Comissão: {{ number_format((float) $proposal->commission_percent, 2, ',', '.') }}%
                ({{ $brl((int) $proposal->commission_cents) }})
            @endif
        </div>
    @endif

    @if($settings->pdf_observacoes)
        <div class="notes">
            <strong>Observações:</strong><br>
            {!! nl2br(e($settings->pdf_observacoes)) !!}
        </div>
    @endif

    @if($proposal->notes)
        <div class="notes" style="background: #ecfeff; border-left-color: #06b6d4; color: #155e75;">
            <strong>Observações específicas desta proposta:</strong><br>
            {!! nl2br(e($proposal->notes)) !!}
        </div>
    @endif

    <h2>Aceite</h2>
    <div class="accept">
        {{ $settings->pdf_aceite_texto ?: 'Declaro estar de acordo com os termos, valores e prazos descritos nesta proposta comercial.' }}
    </div>

    <div class="signature">
        <table>
            <tr>
                <td>
                    <div class="line">Cliente — {{ $proposal->client_name }}</div>
                </td>
                <td>
                    <div class="line">Pasqualino &mdash; {{ $proposal->seller->name ?? 'Responsável Comercial' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Proposta {{ $proposal->code }} &mdash; gerada em {{ now()->format('d/m/Y H:i') }} &mdash; documento válido com assinatura.
    </div>
</body>
</html>
