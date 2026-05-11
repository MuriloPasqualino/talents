<?php

namespace App\Support;

/**
 * Corpo HTML dos modelos padrão (sem tabelas fixas do Word).
 * Usa apenas placeholders dinâmicos — mesmos dados do PDF da proposta comercial.
 */
final class CanonicalContractTemplates
{
    /**
     * @return array<string, string> nome do modelo => body_html
     */
    public static function all(): array
    {
        return [
            'Consultoria - Padrão Talents' => self::consultoria(),
            'Contratação de Talentos - Padrão Talents' => self::contratacaoTalentos(),
            'Palestra - Padrão Talents' => self::palestra(),
        ];
    }

    private static function consultoria(): string
    {
        return <<<'HTML'
<h1 style="font-size:15px;color:#4a2070;margin:0 0 14px;text-align:center;text-transform:uppercase;letter-spacing:0.04em;">Contrato de prestação de serviços de consultoria Talents</h1>

<p style="font-size:11px;line-height:1.55;color:#0f172a;"><strong>CONTRATANTE:</strong> {{cliente_nome}}, pessoa jurídica de direito privado, inscrita no CNPJ sob o nº {{cliente_cnpj}}, com sede em {{cliente_endereco}}, neste ato representada por {{cliente_representante}}, doravante denominada <strong>CONTRATANTE</strong>.</p>

<p style="font-size:11px;line-height:1.55;color:#0f172a;"><strong>CONTRATADA:</strong> {{empresa_nome}}, CNPJ nº {{empresa_cnpj}}, com sede em {{empresa_endereco}}, e-mail {{empresa_email}}, telefone {{empresa_telefone}}, {{empresa_representacao}}, doravante denominada <strong>CONTRATADA</strong>.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula primeira – Objeto</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">O presente contrato tem como objeto a prestação de serviços de consultoria de gestão de pessoas, nos termos da <strong>Proposta Comercial nº {{proposta_codigo}}</strong> (emitida em {{proposta_emitida_em}}, com referência de validade conforme parametrização comercial), incluindo, conforme contratado, dentre outras entregas da natureza abaixo:</p>
<ul style="margin:8px 0 12px;padding-left:18px;font-size:11px;line-height:1.5;color:#0f172a;">
<li>Pesquisa de satisfação dos colaboradores;</li>
<li>Aplicação de mapeamento comportamental;</li>
<li>Entrega de relatório de mapeamento comportamental individual completo;</li>
<li>Análise do relatório de mapeamento comportamental individual (conforme plano contratado);</li>
<li>Devolutiva individual ou em grupo (conforme plano contratado);</li>
<li>Engenharia de cargos e relatório Matcher (conforme plano contratado).</li>
</ul>
<p style="font-size:11px;line-height:1.5;color:#64748b;">A discriminação contratual efetiva, valores e totais — somente o que constar na proposta:</p>
{{servicos_detalhada_html}}
<p style="font-size:11px;line-height:1.55;color:#0f172a;margin-top:10px;"><strong>Honorário total:</strong> {{total_reais}} (<em>{{total_extenso}}</em>). Colaboradores considerados na proposta (quando aplicável): <strong>{{numero_funcionarios}}</strong>.</p>
<p style="font-size:11px;line-height:1.45;color:#64748b;">Lista resumida dos serviços contratados: {{servicos_rotulos}}.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula segunda – Prazo</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Este contrato tem início na data de assinatura e permanecerá vigente até a conclusão dos serviços contratados. Referência de prazo operacional (dias), quando aplicável à condição comercial: <strong>{{prazo_dias}}</strong>.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula terceira – Valor e forma de pagamento</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">A CONTRATANTE compromete-se a pagar à CONTRATADA os valores conforme plano e tabela da proposta acima.</p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;"><strong>Forma de pagamento e condições:</strong></p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">{{forma_pagamento}}</p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;"><strong>Dados para pagamento (referência):</strong> e-mail {{empresa_email}} — CNPJ {{empresa_cnpj}}.</p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Os valores e condições apresentados neste instrumento têm validade de <strong>{{validade_proposta_dias}}</strong> dias a contar da data de envio à CONTRATANTE (parâmetro comercial alinhado ao PDF da proposta; validade calculada até {{validade_data}}). Após esse prazo, poderão ser revistos.</p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">§ 1º Em caso de inadimplência, os valores serão acrescidos de multa de 5% e juros de 1% ao mês sobre o saldo devedor.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula quarta – Obrigações da contratada</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">A CONTRATADA compromete-se a: (1) aplicar os mapeamentos comportamentais conforme os serviços contratados e as metodologias e ferramentas utilizadas; (2) entregar os relatórios de mapeamentos comportamentais individuais completos; (3) analisar os mapeamentos comportamentais individuais conforme os serviços contratados; (4) realizar as devolutivas individuais ou em grupo conforme os serviços contratados; (5) garantir confidencialidade sobre as informações coletadas; (6) reaplicar o mapeamento caso necessário, conforme frequência contratada (por exemplo, 6 meses ou 1 ano), para fins de acompanhar a evolução comportamental de cada colaborador.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula quinta – Obrigações da contratante</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">A CONTRATANTE compromete-se a: (1) fornecer dados corretos e completos dos participantes avaliados; (2) efetuar o pagamento conforme as condições acordadas; (3) não compartilhar ou distribuir os relatórios com terceiros sem autorização da CONTRATADA.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula sexta – Confidencialidade</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Ambas as partes obrigam-se a manter em sigilo todas as informações a que tiverem acesso em razão deste contrato, inclusive relatórios e perfis comportamentais dos avaliados.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula sétima – Propriedade intelectual</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Todos os materiais, metodologias, relatórios e documentos utilizados ou entregues pela CONTRATADA são de sua exclusiva propriedade intelectual, sendo vedada a cópia, reprodução ou utilização para outros fins sem autorização expressa.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula oitava – Rescisão</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">O contrato poderá ser rescindido por qualquer das partes mediante aviso prévio de 15 dias, desde que não haja pendência de serviços ou pagamentos. Em caso de rescisão, serão devidos os valores proporcionais aos serviços efetivamente prestados.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula nona – Foro</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Para dirimir quaisquer dúvidas oriundas deste contrato, as partes elegem o foro da comarca de {{foro_comarca}}, com exclusão de qualquer outro, por mais privilegiado que seja.</p>

<p style="font-size:11px;line-height:1.55;color:#64748b;margin-top:14px;">Indicação / origem do lead (quando houver): {{proposta_indicacao}}. Observações da proposta: {{proposta_observacoes}}. Comissão comercial (quando houver): {{comissao_percent}}% ({{comissao_reais}}). Responsável comercial: {{vendedor_nome}} ({{vendedor_email}}).</p>

<p style="font-size:11px;line-height:1.55;color:#0f172a;margin-top:28px;text-align:center;">________________________________________<br /><strong>CONTRATANTE</strong></p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;margin-top:28px;text-align:center;">________________________________________<br /><strong>CONTRATADA — {{empresa_nome}}</strong></p>
<p style="font-size:11px;line-height:1.45;color:#0f172a;margin-top:16px;">{{cidade_estado}}, {{data_hoje}}.</p>
HTML;
    }

