from __future__ import annotations

import json
import os
import subprocess
import sys
from datetime import date
from pathlib import Path

import fitz

ROOT = Path(__file__).resolve().parents[1]


def _make_sample_pdf(target: Path) -> None:
    doc = fitz.open()
    page = doc.new_page()
    text = """ESPelho de Ponto
Nome: Fulano de Tal
Empresa: ACME

14/04/2026  Entrada 08:00  Saida 12:00  Entrada 13:00  Saida 18:00
15/04/2026  Falta justificada
"""
    page.insert_text((72, 72), text, fontsize=11)
    doc.save(target)
    doc.close()


def test_parse_groups_by_brazilian_date(tmp_path: Path) -> None:
    pdf = tmp_path / "sample.pdf"
    _make_sample_pdf(pdf)

    from rhid_espelho_parser.extract import parse_espelho_pdf

    out = parse_espelho_pdf(
        pdf,
        id_person=99,
        period_ini=date(2026, 4, 1),
        period_fim=date(2026, 4, 30),
    )
    assert out["schema_version"] == 1
    assert out["id_person"] == 99
    assert len(out["days"]) >= 2
    dates = {d["date"] for d in out["days"]}
    assert "2026-04-14" in dates
    assert "2026-04-15" in dates


def test_cli_stdout_json(tmp_path: Path) -> None:
    pdf = tmp_path / "cli.pdf"
    _make_sample_pdf(pdf)
    env = {**os.environ, "PYTHONPATH": str(ROOT)}
    r = subprocess.run(
        [
            sys.executable,
            "-m",
            "rhid_espelho_parser",
            str(pdf),
            "--id-person",
            "1",
            "--period-ini",
            "2026-04-01",
            "--period-fim",
            "2026-04-30",
        ],
        cwd=str(ROOT),
        env=env,
        capture_output=True,
        text=True,
        check=True,
    )
    data = json.loads(r.stdout)
    assert data["schema_version"] == 1
    assert data["id_person"] == 1
    assert "marcacoes_espelho" in data
    assert "marcacoes_espelho_count" in data


def test_preencher_campos_marcacao_espelho_columns() -> None:
    from rhid_espelho_parser.marcacoes_espelho import _preencher_campos_marcacao_pdf_linha

    row = {
        "TODAS_MARCACOES": "07:00 12:00",
        "DIA_DA_SEMANA": "SEG",
        "Data": "01/03/2026",
    }
    data, dia, ent, sai, todas = _preencher_campos_marcacao_pdf_linha(row)
    assert data == "01/03/2026"
    assert dia == "SEG"
    assert todas == "07:00 12:00"
    row2 = {"Marcacoes (espelho)": "08:15 17:30", "Data": "14/04/2026"}
    d2, _, _, _, t2 = _preencher_campos_marcacao_pdf_linha(row2)
    assert d2 == "14/04/2026"
    assert t2 == "08:15 17:30"
