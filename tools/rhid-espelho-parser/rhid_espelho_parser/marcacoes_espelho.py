"""
Marcações (espelho) RHID — mesma abordagem do TALENTS6 (cartao_pdf_parser):
pdfplumber extrai tabelas; linhas viram dicts pelo cabeçalho; colunas DIA_DA_SEMANA,
TODAS_MARCACOES, Marcacoes (espelho), Data, Entrada/Saída, etc.
"""
from __future__ import annotations

import io
import re
from typing import Any

try:
    import pdfplumber

    HAS_PDFPLUMBER = True
except ImportError:
    HAS_PDFPLUMBER = False

_RE_DATE_FLEX = re.compile(r"(\d{1,2}/\d{1,2}/\d{2,4})")

_DIAS_SEMANA = {
    "seg",
    "ter",
    "qua",
    "qui",
    "sex",
    "sáb",
    "sab",
    "dom",
    "segunda",
    "terça",
    "terca",
    "quarta",
    "quinta",
    "sexta",
    "sábado",
    "sabado",
    "domingo",
    "mon",
    "tue",
    "wed",
    "thu",
    "fri",
    "sat",
    "sun",
}


def _normalizar_data(s: str) -> str | None:
    """Converte 1/1/26 em 01/01/2026, retorna None se inválido."""
    if not s or not re.search(r"\d", s):
        return None
    m = _RE_DATE_FLEX.search(s)
    if not m:
        return None
    parts = m.group(1).split("/")
    if len(parts) != 3:
        return None
    d, M, y = parts[0].zfill(2), parts[1].zfill(2), parts[2]
    if len(y) == 2:
        y = "20" + y if int(y) < 50 else "19" + y
    return f"{d}/{M}/{y}"


def _tabela_para_dicts(linhas: list[list[str]]) -> tuple[list[str] | None, list[dict[str, str]]]:
    if not linhas or len(linhas) < 2:
        return None, []
    cabecalho = [str(c or "").strip() for c in linhas[0]]
    dados = []
    for row in linhas[1:]:
        d: dict[str, str] = {}
        for i, val in enumerate(row):
            key = cabecalho[i] if i < len(cabecalho) else f"col_{i}"
            d[key] = str(val or "").strip()
        dados.append(d)
    return cabecalho, dados


def _preencher_campos_marcacao_pdf_linha(row: dict[str, Any]) -> tuple[str, str, str, str, str]:
    """
    Lê data, dia da semana, entradas, saídas e coluna agregada de horários.
    Espelho RHID: DIA_DA_SEMANA, TODAS_MARCACOES; PDF pode trazer \"Marcacoes (espelho)\".
    """
    data = (
        row.get("data")
        or row.get("Data")
        or row.get("Data ")
        or ""
    )
    dia_sem = (
        row.get("dia")
        or row.get("Dia")
        or row.get("Dia da Semana")
        or row.get("Dia da semana")
        or row.get("DIA_DA_SEMANA")
        or row.get("dia_da_semana")
        or ""
    )
    entradas = row.get("entrada") or row.get("Entrada") or row.get("Entradas") or ""
    saidas = row.get("saída") or row.get("Saída") or row.get("Saídas") or row.get("Saida") or ""
    todas = (
        row.get("marcações")
        or row.get("Marcações")
        or row.get("marcacoes")
        or row.get("Marcacoes")
        or row.get("TODAS_MARCACOES")
        or row.get("todas_marcacoes")
        or row.get("Todas_marcacoes")
        or row.get("Marcacoes (espelho)")
        or row.get("Marcações (espelho)")
        or row.get("MARCACOES_ESPELHO")
        or ""
    )

    for key, raw in row.items():
        ks = str(key or "").strip()
        if not ks:
            continue
        kl = ks.lower()
        v = str(raw or "").strip()
        if not v:
            continue
        if "duração" in kl or "duracao" in kl:
            continue
        if "admissão" in kl or "admissao" in kl:
            continue

        if not todas:
            if "todas_marcacoes" in kl or "todas marcacoes" in kl or "todas marcações" in kl:
                todas = v
                continue
            if "espelho" in kl and ("marc" in kl or "marca" in kl):
                todas = v
                continue
            if kl in ("marcações", "marcacoes", "marcação", "marcacao"):
                todas = v
                continue

        if not data and "data" in kl and "admissão" not in kl and "admissao" not in kl:
            data = v
            continue
        if not dia_sem and (
            "dia_da_semana" in kl
            or kl in ("dia da semana", "dia semana")
            or (kl == "dia" and v.split() and v.split()[0].lower() in _DIAS_SEMANA)
        ):
            dia_sem = v
            continue
        if not entradas and "entrada" in kl and "saída" not in kl and "saida" not in kl:
            entradas = v
            continue
        if not saidas and ("saída" in kl or "saida" in kl):
            saidas = v
            continue
        if not data and kl == "dia" and _normalizar_data(v):
            data = v

    return (data, dia_sem, entradas, saidas, todas)


def extrair_marcacoes_do_espelho_pdf(pdf_bytes: bytes) -> list[dict[str, Any]]:
    """
    Extrai linhas de marcação a partir das tabelas do PDF (igual fluxo TALENTS6 / ler_cartao_ponto_pdf
    + extrair_marcacoes_do_cartao / extrair_marcacoes_do_espelho).

    Retorna lista de dicts com: data, dia_semana, entradas, saidas, todas_marcacoes, pagina, row.
    """
    if not HAS_PDFPLUMBER:
        raise RuntimeError("pdfplumber não instalado. pip install pdfplumber")

    marcacoes: list[dict[str, Any]] = []

    with pdfplumber.open(io.BytesIO(pdf_bytes)) as pdf:
        for i, page in enumerate(pdf.pages):
            num_pagina = i + 1
            for table in page.extract_tables() or []:
                if not table:
                    continue
                linhas_limpas = [[str(c or "").strip() for c in (row or [])] for row in table]
                cabecalho, dados = _tabela_para_dicts(linhas_limpas)
                if not cabecalho:
                    continue
                for row in dados:
                    data, dia, entradas, saidas, todas = _preencher_campos_marcacao_pdf_linha(row)
                    if data or todas or entradas or saidas:
                        marcacoes.append(
                            {
                                "data": data,
                                "dia_semana": dia,
                                "entradas": entradas,
                                "saidas": saidas,
                                "todas_marcacoes": todas,
                                "pagina": num_pagina,
                                "row": row,
                            }
                        )
    return marcacoes


def extrair_marcacoes_do_espelho(pdf_bytes: bytes) -> list[dict[str, Any]]:
    """Espelho do nome TALENTS6 (`extrair_marcacoes_do_espelho`); entrada aqui é o PDF em bytes."""
    return extrair_marcacoes_do_espelho_pdf(pdf_bytes)

