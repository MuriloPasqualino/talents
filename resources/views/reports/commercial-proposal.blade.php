<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Proposta Comercial — {{ $proposal->code }}</title>
    <style>
        @page { margin: 12mm 14mm 22mm 14mm; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0f172a;
            line-height: 1.45;
            margin: 0;
            padding: 0 0 6mm;
            box-sizing: border-box;
        }
        .top-stripe {
            height: 3mm;
            background: #4a2070;
            margin: 0 0 8px;
            width: 100%;
        }
        .header { width: 100%; padding-bottom: 10px; margin-bottom: 4px; border-bottom: 1px solid #e5e7eb; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: middle; }
        .header img { max-height: 52px; width: auto; display: block; }
        .header .meta { text-align: right; vertical-align: top; padding-top: 4px; }
        .meta-row { margin-bottom: 6px; }
        .meta-lbl {
            display: block;
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: bold;
        }
        .meta-val { font-size: 12px; color: #0f172a; font-weight: bold; }
        h1 {
            font-size: 22px;
            font-weight: 700;
            color: #4a2070;
            margin: 10px 0 0;
            letter-spacing: -0.02em;
            line-height: 1.15;
        }
        .subtitle {
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin: 8px 0 0;
            line-height: 1.35;
        }
        .client-line {
            font-size: 12px;
            color: #475569;
            margin-top: 6px;
        }
        .accent-line {
            width: 56px;
            height: 3px;
            background: #f4b400;
            margin-top: 6px;
            margin-bottom: 0;
        }
        h2 {
            font-size: 12px;
            color: #4a2070;
            margin: 16px 0 6px;
            padding: 0;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
        }
        h3.service-title {
            font-size: 13px;
            color: #0f172a;
            margin: 18px 0 4px;
            font-weight: 700;
        }
        .muted { color: #64748b; font-size: 11px; line-height: 1.45; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid td { vertical-align: top; padding: 3px 0; }
        .grid .label {
            color: #94a3b8;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: bold;
        }
        .grid .value { color: #0f172a; font-size: 12px; font-weight: bold; margin-top: 2px; }
        .section-text {
            font-size: 11px;
            color: #334155;
            line-height: 1.5;
            margin: 4px 0 0;
        }
        .investment {
            font-size: 11px;
            color: #0f172a;
            margin: 6px 0 4px;
        }
        .investment strong { color: #4a2070; }
        .service-detail {
            font-size: 10px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .service-block {
            page-break-inside: avoid;
            margin-bottom: 4px;
        }
        .desc-bullets {
            margin: 4px 0 6px 18px;
            padding: 0;
            font-size: 11px;
            color: #334155;
            line-height: 1.45;
        }
        .desc-bullets li { margin-bottom: 3px; }
        .desc-paragraph {
            margin: 4px 0 6px;
            font-size: 11px;
            color: #334155;
            line-height: 1.45;
        }
        table.services {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            font-size: 11px;
            page-break-inside: avoid;
        }
        table.services th, table.services td {
            border-bottom: 1px solid #f1f5f9;
            padding: 7px 4px;
            text-align: left;
        }
        table.services th {
            background: transparent;
            color: #4a2070;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
            border-bottom: 1px solid #4a2070;
        }
        table.services td.value, table.services th.value { text-align: right; }
        table.services tr.total td {
            background: transparent;
            font-weight: 700;
            font-size: 14px;
            color: #4a2070;
            border-top: 2px solid #4a2070;
            border-bottom: none;
            padding-top: 8px;
        }
        .commission-inline {
            margin-top: 8px;
            font-size: 11px;
            color: #475569;
            line-height: 1.45;
        }
        .notes {
            margin-top: 12px;
            padding: 0 0 0 14px;
            border-left: 3px solid #f4b400;
            font-size: 11px;
            color: #475569;
            background: transparent;
        }
        .notes strong { color: #0f172a; }
        .notes-proposal {
            border-left-color: #06b6d4;
            color: #475569;
        }
        .closing-text {
            margin-top: 14px;
            font-size: 11px;
            color: #334155;
            line-height: 1.55;
        }
        .footer-wrap {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            page-break-inside: avoid;
            z-index: 10;
        }
        .footer-meta {
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            padding: 4px 0 4px;
        }
        .footer-band {
            width: 100%;
            background: #4a2070;
            color: #fff;
            font-size: 10px;
            padding: 8px 0;
        }
        .footer-band table { width: 100%; border-collapse: collapse; }
        .footer-band td { vertical-align: middle; padding: 4px 16px; color: #fff; font-weight: bold; }
        .footer-band .col-left { text-align: left; }
        .footer-band .col-right { text-align: right; }
    </style>
</head>
<body>
    @php
        $brl = fn ($cents) => 'R$ '.number_format(((int) $cents) / 100, 2, ',', '.');
        $paymentConditions = filled($settings->pdf_condicoes_pagamento)
            ? $settings->pdf_condicoes_pagamento
            : \App\Support\CommercialProposalPdfDefaults::defaultPaymentConditions();
        $closingText = filled($settings->pdf_texto_encerramento)
            ? $settings->pdf_texto_encerramento
            : \App\Support\CommercialProposalPdfDefaults::defaultClosingText();
    @endphp

    <div class="doc-main">
    <div class="top-stripe"></div>

    <div class="header">
        <table>
            <tr>
                <td style="width: 48%;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Talents">
                    @endif
                </td>
                <td class="meta">
                    <div class="meta-row">
                        <span class="meta-lbl">Proposta</span>
                        <span class="meta-val">{{ $proposal->code }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-lbl">Emitida em</span>
                        <span class="meta-val">{{ optional($proposal->created_at)->format('d/m/Y') }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-lbl">Válida até</span>
                        <span class="meta-val">{{ $validityDate->format('d/m/Y') }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <h1>Proposta Comercial</h1>
    @if($proposal->pdf_subtitle)
        <p class="subtitle">{{ $proposal->pdf_subtitle }}</p>
    @endif
    <p class="client-line"><strong>Cliente:</strong> {{ $proposal->client_name }}</p>
    <div class="accent-line"></div>

    <h2>Público Atendido</h2>
    <p class="section-text">Serão contemplados {{ number_format((int) $proposal->employee_count, 0, ',', '.') }} colaboradores.</p>

    @if($proposal->pdf_objetivo)
        <h2>Objetivo</h2>
        <p class="section-text">{!! nl2br(e($proposal->pdf_objetivo)) !!}</p>
    @endif

    @if(empty($services))
        <h2>Serviços</h2>
        <p class="muted">Nenhum serviço selecionado nesta proposta.</p>
    @else
        @foreach($services as $index => $line)
            <div class="service-block">
                <h3 class="service-title">{{ $index + 1 }}. {{ $line['label'] }}</h3>
                @if(!empty($line['detail']))
                    <p class="service-detail">{{ $line['detail'] }}</p>
                @endif
                <p class="investment"><strong>Investimento:</strong> {{ $brl($line['value_cents']) }}</p>
                @if(!empty($line['description']))
                    <div class="service-description">
                        @include('reports.partials.description-text', ['text' => $line['description']])
                    </div>
                @endif
            </div>
        @endforeach

        <h2>Resumo do Investimento</h2>
        <table class="services">
            <thead>
                <tr>
                    <th style="width: 70%;">Serviço</th>
                    <th class="value" style="width: 30%;">Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $line)
                    <tr>
                        <td>{{ $line['label'] }}</td>
                        <td class="value">{{ $brl($line['value_cents']) }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td>Honorário Total</td>
                    <td class="value">{{ $brl((int) $proposal->total_final_cents) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <h2>Condições de Pagamento</h2>
    <div class="section-text">
        @include('reports.partials.description-text', ['text' => $paymentConditions])
        <p class="desc-paragraph">• Prazo de validade desta proposta: {{ $settings->pdf_validade_dias ?? 7 }} dias.</p>
    </div>

    @if($proposal->seller)
        <p class="commission-inline">
            <strong>Vendedor responsável:</strong> {{ $proposal->seller->name }}
        </p>
    @endif

    @if($closingText)
        <div class="closing-text">
            {!! nl2br(e($closingText)) !!}
        </div>
    @endif

    @if($settings->pdf_observacoes)
        <div class="notes">
            <strong>Observações:</strong><br>
            {!! nl2br(e($settings->pdf_observacoes)) !!}
        </div>
    @endif

    @if($proposal->notes)
        <div class="notes notes-proposal">
            <strong>Observações específicas desta proposta:</strong><br>
            {!! nl2br(e($proposal->notes)) !!}
        </div>
    @endif

    </div>

    <div class="footer-wrap">
        <div class="footer-meta">
            Proposta {{ $proposal->code }} — gerada em {{ now()->format('d/m/Y H:i') }}
        </div>
        <div class="footer-band">
            <table>
                <tr>
                    <td class="col-left">WhatsApp (11) 97570-3032</td>
                    <td class="col-right">contato@talentsgestao.com</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
