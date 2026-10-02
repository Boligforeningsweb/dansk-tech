#!/usr/bin/env python3
"""
Laver data/badge-font.json: glyffer (SVG-stier), bredder og kerning fra Inter Medium og Bold,
så lib/badges.php kan tegne badge-teksten som vektorer med præcis bredde.

Kør kun igen, hvis tegnsættet eller skriften skal ændres:
    pip install fonttools uharfbuzz
    python3 tools/badge/build-font.py /sti/til/Inter-Medium.ttf /sti/til/Inter-Bold.ttf

Inter er udgivet under SIL Open Font License 1.1 (se tools/badge/Inter-LICENSE.txt).
"""
import json
import sys
from itertools import product
from pathlib import Path

import uharfbuzz as hb
from fontTools.pens.boundsPen import BoundsPen
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
from fontTools.pens.roundingPen import RoundingPen
from fontTools.ttLib import TTFont

ROOT = Path(__file__).resolve().parents[2]
CHARS = (" abcdefghijklmnopqrstuvwxyzæøåäöüéèáàíóúñç"
         "ABCDEFGHIJKLMNOPQRSTUVWXYZÆØÅÄÖÜÉ"
         "0123456789.,:;!?&+-–—·…/()'’\"@#%*_")


def build(path):
    tt = TTFont(path)
    cmap = tt.getBestCmap()
    glyphset = tt.getGlyphSet()
    blob = hb.Blob.from_file_path(path)
    font = hb.Font(hb.Face(blob))

    glyphs = {}
    for ch in CHARS:
        name = cmap.get(ord(ch))
        if not name:
            continue
        pen = SVGPathPen(glyphset, ntos=lambda v: str(int(round(v))))
        glyphset[name].draw(RoundingPen(pen))
        bounds = BoundsPen(glyphset)
        glyphset[name].draw(bounds)
        # xmax: højre blækkant, så badgets højre margin kan måles fra det synlige bogstav
        xmax = int(round(bounds.bounds[2])) if bounds.bounds else glyphset[name].width
        glyphs[ch] = {"adv": glyphset[name].width, "xmax": xmax, "d": pen.getCommands()}

    # Kerning: forskellen mellem harfbuzz' placering af et par og summen af enkelt-bredderne
    kern = {}
    for a, b in product(glyphs, repeat=2):
        buf = hb.Buffer()
        buf.add_str(a + b)
        buf.guess_segment_properties()
        hb.shape(font, buf, {"kern": True, "liga": False, "calt": False})
        pos = buf.glyph_positions
        if len(pos) == 2:
            diff = pos[0].x_advance - glyphs[a]["adv"]
            if diff:
                kern[a + b] = diff
    return tt["head"].unitsPerEm, {"glyphs": glyphs, "kern": kern}


def main():
    medium, bold = sys.argv[1:3]
    upm, w500 = build(medium)
    _, w700 = build(bold)
    out = {"unitsPerEm": upm, "source": "Inter 4.1 (SIL OFL 1.1)", "weights": {"500": w500, "700": w700}}
    path = ROOT / "data" / "badge-font.json"
    path.write_text(json.dumps(out, ensure_ascii=False, separators=(",", ":")))
    print(f"{path}: {len(w500['glyphs'])} glyffer, {len(w500['kern'])}+{len(w700['kern'])} kerning-par, "
          f"{path.stat().st_size // 1024} KB")


if __name__ == "__main__":
    main()
