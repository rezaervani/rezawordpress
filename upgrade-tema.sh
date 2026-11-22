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
    printf 'Penggunaan: bash upgrade-tema.sh [--site utama|palestina|duniaislam] [--ref REF_GIT]\n'
}

die() {
    printf '%s\n' "$*" >&2
    exit 1
}

while (( $# )); do
    case "$1" in
        --site)
            case "${2:-}" in
                utama|palestina|duniaislam) sites=("$2") ;;
                *) die 'Isi --site dengan utama, palestina, atau duniaislam.' ;;
            esac
            shift 2
            ;;
        --ref)
            [[ $# -ge 2 && -n "$2" && "$2" != -* ]] || die 'Isi --ref dengan satu ref Git.'
            ref=$2
            shift 2
            ;;
        --help|-h) usage; exit 0 ;;
        *) usage >&2; exit 1 ;;
    esac
done

wp_bin=$(command -v wp) || die 'WP-CLI (wp) perlu tersedia di PATH.'
command -v git >/dev/null || die 'Git perlu tersedia di PATH.'
command -v flock >/dev/null || die 'flock perlu tersedia di PATH.'

exec 9>"$HOME/.reza-upgrade-tema.lock"
flock -n 9 || die 'Pembaruan tema lain sedang berjalan.'

declare -A paths owners target_versions active_themes stage_dirs
operator=$(id -un)
checkout=$(mktemp -d)
pending_site=

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

restore_theme() {
    local site=$1 owner live stage previous
    owner=${owners[$site]}
    live="${paths[$site]}/wp-content/themes/$theme"
    stage=${stage_dirs[$site]}
    previous="$stage.previous"

    if [[ -e "$previous" || -L "$previous" ]]; then
        if [[ -e "$live" || -L "$live" ]]; then
            as_owner "$owner" mv -T -- "$live" "$stage/rejected" || return 1
        fi
        as_owner "$owner" mv -T -- "$previous" "$live" || return 1
        printf 'Folder tema lama %s sudah dikembalikan.\n' "$site" >&2
    elif [[ ! -e "$stage/$theme" && -d "$live" ]]; then
        # A theme restored into a previously empty location failed verification.
        as_owner "$owner" mv -T -- "$live" "$stage/rejected" || return 1
    else
        return 0
    fi
    wp_site "$site" eval 'wp_clean_themes_cache(false);' >/dev/null 2>&1 || true
}

