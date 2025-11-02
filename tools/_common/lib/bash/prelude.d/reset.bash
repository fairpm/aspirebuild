# This file should be sourced, not run
#
# WARNING: By no means does this script create a fully hermetic environment.
#          It's meant to help make builds more reproducible, but does not guarantee it.

function nix_only_path {
  perl -MEnv=PATH -E 'say join ":", sort (grep /^\/nix/, (split /:/, $PATH));'
}

# note: __ORIG_PATH is not exported by default
__ORIG_PATH=$PATH

PATH=$(nix_only_path)
