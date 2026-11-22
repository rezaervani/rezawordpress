#!/usr/bin/env bash
set -euo pipefail
umask 077

root=/home/rezaervani/utama
domain=https://rezaervani.com
repo=https://github.com/rezaervani/rezawordpress.git
theme=reza-ervani-institute
ref=main
sites=(utama palestina duniaislam)

usage() {
    printf 'Penggunaan: bash upgrade-tema.sh [--ref REF_GIT]\n'
}

die() {
    printf '%s\n' "$*" >&2
    exit 1
}

case "${1:-}" in
    '') ;;
    --ref)
        [[ $# -eq 2 && -n "$2" ]] || die 'Isi --ref dengan satu ref Git.'
        ref=$2
        ;;
    --help|-h) usage; exit 0 ;;
    *) usage >&2; exit 1 ;;
esac

wp_bin=$(command -v wp) || die 'WP-CLI (wp) perlu tersedia di PATH.'
command -v git >/dev/null || die 'Git perlu tersedia di PATH.'
command -v flock >/dev/null || die 'flock perlu tersedia di PATH.'

exec 9>"$HOME/.reza-upgrade-tema.lock"
flock -n 9 || die 'Pembaruan tema lain sedang berjalan.'

declare -A paths owners target_versions active_themes package_dirs
operator=$(id -un)
checkout=$(mktemp -d)

as_owner() {
    local owner=$1
    shift
    if [[ "$owner" == "$operator" ]]; then
        "$@"
    else
        sudo -u "$owner" -- "$@"
    fi
}

wp_site() {
    local site=$1
    shift
    as_owner "${owners[$site]}" "$wp_bin" --path="${paths[$site]}" --skip-plugins --skip-themes "$@"
}

cleanup() {
    local site
    for site in "${!package_dirs[@]}"; do
        as_owner "${owners[$site]}" rm -rf -- "${package_dirs[$site]}" || true
    done
    rm -rf -- "$checkout"
}
trap cleanup EXIT

git -C "$checkout" init -q
git -C "$checkout" fetch --depth=1 -- "$repo" "$ref"
commit=$(git -C "$checkout" rev-parse 'FETCH_HEAD^{commit}')
printf 'Sumber pembaruan %s (%s)\n' "$ref" "$commit"

for site in "${sites[@]}"; do
    path=$root
    expected_home=$domain
    source_tree=$commit
    if [[ "$site" != utama ]]; then
        path="$root/$site"
        expected_home="$domain/$site"
        source_tree=$(git -C "$checkout" rev-parse "$commit:rezawordpress-$site") ||
            die "Folder tema $site tidak ditemukan pada $ref."
    fi
    paths[$site]=$path
    content="$path/wp-content"
    [[ -f "$path/wp-load.php" && -d "$content/themes/$theme" ]] ||
        die "WordPress atau tema $theme tidak ditemukan di $path."

    owner=$(stat -Lc '%U' "$content")
    [[ "$owner" != root && "$owner" != UNKNOWN ]] ||
        die "Pemilik wp-content untuk $site harus akun WordPress non-root."
    owners[$site]=$owner
    home=$(wp_site "$site" option get home) || die "Gagal memeriksa alamat WordPress $site."
    [[ "${home%/}" == "$expected_home" ]] || die "Alamat $site tidak cocok. Ditemukan $home."
    as_owner "$owner" test -w "$content/themes" || die "Akun $owner tidak dapat menulis ke folder tema $site."
    if [[ -d "$content/upgrade" ]]; then
        as_owner "$owner" test -w "$content/upgrade" || die "Akun $owner tidak dapat menulis ke wp-content/upgrade di $site."
    else
        as_owner "$owner" test -w "$content" || die "Akun $owner tidak dapat membuat wp-content/upgrade di $site."
    fi

    active_themes[$site]=$(wp_site "$site" option get stylesheet)
    current_version=$(wp_site "$site" theme get "$theme" --field=version) ||
        die "Versi tema $site tidak dapat dibaca."
    target_versions[$site]=$(git -C "$checkout" show "$source_tree:$theme/style.css" |
        awk -F ': *' '$1 == "Version" { sub(/\r$/, "", $2); version=$2 } END { print version }') ||
        die "Paket tema $site tidak ditemukan pada $ref."
    [[ -n "${target_versions[$site]}" ]] || die "Versi paket tema $site tidak ditemukan."
    git -C "$checkout" archive --format=zip --output="$checkout/$site.zip" "$source_tree" "$theme"
    package_dirs[$site]=$(as_owner "$owner" mktemp -d)
    as_owner "$owner" tee "${package_dirs[$site]}/$theme.zip" <"$checkout/$site.zip" >/dev/null
    printf '%s %s → %s\n' "$site" "$current_version" "${target_versions[$site]}"
done

backup_dir=$(mktemp -d "$HOME/cadangan-tema-reza-$(date +%Y%m%d-%H%M%S)-XXXXXX")
printf 'Folder cadangan %s\n' "$backup_dir"
for site in "${sites[@]}"; do
    as_owner "${owners[$site]}" tar -C "${paths[$site]}/wp-content/themes" -czf - "$theme" >"$backup_dir/$site.tar.gz" ||
        die "Gagal mencadangkan tema $site."
    tar -tzf "$backup_dir/$site.tar.gz" >/dev/null || die "Cadangan $site tidak bisa dibaca."
done

for site in "${sites[@]}"; do
    printf '\nMemperbarui tema %s\n' "$site"
    wp_site "$site" theme install "${package_dirs[$site]}/$theme.zip" --force ||
        die "Pembaruan berhenti di $site. Cadangan tersedia di $backup_dir."
    installed=$(wp_site "$site" theme get "$theme" --field=version)
    [[ "$installed" == "${target_versions[$site]}" ]] || die "Versi hasil pemasangan di $site tidak cocok."
    active=$(wp_site "$site" option get stylesheet)
    [[ "$active" == "${active_themes[$site]}" ]] || die "Tema aktif di $site berubah selama pembaruan."
    printf '%s selesai (%s)\n' "$site" "$installed"
done
