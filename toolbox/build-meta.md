# Build Meta

This JSON metadata document is _separate_ from the publisher-provided (signed) **Package Meta**, and contains the meta collected by FAIR for use in evaluating the package to assign trust scores, approve for federation, apply labels, and catalogue the entry. 

| Key       | Req'd | Data Source                | Value, FAIR Format          |
| --------- | ----- | -------------------------- | --------------------------- |
| `id`      | yes   | package DID                | DID (cache DID document     |
| 'package' | yes   | plugin slug                | package-build-meta document |
| `release` | yes   | version from plugin header | releae-build-meta document  |                      |


## Package Build Meta Document

This JSON metadata document contains meta which relates to the package generally rather than to a specific release.

| Key                 | Req'd | Data Source                | Value, FAIR Format             |
| ------------------- | ----- | -------------------------- | ------------------------------ |
| `domain_alias`      | no    | external dns validation    | string with result             |
| `domain_reputation` | no    | external checks, APIs      | json list with results         |
| `domain_rbls`       | no    | external checks, APIs      | json list with results         |
| `provenance`        | no    | publisher attestations     | json document                  |
| 'project_health`    | no    | generated                  | json `project-health` document |


### Project Health Document

| Key                 | Req'd | Data Source                | Value, FAIR Format             |
| ------------------- | ----- | -------------------------- | ------------------------------ |


## Release Build Meta Document

This JSON metadata document contains meta wich relates to a specific release (version) of the package.

| Key                | Req'd | Data Source                | Value, FAIR Format                           |
| ------------------ | ----- | -------------------------- | -------------------------------------------- |
| `version`          | yes   | Plugin headers             | string                                       |
| `release_date`     | yes   | infer from svn?            | ISO formatted date string, YYYY-MM-DD        |
| `sbom`             | yes   |                            | [SPDX](https://spdx.dev/)-formatted SBOM     |
| `cve`              | no    | API requests               | cve label                                    |
| `wporg_scan`       | no    | .org scan tools            | results from scan tools                      |
| `php_version`      | no    | code scan                  | json list, min & max compatible php versions |
| `core_version`     | no    | package meta + code scan   | json list, min & max compatible wp core versions observale + requires & tested-to |
| `file_permissions` | no    | code scan                  | world-write octal permissions; corrected?    |
| `phpcs`            | no    | code scan                  | scan result as json document                 |
| `malware_scan`     | no    | code scan and/or API       | scan result as json document                 |





