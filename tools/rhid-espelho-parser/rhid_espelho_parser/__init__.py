"""Extracao heuristica de texto do espelho de ponto RHID (PDF)."""

from .marcacoes_espelho import (
    extrair_marcacoes_do_espelho,
    extrair_marcacoes_do_espelho_pdf,
)

__all__ = [
    "extrair_marcacoes_do_espelho",
    "extrair_marcacoes_do_espelho_pdf",
]
__version__ = "1.1.0"