cleanup() {
    local result=$? site keep_stage=
    if [[ -n "$pending_site" ]] && ! restore_theme "$pending_site"; then
        keep_stage=$pending_site
        printf 'Gagal mengembalikan folder %s secara otomatis. Periksa %s.previous.\n' \
            "$pending_site" "${stage_dirs[$pending_site]}" >&2
        result=1
    fi
    for site in "${!stage_dirs[@]}"; do
        if [[ "$site" != "$keep_stage" ]]; then
            as_owner "${owners[$site]}" rm -rf -- "${stage_dirs[$site]}" || true
        fi
    done
    rm -rf -- "$checkout"
    exit "$result"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

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
    [[ -f "$path/wp-load.php" && -d "$content/themes" ]] ||
        die "Instalasi WordPress tidak ditemukan di $path."

    owner=$(stat -Lc '%U' "$content")
    [[ "$owner" != root && "$owner" != UNKNOWN ]] ||
        die "Pemilik wp-content untuk $site harus akun WordPress non-root."
    owners[$site]=$owner
    home=$(wp_site "$site" option get home) || die "Gagal memeriksa alamat WordPress $site."
    [[ "${home%/}" == "$expected_home" ]] || die "Alamat $site tidak cocok. Ditemukan $home."
    as_owner "$owner" test -w "$content/themes" || die "Akun $owner tidak dapat menulis ke folder tema $site."
    active_themes[$site]=$(wp_site "$site" option get stylesheet)
    current_version=$(wp_site "$site" theme get "$theme" --field=version 2>/dev/null) ||
        current_version='versi lama tidak terbaca'
    target_versions[$site]=$(git -C "$checkout" show "$source_tree:$theme/style.css" |
        awk -F ': *' '$1 == "Version" { sub(/\r$/, "", $2); version=$2 } END { print version }') ||
        die "Paket tema $site tidak ditemukan pada $ref."
    [[ -n "${target_versions[$site]}" ]] || die "Versi paket tema $site tidak ditemukan."
    stage_dirs[$site]=$(as_owner "$owner" mktemp -d "$content/themes/.reza-upgrade-XXXXXX")
    previous="${stage_dirs[$site]}.previous"
    [[ ! -e "$previous" && ! -L "$previous" ]] || die "Folder sementara $site sudah terpakai."
    # All candidate files are new, readable by the web server, and owned by the installer.
    git -C "$checkout" archive --format=tar "$source_tree" "$theme" |
        as_owner "$owner" sh -c 'umask 022; tar --no-same-owner --no-same-permissions -xf - -C "$1"' \
            sh "${stage_dirs[$site]}" || die "Gagal menyiapkan paket tema $site."
    as_owner "$owner" test -f "${stage_dirs[$site]}/$theme/functions.php" || die "Paket tema $site tidak lengkap."
    printf '%s %s → %s\n' "$site" "$current_version" "${target_versions[$site]}"
done

backup_dir=$(mktemp -d "$HOME/cadangan-tema-reza-$(date +%Y%m%d-%H%M%S)-XXXXXX")
printf 'Folder cadangan %s\n' "$backup_dir"
for site in "${sites[@]}"; do
    themes="${paths[$site]}/wp-content/themes"
    if [[ -e "$themes/$theme" || -L "$themes/$theme" ]]; then
        as_owner "${owners[$site]}" tar -C "$themes" -czf - "$theme" >"$backup_dir/$site.tar.gz" ||
            die "Gagal mencadangkan tema $site."
        tar -tzf "$backup_dir/$site.tar.gz" >/dev/null || die "Cadangan $site tidak bisa dibaca."
    else
        printf 'Folder tema lama %s tidak ditemukan.\n' "$site"
    fi
done

for site in "${sites[@]}"; do
    owner=${owners[$site]}
    live="${paths[$site]}/wp-content/themes/$theme"
    stage=${stage_dirs[$site]}
    previous="$stage.previous"
    printf '\nMemperbarui tema %s\n' "$site"
    pending_site=$site
    as_owner "$owner" sh -eu -c '
        if [ -e "$1" ] || [ -L "$1" ]; then mv -T -- "$1" "$2"; fi
        mv -T -- "$3" "$1"
    ' sh "$live" "$previous" "$stage/$theme" ||
        die "Pembaruan berhenti di $site. Cadangan tersedia di $backup_dir."
    wp_site "$site" eval 'wp_clean_themes_cache(false);' >/dev/null ||
        die "Gagal menyegarkan metadata tema $site."
    installed=$(wp_site "$site" theme get "$theme" --field=version) ||
        die "Gagal memeriksa tema yang dipasang di $site."
    [[ "$installed" == "${target_versions[$site]}" ]] || die "Versi hasil pemasangan di $site tidak cocok."
    active=$(wp_site "$site" option get stylesheet)
    [[ "$active" == "${active_themes[$site]}" ]] || die "Tema aktif di $site berubah selama pembaruan."
    pending_site=
    # Mixed ownership can also prevent deleting the detached directory.
    if ! as_owner "$owner" rm -rf -- "$previous" 2>/dev/null; then
        printf 'Sisa folder tema lama %s ada di %s. Cadangan utuh tersedia di %s.\n' "$site" "$previous" "$backup_dir"
    fi
    printf '%s selesai (%s)\n' "$site" "$installed"
done
