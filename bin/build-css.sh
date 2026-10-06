#!/usr/bin/env bash
# Build the committed stylesheet with the Tailwind standalone CLI (no Node.js needed).
# Usage: bin/build-css.sh [--watch]
set -euo pipefail
cd "$(dirname "$0")/.."
TW=bin/tailwindcss
if [ ! -x "$TW" ]; then
  case "$(uname -s)-$(uname -m)" in
    Linux-x86_64) asset=tailwindcss-linux-x64 ;;
    Linux-aarch64) asset=tailwindcss-linux-arm64 ;;
    Darwin-arm64) asset=tailwindcss-macos-arm64 ;;
    Darwin-x86_64) asset=tailwindcss-macos-x64 ;;
    *) echo "Unsupported platform; download Tailwind standalone CLI manually to $TW"; exit 1 ;;
  esac
  echo "Downloading Tailwind standalone CLI ($asset)..."
  curl -sSL -o "$TW" "https://github.com/tailwindlabs/tailwindcss/releases/download/v4.3.3/$asset"
  chmod +x "$TW"
fi
"$TW" -i resources/css/app.css -o public/css/app.css --minify "$@"
