# This file should be sourced, not run

#
# Relevant Environment Variables:
#
#   ASPIREBUILD               absolute path to current builder's .aspirebuild directory or symlink
#                             In a root builder, the .aspirebuild link points to the absolute path of the root.
#
#   ASPIREBUILD_DEPTH         current depth of recursive builders
#
#   ASPIREBUILD_DEPTH_LIMIT   maximum depth of recursive builders (default 10, including the root)
#
#   ASPIREBUILD_BUILDER_DIR   absolute path to location of new builders.  defaults to $ASPIREBUILD/builders
#

set -o errexit
set -o nounset
set -o pipefail

# These are not exported, but will be visible in the tool if they wish to do so.
__ORIG_PWD=$PWD
__HERE=$(dirname "$0")
__HERE=$(realpath -s "$__HERE")   # canonicalize only, don't resolve symlinks

function warn  { echo "$@" >&2; }
function die   { warn "$@"; exit 1; }

function _find_or_create_dot_aspirebuild {
    local dir
    dir=$(dirname "$0")
    dir=$(realpath -s "$dir")    # like $__HERE, in that we don't chase symlinks
    while [[ -n $dir ]]; do
        local dot_aspirebuild="$dir/.aspirebuild"

        if [[ -d "$dot_aspirebuild" ]]; then
            dir=$(realpath "$dir")
            echo "$dir/.aspirebuild"
            return
        elif [[ -d "$dir/tools/_common/lib" ]]; then
            dir=$(realpath "$dir")
            ln -s "$dir" "$dot_aspirebuild"
            echo "$dir/.aspirebuild"
            return
        else
            newdir=$(dirname "$dir")
            [[ $newdir = "$dir" ]] && break
            dir=$newdir
        fi
    done
    die "Could not find aspirebuild root in any parent directory of $PWD"
}

# spawned builders will set this to the new builder's .aspirebuild dir
export ASPIREBUILD=${ASPIREBUILD:-$(_find_or_create_dot_aspirebuild)}
export ASPIREBUILD_DEPTH=$(( ${ASPIREBUILD_DEPTH:--1} + 1 )) # base is level 0, meaning builders will be at level 1

[[ $ASPIREBUILD_DEPTH -lt ${ASPIREBUILD_DEPTH_LIMIT:-10} ]] || die "Maximum aspirebuild recursion depth reached.  Aborted."

# We bail out early if our working directory contains spaces, rather than risk stepping on this mine later.
# We make reasonable efforts to quote bash arguments, but 'bash' and 'reasonable' do not belong in the same sentence.
[[ "$ASPIREBUILD" =~ [[:space:]] ]] && die "Refusing to deal with aspirebuild directory containing whitespace.  Aborted."

cd "$ASPIREBUILD"

# Run all prelude files under the current tool's lib (usually symlinked to tools/_common/lib)
preludes=$(
    shopt -s nullglob
    echo "$__HERE"/../lib/bash/prelude.d/*.bash "$__HERE"/../local/lib/bash/prelude.d/*.bash
);

for file in $preludes; do
    # shellcheck source=/dev/null
    [[ -f $file ]] && source "$file"
done
