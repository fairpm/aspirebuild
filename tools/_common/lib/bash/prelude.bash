# This file should be sourced, not run

set -o errexit
set -o nounset
set -o pipefail

function warn { echo "$@" >&2; }
function die { warn "$@"; exit 1; }

ASPIREBUILD_ORIG_PWD=$PWD

ASPIREBUILD_TOOL_DIR=${ASPIREBUILD_TOOL_DIR:-$(dirname "$0")/..}
ASPIREBUILD_TOOL_DIR=$(realpath "$ASPIREBUILD_TOOL_DIR")

ASPIREBUILD_BASE_DIR=${ASPIREBUILD_BASE_DIR:-$ASPIREBUILD_TOOL_DIR/../..}
ASPIREBUILD_BASE_DIR=$(realpath "$ASPIREBUILD_BASE_DIR")

ASPIREBUILD_BUILDER_DIR=${ASPIREBUILD_BUILDER_DIR:-$ASPIREBUILD_BASE_DIR/builders}
ASPIREBUILD_BUILDER_DIR=$(realpath "$ASPIREBUILD_BUILDER_DIR")

export ASPIREBUILD_ORIG_PWD ASPIREBUILD_TOOL_DIR ASPIREBUILD_BASE_DIR ASPIREBUILD_BUILDER_DIR

# We bail out early if our cwd contains spaces, rather than risk stepping on this mine later.
# We make reasonable efforts to quote bash arguments, but 'bash' and 'reasonable' do not belong in the same sentence.
cd "$ASPIREBUILD_TOOL_DIR" || die "Could not cd to $ASPIREBUILD_TOOL_DIR"
[[ "$PWD" =~ [[:space:]] ]] && die "Refusing to deal with working directory containing whitespace.  Aborted."

mkdir -p "$ASPIREBUILD_BUILDER_DIR"

for file in $(shopt -s nullglob; echo "$ASPIREBUILD_TOOL_DIR/lib/bash/prelude.d"/*.bash); do
    # shellcheck source=/dev/null
    source "$file"
done
