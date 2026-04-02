<?php

namespace Database\Seeders;

/**
 * HSE Management Standards Indicator Tool — 7 dimensões, 35 itens.
 * Escala frequência (1–5) nos itens 1–23; concordância (1–5) nos itens 24–35, conforme manual HSE.
 */
class HseItQuestionData
{
    /**
     * @return array<int, array{title: string, description: string|null, questions: list<array{body: string, reverse_score: bool, response_scale: string}>}>
     */
    public static function blocks(): array
    {
        return [
            [
                'title' => 'Demandas',
                'description' => 'Carga, ritmo e exigências (HSE: Demands).',
                'questions' => [
                    ['body' => 'Diferentes grupos no trabalho exigem de mim coisas difíceis de conciliar.', 'reverse_score' => true, 'response_scale' => 'frequency'],
                    ['body' => 'Tenho prazos inatingíveis.', 'reverse_score' => true, 'response_scale' => 'frequency'],
                    ['body' => 'Tenho que trabalhar de forma muito intensa.', 'reverse_score' => true, 'response_scale' => 'frequency'],
                    ['body' => 'Tenho de deixar de lado algumas tarefas porque tenho demasiado a fazer.', 'reverse_score' => true, 'response_scale' => 'frequency'],
                    ['body' => 'Não consigo fazer pausas suficientes.', 'reverse_score' => true, 'response_scale' => 'frequency'],
                    ['body' => 'Sou pressionado(a) a trabalhar horas extras.', 'reverse_score' => true, 'response_scale' => 'frequency'],
                    ['body' => 'Tenho que trabalhar muito depressa.', 'reverse_score' => true, 'response_scale' => 'frequency'],
                    ['body' => 'Tenho pressões de tempo irreais.', 'reverse_score' => true, 'response_scale' => 'frequency'],
                ],
            ],
            [
                'title' => 'Controle',
                'description' => 'Autonomia e influência sobre o trabalho (HSE: Control).',
                'questions' => [
                    ['body' => 'Tenho liberdade para decidir o que faço no trabalho.', 'reverse_score' => false, 'response_scale' => 'frequency'],
                    ['body' => 'Tenho influência sobre a velocidade do meu trabalho.', 'reverse_score' => false, 'response_scale' => 'frequency'],
                    ['body' => 'Tenho liberdade para decidir como faço o meu trabalho.', 'reverse_score' => false, 'response_scale' => 'frequency'],
                    ['body' => 'Tenho liberdade para decidir o que faço no trabalho?', 'reverse_score' => false, 'response_scale' => 'frequency'],
                    ['body' => 'Tenho alguma influência sobre a forma como trabalho.', 'reverse_score' => false, 'response_scale' => 'agreement'],
                    ['body' => 'O meu horário de trabalho pode ser flexível.', 'reverse_score' => false, 'response_scale' => 'agreement'],
                ],
            ],
            [
                'title' => 'Apoio da gestão',
                'description' => 'Suporte do gestor imediato (HSE: Managerial support).',
                'questions' => [
                    ['body' => 'Recebo feedback de apoio sobre o trabalho que realizo.', 'reverse_score' => false, 'response_scale' => 'frequency'],
                    ['body' => 'Posso contar com o meu gestor imediato para me ajudar num problema de trabalho.', 'reverse_score' => false, 'response_scale' => 'frequency'],
                    ['body' => 'Posso falar com o meu gestor imediato sobre algo que me incomodou no trabalho.', 'reverse_score' => false, 'response_scale' => 'agreement'],
                    ['body' => 'Sou apoiado(a) em trabalho emocionalmente exigente.', 'reverse_score' => false, 'response_scale' => 'agreement'],
                    ['body' => 'O meu gestor imediato incentiva-me no trabalho.', 'reverse_score' => false, 'response_scale' => 'agreement'],
                ],
            ],
            [
                'title' => 'Apoio dos colegas',
                'description' => 'Suporte entre pares (HSE: Peer support).',
                'questions' => [
                    ['body' => 'Se o trabalho fica difícil, os meus colegas ajudam-me.', 'reverse_score' => false, 'response_scale' => 'frequency'],
                    ['body' => 'Recebo a ajuda e o apoio de que preciso dos colegas.', 'reverse_score' => false, 'response_scale' => 'agreement'],
                    ['body' => 'Recebo o respeito que mereço dos meus colegas no trabalho.', 'reverse_score' => false, 'response_scale' => 'agreement'],
                    ['body' => 'Os meus colegas estão dispostos a ouvir os meus problemas relacionados com o trabalho.', 'reverse_score' => false, 'response_scale' => 'agreement'],
                ],
            ],
            [
                'title' => 'Papel e função',
                'description' => 'Clareza de objetivos e responsabilidades (HSE: Role).',
                'questions' => [
                    ['body' => 'Tenho clareza do que se espera de mim no trabalho.', 'reverse_score' => false, 'response_scale' => 'frequency'],
                    ['body' => 'Sei como levar a cabo o meu trabalho.', 'reverse_score' => false, 'response_scale' => 'frequency'],
                    ['body' => 'Tenho clareza das minhas funções e responsabilidades.', 'reverse_score' => false, 'response_scale' => 'frequency'],
                    ['body' => 'Tenho clareza dos objetivos da minha área.', 'reverse_score' => false, 'response_scale' => 'frequency'],
                    ['body' => 'Compreendo como o meu trabalho se encaixa nos objetivos gerais da organização.', 'reverse_score' => false, 'response_scale' => 'frequency'],
                ],
            ],
            [
                'title' => 'Relacionamentos',
                'description' => 'Conflito, assédio e clima entre pessoas (HSE: Relationships).',
                'questions' => [
                    ['body' => 'Sou alvo de assédio na forma de palavras ou comportamentos desagradáveis.', 'reverse_score' => true, 'response_scale' => 'frequency'],
                    ['body' => 'Há atrito ou irritação entre colegas.', 'reverse_score' => true, 'response_scale' => 'frequency'],
                    ['body' => 'Sou alvo de assédio moral no trabalho.', 'reverse_score' => true, 'response_scale' => 'frequency'],
                    ['body' => 'Os relacionamentos no trabalho são tensos.', 'reverse_score' => true, 'response_scale' => 'agreement'],
                ],
            ],
            [
                'title' => 'Mudança',
                'description' => 'Como a organização comunica e envolve nas mudanças (HSE: Change).',
                'questions' => [
                    ['body' => 'Tenho oportunidades suficientes para questionar os gestores sobre mudanças no trabalho.', 'reverse_score' => false, 'response_scale' => 'agreement'],
                    ['body' => 'As pessoas são consultadas sobre mudanças no trabalho.', 'reverse_score' => false, 'response_scale' => 'agreement'],
                    ['body' => 'Quando há mudanças no trabalho, tenho clareza de como funcionarão na prática.', 'reverse_score' => false, 'response_scale' => 'agreement'],
                ],
            ],
        ];
    }
}
