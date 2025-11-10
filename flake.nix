{
  # a work in progress.  only devshell is supported for now.

  description = "AspireBuild";

  inputs = {
    flake-parts.url = "github:hercules-ci/flake-parts";
    flake-root.url = "github:srid/flake-root";
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
  };

  outputs =
    inputs@{ self, flake-parts, ... }:
    flake-parts.lib.mkFlake { inherit inputs; } {
      imports = [
        inputs.flake-root.flakeModule
      ];
      systems = [
        "x86_64-linux"
        "aarch64-linux"
        "aarch64-darwin"
        "x86_64-darwin"
      ];

      # most of the flake should go in here
      perSystem =
        {
          config,
          self',
          inputs',
          pkgs,
          system,
          ...
        }:
        # Per-system attributes can be defined here. The self' and inputs'
        # module parameters provide easy access to attributes of the same
        # system.
        let
          buildInputs = with pkgs; [
            bashInteractive
            coreutils
            curl
            git
            gnutar
            jq
            just
            lrzip
            perl
            php
            php84Packages.composer
            subversion
            sqlite
            systemfd
            tzdata
            watchexec
            zip
            zstd
          ];
        in
        {
          devShells.default = pkgs.mkShell {
            inherit buildInputs;

            inputsFrom = [ config.flake-root.devShell ]; # sets $FLAKE_ROOT

            shellHook = ''
              export ASPIREBUILD=$FLAKE_ROOT
            '';
          };

          # invoke with `nix fmt flake.nix`
          formatter = pkgs.nixfmt-rfc-style;
        };

      flake = {
        # system-agnostic flake attributes go here.  we don't have any yet.
      };
    };
}
