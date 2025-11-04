{
  inputs = {
#   nixpkgs.url = "github:nixos/nixpkgs";
#   nixpkgs.url = "github:nixos/nixpkgs/25.05";
#   nixpkgs.url = "github:nixos/nixpkgs/nixpkgs-unstable";
    nixpkgs.url = "github:nixos/nixpkgs/a5e47a4bea3996a6511f1da3cf6ba92e71a95f04"; # (2025-10-30)
    flake-utils.url = "github:numtide/flake-utils/11707dc2f618dd54ca8739b309ec4fc024de578b"; # (2024-11-13)
  };

  outputs = { self, nixpkgs, flake-utils }:
    flake-utils.lib.eachDefaultSystem (system:
      let
        pkgs = nixpkgs.legacyPackages.${system};
      in {
        # devShell is great for 'nix develop', but running bash in a builder still uses noninteractive bash :-/
        devShell = with pkgs; mkShell {
          buildInputs = [
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
          shellHook = ''
            export SHELL=${lib.getExe pkgs.bash}
          '';
        };

        # WIP docker support
        # https://nixos.org/manual/nixpkgs/stable/#ssec-pkgs-dockerTools-buildImage
        # https://ryantm.github.io/nixpkgs/builders/images/dockertools/ (older but not hideous)
        packages = {
          dockerImage = pkgs.dockerTools.buildImage {
            name = "aspirebuild";
            tag = builtins.substring 0 9 (self.rev or "dev"); # tag with git revision, or 'dev' if dirty

            # created = "now";   # defaults to 1 (that is, 1 second past the epoch). 'now' is not binary-reproducible


            config = {
              # https://community.flake.parts/haskell-flake/docker
              Env = [
                "SSL_CERT_FILE=${pkgs.cacert}/etc/ssl/certs/ca-bundle.crt"
                "SYSTEM_CERTIFICATE_PATH=${pkgs.cacert}/etc/ssl/certs/ca-bundle.crt"
              ];
              # Cmd = [ "/path/to/command" ];
              # WorkingDir = "/data";
              # Volumes = { "/data" = { }; };
            };

            # tag = "sometag"     # default null = nix output hash
            #
            # fromImage = "/path/to/repository-tarball.tgz";  # default null ~ 'FROM scratch'
            # fromImageName = null;                           # default null = first image in repo tarball
            # fromImageTag = "latest";                        # default null = first tag for the base image
            # diskSize = 1024;          # default 1024 (always in MiB)
            # buildVMMemorySize = 512;  # default 512  (always in MiB)
            #
            # copyToRoot = pkgs.buildEnv {
            #   name = "image-root";
            #   paths = [ pkgs.redis ];
            #   pathsToLink = [ "/bin" ];
            # };
            #
            # runAsRoot = ''
            # #!${pkgs.runtimeShell}
            # mkdir -p /data
            # '';
          };
        };
      }
  );
}
