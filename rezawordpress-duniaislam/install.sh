#!/usr/bin/env bash
set -euo pipefail
umask 077

site=/home/rezaervani/utama/duniaislam
expected_home=https://rezaervani.com/duniaislam
repo=https://github.com/rezaervani/rezawordpress.git
theme=reza-ervani-institute
ref=main
activate=false

usage() {
    printf 'Penggunaan: bash install.sh [--ref REF_GIT] [--activate]\n'
}

die() {
    printf '%s\n' "$*" >&2
    exit 1
}

while (( $# )); do
    case "$1" in
        --ref)
            (( $# >= 2 )) || die 'Isi --ref dengan ref Git.'
            ref=$2
            shift 2
            ;;
        --activate)
            activate=true
            shift
            ;;
        --help|-h)
            usage
            exit
            ;;
        *)
            usage >&2
            die "Opsi tidak dikenal: $1"
            ;;
    esac
done

[[ -f "$site/wp-load.php" && -d "$site/wp-content/themes" ]] ||
    die "Instalasi WordPress tidak ditemukan di $site."
wp_bin=$(command -v wp) || die 'WP-CLI (wp) perlu tersedia di PATH.'
command -v git >/dev/null || die 'Git perlu tersedia di PATH.'
owner=$(stat -Lc '%U' "$site/wp-content")
[[ "$owner" != root && "$owner" != UNKNOWN ]] ||
    die "Pemilik wp-content harus akun WordPress non-root yang aktif (terdeteksi: $owner)."

as_owner() {
    if [[ "$(id -un)" == "$owner" ]]; then
        "$@"
    else
        sudo -u "$owner" -- "$@"
    fi
}

wp_site() {
    as_owner "$wp_bin" --path="$site" "$@"
}

home=$(wp_site option get home)
[[ "${home%/}" == "$expected_home" ]] || die "Alamat WordPress tidak cocok: $home"
previous_theme=$(wp_site option get stylesheet)

checkout=$(mktemp -d)
owner_package=
cleanup() {
    if [[ -n "$owner_package" ]]; then
        as_owner rm -rf -- "$owner_package" || true
    fi
    rm -rf -- "$checkout"
}
trap cleanup EXIT

git -C "$checkout" init -q
git -C "$checkout" fetch --depth=1 -- "$repo" "$ref"
git -C "$checkout" cat-file -e "FETCH_HEAD:rezawordpress-duniaislam/$theme/style.css"
git -C "$checkout" archive --format=zip --output="$checkout/$theme.zip" \
    FETCH_HEAD:rezawordpress-duniaislam "$theme"

install_flags=()
if wp_site theme is-installed "$theme"; then
    backup=$(mktemp --suffix=.tar.gz "$HOME/tema-duniaislam-$(date +%Y%m%d-%H%M%S)-XXXXXX")
    as_owner tar -C "$site/wp-content/themes" -czf - "$theme" >"$backup"
    printf 'Cadangan tema: %s\n' "$backup"
    install_flags+=(--force)
fi

owner_package=$(as_owner mktemp -d)
as_owner tee "$owner_package/$theme.zip" <"$checkout/$theme.zip" >/dev/null
wp_site theme install "$owner_package/$theme.zip" "${install_flags[@]}"

if $activate && [[ "$previous_theme" != "$theme" ]]; then
    printf 'Tema sebelumnya: %s\n' "$previous_theme"
    wp_site theme activate "$theme"
fi
wp_site theme status "$theme"
