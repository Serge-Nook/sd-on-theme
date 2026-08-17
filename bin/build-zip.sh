#!/usr/bin/env bash
# Собирает ZIP-архив темы для установки через админку WordPress.
set -euo pipefail

repo_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
slug="sd-on-theme"
version="$(grep -m1 '^Version:' "${repo_dir}/style.css" | sed 's/Version:[[:space:]]*//' | tr -d '\r')"
build_dir="${repo_dir}/build"
archive="${build_dir}/${slug}-${version}.zip"

rm -rf "${build_dir}"
mkdir -p "${build_dir}/${slug}"

git -C "${repo_dir}" archive HEAD | tar -x -C "${build_dir}/${slug}"

# Файлы разработки в пакет не попадают.
rm -f "${build_dir}/${slug}/phpcs.xml.dist" "${build_dir}/${slug}/.gitignore"
rm -rf "${build_dir}/${slug}/bin"

( cd "${build_dir}" && zip -qr "${archive}" "${slug}" )

echo "${archive}"