    private static function contratacaoTalentos(): string
    {
        return <<<'HTML'
<h2 style="font-size:14px;color:#4a2070;margin:16px 0 8px;text-transform:uppercase;">1. Das partes</h2>
<p style="font-size:11px;line-height:1.5;color:#0f172a;"><strong>CONTRATANTE:</strong> {{cliente_nome}}, CNPJ {{cliente_cnpj}}, e-mail {{cliente_email}}, telefone {{cliente_telefone}}, doravante <strong>CONTRATANTE</strong>.</p>
<p style="font-size:11px;line-height:1.5;color:#0f172a;"><strong>CONTRATADA:</strong> {{empresa_nome}}, CNPJ {{empresa_cnpj}}, {{empresa_endereco}} — {{empresa_email}} / {{empresa_telefone}}, doravante <strong>CONTRATADA</strong>.</p>

<h2 style="font-size:14px;color:#4a2070;margin:16px 0 8px;text-transform:uppercase;">2. Do objeto — contratação / recrutamento</h2>
<p style="font-size:11px;line-height:1.5;color:#0f172a;">A CONTRATADA prestará à CONTRATANTE os serviços de recrutamento e seleção / contratação de talentos <strong>na forma contratada na proposta nº {{proposta_codigo}}</strong> (emitida em {{proposta_emitida_em}}, válida até {{validade_data}}). Os serviços efetivos, quantidades e valores são unicamente os da proposta:</p>
<p style="font-size:11px;line-height:1.5;color:#64748b;margin:8px 0;">Destaque do pacote de contratação (quando contratado na proposta):</p>
{{svc_bloco_contratacao_html}}
{{servicos_detalhada_html}}
<p style="font-size:11px;line-height:1.5;color:#0f172a;margin-top:12px;"><strong>Valor total:</strong> {{total_reais}} (<em>{{total_extenso}}</em>).</p>

<h2 style="font-size:14px;color:#4a2070;margin:16px 0 8px;text-transform:uppercase;">3. Pagamento e prazo</h2>
<p style="font-size:11px;line-height:1.5;color:#0f172a;">{{forma_pagamento}}</p>
<p style="font-size:11px;line-height:1.5;color:#0f172a;">Prazo em dias (referência): {{prazo_dias}}</p>

<h2 style="font-size:14px;color:#4a2070;margin:16px 0 8px;text-transform:uppercase;">4. Comercial</h2>
<p style="font-size:11px;line-height:1.5;color:#0f172a;">{{vendedor_nome}} — {{vendedor_email}}. Comissão: {{comissao_percent}}% ({{comissao_reais}}).</p>

<h2 style="font-size:14px;color:#4a2070;margin:16px 0 8px;text-transform:uppercase;">5. Foro</h2>
<p style="font-size:11px;line-height:1.5;color:#0f172a;">Foro de {{cidade_estado}}.</p>
<p style="font-size:11px;line-height:1.45;color:#0f172a;margin-top:24px;">{{cidade_estado}}, {{data_hoje}}.</p>
HTML;
    }

