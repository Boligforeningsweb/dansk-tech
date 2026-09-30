#!/usr/bin/env python3
"""
Genererer og-image.png (1200x630) til dansktechstack.dk.

Billedet er en mosaik af iværksætterne bag projektet, GitHub-bidragydere
og logoer fra products.json. Kør igen når der er kommet nye produkter
eller bidragydere, så tallene og ansigterne er opdaterede.

Kræver: Python 3, Pillow (pip install pillow) og Google Chrome.

    python3 scripts/og-image/build.py            # rød variant (default)
    python3 scripts/og-image/build.py --theme dark
"""
import argparse
import hashlib
import io
import json
import random
import re
import subprocess
import urllib.parse
import urllib.request
from pathlib import Path

from PIL import Image

HERE = Path(__file__).resolve().parent
ROOT = HERE.parent.parent
CACHE = HERE / ".cache"
REPO = "Boligforeningsweb/dansk-tech"
CHROME = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"


def fetch(url):
    req = urllib.request.Request(url, headers={"User-Agent": "dansktechstack-og"})
    with urllib.request.urlopen(req, timeout=20) as r:
        return r.read()


def cached(url, name):
    CACHE.mkdir(exist_ok=True)
    path = CACHE / name
    if not path.exists():
        path.write_bytes(fetch(url))
    return path


def is_identicon(path):
    # GitHubs standard-avatarer består af to farver
    im = Image.open(path).convert("RGB")
    colors = sorted(im.getcolors(im.width * im.height), reverse=True)[:2]
    return sum(c for c, _ in colors) / (im.width * im.height) > 0.97


def backers():
    # Iværksætterne fra "Tusind tak"-sektionen i index.php
    html = (ROOT / "index.php").read_text()
    section = html.split('id="iværksættere"')[1].split("</ul>")[0]
    return [ROOT / src for src in re.findall(r'<img src="([^"]+)"', section)]


def contributors():
    data = json.loads(fetch(f"https://api.github.com/repos/{REPO}/contributors?per_page=100"))
    faces = []
    for c in data:
        path = cached(c["avatar_url"] + "&s=200", f"gh-{c['login']}.img")
        if not is_identicon(path):
            faces.append(path)
    return len(data), faces


def is_logo(path):
    # Flere billeder i images/ er screenshots eller brede wordmarks - dem vil vi ikke have med
    try:
        w, h = Image.open(path).size
    except Exception:
        return False
    return 96 <= w <= 1100 and 0.8 <= w / h <= 1.25


def logos(products):
    result = []
    for p in products:
        domain = re.sub(r"^www\.", "", urllib.parse.urlparse(p["url"]).hostname or "")
        try:
            path = cached(f"https://www.google.com/s2/favicons?domain={domain}&sz=256",
                          "fav-" + hashlib.md5(domain.encode()).hexdigest() + ".png")
            if is_logo(path):
                result.append(path)
                continue
        except Exception:
            pass
        if p.get("image") and is_logo(ROOT / p["image"]):
            result.append(ROOT / p["image"])
    return result


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--theme", choices=["red", "dark"], default="red")
    ap.add_argument("--out", default=str(ROOT / "og-image.png"))
    args = ap.parse_args()

    products = json.loads((ROOT / "products.json").read_text())
    backer_faces = backers()
    contributor_count, contributor_faces = contributors()
    logo_files = logos(products)

    rnd = random.Random(42)
    tiles = [("face", f) for f in backer_faces + contributor_faces] + [("logo", f) for f in logo_files]
    rnd.shuffle(tiles)
    cols, rows = 11, 10
    tiles = (tiles * 3)[: cols * rows]

    tile_html = "\n".join(
        f'<div class="tile {kind}"><img src="{path.as_uri()}"></div>' for kind, path in tiles
    )
    stack_html = "\n".join(f'<img src="{f.as_uri()}">' for f in backer_faces[:6])

    template = (HERE / "template.html").read_text()
    html = (template
            .replace("{{THEME}}", args.theme)
            .replace("{{TILES}}", tile_html)
            .replace("{{STACK}}", stack_html)
            .replace("{{PRODUCTS}}", str(len(products)))
            .replace("{{BACKERS}}", str(len(backer_faces)))
            .replace("{{CONTRIBUTORS}}", str(contributor_count)))
    page = CACHE / "og.html"
    page.write_text(html)

    shot = CACHE / "shot.png"
    subprocess.run([CHROME, "--headless=new", "--disable-gpu", "--hide-scrollbars",
                    "--allow-file-access-from-files", "--force-device-scale-factor=2",
                    "--window-size=1200,630", "--virtual-time-budget=10000",
                    f"--screenshot={shot}", page.as_uri()],
                   check=True, capture_output=True)

    # Render i 2x og skaler ned for skarpere kanter; gem som optimeret PNG
    im = Image.open(shot).convert("RGB").resize((1200, 630), Image.LANCZOS)
    buf = io.BytesIO()
    im.quantize(colors=256, method=Image.Quantize.MEDIANCUT, dither=Image.Dither.FLOYDSTEINBERG).save(buf, "PNG", optimize=True)
    Path(args.out).write_bytes(buf.getvalue())
    print(f"{args.out}: {len(products)} produkter, {len(backer_faces)} iværksættere, "
          f"{contributor_count} bidragydere, {len(buf.getvalue()) // 1024} KB")


if __name__ == "__main__":
    main()
