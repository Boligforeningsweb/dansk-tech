#!/usr/bin/env bash
# Bygger assets/app.css med Tailwinds standalone-CLI (kræver ikke Node).
# Kør efter ændringer i PHP-skabelonerne:  ./tools/tailwind/build.sh
set -euo pipefail

VERSION="3.4.17"
DIR="$(cd "$(dirname "$0")" && pwd)"
ROOT="$(cd "$DIR/../.." && pwd)"
BIN="$DIR/.bin/tailwindcss-$VERSION"

if [ ! -x "$BIN" ]; then
  case "$(uname -s)-$(uname -m)" in
    Darwin-arm64)  PLATFORM="macos-arm64" ;;
    Darwin-x86_64) PLATFORM="macos-x64" ;;
    Linux-x86_64)  PLATFORM="linux-x64" ;;
    Linux-aarch64) PLATFORM="linux-arm64" ;;
    *) echo "Ukendt platform: $(uname -s)-$(uname -m)" >&2; exit 1 ;;
  esac
  mkdir -p "$DIR/.bin"
  curl -sSLf -o "$BIN" "https://github.com/tailwindlabs/tailwindcss/releases/download/v$VERSION/tailwindcss-$PLATFORM"
  chmod +x "$BIN"
fi

cd "$ROOT"
"$BIN" -c tools/tailwind/tailwind.config.js -i tools/tailwind/input.css -o assets/app.css --minify