    private static function palestra(): string
    {
        return <<<'HTML'
<h1 style="font-size:15px;color:#4a2070;margin:0 0 14px;text-align:center;text-transform:uppercase;letter-spacing:0.04em;">Contrato de prestação de serviços de palestra</h1>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Partes contratantes</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;"><strong>Contratada:</strong> {{empresa_nome}}, CNPJ nº {{empresa_cnpj}}, com sede em {{empresa_endereco}}, {{empresa_representacao}}, doravante denominada <strong>CONTRATADA</strong>.</p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;"><strong>Contratante:</strong> {{cliente_nome}}, pessoa jurídica de direito privado, inscrita no CNPJ sob o nº {{cliente_cnpj}}, com sede em {{cliente_endereco}}, neste ato representada por {{cliente_representante}}, doravante denominada <strong>CONTRATANTE</strong>.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula primeira – Objeto</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">O presente contrato tem como objeto a prestação de serviços profissionais de palestra, a ser ministrada pela CONTRATADA, conforme detalhamento abaixo e à <strong>Proposta Comercial nº {{proposta_codigo}}</strong>:</p>
<table style="width:100%;border-collapse:collapse;margin:10px 0;font-size:11px;color:#0f172a;">
<tr><td style="border:1px solid #e2e8f0;padding:8px;width:32%;vertical-align:top;"><strong>Tema da palestra</strong></td><td style="border:1px solid #e2e8f0;padding:8px;">{{palestra_tema}}</td></tr>
<tr><td style="border:1px solid #e2e8f0;padding:8px;vertical-align:top;"><strong>Data</strong></td><td style="border:1px solid #e2e8f0;padding:8px;">{{palestra_data}}</td></tr>
<tr><td style="border:1px solid #e2e8f0;padding:8px;vertical-align:top;"><strong>Horário de início</strong></td><td style="border:1px solid #e2e8f0;padding:8px;">{{palestra_horario_inicio}}</td></tr>
<tr><td style="border:1px solid #e2e8f0;padding:8px;vertical-align:top;"><strong>Duração estimada</strong></td><td style="border:1px solid #e2e8f0;padding:8px;">{{palestra_duracao_horas}}</td></tr>
<tr><td style="border:1px solid #e2e8f0;padding:8px;vertical-align:top;"><strong>Local</strong></td><td style="border:1px solid #e2e8f0;padding:8px;">{{palestra_local}}</td></tr>
<tr><td style="border:1px solid #e2e8f0;padding:8px;vertical-align:top;"><strong>Público-alvo estimado</strong></td><td style="border:1px solid #e2e8f0;padding:8px;">{{palestra_publico_estimado}}</td></tr>
<tr><td style="border:1px solid #e2e8f0;padding:8px;vertical-align:top;"><strong>Formato</strong></td><td style="border:1px solid #e2e8f0;padding:8px;">{{palestra_formato_opcoes_html}}</td></tr>
</table>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">§ Único: Qualquer alteração nas condições acima deverá ser comunicada com antecedência mínima de 10 (dez) dias úteis e formalizada por escrito por ambas as partes.</p>
<p style="font-size:11px;line-height:1.5;color:#64748b;margin-top:10px;">Valores e discriminação do pacote contratado na proposta:</p>
{{servicos_detalhada_html}}

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula segunda – Responsabilidades da contratada</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Compete à CONTRATADA:</p>
<ul style="margin:6px 0 12px;padding-left:18px;font-size:11px;line-height:1.5;color:#0f172a;">
<li>Ministrar a palestra conforme tema, data, horário e local acordados;</li>
<li>Comparecer ao local do evento com antecedência mínima de 30 (trinta) minutos para preparo e testes de equipamento;</li>
<li>Conduzir o conteúdo de forma profissional, ética e adequada ao público informado pela CONTRATANTE;</li>
<li>Comunicar com o maior prazo possível qualquer imprevisto que possa comprometer sua presença ou pontualidade;</li>
<li>Manter sigilo sobre informações confidenciais da CONTRATANTE que venham a seu conhecimento em decorrência deste contrato.</li>
</ul>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula terceira – Responsabilidades da contratante</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Compete à CONTRATANTE:</p>
<ul style="margin:6px 0 12px;padding-left:18px;font-size:11px;line-height:1.5;color:#0f172a;">
<li>Realizar o pagamento conforme as condições estabelecidas na Cláusula Quarta deste contrato;</li>
<li>Disponibilizar estrutura adequada para a realização da palestra, incluindo: som, projetor ou tela de projeção, microfone (com ou sem fio), iluminação adequada e ambiente apropriado ao número de participantes;</li>
<li>Garantir a organização e receptividade do público participante;</li>
<li>Disponibilizar, com antecedência mínima de 5 (cinco) dias úteis, informações sobre o perfil do público, objetivos e contexto da palestra, para que a CONTRATADA possa adaptar o conteúdo quando necessário;</li>
<li>Comunicar com antecedência qualquer alteração de local, horário, público ou estrutura do evento;</li>
<li>Caso o evento seja presencial e exija deslocamento, arcar com os custos de transporte e hospedagem da CONTRATADA, conforme acordado em proposta.</li>
</ul>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula quarta – Honorários e forma de pagamento</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Pelos serviços de palestra objeto deste contrato, a CONTRATANTE pagará à CONTRATADA o valor total de <strong>{{total_reais}}</strong> (<em>{{total_extenso}}</em>), nas seguintes condições:</p>
<table style="width:100%;border-collapse:collapse;margin:10px 0;font-size:11px;color:#0f172a;">
<tr style="background:#f8fafc;"><th style="border:1px solid #e2e8f0;padding:8px;text-align:left;">Condição de pagamento</th><th style="border:1px solid #e2e8f0;padding:8px;text-align:left;">Detalhe</th></tr>
<tr><td style="border:1px solid #e2e8f0;padding:8px;vertical-align:top;">Condições acordadas (texto comercial)</td><td style="border:1px solid #e2e8f0;padding:8px;">{{forma_pagamento}}</td></tr>
</table>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">§ 1º O pagamento será realizado por transferência bancária ou Pix para os seguintes dados: e-mail: {{empresa_email}} | CNPJ: {{empresa_cnpj}}.</p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">§ 2º Em caso de atraso no pagamento, o valor devido será corrigido pelo índice IGPM, acrescido de multa de 2% e juros de 1% ao mês, sem prejuízo de outras medidas cabíveis.</p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">§ 3º O não pagamento do sinal (quando aplicável) até a data acordada autoriza a CONTRATADA a não reservar a data na agenda, ficando a vaga disponível para outros clientes.</p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">§ 4º Os custos de deslocamento (transporte, hospedagem e alimentação), quando aplicáveis, serão custeados pela CONTRATANTE e não estão incluídos no valor dos honorários acima.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula quinta – Cancelamento e remarcação</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Em caso de cancelamento do evento pela CONTRATANTE, serão aplicadas as seguintes condições:</p>
<table style="width:100%;border-collapse:collapse;margin:10px 0;font-size:10px;color:#0f172a;">
<tr style="background:#f8fafc;"><th style="border:1px solid #e2e8f0;padding:6px;text-align:left;">Prazo de cancelamento</th><th style="border:1px solid #e2e8f0;padding:6px;text-align:left;">Condição</th><th style="border:1px solid #e2e8f0;padding:6px;text-align:center;">Multa</th><th style="border:1px solid #e2e8f0;padding:6px;text-align:left;">Reembolso do sinal</th></tr>
<tr><td style="border:1px solid #e2e8f0;padding:6px;">Acima de 30 dias de antecedência</td><td style="border:1px solid #e2e8f0;padding:6px;">Cancelamento sem penalidade</td><td style="border:1px solid #e2e8f0;padding:6px;text-align:center;">0%</td><td style="border:1px solid #e2e8f0;padding:6px;">Reembolso total do sinal</td></tr>
<tr><td style="border:1px solid #e2e8f0;padding:6px;">Entre 20 e 30 dias de antecedência</td><td style="border:1px solid #e2e8f0;padding:6px;">Multa moderada</td><td style="border:1px solid #e2e8f0;padding:6px;text-align:center;">30%</td><td style="border:1px solid #e2e8f0;padding:6px;">Reembolso de 70% do sinal</td></tr>
<tr><td style="border:1px solid #e2e8f0;padding:6px;">Entre 8 e 19 dias de antecedência</td><td style="border:1px solid #e2e8f0;padding:6px;">Multa elevada</td><td style="border:1px solid #e2e8f0;padding:6px;text-align:center;">50%</td><td style="border:1px solid #e2e8f0;padding:6px;">Reembolso de 50% do sinal</td></tr>
<tr><td style="border:1px solid #e2e8f0;padding:6px;">Menos de 7 dias de antecedência</td><td style="border:1px solid #e2e8f0;padding:6px;">Multa máxima</td><td style="border:1px solid #e2e8f0;padding:6px;text-align:center;">100%</td><td style="border:1px solid #e2e8f0;padding:6px;">Sem reembolso</td></tr>
</table>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">§ 1º A remarcação da palestra poderá ocorrer mediante acordo entre as partes e disponibilidade de agenda da CONTRATADA, sem cobrança de multa, desde que solicitada com no mínimo 30 (trinta) dias de antecedência.</p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">§ 2º Em caso de cancelamento por motivo de força maior devidamente comprovado (doença, acidente, calamidade pública, etc.), as partes negociarão de boa-fé a remarcação sem aplicação de multa.</p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">§ 3º Todo cancelamento ou remarcação deverá ser formalizado por escrito (e-mail ou mensagem com confirmação de recebimento), não sendo aceitas solicitações verbais.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula sexta – Impossibilidade de realização pela contratada</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Na hipótese de caso fortuito ou motivo de força maior que impeça a realização da palestra pela CONTRATADA (problemas de saúde, acidentes, restrições de deslocamento, situações emergenciais ou determinações de autoridades públicas), a CONTRATADA deverá:</p>
<ul style="margin:6px 0 12px;padding-left:18px;font-size:11px;line-height:1.5;color:#0f172a;">
<li>Comunicar a CONTRATANTE com a maior brevidade possível, por escrito;</li>
<li>Fará esforços para remarcar a data conforme disponibilidade mútua;</li>
<li>Caso a remarcação não seja possível, restituir integralmente os valores já pagos pela CONTRATANTE no prazo de até 10 (dez) dias úteis.</li>
</ul>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">§ Único: A CONTRATADA poderá, mediante prévia concordância da CONTRATANTE, indicar outro profissional de nível equivalente para a realização da palestra, caso o prazo não permita remarcação.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula sétima – Direito de imagem e registro audiovisual</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Ambas as partes poderão utilizar imagens, fotos e gravações da palestra para fins institucionais e promocionais, desde que:</p>
<ul style="margin:6px 0 12px;padding-left:18px;font-size:11px;line-height:1.5;color:#0f172a;">
<li>Não haja alteração que prejudique a imagem ou reputação de qualquer das partes;</li>
<li>O uso seja limitado a finalidades institucionais, educacionais ou de divulgação;</li>
<li>Não haja monetização direta do conteúdo sem prévia autorização escrita da outra parte.</li>
</ul>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">§ 1º É de responsabilidade exclusiva da CONTRATANTE obter as autorizações de uso de imagem dos participantes do evento, isentando a CONTRATADA de qualquer responsabilidade decorrente de eventuais reivindicações.</p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">§ 2º O conteúdo, metodologia e material didático apresentados pela CONTRATADA são de sua propriedade intelectual exclusiva, sendo vedada a cópia, reprodução ou comercialização sem autorização expressa.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula oitava – Propriedade intelectual</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Todo o conteúdo, metodologia, apresentações, materiais didáticos e dinâmicas utilizados pela CONTRATADA durante a palestra são de sua exclusiva propriedade intelectual, protegidos pela Lei nº 9.610/98.</p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">§ Único: É vedada à CONTRATANTE a reprodução, distribuição, comercialização ou utilização do conteúdo para a realização de novos treinamentos ou palestras, sem autorização expressa e por escrito da CONTRATADA.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula nona – Formalização das comunicações</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Toda e qualquer alteração de data, local, horário, tema, cancelamento ou remarcação deverá ser formalizada por escrito (e-mail ou aditivo contratual assinado) para ter validade contratual.</p>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Comunicações realizadas exclusivamente por aplicativos de mensagens (WhatsApp, Telegram, etc.) não constituem alteração ou rescisão do contrato, salvo quando confirmadas por e-mail por ambas as partes.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula décima – Ausência de vínculo empregatício</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">O presente contrato não estabelece vínculo empregatício entre as partes, tratando-se exclusivamente de prestação de serviços autônomos, não gerando quaisquer obrigações de natureza trabalhista ou previdenciária.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula décima primeira – Confidencialidade</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Ambas as partes se comprometem a manter sigilo sobre todas as informações estratégicas, comerciais ou operacionais obtidas em decorrência deste contrato, pelo prazo de 3 (três) anos após seu encerramento.</p>

<h2 style="font-size:12px;color:#4a2070;margin:18px 0 8px;text-transform:uppercase;letter-spacing:0.06em;">Cláusula décima segunda – Foro</h2>
<p style="font-size:11px;line-height:1.55;color:#0f172a;">Fica eleito o foro da comarca de {{foro_comarca}} para dirimir quaisquer controvérsias decorrentes deste contrato, com renúncia a qualquer outro, por mais privilegiado que seja.</p>

<p style="font-size:10px;line-height:1.45;color:#64748b;margin-top:14px;">Referência comercial: {{vendedor_nome}} ({{vendedor_email}}). Comissão: {{comissao_percent}}% ({{comissao_reais}}). Observações da proposta: {{proposta_observacoes}}</p>

<table style="width:100%;margin-top:28px;font-size:11px;color:#0f172a;border-collapse:collapse;">
<tr><td style="width:50%;vertical-align:top;padding:8px;">
<p style="margin:0 0 6px;"><strong>CONTRATANTE</strong></p>
<p style="margin:0;">{{cliente_representante}}</p>
<p style="margin:6px 0 0;">{{cliente_representante_cargo}} – CNPJ: {{cliente_cnpj}}</p>
</td><td style="width:50%;vertical-align:top;padding:8px;">
<p style="margin:0 0 6px;"><strong>CONTRATADA</strong></p>
<p style="margin:0;">{{empresa_signatario_nome}}</p>
<p style="margin:6px 0 0;">{{empresa_nome}} – CNPJ {{empresa_cnpj}}</p>
<p style="margin:6px 0 0;font-size:10px;color:#64748b;">CPF (representante): {{empresa_signatario_cpf}}</p>
</td></tr>
</table>

<p style="font-size:11px;line-height:1.55;color:#0f172a;margin-top:24px;text-align:center;">{{cidade_assinatura_curta}}, {{data_hoje_por_extenso}}.</p>

<table style="width:100%;margin-top:24px;font-size:11px;color:#0f172a;border-collapse:collapse;">
<tr><td colspan="2" style="padding-bottom:8px;"><strong>TESTEMUNHAS:</strong></td></tr>
<tr><td style="width:50%;vertical-align:top;padding:8px;border-top:1px dashed #cbd5e1;">
<p style="margin:0;">Nome: _________________________________</p>
<p style="margin:8px 0 0;">CPF: ___________________________________</p>
</td><td style="width:50%;vertical-align:top;padding:8px;border-top:1px dashed #cbd5e1;">
<p style="margin:0;">Nome: _________________________________</p>
<p style="margin:8px 0 0;">CPF: ___________________________________</p>
</td></tr>
</table>
HTML;
    }
}
